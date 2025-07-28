<?php

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
     * Run all health checks
     */
    public function run(): array
    {
        $this->checkPhpVersion();
        $this->checkRequiredExtensions();
        $this->checkOptionalExtensions();
        $this->checkSystemRequirements();
        $this->checkDirectoryPermissions();
        $this->checkCronFile();

        return [
            'status' => empty($this->errors) ? 'healthy' : 'unhealthy',
            'errors' => $this->errors,
            'warnings' => $this->warnings,
            'results' => $this->results
        ];
    }

    /**
     * Check PHP version compatibility
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
                'message' => "PHP version {$currentVersion} meets requirements"
            ];
        } else {
            $this->errors[] = [
                'type' => 'php_version',
                'message' => "PHP version {$currentVersion} is below required version {$requiredVersion}",
                'current' => $currentVersion,
                'required' => $requiredVersion
            ];
        }
    }

    /**
     * Check required PHP extensions
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
			'excimer',
			'redis'
        ];

        foreach ($requiredExtensions as $extension) {
            if (extension_loaded($extension)) {
                $this->results["ext_{$extension}"] = [
                    'status' => 'pass',
                    'message' => "Extension {$extension} is loaded"
                ];
            } else {
                $this->errors[] = [
                    'type' => 'missing_extension',
                    'extension' => $extension,
                    'message' => "Required extension {$extension} is not loaded"
                ];
            }
        }
    }

    /**
     * Check optional PHP extensions
     */
    private function checkOptionalExtensions(): void
    {
        $optionalExtensions = [
            'memcached' => 'Memcached caching support',
            'apcu' => 'APCu caching support',
            'imagick' => 'ImageMagick for advanced image processing',
            'sodium' => 'Libsodium for enhanced encryption',
            'dom' => 'DOM processing support'
        ];

        foreach ($optionalExtensions as $extension => $description) {
            if (extension_loaded($extension)) {
                $this->results["ext_{$extension}"] = [
                    'status' => 'pass',
                    'message' => "Optional extension {$extension} is loaded",
                    'description' => $description
                ];
            } else {
                $this->warnings[] = [
                    'type' => 'optional_extension',
                    'extension' => $extension,
                    'message' => "Optional extension {$extension} is not loaded",
                    'description' => $description
                ];
            }
        }
    }

    /**
     * Check system requirements
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
                'message' => "Memory limit is unlimited (optimal)"
            ];
        } elseif ($memoryLimitBytes >= $requiredMemory) {
            $this->results['memory_limit'] = [
                'status' => 'pass',
                'current' => $memoryLimit,
                'required' => '128M',
                'message' => "Memory limit {$memoryLimit} is sufficient"
            ];
        } else {
            $this->warnings[] = [
                'type' => 'memory_limit',
                'message' => "Memory limit {$memoryLimit} may be too low for optimal performance",
                'current' => $memoryLimit,
                'recommended' => '128M'
            ];
        }

        // Check max execution time
        $maxExecutionTime = ini_get('max_execution_time');
        if ($maxExecutionTime >= 30 || $maxExecutionTime == 0) {
            $this->results['max_execution_time'] = [
                'status' => 'pass',
                'current' => $maxExecutionTime,
                'message' => "Max execution time is adequate"
            ];
        } else {
            $this->warnings[] = [
                'type' => 'max_execution_time',
                'message' => "Max execution time {$maxExecutionTime}s may be too low",
                'current' => $maxExecutionTime,
                'recommended' => '30s or 0 (unlimited)'
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
                'message' => "Upload max filesize is unlimited (optimal)"
            ];
        } elseif ($uploadMaxFilesizeBytes >= $requiredUploadSize) {
            $this->results['upload_max_filesize'] = [
                'status' => 'pass',
                'current' => $uploadMaxFilesize,
                'required' => '10M',
                'message' => "Upload max filesize {$uploadMaxFilesize} is sufficient"
            ];
        } else {
            $this->warnings[] = [
                'type' => 'upload_max_filesize',
                'message' => "Upload max filesize {$uploadMaxFilesize} may be too low",
                'current' => $uploadMaxFilesize,
                'recommended' => '10M'
            ];
        }
    }

    /**
     * Check directory permissions
     */
    private function checkDirectoryPermissions(): void
    {
        $directories = [
            'storage' => 'storage/',
            'logs' => 'storage/logs/',
            'cache' => 'storage/cache/',
            'uploads' => 'storage/uploads/',
            'temp' => 'storage/temp/'
        ];

        foreach ($directories as $name => $path) {
            $fullPath = getcwd() . '/' . $path;
            
            if (!is_dir($fullPath)) {
                if (!mkdir($fullPath, 0755, true)) {
                    $this->errors[] = [
                        'type' => 'directory_permission',
                        'directory' => $path,
                        'message' => "Cannot create directory {$path}"
                    ];
                    continue;
                }
            }

            if (is_writable($fullPath)) {
                $this->results["dir_{$name}"] = [
                    'status' => 'pass',
                    'path' => $path,
                    'message' => "Directory {$path} is writable"
                ];
            } else {
                $this->errors[] = [
                    'type' => 'directory_permission',
                    'directory' => $path,
                    'message' => "Directory {$path} is not writable"
                ];
            }
        }
    }

    /**
     * Check if cron check file exists
     */
    private function checkCronFile(): void
    {
        $cronFile = 'backend/storage/caches/cron/a-check-cron.mydtt';
        if (file_exists($cronFile)) {
            $this->results['cron_file'] = [
                'status' => 'pass',
                'message' => 'Cron check file exists: cron jobs are likely to run.'
            ];
        } else {
            $this->errors[] = [
                'type' => 'cron_file',
                'message' => 'Cron check file missing: cron jobs may not run!'
            ];
        }
    }


  

    /**
     * Parse memory limit string to bytes
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

    /**
     * Get formatted health check report
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
        $report .= "  • Total checks: " . count($this->results) . "\n";
        $report .= "  • Passed: " . count(array_filter($this->results, fn($r) => $r['status'] === 'pass')) . "\n";
        $report .= "  • Errors: " . count($this->errors) . "\n";
        $report .= "  • Warnings: " . count($this->warnings) . "\n";

        return $report;
    }
}