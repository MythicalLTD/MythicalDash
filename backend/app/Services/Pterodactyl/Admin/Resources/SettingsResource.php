<?php

namespace MythicalDash\Services\Pterodactyl\Admin\Resources;

use GuzzleHttp\Exception\ClientException;
use MythicalDash\Services\Pterodactyl\Admin\PterodactylAdmin;
use MythicalDash\Services\Pterodactyl\Exceptions\AuthenticationException;
use MythicalDash\Services\Pterodactyl\Exceptions\PermissionException;
use MythicalDash\Services\Pterodactyl\Exceptions\RateLimitException;
use MythicalDash\Services\Pterodactyl\Exceptions\ValidationException;

class SettingsResource extends PterodactylAdmin
{
    /**
     * Get panel settings
     *
     * @return array
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws RateLimitException
     */
    public function getPanelSettings(): array
    {
        try {
            return $this->request('GET', '/api/application/settings');
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
     * Update panel settings
     *
     * @param array $settings
     * @return array
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws ValidationException
     * @throws RateLimitException
     */
    public function updatePanelSettings(array $settings): array
    {
        try {
            return $this->request('PATCH', '/api/application/settings', [
                'json' => $settings,
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
     * Get mail settings
     *
     * @return array
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws RateLimitException
     */
    public function getMailSettings(): array
    {
        try {
            return $this->request('GET', '/api/application/settings/mail');
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
     * Update mail settings
     *
     * @param array $settings
     * @return array
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws ValidationException
     * @throws RateLimitException
     */
    public function updateMailSettings(array $settings): array
    {
        try {
            return $this->request('PATCH', '/api/application/settings/mail', [
                'json' => $settings,
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
     * Test mail settings
     *
     * @param string $email
     * @return array
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws ValidationException
     * @throws RateLimitException
     */
    public function testMailSettings(string $email): array
    {
        try {
            return $this->request('POST', '/api/application/settings/mail/test', [
                'json' => [
                    'email' => $email,
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
     * Get advanced settings
     *
     * @return array
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws RateLimitException
     */
    public function getAdvancedSettings(): array
    {
        try {
            return $this->request('GET', '/api/application/settings/advanced');
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
     * Update advanced settings
     *
     * @param array $settings
     * @return array
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws ValidationException
     * @throws RateLimitException
     */
    public function updateAdvancedSettings(array $settings): array
    {
        try {
            return $this->request('PATCH', '/api/application/settings/advanced', [
                'json' => $settings,
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
} 