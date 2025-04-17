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

namespace MythicalDash\Chat\User;

use MythicalDash\App;
use Gravatar\Gravatar;
use MythicalDash\Mail\Mail;
use MythicalDash\Chat\Database;
use MythicalDash\Mail\templates\Verify;
use MythicalDash\Mail\templates\NewLogin;
use MythicalDash\Chat\columns\UserColumns;
use MythicalDash\Mail\templates\ResetPassword;
use MythicalDash\Chat\interface\UserActivitiesTypes;
use MythicalDash\Chat\columns\EmailVerificationColumns;
use MythicalDash\Hooks\MythicalSystems\CloudFlare\CloudFlare;

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
                    $verify_token = App::getInstance(true)->generateCode();
                    $appInstance->getLogger()->debug('Verify token: ' . $verify_token);
                    Verification::add($verify_token, $uuid, EmailVerificationColumns::$type_verify);
                    $appInstance->getLogger()->debug('Verification added');
                    Verify::sendMail($uuid, $verify_token);
                    $appInstance->getLogger()->debug('Email sent');
                } catch (\Exception $e) {
                    App::getInstance(true)->getLogger()->error('Failed to send email: ' . $e->getMessage());
                    $appInstance->getLogger()->debug('Failed to send email');
                    self::updateInfo($token, UserColumns::VERIFIED, 'false', false);
                }
            } else {
                self::updateInfo($token, UserColumns::VERIFIED, 'true', false);
            }
        } catch (\Exception $e) {
            App::getInstance(true)->getLogger()->error('Failed to register user: ' . $e->getMessage());
            throw new \Exception('Failed to register user: ' . $e->getMessage());
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
                    UserActivities::add($user['uuid'], UserActivitiesTypes::$login, CloudFlare::getRealUserIP());

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
    public static function exists(UserColumns|string $info, string $value): bool
    {
        try {
            if (!in_array($info, UserColumns::getColumns())) {
                throw new \InvalidArgumentException('Invalid column name: ' . $info);
            }

            $con = self::getPdoConnection();
            $stmt = $con->prepare('SELECT * FROM ' . self::TABLE_NAME . ' WHERE ' . $info . ' = :value');
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
    public static function getInfo(string $token, UserColumns|string $info, bool $encrypted): ?string
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
                return App::getInstance(true)->decrypt($stmt->fetchColumn());
            }

            return $stmt->fetchColumn();
        } catch (\Exception $e) {
            Database::db_Error('Failed to grab the info about the user: ' . $e->getMessage());

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
    public static function updateInfo(string $token, UserColumns|string $info, ?string $value, bool $encrypted): bool
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
     * @return string The token
     */
    public static function getTokenFromUUID(string $uuid): string
    {
        try {
            $con = self::getPdoConnection();
            $stmt = $con->prepare('SELECT token FROM ' . self::TABLE_NAME . ' WHERE uuid = :uuid');
            $stmt->bindParam(':uuid', $uuid);
            $stmt->execute();

            return $stmt->fetchColumn();
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
    public static function getUUIDFromInfo(UserColumns|string $info, string $value): string
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
}
