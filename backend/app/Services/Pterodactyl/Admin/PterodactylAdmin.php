<?php

namespace MythicalDash\Services\Pterodactyl\Admin;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class PterodactylAdmin
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
     * Send a request to the Pterodactyl Admin API
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
} 