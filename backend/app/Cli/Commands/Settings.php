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
use MythicalDash\Cli\CommandBuilder;
use MythicalDash\Config\ConfigFactory;

class Settings extends App implements CommandBuilder
{
    private static $cliApp;
    private static $config;
    private static $settings = [];
    private static $currentIndex = 0;
    private static $pageSize = 10;
    private static $currentPage = 0;

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
            self::$settings = array_values(self::$config->getConfigurableSettings());

            self::showMainMenu();
        } catch (\Exception $e) {
            self::$cliApp->send('&cAn error occurred while connecting to the database: ' . $e->getMessage());
            exit;
        }
    }

    public static function getDescription(): string
    {
        return 'Interactive settings configuration with a beautiful UI!';
    }

    public static function getSubCommands(): array
    {
        return [];
    }

    private static function showMainMenu(): void
    {
        while (true) {
            self::clearScreen();
            self::showHeader();
            self::showSettingsList();
            self::showFooter();

            $input = self::getUserInput();

            if ($input === 'q' || $input === 'quit') {
                self::$cliApp->send('&aGoodbye!');
                exit;
            } elseif ($input === 'n' || $input === 'next') {
                self::nextPage();
            } elseif ($input === 'p' || $input === 'prev') {
                self::prevPage();
            } elseif (is_numeric($input)) {
                $index = (int) $input - 1;
                if ($index >= 0 && $index < count(self::$settings)) {
                    self::editSetting($index);
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
        self::$cliApp->send('&6╔══════════════════════════════════════════════════════════════╗');
        self::$cliApp->send('&6║                    &eMythicalDash Settings                    &6║');
        self::$cliApp->send('&6╠══════════════════════════════════════════════════════════════╣');
        self::$cliApp->send('&6║  &7Total Settings: &e' . count(self::$settings) . ' &7| Page: &e' . (self::$currentPage + 1) . '/' . ceil(count(self::$settings) / self::$pageSize) . ' &6║');
        self::$cliApp->send('&6╚══════════════════════════════════════════════════════════════╝');
        self::$cliApp->send('');
    }

    private static function showSettingsList(): void
    {
        $startIndex = self::$currentPage * self::$pageSize;
        $endIndex = min($startIndex + self::$pageSize, count(self::$settings));

        for ($i = $startIndex; $i < $endIndex; ++$i) {
            $setting = self::$settings[$i];
            $currentValue = self::$config->getDBSetting($setting, 'DEFAULT');
            $displayValue = strlen($currentValue) > 30 ? substr($currentValue, 0, 27) . '...' : $currentValue;

            $number = $i + 1;
            $line = sprintf('&7%2d. &e%-25s &7→ &a%s', $number, $setting, $displayValue);
            self::$cliApp->send($line);
        }

        self::$cliApp->send('');
    }

    private static function showFooter(): void
    {
        self::$cliApp->send('&6╔══════════════════════════════════════════════════════════════╗');
        self::$cliApp->send('&6║  &7Commands: &e[number] &7to edit | &en/next &7| &ep/prev &7| &eq/quit &6║');
        self::$cliApp->send('&6╚══════════════════════════════════════════════════════════════╝');
        self::$cliApp->send('');
        self::$cliApp->send('&7Enter your choice: &e');
    }

    private static function editSetting(int $index): void
    {
        $setting = self::$settings[$index];
        $currentValue = self::$config->getDBSetting($setting, 'DEFAULT');

        while (true) {
            self::clearScreen();
            self::$cliApp->send('&6╔══════════════════════════════════════════════════════════════╗');
            self::$cliApp->send('&6║                        &eEdit Setting                        &6║');
            self::$cliApp->send('&6╠══════════════════════════════════════════════════════════════╣');
            self::$cliApp->send('&6║  &7Setting: &e' . $setting . ' &6║');
            self::$cliApp->send('&6║  &7Current Value: &a' . $currentValue . ' &6║');
            self::$cliApp->send('&6╠══════════════════════════════════════════════════════════════╣');
            self::$cliApp->send('&6║  &7Enter new value (or &cq &7to cancel): &e');
            self::$cliApp->send('&6╚══════════════════════════════════════════════════════════════╝');

            $newValue = self::getUserInput();

            if ($newValue === 'q' || $newValue === 'quit') {
                break;
            }

            if (empty($newValue)) {
                self::$cliApp->send('&cValue cannot be empty. Press any key to continue...');
                self::waitForInput();
                continue;
            }

            try {
                $result = self::$config->setSetting($setting, $newValue);
                if ($result) {
                    self::$cliApp->send('&a✓ Setting updated successfully!');
                    self::$cliApp->send('&7Press any key to continue...');
                    self::waitForInput();
                    break;
                }
                self::$cliApp->send('&c✗ Failed to update setting. Press any key to continue...');
                self::waitForInput();

            } catch (\Exception $e) {
                self::$cliApp->send('&c✗ Error updating setting: ' . $e->getMessage());
                self::$cliApp->send('&7Press any key to continue...');
                self::waitForInput();
            }
        }
    }

    private static function nextPage(): void
    {
        $maxPage = ceil(count(self::$settings) / self::$pageSize) - 1;
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
