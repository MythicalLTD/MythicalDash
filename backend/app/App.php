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

namespace MythicalDash;

use RateLimit\Rate;
use MythicalDash\Chat\Database;
use RateLimit\RedisRateLimiter;
use MythicalDash\Hooks\MythicalAPP;
use MythicalDash\Router\Router as rt;
use MythicalDash\Config\ConfigFactory;
use MythicalDash\Logger\LoggerFactory;
use RateLimit\Exception\LimitExceeded;
use MythicalDash\Config\ConfigInterface;
use MythicalDash\CloudFlare\CloudFlareRealIP;
use MythicalDash\Plugins\Events\Events\AppEvent;
use MythicalDash\Hooks\MythicalSystems\Utils\XChaCha20;

class App extends MythicalAPP
{
    public static App $instance;
    public Database $db;
    public rt $router;

    public function __construct(bool $softBoot, bool $isCron = false)
    {
        /**
         * Load the environment variables.
         */
        $this->loadEnv();

        /**
         * Instance.
         */
        self::$instance = $this;

        /**
         * Soft boot.
         *
         * If the soft boot is true, we do not want to initialize the database connection or the router.
         *
         * This is useful for commands or other things that do not require the database connection.
         *
         * This is also a lite way to boot the application without initializing the database connection or the router!.
         */
        if ($softBoot) {
            return;
        }

        if ($isCron) {
            define('CRON_MODE', true);
        }

        /**
         * @global \MythicalDash\Plugins\PluginManager $pluginManager
         * @global \MythicalDash\Plugins\Events\PluginEvent $eventManager
         */
        global $pluginManager, $eventManager;

        /**
         * Redis.
         */
        $redis = new FastChat\Redis();
        if ($redis->testConnection() == false) {
            define('REDIS_ENABLED', false);
        } else {
            define('REDIS_ENABLED', true);
        }

        if (!defined('CRON_MODE')) {
            // @phpstan-ignore-next-line
            if (!isset($_ENV['firewall_enabled'])) {
                $_ENV['firewall_enabled'] = 'true';
                $this->updateEnvValue(ConfigInterface::FIREWALL_ENABLED, 'true', false);
            }
            if (!isset($_ENV['firewall_rate_limit'])) {
                $_ENV['firewall_rate_limit'] = '100';
                $this->updateEnvValue(ConfigInterface::FIREWALL_RATE_LIMIT, '100', false);
            }
            if (!isset($_ENV['firewall_block_vpn'])) {
                $_ENV['firewall_block_vpn'] = 'false';
                $this->updateEnvValue(ConfigInterface::FIREWALL_BLOCK_VPN, 'false', false);
            }
            if (isset($_ENV['firewall_enabled']) && $_ENV['firewall_enabled'] == 'true') {
                try {
                    $redis = new \Redis();
                    if (isset($_ENV['firewall_rate_limit'])) {
                        $rateLimiter = new RedisRateLimiter(Rate::perMinute($_ENV['firewall_rate_limit']), $redis, 'rate_limiting');
                        try {
                            $rateLimiter->limit(CloudFlareRealIP::getRealIP());
                        } catch (LimitExceeded $e) {
                            self::getLogger()->error('User: ' . $e->getMessage());
                            self::init();
                            self::ServiceUnavailable('You are being rate limited!', ['error_code' => 'RATE_LIMITED']);
                        } catch (\Exception $e) {
                            self::getLogger()->error('-----------------------------');
                            self::getLogger()->error('REDIS SERVER IS DOWN');
                            self::getLogger()->error('RATE LIMITING IS DISABLED');
                            self::getLogger()->error('YOU SHOULD FIX THIS ASAP');
                            self::getLogger()->error('NO SUPPORT WILL BE PROVIDED');
                            self::getLogger()->error('-----------------------------');
                        }
                    } else {
                        self::getLogger()->error('Firewall rate limit is not set');
                        self::getLogger()->error('Rate limiting is disabled');
                        self::getLogger()->error('You should fix this ASAP');
                        self::getLogger()->error('No support will be provided');
                        self::getLogger()->error('-----------------------------');
                    }
                } catch (\Exception $e) {
                    self::getLogger()->error('Redis server is not available - rate limiting disabled');
                    $rateLimiter = null;
                }
            }
        }

        /**
         * Database Connection.
         */
        try {
            if (isset($_ENV['DATABASE_HOST']) && isset($_ENV['DATABASE_DATABASE']) && isset($_ENV['DATABASE_USER']) && isset($_ENV['DATABASE_PASSWORD']) && isset($_ENV['DATABASE_PORT'])) {
                $this->db = new Database($_ENV['DATABASE_HOST'], $_ENV['DATABASE_DATABASE'], $_ENV['DATABASE_USER'], $_ENV['DATABASE_PASSWORD'], $_ENV['DATABASE_PORT']);
            } else {
                self::init();
                self::InternalServerError('Database connection failed', null);
            }
        } catch (\Exception $e) {
            self::init();
            self::InternalServerError($e->getMessage(), null);
        }

        /**
         * Initialize the plugin manager.
         */
        if (!defined('CRON_MODE')) {
            $pluginManager->loadKernel();
            define('LOGGER', $this->getLogger());
        }

        if ($isCron) {
            return;
        }

        $this->router = new rt();
        $this->registerApiRoutes($this->router);
        $eventManager->emit(AppEvent::onAppLoad(), []);
        $eventManager->emit(AppEvent::onRouterReady(), [$this->router]);

        $this->router->add('/(.*)', function ($route): void {
            self::init();
            self::NotFound('The api route does not exist!', ['error_code' => 'API_ROUTE_NOT_FOUND', 'route' => $route]);
        });

        // Robust timezone setting with fallback and error handling
        $timezone = 'UTC';
        try {
            $tzFromConfig = $this->getConfig()->getDBSetting(ConfigInterface::APP_TIMEZONE, 'UTC');
            if ($tzFromConfig && in_array($tzFromConfig, \DateTimeZone::listIdentifiers())) {
                $timezone = $tzFromConfig;
            } else {
                self::getLogger()->warning('Configured timezone "' . $tzFromConfig . '" is invalid or not recognized. Falling back to UTC.');
            }
        } catch (\Throwable $e) {
            self::getLogger()->warning('Failed to get timezone from config, falling back to UTC: ' . $e->getMessage());
        }

        try {
            date_default_timezone_set($timezone);
        } catch (\Throwable $e) {
            self::getLogger()->warning('Failed to set timezone "' . $timezone . '", falling back to UTC: ' . $e->getMessage());
            date_default_timezone_set('UTC');
        }

        // Get the current host (without protocol or port)
        $host = $_SERVER['HTTP_HOST'] ?? '';
        // Always update to the current host if the config is still the default "framework"
        if ($this->getConfig()->getDBSetting(ConfigInterface::APP_URL, 'framework.mythical.systems') === 'framework.mythical.systems') {
            if (!empty($host)) {
                $this->getConfig()->setSetting(ConfigInterface::APP_URL, $host);
            }
        }

        try {
            $this->router->route();
        } catch (\Exception $e) {
            self::init();
            self::InternalServerError($e->getMessage(), null);
        }
    }

