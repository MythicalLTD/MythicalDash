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

namespace MythicalDash\Services\Pterodactyl\Client;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class PterodactylClient
{
    private Client $httpClient;
    private string $apiKey;
    private string $baseUrl;

    public function __construct(string $baseUrl, string $apiKey)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->apiKey = $apiKey;
        $this->httpClient = new Client([
            'base_uri' => $this->baseUrl,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    /**
     * Get account details.
     *
     * @throws GuzzleException
     */
    public function getAccountDetails(): array
    {
        return $this->request('GET', '/api/client/account');
    }

    /**
     * Get account API keys.
     *
     * @throws GuzzleException
     */
    public function getApiKeys(): array
    {
        return $this->request('GET', '/api/client/account/api-keys');
    }

    /**
     * Create API key.
     *
     * @throws GuzzleException
     */
    public function createApiKey(string $description, array $allowedIps = []): array
    {
        return $this->request('POST', '/api/client/account/api-keys', [
            'json' => [
                'description' => $description,
                'allowed_ips' => $allowedIps,
            ],
        ]);
    }

    /**
     * Delete API key.
     *
     * @throws GuzzleException
     */
    public function deleteApiKey(string $identifier): array
    {
        return $this->request('DELETE', "/api/client/account/api-keys/{$identifier}");
    }

    /**
     * Send a request to the Pterodactyl API.
     *
     * @param string $method HTTP method
     * @param string $endpoint API endpoint
     * @param array $options Request options
     *
     * @throws GuzzleException
     *
     * @return array Response data
     */
    protected function request(string $method, string $endpoint, array $options = []): array
    {
        $response = $this->httpClient->request($method, $endpoint, $options);

        return json_decode($response->getBody()->getContents(), true);
    }
}
