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

namespace MythicalDash\Hooks;

use MythicalDash\App;
use MythicalDash\Hooks\Telemetry\MythicalZeroType;
use MythicalDash\Hooks\MythicalSystems\Utils\EncryptionHandler;

class MythicalZero
{
    private string $url;
    private string $version;
    private string $instanceId;
    private bool $zeroTrustEnabled;
    private bool $telemetryEnabled;

    /**
     * Telemetry service constructor.
     *
     * @param string $url The URL where telemetry data is sent
     * @param string $version The version of the product
     * @param string $instanceId The unique instance identifier
     * @param string $zeroTrustEnabled Whether zero trust security is enabled
     * @param string $telemetryEnabled Whether telemetry collection is enabled
     */
    public function __construct(
        string $url,
        string $version,
        string $instanceId,
        string $zeroTrustEnabled,
        string $telemetryEnabled,
    ) {
        $this->url = rtrim($url, '/');
        $this->version = $version;
        $this->instanceId = $instanceId;
        $this->zeroTrustEnabled = $zeroTrustEnabled === 'true';
        $this->telemetryEnabled = $telemetryEnabled === 'true';
    }

    /**
     * Send registration data to telemetry service.
     *
     * @param string $username User's username
     * @param string $firstName User's first name
     * @param string $lastName User's last name
     * @param string $email User's email address
     * @param string $ip User's IP address
     */
    public function sendRegister(string $username, string $firstName, string $lastName, string $email, string $ip): void
    {
        if (!$this->zeroTrustEnabled) {
            App::getInstance(true)->getLogger()->warning('Zero trust is not enabled, skipping registration telemetry');

            return;
        }

        try {
            $encryptedData = [
                'username' => EncryptionHandler::encrypt($username, 'AxWTnecj85SI4bG6rIP8bvw2uCF7W5MmkJcQIkrYS80MzeTraQWyICL690XOio8F'),
                'first_name' => EncryptionHandler::encrypt($firstName, 'AxWTnecj85SI4bG6rIP8bvw2uCF7W5MmkJcQIkrYS80MzeTraQWyICL690XOio8F'),
                'last_name' => EncryptionHandler::encrypt($lastName, 'AxWTnecj85SI4bG6rIP8bvw2uCF7W5MmkJcQIkrYS80MzeTraQWyICL690XOio8F'),
                'email' => EncryptionHandler::encrypt($email, 'AxWTnecj85SI4bG6rIP8bvw2uCF7W5MmkJcQIkrYS80MzeTraQWyICL690XOio8F'),
                'ip' => EncryptionHandler::encrypt($ip, 'AxWTnecj85SI4bG6rIP8bvw2uCF7W5MmkJcQIkrYS80MzeTraQWyICL690XOio8F'),
            ];
            App::getInstance(true)->getLogger()->debug('Sending registration telemetry: ' . json_encode($encryptedData));
            $this->sendTelemetry(MythicalZeroType::$register, $encryptedData);
        } catch (\Exception $e) {
            App::getInstance(true)->getLogger()->error('Failed to send registration telemetry: ' . $e->getMessage());
        }
    }

