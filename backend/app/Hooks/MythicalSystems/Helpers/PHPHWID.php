<?php

/*
 * This file is part of MythicalDash.
 * Please view the LICENSE file that was distributed with this source code.
 *
 * # MythicalSystems License v2.0
 *
 * ## Copyright (c) 2021–2025 MythicalSystems and Cassian Gherman
 *
 * Breaking any of the following rules will result in a permanent ban from the MythicalSystems community and all of its services.
 */

namespace MythicalDash\Hooks\MythicalSystems\Helpers;

class PHPHWID
{
    public static function generateHWID(): string
    {
        try {
            $hwid = '';

            // Get CPU info safely
            if (PHP_OS === 'Linux') {
                // Linux CPU info
                if (is_readable('/proc/cpuinfo')) {
                    $cpuinfo = @file_get_contents('/proc/cpuinfo');
                    if ($cpuinfo !== false) {
                        if (preg_match('/^Serial\s*: (.+)$/m', $cpuinfo, $matches)) {
                            $hwid .= $matches[1];
                        } elseif (preg_match('/^Hardware\s*: (.+)$/m', $cpuinfo, $matches)) {
                            $hwid .= $matches[1];
                        }
                    }
                }
            } else {
                // Windows CPU info
                $wmic = @shell_exec('wmic cpu get ProcessorId');
                if ($wmic && preg_match('/([A-Z0-9]+)/', $wmic, $matches)) {
                    $hwid .= $matches[1];
                }
            }

            // Get MAC address safely
            $mac = '';
            if (PHP_OS === 'Linux') {
                // Try multiple methods for Linux
                $methods = [
                    'cat /sys/class/net/$(ls /sys/class/net | head -n 1)/address',
                    "ifconfig -a | grep -Po 'HWaddr \K.*$'",
                    "ip link | grep -Po 'ether \K.*$'",
                ];

                foreach ($methods as $method) {
                    $mac = @shell_exec($method);
                    if ($mac) {
                        break;
                    }
                }
            } else {
                // Windows MAC address
                $mac = @shell_exec('getmac /NH /FO CSV | findstr /R "[0-9A-Fa-f][0-9A-Fa-f]"');
            }

            if ($mac) {
                $mac = preg_replace('/[^A-Fa-f0-9]/', '', $mac);
                $hwid .= $mac;
            }

            // Get hostname safely
            $hostname = @gethostname();
            if ($hostname) {
                $hwid .= $hostname;
            }

            // Fallback if no hardware info could be gathered
            if (empty($hwid)) {
                // Use a combination of server-specific information
                $hwid = implode('', [
                    php_uname(),
                    $_SERVER['SERVER_ADDR'] ?? '',
                    $_SERVER['SERVER_NAME'] ?? '',
                    $_SERVER['SERVER_SOFTWARE'] ?? '',
                ]);
            }

            // Always return a valid hash
            return hash('sha256', $hwid ?: uniqid('fallback_', true));

        } catch (\Throwable $e) {
            // Log error silently and return a fallback HWID
            error_log('HWID Generation Error: ' . $e->getMessage());

            return hash('sha256', uniqid('emergency_fallback_', true));
        } catch (\Exception $e) {
            return hash('sha256', uniqid('emergency_fallback_', true));
        }
    }
}
