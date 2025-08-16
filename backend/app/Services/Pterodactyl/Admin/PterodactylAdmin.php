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
 * Make sure to read the docs before making any changes. And note that any changes you make will be overwritten by the next update.
 *
 * Be careful with the code you write, and make sure to test it before committing it.
 *
 * Please rather than modifying the dashboard code try to report the thing you wish on our github or write a plugin
 */

namespace MythicalDash\Services\Pterodactyl\Admin;

use MythicalDash\App;
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
     * Send a request to the Pterodactyl Admin API.
     *
     * @param string $method HTTP method
     * @param string $endpoint API endpoint
     * @param array $options Request options
     *
     * @throws GuzzleException
     *
     * @return array Response data
     */
    protected function request(string $method, string $endpoint, array $options = []): ?array
    {
        try {
            $response = $this->httpClient->request($method, $endpoint, $options);

            if ($response->getStatusCode() === 204) {
                App::getInstance(true)->getLogger()->error('Pterodactyl Admin API returned 204 status code');

                return [];
            }
            if ($response->getStatusCode() === 404) {
                App::getInstance(true)->getLogger()->error('Pterodactyl Admin API returned 404 status code');

                return [];
            }
            if ($response->getStatusCode() === 401) {
                App::getInstance(true)->getLogger()->error('Pterodactyl Admin API returned 401 status code');

                return [];
            }

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            App::getInstance(true)->getLogger()->error('Failed to send request to Pterodactyl Admin API: ' . $e->getMessage());

            return [];
        }
    }
}
