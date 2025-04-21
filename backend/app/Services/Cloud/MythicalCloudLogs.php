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
            $logs = App::getInstance(true, false)->getLogger()->getLogs();
            if (empty($logs)) {
                App::getInstance(true)->getLogger()->warning('No logs to upload');

                return null;
            }

            // Limit logs to 250 rows (keep the most recent logs)
            if (count($logs) > 250) {
                $logs = array_slice($logs, -250);
                App::getInstance(true)->getLogger()->info('Logs truncated to 250 rows for upload');
            }

            $route = 'https://api.mythical.systems/log';

            // Convert logs array to text with newlines
            $logsText = implode("\n", $logs);

            $ch = curl_init($route);
            if ($ch === false) {
                App::getInstance(true)->getLogger()->error('Failed to initialize cURL');

                return null;
            }

            curl_setopt_array($ch, [
                CURLOPT_CUSTOMREQUEST => 'PUT',
                CURLOPT_POSTFIELDS => $logsText,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: text/plain',
                    'Content-Length: ' . strlen($logsText),
                ],
                CURLOPT_TIMEOUT => 10,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($response === false) {
                App::getInstance(true)->getLogger()->error('cURL error: ' . $curlError);

                return null;
            }

            if ($httpCode !== 200) {
                App::getInstance(true)->getLogger()->error('HTTP error: ' . $httpCode);

                return null;
            }

            $responseData = json_decode($response, true);
            if ($responseData === null) {
                App::getInstance(true)->getLogger()->error('Failed to decode response JSON');

                return null;
            }

            if (!isset($responseData['success']) || !$responseData['success']) {
                App::getInstance(true)->getLogger()->error('API error: ' . ($responseData['message'] ?? 'Unknown error'));

                return null;
            }

            if (!isset($responseData['user_url'])) {
                App::getInstance(true)->getLogger()->error('No log URL in response');

                return null;
            }

            App::getInstance(true)->getLogger()->info('Logs uploaded successfully to: ' . $responseData['user_url']);

            return $responseData['user_url'];

        } catch (\Exception $e) {
            App::getInstance(true)->getLogger()->error('Failed to upload logs: ' . $e->getMessage());

            return null;
        }
    }

    public static function uploadWebServerLogsToCloud(): ?string
    {
        try {
            $logs = App::getInstance(true, false)->getWebServerLogger()->getLogs(true);
            if (empty($logs)) {
                App::getInstance(true)->getLogger()->warning('No logs to upload');

                return null;
            }

            // Limit logs to 250 rows (keep the most recent logs)
            if (count($logs) > 250) {
                $logs = array_slice($logs, -250);
                App::getInstance(true)->getLogger()->info('Web server logs truncated to 250 rows for upload');
            }

            $route = 'https://api.mythical.systems/log';

            // Convert logs array to text with newlines
            $logsText = implode("\n", $logs);

            $ch = curl_init($route);
            if ($ch === false) {
                App::getInstance(true)->getLogger()->error('Failed to initialize cURL');

                return null;
            }

            curl_setopt_array($ch, [
                CURLOPT_CUSTOMREQUEST => 'PUT',
                CURLOPT_POSTFIELDS => $logsText,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: text/plain',
                    'Content-Length: ' . strlen($logsText),
                ],
                CURLOPT_TIMEOUT => 10,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($response === false) {
                App::getInstance(true)->getLogger()->error('cURL error: ' . $curlError);

                return null;
            }

            if ($httpCode !== 200) {
                App::getInstance(true)->getLogger()->error('HTTP error: ' . $httpCode);

                return null;
            }

            $responseData = json_decode($response, true);
            if ($responseData === null) {
                App::getInstance(true)->getLogger()->error('Failed to decode response JSON');

                return null;
            }

            if (!isset($responseData['success']) || !$responseData['success']) {
                App::getInstance(true)->getLogger()->error('API error: ' . ($responseData['message'] ?? 'Unknown error'));

                return null;
            }

            if (!isset($responseData['user_url'])) {
                App::getInstance(true)->getLogger()->error('No log URL in response');

                return null;
            }

            App::getInstance(true)->getLogger()->info('Logs uploaded successfully to: ' . $responseData['user_url']);

            return $responseData['user_url'];

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
