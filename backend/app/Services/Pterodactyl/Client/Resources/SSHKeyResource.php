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
use MythicalDash\Services\Pterodactyl\Exceptions\RateLimitException;
use MythicalDash\Services\Pterodactyl\Exceptions\PermissionException;
use MythicalDash\Services\Pterodactyl\Exceptions\ValidationException;
use MythicalDash\Services\Pterodactyl\Exceptions\AuthenticationException;
use MythicalDash\Services\Pterodactyl\Exceptions\ResourceNotFoundException;

class SSHKeyResource extends PterodactylClient
{
    /**
     * List all SSH keys.
     *
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws RateLimitException
     */
    public function listKeys(): array
    {
        try {
            return $this->request('GET', '/api/client/account/ssh-keys');
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();

            if ($statusCode === 401) {
                throw AuthenticationException::invalidCredentials();
            }

            if ($statusCode === 403) {
                throw PermissionException::missingPermission('view_ssh_keys');
            }

            if ($statusCode === 429) {
                $retryAfter = (int) $response->getHeaderLine('Retry-After');
                throw RateLimitException::withRetryAfter($retryAfter);
            }

            throw $e;
        }
    }

    /**
     * Get a specific SSH key.
     *
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws ResourceNotFoundException
     * @throws RateLimitException
     */
    public function getKey(string $keyId): array
    {
        try {
            return $this->request('GET', "/api/client/account/ssh-keys/{$keyId}");
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();

            if ($statusCode === 401) {
                throw AuthenticationException::invalidCredentials();
            }

            if ($statusCode === 403) {
                throw PermissionException::missingPermission('view_ssh_keys');
            }

            if ($statusCode === 404) {
                throw ResourceNotFoundException::forResource('SSH key', $keyId);
            }

            if ($statusCode === 429) {
                $retryAfter = (int) $response->getHeaderLine('Retry-After');
                throw RateLimitException::withRetryAfter($retryAfter);
            }

            throw $e;
        }
    }

    /**
     * Create a new SSH key.
     *
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws ValidationException
     * @throws RateLimitException
     */
    public function createKey(string $name, string $publicKey): array
    {
        try {
            return $this->request('POST', '/api/client/account/ssh-keys', [
                'json' => [
                    'name' => $name,
                    'public_key' => $publicKey,
                ],
            ]);
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();

            if ($statusCode === 401) {
                throw AuthenticationException::invalidCredentials();
            }

            if ($statusCode === 403) {
                throw PermissionException::missingPermission('create_ssh_key');
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
     * Delete an SSH key.
     *
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws ResourceNotFoundException
     * @throws RateLimitException
     */
    public function deleteKey(string $keyId): array
    {
        try {
            return $this->request('DELETE', "/api/client/account/ssh-keys/{$keyId}");
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();

            if ($statusCode === 401) {
                throw AuthenticationException::invalidCredentials();
            }

            if ($statusCode === 403) {
                throw PermissionException::missingPermission('delete_ssh_key');
            }

            if ($statusCode === 404) {
                throw ResourceNotFoundException::forResource('SSH key', $keyId);
            }

            if ($statusCode === 429) {
                $retryAfter = (int) $response->getHeaderLine('Retry-After');
                throw RateLimitException::withRetryAfter($retryAfter);
            }

            throw $e;
        }
    }
}
