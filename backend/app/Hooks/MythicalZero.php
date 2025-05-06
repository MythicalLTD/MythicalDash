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

class MythicalZero
{
    private string $url;
    private string $version;
    private string $instanceId;
    private bool $zeroTrustEnabled;
    private bool $telemetryEnabled;
    private string $licenseKey;

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
        string $licenseKey,
        string $zeroTrustEnabled,
        string $telemetryEnabled,
    ) {
        $this->url = rtrim($url, '/');
        $this->version = $version;
        $this->instanceId = $instanceId;
        $this->licenseKey = $licenseKey;
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
            $client = new \GuzzleHttp\Client();
            $headers = [
                'Content-Type' => 'application/json',
                'User-Agent' => 'MythicalDash-Telemetry/' . $this->version . ' (Instance: ' . $this->instanceId . ')',
            ];
            $body = json_encode([
                'username' => $username,
                'email' => $email,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'ip' => $ip,
            ]);

            $request = new \GuzzleHttp\Psr7\Request(
                'PUT',
                $this->url . '/api/system/license/' . $this->licenseKey . '/mythicalzero/user/register',
                $headers,
                $body
            );

            try {
                $response = $client->sendAsync($request)->wait();
                App::getInstance(true)->getLogger()->debug('Registration telemetry sent successfully');
            } catch (\Exception $e) {
                App::getInstance(true)->getLogger()->error('Failed to send registration telemetry: ' . $e->getMessage());
            }
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
            $client = new \GuzzleHttp\Client();
            $headers = [
                'Content-Type' => 'application/json',
                'User-Agent' => 'MythicalDash-Telemetry/' . $this->version . ' (Instance: ' . $this->instanceId . ')',
            ];
            $body = json_encode([
                'username' => $username,
                'email' => $email,
                'first_name' => $first_name,
                'last_name' => $last_name,
                'ip' => $ip,
                'credits' => $credits,
                'uuid' => $uuid,
                'banned' => $banned,
                'verified' => $verified,
                'discord_id' => $discord_id,
                'github_id' => $github_id,
            ]);

            $request = new \GuzzleHttp\Psr7\Request(
                'PUT',
                $this->url . '/api/system/license/' . $this->licenseKey . '/mythicalzero/user/login',
                $headers,
                $body
            );

            try {
                $response = $client->sendAsync($request)->wait();
                App::getInstance(true)->getLogger()->debug('Login telemetry sent successfully');
            } catch (\Exception $e) {
                App::getInstance(true)->getLogger()->error('Failed to send login telemetry: ' . $e->getMessage());
            }
        } catch (\Exception $e) {
            App::getInstance(true)->getLogger()->error('Failed to send login telemetry: ' . $e->getMessage());
        }
    }
}
