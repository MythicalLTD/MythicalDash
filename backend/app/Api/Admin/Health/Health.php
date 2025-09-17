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

use MythicalDash\App;
use MythicalDash\Permissions;
use MythicalDash\Chat\TimedTask;
use MythicalDash\Middleware\PermissionMiddleware;
use MythicalDash\Services\Cloud\MythicalCloudLogs;

$router->get('/api/admin/health', function (): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyGET();

    $session = new MythicalDash\Chat\User\Session($appInstance);
    $accountToken = $session->SESSION_KEY;

    PermissionMiddleware::handle($appInstance, Permissions::ADMIN_HEALTH_VIEW, $session);
    // Get database connection
    $db = $appInstance->getDatabase()->getPdo();

    // Get database size
    $dbSize = 0;
    $dbTables = $db->query('SHOW TABLE STATUS')->fetchAll(PDO::FETCH_ASSOC);
    foreach ($dbTables as $table) {
        $dbSize += $table['Data_length'] + $table['Index_length'];
    }

    // Get active connections
    $activeConnections = $db->query("SHOW STATUS WHERE Variable_name = 'Threads_connected'")->fetch(PDO::FETCH_ASSOC)['Value'];
    $maxConnections = $db->query("SHOW VARIABLES LIKE 'max_connections'")->fetch(PDO::FETCH_ASSOC)['Value'];

    // Get server load
    $load = sys_getloadavg();

    // Get CPU info
    $cpuInfo = file_exists('/proc/cpuinfo') ? file_get_contents('/proc/cpuinfo') : '';
    $cpuCores = substr_count($cpuInfo, 'processor');

    // Get memory info
    $memInfo = file_exists('/proc/meminfo') ? file_get_contents('/proc/meminfo') : '';
    preg_match_all('/^(\w+):\s+(\d+)/m', $memInfo, $matches);
    $memInfo = array_combine($matches[1], $matches[2]);

    $health = [
        'status' => 'healthy',
        'timestamp' => time(),
        'system' => [
            'installation_path' => [
                'current' => realpath(__DIR__ . '/../../../../../'),
                'expected' => '/var/www/mythicaldash-v3',
                'status' => realpath(__DIR__ . '/../../../../../') === '/var/www/mythicaldash-v3' ? 'ok' : 'warning',
            ],
            'php_version' => [
                'current' => PHP_VERSION,
                'recommended' => '8.1.0',
                'status' => version_compare(PHP_VERSION, '8.1.0', '>=') ? 'ok' : 'warning',
            ],
            'memory_usage' => [
                'current' => memory_get_usage(true),
                'peak' => memory_get_peak_usage(true),
                'limit' => ini_get('memory_limit'),
                'status' => 'ok',
            ],
            'disk_space' => [
                'free' => disk_free_space('/'),
                'total' => disk_total_space('/'),
                'used' => disk_total_space('/') - disk_free_space('/'),
                'status' => 'ok',
            ],
            'max_execution_time' => [
                'current' => ini_get('max_execution_time'),
                'recommended' => 300,
                'status' => ini_get('max_execution_time') >= 300 ? 'ok' : 'warning',
            ],
            'max_input_time' => [
                'current' => ini_get('max_input_time'),
                'recommended' => 300,
                'status' => ini_get('max_input_time') >= 300 ? 'ok' : 'warning',
            ],
            'upload_max_filesize' => [
                'current' => ini_get('upload_max_filesize'),
                'recommended' => '64M',
                'status' => 'ok',
            ],
            'post_max_size' => [
                'current' => ini_get('post_max_size'),
                'recommended' => '64M',
                'status' => 'ok',
            ],
            'server_load' => [
                '1min' => $load[0],
                '5min' => $load[1],
                '15min' => $load[2],
                'cpu_cores' => $cpuCores,
                'status' => ($load[0] < $cpuCores) ? 'ok' : 'warning',
            ],
            'system_memory' => [
                'total' => $memInfo['MemTotal'] ?? 0,
                'free' => $memInfo['MemFree'] ?? 0,
                'cached' => $memInfo['Cached'] ?? 0,
                'buffers' => $memInfo['Buffers'] ?? 0,
                'status' => 'ok',
            ],
            'opcache' => [
                'enabled' => ini_get('opcache.enable'),
                'memory_usage' => opcache_get_status()['memory_usage'] ?? null,
                'status' => ini_get('opcache.enable') ? 'ok' : 'warning',
            ],
        ],
        'database' => [
            'size' => [
                'current' => $dbSize,
                'formatted' => number_format($dbSize / 1024 / 1024, 2) . ' MB',
                'status' => 'ok',
            ],
            'connections' => [
                'active' => $activeConnections,
                'max' => $maxConnections,
                'status' => ($activeConnections < $maxConnections * 0.8) ? 'ok' : 'warning',
            ],
            'tables' => [
                'count' => count($dbTables),
                'status' => 'ok',
            ],
            'slow_queries' => [
                'count' => $db->query("SHOW GLOBAL STATUS LIKE 'Slow_queries'")->fetch(PDO::FETCH_ASSOC)['Value'],
                'status' => 'ok',
            ],
        ],
        'extensions' => [
            'required' => [
                'curl' => [
                    'installed' => extension_loaded('curl'),
                    'status' => extension_loaded('curl') ? 'ok' : 'error',
                ],
                'json' => [
                    'installed' => extension_loaded('json'),
                    'status' => extension_loaded('json') ? 'ok' : 'error',
                ],
                'pdo' => [
                    'installed' => extension_loaded('pdo'),
                    'status' => extension_loaded('pdo') ? 'ok' : 'error',
                ],
                'mbstring' => [
                    'installed' => extension_loaded('mbstring'),
                    'status' => extension_loaded('mbstring') ? 'ok' : 'error',
                ],
                'openssl' => [
                    'installed' => extension_loaded('openssl'),
                    'status' => extension_loaded('openssl') ? 'ok' : 'error',
                ],
                'gd' => [
                    'installed' => extension_loaded('gd'),
                    'status' => extension_loaded('gd') ? 'ok' : 'error',
                ],
                'zip' => [
                    'installed' => extension_loaded('zip'),
                    'status' => extension_loaded('zip') ? 'ok' : 'error',
                ],
                'fileinfo' => [
                    'installed' => extension_loaded('fileinfo'),
                    'status' => extension_loaded('fileinfo') ? 'ok' : 'error',
                ],
            ],
        ],
        'permissions' => [
            'storage' => [
                'writable' => is_writable(__DIR__ . '/../../../../storage'),
                'status' => is_writable(__DIR__ . '/../../../../storage') ? 'ok' : 'error',
            ],
            'logs' => [
                'writable' => is_writable(__DIR__ . '/../../../../storage/logs'),
                'status' => is_writable(__DIR__ . '/../../../../storage/logs') ? 'ok' : 'error',
            ],
            'addons' => [
                'writable' => is_writable(__DIR__ . '/../../../../storage/addons'),
                'status' => is_writable(__DIR__ . '/../../../../storage/addons') ? 'ok' : 'error',
            ],
            'caches' => [
                'writable' => is_writable(__DIR__ . '/../../../../storage/caches'),
                'status' => is_writable(__DIR__ . '/../../../../storage/caches') ? 'ok' : 'error',
            ],
        ],
    ];

    // Check if any component has an error status
    foreach ($health['extensions']['required'] as $extension) {
        if ($extension['status'] === 'error') {
            $health['status'] = 'unhealthy';
            break;
        }
    }

    foreach ($health['permissions'] as $permission) {
        if ($permission['status'] === 'error') {
            $health['status'] = 'unhealthy';
            break;
        }
    }

    // Check disk space warning (if less than 10% free)
    $diskFreePercent = ($health['system']['disk_space']['free'] / $health['system']['disk_space']['total']) * 100;
    if ($diskFreePercent < 10) {
        $health['system']['disk_space']['status'] = 'warning';
        $health['status'] = 'warning';
    }

    // Check memory usage warning (if more than 80% used)
    $memoryUsedPercent = ($health['system']['memory_usage']['current'] / $health['system']['memory_usage']['peak']) * 100;
    if ($memoryUsedPercent > 80) {
        $health['system']['memory_usage']['status'] = 'warning';
        $health['status'] = 'warning';
    }

    // Check database connection warning (if more than 80% of max connections)
    if ($health['database']['connections']['active'] > $health['database']['connections']['max'] * 0.8) {
        $health['database']['connections']['status'] = 'warning';
        $health['status'] = 'warning';
    }

    // Check server load warning (if load average exceeds CPU cores)
    if ($health['system']['server_load']['1min'] > $health['system']['server_load']['cpu_cores']) {
        $health['system']['server_load']['status'] = 'warning';
        $health['status'] = 'warning';
    }

    // Recent cron/timed task heartbeats
    $recentCronsRaw = TimedTask::getAll(null, 10, 0);
    $now = time();
    $expectedMap = [
        'mail-sender' => 60, // 1 minute
        'a-check-cron' => 60, // 1 minute
        'server-deploy' => 60, // 1 minute
        'proxy-list-processor' => 604800, // 7 days
        'renew-worker' => 86400, // 1 day
        'update-env' => 3600, // 1 hour
        'daily-backup-job' => 86400, // 1 day
    ];
    $recentCrons = array_map(function ($row) use ($now, $expectedMap) {
        $name = $row['task_name'] ?? '';
        $lastRunAt = isset($row['last_run_at']) && $row['last_run_at'] !== null ? strtotime($row['last_run_at']) : null;
        $expected = $expectedMap[$name] ?? 300; // default 5 minutes if unknown
        $late = $lastRunAt ? (($now - $lastRunAt) > ($expected * 2)) : true; // late if never ran or >2x expected

        return [
            'id' => (int) ($row['id'] ?? 0),
            'task_name' => $name,
            'last_run_at' => $row['last_run_at'] ?? null,
            'last_run_success' => (int) ($row['last_run_success'] ?? 0) === 1,
            'last_run_message' => $row['last_run_message'] ?? null,
            'expected_interval_seconds' => $expected,
            'late' => $late,
        ];
    }, $recentCronsRaw);

    $appInstance->OK('Health check passed', [
        'health' => $health,
        'cron' => [
            'recent' => $recentCrons,
            'summary' => empty($recentCrons) ? 'Cron tasks have not run yet.' : null,
        ],
    ]);

});

$router->post('/api/admin/logs/upload', function (): void {
    App::init();
    $appInstance = App::getInstance(true);
    $appInstance->allowOnlyPOST();

    $session = new MythicalDash\Chat\User\Session($appInstance);
    $accountToken = $session->SESSION_KEY;

    PermissionMiddleware::handle($appInstance, Permissions::ADMIN_DASHBOARD_VIEW, $session);
    // Get logs directory

    // Upload dashboard logs
    $dashboardLogsUrl = MythicalCloudLogs::uploadDashboardLogsToCloud();

    // Upload web server logs
    $webServerLogsUrl = MythicalCloudLogs::uploadWebServerLogsToCloud();

    if ($dashboardLogsUrl && $webServerLogsUrl) {
        $appInstance->OK('Logs uploaded successfully', ['dashboard_logs_url' => $dashboardLogsUrl, 'web_server_logs_url' => $webServerLogsUrl]);
    } else {
        $appInstance->BadRequest('Failed to upload logs', ['error_code' => 'LOG_UPLOAD_FAILED']);
    }
});
