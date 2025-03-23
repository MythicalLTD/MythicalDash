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

namespace MythicalDash\Services\Pterodactyl\Admin\Resources;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use MythicalDash\Services\Pterodactyl\Admin\PterodactylAdmin;
use MythicalDash\Services\Pterodactyl\Exceptions\RateLimitException;
use MythicalDash\Services\Pterodactyl\Exceptions\PermissionException;
use MythicalDash\Services\Pterodactyl\Exceptions\ValidationException;
use MythicalDash\Services\Pterodactyl\Exceptions\AuthenticationException;
use MythicalDash\Services\Pterodactyl\Exceptions\ResourceNotFoundException;

class NodesResource extends PterodactylAdmin
{
    /**
     * List all nodes.
     *
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws RateLimitException
     */
    public function listNodes(int $page = 1, int $perPage = 50): array
    {
        try {
            return $this->request('GET', '/api/application/nodes', [
                'query' => [
                    'page' => $page,
                    'per_page' => $perPage,
                ],
            ]);
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();

            if ($statusCode === 401) {
                throw AuthenticationException::invalidCredentials();
            }

            if ($statusCode === 403) {
                throw PermissionException::adminRequired();
            }

            if ($statusCode === 429) {
                $retryAfter = (int) $response->getHeaderLine('Retry-After');
                throw RateLimitException::withRetryAfter($retryAfter);
            }

            throw $e;
        }
    }

    /**
     * Get a specific node.
     *
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws ResourceNotFoundException
     * @throws RateLimitException
     */
    public function getNode(int $nodeId): array
    {
        try {
            return $this->request('GET', "/api/application/nodes/{$nodeId}");
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
                throw ResourceNotFoundException::forResource('node', (string) $nodeId);
            }

            if ($statusCode === 429) {
                $retryAfter = (int) $response->getHeaderLine('Retry-After');
                throw RateLimitException::withRetryAfter($retryAfter);
            }

            throw $e;
        }
    }

    /**
     * Get node configuration.
     *
     * @throws GuzzleException
     */
    public function getNodeConfiguration(int $nodeId): array
    {
        return $this->request('GET', "/api/application/nodes/{$nodeId}/configuration");
    }

    /**
     * Create a new node.
     *
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws ValidationException
     * @throws RateLimitException
     */
    public function createNode(array $data): array
    {
        try {
            return $this->request('POST', '/api/application/nodes', [
                'json' => $data,
            ]);
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();

            if ($statusCode === 401) {
                throw AuthenticationException::invalidCredentials();
            }

            if ($statusCode === 403) {
                throw PermissionException::adminRequired();
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

    /**
     * Update a node.
     *
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws ResourceNotFoundException
     * @throws ValidationException
     * @throws RateLimitException
     */
    public function updateNode(int $nodeId, array $data): array
    {
        try {
            return $this->request('PATCH', "/api/application/nodes/{$nodeId}", [
                'json' => $data,
            ]);
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
                throw ResourceNotFoundException::forResource('node', (string) $nodeId);
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

    /**
     * Delete a node.
     *
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws ResourceNotFoundException
     * @throws RateLimitException
     */
    public function deleteNode(int $nodeId): array
    {
        try {
            return $this->request('DELETE', "/api/application/nodes/{$nodeId}");
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
                throw ResourceNotFoundException::forResource('node', (string) $nodeId);
            }

            if ($statusCode === 429) {
                $retryAfter = (int) $response->getHeaderLine('Retry-After');
                throw RateLimitException::withRetryAfter($retryAfter);
            }

            throw $e;
        }
    }

    /**
     * List node allocations.
     *
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws ResourceNotFoundException
     * @throws RateLimitException
     */
    public function listAllocations(int $nodeId, int $page = 1, int $perPage = 50): array
    {
        try {
            return $this->request('GET', "/api/application/nodes/{$nodeId}/allocations", [
                'query' => [
                    'page' => $page,
                    'per_page' => $perPage,
                ],
            ]);
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
                throw ResourceNotFoundException::forResource('node', (string) $nodeId);
            }

            if ($statusCode === 429) {
                $retryAfter = (int) $response->getHeaderLine('Retry-After');
                throw RateLimitException::withRetryAfter($retryAfter);
            }

            throw $e;
        }
    }

    /**
     * Create node allocations.
     *
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws ResourceNotFoundException
     * @throws ValidationException
     * @throws RateLimitException
     */
    public function createAllocations(int $nodeId, string $ip, array $ports): array
    {
        try {
            return $this->request('POST', "/api/application/nodes/{$nodeId}/allocations", [
                'json' => [
                    'ip' => $ip,
                    'ports' => $ports,
                ],
            ]);
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
                throw ResourceNotFoundException::forResource('node', (string) $nodeId);
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

    /**
     * Delete node allocation.
     *
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws ResourceNotFoundException
     * @throws RateLimitException
     */
    public function deleteAllocation(int $nodeId, int $allocationId): array
    {
        try {
            return $this->request('DELETE', "/api/application/nodes/{$nodeId}/allocations/{$allocationId}");
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
                throw ResourceNotFoundException::forResource('allocation', (string) $allocationId);
            }

            if ($statusCode === 429) {
                $retryAfter = (int) $response->getHeaderLine('Retry-After');
                throw RateLimitException::withRetryAfter($retryAfter);
            }

            throw $e;
        }
    }
}
