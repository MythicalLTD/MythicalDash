<?php

namespace MythicalDash\Cron;

use MythicalDash\Chat\TimedTask;
use MythicalDash\Cron\Cron;
use MythicalDash\Cron\TimeTask;
use PDO;

class ProxyListProcessor implements TimeTask
{
	private function proxyList(): array {
		return [
			"https://raw.githubusercontent.com/TheSpeedX/PROXY-List/master/http.txt",
			"https://raw.githubusercontent.com/clarketm/proxy-list/master/proxy-list-raw.txt",
			"https://raw.githubusercontent.com/scriptzteam/ProtonVPN-VPN-IPs/main/exit_ips.txt",
			"https://raw.githubusercontent.com/mmpx12/proxy-list/master/ips-list.txt",
			"https://check.torproject.org/torbulkexitlist?ip=1.1.1.1",
			"https://cinsscore.com/list/ci-badguys.txt",
			"https://lists.blocklist.de/lists/all.txt",
			"https://blocklist.greensnow.co/greensnow.txt",
			"https://raw.githubusercontent.com/firehol/blocklist-ipsets/master/stopforumspam_7d.ipset",
			"https://raw.githubusercontent.com/jetkai/proxy-list/main/online-proxies/txt/proxies.txt",
			"https://raw.githubusercontent.com/monosans/proxy-list/main/proxies/socks4.txt",
		];
	}

	private function formatBytes($bytes, $precision = 2): string {
		$units = ['B', 'KB', 'MB', 'GB', 'TB'];
		$bytes = max($bytes, 0);
		$pow = floor(($bytes ? log($bytes) : 0) / log(1024));
		$pow = min($pow, count($units) - 1);
		$bytes /= pow(1024, $pow);
		return round($bytes, $precision) . ' ' . $units[$pow];
	}

	private function fetchProxies(PDO $db): array {
		$stats = [
			'total' => 0,
			'valid' => 0,
			'failed_sources' => 0,
			'invalid' => 0
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
						$stats['valid']++;
					} else {
						$stats['invalid']++;
					}
				}
			} else {
				$stats['failed_sources']++;
			}

			curl_close($ch);
		}

		return $stats;
	}

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
				TimedTask::markRun("proxy-list-processor", true, "Proxy list heartbeat " . $stats['valid'] . ' valid proxies and ' . $stats['invalid'] . ' invalid proxies');
			});
		} catch (\Exception $e) {
			$app = \MythicalDash\App::getInstance(false, true);
			TimedTask::markRun("proxy-list-processor", false, $e->getMessage());
			$app->getLogger()->error('Failed to update proxy list: ' . $e->getMessage());
		}
	}
}