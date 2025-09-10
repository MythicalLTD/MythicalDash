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

namespace MythicalDash\Cli\Extra;

use MythicalDash\Cli\App;

class HealthCheck
{
    private App $app;
    private array $results = [];
    private array $errors = [];
    private array $warnings = [];

    public function __construct(App $app)
    {
        $this->app = $app;
    }

    /**
     * Run all health checks.
     */
    public function run(): array
    {
        $this->checkPhpVersion();
        $this->checkRequiredExtensions();
        $this->checkOptionalExtensions();
        $this->checkSystemRequirements();
        $this->checkPhpIniSettings();
        $this->checkSecuritySettings();
        $this->checkRedisConnectivity();
        $this->checkDirectoryPermissions();
        $this->checkCronFile();
        $this->checkEnvironmentFile();
        $this->checkComposerDependencies();

        return [
            'status' => empty($this->errors) ? 'healthy' : 'unhealthy',
            'errors' => $this->errors,
            'warnings' => $this->warnings,
            'results' => $this->results,
        ];
    }

    /**
     * Get formatted health check report.
     */
    public function getReport(): string
    {
        $report = "=== MythicalDash Health Check Report ===\n\n";

        if (empty($this->errors) && empty($this->warnings)) {
            $report .= "✅ All checks passed! System is healthy.\n\n";
        }

        if (!empty($this->errors)) {
            $report .= "❌ ERRORS:\n";
            foreach ($this->errors as $error) {
                $report .= "  • {$error['message']}\n";
            }
            $report .= "\n";
        }

        if (!empty($this->warnings)) {
            $report .= "⚠️  WARNINGS:\n";
            foreach ($this->warnings as $warning) {
                $report .= "  • {$warning['message']}\n";
            }
            $report .= "\n";
        }

        $report .= "📊 SUMMARY:\n";
        $report .= '  • Total checks: ' . count($this->results) . "\n";
        $report .= '  • Passed: ' . count(array_filter($this->results, fn ($r) => $r['status'] === 'pass')) . "\n";
        $report .= '  • Errors: ' . count($this->errors) . "\n";
        $report .= '  • Warnings: ' . count($this->warnings) . "\n";

        return $report;
    }

    /**
     * Check PHP version compatibility.
     */
    private function checkPhpVersion(): void
    {
        $currentVersion = PHP_VERSION;
        $requiredVersion = '8.2.0';

        if (version_compare($currentVersion, $requiredVersion, '>=')) {
            $this->results['php_version'] = [
                'status' => 'pass',
                'current' => $currentVersion,
                'required' => $requiredVersion,
                'message' => "PHP version {$currentVersion} meets requirements",
            ];
        } else {
            $this->errors[] = [
                'type' => 'php_version',
                'message' => "PHP version {$currentVersion} is below required version {$requiredVersion}",
                'current' => $currentVersion,
                'required' => $requiredVersion,
            ];
        }
    }

    /**
     * Check required PHP extensions.
     */
    private function checkRequiredExtensions(): void
    {
        $requiredExtensions = [
            'pdo',
            'pdo_mysql',
            'json',
            'mbstring',
            'openssl',
            'curl',
            'fileinfo',
            'zip',
            'gd',
            'intl',
            'bcmath',
            'xml',
            'redis',
        ];

        foreach ($requiredExtensions as $extension) {
            if (extension_loaded($extension)) {
                $this->results["ext_{$extension}"] = [
                    'status' => 'pass',
                    'message' => "Extension {$extension} is loaded",
                ];
            } else {
                $this->errors[] = [
                    'type' => 'missing_extension',
                    'extension' => $extension,
                    'message' => "Required extension {$extension} is not loaded",
                ];
            }
        }
    }

    /**
     * Check optional PHP extensions.
     */
    private function checkOptionalExtensions(): void
    {
        $optionalExtensions = [
            'memcached' => 'Memcached caching support',
            'apcu' => 'APCu caching support',
            'imagick' => 'ImageMagick for advanced image processing',
            'sodium' => 'Libsodium for enhanced encryption',
            'dom' => 'DOM processing support',
        ];

        foreach ($optionalExtensions as $extension => $description) {
            if (extension_loaded($extension)) {
                $this->results["ext_{$extension}"] = [
                    'status' => 'pass',
                    'message' => "Optional extension {$extension} is loaded",
                    'description' => $description,
                ];
            } else {
                $this->warnings[] = [
                    'type' => 'optional_extension',
                    'extension' => $extension,
                    'message' => "Optional extension {$extension} is not loaded",
                    'description' => $description,
                ];
            }
        }
    }

