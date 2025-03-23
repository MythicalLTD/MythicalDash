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

namespace MythicalDash\Services\Pterodactyl\Client\Resources;

use GuzzleHttp\Exception\ClientException;
use MythicalDash\Services\Pterodactyl\Client\PterodactylClient;
use MythicalDash\Services\Pterodactyl\Exceptions\ServerException;
use MythicalDash\Services\Pterodactyl\Exceptions\RateLimitException;
use MythicalDash\Services\Pterodactyl\Exceptions\PermissionException;
use MythicalDash\Services\Pterodactyl\Exceptions\ValidationException;
use MythicalDash\Services\Pterodactyl\Exceptions\AuthenticationException;
use MythicalDash\Services\Pterodactyl\Exceptions\ResourceNotFoundException;

class ServerResource extends PterodactylClient
{
    /**
     * Get list of servers.
     *
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws RateLimitException
     */
    public function listServers(): array
    {
        try {
            return $this->request('GET', '/api/client');
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();

            if ($statusCode === 401) {
                throw AuthenticationException::invalidCredentials();
            }

            if ($statusCode === 403) {
                throw PermissionException::missingPermission('view_server');
            }

            if ($statusCode === 429) {
                $retryAfter = (int) $response->getHeaderLine('Retry-After');
                throw RateLimitException::withRetryAfter($retryAfter);
            }

            throw $e;
        }
    }

    /**
     * Get server details.
     *
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws ResourceNotFoundException
     * @throws RateLimitException
     */
    public function getServer(string $serverId): array
    {
        try {
            return $this->request('GET', "/api/client/servers/{$serverId}");
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();

            if ($statusCode === 401) {
                throw AuthenticationException::invalidCredentials();
            }

            if ($statusCode === 403) {
                throw PermissionException::missingPermission('view_server');
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
     * Get server utilization.
     *
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws ResourceNotFoundException
     * @throws ServerException
     * @throws RateLimitException
     */
    public function getServerUtilization(string $serverId): array
    {
        try {
            return $this->request('GET', "/api/client/servers/{$serverId}/resources");
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();

            if ($statusCode === 401) {
                throw AuthenticationException::invalidCredentials();
            }

            if ($statusCode === 403) {
                throw PermissionException::missingPermission('view_server_utilization');
            }

            if ($statusCode === 404) {
                throw ResourceNotFoundException::forResource('server', $serverId);
            }

            if ($statusCode === 409) {
                throw ServerException::serverOffline();
            }

            if ($statusCode === 429) {
                $retryAfter = (int) $response->getHeaderLine('Retry-After');
                throw RateLimitException::withRetryAfter($retryAfter);
            }

            throw $e;
        }
    }

    /**
     * Send power signal to server.
     *
     * @param string $signal start|stop|restart|kill
     *
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws ResourceNotFoundException
     * @throws ServerException
     * @throws ValidationException
     * @throws RateLimitException
     */
    public function sendPowerSignal(string $serverId, string $signal): array
    {
        try {
            return $this->request('POST', "/api/client/servers/{$serverId}/power", [
                'json' => ['signal' => $signal],
            ]);
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();

            if ($statusCode === 401) {
                throw AuthenticationException::invalidCredentials();
            }

            if ($statusCode === 403) {
                throw PermissionException::missingPermission('send_power_signals');
            }

            if ($statusCode === 404) {
                throw ResourceNotFoundException::forResource('server', $serverId);
            }

            if ($statusCode === 409) {
                throw ServerException::powerActionInProgress();
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
     * Send command to server.
     *
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws ResourceNotFoundException
     * @throws ServerException
     * @throws ValidationException
     * @throws RateLimitException
     */
    public function sendCommand(string $serverId, string $command): array
    {
        try {
            return $this->request('POST', "/api/client/servers/{$serverId}/command", [
                'json' => ['command' => $command],
            ]);
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();

            if ($statusCode === 401) {
                throw AuthenticationException::invalidCredentials();
            }

            if ($statusCode === 403) {
                throw PermissionException::missingPermission('send_commands');
            }

            if ($statusCode === 404) {
                throw ResourceNotFoundException::forResource('server', $serverId);
            }

            if ($statusCode === 409) {
                throw ServerException::serverOffline();
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
     * Get server backup list.
     *
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws ResourceNotFoundException
     * @throws RateLimitException
     */
    public function listBackups(string $serverId): array
    {
        try {
            return $this->request('GET', "/api/client/servers/{$serverId}/backups");
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();

            if ($statusCode === 401) {
                throw AuthenticationException::invalidCredentials();
            }

            if ($statusCode === 403) {
                throw PermissionException::missingPermission('view_backups');
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
     * Create server backup.
     *
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws ResourceNotFoundException
     * @throws ServerException
     * @throws ValidationException
     * @throws RateLimitException
     */
    public function createBackup(string $serverId, string $name, bool $isLocked = false): array
    {
        try {
            return $this->request('POST', "/api/client/servers/{$serverId}/backups", [
                'json' => [
                    'name' => $name,
                    'is_locked' => $isLocked,
                ],
            ]);
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();

            if ($statusCode === 401) {
                throw AuthenticationException::invalidCredentials();
            }

            if ($statusCode === 403) {
                throw PermissionException::missingPermission('create_backup');
            }

            if ($statusCode === 404) {
                throw ResourceNotFoundException::forResource('server', $serverId);
            }

            if ($statusCode === 409) {
                throw ServerException::backupInProgress();
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
     * Delete server backup.
     *
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws ResourceNotFoundException
     * @throws RateLimitException
     */
    public function deleteBackup(string $serverId, string $backupId): array
    {
        try {
            return $this->request('DELETE', "/api/client/servers/{$serverId}/backups/{$backupId}");
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();

            if ($statusCode === 401) {
                throw AuthenticationException::invalidCredentials();
            }

            if ($statusCode === 403) {
                throw PermissionException::missingPermission('delete_backup');
            }

            if ($statusCode === 404) {
                throw ResourceNotFoundException::forResource('backup', $backupId);
            }

            if ($statusCode === 429) {
                $retryAfter = (int) $response->getHeaderLine('Retry-After');
                throw RateLimitException::withRetryAfter($retryAfter);
            }

            throw $e;
        }
    }
}