    /**
     * Get the router.
     */
    public function getRouter(): rt
    {
        return $this->router;
    }

    /**
     * Register all api endpoints.
     *
     * @param rt $router The router instance
     */
    public function registerApiRoutes(rt $router): void
    {
        try {

            $routersDir = APP_ROUTES_DIR;
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($routersDir));
            $phpFiles = new \RegexIterator($iterator, '/\.php$/');
            foreach ($phpFiles as $phpFile) {
                try {
                    self::init();
                    include $phpFile->getPathname();
                } catch (\Exception $e) {
                    self::init();
                    self::InternalServerError($e->getMessage(), null);
                }
            }
        } catch (\Exception $e) {
            self::init();
            self::InternalServerError($e->getMessage(), null);
        }
    }

    /**
     * Load the environment variables.
     */
    public function loadEnv(): void
    {
        try {
            if (file_exists(__DIR__ . '/../storage/.env')) {
                $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../storage/');
                $dotenv->load();

            } else {
                echo 'No .env file found';
                exit;
            }
        } catch (\Exception $e) {
            echo $e->getMessage();
            exit;
        }
    }

    /**
     * Update the value of an environment variable.
     *
     * @param string $key The key of the environment variable
     * @param string $value The value of the environment variable
     * @param bool $encode If the value should be encoded
     *
     * @return bool If the value was updated
     */
    public function updateEnvValue(string $key, string $value, bool $encode): bool
    {
        $envFile = __DIR__ . '/../storage/.env'; // Path to your .env file
        if (!file_exists($envFile)) {
            return false; // Return false if .env file doesn't exist
        }

        // Read the .env file into an array of lines (preserve all lines including empty and comments)
        $lines = file($envFile, FILE_IGNORE_NEW_LINES);
        if ($lines === false) {
            $this->getLogger()->error('Failed to read .env file');

            return false;
        }

        $updated = false;
        foreach ($lines as &$line) {
            // Skip comments and empty lines - preserve them as is
            if (empty(trim($line)) || strpos(trim($line), '#') === 0) {
                continue;
            }

            // Only process lines that contain '='
            if (strpos($line, '=') === false) {
                continue;
            }

            // Split the line into key and value
            [$envKey, $envValue] = explode('=', $line, 2);

            // Trim whitespace from the key
            if (trim($envKey) === $key) {
                // Update the value while preserving the original format
                $line = "$key=\"$value\"";
                $updated = true;
                break; // Exit loop once we find and update the key
            }
        }

        // If the key doesn't exist, add it at the end
        if (!$updated) {
            $lines[] = "$key=\"$value\"";
        }

        // Check if we have write permissions
        if (!is_writable($envFile)) {
            $this->getLogger()->error('Cannot write to .env file - insufficient permissions');

            return false;
        }

        // Create a backup of the original .env file before writing
        $backupFile = $envFile . '.backup.' . date('Y-m-d_H-i-s');
        if (!copy($envFile, $backupFile)) {
            $this->getLogger()->error('Failed to create backup of .env file');

            return false;
        }

        // Try to write the file with proper line endings
        try {
            $content = implode(PHP_EOL, $lines);
            if (file_put_contents($envFile, $content) === false) {
                // Restore from backup if write fails
                copy($backupFile, $envFile);
                $this->getLogger()->error('Failed to write to .env file - restored from backup');

                return false;
            }

            // Clean up backup file after successful write
            unlink($backupFile);

            return true;
        } catch (\Exception $e) {
            // Restore from backup if an exception occurs
            if (file_exists($backupFile)) {
                copy($backupFile, $envFile);
                unlink($backupFile);
            }
            $this->getLogger()->error('Failed to write to .env file: ' . $e->getMessage() . ' - restored from backup');

            return false;
        }
    }

    /**
     * Get the config factory.
     */
    public function getConfig(): ConfigFactory
    {
        if (isset(self::$instance->db)) {
            return new ConfigFactory(self::$instance->db->getPdo());
        }
        throw new \Exception('Database connection is not initialized.');
    }

    /**
     * Get the database.
     */
    public function getDatabase(): Database
    {
        return $this->db;
    }

    /**
     * Get the logger factory.
     */
    public function getLogger(): LoggerFactory
    {
        return new LoggerFactory(__DIR__ . '/../storage/logs/mythicaldash.log');
    }

    /**
     * Get the web server logger factory.
     */
    public function getWebServerLogger(): LoggerFactory
    {
        return new LoggerFactory(__DIR__ . '/../storage/logs/mythicaldash-v3.log');
    }

    /**
     * Get the instance of the App class.
     */
    public static function getInstance(bool $softBoot, bool $isCron = false): App
    {
        if (!isset(self::$instance)) {
            self::$instance = new self($softBoot, $isCron);
        }

        return self::$instance;
    }

    /**
     * Encrypt the data.
     *
     * @param string $data The data to encrypt
     */
    public function encrypt(string $data): string
    {
        return XChaCha20::encrypt($data, $_ENV['DATABASE_ENCRYPTION_KEY'], true);
    }

    /**
     * Decrypt the data.
     *
     * @param string $data The data to decrypt
     *
     * @return void
     */
    public function decrypt(string $data): string
    {
        return XChaCha20::decrypt($data, $_ENV['DATABASE_ENCRYPTION_KEY'], true);
    }

    /**
     * Generate a random code.
     */
    public function generateCode(): string
    {
        $code = base64_encode(random_bytes(64));
        $code = str_replace('=', '', $code);
        $code = str_replace('+', '', $code);
        $code = str_replace('/', '', $code);

        return $code;
    }

    /**
     * Generate a random pin.
     */
    public function generatePin(): int
    {
        return random_int(100000, 999999);
    }
}
