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

namespace MythicalDash\Cli\Commands;

use MythicalDash\Cli\App;
use MythicalDash\Chat\Database;
use MythicalDash\Chat\User\User;
use MythicalDash\Chat\User\Roles;
use MythicalDash\Cli\CommandBuilder;
use MythicalDash\Config\ConfigFactory;
use MythicalDash\Chat\columns\UserColumns;

class MakeAdmin extends App implements CommandBuilder
{
    private static $cliApp;
    private static $config;
    private static $users = [];
    private static $pageSize = 10;
    private static $currentPage = 0;
    private static $searchTerm = '';
    private static $filteredUsers = [];

    public static function execute(array $args): void
    {
        self::$cliApp = App::getInstance();

        if (!file_exists(__DIR__ . '/../../../storage/.env')) {
            self::$cliApp->send('&7The application is not setup!');
            exit;
        }

        \MythicalDash\App::getInstance(true)->loadEnv();

        try {
            $db = new Database($_ENV['DATABASE_HOST'], $_ENV['DATABASE_DATABASE'], $_ENV['DATABASE_USER'], $_ENV['DATABASE_PASSWORD'], $_ENV['DATABASE_PORT']);
            self::$config = new ConfigFactory($db->getPdo());

            self::showWelcome();
            self::handleUserInput();
        } catch (\Exception $e) {
            self::$cliApp->send('&cAn error occurred while connecting to the database: ' . $e->getMessage());
            exit;
        }
    }

    public static function getDescription(): string
    {
        return 'Promote users to admin with a beautiful search interface!';
    }

    public static function getSubCommands(): array
    {
        return [];
    }

    private static function showWelcome(): void
    {
        self::clearScreen();
        self::$cliApp->send('&6╔══════════════════════════════════════════════════════════════╗');
        self::$cliApp->send('&6║                    &eMake User Admin                         &6║');
        self::$cliApp->send('&6╠══════════════════════════════════════════════════════════════╣');
        self::$cliApp->send('&6║  &7Welcome to the admin promotion tool!                      &6║');
        self::$cliApp->send('&6║  &7You can search for users and promote them to admin.       &6║');
        self::$cliApp->send('&6╚══════════════════════════════════════════════════════════════╝');
        self::$cliApp->send('');
    }

    private static function handleUserInput(): void
    {
        while (true) {
            self::directEmailInput();
            break;
        }
    }

    private static function searchAndSelectUser(): void
    {
        self::loadUsers();

        while (true) {
            self::clearScreen();
            self::$cliApp->send('&6╔══════════════════════════════════════════════════════════════╗');
            self::$cliApp->send('&6║                      &eSearch Users                          &6║');
            self::$cliApp->send('&6╠══════════════════════════════════════════════════════════════╣');
            if (!empty(self::$searchTerm)) {
                self::$cliApp->send('&6║  &7Current search: &e"' . self::$searchTerm . '"              &6║');
                self::$cliApp->send('&6║  &7Found: &e' . count(self::$filteredUsers) . ' &7users            &6║');
            } else {
                self::$cliApp->send('&6║  &7Enter search term to find users                           &6║');
            }
            self::$cliApp->send('&6║                                                              &6║');
            self::$cliApp->send('&6║  &7Commands: &es &7- search | &el &7- list results | &eq &7- quit     &6║');
            self::$cliApp->send('&6╚══════════════════════════════════════════════════════════════╝');
            self::$cliApp->send('');
            self::$cliApp->send('&7Enter command: &e');

            $input = trim(strtolower(fgets(STDIN)));

            if ($input === 'q' || $input === 'quit') {
                return;
            } elseif ($input === 's' || $input === 'search') {
                self::handleSearch();
            } elseif ($input === 'l' || $input === 'list') {
                if (!empty(self::$filteredUsers)) {
                    self::showUserSelectionList();
                } else {
                    self::$cliApp->send('&cNo users found. Please search first. Press any key...');
                    fgets(STDIN);
                }
            } else {
                self::$cliApp->send('&cInvalid command. Press any key...');
                fgets(STDIN);
            }
        }
    }

