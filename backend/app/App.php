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

namespace MythicalDash;

use RateLimit\Rate;
use MythicalDash\Chat\Database;
use RateLimit\RedisRateLimiter;
use MythicalDash\Hooks\MythicalAPP;
use MythicalDash\Hooks\MythicalZero;
use MythicalDash\Router\Router as rt;
use MythicalDash\Config\ConfigFactory;
use MythicalDash\Logger\LoggerFactory;
use RateLimit\Exception\LimitExceeded;
use MythicalDash\Config\ConfigInterface;
use MythicalDash\Hooks\LicenseValidator;
use MythicalDash\CloudFlare\CloudFlareRealIP;
use MythicalDash\Plugins\Events\Events\AppEvent;
use MythicalDash\Hooks\MythicalSystems\Utils\XChaCha20;

class App extends MythicalAPP
{
    public static App $instance;
    public LicenseValidator $licenseValidator;
    public Database $db;
    public MythicalZero $telemetry;

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
            $rateLimiter = new RedisRateLimiter(Rate::perMinute(RATE_LIMIT), new \Redis(), 'rate_limiting');
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
        }

        /**
         * Database Connection.
         */
        try {
            $this->db = new Database($_ENV['DATABASE_HOST'], $_ENV['DATABASE_DATABASE'], $_ENV['DATABASE_USER'], $_ENV['DATABASE_PASSWORD']);
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

        $router = new rt();
        $this->registerApiRoutes($router);
        $eventManager->emit(AppEvent::onAppLoad(), []);
        $eventManager->emit(AppEvent::onRouterReady(), [$router]);

        try {
            /**
             * License validator.
             */
            $this->licenseValidator = new LicenseValidator($this->getConfig()->getSetting(ConfigInterface::LICENSE_KEY, 'NULL'));
            if (!$this->licenseValidator->validate()) {
                define('HAS_VALID_LICENSE', false);
            } else {
                $this->getLogger()->debug('License is valid! Thank you for supporting the development of MythicalDash!');
                define('HAS_VALID_LICENSE', true);
            }
        } catch (\Exception $e) {
            App::getInstance(true)->getLogger()->error('License validator error: ' . $e->getMessage());
        }
        /**
         * MythicalZero.
         */
        $this->telemetry = new MythicalZero(
            'https://api.mythical.systems',
            APP_VERSION,
            preg_replace('/^https?:\/\//', '', $this->getConfig()->getSetting(ConfigInterface::APP_URL, 'NULL')),
            $this->getConfig()->getSetting(ConfigInterface::MYTHICAL_ZERO_TRUST_ENABLED, 'true'),
            $this->getConfig()->getSetting(ConfigInterface::TELEMETRY_ENABLED, 'true'),
        );

        if ($this->getConfig()->getSetting(ConfigInterface::APP_URL, 'https://mythicaldash-v3.mythical.systems') == 'https://mythicaldash-v3.mythical.systems') {
            $appUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
            $this->getConfig()->setSetting(ConfigInterface::APP_URL, $appUrl);
        }

        $router->add('/(.*)', function ($route): void {
            self::init();
            self::NotFound('The api route does not exist!', ['error_code' => 'API_ROUTE_NOT_FOUND', 'route' => $route]);
        });

        try {
            $router->route();
        } catch (\Exception $e) {
            self::init();
            self::InternalServerError($e->getMessage(), null);
        }
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
     * Get the license validator.
     */
    public function getLicenseValidator(): LicenseValidator
    {
        return $this->licenseValidator;
    }

    /**
     * Get the telemetry.
     */
    public function getTelemetry(): MythicalZero
    {
        return $this->telemetry;
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

        // Read the .env file into an array of lines
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        $updated = false;
        foreach ($lines as &$line) {
            // Skip comments and lines that don't contain '='
            if (strpos(trim($line), '#') === 0 || strpos($line, '=') === false) {
                continue;
            }

            // Split the line into key and value
            [$envKey, $envValue] = explode('=', $line, 2);

            // Trim whitespace from the key
            if (trim($envKey) === $key) {
                // Update the value
                $line = "$key=\"$value\"";
                $updated = true;
            }
        }

        // If the key doesn't exist, add it
        if (!$updated) {
            $lines[] = "$key=$value";
        }

        // Write the updated lines back to the .env file
        return file_put_contents($envFile, implode(PHP_EOL, $lines)) !== false;
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

    /**
     * Generate a performance heatmap of the application.
     * Tracks execution time, memory usage, and frequency of function calls.
     *
     * @param int $duration Duration in seconds to collect data
     * @param array $options Additional options for heatmap generation
     *
     * @return array Heatmap data containing performance metrics
     */
    public function heatmap(int $duration = 60, array $options = []): array
    {
        $startTime = microtime(true);
        $heatmapData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'duration' => $duration,
            'metrics' => [
                'memory' => [],
                'cpu' => [],
                'io' => [],
                'database' => [],
                'function_calls' => [],
            ],
            'hotspots' => [],
        ];

        // Memory usage tracking
        $heatmapData['metrics']['memory'] = [
            'peak' => memory_get_peak_usage(true),
            'current' => memory_get_usage(true),
            'limit' => ini_get('memory_limit'),
        ];

        // CPU load
        if (function_exists('sys_getloadavg')) {
            $heatmapData['metrics']['cpu'] = sys_getloadavg();
        }

        // Database metrics if available
        if (isset($this->db)) {
            try {
                $stmt = $this->db->getPdo()->query('SHOW STATUS');
                $dbMetrics = $stmt->fetchAll(\PDO::FETCH_KEY_PAIR);
                $heatmapData['metrics']['database'] = [
                    'connections' => $dbMetrics['Threads_connected'] ?? 0,
                    'queries' => $dbMetrics['Questions'] ?? 0,
                    'slow_queries' => $dbMetrics['Slow_queries'] ?? 0,
                ];
            } catch (\Exception $e) {
                $heatmapData['metrics']['database'] = ['error' => $e->getMessage()];
            }
        }

        // I/O operations
        if (function_exists('disk_free_space')) {
            $heatmapData['metrics']['io'] = [
                'disk_free' => disk_free_space('/'),
                'disk_total' => disk_total_space('/'),
            ];
        }

        return $heatmapData;
    }

    /**
     * Generate a comprehensive health report of the application.
     * Checks various system components and dependencies.
     *
     * @return array Health status of various system components
     */
    public function healthdump(): array
    {
        $health = [
            'timestamp' => date('Y-m-d H:i:s'),
            'status' => 'healthy',
            'components' => [],
        ];

        // Check PHP version and extensions
        $health['components']['php'] = [
            'version' => PHP_VERSION,
            'extensions' => get_loaded_extensions(),
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
        ];

        // Check database connection
        if (isset($this->db)) {
            try {
                $this->db->getPdo()->query('SELECT 1');
                $health['components']['database'] = [
                    'status' => 'connected',
                    'type' => $this->db->getPdo()->getAttribute(\PDO::ATTR_DRIVER_NAME),
                ];
            } catch (\Exception $e) {
                $health['components']['database'] = [
                    'status' => 'error',
                    'message' => $e->getMessage(),
                ];
                $health['status'] = 'degraded';
            }
        }

        // Check Redis if enabled
        if (defined('REDIS_ENABLED') && REDIS_ENABLED) {
            try {
                $redis = new \Redis();
                $redis->connect($_ENV['REDIS_HOST'] ?? 'localhost');
                $health['components']['redis'] = [
                    'status' => 'connected',
                    'version' => $redis->info()['redis_version'],
                ];
            } catch (\Exception $e) {
                $health['components']['redis'] = [
                    'status' => 'error',
                    'message' => $e->getMessage(),
                ];
                $health['status'] = 'degraded';
            }
        }

        // Check disk space
        $health['components']['disk'] = [
            'free_space' => disk_free_space('/'),
            'total_space' => disk_total_space('/'),
            'usage_percentage' => round((1 - disk_free_space('/') / disk_total_space('/')) * 100, 2),
        ];

        return $health;
    }

    /**
     * Generate a core dump of the application's current state.
     * Captures detailed debug information for troubleshooting.
     *
     * @param bool $includeEnv Whether to include environment variables
     * @param bool $includeSensitive Whether to include sensitive data (use with caution)
     *
     * @return array Detailed application state information
     */
    public function coreDump(bool $includeEnv = false, bool $includeSensitive = false): array
    {
        $dump = [
            'timestamp' => date('Y-m-d H:i:s'),
            'process' => [
                'pid' => getmypid(),
                'user' => get_current_user(),
                'memory_usage' => memory_get_usage(true),
                'peak_memory_usage' => memory_get_peak_usage(true),
            ],
            'runtime' => [
                'php_version' => PHP_VERSION,
                'sapi' => PHP_SAPI,
                'os' => PHP_OS,
                'extensions' => get_loaded_extensions(),
                'ini_settings' => ini_get_all(null, false),
            ],
            'stack_trace' => $this->getDebugBacktrace(),
            'errors' => [],
        ];

        // Capture error log if available
        $errorLog = ini_get('error_log');
        if ($errorLog && file_exists($errorLog)) {
            $dump['errors'] = array_slice(file($errorLog), -100); // Last 100 lines
        }

        // Include environment variables if requested
        if ($includeEnv) {
            $dump['environment'] = $_ENV;
            if (!$includeSensitive) {
                // Remove sensitive data
                foreach ($dump['environment'] as $key => &$value) {
                    if (preg_match('/(password|key|secret|token)/i', $key)) {
                        $value = '******';
                    }
                }
            }
        }

        // Add loaded classes and interfaces
        $dump['classes'] = [
            'declared' => get_declared_classes(),
            'interfaces' => get_declared_interfaces(),
        ];

        return $dump;
    }

    /**
     * Get a cleaned up debug backtrace.
     *
     * @return array Formatted backtrace information
     */
    private function getDebugBacktrace(): array
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        $cleanTrace = [];

        foreach ($trace as $call) {
            $cleanTrace[] = [
                'file' => $call['file'] ?? 'unknown',
                'line' => $call['line'] ?? 0,
                'function' => $call['function'] ?? 'unknown',
                'class' => $call['class'] ?? null,
            ];
        }

        return $cleanTrace;
    }
}
