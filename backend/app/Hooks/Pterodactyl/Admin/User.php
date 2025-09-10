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

namespace MythicalDash\Hooks\Pterodactyl\Admin;

use MythicalDash\App;
use MythicalDash\Config\ConfigInterface;
use MythicalDash\Services\Pterodactyl\Admin\Resources\UsersResource;
use MythicalDash\Services\Pterodactyl\Exceptions\ValidationException;
use MythicalDash\Services\Pterodactyl\Exceptions\PterodactylException;
use MythicalDash\Services\Pterodactyl\Exceptions\ResourceNotFoundException;

class User extends UsersResource
{
    /**
     * Perform a login action on the Pterodactyl panel.
     *
     * @param string $pterodactylUserId The UUID of the user to login
     * @param string $email The email of the user to login
     * @param string $username The username of the user to login
     * @param string $first_name The first name of the user to login
     * @param string $last_name The last name of the user to login
     * @param string $password The password of the user to login
     */
    public static function performLogin(string $pterodactylUserId, string $email, string $username, string $first_name, string $last_name, string $password): void
    {
        $appInstance = App::getInstance(true);

        $config = $appInstance->getConfig();
        $userResource = new UsersResource($config->getDBSetting(ConfigInterface::PTERODACTYL_BASE_URL, ''), $config->getDBSetting(ConfigInterface::PTERODACTYL_API_KEY, ''));

        try {
            // Check if user exists and has servers
            $user = $userResource->getUserWithServers((int) $pterodactylUserId);
            if (empty($user)) {
                $appInstance->getLogger()->error('[Pterodactyl/Admin/User#performLogin:1] User data is empty: ' . $pterodactylUserId);
                throw new \Exception('User data is empty: ' . $pterodactylUserId);
            }
            // If user exists, update their details
            self::performUpdateUser($userResource, $pterodactylUserId, $username, $first_name, $last_name, $email, $password);
        } catch (ResourceNotFoundException $e) {
            // User not found, create new user
            $appInstance->getLogger()->error('[Pterodactyl/Admin/User#performLogin:2] User not found by id: ' . $pterodactylUserId);
            throw new \Exception('User not found by id: ' . $pterodactylUserId);
        } catch (\Exception $e) {
            // TODO: Delete user from mythicaldash
            $appInstance->getLogger()->error('[Pterodactyl/Admin/User#performLogin:3] Failed to update user in Pterodactyl: ' . $e->getMessage());
            throw new \Exception('Failed to update user in Pterodactyl: ' . $e->getMessage());
        }
    }

    /**
     * Perform a register action on the Pterodactyl panel.
     *
     * @param string $first_name The first name of the user to register
     * @param string $last_name The last name of the user to register
     * @param string $username The username of the user to register
     * @param string $email The email of the user to register
     * @param string $password The password of the user to register
     *
     * @return int The user id of the user in the pterodactyl panel
     */
    public static function performRegister(string $first_name, string $last_name, string $username, string $email, string $password): int
    {
        $appInstance = App::getInstance(true);

        // Validate input before proceeding
        $validationErrors = [];

        if (!self::validateUsername($username)) {
            $validationErrors[] = 'Username must be between 3 and 32 characters and can only contain letters, numbers, dashes, and underscores';
        }

        if (!self::validateEmail($email)) {
            $validationErrors[] = 'Email must be valid and not exceed 191 characters';
        }

        if (!self::validateName($first_name)) {
            $validationErrors[] = 'First name must be between 1 and 191 characters and can only contain letters, spaces, dashes, and apostrophes';
        }

        if (!self::validateName($last_name)) {
            $validationErrors[] = 'Last name must be between 1 and 191 characters and can only contain letters, spaces, dashes, and apostrophes';
        }

        if (!self::validatePassword($password)) {
            $validationErrors[] = 'Password must be at least 8 characters long';
        }

        if (!empty($validationErrors)) {
            $appInstance->getLogger()->error('[Pterodactyl/Admin/User#performRegister:1] Validation failed: ' . implode(', ', $validationErrors));
            throw new \Exception('Validation failed: ' . implode(', ', $validationErrors));
        }

        try {
            $config = $appInstance->getConfig();
            // Get the Pterodactyl base URL and API key from the config
            $userResource = new UsersResource($config->getDBSetting(ConfigInterface::PTERODACTYL_BASE_URL, ''), $config->getDBSetting(ConfigInterface::PTERODACTYL_API_KEY, ''));

            // Check if user exists by email
            try {
                $user = $userResource->findUserByEmail($email);

                return $user['attributes']['id'];
            } catch (ResourceNotFoundException $e) {
                // User not found by email, continue checking username
            }

            // Check if user exists by username
            try {
                $user = $userResource->findUserByUsername($username);

                return $user['attributes']['id'];
            } catch (ResourceNotFoundException $e) {
                // User not found by username either, proceed with creation
            }

            // If we get here, the user doesn't exist, so create them
            try {
                $newUser = $userResource->createUser(
                    $email,
                    $username,
                    $first_name,
                    $last_name,
                    $password
                );

                if (empty($newUser)) {
                    throw new \Exception('Failed to register user in Pterodactyl: Empty response');
                }

                return $newUser['attributes']['id'];
            } catch (ValidationException|PterodactylException $e) {
                $appInstance->getLogger()->error('[Pterodactyl/Admin/User#performRegister:2] Failed to register user in Pterodactyl: ' . $e->getMessage());
                throw new \Exception('Failed to register user in Pterodactyl: ' . $e->getMessage());
            }
        } catch (\Exception $e) {
            $appInstance->getLogger()->error('[Pterodactyl/Admin/User#performRegister:3] Failed to register user in Pterodactyl: ' . $e->getMessage());
            throw new \Exception('Failed to register user in Pterodactyl: ' . $e->getMessage());
        }
    }

