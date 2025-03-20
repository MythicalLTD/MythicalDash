<?php

namespace MythicalDash\Services\Pterodactyl\Admin\Resources;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use MythicalDash\Services\Pterodactyl\Admin\PterodactylAdmin;
use MythicalDash\Services\Pterodactyl\Exceptions\PterodactylException;
use MythicalDash\Services\Pterodactyl\Exceptions\ValidationException;
use MythicalDash\Services\Pterodactyl\Exceptions\ResourceNotFoundException;

class UsersResource extends PterodactylAdmin
{
    /**
     * List all users
     *
     * @param int $page
     * @param int $perPage
     * @return array
     * @throws PterodactylException
     */
    public function listUsers(int $page = 1, int $perPage = 50): array
    {
        try {
            return $this->request('GET', '/api/application/users', [
                'query' => [
                    'page' => $page,
                    'per_page' => $perPage,
                ],
            ]);
        } catch (ClientException $e) {
            throw new PterodactylException('Failed to retrieve users list: ' . $e->getMessage());
        }
    }

    /**
     * Get a specific user
     *
     * @param int $userId
     * @return array
     * @throws ResourceNotFoundException
     * @throws PterodactylException
     */
    public function getUser(int $userId): array
    {
        try {
            return $this->request('GET', "/api/application/users/{$userId}");
        } catch (ClientException $e) {
            if ($e->getResponse()->getStatusCode() === 404) {
                throw ResourceNotFoundException::forResource('user', (string)$userId);
            }
            throw new PterodactylException('Failed to retrieve user: ' . $e->getMessage());
        }
    }

    /**
     * Create a new user
     *
     * @param string $email
     * @param string $username
     * @param string $firstName
     * @param string $lastName
     * @param string $password
     * @param bool $isAdmin
     * @return array
     * @throws ValidationException
     * @throws PterodactylException
     */
    public function createUser(
        string $email,
        string $username,
        string $firstName,
        string $lastName,
        string $password,
        bool $isAdmin = false
    ): array {
        try {
            return $this->request('POST', '/api/application/users', [
                'json' => [
                    'email' => $email,
                    'username' => $username,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'password' => $password,
                    'root_admin' => $isAdmin,
                ],
            ]);
        } catch (ClientException $e) {
            if ($e->getResponse()->getStatusCode() === 422) {
                $errors = json_decode($e->getResponse()->getBody()->getContents(), true);
                throw ValidationException::withErrors($errors['errors'] ?? []);
            }
            throw new PterodactylException('Failed to create user: ' . $e->getMessage());
        }
    }

    /**
     * Update a user
     *
     * @param int $userId
     * @param array $data
     * @return array
     * @throws ValidationException
     * @throws ResourceNotFoundException
     * @throws PterodactylException
     */
    public function updateUser(int $userId, array $data): array
    {
        try {
            return $this->request('PATCH', "/api/application/users/{$userId}", [
                'json' => $data,
            ]);
        } catch (ClientException $e) {
            if ($e->getResponse()->getStatusCode() === 404) {
                throw ResourceNotFoundException::forResource('user', (string)$userId);
            }
            if ($e->getResponse()->getStatusCode() === 422) {
                $errors = json_decode($e->getResponse()->getBody()->getContents(), true);
                throw ValidationException::withErrors($errors['errors'] ?? []);
            }
            throw new PterodactylException('Failed to update user: ' . $e->getMessage());
        }
    }

    /**
     * Delete a user
     *
     * @param int $userId
     * @return array
     * @throws ResourceNotFoundException
     * @throws PterodactylException
     */
    public function deleteUser(int $userId): array
    {
        try {
            return $this->request('DELETE', "/api/application/users/{$userId}");
        } catch (ClientException $e) {
            if ($e->getResponse()->getStatusCode() === 404) {
                throw ResourceNotFoundException::forResource('user', (string)$userId);
            }
            throw new PterodactylException('Failed to delete user: ' . $e->getMessage());
        }
    }

    /**
     * List user's API keys
     *
     * @param int $userId
     * @return array
     * @throws ResourceNotFoundException
     * @throws PterodactylException
     */
    public function listApiKeys(int $userId): array
    {
        try {
            return $this->request('GET', "/api/application/users/{$userId}/api-keys");
        } catch (ClientException $e) {
            if ($e->getResponse()->getStatusCode() === 404) {
                throw ResourceNotFoundException::forResource('user', (string)$userId);
            }
            throw new PterodactylException('Failed to retrieve API keys: ' . $e->getMessage());
        }
    }

    /**
     * Create API key for user
     *
     * @param int $userId
     * @param string $description
     * @param array $allowedIps
     * @return array
     * @throws ValidationException
     * @throws ResourceNotFoundException
     * @throws PterodactylException
     */
    public function createApiKey(int $userId, string $description, array $allowedIps = []): array
    {
        try {
            return $this->request('POST', "/api/application/users/{$userId}/api-keys", [
                'json' => [
                    'description' => $description,
                    'allowed_ips' => $allowedIps,
                ],
            ]);
        } catch (ClientException $e) {
            if ($e->getResponse()->getStatusCode() === 404) {
                throw ResourceNotFoundException::forResource('user', (string)$userId);
            }
            if ($e->getResponse()->getStatusCode() === 422) {
                $errors = json_decode($e->getResponse()->getBody()->getContents(), true);
                throw ValidationException::withErrors($errors['errors'] ?? []);
            }
            throw new PterodactylException('Failed to create API key: ' . $e->getMessage());
        }
    }

    /**
     * Delete API key
     *
     * @param int $userId
     * @param string $keyIdentifier
     * @return array
     * @throws ResourceNotFoundException
     * @throws PterodactylException
     */
    public function deleteApiKey(int $userId, string $keyIdentifier): array
    {
        try {
            return $this->request('DELETE', "/api/application/users/{$userId}/api-keys/{$keyIdentifier}");
        } catch (ClientException $e) {
            if ($e->getResponse()->getStatusCode() === 404) {
                throw ResourceNotFoundException::forResource('API key', $keyIdentifier);
            }
            throw new PterodactylException('Failed to delete API key: ' . $e->getMessage());
        }
    }
} 