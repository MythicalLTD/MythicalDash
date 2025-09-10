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

namespace MythicalDash\Services\Cloud;

use MythicalDash\App;

class MythicalCloudLogs
{
    /**
     * Upload dashboard logs to mclo.gs and return the log URL.
     *
     * @return string|null Returns the log URL on success, null on failure
     */
    public static function uploadDashboardLogsToCloud(): ?string
    {
        try {
            $appInstance = App::getInstance(true, false);
            $logs = $appInstance->getLogger()->getLogs(false);

            if (empty($logs)) {
                App::getInstance(true)->getLogger()->warning('No logs to upload');

                return null;
            }

            // Convert logs array to string content
            $logContent = implode("\n", $logs);

            // Limit content to 10MiB and 25k lines as per mclo.gs limits
            $lines = explode("\n", $logContent);
            if (count($lines) > 25000) {
                $lines = array_slice($lines, -25000);
                $logContent = implode("\n", $lines);
                App::getInstance(true)->getLogger()->debug('Dashboard logs truncated to 25000 lines for upload');
            }

            if (strlen($logContent) > 10485760) { // 10MiB
                $logContent = substr($logContent, -10485760);
                App::getInstance(true)->getLogger()->debug('Dashboard logs truncated to 10MiB for upload');
            }

            $client = new \GuzzleHttp\Client();

            $response = $client->post('https://api.mclo.gs/1/log', [
                'form_params' => [
                    'content' => $logContent,
                ],
                'timeout' => 30,
            ]);

            $responseData = json_decode($response->getBody()->getContents(), true);

            if (!$responseData || !$responseData['success']) {
                App::getInstance(true)->getLogger()->error('mclo.gs API error: ' . ($responseData['error'] ?? 'Unknown error'));

                return null;
            }

            App::getInstance(true)->getLogger()->debug('Dashboard logs uploaded to mclo.gs successfully');

            return $responseData['url'];

        } catch (\Exception $e) {
            App::getInstance(true)->getLogger()->error('Failed to upload dashboard logs to mclo.gs: ' . $e->getMessage());

            return null;
        }
    }

    /**
     * Upload web server logs to mclo.gs and return the log URL.
     *
     * @return string|null Returns the log URL on success, null on failure
     */
    public static function uploadWebServerLogsToCloud(): ?string
    {
        try {
            $appInstance = App::getInstance(true, false);
            $logs = $appInstance->getWebServerLogger()->getLogs(true);

            if (empty($logs)) {
                $logs = [
                    'Looks like the webserver logs are empty, this is good btw!',
                ];
            }

            // Convert logs array to string content
            $logContent = implode("\n", $logs);

            // Limit content to 10MiB and 25k lines as per mclo.gs limits
            $lines = explode("\n", $logContent);
            if (count($lines) > 25000) {
                $lines = array_slice($lines, -25000);
                $logContent = implode("\n", $lines);
                App::getInstance(true)->getLogger()->debug('Web server logs truncated to 25000 lines for upload');
            }

            if (strlen($logContent) > 10485760) { // 10MiB
                $logContent = substr($logContent, -10485760);
                App::getInstance(true)->getLogger()->debug('Web server logs truncated to 10MiB for upload');
            }

            $client = new \GuzzleHttp\Client();

            $response = $client->post('https://api.mclo.gs/1/log', [
                'form_params' => [
                    'content' => $logContent,
                ],
                'timeout' => 30,
            ]);

            $responseData = json_decode($response->getBody()->getContents(), true);

            if (!$responseData || !$responseData['success']) {
                App::getInstance(true)->getLogger()->error('mclo.gs API error: ' . ($responseData['error'] ?? 'Unknown error'));

                return null;
            }

            App::getInstance(true)->getLogger()->debug('Web server logs uploaded to mclo.gs successfully');

            return $responseData['url'];

        } catch (\Exception $e) {
            App::getInstance(true)->getLogger()->error('Failed to upload web server logs to mclo.gs: ' . $e->getMessage());

            return null;
        }
    }

