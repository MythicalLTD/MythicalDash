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

class FetchProxy extends App implements CommandBuilder
{
    public static function execute(array $args): void
    {
        $startTime = microtime(true);
        $app = App::getInstance();
        if (!file_exists(__DIR__ . '/../../../storage/.env')) {
            \MythicalDash\App::getInstance(true)->getLogger()->warning('Executed a command without a .env file');
            $app->send('The .env file does not exist. Please create one before running this command');
            exit;
        }

        try {
            \MythicalDash\App::getInstance(true)->loadEnv();
            if (isset($_ENV['DATABASE_HOST']) && isset($_ENV['DATABASE_DATABASE']) && isset($_ENV['DATABASE_USER']) && isset($_ENV['DATABASE_PASSWORD']) && isset($_ENV['DATABASE_PORT'])) {
                $db = new Database($_ENV['DATABASE_HOST'], $_ENV['DATABASE_DATABASE'], $_ENV['DATABASE_USER'], $_ENV['DATABASE_PASSWORD'], $_ENV['DATABASE_PORT']);
            } else {
                $app->send('&cFailed to connect to the database: &rDatabase connection failed!');
                exit;
            }
            $db = $db->getPdo();
        } catch (\Exception $e) {
            $app->send('&cFailed to connect to the database: &r' . $e->getMessage());
            exit;
        }

        try {
            $db->query('SET foreign_key_checks = 0');
            $db->query('TRUNCATE TABLE mythicaldash_proxylist');

            $app->send('&aStarting proxy fetch operation...');
            $stats = self::fetchProxys($db);
            $db->query('SET foreign_key_checks = 1');

            $endTime = microtime(true);
            $executionTime = round($endTime - $startTime, 2);

            $app->send('&aProxy fetch completed successfully!');
            $app->send('&eSummary:');
            $app->send('&e- Total proxies fetched: &r' . $stats['total']);
            $app->send('&e- Valid IPs inserted: &r' . $stats['valid']);
            $app->send('&e- Failed sources: &r' . $stats['failed_sources']);
            $app->send('&e- Execution time: &r' . $executionTime . ' seconds');
        } catch (\Exception $e) {
            $app->send('&cFailed to rebuild the database: &r' . $e->getMessage());
            exit;
        }
        exit;
    }

    public static function getDescription(): string
    {
        return 'Updates the proxy list for vpn and proxy check';
    }

    public static function getSubCommands(): array
    {
        return [];
    }

