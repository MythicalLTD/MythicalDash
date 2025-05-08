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

use MythicalDash\App;

$router->get('/api/system/ping', function () {
    App::init();
    $appInstance = App::getInstance(true);
    $config = $appInstance->getConfig();

    if (isset($_GET['host'])) {
        $host = $_GET['host'];

        // Use system ping command with 1 packet and 2 second timeout
        $command = 'ping -c 1 -W 2 ' . escapeshellarg($host);
        $output = [];
        $returnVar = 0;

        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            $appInstance->BadRequest('Failed to ping host', [
                'error' => 'Could not ping host',
                'message' => implode("\n", $output),
            ]);

            return;
        }

        // Extract ping time from output
        $pingTime = 0;
        foreach ($output as $line) {
            if (preg_match('/time=([0-9.]+)/', $line, $matches)) {
                $pingTime = floatval($matches[1]);
                break;
            }
        }

        $appInstance->OK('Ping successful', [
            'host' => $host,
            'ping' => $pingTime,
        ]);

        return;
    }
});
