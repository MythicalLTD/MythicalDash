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

        if (getcwd() !== '/var/www/mythicaldash-v3' && getcwd() !== '/var/www/html' && getcwd() !== '/var/www/html/backend') {
            exit('We detected that you are not running this command from the root directory of MythicalDash. Please run this command from the root directory.');
        }

        $addonDir = getcwd() . '/backend/storage/addons/imagehostbridge';
        if (is_dir($addonDir)) {
            // Recursively delete the directory and its contents
            $iterator = new \RecursiveDirectoryIterator($addonDir, \FilesystemIterator::SKIP_DOTS);
            $files = new \RecursiveIteratorIterator($iterator, \RecursiveIteratorIterator::CHILD_FIRST);
            foreach ($files as $file) {
                if ($file->isDir()) {
                    rmdir($file->getPathname());
                } else {
                    unlink($file->getPathname());
                }
            }
            rmdir($addonDir);
        }

        if (file_exists(getcwd() . '/backend/app/Api/System/License.php')) {
            $this->sendOutput('Removing License.php...');
            unlink(getcwd() . '/backend/app/Api/System/License.php');
            $this->sendOutput('License.php removed successfully!');
        }

        if (file_exists(getcwd() . '/backend/storage/cron/php/TelemetryJob.php')) {
            $this->sendOutput('Removing TelemetryJob.php...');
            unlink(getcwd() . '/backend/storage/cron/php/TelemetryJob.php');
            $this->sendOutput('TelemetryJob.php removed successfully!');
        }

        // Check if mythicaldash-v3.log exists and rename it to mythicaldash.log
        $oldLogFile = getcwd() . '/backend/storage/logs/mythicaldash-v3.log';
        $newLogFile = getcwd() . '/backend/storage/logs/mythicaldash.log';

        // Create both log files if they don't exist
        if (!file_exists($oldLogFile)) {
            $this->sendOutput('Creating mythicaldash-v3.log');
            touch($oldLogFile);
        }

        if (!file_exists($newLogFile)) {
            $this->sendOutput('Creating mythicaldash.log');
            touch($newLogFile);
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

    /**
     * Run startup health checks (only critical errors).
     */
    private function runStartupHealthChecks(): void
    {
        // Import and run health checks
        require_once __DIR__ . '/Extra/HealthCheck.php';
        $healthCheck = new Extra\HealthCheck($this);
        $results = $healthCheck->run();

        // Only report if there are critical errors
        if (!empty($results['errors'])) {
            $this->send("\e[31m❌ CRITICAL ERRORS DETECTED:\e[0m");
            $this->send("\n");

            foreach ($results['errors'] as $error) {
                $this->send("\e[31m  • {$error['message']}\e[0m");
            }

            $this->send("\n\e[31m⚠️  Please fix these errors before running MythicalDash.\e[0m");
            exit(1);
        }
    }

    /**
     * Run health checks and display results.
     */
    private function runHealthChecks(): void
    {
        $this->send("\e[36mRunning MythicalDash Health Checks...\e[0m");
        $this->send("\n");

        // Import and run health checks
        require_once __DIR__ . '/Extra/HealthCheck.php';
        $healthCheck = new Extra\HealthCheck($this);
        $results = $healthCheck->run();

        // Display the report
        $report = $healthCheck->getReport();
        $this->send($report);

        // Display detailed results with colors
        $this->send("\e[36mDetailed Results:\e[0m");
        $this->send("\n");

        foreach ($results['results'] as $check => $result) {
            if ($result['status'] === 'pass') {
                $this->send("\e[32m✅ {$result['message']}\e[0m");
            } else {
                $this->send("\e[31m❌ {$result['message']}\e[0m");
            }
        }

        // Display errors
        if (!empty($results['errors'])) {
            $this->send("\n\e[31m❌ ERRORS:\e[0m");
            foreach ($results['errors'] as $error) {
                $this->send("\e[31m  • {$error['message']}\e[0m");
            }
        }

        // Display warnings
        if (!empty($results['warnings'])) {
            $this->send("\n\e[33m⚠️  WARNINGS:\e[0m");
            foreach ($results['warnings'] as $warning) {
                $this->send("\e[33m  • {$warning['message']}\e[0m");
            }
        }

        // Final status
        $this->send("\n\e[36mHealth Check Summary:\e[0m");
        $this->send('  • Total checks: ' . count($results['results']));
        $this->send('  • Passed: ' . count(array_filter($results['results'], fn ($r) => $r['status'] === 'pass')));
        $this->send('  • Errors: ' . count($results['errors']));
        $this->send('  • Warnings: ' . count($results['warnings']));

        if ($results['status'] === 'healthy') {
            $this->send("\n\e[32m🎉 MythicalDash is healthy and ready to run!\e[0m");
        } else {
            $this->send("\n\e[31m⚠️  Please fix the errors above before running MythicalDash.\e[0m");
        }

        $this->send("\n");
        exit;
    }
}