    private static function loadUsers(): void
    {
        $columns = [
            UserColumns::ID,
            UserColumns::USERNAME,
            UserColumns::EMAIL,
            UserColumns::FIRST_NAME,
            UserColumns::LAST_NAME,
            UserColumns::ROLE_ID,
            UserColumns::ACCOUNT_TOKEN,
            UserColumns::DELETED,
        ];

        $encrypted = [
            UserColumns::FIRST_NAME,
            UserColumns::LAST_NAME,
        ];

        self::$users = User::getListWithFilters($columns, $encrypted);
        // Filter out deleted users and existing admins/owners
        self::$users = array_filter(self::$users, function ($user) {
            if ($user['deleted'] !== 'false') {
                return false;
            }

            // Get role info to check if user is already admin or higher
            $roleName = Roles::getRoleNameById((int) $user['role'], true); // Use real_name
            $adminLevelRoles = ['admin', 'owner', 'administrator', 'root'];

            return !in_array(strtolower($roleName ?? ''), $adminLevelRoles);
        });
        self::applySearch();
    }

    private static function applySearch(): void
    {
        if (empty(self::$searchTerm)) {
            self::$filteredUsers = [];
        } else {
            self::$filteredUsers = array_filter(self::$users, function ($user) {
                $searchLower = strtolower(self::$searchTerm);

                return strpos(strtolower($user['username']), $searchLower) !== false
                       || strpos(strtolower($user['email']), $searchLower) !== false
                       || strpos(strtolower($user['first_name']), $searchLower) !== false
                       || strpos(strtolower($user['last_name']), $searchLower) !== false
                       || strpos(strtolower($user['id']), $searchLower) !== false;
            });
        }
        self::$currentPage = 0;
    }

    private static function handleSearch(): void
    {
        self::clearScreen();
        self::$cliApp->send('&6╔══════════════════════════════════════════════════════════════╗');
        self::$cliApp->send('&6║                      &eSearch Users                          &6║');
        self::$cliApp->send('&6╠══════════════════════════════════════════════════════════════╣');
        self::$cliApp->send('&6║  &7Search by username, email, name, or ID                    &6║');
        self::$cliApp->send('&6║  &7Enter search term: &e');
        self::$cliApp->send('&6╚══════════════════════════════════════════════════════════════╝');

        $search = trim(fgets(STDIN));

        if (!empty($search)) {
            self::$searchTerm = $search;
            self::applySearch();
        }
    }

    private static function showUserSelectionList(): void
    {
        while (true) {
            self::clearScreen();
            self::$cliApp->send('&6╔══════════════════════════════════════════════════════════════╗');
            self::$cliApp->send('&6║                    &eSelect User for Admin                   &6║');
            self::$cliApp->send('&6╠══════════════════════════════════════════════════════════════╣');
            self::$cliApp->send('&6║  &7Search: &e"' . self::$searchTerm . '" &7| Found: &e' . count(self::$filteredUsers) . ' &7users &6║');
            self::$cliApp->send('&6╚══════════════════════════════════════════════════════════════╝');
            self::$cliApp->send('');

            $startIndex = self::$currentPage * self::$pageSize;
            $endIndex = min($startIndex + self::$pageSize, count(self::$filteredUsers));
            $usersArray = array_values(self::$filteredUsers);

            if (empty(self::$filteredUsers)) {
                self::$cliApp->send('&7No users found matching your search.');
                self::$cliApp->send('');
            } else {
                for ($i = $startIndex; $i < $endIndex; ++$i) {
                    $user = $usersArray[$i];
                    $displayIndex = ($i - $startIndex) + 1;

                    $username = strlen($user['username']) > 15 ? substr($user['username'], 0, 12) . '...' : $user['username'];
                    $email = strlen($user['email']) > 25 ? substr($user['email'], 0, 22) . '...' : $user['email'];
                    $fullName = trim($user['first_name'] . ' ' . $user['last_name']);
                    $role = self::getRoleName($user['role']);

                    $line = sprintf(
                        '&7%2d. &e%-15s &7| &b%-25s &7| &d%-10s &7(ID: %s)',
                        $displayIndex,
                        $username,
                        $email,
                        $role,
                        $user['id']
                    );

                    self::$cliApp->send($line);
                }
                self::$cliApp->send('');
            }

            // Show navigation
            $totalPages = ceil(count(self::$filteredUsers) / self::$pageSize);
            $currentPageNum = self::$currentPage + 1;

            self::$cliApp->send('&6╔══════════════════════════════════════════════════════════════╗');
            if ($totalPages > 1) {
                self::$cliApp->send('&6║ &7Page: &e' . $currentPageNum . '/' . $totalPages . ' &7| Commands: &e[number] &7select | &en/p &7nav | &eb &7back &6║');
            } else {
                self::$cliApp->send('&6║ &7Commands: &e[number] &7to select user | &eb &7back | &eq &7quit     &6║');
            }
            self::$cliApp->send('&6╚══════════════════════════════════════════════════════════════╝');
            self::$cliApp->send('');
            self::$cliApp->send('&7Enter your choice: &e');

            $input = trim(strtolower(fgets(STDIN)));

            if ($input === 'q' || $input === 'quit' || $input === 'b' || $input === 'back') {
                return;
            } elseif ($input === 'n' || $input === 'next') {
                self::nextPage();
            } elseif ($input === 'p' || $input === 'prev') {
                self::prevPage();
            } elseif (is_numeric($input)) {
                $index = (int) $input - 1;
                $actualIndex = $startIndex + $index;

                if ($actualIndex >= 0 && $actualIndex < count(self::$filteredUsers)) {
                    $user = $usersArray[$actualIndex];
                    self::confirmAndPromoteUser($user);

                    return;
                }
                self::$cliApp->send('&cInvalid selection. Press any key to continue...');
                fgets(STDIN);

            } else {
                self::$cliApp->send('&cInvalid input. Press any key to continue...');
                fgets(STDIN);
            }
        }
    }