    /**
     * Check system requirements.
     */
    private function checkSystemRequirements(): void
    {
        // Check memory limit
        $memoryLimit = ini_get('memory_limit');
        $memoryLimitBytes = $this->parseMemoryLimit($memoryLimit);
        $requiredMemory = 128 * 1024 * 1024; // 128MB

        if ($memoryLimit === '-1') {
            $this->results['memory_limit'] = [
                'status' => 'pass',
                'current' => $memoryLimit,
                'required' => '128M',
                'message' => 'Memory limit is unlimited (optimal)',
            ];
        } elseif ($memoryLimitBytes >= $requiredMemory) {
            $this->results['memory_limit'] = [
                'status' => 'pass',
                'current' => $memoryLimit,
                'required' => '128M',
                'message' => "Memory limit {$memoryLimit} is sufficient",
            ];
        } else {
            $this->warnings[] = [
                'type' => 'memory_limit',
                'message' => "Memory limit {$memoryLimit} may be too low for optimal performance",
                'current' => $memoryLimit,
                'recommended' => '128M',
            ];
        }

        // Check max execution time
        $maxExecutionTime = ini_get('max_execution_time');
        if ($maxExecutionTime >= 30 || $maxExecutionTime == 0) {
            $this->results['max_execution_time'] = [
                'status' => 'pass',
                'current' => $maxExecutionTime,
                'message' => 'Max execution time is adequate',
            ];
        } else {
            $this->warnings[] = [
                'type' => 'max_execution_time',
                'message' => "Max execution time {$maxExecutionTime}s may be too low",
                'current' => $maxExecutionTime,
                'recommended' => '30s or 0 (unlimited)',
            ];
        }

        // Check upload max filesize
        $uploadMaxFilesize = ini_get('upload_max_filesize');
        $uploadMaxFilesizeBytes = $this->parseMemoryLimit($uploadMaxFilesize);
        $requiredUploadSize = 10 * 1024 * 1024; // 10MB

        if ($uploadMaxFilesize === '-1') {
            $this->results['upload_max_filesize'] = [
                'status' => 'pass',
                'current' => $uploadMaxFilesize,
                'required' => '10M',
                'message' => 'Upload max filesize is unlimited (optimal)',
            ];
        } elseif ($uploadMaxFilesizeBytes >= $requiredUploadSize) {
            $this->results['upload_max_filesize'] = [
                'status' => 'pass',
                'current' => $uploadMaxFilesize,
                'required' => '10M',
                'message' => "Upload max filesize {$uploadMaxFilesize} is sufficient",
            ];
        } else {
            $this->warnings[] = [
                'type' => 'upload_max_filesize',
                'message' => "Upload max filesize {$uploadMaxFilesize} may be too low modify this in your /etc/php/8.3/fpm/php.ini file. (upload_max_filesize = 10M)",
                'current' => $uploadMaxFilesize,
                'recommended' => '10M',
            ];
        }
    }

    /**
     * Check directory permissions.
     */
    private function checkDirectoryPermissions(): void
    {
        $directories = [
            'storage' => 'storage/',
            'logs' => 'storage/logs/',
            'cache' => 'storage/cache/',
            'uploads' => 'storage/uploads/',
            'temp' => 'storage/temp/',
        ];

        foreach ($directories as $name => $path) {
            $fullPath = getcwd() . '/' . $path;

            if (!is_dir($fullPath)) {
                if (!mkdir($fullPath, 0755, true)) {
                    $this->errors[] = [
                        'type' => 'directory_permission',
                        'directory' => $path,
                        'message' => "Cannot create directory {$path}",
                    ];
                    continue;
                }
            }

            if (is_writable($fullPath)) {
                $this->results["dir_{$name}"] = [
                    'status' => 'pass',
                    'path' => $path,
                    'message' => "Directory {$path} is writable",
                ];
            } else {
                $this->errors[] = [
                    'type' => 'directory_permission',
                    'directory' => $path,
                    'message' => "Directory {$path} is not writable",
                ];
            }
        }
    }

