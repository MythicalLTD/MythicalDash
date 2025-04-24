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

namespace MythicalDash\Cli;

use MythicalDash\Cli\Commands\Help;

class App extends \MythicalDash\Hooks\MythicalSystems\Utils\BungeeChatApi
{
    public $prefix = '&7[&5&lMythical&d&lDash&7] &8&l| &7';
    public $bars = '&7&m-----------------------------------------------------&r';
    public static App $instance;

    public function __construct(string $commandName, array $args)
    {
        $this->handleCustomCommands($commandName, $args);
        self::$instance = $this;

        if (getcwd() !== '/var/www/mythicaldash-v3') {
            exit('We detected that you are not running this command from the root directory of MythicalDash. Please run this command from the root directory.');
        }

        // Try plugin commands first, then fall back to built-in commands
        if ($this->registerPluginCommands($commandName, $args)) {
            return;
        }

        $this->registerBuiltInCommands($commandName, $args);
    }

    /**
     * Register a built-in command.
     *
     * @param string $commandName the name of the command to register
     * @param array $args The command arguments
     */
    public function registerBuiltInCommands(string $commandName, array $args): void
    {
        $commandName = ucfirst($commandName);
        $commandFile = __DIR__ . "/Commands/$commandName.php";

        if (!file_exists($commandFile)) {
            Help::execute([]);

            return;
        }

        require_once $commandFile;

        $commandClass = "MythicalDash\\Cli\\Commands\\$commandName";

        if (!class_exists($commandClass)) {
            $this->send('&cCommand not found.');

            return;
        }

        $commandClass::execute($args);
    }

