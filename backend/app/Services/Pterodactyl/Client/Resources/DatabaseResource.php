<?php

namespace MythicalDash\Services\Pterodactyl\Client\Resources;

use GuzzleHttp\Exception\ClientException;
use MythicalDash\Services\Pterodactyl\Client\PterodactylClient;
use MythicalDash\Services\Pterodactyl\Exceptions\AuthenticationException;
use MythicalDash\Services\Pterodactyl\Exceptions\PermissionException;
use MythicalDash\Services\Pterodactyl\Exceptions\RateLimitException;
use MythicalDash\Services\Pterodactyl\Exceptions\ResourceNotFoundException;
use MythicalDash\Services\Pterodactyl\Exceptions\ValidationException;

class DatabaseResource extends PterodactylClient
{
    /**
     * List databases for a server
     *
     * @param string $serverId
     * @return array
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws ResourceNotFoundException
     * @throws RateLimitException
     */
    public function listDatabases(string $serverId): array
    {
        try {
            return $this->request('GET', "/api/client/servers/{$serverId}/databases");
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();

            if ($statusCode === 401) {
                throw AuthenticationException::invalidCredentials();
            }

            if ($statusCode === 403) {
                throw PermissionException::missingPermission('view_databases');
            }

            if ($statusCode === 404) {
                throw ResourceNotFoundException::forResource('server', $serverId);
            }

            if ($statusCode === 429) {
                $retryAfter = (int) $response->getHeaderLine('Retry-After');
                throw RateLimitException::withRetryAfter($retryAfter);
            }

            throw $e;
        }
    }

    /**
     * Create new database
     *
     * @param string $serverId
     * @param string $database
     * @param string $remote
     * @return array
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws ResourceNotFoundException
     * @throws ValidationException
     * @throws RateLimitException
     */
    public function createDatabase(string $serverId, string $database, string $remote = '%'): array
    {
        try {
            return $this->request('POST', "/api/client/servers/{$serverId}/databases", [
                'json' => [
                    'database' => $database,
                    'remote' => $remote,
                ],
            ]);
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();

            if ($statusCode === 401) {
                throw AuthenticationException::invalidCredentials();
            }

            if ($statusCode === 403) {
                throw PermissionException::missingPermission('create_database');
            }

            if ($statusCode === 404) {
                throw ResourceNotFoundException::forResource('server', $serverId);
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
     * Reset database password
     *
     * @param string $serverId
     * @param string $databaseId
     * @return array
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws ResourceNotFoundException
     * @throws RateLimitException
     */
    public function rotateDatabasePassword(string $serverId, string $databaseId): array
    {
        try {
            return $this->request('POST', "/api/client/servers/{$serverId}/databases/{$databaseId}/rotate-password");
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();

            if ($statusCode === 401) {
                throw AuthenticationException::invalidCredentials();
            }

            if ($statusCode === 403) {
                throw PermissionException::missingPermission('rotate_database_password');
            }

            if ($statusCode === 404) {
                throw ResourceNotFoundException::forResource('database', $databaseId);
            }

            if ($statusCode === 429) {
                $retryAfter = (int) $response->getHeaderLine('Retry-After');
                throw RateLimitException::withRetryAfter($retryAfter);
            }

            throw $e;
        }
    }

    /**
     * Delete database
     *
     * @param string $serverId
     * @param string $databaseId
     * @return array
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws ResourceNotFoundException
     * @throws RateLimitException
     */
    public function deleteDatabase(string $serverId, string $databaseId): array
    {
        try {
            return $this->request('DELETE', "/api/client/servers/{$serverId}/databases/{$databaseId}");
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();

            if ($statusCode === 401) {
                throw AuthenticationException::invalidCredentials();
            }

            if ($statusCode === 403) {
                throw PermissionException::missingPermission('delete_database');
            }

            if ($statusCode === 404) {
                throw ResourceNotFoundException::forResource('database', $databaseId);
            }

            if ($statusCode === 429) {
                $retryAfter = (int) $response->getHeaderLine('Retry-After');
                throw RateLimitException::withRetryAfter($retryAfter);
            }

            throw $e;
        }
    }
} 