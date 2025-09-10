<?php

/*
 * This file is part of MythicalDash.
 *
 * MIT License
 *
 * Copyright (c) 2020-2025 MythicalSystems
 * Copyright (c) 2020-2025 Cassian Gherman (NaysKutzu)
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 * Please rather than modifying the dashboard code try to report the thing you wish on our github or write a plugin
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
    public function listUsers(int $page = 1, int $perPage = 50, array $includes = ['servers']): array
    {
        try {
            $query = [
                'page' => $page,
                'per_page' => $perPage,
            ];

            if (!empty($includes)) {
                $query['include'] = implode(',', $includes);
            }

            return $this->request('GET', '/api/application/users', [
                'query' => $query,
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
            return$this->request('GET', "/api/application/users/{$userId}");
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
            ]) ?? [];
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
            ]) ?? [];
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
    public function deleteUser(int $userId): void
    {
        try {
            $this->request('DELETE', "/api/application/users/{$userId}");
        } catch (ClientException $e) {
            if ($e->getResponse()->getStatusCode() === 404) {
                throw ResourceNotFoundException::forResource('user', (string) $userId);
            }
            throw new PterodactylException('Failed to delete user: ' . $e->getMessage());
        }
    }

    /**
     * Find a user by email.
     *
     * @param string $email Email address to search for
     *
     * @throws PterodactylException
     *
     * @return array User data
     */
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

    /**
     * Find a user by username.
     *
     * @param string $username Username to search for
     *
     * @throws PterodactylException
     *
     * @return array User data
     */
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

    /**
     * Find a user by uuid.
     *
     * @param string $uuid UUID to search for
     *
     * @throws PterodactylException
     *
     * @return array User data
     */
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

    /**
     * Find a user by external id.
     *
     * @param string $externalId External ID to search for
     *
     * @throws PterodactylException
     *
     * @return array User data
     */
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
     * Check if a user exists.
     *
     * @param int $userId The ID of the user to check
     *
     * @return bool True if the user exists, false otherwise
     */
    public function doesUserExist(int $userId): bool
    {
        try {
            $this->getUser((int) $userId);

            return true;
        } catch (ResourceNotFoundException $e) {
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }
}
