<?php

namespace MythicalDash\Services\Pterodactyl\Admin\Resources;

use GuzzleHttp\Exception\ClientException;
use MythicalDash\Services\Pterodactyl\Admin\PterodactylAdmin;
use MythicalDash\Services\Pterodactyl\Exceptions\AuthenticationException;
use MythicalDash\Services\Pterodactyl\Exceptions\PermissionException;
use MythicalDash\Services\Pterodactyl\Exceptions\RateLimitException;
use MythicalDash\Services\Pterodactyl\Exceptions\ResourceNotFoundException;
use MythicalDash\Services\Pterodactyl\Exceptions\ValidationException;

class DatabasesResource extends PterodactylAdmin
{
    /**
     * List all database hosts
     *
     * @param int $page
     * @param int $perPage
     * @return array
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws RateLimitException
     */
    public function listDatabaseHosts(int $page = 1, int $perPage = 50): array
    {
        try {
            return $this->request('GET', '/api/application/databases', [
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
     * Get a specific database host
     *
     * @param int $databaseId
     * @return array
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws ResourceNotFoundException
     * @throws RateLimitException
     */
    public function getDatabaseHost(int $databaseId): array
    {
        try {
            return $this->request('GET', "/api/application/databases/{$databaseId}");
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
                throw ResourceNotFoundException::forResource('database host', (string) $databaseId);
            }

            if ($statusCode === 429) {
                $retryAfter = (int) $response->getHeaderLine('Retry-After');
                throw RateLimitException::withRetryAfter($retryAfter);
            }

            throw $e;
        }
    }

    /**
     * Create a new database host
     *
     * @param string $name
     * @param string $host
     * @param int $port
     * @param string $username
     * @param string $password
     * @param int $nodeId
     * @return array
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws ValidationException
     * @throws RateLimitException
     */
    public function createDatabaseHost(
        string $name,
        string $host,
        int $port,
        string $username,
        string $password,
        int $nodeId
    ): array {
        try {
            return $this->request('POST', '/api/application/databases', [
                'json' => [
                    'name' => $name,
                    'host' => $host,
                    'port' => $port,
                    'username' => $username,
                    'password' => $password,
                    'node_id' => $nodeId,
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
     * Update a database host
     *
     * @param int $databaseId
     * @param array $data
     * @return array
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws ResourceNotFoundException
     * @throws ValidationException
     * @throws RateLimitException
     */
    public function updateDatabaseHost(int $databaseId, array $data): array
    {
        try {
            return $this->request('PATCH', "/api/application/databases/{$databaseId}", [
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
                throw ResourceNotFoundException::forResource('database host', (string) $databaseId);
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
     * Delete a database host
     *
     * @param int $databaseId
     * @return array
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws ResourceNotFoundException
     * @throws RateLimitException
     */
    public function deleteDatabaseHost(int $databaseId): array
    {
        try {
            return $this->request('DELETE', "/api/application/databases/{$databaseId}");
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
                throw ResourceNotFoundException::forResource('database host', (string) $databaseId);
            }

            if ($statusCode === 429) {
                $retryAfter = (int) $response->getHeaderLine('Retry-After');
                throw RateLimitException::withRetryAfter($retryAfter);
            }

            throw $e;
        }
    }

    /**
     * Reset database host password
     *
     * @param int $databaseId
     * @return array
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws ResourceNotFoundException
     * @throws RateLimitException
     */
    public function resetDatabaseHostPassword(int $databaseId): array
    {
        try {
            return $this->request('POST', "/api/application/databases/{$databaseId}/reset-password");
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
                throw ResourceNotFoundException::forResource('database host', (string) $databaseId);
            }

            if ($statusCode === 429) {
                $retryAfter = (int) $response->getHeaderLine('Retry-After');
                throw RateLimitException::withRetryAfter($retryAfter);
            }

            throw $e;
        }
    }
} 