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

namespace MythicalDash\Cron;

use MythicalDash\Chat\TimedTask;

class ProxyListProcessor implements TimeTask
{
    public function run()
    {
        $cron = new Cron('proxy-list-processor', '7D');
        try {
            $cron->runIfDue(function () {
                $app = \MythicalDash\App::getInstance(false, true);
                $db = $app->getDatabase()->getPdo();

                // Only clear existing proxies ONCE before fetching all sources
                $db->query('SET foreign_key_checks = 0');
                $db->query('TRUNCATE TABLE mythicaldash_proxylist');

                // Fetch new proxies from all sources
                $stats = $this->fetchProxies($db);

                $db->query('SET foreign_key_checks = 1');

                // Log results
                $app->getLogger()->info('Proxy list updated successfully ' . $stats['valid'] . ' valid proxies and ' . $stats['invalid'] . ' invalid proxies');
                TimedTask::markRun('proxy-list-processor', true, 'Proxy list heartbeat ' . $stats['valid'] . ' valid proxies and ' . $stats['invalid'] . ' invalid proxies');
            });
        } catch (\Exception $e) {
            $app = \MythicalDash\App::getInstance(false, true);
            TimedTask::markRun('proxy-list-processor', false, $e->getMessage());
            $app->getLogger()->error('Failed to update proxy list: ' . $e->getMessage());
        }
    }

    private function proxyList(): array
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

    private function formatBytes($bytes, $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    private function fetchProxies(\PDO $db): array
    {
        $stats = [
            'total' => 0,
            'valid' => 0,
            'failed_sources' => 0,
            'invalid' => 0,
        ];

        // Instead of multi-curl, process each source one by one and only clear the DB once at the start
        foreach ($this->proxyList() as $proxyUrl) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $proxyUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_USERAGENT, 'MythicalDash/1.0');
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

            $content = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if ($content !== false && $httpCode === 200) {
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
                ++$stats['failed_sources'];
            }

            curl_close($ch);
        }

        return $stats;
    }
}
