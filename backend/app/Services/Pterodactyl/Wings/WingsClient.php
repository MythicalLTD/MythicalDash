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

namespace MythicalDash\Services\Pterodactyl\Wings;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use MythicalDash\Services\Pterodactyl\Exceptions\ServerException;
use MythicalDash\Services\Pterodactyl\Exceptions\RateLimitException;
use MythicalDash\Services\Pterodactyl\Exceptions\PermissionException;
use MythicalDash\Services\Pterodactyl\Exceptions\ValidationException;
use MythicalDash\Services\Pterodactyl\Exceptions\AuthenticationException;
use MythicalDash\Services\Pterodactyl\Exceptions\ResourceNotFoundException;

class WingsClient
{
    protected Client $client;
    protected string $url;
    protected string $token;

    /**
     * Create a new Wings client instance.
     *
     * @param string $url Wings server URL
     * @param string $token JWT token for authentication
     */
    public function __construct(string $url, string $token)
    {
        $this->url = rtrim($url, '/');
        $this->token = $token;

        $this->client = new Client([
            'base_uri' => $this->url,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    /**
     * Send a power signal to a server.
     *
     * @param string $serverId Server identifier
     * @param string $signal Power signal (start|stop|restart|kill)
     *
     * @return array Response data
     */
    public function sendPowerSignal(string $serverId, string $signal): array
    {
        return $this->request('POST', "/api/servers/{$serverId}/power", [
            'json' => ['signal' => $signal],
        ]);
    }

    /**
     * Send a command to a server.
     *
     * @param string $serverId Server identifier
     * @param string $command Command to execute
     *
     * @return array Response data
     */
    public function sendCommand(string $serverId, string $command): array
    {
        return $this->request('POST', "/api/servers/{$serverId}/commands", [
            'json' => ['command' => $command],
        ]);
    }

    /**
     * Get server details.
     *
     * @param string $serverId Server identifier
     *
     * @return array Server configuration and details
     */
    public function getServer(string $serverId): array
    {
        return $this->request('GET', "/api/servers/{$serverId}");
    }

    /**
     * Get server resource usage.
     *
     * @param string $serverId Server identifier
     *
     * @return array Server resource statistics
     */
    public function getServerResources(string $serverId): array
    {
        return $this->request('GET', "/api/servers/{$serverId}/resources");
    }

    /**
     * Make an HTTP request to the Wings API.
     *
     * @param string $method HTTP method
     * @param string $endpoint API endpoint
     * @param array $options Request options
     *
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws ResourceNotFoundException
     * @throws ServerException
     * @throws ValidationException
     * @throws RateLimitException
     *
     * @return array Response data
     */
    protected function request(string $method, string $endpoint, array $options = []): array
    {
        try {
            $response = $this->client->request($method, $endpoint, $options);
            $contents = $response->getBody()->getContents();

            return $contents ? json_decode($contents, true) : [];
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();

            if ($statusCode === 401) {
                throw AuthenticationException::invalidCredentials();
            }

            if ($statusCode === 403) {
                throw PermissionException::adminRequired();
            }

            if ($statusCode === 404) {
                throw ResourceNotFoundException::forResource('resource', $endpoint);
            }

            if ($statusCode === 409) {
                $error = json_decode($response->getBody()->getContents(), true);
                throw new ServerException($error['error'] ?? 'Server is in an invalid state');
            }

            if ($statusCode === 422) {
                $errors = json_decode($response->getBody()->getContents(), true);
                throw ValidationException::withErrors($errors['errors'] ?? []);
            }

            if ($statusCode === 429) {
                $retryAfter = (int) $response->getHeaderLine('Retry-After');
                throw RateLimitException::withRetryAfter($retryAfter);
            }

            throw $e;
        }
    }
}
