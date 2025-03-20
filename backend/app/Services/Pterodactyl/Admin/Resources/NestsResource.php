<?php

namespace MythicalDash\Services\Pterodactyl\Admin\Resources;

use GuzzleHttp\Exception\ClientException;
use MythicalDash\Services\Pterodactyl\Admin\PterodactylAdmin;
use MythicalDash\Services\Pterodactyl\Exceptions\AuthenticationException;
use MythicalDash\Services\Pterodactyl\Exceptions\PermissionException;
use MythicalDash\Services\Pterodactyl\Exceptions\RateLimitException;
use MythicalDash\Services\Pterodactyl\Exceptions\ResourceNotFoundException;
use MythicalDash\Services\Pterodactyl\Exceptions\ValidationException;

class NestsResource extends PterodactylAdmin
{
    /**
     * List all nests
     *
     * @param int $page
     * @param int $perPage
     * @return array
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws RateLimitException
     */
    public function listNests(int $page = 1, int $perPage = 50): array
    {
        try {
            return $this->request('GET', '/api/application/nests', [
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
     * Get a specific nest
     *
     * @param int $nestId
     * @return array
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws ResourceNotFoundException
     * @throws RateLimitException
     */
    public function getNest(int $nestId): array
    {
        try {
            return $this->request('GET', "/api/application/nests/{$nestId}");
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
                throw ResourceNotFoundException::forResource('nest', (string) $nestId);
            }

            if ($statusCode === 429) {
                $retryAfter = (int) $response->getHeaderLine('Retry-After');
                throw RateLimitException::withRetryAfter($retryAfter);
            }

            throw $e;
        }
    }

    /**
     * List eggs in a nest
     *
     * @param int $nestId
     * @param int $page
     * @param int $perPage
     * @return array
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws ResourceNotFoundException
     * @throws RateLimitException
     */
    public function listEggs(int $nestId, int $page = 1, int $perPage = 50): array
    {
        try {
            return $this->request('GET', "/api/application/nests/{$nestId}/eggs", [
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
                throw ResourceNotFoundException::forResource('nest', (string) $nestId);
            }

            if ($statusCode === 429) {
                $retryAfter = (int) $response->getHeaderLine('Retry-After');
                throw RateLimitException::withRetryAfter($retryAfter);
            }

            throw $e;
        }
    }

    /**
     * Get a specific egg
     *
     * @param int $nestId
     * @param int $eggId
     * @return array
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws ResourceNotFoundException
     * @throws RateLimitException
     */
    public function getEgg(int $nestId, int $eggId): array
    {
        try {
            return $this->request('GET', "/api/application/nests/{$nestId}/eggs/{$eggId}");
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
                throw ResourceNotFoundException::forResource('egg', (string) $eggId);
            }

            if ($statusCode === 429) {
                $retryAfter = (int) $response->getHeaderLine('Retry-After');
                throw RateLimitException::withRetryAfter($retryAfter);
            }

            throw $e;
        }
    }

    /**
     * Create a new nest
     *
     * @param string $name
     * @param string $description
     * @return array
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws ValidationException
     * @throws RateLimitException
     */
    public function createNest(string $name, string $description): array
    {
        try {
            return $this->request('POST', '/api/application/nests', [
                'json' => [
                    'name' => $name,
                    'description' => $description,
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
     * Update a nest
     *
     * @param int $nestId
     * @param array $data
     * @return array
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws ResourceNotFoundException
     * @throws ValidationException
     * @throws RateLimitException
     */
    public function updateNest(int $nestId, array $data): array
    {
        try {
            return $this->request('PATCH', "/api/application/nests/{$nestId}", [
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
                throw ResourceNotFoundException::forResource('nest', (string) $nestId);
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
     * Delete a nest
     *
     * @param int $nestId
     * @return array
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws ResourceNotFoundException
     * @throws RateLimitException
     */
    public function deleteNest(int $nestId): array
    {
        try {
            return $this->request('DELETE', "/api/application/nests/{$nestId}");
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
                throw ResourceNotFoundException::forResource('nest', (string) $nestId);
            }

            if ($statusCode === 429) {
                $retryAfter = (int) $response->getHeaderLine('Retry-After');
                throw RateLimitException::withRetryAfter($retryAfter);
            }

            throw $e;
        }
    }

    /**
     * Create a new egg
     *
     * @param int $nestId
     * @param array $data
     * @return array
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws ResourceNotFoundException
     * @throws ValidationException
     * @throws RateLimitException
     */
    public function createEgg(int $nestId, array $data): array
    {
        try {
            return $this->request('POST', "/api/application/nests/{$nestId}/eggs", [
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
                throw ResourceNotFoundException::forResource('nest', (string) $nestId);
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
     * Update an egg
     *
     * @param int $nestId
     * @param int $eggId
     * @param array $data
     * @return array
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws ResourceNotFoundException
     * @throws ValidationException
     * @throws RateLimitException
     */
    public function updateEgg(int $nestId, int $eggId, array $data): array
    {
        try {
            return $this->request('PATCH', "/api/application/nests/{$nestId}/eggs/{$eggId}", [
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
                throw ResourceNotFoundException::forResource('egg', (string) $eggId);
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
     * Delete an egg
     *
     * @param int $nestId
     * @param int $eggId
     * @return array
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws ResourceNotFoundException
     * @throws RateLimitException
     */
    public function deleteEgg(int $nestId, int $eggId): array
    {
        try {
            return $this->request('DELETE', "/api/application/nests/{$nestId}/eggs/{$eggId}");
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
                throw ResourceNotFoundException::forResource('egg', (string) $eggId);
            }

            if ($statusCode === 429) {
                $retryAfter = (int) $response->getHeaderLine('Retry-After');
                throw RateLimitException::withRetryAfter($retryAfter);
            }

            throw $e;
        }
    }
} 