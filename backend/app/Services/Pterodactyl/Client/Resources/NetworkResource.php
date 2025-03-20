<?php

namespace MythicalDash\Services\Pterodactyl\Client\Resources;

use GuzzleHttp\Exception\ClientException;
use MythicalDash\Services\Pterodactyl\Client\PterodactylClient;
use MythicalDash\Services\Pterodactyl\Exceptions\AuthenticationException;
use MythicalDash\Services\Pterodactyl\Exceptions\PermissionException;
use MythicalDash\Services\Pterodactyl\Exceptions\RateLimitException;
use MythicalDash\Services\Pterodactyl\Exceptions\ResourceNotFoundException;
use MythicalDash\Services\Pterodactyl\Exceptions\ValidationException;

class NetworkResource extends PterodactylClient
{
    /**
     * List allocations for a server
     *
     * @param string $serverId
     * @return array
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws ResourceNotFoundException
     * @throws RateLimitException
     */
    public function listAllocations(string $serverId): array
    {
        try {
            return $this->request('GET', "/api/client/servers/{$serverId}/network/allocations");
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();

            if ($statusCode === 401) {
                throw AuthenticationException::invalidCredentials();
            }

            if ($statusCode === 403) {
                throw PermissionException::missingPermission('view_allocations');
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
     * Create new allocation
     *
     * @param string $serverId
     * @return array
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws ResourceNotFoundException
     * @throws ValidationException
     * @throws RateLimitException
     */
    public function createAllocation(string $serverId): array
    {
        try {
            return $this->request('POST', "/api/client/servers/{$serverId}/network/allocations");
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();

            if ($statusCode === 401) {
                throw AuthenticationException::invalidCredentials();
            }

            if ($statusCode === 403) {
                throw PermissionException::missingPermission('create_allocation');
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
     * Set allocation note
     *
     * @param string $serverId
     * @param string $allocationId
     * @param string $notes
     * @return array
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws ResourceNotFoundException
     * @throws ValidationException
     * @throws RateLimitException
     */
    public function setAllocationNote(string $serverId, string $allocationId, string $notes): array
    {
        try {
            return $this->request('POST', "/api/client/servers/{$serverId}/network/allocations/{$allocationId}", [
                'json' => ['notes' => $notes],
            ]);
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();

            if ($statusCode === 401) {
                throw AuthenticationException::invalidCredentials();
            }

            if ($statusCode === 403) {
                throw PermissionException::missingPermission('edit_allocation');
            }

            if ($statusCode === 404) {
                throw ResourceNotFoundException::forResource('allocation', $allocationId);
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
     * Set primary allocation
     *
     * @param string $serverId
     * @param string $allocationId
     * @return array
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws ResourceNotFoundException
     * @throws RateLimitException
     */
    public function setPrimaryAllocation(string $serverId, string $allocationId): array
    {
        try {
            return $this->request('POST', "/api/client/servers/{$serverId}/network/allocations/{$allocationId}/primary");
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();

            if ($statusCode === 401) {
                throw AuthenticationException::invalidCredentials();
            }

            if ($statusCode === 403) {
                throw PermissionException::missingPermission('edit_allocation');
            }

            if ($statusCode === 404) {
                throw ResourceNotFoundException::forResource('allocation', $allocationId);
            }

            if ($statusCode === 429) {
                $retryAfter = (int) $response->getHeaderLine('Retry-After');
                throw RateLimitException::withRetryAfter($retryAfter);
            }

            throw $e;
        }
    }

    /**
     * Delete allocation
     *
     * @param string $serverId
     * @param string $allocationId
     * @return array
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws ResourceNotFoundException
     * @throws RateLimitException
     */
    public function deleteAllocation(string $serverId, string $allocationId): array
    {
        try {
            return $this->request('DELETE', "/api/client/servers/{$serverId}/network/allocations/{$allocationId}");
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();

            if ($statusCode === 401) {
                throw AuthenticationException::invalidCredentials();
            }

            if ($statusCode === 403) {
                throw PermissionException::missingPermission('delete_allocation');
            }

            if ($statusCode === 404) {
                throw ResourceNotFoundException::forResource('allocation', $allocationId);
            }

            if ($statusCode === 429) {
                $retryAfter = (int) $response->getHeaderLine('Retry-After');
                throw RateLimitException::withRetryAfter($retryAfter);
            }

            throw $e;
        }
    }
} 