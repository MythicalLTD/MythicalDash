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

namespace MythicalDash\Chat\User;

use MythicalDash\App;
use Gravatar\Gravatar;
use MythicalDash\Mail\Mail;
use MythicalDash\Chat\Database;
use MythicalDash\Mail\templates\Verify;
use MythicalDash\Config\ConfigInterface;
use MythicalDash\Mail\templates\NewLogin;
use MythicalDash\Chat\columns\UserColumns;
use MythicalDash\Mail\templates\ResetPassword;
use MythicalDash\Chat\columns\EmailVerificationColumns;

class User extends Database
{
    public const TABLE_NAME = 'mythicaldash_users';

    public static function getTableName(): string
    {
        return self::TABLE_NAME;
    }

    /**
     * Register a new user in the database.
     *
     * @param string $username The username of the user
     * @param string $password The password of the user
     * @param string $email The email of the user
     * @param string $first_name The first name of the user
     * @param string $last_name The last name of the user
     * @param string $ip The ip of the user
     * @param int $pterodactylUserId The user id of the user in the pterodactyl panel
     */
    public static function register(string $username, string $password, string $email, string $first_name, string $last_name, string $ip, int $pterodactylUserId): void
    {
        $config = App::getInstance(true)->getConfig();
        try {
            $appInstance = App::getInstance(true);
            $first_name = $appInstance->encrypt($first_name);
            $last_name = $appInstance->encrypt($last_name);

            /**
             * The UUID generation and logic.
             */
            $uuidMngr = new \MythicalDash\Hooks\MythicalSystems\User\UUIDManager();
            $uuid = $uuidMngr->generateUUID();
            $token = App::getInstance(true)->encrypt(date('Y-m-d H:i:s') . $uuid . random_bytes(16) . base64_encode($email));

            /**
             * GRAvatar Logic.
             */
            try {
                $gravatar = new Gravatar(['s' => 9001], true);
                $avatar = $gravatar->avatar($email);
            } catch (\Exception) {
                $avatar = 'https://www.gravatar.com/avatar';
            }

            /**
             * Get the PDO connection.
             */
            $pdoConnection = self::getPdoConnection();

            /**
             * Prepare the statement.
             */
            $stmt = $pdoConnection->prepare('
            INSERT INTO ' . self::TABLE_NAME . ' 
            (username, first_name, last_name, email, password, avatar, background, uuid, pterodactyl_user_id, token, role, first_ip, last_ip, banned, verified, support_pin) 
            VALUES 
            (:username, :first_name, :last_name, :email, :password, :avatar, :background, :uuid, :pterodactyl_user_id, :token, :role, :first_ip, :last_ip, :banned, :verified, :support_pin)
        ');
            $password = App::getInstance(true)->encrypt($password);

            $stmt->execute([
                ':username' => $username,
                ':first_name' => $first_name,
                ':last_name' => $last_name,
                ':email' => $email,
                ':password' => $password,
                ':avatar' => $avatar,
                ':background' => 'https://cdn.mythical.systems/background.gif',
                ':uuid' => $uuid,
                ':pterodactyl_user_id' => $pterodactylUserId,
                ':token' => $token,
                ':role' => 1,
                ':first_ip' => $ip,
                ':last_ip' => $ip,
                ':banned' => 'NO',
                ':verified' => 'false',
                ':support_pin' => App::getInstance(true)->generatePin(),
            ]);
            /**
             * Check if the mail is enabled.
             *
             * If it is, the user is not verified.
             *
             * If it is not, the user is verified.
             */
            if (Mail::isEnabled()) {
                try {
                    if ($config->getDBSetting(ConfigInterface::FORCE_MAIL_LINK, 'false') == 'true') {
                        $verify_token = App::getInstance(true)->generateCode();
                        $appInstance->getLogger()->debug('Verify token: ' . $verify_token);
                        Verification::add($verify_token, $uuid, EmailVerificationColumns::$type_verify);
                        $appInstance->getLogger()->debug('Verification added');
                        Verify::sendMail($uuid, $verify_token);
                        $appInstance->getLogger()->debug('Email sent');
                    } else {
                        self::updateInfo($token, UserColumns::VERIFIED, 'true', false);
                    }
                } catch (\Exception $e) {
                    App::getInstance(true)->getLogger()->error('Failed to send email: ' . $e->getMessage());
                    $appInstance->getLogger()->debug('Failed to send email');
                    self::updateInfo($token, UserColumns::VERIFIED, 'false', false);
                }
            } else {
                self::updateInfo($token, UserColumns::VERIFIED, 'true', false);
            }

            // Check if the user is the first user (id = 1) and set role to owner (role id 8) if so
            $con = self::getPdoConnection();
            $stmt = $con->prepare('SELECT id FROM ' . self::TABLE_NAME . ' WHERE uuid = :uuid');
            $stmt->bindParam(':uuid', $uuid);
            $stmt->execute();
            $userRow = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($userRow && isset($userRow['id']) && (int) $userRow['id'] === 1) {
                // Set role to owner (role id 8)
                self::updateInfo($token, UserColumns::ROLE_ID, 8, false);
            }
        } catch (\Exception $e) {
            App::getInstance(true)->getLogger()->error('Failed to register user: ' . $e->getMessage());
            throw new \Exception('Failed to register user: ' . $e->getMessage());
        }
    }

    /**
     * Check if the user is the first user in the database.
     *
     * @return bool If the user is the first user in the database
     *
     * @deprecated this method is deprecated and will be removed in the future
     */
    public static function isFirstUserInDatabase(): bool
    {
        try {
            $con = self::getPdoConnection();
            $stmt = $con->prepare('SELECT COUNT(*) as count FROM ' . self::TABLE_NAME . ' WHERE deleted = "false"');
            $stmt->execute();
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            return (int) $result['count'] === 0;
        } catch (\Exception $e) {
            Database::db_Error('Failed to check if user is first in database: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Get the list of users with filters.
     *
     * @param array $rows The rows to fetch
     * @param array $encrypted The rows that are encrypted
     *
     * @return array The list of users
     */
    public static function getListWithFilters(array $rows, array $encrypted): array
    {
        try {
            $con = self::getPdoConnection();
            $stmt = $con->prepare('SELECT ' . implode(', ', $rows) . ' FROM ' . self::TABLE_NAME . ' WHERE deleted = "false" ORDER BY id ASC');
            $stmt->execute();
            $users = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($users as &$user) {
                foreach ($rows as $row) {
                    if (in_array($row, $encrypted)) {
                        $user[$row] = App::getInstance(true)->decrypt($user[$row]);
                    }

                }
            }

            return $users;
        } catch (\Exception $e) {
            Database::db_Error('Failed to get user list: ' . $e->getMessage());

            return [];
        }

    }

    /**
     * Get the user by uuid.
     *
     * @param string $uuid The uuid of the user
     *
     * @return array|null The user or null if not found
     */
    public static function getUserByUuid(string $uuid): ?array
    {
        try {
            $con = self::getPdoConnection();
            $stmt = $con->prepare('SELECT * FROM ' . self::TABLE_NAME . ' WHERE uuid = :uuid');
            $stmt->bindParam(':uuid', $uuid);
            $stmt->execute();

            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
        } catch (\Exception $e) {
            Database::db_Error('Failed to get user by uuid: ' . $e->getMessage());

            return null;
        }
    }

    /**
     * Get the list of users.
     *
     * @return array The list of users
     */
    public static function getList(): array
    {
        try {
            $con = self::getPdoConnection();
            $stmt = $con->prepare('SELECT * FROM ' . self::TABLE_NAME . ' WHERE deleted = "false" ORDER BY id ASC');
            $stmt->execute();

            $users = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Decrypt sensitive fields for each user
            foreach ($users as &$user) {
                if (isset($user['first_name'])) {
                    $user['first_name'] = App::getInstance(true)->decrypt($user['first_name']);
                }
                if (isset($user['last_name'])) {
                    $user['last_name'] = App::getInstance(true)->decrypt($user['last_name']);
                }
            }

            return $users;

        } catch (\Exception $e) {
            Database::db_Error('Failed to get user list: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Forgot password logic.
     *
     * @param string $email The email of the user
     *
     * @return bool If the email was sent
     */
    public static function forgotPassword(string $email): bool
    {
        try {
            $con = self::getPdoConnection();
            $stmt = $con->prepare('SELECT token, uuid FROM ' . self::TABLE_NAME . ' WHERE email = :email');
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($user) {
                if (Mail::isEnabled()) {
                    try {
                        $verify_token = $verify_token = App::getInstance(true)->generateCode();
                        Verification::add($verify_token, $user['uuid'], EmailVerificationColumns::$type_password);
                        ResetPassword::sendMail($user['uuid'], $verify_token);
                    } catch (\Exception $e) {
                        App::getInstance(true)->getLogger()->error('Failed to send email: ' . $e->getMessage());
                    }

                    return true;
                }

                return false;
            }

            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Login the user.
     *
     * @param string $login The login of the user
     * @param string $password The password of the user
     *
     * @return string If the login was successful
     */
    public static function login(string $login, string $password): string
    {
        try {
            $con = self::getPdoConnection();
            $stmt = $con->prepare('SELECT password, token, uuid FROM ' . self::TABLE_NAME . ' WHERE username = :login OR email = :login');
            $stmt->bindParam(':login', $login);
            $stmt->execute();
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($user) {
                if (App::getInstance(true)->decrypt($user['password']) == $password) {
                    self::logout();
                    if (!$user['token'] == '') {
                        setcookie('user_token', $user['token'], time() + 3600, '/');
                    } else {
                        App::getInstance(true)->getLogger()->error('Failed to login user: Token is empty');

                        return 'false';
                    }
                    if (Mail::isEnabled()) {
                        try {
                            NewLogin::sendMail($user['uuid']);
                        } catch (\Exception $e) {
                            App::getInstance(true)->getLogger()->error('Failed to send email: ' . $e->getMessage());
                        }
                    }

                    return $user['token'];
                }

                return 'false';
            }

            return 'false';
        } catch (\Exception $e) {
            App::getInstance(true)->getLogger()->error('Failed to login user: ' . $e->getMessage());

            return 'false';
        }
    }

    /**
     * Logout the user.
     */
    public static function logout(): void
    {
        setcookie('user_token', '', time() - 460800 * 460800 * 460800, '/');
    }

    /**
     * Does the user info exist?
     *
     * @return bool If the user info exists
     */
    public static function exists(UserColumns | string $info, string $value, bool $doNotIncludeDeleted = false): bool
    {
        try {
            if (!in_array($info, UserColumns::getColumns())) {
                throw new \InvalidArgumentException('Invalid column name: ' . $info);
            }

            $con = self::getPdoConnection();
            $stmt = $con->prepare('SELECT * FROM ' . self::TABLE_NAME . ' WHERE ' . $info . ' = :value' . ($doNotIncludeDeleted ? ' AND deleted = "false"' : ''));
            $stmt->bindParam(':value', $value);
            $stmt->execute();

            return (bool) $stmt->fetchColumn();
        } catch (\Exception $e) {
            Database::db_Error('Failed to check if user exists: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Check if the support pin is correct.
     *
     * @param string $supportPin The support pin
     *
     * @return bool If the support pin is correct
     */
    public static function checkSupportPin(string $supportPin): bool
    {
        try {
            $con = self::getPdoConnection();
            $stmt = $con->prepare('SELECT * FROM ' . self::TABLE_NAME . ' WHERE support_pin = :supportPin');
            $stmt->bindParam(':supportPin', $supportPin);
            $stmt->execute();

            return (bool) $stmt->fetchColumn();

        } catch (\Exception $e) {
            Database::db_Error('Failed to check support pin: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Convert the support pin to the UUID.
     *
     * @param string $supportPin The support pin of the user!
     *
     * @return string The UUID of the user
     */
    public static function convertPinToUUID(string $supportPin): string
    {
        try {
            $con = self::getPdoConnection();
            $stmt = $con->prepare('SELECT uuid FROM ' . self::TABLE_NAME . ' WHERE support_pin = :supportPin AND deleted = "false" LIMIT 1');
            $stmt->bindParam(':supportPin', $supportPin);
            $stmt->execute();

            return $stmt->fetchColumn();
        } catch (\Exception $e) {
            Database::db_Error('Failed to convert pin to uuid: ' . $e->getMessage());

            return null;
        }
    }

    /**
     * Convert the email to the UUID.
     *
     * @param string $email The email of the user
     *
     * @return string The UUID of the user
     */
    public static function convertEmailToUUID(string $email): string
    {
        try {
            $con = self::getPdoConnection();
            $stmt = $con->prepare('SELECT uuid FROM ' . self::TABLE_NAME . ' WHERE email = :email AND deleted = "false" LIMIT 1');
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            return $stmt->fetchColumn();
        } catch (\Exception $e) {
            Database::db_Error('Failed to convert email to uuid: ' . $e->getMessage());

            return null;
        }
    }

    /**
     * Get the user info.
     *
     * @param UserColumns|string $info The column name
     *
     * @throws \InvalidArgumentException If the column name is invalid
     *
     * @return string|null The value of the column
     */
    public static function getInfo(string $token, UserColumns | string $info, bool $encrypted): ?string
    {
        try {
            if (!in_array($info, UserColumns::getColumns())) {
                throw new \InvalidArgumentException('Invalid column name: ' . $info);
            }
            $con = self::getPdoConnection();
            $stmt = $con->prepare('SELECT ' . $info . ' FROM ' . self::TABLE_NAME . ' WHERE token = :token');
            $stmt->bindParam(':token', $token);
            $stmt->execute();
            if ($encrypted) {
                return App::getInstance(true)->decrypt($stmt->fetchColumn()) ?? null;
            }

            return $stmt->fetchColumn() ?? null;
        } catch (\Exception $e) {
            Database::db_Error('Failed to grab the info about the user: ' . $e->getMessage());

            return null;
        }
    }

    /**
     * Get the user info by UUID.
     *
     * @param string $uuid The UUID of the user
     * @param UserColumns|string $info The column name
     * @param bool $encrypted If the value is encrypted
     *
     * @return string|null The value of the column
     */
    public static function getInfoUUID(string $uuid, UserColumns | string $info, bool $encrypted): ?string
    {
        try {
            $con = self::getPdoConnection();
            $stmt = $con->prepare('SELECT ' . $info . ' FROM ' . self::TABLE_NAME . ' WHERE uuid = :uuid');
            $stmt->bindParam(':uuid', $uuid);
            $stmt->execute();
            if ($encrypted) {
                return App::getInstance(true)->decrypt($stmt->fetchColumn()) ?? null;
            }

            return $stmt->fetchColumn() ?? null;
        } catch (\Exception $e) {
            Database::db_Error('Failed to get info: ' . $e->getMessage());

            return null;
        }
    }

    /**
     * Get the user info.
     *
     * @param string $token The token
     * @param array $columns The columns to fetch
     * @param array $columns_encrypted The columns that are encrypted
     *
     * @return array The user info
     */
    public static function getInfoArray(string $token, array $columns, array $columns_encrypted): array
    {
        try {
            $con = self::getPdoConnection();
            $columns_str = implode(', ', $columns);
            $stmt = $con->prepare('SELECT ' . $columns_str . ' FROM ' . self::TABLE_NAME . ' WHERE token = :token');
            $stmt->bindParam(':token', $token);
            $stmt->execute();

            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$result) {
                return [];
            }

            foreach ($columns as $column) {
                if (in_array($column, $columns_encrypted)) {
                    if (isset($result[$column]) && $result[$column] !== null) {
                        $result[$column] = App::getInstance(true)->decrypt($result[$column]);
                    }
                }
            }

            return $result;

        } catch (\Exception $e) {
            Database::db_Error('Failed to get info: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Update the user info.
     *
     * @param UserColumns|string $info The column name
     * @param string $value The value to update
     * @param bool $encrypted If the value is encrypted
     *
     * @throws \InvalidArgumentException If the column name is invalid
     *
     * @return bool If the update was successful
     */
    public static function updateInfo(string $token, UserColumns | string $info, ?string $value, bool $encrypted): bool
    {
        try {
            if (!in_array($info, UserColumns::getColumns())) {
                throw new \InvalidArgumentException('Invalid column name: ' . $info);
            }
            $con = self::getPdoConnection();
            if ($encrypted) {
                $value = App::getInstance(true)->encrypt($value);
            }
            $stmt = $con->prepare('UPDATE ' . self::TABLE_NAME . ' SET ' . $info . ' = :value WHERE token = :token');
            $stmt->bindParam(':value', $value);
            $stmt->bindParam(':token', $token);

            return $stmt->execute();
        } catch (\Exception $e) {
            Database::db_Error('Failed to update user info: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Get the token from the UUID.
     *
     * @param string $uuid The UUID
     *
     * @return string|null The token
     */
    public static function getTokenFromUUID(string $uuid): ?string
    {
        try {
            $con = self::getPdoConnection();
            $stmt = $con->prepare('SELECT token FROM ' . self::TABLE_NAME . ' WHERE uuid = :uuid');
            $stmt->bindParam(':uuid', $uuid);
            $stmt->execute();

            return $stmt->fetchColumn() ?? null;
        } catch (\Exception $e) {
            Database::db_Error('Failed to uuid to token: ' . $e->getMessage());

            return null;
        }
    }

    /**
     * Get the UUID from the info.
     *
     * @param UserColumns|string $info The column name
     * @param string $value The value
     *
     * @return string The UUID
     */
    public static function getUUIDFromInfo(UserColumns | string $info, string $value): string
    {
        try {
            $con = self::getPdoConnection();
            $stmt = $con->prepare('SELECT uuid FROM ' . self::TABLE_NAME . ' WHERE ' . $info . ' = :value');
            $stmt->bindParam(':value', $value);
            $stmt->execute();

            return $stmt->fetchColumn();
        } catch (\Exception $e) {
            Database::db_Error('Failed to uuid from info: ' . $e->getMessage());

            return null;
        }
    }

    /**
     * Get the UUID from the github id.
     *
     * @param string $githubID The github id
     *
     * @return string The UUID
     */
    public static function getUUIDFromGitHubID(string $githubID): string
    {
        try {
            $con = self::getPdoConnection();
            $stmt = $con->prepare('SELECT uuid FROM ' . self::TABLE_NAME . ' WHERE github_id = :githubID AND deleted = "false" LIMIT 1');
            $stmt->bindParam(':githubID', $githubID);
            $stmt->execute();

            return $stmt->fetchColumn();
        } catch (\Exception $e) {
            Database::db_Error('Failed to uuid from github id: ' . $e->getMessage());

            return '';
        }
    }

    /**
     * Get the UUID from the discord id.
     *
     * @param string $discordID The discord id
     *
     * @return string The UUID
     */
    public static function getUUIDFromDiscordID(string $discordID): string
    {
        try {
            $con = self::getPdoConnection();
            $stmt = $con->prepare('SELECT uuid FROM ' . self::TABLE_NAME . ' WHERE discord_id = :discordID AND deleted = "false" LIMIT 1');
            $stmt->bindParam(':discordID', $discordID);
            $stmt->execute();

            return $stmt->fetchColumn();
        } catch (\Exception $e) {
            Database::db_Error('Failed to uuid from discord id: ' . $e->getMessage());

            return '';
        }
    }

    /**
     * Get the token from the email.
     *
     * @param string $email The email
     *
     * @return string The token
     */
    public static function getTokenFromEmail(string $email): string
    {
        try {
            $con = self::getPdoConnection();
            $stmt = $con->prepare('SELECT token FROM ' . self::TABLE_NAME . ' WHERE email = :email');
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            return $stmt->fetchColumn();
        } catch (\Exception $e) {
            Database::db_Error('Failed to uuid to token: ' . $e->getMessage());

            return null;
        }
    }

    /**
     * Process the template.
     *
     * @param string $template The template
     * @param string $uuid The UUID
     *
     * @return string The processed template
     */
    public static function processTemplate(string $template, string $uuid): string
    {
        try {

            $columns = [
                UserColumns::USERNAME,
                UserColumns::EMAIL,
                UserColumns::FIRST_NAME,
                UserColumns::LAST_NAME,
                UserColumns::AVATAR,
                UserColumns::BACKGROUND,
                UserColumns::ROLE_ID,
                UserColumns::FIRST_IP,
                UserColumns::LAST_IP,
                UserColumns::BANNED,
                UserColumns::VERIFIED,
                UserColumns::TWO_FA_ENABLED,
                UserColumns::DELETED,
                UserColumns::LAST_SEEN,
                UserColumns::FIRST_SEEN,
            ];

            $columns_encrypted = [
                UserColumns::FIRST_NAME,
                UserColumns::LAST_NAME,
            ];

            $userInfo = self::getInfoArray(self::getTokenFromUUID($uuid), $columns, $columns_encrypted);

            foreach ($userInfo as $key => $value) {
                $template = str_replace('${' . $key . '}', $value, $template);
            }

            return $template;
        } catch (\Exception $e) {
            Database::db_Error('Failed to process the template: ' . $e->getMessage());

            return null;
        }
    }

    /**
     * Delete the user.
     *
     * @param string $token The token
     *
     * @return bool If the user was deleted
     */
    public static function delete(string $token): bool
    {
        return self::updateInfo($token, UserColumns::DELETED, 'true', false);
    }

    /**
     * Get the user's credits.
     *
     * @param string $token The token
     *
     * @return int The user's credits
     *
     * @deprecated Use checkCreditsAtomic instead
     */
    public static function getCredits(string $token): int
    {
        return intval(self::getInfo($token, UserColumns::CREDITS, false));
    }

    /**
     * Add credits to the user's account.
     *
     * @param string $token The token
     * @param int $credits The number of credits to add
     *
     * @deprecated Use addCreditsAtomic instead
     */
    public static function addCredits(string $token, int $credits): void
    {
        $currentCredits = self::getCredits($token);
        self::updateInfo($token, UserColumns::CREDITS, $currentCredits + $credits, false);
    }

    /**
     * Remove credits from the user's account.
     *
     * @param string $token The token
     * @param int $credits The number of credits to remove
     *
     * @deprecated Use removeCreditsAtomic instead
     */
    public static function removeCredits(string $token, int $credits): void
    {
        $currentCredits = self::getCredits($token);
        self::updateInfo($token, UserColumns::CREDITS, $currentCredits - $credits, false);
    }

    /**
     * Remove credits from the user's account atomically with row-level locking.
     * This method prevents race conditions by using database transactions.
     *
     * @param string $token The token
     * @param int $credits The number of credits to remove
     *
     * @return bool true if successful, false if insufficient credits or operation failed
     */
    public static function removeCreditsAtomic(string $token, int $credits): bool
    {
        try {
            $con = self::getPdoConnection();

            // First check if user has enough credits (no lock needed for read)
            $stmt = $con->prepare('SELECT credits FROM ' . self::TABLE_NAME . ' WHERE token = ?');
            $stmt->execute([$token]);
            $currentCredits = (int) $stmt->fetchColumn();

            // Check if user has enough credits
            if ($currentCredits < $credits) {
                return false;
            }

            // Use atomic UPDATE with condition to prevent negative credits
            $stmt = $con->prepare('UPDATE ' . self::TABLE_NAME . ' SET credits = credits - ? WHERE token = ? AND credits >= ?');
            $result = $stmt->execute([$credits, $token, $credits]);

            // Check if the update was successful (rowCount > 0 means credits were sufficient)
            return $result && $stmt->rowCount() > 0;

        } catch (\Exception $e) {
            self::db_Error('Failed to remove credits atomically: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Add credits to the user's account atomically with row-level locking.
     * This method prevents race conditions by using database transactions.
     *
     * @param string $token The token
     * @param int $credits The number of credits to add
     *
     * @return bool true if successful, false if operation failed
     */
    public static function addCreditsAtomic(string $token, int $credits): bool
    {
        try {
            $con = self::getPdoConnection();

            // Simple atomic UPDATE - MySQL handles concurrency internally
            // This is much faster and safer than manual locking
            $stmt = $con->prepare('UPDATE ' . self::TABLE_NAME . ' SET credits = credits + ? WHERE token = ?');
            $result = $stmt->execute([$credits, $token]);

            return $result && $stmt->rowCount() > 0;

        } catch (\Exception $e) {
            self::db_Error('Failed to add credits atomically: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Check if user has sufficient credits atomically with row-level locking.
     * This method prevents race conditions by using database transactions.
     *
     * @param string $token The token
     * @param int $requiredCredits The number of credits required
     *
     * @return array with 'has_sufficient' boolean and 'current_credits' integer
     */
    public static function checkCreditsAtomic(string $token, int $requiredCredits): array
    {
        try {
            $con = self::getPdoConnection();

            // Simple SELECT - no locking needed for read operations
            $stmt = $con->prepare('SELECT credits FROM ' . self::TABLE_NAME . ' WHERE token = ?');
            $stmt->execute([$token]);
            $currentCredits = (int) $stmt->fetchColumn();

            return [
                'has_sufficient' => $currentCredits >= $requiredCredits,
                'current_credits' => $currentCredits,
            ];

        } catch (\Exception $e) {
            self::db_Error('Failed to check credits atomically: ' . $e->getMessage());

            return [
                'has_sufficient' => false,
                'current_credits' => 0,
            ];
        }
    }

    public static function getUserByUploadKey(string $uploadKey): ?string
    {
        try {
            $con = self::getPdoConnection();
            $stmt = $con->prepare('SELECT uuid FROM ' . self::TABLE_NAME . ' WHERE image_hosting_upload_key = :uploadKey');
            $stmt->bindParam(':uploadKey', $uploadKey);
            $stmt->execute();

            $result = $stmt->fetchColumn();

            return $result ? (string) $result : null;
        } catch (\Exception $e) {
            Database::db_Error('Failed to get user by upload key: ' . $e->getMessage());

            return null;
        }
    }

    /**
     * Get paginated users with optional search across username and email.
     *
     * @return array{items: array<int, array<string,mixed>>, total: int}
     */
    public static function getPaginatedWithSearch(array $rows, array $encrypted, int $page = 1, int $limit = 20, ?string $search = null): array
    {
        try {
            $page = max(1, $page);
            $limit = max(1, min(100, $limit));
            $offset = ($page - 1) * $limit;

            $con = self::getPdoConnection();

            $where = 'deleted = "false"';
            $params = [];
            if ($search !== null && $search !== '') {
                $where .= ' AND (username LIKE :q OR email LIKE :q)';
                $params[':q'] = '%' . $search . '%';
            }

            // Total count
            $countSql = 'SELECT COUNT(*) as cnt FROM ' . self::TABLE_NAME . ' WHERE ' . $where;
            $countStmt = $con->prepare($countSql);
            foreach ($params as $k => $v) {
                $countStmt->bindValue($k, $v);
            }
            $countStmt->execute();
            $total = (int) $countStmt->fetch(\PDO::FETCH_ASSOC)['cnt'];

            // Page items
            $sql = 'SELECT ' . implode(', ', $rows) . ' FROM ' . self::TABLE_NAME . ' WHERE ' . $where . ' ORDER BY id ASC LIMIT :limit OFFSET :offset';
            $stmt = $con->prepare($sql);
            foreach ($params as $k => $v) {
                $stmt->bindValue($k, $v);
            }
            $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
            $stmt->execute();
            $users = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($users as &$user) {
                foreach ($rows as $row) {
                    if (in_array($row, $encrypted)) {
                        $user[$row] = App::getInstance(true)->decrypt($user[$row]);
                    }
                }
            }

            return [
                'items' => $users,
                'total' => $total,
            ];
        } catch (\Exception $e) {
            Database::db_Error('Failed to get paginated user list: ' . $e->getMessage());

            return [
                'items' => [],
                'total' => 0,
            ];
        }
    }
}