    /**
     * Perform an update user action on the Pterodactyl panel.
     *
     * @param int $userId The ID of the user to update
     * @param string $username The username of the user to update
     * @param string $first_name The first name of the user to update
     * @param string $last_name The last name of the user to update
     * @param string $email The email of the user to update
     * @param string $password The password of the user to update
     */
    public static function performUpdateUser(UsersResource $user, $userId, string $username, string $first_name, string $last_name, string $email, string $password): void
    {
        $appInstance = App::getInstance(true);

        try {
            $user->updateUser($userId, ['username' => $username, 'email' => $email, 'password' => $password, 'first_name' => $first_name, 'last_name' => $last_name, 'language' => 'en']);
        } catch (ValidationException $e) {
            $appInstance->getLogger()->error('[Pterodactyl/Admin/User#performUpdateUser:1] Failed to update user in Pterodactyl: ' . $e->getMessage());
            throw new \Exception('Failed to update user in Pterodactyl: ' . $e->getMessage());
        } catch (ResourceNotFoundException $e) {
            $appInstance->getLogger()->error('[Pterodactyl/Admin/User#performUpdateUser:2] Failed to update user in Pterodactyl: ' . $e->getMessage());
            throw new \Exception('Failed to update user in Pterodactyl: ' . $e->getMessage());
        } catch (PterodactylException $e) {
            $appInstance->getLogger()->error('[Pterodactyl/Admin/User#performUpdateUser:3] Failed to update user in Pterodactyl: ' . $e->getMessage());
            throw new \Exception('Failed to update user in Pterodactyl: ' . $e->getMessage());
        }
    }

    /**
     * Check if a user exists in the Pterodactyl panel.
     *
     * @param string $userId The UUID of the user to check
     *
     * @return bool True if the user exists, false otherwise
     */
    public static function exists(string $userId): bool
    {
        $appInstance = App::getInstance(true);
        $config = $appInstance->getConfig();
        $userResource = new UsersResource($config->getDBSetting(ConfigInterface::PTERODACTYL_BASE_URL, ''), $config->getDBSetting(ConfigInterface::PTERODACTYL_API_KEY, ''));
        try {
            $userResource->doesUserExist((int) $userId);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @param string $username The username of the user
     *
     * @return int The user id of the user in the pterodactyl panel
     */
    private static function validateUsername(string $username): bool
    {
        return preg_match('/^[a-zA-Z0-9_-]{3,32}$/', $username);
    }

    /**
     * Validate email according to Pterodactyl standards
     * - Must be a valid email format
     * - Maximum length of 191 characters.
     */
    private static function validateEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) && strlen($email) <= 191;
    }

    /**
     * Validate name according to Pterodactyl standards
     * - Must be between 1 and 191 characters
     * - Can only contain letters, spaces, dashes, and apostrophes.
     */
    private static function validateName(string $name): bool
    {
        return preg_match('/^[a-zA-Z\' -]{1,191}$/', $name);
    }

    /**
     * Validate password according to Pterodactyl standards
     * - Must be at least 8 characters.
     */
    private static function validatePassword(string $password): bool
    {
        return strlen($password) >= 8;
    }
}