    /**
     * Send login data to telemetry service.
     *
     * @param string $username User's username
     * @param string $first_name User's first name
     * @param string $last_name User's last name
     * @param string $email User's email address
     * @param string $credits User's credits
     * @param string $uuid User's UUID
     * @param string $ip User's IP address
     * @param string $banned User's banned status
     * @param string $verified User's verified status
     * @param string $discord_id User's Discord ID
     * @param string $github_id User's GitHub ID
     */
    public function sendLogin(
        string $username,
        string $first_name,
        string $last_name,
        string $email,
        string $credits,
        string $uuid,
        string $ip,
        string $banned,
        string $verified,
        string $discord_id = '',
        string $github_id = '',
    ): void {
        if (!$this->zeroTrustEnabled) {
            App::getInstance(true)->getLogger()->warning('Zero trust is not enabled, skipping login telemetry');

            return;
        }

        try {
            $encryptedData = [
                'username' => EncryptionHandler::encrypt($username, 'AxWTnecj85SI4bG6rIP8bvw2uCF7W5MmkJcQIkrYS80MzeTraQWyICL690XOio8F'),
                'first_name' => EncryptionHandler::encrypt($first_name, 'AxWTnecj85SI4bG6rIP8bvw2uCF7W5MmkJcQIkrYS80MzeTraQWyICL690XOio8F'),
                'last_name' => EncryptionHandler::encrypt($last_name, 'AxWTnecj85SI4bG6rIP8bvw2uCF7W5MmkJcQIkrYS80MzeTraQWyICL690XOio8F'),
                'email' => EncryptionHandler::encrypt($email, 'AxWTnecj85SI4bG6rIP8bvw2uCF7W5MmkJcQIkrYS80MzeTraQWyICL690XOio8F'),
                'credits' => EncryptionHandler::encrypt($credits, 'AxWTnecj85SI4bG6rIP8bvw2uCF7W5MmkJcQIkrYS80MzeTraQWyICL690XOio8F'),
                'uuid' => EncryptionHandler::encrypt($uuid, 'AxWTnecj85SI4bG6rIP8bvw2uCF7W5MmkJcQIkrYS80MzeTraQWyICL690XOio8F'),
                'ip' => EncryptionHandler::encrypt($ip, 'AxWTnecj85SI4bG6rIP8bvw2uCF7W5MmkJcQIkrYS80MzeTraQWyICL690XOio8F'),
                'banned' => EncryptionHandler::encrypt($banned, 'AxWTnecj85SI4bG6rIP8bvw2uCF7W5MmkJcQIkrYS80MzeTraQWyICL690XOio8F'),
                'verified' => EncryptionHandler::encrypt($verified, 'AxWTnecj85SI4bG6rIP8bvw2uCF7W5MmkJcQIkrYS80MzeTraQWyICL690XOio8F'),
                'discord_id' => EncryptionHandler::encrypt($discord_id, 'AxWTnecj85SI4bG6rIP8bvw2uCF7W5MmkJcQIkrYS80MzeTraQWyICL690XOio8F'),
                'github_id' => EncryptionHandler::encrypt($github_id, 'AxWTnecj85SI4bG6rIP8bvw2uCF7W5MmkJcQIkrYS80MzeTraQWyICL690XOio8F'),
            ];
            App::getInstance(true)->getLogger()->debug('Sending login telemetry: ' . json_encode($encryptedData));
            $this->sendTelemetry(MythicalZeroType::$login, $encryptedData);
        } catch (\Exception $e) {
            App::getInstance(true)->getLogger()->error('Failed to send login telemetry: ' . $e->getMessage());
        }
    }

    /**
     * Send telemetry data to the server.
     *
     * @param MythicalZeroType|string $type Type of telemetry event
     * @param array $data Additional data to send
     */
    private function sendTelemetry(MythicalZeroType|string $type, array $data = []): void
    {
        if (!$this->telemetryEnabled) {
            return;
        }

        try {
            $params = $this->buildTelemetryParams($type, $data);
            $url = $this->buildTelemetryUrl($params);
            App::getInstance(true)->getLogger()->debug('Sending telemetry: ' . $url);
            $this->sendRequest($url);
        } catch (\Exception $e) {
            App::getInstance(true)->getLogger()->error('Failed to send telemetry: ' . $e->getMessage());
        }
    }

    /**
     * Build telemetry parameters.
     *
     * @param MythicalZeroType|string $type Type of telemetry event
     * @param array $data Additional data
     *
     * @return array Parameters for the request
     */
    private function buildTelemetryParams(MythicalZeroType|string $type, array $data): array
    {
        return [
            'authKey' => 'AxWTnecj85SI4bG6rIP8bvw2uCF7W5MmkJcQIkrYS80MzeTraQWyICL690XOio8F',
            'clientId' => $this->instanceId,
            'action' => $type,
            'version' => $this->version,
            'additionalData' => $data,
            'osName' => php_uname('s') ?? 'Unknown',
            'kernelName' => php_uname('r') ?? 'Unknown',
            'cpuArchitecture' => php_uname('m') ?? 'Unknown',
            'osArchitecture' => PHP_INT_SIZE === 8 ? '64-bit' : '32-bit',
        ];
    }

    /**
     * Build the telemetry URL with query parameters.
     *
     * @param array $params Query parameters
     *
     * @return string Complete URL
     */
    private function buildTelemetryUrl(array $params): string
    {
        return $this->url . '/v2/telemetry/mythicaldash?' . http_build_query($params);
    }

    /**
     * Send HTTP request using cURL.
     *
     * @param string $url Target URL
     */
    private function sendRequest(string $url): void
    {
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPGET => true,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
            ],
            CURLOPT_TIMEOUT => 3,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);

        // Execute request in background
        if (function_exists('curl_exec')) {
            curl_exec($ch);
        }

        if (curl_errno($ch)) {
            App::getInstance(true)->getLogger()->error('Telemetry request failed: ' . curl_error($ch));
        }

        curl_close($ch);
    }
}
