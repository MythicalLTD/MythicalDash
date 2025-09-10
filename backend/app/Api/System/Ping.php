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

use MythicalDash\App;

$router->get('/api/system/ping', function () {
    App::init();
    $appInstance = App::getInstance(true);
    $config = $appInstance->getConfig();

    if (isset($_GET['host'])) {
        $host = $_GET['host'];

        // Strict validation: allow only valid IPv4, IPv6, or hostname (RFC 1123)
        $isValid = false;
        if (filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6)) {
            $isValid = true;
        } elseif (preg_match('/^(?=.{1,253}$)(?!-)[A-Za-z0-9-]{1,63}(?<!-)(\.(?!-)[A-Za-z0-9-]{1,63}(?<!-))*\.?$/', $host)) {
            // Hostname validation (RFC 1123, no protocol, no slashes, no spaces)
            $isValid = true;
        }

        if (!$isValid) {
            $appInstance->BadRequest('Invalid host', [
                'error' => 'Invalid host format. Only valid IP addresses or hostnames are allowed.',
            ]);

            return;
        }

        // Block dangerous hostnames (localhost, 127.0.0.1, ::1, etc.)
        $blockedHosts = [
            'localhost',
            '127.0.0.1',
            '::1',
            '0.0.0.0',
            'broadcasthost',
            'local',
            'ip6-localhost',
            'ip6-loopback',
        ];
        $lowerHost = strtolower($host);
        if (
            in_array($lowerHost, $blockedHosts, true)
            || preg_match('/^(127\.|0\.0\.0\.0|::1)/', $lowerHost)
        ) {
            $appInstance->BadRequest('Pinging local or reserved addresses is not allowed.', [
                'error' => 'Blocked host',
            ]);

            return;
        }

        // Use escapeshellarg for extra safety, but validation above should make this safe
        $command = 'ping -c 1 -W 2 ' . escapeshellarg($host);

        // Use proc_open for more control and to avoid shell injection
        $descriptorspec = [
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];
        $process = proc_open($command, $descriptorspec, $pipes, null, null, ['bypass_shell' => true]);
        if (!is_resource($process)) {
            $appInstance->InternalServerError('Failed to start ping process', [
                'error' => 'Could not start ping process',
            ]);

            return;
        }

        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $returnVar = proc_close($process);

        if ($returnVar !== 0) {
            $appInstance->BadRequest('Failed to ping host', [
                'error' => 'Could not ping host',
                'message' => $stderr ?: $stdout,
            ]);

            return;
        }

        // Extract ping time from output
        $pingTime = 0;
        if (preg_match('/time=([0-9.]+)/', $stdout, $matches)) {
            $pingTime = floatval($matches[1]);
        }

        $appInstance->OK('Ping successful', [
            'host' => $host,
            'ping' => $pingTime,
        ]);

        return;
    }
});
