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
use MythicalDash\Services\Pterodactyl\Exceptions\ValidationException;
use MythicalDash\Services\Pterodactyl\Exceptions\AuthenticationException;

class AccountResource extends PterodactylClient
{
    /**
     * Get account details.
     *
     * @throws AuthenticationException
     * @throws RateLimitException
     */
    public function getDetails(): array
    {
        try {
            return $this->request('GET', '/api/client/account');
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
     * Update email address.
     *
     * @param string $email New email address
     * @param string $password Current password
     *
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
     * Update password.
     *
     * @param string $currentPassword Current password
     * @param string $newPassword New password
     *
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
     * Enable 2FA.
     *
     * @throws AuthenticationException
     * @throws RateLimitException
     */
    public function enable2FA(): array
    {
        try {
            return $this->request('POST', '/api/client/account/two-factor');
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
     * Disable 2FA.
     *
     * @param string $code 2FA code
     * @param string $password Current password
     *
     * @throws AuthenticationException
     * @throws ValidationException
     * @throws RateLimitException
     */
    public function disable2FA(string $code, string $password): array
    {
        try {
            return $this->request('DELETE', '/api/client/account/two-factor', [
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
     * Get API keys.
     *
     * @throws AuthenticationException
     * @throws RateLimitException
     */
    public function getApiKeys(): array
    {
        try {
            return $this->request('GET', '/api/client/account/api-keys');
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
     * Create API key.
     *
     * @param string $description Key description
     * @param array $allowedIps Allowed IP addresses
     *
     * @throws AuthenticationException
     * @throws ValidationException
     * @throws RateLimitException
     */
    public function createApiKey(string $description, array $allowedIps = []): array
    {
        try {
            return $this->request('POST', '/api/client/account/api-keys', [
                'json' => [
                    'description' => $description,
                    'allowed_ips' => $allowedIps,
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
     * Delete API key.
     *
     * @param string $keyId API key identifier
     *
     * @throws AuthenticationException
     * @throws RateLimitException
     */
    public function deleteApiKey(string $keyId): array
    {
        try {
            return $this->request('DELETE', "/api/client/account/api-keys/{$keyId}");
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
}