    private static function getRoleName(string $roleId): string
    {
        $roleName = Roles::getRoleNameById((int) $roleId, false);

        return $roleName ?? 'Unknown';
    }

    private static function directEmailInput(): void
    {
        self::clearScreen();
        self::$cliApp->send('&6╔══════════════════════════════════════════════════════════════╗');
        self::$cliApp->send('&6║                    &eEnter Email Address                     &6║');
        self::$cliApp->send('&6╠══════════════════════════════════════════════════════════════╣');
        self::$cliApp->send('&6║  &7Please enter the user\'s email address:                    &6║');
        self::$cliApp->send('&6║  &7(or type &cq &7to go back)                                 &6║');
        self::$cliApp->send('&6║                                                              &6║');
        self::$cliApp->send('&6║  &7Email: &e');
        self::$cliApp->send('&6╚══════════════════════════════════════════════════════════════╝');

        $email = trim(fgets(STDIN));

        if ($email === 'q' || $email === 'quit') {
            return;
        }

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            self::$cliApp->send('&cInvalid email address. Press any key to try again...');
            fgets(STDIN);
            self::directEmailInput();

            return;
        }

        self::handleDirectEmail($email);
    }

    private static function handleDirectEmail(string $email): void
    {
        try {
            if (!User::exists(UserColumns::EMAIL, $email)) {
                self::$cliApp->send('&cUser with email "' . $email . '" not found!');
                self::$cliApp->send('&7Press any key to continue...');
                fgets(STDIN);

                return;
            }

            $token = User::getTokenFromEmail($email);
            if (!$token) {
                self::$cliApp->send('&cError: Could not retrieve user token!');
                self::$cliApp->send('&7Press any key to continue...');
                fgets(STDIN);

                return;
            }

            // Get user info
            $columns = [
                UserColumns::ID,
                UserColumns::USERNAME,
                UserColumns::EMAIL,
                UserColumns::FIRST_NAME,
                UserColumns::LAST_NAME,
                UserColumns::ROLE_ID,
                UserColumns::ACCOUNT_TOKEN,
            ];

            $encrypted = [
                UserColumns::FIRST_NAME,
                UserColumns::LAST_NAME,
            ];

            $user = User::getInfoArray($token, $columns, $encrypted);

            if (empty($user)) {
                self::$cliApp->send('&cError: Could not retrieve user information!');
                self::$cliApp->send('&7Press any key to continue...');
                fgets(STDIN);

                return;
            }

            // Check if user is already admin level
            $roleName = Roles::getRoleNameById((int) $user['role'], true); // Use real_name
            $adminLevelRoles = ['admin', 'owner', 'administrator', 'root'];

            if (in_array(strtolower($roleName ?? ''), $adminLevelRoles)) {
                self::$cliApp->send('&cUser is already an admin or higher level role!');
                self::$cliApp->send('&7Current role: ' . $roleName);
                self::$cliApp->send('&7Press any key to continue...');
                fgets(STDIN);

                return;
            }

            self::confirmAndPromoteUser($user);

        } catch (\Exception $e) {
            self::$cliApp->send('&cError: ' . $e->getMessage());
            self::$cliApp->send('&7Press any key to continue...');
            fgets(STDIN);
        }
    }

    private static function confirmAndPromoteUser(array $user): void
    {
        $fullName = trim($user['first_name'] . ' ' . $user['last_name']);
        $currentRole = self::getRoleName($user['role']);

        self::clearScreen();
        self::$cliApp->send('&6╔══════════════════════════════════════════════════════════════╗');
        self::$cliApp->send('&6║                   &eConfirm Admin Promotion                  &6║');
        self::$cliApp->send('&6╠══════════════════════════════════════════════════════════════╣');
        self::$cliApp->send('&6║  &7User Details:                                             &6║');
        self::$cliApp->send('&6║  &7ID: &e' . $user['id'] . ' &6║');
        self::$cliApp->send('&6║  &7Username: &e' . $user['username'] . ' &6║');
        self::$cliApp->send('&6║  &7Email: &e' . $user['email'] . ' &6║');
        self::$cliApp->send('&6║  &7Name: &e' . $fullName . ' &6║');
        self::$cliApp->send('&6║  &7Current Role: &d' . $currentRole . ' &6║');
        self::$cliApp->send('&6║                                                              &6║');
        self::$cliApp->send('&6║  &7Are you sure you want to promote this user to &cAdmin&7?    &6║');
        self::$cliApp->send('&6║  &7Type &ayes &7to confirm or &cno &7to cancel: &e');
        self::$cliApp->send('&6╚══════════════════════════════════════════════════════════════╝');

        $confirmation = trim(strtolower(fgets(STDIN)));

        if ($confirmation === 'yes' || $confirmation === 'y') {
            self::promoteUserToAdmin($user);
        } else {
            self::$cliApp->send('&7Operation cancelled.');
            self::$cliApp->send('&7Press any key to continue...');
            fgets(STDIN);
        }
    }

    private static function promoteUserToAdmin(array $user): void
    {
        try {
            // Find the admin role ID from the database
            $roles = Roles::getList();
            $adminRoleId = null;

            foreach ($roles as $role) {
                $realName = strtolower($role['real_name'] ?? '');
                if ($realName === 'admin' || $realName === 'administrator') {
                    $adminRoleId = $role['id'];
                    break;
                }
            }

            if ($adminRoleId === null) {
                self::$cliApp->send('&c✗ Admin role not found in the system!');
                self::$cliApp->send('&7Please ensure an admin role exists in the database.');
                self::$cliApp->send('&7Press any key to exit...');
                fgets(STDIN);

                return;
            }

            $token = $user[UserColumns::ACCOUNT_TOKEN];
            $result = User::updateInfo($token, UserColumns::ROLE_ID, (string) $adminRoleId, false);

            if ($result) {
                $adminRoleName = Roles::getRoleNameById($adminRoleId, false) ?? 'Admin';

                self::clearScreen();
                self::$cliApp->send('&6╔══════════════════════════════════════════════════════════════╗');
                self::$cliApp->send('&6║                     &aSuccess!                               &6║');
                self::$cliApp->send('&6╠══════════════════════════════════════════════════════════════╣');
                self::$cliApp->send('&6║  &a✓ User &e' . $user['username'] . ' &ahas been promoted to ' . $adminRoleName . '!     &6║');
                self::$cliApp->send('&6║                                                              &6║');
                self::$cliApp->send('&6║  &7User Details:                                             &6║');
                self::$cliApp->send('&6║  &7• Username: &e' . $user['username'] . ' &6║');
                self::$cliApp->send('&6║  &7• Email: &e' . $user['email'] . ' &6║');
                self::$cliApp->send('&6║  &7• New Role: &d' . $adminRoleName . ' &6║');
                self::$cliApp->send('&6║                                                              &6║');
                self::$cliApp->send('&6║  &7The user now has administrative privileges!              &6║');
                self::$cliApp->send('&6╚══════════════════════════════════════════════════════════════╝');
            } else {
                self::$cliApp->send('&c✗ Failed to promote user to admin!');
                self::$cliApp->send('&7This could be due to a database error or permission issue.');
            }
        } catch (\Exception $e) {
            self::$cliApp->send('&c✗ Error promoting user: ' . $e->getMessage());
        }

        self::$cliApp->send('');
        self::$cliApp->send('&7Press any key to exit...');
        fgets(STDIN);
    }

    private static function nextPage(): void
    {
        $maxPage = ceil(count(self::$filteredUsers) / self::$pageSize) - 1;
        if (self::$currentPage < $maxPage) {
            ++self::$currentPage;
        }
    }

    private static function prevPage(): void
    {
        if (self::$currentPage > 0) {
            --self::$currentPage;
        }
    }

    private static function clearScreen(): void
    {
        if (PHP_OS_FAMILY === 'Windows') {
            system('cls');
        } else {
            system('clear');
        }
    }
}
