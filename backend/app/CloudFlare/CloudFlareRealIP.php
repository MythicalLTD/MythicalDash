<?php

/*
 * This file is part of MythicalDash.
 *
 * MIT License
 *
 * Copyright (c) 2020-2025 MythicalSystems
 * Copyright (c) 2020-2025 Cassian Gherman (NaysKutzu)
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 * Please rather than modifying the dashboard code try to report the thing you wish on our github or write a plugin
 */

namespace MythicalDash\CloudFlare;

class CloudFlareRealIP
{
    /**
     * List of Cloudflare IPv4 and IPv6 ranges.
     */
    private static $cloudflareRanges = [
        // IPv4
        '173.245.48.0/20',
        '103.21.244.0/22',
        '103.22.200.0/22',
        '103.31.4.0/22',
        '141.101.64.0/18',
        '108.162.192.0/18',
        '190.93.240.0/20',
        '188.114.96.0/20',
        '197.234.240.0/22',
        '198.41.128.0/17',
        '162.158.0.0/15',
        '104.16.0.0/13',
        '104.24.0.0/14',
        '172.64.0.0/13',
        '131.0.72.0/22',
        // IPv6
        '2400:cb00::/32',
        '2606:4700::/32',
        '2803:f800::/32',
        '2405:b500::/32',
        '2405:8100::/32',
        '2a06:98c0::/29',
        '2c0f:f248::/32',
    ];

    /**
     * Get the real client IP address, considering Cloudflare and various proxy headers.
     *
     * Order of precedence:
     * 1. HTTP_CF_CONNECTING_IP (Cloudflare)
     * 2. HTTP_X_FORWARDED_FOR (load balancers, proxies)
     * 3. HTTP_X_REAL_IP (Nginx, other proxies)
     * 4. HTTP_X_CLIENT_IP (some proxies)
     * 5. HTTP_CLIENT_IP (some proxies)
     * 6. REMOTE_ADDR (fallback)
     *
     * @return string Real client IP address
     */
    public static function getRealIP()
    {
        $remoteAddr = $_SERVER['REMOTE_ADDR'] ?? '';

        // Cloudflare - highest priority
        if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            $cfIP = $_SERVER['HTTP_CF_CONNECTING_IP'];
            // If we're behind Cloudflare, trust their header
            if (self::isFromCloudflare($remoteAddr) || self::isValidIP($cfIP)) {
                return $cfIP;
            }
        }

