<?php

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
     * Send a request to the Pterodactyl API
     *
     * @param string $method HTTP method
     * @param string $endpoint API endpoint
     * @param array $options Request options
     * @return array Response data
     * @throws GuzzleException
     */
    protected function request(string $method, string $endpoint, array $options = []): array
    {
        $response = $this->httpClient->request($method, $endpoint, $options);
        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Get account details
     *
     * @return array
     * @throws GuzzleException
     */
    public function getAccountDetails(): array
    {
        return $this->request('GET', '/api/client/account');
    }

    /**
     * Get account API keys
     *
     * @return array
     * @throws GuzzleException
     */
    public function getApiKeys(): array
    {
        return $this->request('GET', '/api/client/account/api-keys');
    }

    /**
     * Create API key
     *
     * @param string $description
     * @param array $allowedIps
     * @return array
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
     * Delete API key
     *
     * @param string $identifier
     * @return array
     * @throws GuzzleException
     */
    public function deleteApiKey(string $identifier): array
    {
        return $this->request('DELETE', "/api/client/account/api-keys/{$identifier}");
    }
} 