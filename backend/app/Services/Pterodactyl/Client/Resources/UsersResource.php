<?php

namespace MythicalDash\Services\Pterodactyl\Client\Resources;

use GuzzleHttp\Exception\ClientException;
use MythicalDash\Services\Pterodactyl\Client\PterodactylClient;
use MythicalDash\Services\Pterodactyl\Exceptions\AuthenticationException;
use MythicalDash\Services\Pterodactyl\Exceptions\PermissionException;
use MythicalDash\Services\Pterodactyl\Exceptions\RateLimitException;
use MythicalDash\Services\Pterodactyl\Exceptions\ResourceNotFoundException;
use MythicalDash\Services\Pterodactyl\Exceptions\ValidationException;

class UsersResource extends PterodactylClient
{
    /**
     * List subusers
     *
     * @param string $serverId
     * @return array
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws ResourceNotFoundException
     * @throws RateLimitException
     */
    public function listSubusers(string $serverId): array
    {
        try {
            return $this->request('GET', "/api/client/servers/{$serverId}/users");
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();

            if ($statusCode === 401) {
                throw AuthenticationException::invalidCredentials();
            }

            if ($statusCode === 403) {
                throw PermissionException::missingPermission('view_subusers');
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
     * Get specific subuser
     *
     * @param string $serverId
     * @param string $userId
     * @return array
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws ResourceNotFoundException
     * @throws RateLimitException
     */
    public function getSubuser(string $serverId, string $userId): array
    {
        try {
            return $this->request('GET', "/api/client/servers/{$serverId}/users/{$userId}");
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();

            if ($statusCode === 401) {
                throw AuthenticationException::invalidCredentials();
            }

            if ($statusCode === 403) {
                throw PermissionException::missingPermission('view_subusers');
            }

            if ($statusCode === 404) {
                throw ResourceNotFoundException::forResource('subuser', $userId);
            }

            if ($statusCode === 429) {
                $retryAfter = (int) $response->getHeaderLine('Retry-After');
                throw RateLimitException::withRetryAfter($retryAfter);
            }

            throw $e;
        }
    }

    /**
     * Create subuser
     *
     * @param string $serverId
     * @param string $email
     * @param array $permissions
     * @return array
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws ResourceNotFoundException
     * @throws ValidationException
     * @throws RateLimitException
     */
    public function createSubuser(string $serverId, string $email, array $permissions): array
    {
        try {
            return $this->request('POST', "/api/client/servers/{$serverId}/users", [
                'json' => [
                    'email' => $email,
                    'permissions' => $permissions,
                ],
            ]);
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();

            if ($statusCode === 401) {
                throw AuthenticationException::invalidCredentials();
            }

            if ($statusCode === 403) {
                throw PermissionException::missingPermission('create_subuser');
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
     * Update subuser
     *
     * @param string $serverId
     * @param string $userId
     * @param array $permissions
     * @return array
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws ResourceNotFoundException
     * @throws ValidationException
     * @throws RateLimitException
     */
    public function updateSubuser(string $serverId, string $userId, array $permissions): array
    {
        try {
            return $this->request('PATCH', "/api/client/servers/{$serverId}/users/{$userId}", [
                'json' => [
                    'permissions' => $permissions,
                ],
            ]);
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();

            if ($statusCode === 401) {
                throw AuthenticationException::invalidCredentials();
            }

            if ($statusCode === 403) {
                throw PermissionException::missingPermission('update_subuser');
            }

            if ($statusCode === 404) {
                throw ResourceNotFoundException::forResource('subuser', $userId);
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
     * Delete subuser
     *
     * @param string $serverId
     * @param string $userId
     * @return array
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws ResourceNotFoundException
     * @throws RateLimitException
     */
    public function deleteSubuser(string $serverId, string $userId): array
    {
        try {
            return $this->request('DELETE', "/api/client/servers/{$serverId}/users/{$userId}");
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();

            if ($statusCode === 401) {
                throw AuthenticationException::invalidCredentials();
            }

            if ($statusCode === 403) {
                throw PermissionException::missingPermission('delete_subuser');
            }

            if ($statusCode === 404) {
                throw ResourceNotFoundException::forResource('subuser', $userId);
            }

            if ($statusCode === 429) {
                $retryAfter = (int) $response->getHeaderLine('Retry-After');
                throw RateLimitException::withRetryAfter($retryAfter);
            }

            throw $e;
        }
    }

    /**
     * Get permissions
     *
     * @return array
     * @throws AuthenticationException
     * @throws RateLimitException
     */
    public function getPermissions(): array
    {
        try {
            return $this->request('GET', '/api/client/permissions');
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();

            if ($statusCode === 401) {
                throw AuthenticationException::invalidCredentials();
            }

            if ($statusCode === 429) {
                $retryAfter = (int) $response->getHeaderLine('Retry-After');
                throw RateLimitException::withRetryAfter($retryAfter);
            }

            throw $e;
        }
    }

    /**
     * Update email
     *
     * @param string $email
     * @param string $password
     * @return array
     * @throws AuthenticationException
     * @throws ValidationException
     * @throws RateLimitException
     */
    public function updateEmail(string $email, string $password): array
    {
        try {
            return $this->request('PUT', '/api/client/account/email', [
                'json' => [
                    'email' => $email,
                    'password' => $password,
                ],
            ]);
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();

            if ($statusCode === 401) {
                throw AuthenticationException::invalidCredentials();
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
     * Update password
     *
     * @param string $currentPassword
     * @param string $newPassword
     * @return array
     * @throws AuthenticationException
     * @throws ValidationException
     * @throws RateLimitException
     */
    public function updatePassword(string $currentPassword, string $newPassword): array
    {
        try {
            return $this->request('PUT', '/api/client/account/password', [
                'json' => [
                    'current_password' => $currentPassword,
                    'password' => $newPassword,
                    'password_confirmation' => $newPassword,
                ],
            ]);
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();

            if ($statusCode === 401) {
                throw AuthenticationException::invalidCredentials();
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
     * Update 2FA
     *
     * @param string $code
     * @param string $password
     * @return array
     * @throws AuthenticationException
     * @throws ValidationException
     * @throws RateLimitException
     */
    public function update2FA(string $code, string $password): array
    {
        try {
            return $this->request('POST', '/api/client/account/two-factor', [
                'json' => [
                    'code' => $code,
                    'password' => $password,
                ],
            ]);
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();

            if ($statusCode === 401) {
                throw AuthenticationException::invalidCredentials();
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
     * Disable 2FA
     *
     * @param string $password
     * @return array
     * @throws AuthenticationException
     * @throws ValidationException
     * @throws RateLimitException
     */
    public function disable2FA(string $password): array
    {
        try {
            return $this->request('DELETE', '/api/client/account/two-factor', [
                'json' => [
                    'password' => $password,
                ],
            ]);
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();

            if ($statusCode === 401) {
                throw AuthenticationException::invalidCredentials();
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