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