    /**
     * Check if cron check file exists.
     */
    private function checkCronFile(): void
    {
        $cronFile = 'backend/storage/caches/cron/a-check-cron.mydtt';
        if (file_exists($cronFile)) {
            $this->results['cron_file'] = [
                'status' => 'pass',
                'message' => 'Cron check file exists: cron jobs are likely to run.',
            ];
        } else {
            $this->errors[] = [
                'type' => 'cron_file',
                'message' => 'Cron check file missing: cron jobs may not run!',
            ];
        }
    }

    /**
     * Check additional PHP INI settings.
     */
    private function checkPhpIniSettings(): void
    {
        // Check post max size
        $postMaxSize = ini_get('post_max_size');
        $postMaxSizeBytes = $this->parseMemoryLimit($postMaxSize);
        $requiredPostSize = 10 * 1024 * 1024; // 10MB

        if ($postMaxSize === '-1') {
            $this->results['post_max_size'] = [
                'status' => 'pass',
                'current' => $postMaxSize,
                'required' => '10M',
                'message' => 'Post max size is unlimited (optimal)',
            ];
        } elseif ($postMaxSizeBytes >= $requiredPostSize) {
            $this->results['post_max_size'] = [
                'status' => 'pass',
                'current' => $postMaxSize,
                'required' => '10M',
                'message' => "Post max size {$postMaxSize} is sufficient",
            ];
        } else {
            $this->warnings[] = [
                'type' => 'post_max_size',
                'message' => "Post max size {$postMaxSize} may be too low for file uploads modify this in your /etc/php/8.3/fpm/php.ini file. (post_max_size = 10M)",
                'current' => $postMaxSize,
                'recommended' => '10M',
            ];
        }

        // Check max input vars
        $maxInputVars = ini_get('max_input_vars');
        if ($maxInputVars >= 1000) {
            $this->results['max_input_vars'] = [
                'status' => 'pass',
                'current' => $maxInputVars,
                'message' => 'Max input vars is adequate',
            ];
        } else {
            $this->warnings[] = [
                'type' => 'max_input_vars',
                'message' => "Max input vars {$maxInputVars} may be too low for complex forms",
                'current' => $maxInputVars,
                'recommended' => '1000',
            ];
        }

        // Check max file uploads
        $maxFileUploads = ini_get('max_file_uploads');
        if ($maxFileUploads >= 20) {
            $this->results['max_file_uploads'] = [
                'status' => 'pass',
                'current' => $maxFileUploads,
                'message' => 'Max file uploads is adequate',
            ];
        } else {
            $this->warnings[] = [
                'type' => 'max_file_uploads',
                'message' => "Max file uploads {$maxFileUploads} may be too low for image hosting",
                'current' => $maxFileUploads,
                'recommended' => '20',
            ];
        }

        // Check display errors (should be off in production)
        $displayErrors = ini_get('display_errors');
        if ($displayErrors === 'Off' || $displayErrors === '0') {
            $this->results['display_errors'] = [
                'status' => 'pass',
                'current' => $displayErrors,
                'message' => 'Display errors is disabled (secure)',
            ];
        } else {
            $this->warnings[] = [
                'type' => 'display_errors',
                'message' => 'Display errors is enabled (security risk in production) modify this in your /etc/php/8.3/fpm/php.ini file. (display_errors = Off)',
                'current' => $displayErrors,
                'recommended' => 'Off',
            ];
        }

        // Check log errors
        $logErrors = ini_get('log_errors');
        if ($logErrors === 'On' || $logErrors === '1') {
            $this->results['log_errors'] = [
                'status' => 'pass',
                'current' => $logErrors,
                'message' => 'Error logging is enabled',
            ];
        } else {
            $this->warnings[] = [
                'type' => 'log_errors',
                'message' => 'Error logging is disabled (recommended for debugging)',
                'current' => $logErrors,
                'recommended' => 'On',
            ];
        }

        // Check allow url fopen
        $allowUrlFopen = ini_get('allow_url_fopen');
        if ($allowUrlFopen === 'On' || $allowUrlFopen === '1') {
            $this->results['allow_url_fopen'] = [
                'status' => 'pass',
                'current' => $allowUrlFopen,
                'message' => 'URL fopen is enabled (required for some features)',
            ];
        } else {
            $this->warnings[] = [
                'type' => 'allow_url_fopen',
                'message' => 'URL fopen is disabled (may affect some features)',
                'current' => $allowUrlFopen,
                'recommended' => 'On',
            ];
        }

        // Check timezone
        $timezone = ini_get('date.timezone');
        if (!empty($timezone)) {
            $this->results['timezone'] = [
                'status' => 'pass',
                'current' => $timezone,
                'message' => "Timezone is set to {$timezone}",
            ];
        } else {
            $this->warnings[] = [
                'type' => 'timezone',
                'message' => 'Timezone is not set (may cause date/time issues)',
                'current' => 'Not set',
                'recommended' => 'UTC or your local timezone',
            ];
        }
    }