    public static function fetchProxys(\PDO $db): array
    {
        $app = App::getInstance();
        $stats = [
            'total' => 0,
            'valid' => 0,
            'failed_sources' => 0,
            'invalid' => 0,
        ];

        $proxyList = self::proxyList();
        $totalSources = count($proxyList);

        // Initialize curl multi handle
        $mh = curl_multi_init();
        $handles = [];
        $results = [];

        // Setup curl handles for all sources
        foreach ($proxyList as $index => $proxyUrl) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $proxyUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_USERAGENT, 'MythicalDash/1.0');
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_NOPROGRESS, false);

            // Set up progress tracking
            $results[$index] = [
                'url' => $proxyUrl,
                'status' => 'pending',
                'progress' => 0,
                'downloaded' => 0,
                'total' => 0,
                'started' => false,
            ];

            curl_setopt($ch, CURLOPT_PROGRESSFUNCTION, function ($downloadSize, $downloaded, $uploadSize, $uploaded) use ($index, &$results) {
                if ($downloadSize > 0) {
                    $results[$index]['started'] = true;
                    $results[$index]['total'] = $downloadSize;
                    $results[$index]['downloaded'] = $downloaded;
                    $results[$index]['progress'] = min(100, $downloaded / $downloadSize * 100);
                    $results[$index]['status'] = 'processing';
                }
            });

            curl_multi_add_handle($mh, $ch);
            $handles[$index] = $ch;
        }

        // Function to display progress bars
        $displayProgress = function () use ($results, $app) {
            $app->send("\n&aFetching proxies from multiple sources...");

            foreach ($results as $index => $result) {
                $status = match($result['status']) {
                    'pending' => '&e⏳ Pending',
                    'processing' => '&b🔄 Processing',
                    'success' => '&a✓ Success',
                    'failed' => '&c✗ Failed',
                    default => '&e⏳ Pending',
                };

                $sizeInfo = '';
                if ($result['total'] > 0) {
                    $downloaded = self::formatBytes($result['downloaded']);
                    $total = self::formatBytes($result['total']);
                    $sizeInfo = " ({$downloaded}/{$total})";
                }

                $app->send(sprintf(
                    '&eSource %d: %s %s%s',
                    $index + 1,
                    $status,
                    $result['url'],
                    $sizeInfo
                ));
            }
        };

        // Process curl handles
        $running = null;
        $lastUpdate = 0;
        do {
            $status = curl_multi_exec($mh, $running);

            // Check for completed transfers
            while ($info = curl_multi_info_read($mh)) {
                $index = array_search($info['handle'], $handles);
                if ($index !== false) {
                    $httpCode = curl_getinfo($info['handle'], CURLINFO_HTTP_CODE);
                    if ($httpCode === 200) {
                        $results[$index]['status'] = 'success';
                        $results[$index]['progress'] = 100;
                    } else {
                        $results[$index]['status'] = 'failed';
                    }
                }
            }

            // Update status for handles that have started
            foreach ($handles as $index => $ch) {
                $info = curl_getinfo($ch);
                if ($info['total_time'] > 0 && !$results[$index]['started']) {
                    $results[$index]['started'] = true;
                    $results[$index]['status'] = 'processing';
                }
            }

            // Update display every second
            $currentTime = microtime(true);
            if ($currentTime - $lastUpdate >= 1.0) {
                $displayProgress();
                $lastUpdate = $currentTime;
            }

            if ($running) {
                curl_multi_select($mh, 0.1);
            }
        } while ($running > 0);

        // Process results
        foreach ($handles as $index => $ch) {
            $content = curl_multi_getcontent($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if ($content !== false && $httpCode === 200) {
                $results[$index]['status'] = 'success';
                $results[$index]['progress'] = 100;
                $proxies = array_filter(explode("\n", $content), 'trim');
                $stats['total'] += count($proxies);

                $stmt = $db->prepare('INSERT INTO mythicaldash_proxylist (ip) VALUES (:ip)');

                foreach ($proxies as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP)) {
                        $stmt->execute(['ip' => $ip]);
                        ++$stats['valid'];
                    } else {
                        ++$stats['invalid'];
                    }
                }
            } else {
                $results[$index]['status'] = 'failed';
                ++$stats['failed_sources'];
            }

            curl_multi_remove_handle($mh, $ch);
            curl_close($ch);
        }

        curl_multi_close($mh);

        // Final progress display
        $displayProgress();

        return $stats;
    }

    /**
     * Get the proxy list from the internet.
     *
     * @return string[]
     */
    public static function proxyList(): array
    {
        return [
            'https://raw.githubusercontent.com/TheSpeedX/PROXY-List/master/http.txt',
            'https://raw.githubusercontent.com/clarketm/proxy-list/master/proxy-list-raw.txt',
            'https://raw.githubusercontent.com/scriptzteam/ProtonVPN-VPN-IPs/main/exit_ips.txt',
            'https://raw.githubusercontent.com/mmpx12/proxy-list/master/ips-list.txt',
            'https://check.torproject.org/torbulkexitlist?ip=1.1.1.1',
            'https://cinsscore.com/list/ci-badguys.txt',
            'https://lists.blocklist.de/lists/all.txt',
            'https://blocklist.greensnow.co/greensnow.txt',
            'https://raw.githubusercontent.com/firehol/blocklist-ipsets/master/stopforumspam_7d.ipset',
            'https://raw.githubusercontent.com/jetkai/proxy-list/main/online-proxies/txt/proxies.txt',
            'https://raw.githubusercontent.com/monosans/proxy-list/main/proxies/socks4.txt',
        ];
    }

    /**
     * Format bytes to human readable format.
     */
    private static function formatBytes($bytes, $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
