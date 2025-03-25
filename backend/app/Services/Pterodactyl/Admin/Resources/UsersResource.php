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

namespace MythicalDash\Services\Pterodactyl\Admin\Resources;

use GuzzleHttp\Exception\ClientException;
use MythicalDash\Services\Pterodactyl\Admin\PterodactylAdmin;
use MythicalDash\Services\Pterodactyl\Exceptions\ValidationException;
use MythicalDash\Services\Pterodactyl\Exceptions\PterodactylException;
use MythicalDash\Services\Pterodactyl\Exceptions\ResourceNotFoundException;

class UsersResource extends PterodactylAdmin
{
    /**
     * List all users.
     *
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
     * Get a specific user.
     *
     * @throws ResourceNotFoundException
     * @throws PterodactylException
     */
    public function getUser(int $userId): array
    {
        try {
            return $this->request('GET', "/api/application/users/{$userId}");
        } catch (ClientException $e) {
            if ($e->getResponse()->getStatusCode() === 404) {
                throw ResourceNotFoundException::forResource('user', (string) $userId);
            }
            throw new PterodactylException('Failed to retrieve user: ' . $e->getMessage());
        }
    }

    public function getUserWithServers(int $userId): array
    {
        try {
            return $this->request('GET', "/api/application/users/{$userId}", [
                'query' => [
                    'include' => 'servers',
                ],
            ]);
        } catch (ClientException $e) {
            if ($e->getResponse()->getStatusCode() === 404) {
                throw ResourceNotFoundException::forResource('user', (string) $userId);
            }
            throw new PterodactylException('Failed to retrieve user: ' . $e->getMessage());
        }
    }

    /**
     * Create a new user.
     *
     * @throws ValidationException
     * @throws PterodactylException
     */
    public function createUser(
        string $email,
        string $username,
        string $firstName,
        string $lastName,
        string $password,
    ): array {
        try {
            return $this->request('POST', '/api/application/users', [
                'json' => [
                    'email' => $email,
                    'username' => $username,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'password' => $password,
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
     * Update a user.
     *
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
                throw ResourceNotFoundException::forResource('user', (string) $userId);
            }
            if ($e->getResponse()->getStatusCode() === 422) {
                $errors = json_decode($e->getResponse()->getBody()->getContents(), true);
                throw ValidationException::withErrors($errors['errors'] ?? []);
            }
            throw new PterodactylException('Failed to update user: ' . $e->getMessage());
        }
    }

    /**
     * Delete a user.
     *
     * @throws ResourceNotFoundException
     * @throws PterodactylException
     */
    public function deleteUser(int $userId): array
    {
        try {
            return $this->request('DELETE', "/api/application/users/{$userId}");
        } catch (ClientException $e) {
            if ($e->getResponse()->getStatusCode() === 404) {
                throw ResourceNotFoundException::forResource('user', (string) $userId);
            }
            throw new PterodactylException('Failed to delete user: ' . $e->getMessage());
        }
    }

    public function findUserByEmail(string $email): array
    {
        try {
            $response = $this->request('GET', '/api/application/users', [
                'query' => [
                    'filter[email]' => $email,
                ],
            ]);

            if (empty($response['data'])) {
                throw new ResourceNotFoundException('User not found with email: ' . $email);
            }

            // Return the first (and should be only) user with this email
            return $response['data'][0];
        } catch (ClientException $e) {
            throw new PterodactylException('Failed to find user by email: ' . $e->getMessage());
        }
    }

    public function findUserByUsername(string $username): array
    {
        try {
            $response = $this->request('GET', '/api/application/users', [
                'query' => [
                    'filter[username]' => $username,
                ],
            ]);

            if (empty($response['data'])) {
                throw new ResourceNotFoundException('User not found with username: ' . $username);
            }

            return $response['data'][0];
        } catch (ClientException $e) {
            throw new PterodactylException('Failed to find user by username: ' . $e->getMessage());
        }
    }

    public function findUserByUuid(string $uuid): array
    {
        try {
            $response = $this->request('GET', '/api/application/users', [
                'query' => [
                    'filter[uuid]' => $uuid,
                ],
            ]);

            if (empty($response['data'])) {
                throw new ResourceNotFoundException('User not found with uuid: ' . $uuid);
            }

            return $response['data'][0];
        } catch (ClientException $e) {
            throw new PterodactylException('Failed to find user by uuid: ' . $e->getMessage());
        }
    }

    public function findUserByExternalId(string $externalId): array
    {
        try {
            $response = $this->request('GET', '/api/application/users', [
                'query' => [
                    'filter[external_id]' => $externalId,
                ],
            ]);

            if (empty($response['data'])) {
                throw new ResourceNotFoundException('User not found with external id: ' . $externalId);
            }

            return $response['data'][0];
        } catch (ClientException $e) {
            throw new PterodactylException('Failed to find user by external id: ' . $e->getMessage());
        }
    }

    /**
     * List all users with their servers.
     *
     * @param int $page Page number
     * @param int $perPage Items per page
     * @param array $filters Optional filters (email, uuid, username, external_id)
     * @param string $sortBy Sort by field (id or uuid)
     *
     * @throws PterodactylException
     */
    public function listUsersWithServers(
        int $page = 1,
        int $perPage = 50,
        array $filters = [],
        string $sortBy = 'id',
    ): array {
        try {
            $query = [
                'page' => $page,
                'per_page' => $perPage,
                'include' => 'servers',
            ];

            // Add any provided filters
            foreach ($filters as $key => $value) {
                if (in_array($key, ['email', 'uuid', 'username', 'external_id'])) {
                    $query["filter[$key]"] = $value;
                }
            }

            // Add sorting if valid
            if (in_array($sortBy, ['id', 'uuid'])) {
                $query['sort'] = $sortBy;
            }

            return $this->request('GET', '/api/application/users', [
                'query' => $query,
            ]);
        } catch (ClientException $e) {
            throw new PterodactylException('Failed to retrieve users with servers: ' . $e->getMessage());
        }
    }
}