    /**
     * Check security-related settings.
     */
    private function checkSecuritySettings(): void
    {
        // Check expose PHP
        $exposePhp = ini_get('expose_php');
        if ($exposePhp === 'Off' || $exposePhp === '0') {
            $this->results['expose_php'] = [
                'status' => 'pass',
                'current' => $exposePhp,
                'message' => 'PHP exposure is disabled (secure)',
            ];
        } else {
            $this->warnings[] = [
                'type' => 'expose_php',
                'message' => 'PHP exposure is enabled (security risk in production) modify this in your /etc/php/8.3/fpm/php.ini file. (expose_php = Off)',
                'current' => $exposePhp,
                'recommended' => 'Off',
            ];
        }
    }

    /**
     * Check Redis connectivity.
     */
    private function checkRedisConnectivity(): void
    {
        if (extension_loaded('redis')) {
            try {
                $redis = new \Redis();
                $redis->connect('127.0.0.1', 6379, 2);
                if ($redis->isConnected() && $redis->ping()) {
                    $this->results['redis_connectivity'] = [
                        'status' => 'pass',
                        'message' => 'Redis connection successful',
                    ];
                } else {
                    $this->warnings[] = [
                        'type' => 'redis_connectivity',
                        'message' => 'Redis connection failed (service not responding)',
                    ];
                }
            } catch (\Exception $e) {
                $this->warnings[] = [
                    'type' => 'redis_connectivity',
                    'message' => 'Redis connection failed (service may not be running)',
                    'error' => $e->getMessage(),
                ];
            }
        } else {
            $this->warnings[] = [
                'type' => 'redis_connectivity',
                'message' => 'Redis extension not loaded (caching may be affected)',
            ];
        }
    }

    /**
     * Check environment file.
     */
    private function checkEnvironmentFile(): void
    {
        $envFile = getcwd() . '/backend/storage/.env';
        if (file_exists($envFile)) {
            $this->results['environment_file'] = [
                'status' => 'pass',
                'message' => 'Environment file (.env) exists',
            ];
        } else {
            $this->errors[] = [
                'type' => 'environment_file',
                'message' => 'Environment file (.env) is missing',
            ];
        }
    }

    /**
     * Check Composer dependencies.
     */
    private function checkComposerDependencies(): void
    {
        $composerLock = getcwd() . '/backend/composer.lock';
        $vendorDir = getcwd() . '/backend/storage/packages';

        if (file_exists($composerLock)) {
            $this->results['composer_lock'] = [
                'status' => 'pass',
                'message' => 'Composer lock file exists',
            ];
        } else {
            $this->warnings[] = [
                'type' => 'composer_lock',
                'message' => 'Composer lock file missing (run composer install)',
            ];
        }

        if (is_dir($vendorDir)) {
            $this->results['vendor_directory'] = [
                'status' => 'pass',
                'message' => 'Vendor directory exists',
            ];
        } else {
            $this->errors[] = [
                'type' => 'vendor_directory',
                'message' => 'Vendor directory missing (run composer install)',
            ];
        }
    }

    /**
     * Parse memory limit string to bytes.
     */
    private function parseMemoryLimit(string $memoryLimit): int
    {
        // Handle unlimited memory (-1)
        if ($memoryLimit === '-1') {
            return PHP_INT_MAX;
        }

        $unit = strtolower(substr($memoryLimit, -1));
        $value = (int) substr($memoryLimit, 0, -1);

        switch ($unit) {
            case 'g':
                return $value * 1024 * 1024 * 1024;
            case 'm':
                return $value * 1024 * 1024;
            case 'k':
                return $value * 1024;
            default:
                return $value;
        }
    }
}
