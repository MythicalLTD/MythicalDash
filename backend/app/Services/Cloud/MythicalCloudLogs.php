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

namespace MythicalDash\Services\Cloud;

use MythicalDash\App;
use MythicalDash\Chat\Database;
use MythicalDash\Config\ConfigFactory;
use MythicalDash\Config\ConfigInterface;

class MythicalCloudLogs
{
    /**
     * Upload logs to the cloud and return the log URL.
     *
     * @return string|null Returns the log URL on success, null on failure
     */
    public static function uploadDashboardLogsToCloud(): ?string
    {
        try {
            $appInstance = App::getInstance(true, false);
            $logs = $appInstance->getLogger()->getLogs(false);
            $appInstance->loadEnv();
            $db = new Database(
                $_ENV['DATABASE_HOST'],
                $_ENV['DATABASE_DATABASE'],
                $_ENV['DATABASE_USER'],
                $_ENV['DATABASE_PASSWORD']
            );
            $config = new ConfigFactory($db->getPdo());

            if (empty($logs)) {
                App::getInstance(true)->getLogger()->warning('No logs to upload');

                return null;
            }

            // Limit logs to 250 rows (keep the most recent logs)
            if (count($logs) > 250) {
                $logs = array_slice($logs, -250);
                App::getInstance(true)->getLogger()->debug('Web server logs truncated to 250 rows for upload');
            }

            $client = new \GuzzleHttp\Client();
            $headers = [
                'Content-Type' => 'application/json',
            ];

            $body = json_encode([
                'logs' => $logs,
            ]);

            $request = new \GuzzleHttp\Psr7\Request(
                'PUT',
                'https://mymythicalid.mythical.systems/api/system/license/' . $config->getSetting(ConfigInterface::LICENSE_KEY, 'NULL') . '/logs',
                $headers,
                $body
            );

            $response = $client->sendAsync($request)->wait();
            $responseBody = $response->getBody()->getContents();

            $responseData = json_decode($responseBody, true);
            if ($responseData === null) {
                App::getInstance(true)->getLogger()->error('Failed to decode response JSON');

                return null;
            }

            if (!isset($responseData['success']) || !$responseData['success']) {
                App::getInstance(true)->getLogger()->error('API error: ' . ($responseData['message'] ?? 'Unknown error'));

                return null;
            }
            if (!isset($responseData['logs'])) {
                App::getInstance(true)->getLogger()->error('No log URL in response');

                return null;
            }
            App::getInstance(true)->getLogger()->debug('Web server logs uploaded successfully');

            return $responseData['logs'];

        } catch (\Exception $e) {
            App::getInstance(true)->getLogger()->error('Failed to upload logs: ' . $e->getMessage());

            return null;
        }
    }

    public static function uploadWebServerLogsToCloud(): ?string
    {
        try {
            $appInstance = App::getInstance(true, false);
            $logs = $appInstance->getWebServerLogger()->getLogs(true);
            $appInstance->loadEnv();
            $db = new Database(
                $_ENV['DATABASE_HOST'],
                $_ENV['DATABASE_DATABASE'],
                $_ENV['DATABASE_USER'],
                $_ENV['DATABASE_PASSWORD']
            );
            $config = new ConfigFactory($db->getPdo());

            if (empty($logs)) {
                App::getInstance(true)->getLogger()->warning('No logs to upload');

                return null;
            }

            // Limit logs to 250 rows (keep the most recent logs)
            if (count($logs) > 250) {
                $logs = array_slice($logs, -250);
                App::getInstance(true)->getLogger()->debug('Web server logs truncated to 250 rows for upload');
            }

            $client = new \GuzzleHttp\Client();
            $headers = [
                'Content-Type' => 'application/json',
            ];

            $body = json_encode([
                'logs' => $logs,
            ]);

            $request = new \GuzzleHttp\Psr7\Request(
                'PUT',
                'https://mymythicalid.mythical.systems/api/system/license/' . $config->getSetting(ConfigInterface::LICENSE_KEY, 'NULL') . '/logs',
                $headers,
                $body
            );

            $response = $client->sendAsync($request)->wait();
            $responseBody = $response->getBody()->getContents();

            $responseData = json_decode($responseBody, true);
            if ($responseData === null) {
                App::getInstance(true)->getLogger()->error('Failed to decode response JSON');

                return null;
            }

            if (!isset($responseData['success']) || !$responseData['success']) {
                App::getInstance(true)->getLogger()->error('API error: ' . ($responseData['message'] ?? 'Unknown error'));

                return null;
            }
            if (!isset($responseData['logs'])) {
                App::getInstance(true)->getLogger()->error('No log URL in response');

                return null;
            }
            App::getInstance(true)->getLogger()->debug('Web server logs uploaded successfully');

            return $responseData['logs'];

        } catch (\Exception $e) {
            App::getInstance(true)->getLogger()->error('Failed to upload logs: ' . $e->getMessage());

            return null;
        }
    }

    /**
     * Get logs from the cloud.
     *
     * @return array|null Returns the logs on success, null on failure
     */
    public static function getLogsFromCloud(): ?array
    {
        // Implementation for getting logs from cloud
        return null;
    }
}