    /**
     * Upload any log content to mclo.gs and return the log URL.
     *
     * @param string $logContent The raw log content
     * @param string $logType Optional log type for identification
     *
     * @return array|null Returns array with url, id, and raw url on success, null on failure
     */
    public static function uploadLogContent(string $logContent, string $logType = 'MythicalDash Log'): ?array
    {
        try {
            if (empty($logContent)) {
                App::getInstance(true)->getLogger()->warning('No log content to upload');

                return null;
            }

            // Limit content to 10MiB and 25k lines as per mclo.gs limits
            $lines = explode("\n", $logContent);
            if (count($lines) > 25000) {
                $lines = array_slice($lines, -25000);
                $logContent = implode("\n", $lines);
                App::getInstance(true)->getLogger()->debug('Log content truncated to 25000 lines for upload');
            }

            if (strlen($logContent) > 10485760) { // 10MiB
                $logContent = substr($logContent, -10485760);
                App::getInstance(true)->getLogger()->debug('Log content truncated to 10MiB for upload');
            }

            $client = new \GuzzleHttp\Client();

            $response = $client->post('https://api.mclo.gs/1/log', [
                'form_params' => [
                    'content' => $logContent,
                ],
                'timeout' => 30,
            ]);

            $responseData = json_decode($response->getBody()->getContents(), true);

            if (!$responseData || !$responseData['success']) {
                App::getInstance(true)->getLogger()->error('mclo.gs API error: ' . ($responseData['error'] ?? 'Unknown error'));

                return null;
            }

            App::getInstance(true)->getLogger()->debug("Log content uploaded to mclo.gs successfully: {$responseData['url']}");

            return $responseData;

        } catch (\Exception $e) {
            App::getInstance(true)->getLogger()->error('Failed to upload log content to mclo.gs: ' . $e->getMessage());

            return null;
        }
    }

    /**
     * Analyze log content using mclo.gs without saving it.
     *
     * @param string $logContent The raw log content to analyze
     *
     * @return array|null Returns analysis data on success, null on failure
     */
    public static function analyzeLogContent(string $logContent): ?array
    {
        try {
            if (empty($logContent)) {
                App::getInstance(true)->getLogger()->warning('No log content to analyze');

                return null;
            }

            // Limit content to 10MiB and 25k lines as per mclo.gs limits
            $lines = explode("\n", $logContent);
            if (count($lines) > 25000) {
                $lines = array_slice($lines, -25000);
                $logContent = implode("\n", $lines);
            }

            if (strlen($logContent) > 10485760) { // 10MiB
                $logContent = substr($logContent, -10485760);
            }

            $client = new \GuzzleHttp\Client();

            $response = $client->post('https://api.mclo.gs/1/analyse', [
                'form_params' => [
                    'content' => $logContent,
                ],
                'timeout' => 30,
            ]);

            $responseData = json_decode($response->getBody()->getContents(), true);

            if (!$responseData || !$responseData['success']) {
                App::getInstance(true)->getLogger()->error('mclo.gs analysis error: ' . ($responseData['error'] ?? 'Unknown error'));

                return null;
            }

            App::getInstance(true)->getLogger()->debug('Log content analyzed successfully');

            return $responseData;

        } catch (\Exception $e) {
            App::getInstance(true)->getLogger()->error('Failed to analyze log content with mclo.gs: ' . $e->getMessage());

            return null;
        }
    }

    /**
     * Get raw log content from mclo.gs by ID.
     *
     * @param string $logId The log ID from mclo.gs
     *
     * @return string|null Returns raw log content on success, null on failure
     */
    public static function getRawLogContent(string $logId): ?string
    {
        try {
            $client = new \GuzzleHttp\Client();

            $response = $client->get("https://api.mclo.gs/1/raw/{$logId}", [
                'timeout' => 30,
            ]);

            if ($response->getStatusCode() === 200) {
                return $response->getBody()->getContents();
            }

            return null;

        } catch (\Exception $e) {
            App::getInstance(true)->getLogger()->error("Failed to get raw log content from mclo.gs (ID: {$logId}): " . $e->getMessage());

            return null;
        }
    }

    /**
     * Get log insights from mclo.gs by ID.
     *
     * @param string $logId The log ID from mclo.gs
     *
     * @return array|null Returns insights data on success, null on failure
     */
    public static function getLogInsights(string $logId): ?array
    {
        try {
            $client = new \GuzzleHttp\Client();

            $response = $client->get("https://api.mclo.gs/1/insights/{$logId}", [
                'timeout' => 30,
            ]);

            $responseData = json_decode($response->getBody()->getContents(), true);

            if (!$responseData || !$responseData['success']) {
                App::getInstance(true)->getLogger()->error('mclo.gs insights error: ' . ($responseData['error'] ?? 'Unknown error'));

                return null;
            }

            return $responseData;

        } catch (\Exception $e) {
            App::getInstance(true)->getLogger()->error("Failed to get log insights from mclo.gs (ID: {$logId}): " . $e->getMessage());

            return null;
        }
    }

    /**
     * Check mclo.gs storage limits.
     *
     * @return array|null Returns limits data on success, null on failure
     */
    public static function getStorageLimits(): ?array
    {
        try {
            $client = new \GuzzleHttp\Client();

            $response = $client->get('https://api.mclo.gs/1/limits', [
                'timeout' => 30,
            ]);

            $responseData = json_decode($response->getBody()->getContents(), true);

            if (!$responseData) {
                App::getInstance(true)->getLogger()->error('Failed to get mclo.gs storage limits');

                return null;
            }

            return $responseData;

        } catch (\Exception $e) {
            App::getInstance(true)->getLogger()->error('Failed to get mclo.gs storage limits: ' . $e->getMessage());

            return null;
        }
    }
}
