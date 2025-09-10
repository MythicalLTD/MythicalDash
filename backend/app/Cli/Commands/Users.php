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

class Users extends App implements CommandBuilder
{
    private static $cliApp;
    private static $config;
    private static $users = [];
    private static $currentIndex = 0;
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
            self::loadUsers();

            self::showMainMenu();
        } catch (\Exception $e) {
            self::$cliApp->send('&cAn error occurred while connecting to the database: ' . $e->getMessage());
            exit;
        }
    }

    public static function getDescription(): string
    {
        return 'Interactive user management with a beautiful UI! Browse, search, and edit users.';
    }

    public static function getSubCommands(): array
    {
        return [];
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
            UserColumns::VERIFIED,
            UserColumns::BANNED,
            UserColumns::CREDITS,
            UserColumns::UUID,
            UserColumns::ACCOUNT_TOKEN,
            UserColumns::LAST_SEEN,
            UserColumns::DELETED,
            // Resource limits
            UserColumns::MEMORY_LIMIT,
            UserColumns::DISK_LIMIT,
            UserColumns::CPU_LIMIT,
            UserColumns::SERVER_LIMIT,
            UserColumns::BACKUP_LIMIT,
            UserColumns::DATABASE_LIMIT,
            UserColumns::ALLOCATION_LIMIT,
            // Additional status fields
            'locked',
            UserColumns::IMAGE_HOSTING_ENABLED,
            UserColumns::TWO_FA_ENABLED,
            UserColumns::TWO_FA_BLOCKED,
            UserColumns::SUPPORT_PIN,
            UserColumns::DISCORD_LINKED,
            UserColumns::GITHUB_LINKED,
            UserColumns::PTERODACTYL_USER_ID,
        ];

        $encrypted = [
            UserColumns::FIRST_NAME,
            UserColumns::LAST_NAME,
        ];

        self::$users = User::getListWithFilters($columns, $encrypted);
        self::applySearch();
    }

    private static function applySearch(): void
    {
        if (empty(self::$searchTerm)) {
            self::$filteredUsers = self::$users;
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
        self::$currentPage = 0; // Reset to first page when search changes
    }

    private static function showMainMenu(): void
    {
        while (true) {
            self::clearScreen();
            self::showHeader();
            self::showUsersList();
            self::showFooter();

            $input = self::getUserInput();

            if ($input === 'q' || $input === 'quit') {
                self::$cliApp->send('&aGoodbye!');
                exit;
            } elseif ($input === 'n' || $input === 'next') {
                self::nextPage();
            } elseif ($input === 'p' || $input === 'prev') {
                self::prevPage();
            } elseif ($input === 's' || $input === 'search') {
                self::handleSearch();
            } elseif ($input === 'c' || $input === 'clear') {
                self::$searchTerm = '';
                self::applySearch();
            } elseif ($input === 'r' || $input === 'refresh') {
                self::loadUsers();
            } elseif (is_numeric($input)) {
                $index = (int) $input - 1;
                $startIndex = self::$currentPage * self::$pageSize;
                $actualIndex = $startIndex + $index;

                if ($actualIndex >= 0 && $actualIndex < count(self::$filteredUsers)) {
                    $user = array_values(self::$filteredUsers)[$actualIndex];
                    self::editUser($user);
                } else {
                    self::$cliApp->send('&cInvalid selection. Press any key to continue...');
                    self::waitForInput();
                }
            } else {
                self::$cliApp->send('&cInvalid input. Press any key to continue...');
                self::waitForInput();
            }
        }
    }

    private static function showHeader(): void
    {
        $totalUsers = count(self::$filteredUsers);
        $totalPages = ceil($totalUsers / self::$pageSize);
        $currentPageNum = self::$currentPage + 1;

        self::$cliApp->send('&6╔══════════════════════════════════════════════════════════════╗');
        self::$cliApp->send('&6║                    &eMythicalDash Users                     &6║');
        self::$cliApp->send('&6╠══════════════════════════════════════════════════════════════╣');

        if (!empty(self::$searchTerm)) {
            self::$cliApp->send('&6║  &7Search: &e"' . self::$searchTerm . '" &7| Results: &e' . $totalUsers . ' &6║');
        }

        self::$cliApp->send('&6║  &7Total Users: &e' . $totalUsers . ' &7| Page: &e' . $currentPageNum . '/' . max(1, $totalPages) . ' &6║');
        self::$cliApp->send('&6╚══════════════════════════════════════════════════════════════╝');
        self::$cliApp->send('');
    }

    private static function showUsersList(): void
    {
        $startIndex = self::$currentPage * self::$pageSize;
        $endIndex = min($startIndex + self::$pageSize, count(self::$filteredUsers));
        $usersArray = array_values(self::$filteredUsers);

        if (empty(self::$filteredUsers)) {
            self::$cliApp->send('&7No users found.');
            self::$cliApp->send('');

            return;
        }

        for ($i = $startIndex; $i < $endIndex; ++$i) {
            $user = $usersArray[$i];
            $displayIndex = ($i - $startIndex) + 1;

            // Format user display
            $username = strlen($user['username']) > 15 ? substr($user['username'], 0, 12) . '...' : $user['username'];
            $email = strlen($user['email']) > 20 ? substr($user['email'], 0, 17) . '...' : $user['email'];
            $fullName = trim($user['first_name'] . ' ' . $user['last_name']);
            $fullName = strlen($fullName) > 15 ? substr($fullName, 0, 12) . '...' : $fullName;

            // Status indicators
            $verified = $user['verified'] === 'true' ? '&a✓' : '&c✗';
            $banned = $user['banned'] === 'YES' ? '&c[BANNED]' : '';
            $role = self::getRoleName($user['role']);

            $line = sprintf(
                '&7%2d. &e%-15s &7| &b%-20s &7| &a%-15s &7| %s%s &7| &d%s %s',
                $displayIndex,
                $username,
                $email,
                $fullName,
                $verified,
                $banned,
                $role,
                '&7(ID: ' . $user['id'] . ')'
            );

            self::$cliApp->send($line);
        }

        self::$cliApp->send('');
    }

    private static function getRoleName(string $roleId): string
    {
        $roleName = Roles::getRoleNameById((int) $roleId, false);

        return $roleName ?? 'Unknown';
    }

    private static function showFooter(): void
    {
        self::$cliApp->send('&6╔══════════════════════════════════════════════════════════════╗');
        self::$cliApp->send('&6║ &7Commands: &e[number] &7edit | &es/search &7| &ec/clear &7| &er/refresh &6║');
        self::$cliApp->send('&6║ &7Navigation: &en/next &7| &ep/prev &7| &eq/quit                     &6║');
        self::$cliApp->send('&6╚══════════════════════════════════════════════════════════════╝');
        self::$cliApp->send('');
        self::$cliApp->send('&7Enter your choice: &e');
    }

    private static function handleSearch(): void
    {
        self::clearScreen();
        self::$cliApp->send('&6╔══════════════════════════════════════════════════════════════╗');
        self::$cliApp->send('&6║                        &eSearch Users                        &6║');
        self::$cliApp->send('&6╠══════════════════════════════════════════════════════════════╣');
        self::$cliApp->send('&6║  &7Search by username, email, name, or ID                    &6║');
        self::$cliApp->send('&6║  &7Enter search term (or press Enter to cancel): &e');
        self::$cliApp->send('&6╚══════════════════════════════════════════════════════════════╝');

        $search = trim(fgets(STDIN));

        if (!empty($search)) {
            self::$searchTerm = $search;
            self::applySearch();
        }
    }

    private static function editUser(array $user): void
    {
        while (true) {
            self::clearScreen();
            self::showUserDetails($user);

            $input = self::getUserInput();

            if ($input === 'q' || $input === 'quit' || $input === 'b' || $input === 'back') {
                break;
            } elseif ($input === '1') {
                self::editUserField($user, UserColumns::FIRST_NAME, 'First Name', true);
            } elseif ($input === '2') {
                self::editUserField($user, UserColumns::LAST_NAME, 'Last Name', true);
            } elseif ($input === '3') {
                self::editUserRole($user);
            } elseif ($input === '4') {
                self::editUserCredits($user);
            } elseif ($input === '5') {
                self::toggleUserVerification($user);
            } elseif ($input === '6') {
                self::toggleUserBan($user);
            } elseif ($input === '7') {
                self::toggleUserLock($user);
            } elseif ($input === '8') {
                self::toggleImageHosting($user);
            } elseif ($input === '9') {
                self::editResourceLimit($user, UserColumns::MEMORY_LIMIT, 'Memory Limit (MB)');
            } elseif ($input === 'a') {
                self::editResourceLimit($user, UserColumns::DISK_LIMIT, 'Disk Limit (MB)');
            } elseif ($input === 'b') {
                self::editResourceLimit($user, UserColumns::CPU_LIMIT, 'CPU Limit (%)');
            } elseif ($input === 'c') {
                self::editResourceLimit($user, UserColumns::SERVER_LIMIT, 'Server Limit');
            } elseif ($input === 'd') {
                self::editResourceLimit($user, UserColumns::BACKUP_LIMIT, 'Backup Limit');
            } elseif ($input === 'e') {
                self::editResourceLimit($user, UserColumns::DATABASE_LIMIT, 'Database Limit');
            } elseif ($input === 'f') {
                self::editResourceLimit($user, UserColumns::ALLOCATION_LIMIT, 'Allocation Limit');
            } elseif ($input === 'i') {
                self::toggle2FA($user);
            } elseif ($input === 'j') {
                self::toggle2FABlocked($user);
            } elseif ($input === 'k') {
                self::resetUserPassword($user);
            } elseif ($input === 'l') {
                self::regenerateSupportPin($user);
            } elseif ($input === 'm') {
                self::toggleDiscordLink($user);
            } elseif ($input === 'n') {
                self::toggleGitHubLink($user);
            } elseif ($input === 'o') {
                self::editPterodactylUserId($user);
            } elseif ($input === 'r' || $input === 'refresh') {
                // Refresh user data
                $token = $user[UserColumns::ACCOUNT_TOKEN];
                $user = self::refreshUserData($token);
                if (!$user) {
                    self::$cliApp->send('&cUser not found! Press any key to return...');
                    self::waitForInput();
                    break;
                }
            } else {
                self::$cliApp->send('&cInvalid input. Press any key to continue...');
                self::waitForInput();
            }
        }
    }

    private static function showUserDetails(array $user): void
    {
        $verified = $user['verified'] === 'true' ? '&aVerified' : '&cNot Verified';
        $banned = $user['banned'] === 'YES' ? '&cBanned' : '&aActive';
        $locked = ($user['locked'] ?? 'false') === 'true' ? '&cLocked' : '&aUnlocked';
        $imageHosting = ($user['image_hosting_enabled'] ?? 'false') === 'true' ? '&aEnabled' : '&cDisabled';
        $twoFaEnabled = ($user['2fa_enabled'] ?? 'false') === 'true' ? '&aEnabled' : '&cDisabled';
        $twoFaBlocked = ($user['2fa_blocked'] ?? 'false') === 'true' ? '&cBlocked' : '&aUnblocked';
        $discordLinked = ($user['discord_linked'] ?? 'false') === 'true' ? '&aLinked' : '&cNot Linked';
        $githubLinked = ($user['github_linked'] ?? 'false') === 'true' ? '&aLinked' : '&cNot Linked';
        $role = self::getRoleName($user['role']);

        self::$cliApp->send('&6╔══════════════════════════════════════════════════════════════╗');
        self::$cliApp->send('&6║                      &eEdit User Details                     &6║');
        self::$cliApp->send('&6╠══════════════════════════════════════════════════════════════╣');
        self::$cliApp->send('&6║  &7ID: &e' . $user['id'] . ' &7| UUID: &e' . substr($user['uuid'], 0, 8) . '... &6║');
        self::$cliApp->send('&6║  &7Username: &e' . $user['username'] . ' &7(read-only) &6║');
        self::$cliApp->send('&6║  &7Email: &e' . $user['email'] . ' &7(read-only) &6║');
        self::$cliApp->send('&6║  &7Support PIN: &e' . ($user['support_pin'] ?? 'None') . ' &6║');
        self::$cliApp->send('&6║                                                              &6║');
        self::$cliApp->send('&6║  &e--- Personal Information ---                             &6║');
        self::$cliApp->send('&6║  &71. First Name: &e' . $user['first_name'] . ' &6║');
        self::$cliApp->send('&6║  &72. Last Name: &e' . $user['last_name'] . ' &6║');
        self::$cliApp->send('&6║                                                              &6║');
        self::$cliApp->send('&6║  &e--- Account Settings ---                                 &6║');
        self::$cliApp->send('&6║  &73. Role: &d' . $role . ' &7(ID: ' . $user['role'] . ') &6║');
        self::$cliApp->send('&6║  &74. Credits: &a' . $user['credits'] . ' &6║');
        self::$cliApp->send('&6║  &75. Verification: ' . $verified . ' &6║');
        self::$cliApp->send('&6║  &76. Ban Status: ' . $banned . ' &6║');
        self::$cliApp->send('&6║  &77. Lock Status: ' . $locked . ' &6║');
        self::$cliApp->send('&6║  &78. Image Hosting: ' . $imageHosting . ' &6║');
        self::$cliApp->send('&6║                                                              &6║');
        self::$cliApp->send('&6║  &e--- Security & Authentication ---                        &6║');
        self::$cliApp->send('&6║  &7i. 2FA Status: ' . $twoFaEnabled . ' &7| &7j. 2FA Blocked: ' . $twoFaBlocked . ' &6║');
        self::$cliApp->send('&6║  &7k. Reset Password &7| &7l. Regenerate Support PIN &6║');
        self::$cliApp->send('&6║                                                              &6║');
        self::$cliApp->send('&6║  &e--- External Accounts ---                                &6║');
        self::$cliApp->send('&6║  &7m. Discord: ' . $discordLinked . ' &7| &7n. GitHub: ' . $githubLinked . ' &6║');
        self::$cliApp->send('&6║                                                              &6║');
        self::$cliApp->send('&6║  &e--- Resource Limits ---                                  &6║');
        self::$cliApp->send('&6║  &79. Memory: &b' . $user['memory_limit'] . ' MB &7| &7a. Disk: &b' . $user['disk_limit'] . ' MB &6║');
        self::$cliApp->send('&6║  &7b. CPU: &b' . $user['cpu_limit'] . '% &7| &7c. Servers: &b' . $user['server_limit'] . ' &6║');
        self::$cliApp->send('&6║  &7d. Backups: &b' . $user['backup_limit'] . ' &7| &7e. Databases: &b' . $user['database_limit'] . ' &6║');
        self::$cliApp->send('&6║  &7f. Allocations: &b' . $user['allocation_limit'] . ' &6║');
        self::$cliApp->send('&6║                                                              &6║');
        self::$cliApp->send('&6║  &e--- Panel Integration ---                                &6║');
        self::$cliApp->send('&6║  &7o. Pterodactyl User ID: &b' . ($user['pterodactyl_user_id'] ?? 'None') . ' &6║');
        self::$cliApp->send('&6║                                                              &6║');
        self::$cliApp->send('&6╠══════════════════════════════════════════════════════════════╣');
        self::$cliApp->send('&6║ &7Commands: &e[1-9,a-o] &7edit | &er/refresh &7| &eq/b/back/quit &6║');
        self::$cliApp->send('&6╚══════════════════════════════════════════════════════════════╝');
        self::$cliApp->send('');
        self::$cliApp->send('&7Enter your choice: &e');
    }

    private static function editUserField(array &$user, string $field, string $fieldName, bool $encrypted): void
    {
        $currentValue = $user[$field];

        self::clearScreen();
        self::$cliApp->send('&6╔══════════════════════════════════════════════════════════════╗');
        self::$cliApp->send('&6║                       &eEdit ' . $fieldName . ' &6║');
        self::$cliApp->send('&6╠══════════════════════════════════════════════════════════════╣');
        self::$cliApp->send('&6║  &7User: &e' . $user['username'] . ' &6║');
        self::$cliApp->send('&6║  &7Current ' . $fieldName . ': &a' . $currentValue . ' &6║');
        self::$cliApp->send('&6║                                                              &6║');
        self::$cliApp->send('&6║  &7Enter new value (or &cq &7to cancel): &e');
        self::$cliApp->send('&6╚══════════════════════════════════════════════════════════════╝');

        $newValue = trim(fgets(STDIN));

        if ($newValue === 'q' || $newValue === 'quit') {
            return;
        }

        if (empty($newValue)) {
            self::$cliApp->send('&cValue cannot be empty. Press any key to continue...');
            self::waitForInput();

            return;
        }

        try {
            $token = $user[UserColumns::ACCOUNT_TOKEN];
            $result = User::updateInfo($token, $field, $newValue, $encrypted);

            if ($result) {
                $user[$field] = $newValue; // Update local array
                self::$cliApp->send('&a✓ ' . $fieldName . ' updated successfully!');
                self::$cliApp->send('&7Press any key to continue...');
                self::waitForInput();
            } else {
                self::$cliApp->send('&c✗ Failed to update ' . $fieldName . '. Press any key to continue...');
                self::waitForInput();
            }
        } catch (\Exception $e) {
            self::$cliApp->send('&c✗ Error updating ' . $fieldName . ': ' . $e->getMessage());
            self::$cliApp->send('&7Press any key to continue...');
            self::waitForInput();
        }
    }

    private static function editUserRole(array &$user): void
    {
        // Get available roles from database
        $availableRoles = Roles::getList();

        if (empty($availableRoles)) {
            self::$cliApp->send('&cNo roles available in the system. Press any key to continue...');
            self::waitForInput();

            return;
        }

        while (true) {
            self::clearScreen();
            self::$cliApp->send('&6╔══════════════════════════════════════════════════════════════╗');
            self::$cliApp->send('&6║                       &eSelect Role                          &6║');
            self::$cliApp->send('&6╠══════════════════════════════════════════════════════════════╣');
            self::$cliApp->send('&6║  &7User: &e' . $user['username'] . ' &6║');
            self::$cliApp->send('&6║  &7Current Role: &d' . self::getRoleName($user['role']) . ' &7(ID: ' . $user['role'] . ') &6║');
            self::$cliApp->send('&6║                                                              &6║');

            // Display available roles
            $displayCount = 0;
            foreach ($availableRoles as $role) {
                ++$displayCount;
                $roleName = $role['name'] ?? 'Unknown';
                $roleColor = isset($role['color']) ? '&' . $role['color'] : '&7';
                self::$cliApp->send('&6║  &7' . sprintf('%2d', $displayCount) . '. ' . $roleColor . $roleName . ' &7(ID: ' . $role['id'] . ') &6║');
            }

            self::$cliApp->send('&6║                                                              &6║');
            self::$cliApp->send('&6║  &7Enter role number or &cq &7to cancel: &e');
            self::$cliApp->send('&6╚══════════════════════════════════════════════════════════════╝');

            $input = trim(fgets(STDIN));

            if ($input === 'q' || $input === 'quit') {
                return;
            }

            if (!is_numeric($input) || $input < 1 || $input > count($availableRoles)) {
                self::$cliApp->send('&cInvalid role number. Press any key to continue...');
                self::waitForInput();
                continue;
            }

            $selectedRoleIndex = (int) $input - 1;
            $selectedRole = $availableRoles[$selectedRoleIndex];
            $roleId = $selectedRole['id'];

            try {
                $token = $user[UserColumns::ACCOUNT_TOKEN];
                $result = User::updateInfo($token, UserColumns::ROLE_ID, (string) $roleId, false);

                if ($result) {
                    $user['role'] = (string) $roleId; // Update local array
                    self::$cliApp->send('&a✓ Role updated to ' . $selectedRole['name'] . ' successfully!');
                    self::$cliApp->send('&7Press any key to continue...');
                    self::waitForInput();

                    return;
                }
                self::$cliApp->send('&c✗ Failed to update role. Press any key to continue...');
                self::waitForInput();

            } catch (\Exception $e) {
                self::$cliApp->send('&c✗ Error updating role: ' . $e->getMessage());
                self::$cliApp->send('&7Press any key to continue...');
                self::waitForInput();
            }
        }
    }

    private static function editUserCredits(array &$user): void
    {
        self::clearScreen();
        self::$cliApp->send('&6╔══════════════════════════════════════════════════════════════╗');
        self::$cliApp->send('&6║                      &eEdit Credits                          &6║');
        self::$cliApp->send('&6╠══════════════════════════════════════════════════════════════╣');
        self::$cliApp->send('&6║  &7User: &e' . $user['username'] . ' &6║');
        self::$cliApp->send('&6║  &7Current Credits: &a' . $user['credits'] . ' &6║');
        self::$cliApp->send('&6║                                                              &6║');
        self::$cliApp->send('&6║  &7Enter new credit amount (or &cq &7to cancel): &e');
        self::$cliApp->send('&6╚══════════════════════════════════════════════════════════════╝');

        $input = trim(fgets(STDIN));

        if ($input === 'q' || $input === 'quit') {
            return;
        }

        if (!is_numeric($input) || $input < 0) {
            self::$cliApp->send('&cInvalid credit amount. Must be a positive number. Press any key to continue...');
            self::waitForInput();

            return;
        }

        try {
            $token = $user[UserColumns::ACCOUNT_TOKEN];
            $result = User::updateInfo($token, UserColumns::CREDITS, $input, false);

            if ($result) {
                $user['credits'] = $input; // Update local array
                self::$cliApp->send('&a✓ Credits updated to ' . $input . ' successfully!');
                self::$cliApp->send('&7Press any key to continue...');
                self::waitForInput();
            } else {
                self::$cliApp->send('&c✗ Failed to update credits. Press any key to continue...');
                self::waitForInput();
            }
        } catch (\Exception $e) {
            self::$cliApp->send('&c✗ Error updating credits: ' . $e->getMessage());
            self::$cliApp->send('&7Press any key to continue...');
            self::waitForInput();
        }
    }

    private static function toggleUserVerification(array &$user): void
    {
        $currentStatus = $user['verified'] === 'true';
        $newStatus = $currentStatus ? 'false' : 'true';
        $statusText = $currentStatus ? 'unverified' : 'verified';

        self::clearScreen();
        self::$cliApp->send('&6╔══════════════════════════════════════════════════════════════╗');
        self::$cliApp->send('&6║                  &eToggle Verification                       &6║');
        self::$cliApp->send('&6╠══════════════════════════════════════════════════════════════╣');
        self::$cliApp->send('&6║  &7User: &e' . $user['username'] . ' &6║');
        self::$cliApp->send('&6║  &7Current Status: ' . ($currentStatus ? '&aVerified' : '&cNot Verified') . ' &6║');
        self::$cliApp->send('&6║                                                              &6║');
        self::$cliApp->send('&6║  &7Set user as ' . ($currentStatus ? '&cunverified' : '&averified') . '? (y/N): &e');
        self::$cliApp->send('&6╚══════════════════════════════════════════════════════════════╝');

        $input = trim(strtolower(fgets(STDIN)));

        if ($input !== 'y' && $input !== 'yes') {
            return;
        }

        try {
            $token = $user[UserColumns::ACCOUNT_TOKEN];
            $result = User::updateInfo($token, UserColumns::VERIFIED, $newStatus, false);

            if ($result) {
                $user['verified'] = $newStatus; // Update local array
                self::$cliApp->send('&a✓ User is now ' . $statusText . '!');
                self::$cliApp->send('&7Press any key to continue...');
                self::waitForInput();
            } else {
                self::$cliApp->send('&c✗ Failed to update verification status. Press any key to continue...');
                self::waitForInput();
            }
        } catch (\Exception $e) {
            self::$cliApp->send('&c✗ Error updating verification: ' . $e->getMessage());
            self::$cliApp->send('&7Press any key to continue...');
            self::waitForInput();
        }
    }

    private static function toggleUserBan(array &$user): void
    {
        $currentlyBanned = $user['banned'] === 'YES';
        $newStatus = $currentlyBanned ? 'NO' : 'YES';
        $statusText = $currentlyBanned ? 'unbanned' : 'banned';

        self::clearScreen();
        self::$cliApp->send('&6╔══════════════════════════════════════════════════════════════╗');
        self::$cliApp->send('&6║                    &eToggle Ban Status                       &6║');
        self::$cliApp->send('&6╠══════════════════════════════════════════════════════════════╣');
        self::$cliApp->send('&6║  &7User: &e' . $user['username'] . ' &6║');
        self::$cliApp->send('&6║  &7Current Status: ' . ($currentlyBanned ? '&cBanned' : '&aActive') . ' &6║');
        self::$cliApp->send('&6║                                                              &6║');
        self::$cliApp->send('&6║  &7' . ($currentlyBanned ? 'Unban' : 'Ban') . ' this user? (y/N): &e');
        self::$cliApp->send('&6╚══════════════════════════════════════════════════════════════╝');

        $input = trim(strtolower(fgets(STDIN)));

        if ($input !== 'y' && $input !== 'yes') {
            return;
        }

        try {
            $token = $user[UserColumns::ACCOUNT_TOKEN];
            $result = User::updateInfo($token, UserColumns::BANNED, $newStatus, false);

            if ($result) {
                $user['banned'] = $newStatus; // Update local array
                self::$cliApp->send('&a✓ User has been ' . $statusText . '!');
                self::$cliApp->send('&7Press any key to continue...');
                self::waitForInput();
            } else {
                self::$cliApp->send('&c✗ Failed to update ban status. Press any key to continue...');
                self::waitForInput();
            }
        } catch (\Exception $e) {
            self::$cliApp->send('&c✗ Error updating ban status: ' . $e->getMessage());
            self::$cliApp->send('&7Press any key to continue...');
            self::waitForInput();
        }
    }

    private static function toggleUserLock(array &$user): void
    {
        $currentlyLocked = ($user['locked'] ?? 'false') === 'true';
        $newStatus = $currentlyLocked ? 'false' : 'true';
        $statusText = $currentlyLocked ? 'unlocked' : 'locked';

        self::clearScreen();
        self::$cliApp->send('&6╔══════════════════════════════════════════════════════════════╗');
        self::$cliApp->send('&6║                    &eToggle Lock Status                       &6║');
        self::$cliApp->send('&6╠══════════════════════════════════════════════════════════════╣');
        self::$cliApp->send('&6║  &7User: &e' . $user['username'] . ' &6║');
        self::$cliApp->send('&6║  &7Current Status: ' . ($currentlyLocked ? '&cLocked' : '&aUnlocked') . ' &6║');
        self::$cliApp->send('&6║                                                              &6║');
        self::$cliApp->send('&6║  &7' . ($currentlyLocked ? 'Unlock' : 'Lock') . ' this user account? (y/N): &e');
        self::$cliApp->send('&6╚══════════════════════════════════════════════════════════════╝');

        $input = trim(strtolower(fgets(STDIN)));

        if ($input !== 'y' && $input !== 'yes') {
            return;
        }

        try {
            $token = $user[UserColumns::ACCOUNT_TOKEN];
            $result = User::updateInfo($token, 'locked', $newStatus, false);

            if ($result) {
                $user['locked'] = $newStatus; // Update local array
                self::$cliApp->send('&a✓ User account has been ' . $statusText . '!');
                self::$cliApp->send('&7Press any key to continue...');
                self::waitForInput();
            } else {
                self::$cliApp->send('&c✗ Failed to update lock status. Press any key to continue...');
                self::waitForInput();
            }
        } catch (\Exception $e) {
            self::$cliApp->send('&c✗ Error updating lock status: ' . $e->getMessage());
            self::$cliApp->send('&7Press any key to continue...');
            self::waitForInput();
        }
    }

    private static function toggleImageHosting(array &$user): void
    {
        $currentlyEnabled = ($user['image_hosting_enabled'] ?? 'false') === 'true';
        $newStatus = $currentlyEnabled ? 'false' : 'true';
        $statusText = $currentlyEnabled ? 'disabled' : 'enabled';

        self::clearScreen();
        self::$cliApp->send('&6╔══════════════════════════════════════════════════════════════╗');
        self::$cliApp->send('&6║                 &eToggle Image Hosting                       &6║');
        self::$cliApp->send('&6╠══════════════════════════════════════════════════════════════╣');
        self::$cliApp->send('&6║  &7User: &e' . $user['username'] . ' &6║');
        self::$cliApp->send('&6║  &7Current Status: ' . ($currentlyEnabled ? '&aEnabled' : '&cDisabled') . ' &6║');
        self::$cliApp->send('&6║                                                              &6║');
        self::$cliApp->send('&6║  &7' . ($currentlyEnabled ? 'Disable' : 'Enable') . ' image hosting for this user? (y/N): &e');
        self::$cliApp->send('&6╚══════════════════════════════════════════════════════════════╝');

        $input = trim(strtolower(fgets(STDIN)));

        if ($input !== 'y' && $input !== 'yes') {
            return;
        }

        try {
            $token = $user[UserColumns::ACCOUNT_TOKEN];
            $result = User::updateInfo($token, UserColumns::IMAGE_HOSTING_ENABLED, $newStatus, false);

            if ($result) {
                $user['image_hosting_enabled'] = $newStatus; // Update local array
                self::$cliApp->send('&a✓ Image hosting has been ' . $statusText . '!');
                self::$cliApp->send('&7Press any key to continue...');
                self::waitForInput();
            } else {
                self::$cliApp->send('&c✗ Failed to update image hosting status. Press any key to continue...');
                self::waitForInput();
            }
        } catch (\Exception $e) {
            self::$cliApp->send('&c✗ Error updating image hosting status: ' . $e->getMessage());
            self::$cliApp->send('&7Press any key to continue...');
            self::waitForInput();
        }
    }

    private static function editResourceLimit(array &$user, string $field, string $fieldName): void
    {
        $currentValue = $user[$field] ?? '0';

        self::clearScreen();
        self::$cliApp->send('&6╔══════════════════════════════════════════════════════════════╗');
        self::$cliApp->send('&6║                     &eEdit ' . $fieldName . ' &6║');
        self::$cliApp->send('&6╠══════════════════════════════════════════════════════════════╣');
        self::$cliApp->send('&6║  &7User: &e' . $user['username'] . ' &6║');
        self::$cliApp->send('&6║  &7Current ' . $fieldName . ': &a' . $currentValue . ' &6║');
        self::$cliApp->send('&6║                                                              &6║');
        self::$cliApp->send('&6║  &7Enter new value (0 for unlimited, &cq &7to cancel): &e');
        self::$cliApp->send('&6╚══════════════════════════════════════════════════════════════╝');

        $input = trim(fgets(STDIN));

        if ($input === 'q' || $input === 'quit') {
            return;
        }

        if (!is_numeric($input) || $input < 0) {
            self::$cliApp->send('&cInvalid value. Must be a non-negative number. Press any key to continue...');
            self::waitForInput();

            return;
        }

        try {
            $token = $user[UserColumns::ACCOUNT_TOKEN];
            $result = User::updateInfo($token, $field, $input, false);

            if ($result) {
                $user[$field] = $input; // Update local array
                $displayValue = $input == 0 ? 'unlimited' : $input;
                self::$cliApp->send('&a✓ ' . $fieldName . ' updated to ' . $displayValue . ' successfully!');
                self::$cliApp->send('&7Press any key to continue...');
                self::waitForInput();
            } else {
                self::$cliApp->send('&c✗ Failed to update ' . $fieldName . '. Press any key to continue...');
                self::waitForInput();
            }
        } catch (\Exception $e) {
            self::$cliApp->send('&c✗ Error updating ' . $fieldName . ': ' . $e->getMessage());
            self::$cliApp->send('&7Press any key to continue...');
            self::waitForInput();
        }
    }

    private static function refreshUserData(string $token): ?array
    {
        $columns = [
            UserColumns::ID,
            UserColumns::USERNAME,
            UserColumns::EMAIL,
            UserColumns::FIRST_NAME,
            UserColumns::LAST_NAME,
            UserColumns::ROLE_ID,
            UserColumns::VERIFIED,
            UserColumns::BANNED,
            UserColumns::CREDITS,
            UserColumns::UUID,
            UserColumns::ACCOUNT_TOKEN,
            UserColumns::LAST_SEEN,
            UserColumns::DELETED,
            // Resource limits
            UserColumns::MEMORY_LIMIT,
            UserColumns::DISK_LIMIT,
            UserColumns::CPU_LIMIT,
            UserColumns::SERVER_LIMIT,
            UserColumns::BACKUP_LIMIT,
            UserColumns::DATABASE_LIMIT,
            UserColumns::ALLOCATION_LIMIT,
            // Additional status fields
            'locked',
            UserColumns::IMAGE_HOSTING_ENABLED,
            UserColumns::TWO_FA_ENABLED,
            UserColumns::TWO_FA_BLOCKED,
            UserColumns::SUPPORT_PIN,
            UserColumns::DISCORD_LINKED,
            UserColumns::GITHUB_LINKED,
            UserColumns::PTERODACTYL_USER_ID,
        ];

        $encrypted = [
            UserColumns::FIRST_NAME,
            UserColumns::LAST_NAME,
        ];

        return User::getInfoArray($token, $columns, $encrypted);
    }

    private static function toggle2FA(array &$user): void
    {
        $currentlyEnabled = ($user['2fa_enabled'] ?? 'false') === 'true';
        $newStatus = $currentlyEnabled ? 'false' : 'true';
        $statusText = $currentlyEnabled ? 'disabled' : 'enabled';

        self::clearScreen();
        self::$cliApp->send('&6╔══════════════════════════════════════════════════════════════╗');
        self::$cliApp->send('&6║                     &eToggle 2FA Status                      &6║');
        self::$cliApp->send('&6╠══════════════════════════════════════════════════════════════╣');
        self::$cliApp->send('&6║  &7User: &e' . $user['username'] . ' &6║');
        self::$cliApp->send('&6║  &7Current 2FA: ' . ($currentlyEnabled ? '&aEnabled' : '&cDisabled') . ' &6║');
        self::$cliApp->send('&6║                                                              &6║');
        self::$cliApp->send('&6║  &7' . ($currentlyEnabled ? 'Disable' : 'Enable') . ' 2FA for this user? (y/N): &e');
        if ($currentlyEnabled) {
            self::$cliApp->send('&6║  &c⚠ Warning: This will remove their 2FA protection! &6║');
        }
        self::$cliApp->send('&6╚══════════════════════════════════════════════════════════════╝');

        $input = trim(strtolower(fgets(STDIN)));

        if ($input !== 'y' && $input !== 'yes') {
            return;
        }

        try {
            $token = $user[UserColumns::ACCOUNT_TOKEN];
            $result = User::updateInfo($token, UserColumns::TWO_FA_ENABLED, $newStatus, false);

            if ($result) {
                $user['2fa_enabled'] = $newStatus; // Update local array
                self::$cliApp->send('&a✓ 2FA has been ' . $statusText . '!');
                self::$cliApp->send('&7Press any key to continue...');
                self::waitForInput();
            } else {
                self::$cliApp->send('&c✗ Failed to update 2FA status. Press any key to continue...');
                self::waitForInput();
            }
        } catch (\Exception $e) {
            self::$cliApp->send('&c✗ Error updating 2FA status: ' . $e->getMessage());
            self::$cliApp->send('&7Press any key to continue...');
            self::waitForInput();
        }
    }

    private static function toggle2FABlocked(array &$user): void
    {
        $currentlyBlocked = ($user['2fa_blocked'] ?? 'false') === 'true';
        $newStatus = $currentlyBlocked ? 'false' : 'true';
        $statusText = $currentlyBlocked ? 'unblocked' : 'blocked';

        self::clearScreen();
        self::$cliApp->send('&6╔══════════════════════════════════════════════════════════════╗');
        self::$cliApp->send('&6║                   &eToggle 2FA Block Status                  &6║');
        self::$cliApp->send('&6╠══════════════════════════════════════════════════════════════╣');
        self::$cliApp->send('&6║  &7User: &e' . $user['username'] . ' &6║');
        self::$cliApp->send('&6║  &7Current Status: ' . ($currentlyBlocked ? '&cBlocked' : '&aUnblocked') . ' &6║');
        self::$cliApp->send('&6║                                                              &6║');
        self::$cliApp->send('&6║  &7' . ($currentlyBlocked ? 'Unblock' : 'Block') . ' 2FA for this user? (y/N): &e');
        if (!$currentlyBlocked) {
            self::$cliApp->send('&6║  &c⚠ Warning: This will prevent 2FA usage! &6║');
        }
        self::$cliApp->send('&6╚══════════════════════════════════════════════════════════════╝');

        $input = trim(strtolower(fgets(STDIN)));

        if ($input !== 'y' && $input !== 'yes') {
            return;
        }

        try {
            $token = $user[UserColumns::ACCOUNT_TOKEN];
            $result = User::updateInfo($token, UserColumns::TWO_FA_BLOCKED, $newStatus, false);

            if ($result) {
                $user['2fa_blocked'] = $newStatus; // Update local array
                self::$cliApp->send('&a✓ 2FA has been ' . $statusText . '!');
                self::$cliApp->send('&7Press any key to continue...');
                self::waitForInput();
            } else {
                self::$cliApp->send('&c✗ Failed to update 2FA block status. Press any key to continue...');
                self::waitForInput();
            }
        } catch (\Exception $e) {
            self::$cliApp->send('&c✗ Error updating 2FA block status: ' . $e->getMessage());
            self::$cliApp->send('&7Press any key to continue...');
            self::waitForInput();
        }
    }

    private static function resetUserPassword(array &$user): void
    {
        self::clearScreen();
        self::$cliApp->send('&6╔══════════════════════════════════════════════════════════════╗');
        self::$cliApp->send('&6║                     &eReset User Password                    &6║');
        self::$cliApp->send('&6╠══════════════════════════════════════════════════════════════╣');
        self::$cliApp->send('&6║  &7User: &e' . $user['username'] . ' &6║');
        self::$cliApp->send('&6║  &7Email: &e' . $user['email'] . ' &6║');
        self::$cliApp->send('&6║                                                              &6║');
        self::$cliApp->send('&6║  &7Enter new password (min 8 chars, &cq &7to cancel): &e');
        self::$cliApp->send('&6╚══════════════════════════════════════════════════════════════╝');

        $password = trim(fgets(STDIN));

        if ($password === 'q' || $password === 'quit') {
            return;
        }

        if (strlen($password) < 8) {
            self::$cliApp->send('&cPassword must be at least 8 characters long. Press any key to continue...');
            self::waitForInput();

            return;
        }

        // Confirm password
        self::$cliApp->send('&7Confirm new password: &e');
        $passwordConfirm = trim(fgets(STDIN));

        if ($password !== $passwordConfirm) {
            self::$cliApp->send('&cPasswords do not match. Press any key to continue...');
            self::waitForInput();

            return;
        }

        try {
            $token = $user[UserColumns::ACCOUNT_TOKEN];
            $result = User::updateInfo($token, UserColumns::PASSWORD, $password, true); // Password is encrypted

            if ($result) {
                self::$cliApp->send('&a✓ Password has been reset successfully!');
                self::$cliApp->send('&7Press any key to continue...');
                self::waitForInput();
            } else {
                self::$cliApp->send('&c✗ Failed to reset password. Press any key to continue...');
                self::waitForInput();
            }
        } catch (\Exception $e) {
            self::$cliApp->send('&c✗ Error resetting password: ' . $e->getMessage());
            self::$cliApp->send('&7Press any key to continue...');
            self::waitForInput();
        }
    }

    private static function regenerateSupportPin(array &$user): void
    {
        self::clearScreen();
        self::$cliApp->send('&6╔══════════════════════════════════════════════════════════════╗');
        self::$cliApp->send('&6║                &eRegenerate Support PIN                      &6║');
        self::$cliApp->send('&6╠══════════════════════════════════════════════════════════════╣');
        self::$cliApp->send('&6║  &7User: &e' . $user['username'] . ' &6║');
        self::$cliApp->send('&6║  &7Current PIN: &e' . ($user['support_pin'] ?? 'None') . ' &6║');
        self::$cliApp->send('&6║                                                              &6║');
        self::$cliApp->send('&6║  &7Generate new support PIN? (y/N): &e');
        self::$cliApp->send('&6╚══════════════════════════════════════════════════════════════╝');

        $input = trim(strtolower(fgets(STDIN)));

        if ($input !== 'y' && $input !== 'yes') {
            return;
        }

        try {
            $newPin = \MythicalDash\App::getInstance(true)->generatePin();
            $token = $user[UserColumns::ACCOUNT_TOKEN];
            $result = User::updateInfo($token, UserColumns::SUPPORT_PIN, $newPin, false);

            if ($result) {
                $user['support_pin'] = $newPin; // Update local array
                self::$cliApp->send('&a✓ Support PIN regenerated successfully!');
                self::$cliApp->send('&7New PIN: &e' . $newPin);
                self::$cliApp->send('&7Press any key to continue...');
                self::waitForInput();
            } else {
                self::$cliApp->send('&c✗ Failed to regenerate support PIN. Press any key to continue...');
                self::waitForInput();
            }
        } catch (\Exception $e) {
            self::$cliApp->send('&c✗ Error regenerating support PIN: ' . $e->getMessage());
            self::$cliApp->send('&7Press any key to continue...');
            self::waitForInput();
        }
    }

    private static function toggleDiscordLink(array &$user): void
    {
        $currentlyLinked = ($user['discord_linked'] ?? 'false') === 'true';
        $newStatus = $currentlyLinked ? 'false' : 'true';
        $statusText = $currentlyLinked ? 'unlinked' : 'linked';

        self::clearScreen();
        self::$cliApp->send('&6╔══════════════════════════════════════════════════════════════╗');
        self::$cliApp->send('&6║                   &eToggle Discord Link                      &6║');
        self::$cliApp->send('&6╠══════════════════════════════════════════════════════════════╣');
        self::$cliApp->send('&6║  &7User: &e' . $user['username'] . ' &6║');
        self::$cliApp->send('&6║  &7Current Status: ' . ($currentlyLinked ? '&aLinked' : '&cNot Linked') . ' &6║');
        self::$cliApp->send('&6║                                                              &6║');
        self::$cliApp->send('&6║  &7' . ($currentlyLinked ? 'Unlink' : 'Link') . ' Discord account? (y/N): &e');
        self::$cliApp->send('&6╚══════════════════════════════════════════════════════════════╝');

        $input = trim(strtolower(fgets(STDIN)));

        if ($input !== 'y' && $input !== 'yes') {
            return;
        }

        try {
            $token = $user[UserColumns::ACCOUNT_TOKEN];
            $result = User::updateInfo($token, UserColumns::DISCORD_LINKED, $newStatus, false);

            if ($result) {
                $user['discord_linked'] = $newStatus; // Update local array
                self::$cliApp->send('&a✓ Discord account has been ' . $statusText . '!');
                self::$cliApp->send('&7Press any key to continue...');
                self::waitForInput();
            } else {
                self::$cliApp->send('&c✗ Failed to update Discord link status. Press any key to continue...');
                self::waitForInput();
            }
        } catch (\Exception $e) {
            self::$cliApp->send('&c✗ Error updating Discord link status: ' . $e->getMessage());
            self::$cliApp->send('&7Press any key to continue...');
            self::waitForInput();
        }
    }

    private static function toggleGitHubLink(array &$user): void
    {
        $currentlyLinked = ($user['github_linked'] ?? 'false') === 'true';
        $newStatus = $currentlyLinked ? 'false' : 'true';
        $statusText = $currentlyLinked ? 'unlinked' : 'linked';

        self::clearScreen();
        self::$cliApp->send('&6╔══════════════════════════════════════════════════════════════╗');
        self::$cliApp->send('&6║                    &eToggle GitHub Link                      &6║');
        self::$cliApp->send('&6╠══════════════════════════════════════════════════════════════╣');
        self::$cliApp->send('&6║  &7User: &e' . $user['username'] . ' &6║');
        self::$cliApp->send('&6║  &7Current Status: ' . ($currentlyLinked ? '&aLinked' : '&cNot Linked') . ' &6║');
        self::$cliApp->send('&6║                                                              &6║');
        self::$cliApp->send('&6║  &7' . ($currentlyLinked ? 'Unlink' : 'Link') . ' GitHub account? (y/N): &e');
        self::$cliApp->send('&6╚══════════════════════════════════════════════════════════════╝');

        $input = trim(strtolower(fgets(STDIN)));

        if ($input !== 'y' && $input !== 'yes') {
            return;
        }

        try {
            $token = $user[UserColumns::ACCOUNT_TOKEN];
            $result = User::updateInfo($token, UserColumns::GITHUB_LINKED, $newStatus, false);

            if ($result) {
                $user['github_linked'] = $newStatus; // Update local array
                self::$cliApp->send('&a✓ GitHub account has been ' . $statusText . '!');
                self::$cliApp->send('&7Press any key to continue...');
                self::waitForInput();
            } else {
                self::$cliApp->send('&c✗ Failed to update GitHub link status. Press any key to continue...');
                self::waitForInput();
            }
        } catch (\Exception $e) {
            self::$cliApp->send('&c✗ Error updating GitHub link status: ' . $e->getMessage());
            self::$cliApp->send('&7Press any key to continue...');
            self::waitForInput();
        }
    }

    private static function editPterodactylUserId(array &$user): void
    {
        $currentValue = $user['pterodactyl_user_id'] ?? '0';

        self::clearScreen();
        self::$cliApp->send('&6╔══════════════════════════════════════════════════════════════╗');
        self::$cliApp->send('&6║                 &eEdit Pterodactyl User ID                   &6║');
        self::$cliApp->send('&6╠══════════════════════════════════════════════════════════════╣');
        self::$cliApp->send('&6║  &7User: &e' . $user['username'] . ' &6║');
        self::$cliApp->send('&6║  &7Current ID: &a' . $currentValue . ' &6║');
        self::$cliApp->send('&6║                                                              &6║');
        self::$cliApp->send('&6║  &7Enter new Pterodactyl User ID (&cq &7to cancel): &e');
        self::$cliApp->send('&6╚══════════════════════════════════════════════════════════════╝');

        $input = trim(fgets(STDIN));

        if ($input === 'q' || $input === 'quit') {
            return;
        }

        if (!is_numeric($input) || $input < 0) {
            self::$cliApp->send('&cInvalid ID. Must be a non-negative number. Press any key to continue...');
            self::waitForInput();

            return;
        }

        try {
            $token = $user[UserColumns::ACCOUNT_TOKEN];
            $result = User::updateInfo($token, UserColumns::PTERODACTYL_USER_ID, $input, false);

            if ($result) {
                $user['pterodactyl_user_id'] = $input; // Update local array
                self::$cliApp->send('&a✓ Pterodactyl User ID updated to ' . $input . ' successfully!');
                self::$cliApp->send('&7Press any key to continue...');
                self::waitForInput();
            } else {
                self::$cliApp->send('&c✗ Failed to update Pterodactyl User ID. Press any key to continue...');
                self::waitForInput();
            }
        } catch (\Exception $e) {
            self::$cliApp->send('&c✗ Error updating Pterodactyl User ID: ' . $e->getMessage());
            self::$cliApp->send('&7Press any key to continue...');
            self::waitForInput();
        }
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

    private static function getUserInput(): string
    {
        $handle = fopen('php://stdin', 'r');
        $input = trim(fgets($handle));
        fclose($handle);

        return strtolower($input);
    }

    private static function waitForInput(): void
    {
        $handle = fopen('php://stdin', 'r');
        fgets($handle);
        fclose($handle);
    }
}