        // Special case: If we're behind Cloudflare but don't have CF header,
        // and remote_addr is 127.0.0.1, try to trust X-Forwarded-For anyway
        if ($remoteAddr === '127.0.0.1' || $remoteAddr === '::1' || self::isFromCloudflare($remoteAddr)) {
            if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $forwardedIPs = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                $realIP = trim($forwardedIPs[0]);
                if (self::isValidIP($realIP)) {
                    return $realIP;
                }
            }
        }

        // Only check proxy headers if we should trust them
        if (self::shouldTrustProxyHeaders()) {
            // X-Forwarded-For (most common proxy header)
            if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $forwardedIPs = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                $realIP = trim($forwardedIPs[0]); // First IP is usually the real client
                if (self::isValidIP($realIP)) {
                    return $realIP;
                }
            }

            // X-Real-IP (Nginx, other proxies)
            if (!empty($_SERVER['HTTP_X_REAL_IP'])) {
                $realIP = $_SERVER['HTTP_X_REAL_IP'];
                if (self::isValidIP($realIP)) {
                    return $realIP;
                }
            }

            // X-Client-IP (some proxies)
            if (!empty($_SERVER['HTTP_X_CLIENT_IP'])) {
                $clientIP = $_SERVER['HTTP_X_CLIENT_IP'];
                if (self::isValidIP($clientIP)) {
                    return $clientIP;
                }
            }

            // Client-IP (some proxies)
            if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
                $clientIP = $_SERVER['HTTP_CLIENT_IP'];
                if (self::isValidIP($clientIP)) {
                    return $clientIP;
                }
            }
        }

        return $remoteAddr;
    }

    /**
     * Get debug information about IP detection.
     * This method can be used for troubleshooting IP detection issues.
     *
     * @return array Debug information
     */
    public static function getDebugInfo()
    {
        return [
            'remote_addr' => $_SERVER['REMOTE_ADDR'] ?? '',
            'http_cf_connecting_ip' => $_SERVER['HTTP_CF_CONNECTING_IP'] ?? '',
            'http_x_forwarded_for' => $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '',
            'http_x_real_ip' => $_SERVER['HTTP_X_REAL_IP'] ?? '',
            'http_x_client_ip' => $_SERVER['HTTP_X_CLIENT_IP'] ?? '',
            'http_client_ip' => $_SERVER['HTTP_CLIENT_IP'] ?? '',
            'detected_ip' => self::getRealIP(),
            'is_from_cloudflare' => self::isFromCloudflare($_SERVER['REMOTE_ADDR'] ?? ''),
            'trust_proxy_headers' => self::shouldTrustProxyHeaders(),
        ];
    }

    /**
     * Check if an IP is in a given CIDR range.
     */
    private static function ipInRange($ip, $cidr)
    {
        if (strpos($cidr, ':') !== false) {
            // IPv6
            return self::ipv6InRange($ip, $cidr);
        }
        // IPv4
        list($subnet, $mask) = explode('/', $cidr);

        return (ip2long($ip) & ~((1 << (32 - $mask)) - 1)) == ip2long($subnet);
    }

    /**
     * Check if an IPv6 address is in a given CIDR range.
     */
    private static function ipv6InRange($ip, $cidr)
    {
        list($subnet, $mask) = explode('/', $cidr);
        $ip_bin = inet_pton($ip);
        $subnet_bin = inet_pton($subnet);
        $mask = (int) $mask;
        $ip_bits = unpack('H*', $ip_bin)[1];
        $subnet_bits = unpack('H*', $subnet_bin)[1];
        $ip_bits = base_convert($ip_bits, 16, 2);
        $subnet_bits = base_convert($subnet_bits, 16, 2);

        return substr($ip_bits, 0, $mask) === substr($subnet_bits, 0, $mask);
    }

    /**
     * Check if an IP is from Cloudflare.
     */
    private static function isFromCloudflare($ip)
    {
        foreach (self::$cloudflareRanges as $range) {
            if (self::ipInRange($ip, $range)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Validate if an IP address is valid and not a private/local IP.
     *
     * @param string $ip IP address to validate
     *
     * @return bool True if valid and not private/local
     */
    private static function isValidIP($ip)
    {
        if (empty($ip)) {
            return false;
        }

        // Check if it's a valid IP address
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            return false;
        }

        // Reject private/local IPs
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return true;
        }

        return false;
    }

    /**
     * Check if we should trust proxy headers.
     * This can be configured via environment variable TRUST_PROXY_HEADERS.
     *
     * @return bool True if we should trust proxy headers
     */
    private static function shouldTrustProxyHeaders()
    {
        // Check environment variable first
        $trustProxy = $_ENV['TRUST_PROXY_HEADERS'] ?? '';
        if ($trustProxy === 'true' || $trustProxy === '1') {
            return true;
        }

        // Auto-detect if we're behind a reverse proxy
        $remoteAddr = $_SERVER['REMOTE_ADDR'] ?? '';

        // If remote_addr is localhost, we're likely behind a proxy
        if ($remoteAddr === '127.0.0.1' || $remoteAddr === '::1') {
            return true;
        }

        // If we have any proxy headers, we're likely behind a proxy
        $proxyHeaders = [
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'HTTP_X_CLIENT_IP',
            'HTTP_CLIENT_IP',
            'HTTP_CF_CONNECTING_IP',
        ];

        foreach ($proxyHeaders as $header) {
            if (!empty($_SERVER[$header])) {
                return true;
            }
        }

        return false;
    }
}