    /**
     * Register and execute plugin commands.
     *
     * @param string $commandName the name of the command to register
     * @param array $args The command arguments
     *
     * @return bool WhethregisterBuiltInCommandser a plugin command was found and executed
     */
    public function registerPluginCommands(string $commandName, array $args): bool
    {
        $pluginDirectory = getcwd() . '/backend/storage/addons';

        if (!is_dir($pluginDirectory)) {
            return false;
        }

        $plugins = array_diff(scandir($pluginDirectory), ['.', '..']);

        foreach ($plugins as $plugin) {
            $pluginPath = $pluginDirectory . '/' . $plugin;
            if (!is_dir($pluginPath)) {
                continue;
            }

            // Check if the plugin has a commands folder
            $commandsFolder = $pluginPath . '/Commands';
            if (!is_dir($commandsFolder)) {
                continue;
            }

            $commandFiles = array_diff(scandir($commandsFolder), ['.', '..']);

            foreach ($commandFiles as $commandFile) {
                if (!str_ends_with($commandFile, '.php')) {
                    continue;
                }

                require_once $commandsFolder . '/' . $commandFile;

                $className = pathinfo($commandFile, PATHINFO_FILENAME);
                $commandClass = "MythicalDash\\Addons\\$plugin\\Commands\\$className";

                if (!class_exists($commandClass)) {
                    continue;
                }

                if (strtolower($className) === strtolower($commandName)) {
                    $commandClass::execute($args);

                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Send a message to the console.
     *
     * @param string $message the message to send
     */
    public function send(string $message): void
    {
        self::sendOutputWithNewLine($this->prefix . $message);
    }

    /**
     * Get the instance of the App.
     */
    public static function getInstance(): App
    {
        return self::$instance;
    }

    /**
     * Custom commands handler.
     *
     * This method is used to handle custom commands that are not part of the CLI.
     *
     * The following commands are handled:
     * - frontend:build
     * - frontend:watch
     * - backend:lint
     *
     * DO NOT REMOVE OR MODIFY THIS METHOD.
     * IF YOU DO NOT KNOW WHAT YOU ARE DOING, DO NOT MODIFY THIS METHOD.
     */
    private function handleCustomCommands(string $cmdName, array $subCmd): void
    {
        if ($cmdName == 'frontend:build') {
            $process = popen('cd frontend && yarn build 2>&1', 'r');
            if (is_resource($process)) {
                while (!feof($process)) {
                    $output = fgets($process);
                    $this->processOutput($output);
                }
                $returnVar = pclose($process);
                if ($returnVar !== 0) {
                    $this->sendOutput('Failed to build frontend.');
                    $this->sendOutput("\n");
                } else {
                    $this->sendOutput('Frontend built successfully.');
                    $this->sendOutput("\n");
                }
            } else {
                $this->sendOutput($this->prefix . 'Failed to start build process.');
            }

            exit;
        } elseif ($cmdName == 'frontend:watch') {
            $process = popen('cd frontend && yarn dev 2>&1', 'r');
            if (is_resource($process)) {
                while (!feof($process)) {
                    $output = fgets($process);
                    $this->processOutput($output);
                }
                $returnVar = pclose($process);
                if ($returnVar !== 0) {
                    $this->sendOutput('Failed to watch frontend.');
                    $this->sendOutput(message: "\n");
                } else {
                    $this->sendOutput('Frontend is now being watched.');
                    $this->sendOutput("\n");
                }
            } else {
                $this->sendOutput($this->prefix . 'Failed to start watch process.');
            }

            exit;
        } elseif ($cmdName == 'backend:lint') {
            $process = popen('cd backend && export COMPOSER_ALLOW_SUPERUSER=1 && composer run lint 2>&1', 'r');
            if (is_resource($process)) {
                while (!feof($process)) {
                    $output = fgets($process);
                    $this->processOutput($output);
                }
                $returnVar = pclose($process);
                if ($returnVar !== 0) {
                    $this->sendOutput('Failed to lint backend.');
                    $this->sendOutput("\n");
                } else {
                    $this->sendOutput('Backend linted successfully.');
                    $this->sendOutput("\n");
                }
            } else {
                $this->sendOutput($this->prefix . 'Failed to start lint process.');
            }
            exit;
        } elseif ($cmdName == 'backend:watch') {
            $process = popen('tail -f backend/storage/logs/mythicaldash.log backend/storage/logs/mythicaldash-v3.log', 'r');
            $this->sendOutput('Please wait while we attach to the process...');
            $this->sendOutput(message: "\n");
            sleep(5);
            if (is_resource($process)) {
                $this->sendOutput('Attached to the process.');
                $this->sendOutput(message: "\n");
                while (!feof($process)) {
                    $output = fgets($process);
                    $this->processOutput($output);
                }
                $returnVar = pclose($process);
                if ($returnVar !== 0) {
                    $this->sendOutput('Failed to watch backend.');
                    $this->sendOutput(message: "\n");
                } else {
                    $this->sendOutput('Backend is now being watched.');
                    $this->sendOutput("\n");
                }
            } else {
                $this->sendOutput($this->prefix . 'Failed to start watch process.');
            }
            exit;
        } elseif ($cmdName == 'make:migration') {
            $this->sendOutput('Enter migration name (e.g. add-user-table): ');
            $migrationName = trim(fgets(STDIN));

            if (empty($migrationName)) {
                $this->sendOutput('Migration name is required.');
                $this->sendOutput("\n");
                exit;
            }

            $date = date('Y-m-d.H.i');
            $filename = $date . '-' . $migrationName . '.sql';

            $filepath = 'backend/storage/migrations/' . $filename;

            file_put_contents($filepath, '');
            $this->sendOutput('Created migration file: ' . $filename);
            $this->sendOutput("\n");
            exit;
        } elseif ($cmdName == 'push') {
            $process = popen('cd backend && export COMPOSER_ALLOW_SUPERUSER=1 && composer run lint 2>&1', 'r');
            if (is_resource($process)) {
                while (!feof($process)) {
                    $output = fgets($process);
                    $this->processOutput($output);
                }
                $returnVar = pclose($process);
                if ($returnVar !== 0) {
                    $this->sendOutput('Failed to lint backend.');
                    $this->sendOutput("\n");
                } else {
                    $this->sendOutput('Backend linted successfully.');
                    $this->sendOutput("\n");
                }
            } else {
                $this->sendOutput($this->prefix . 'Failed to start backend lint process.');
            }

            $process = popen('cd frontend && yarn lint 2>&1', 'r');
            if (is_resource($process)) {
                while (!feof($process)) {
                    $output = fgets($process);
                    $this->processOutput($output);
                }
                $returnVar = pclose($process);
                if ($returnVar !== 0) {
                    $this->sendOutput('Failed to lint frontend.');
                    $this->sendOutput("\n");
                } else {
                    $this->sendOutput('Frontend linted successfully.');
                    $this->sendOutput("\n");
                }
            } else {
                $this->sendOutput($this->prefix . 'Failed to start frontend lint process.');
            }

            $process = popen('cd frontend && yarn format 2>&1', 'r');
            if (is_resource($process)) {
                while (!feof($process)) {
                    $output = fgets($process);
                    $this->processOutput($output);
                }
                $returnVar = pclose($process);
                if ($returnVar !== 0) {
                    $this->sendOutput('Failed to format frontend.');
                    $this->sendOutput("\n");
                } else {
                    $this->sendOutput('Frontend formatted successfully.');
                    $this->sendOutput("\n");
                }
            } else {
                $this->sendOutput($this->prefix . 'Failed to start frontend format process.');
            }

            exit;
        }
    }

    private function processOutput(string $output): void
    {
        // Skip Vue DevTools and help messages
        if (
            strpos($output, 'Vue DevTools:') !== false
            || strpos($output, 'press h + enter') !== false
        ) {
            return;
        }

        // Strip timestamp and replace vite/VITE with MythicalDash
        $output = preg_replace('/\d{1,2}:\d{2}:\d{2}\s[AP]M\s\[vite\]\s/', '[MythicalDash] ', $output);
        $output = str_replace(['vite', 'VITE'], ['mythicalcompiler', 'MythicalCompiler'], $output);

        // Handle different log levels with colors
        if (stripos($output, '[DEBUG]') !== false || stripos($output, 'debug') !== false) {
            $this->sendOutput($this->prefix . "\e[34m" . $output . "\e[0m"); // Blue for DEBUG
        } elseif (stripos($output, '[INFO]') !== false || stripos($output, 'info') !== false) {
            $this->sendOutput($this->prefix . "\e[32m" . $output . "\e[0m"); // Green for INFO
        } elseif (stripos($output, '[WARNING]') !== false || stripos($output, 'warning') !== false) {
            $this->sendOutput($this->prefix . "\e[33m" . $output . "\e[0m"); // Yellow for WARNING
        } elseif (stripos($output, '[ERROR]') !== false || stripos($output, 'error') !== false) {
            $this->sendOutput($this->prefix . "\e[31m" . $output . "\e[0m"); // Red for ERROR
        } elseif (stripos($output, '[CRITICAL]') !== false) {
            $this->sendOutput($this->prefix . "\e[35m" . $output . "\e[0m"); // Magenta for CRITICAL
        } else {
            $this->sendOutput($this->prefix . $output);
        }
    }
}
