<?php

use MythicalDash\Plugins\PluginManager;
define('APP_STARTUP', microtime(true));
define('APP_START', microtime(true));
define('APP_PUBLIC', __DIR__);
define('APP_DIR', APP_PUBLIC . '/../../');
define('APP_STORAGE_DIR', APP_DIR . 'storage/');
define('APP_CACHE_DIR', APP_STORAGE_DIR . 'caches');
define('APP_CRON_DIR', APP_STORAGE_DIR . 'cron');
define('APP_LOGS_DIR', APP_STORAGE_DIR . 'logs');
define('APP_ADDONS_DIR', APP_STORAGE_DIR . 'addons');
define('APP_SOURCECODE_DIR', APP_DIR . 'app');
define('APP_ROUTES_DIR', APP_SOURCECODE_DIR . '/Api');
define('APP_DEBUG', true);
define('SYSTEM_OS_NAME', gethostname() . '/' . PHP_OS_FAMILY);
define('SYSTEM_KERNEL_NAME', php_uname('s'));
define('TELEMETRY', true);
define('APP_VERSION', '3.0.0.1-remastered');
define('APP_UPSTREAM', 'github.com/mythicalltd/mythicaldash');

require(__DIR__ . '/../packages/autoload.php');

use MythicalDash\Cli\App;
use MythicalDash\App as NormalApp;

$pluginManager = new PluginManager();
$app = new NormalApp(false,true);

App::sendOutputWithNewLine('&7Starting MythicalDash cron runner.');

// Run main cronjobs
foreach (glob(__DIR__ . '/php/*.php') as $file) {
	App::sendOutputWithNewLine("");
	App::sendOutputWithNewLine("|----");
	require_once $file;
	$className = 'MythicalDash\Cron\\' . basename($file, '.php');
	try {
		if (class_exists($className)) {
			$worker = new $className();
			App::sendOutputWithNewLine('&7Running &d' . $className. '&7.');
			$worker->run();
			App::sendOutputWithNewLine('&7Finished running &d' . $className . '&7.');
		} else {
			App::sendOutputWithNewLine('&7Class &d' . $className . '&7 not found');
		}
	} catch (\Exception $e) {
		App::sendOutputWithNewLine('&7Error running &d' . $className . '&7: &c' . $e->getMessage());
	}
}

// Run addon cronjobs
$addonsDir = APP_ADDONS_DIR;
if (is_dir($addonsDir)) {
	$plugins = array_diff(scandir($addonsDir), ['.', '..']);
	foreach ($plugins as $plugin) {
		$cronDir = $addonsDir . '/' . $plugin . '/Cron';
		if (!is_dir($cronDir)) {
			continue;
		}

		foreach (glob($cronDir . '/*.php') as $file) {
			App::sendOutputWithNewLine("");
			App::sendOutputWithNewLine("|----");
			require_once $file;
			$className = 'MythicalDash\Addons\\' . $plugin . '\Cron\\' . basename($file, '.php');
			try {
				if (class_exists($className)) {
					$worker = new $className();
					App::sendOutputWithNewLine('&7Running &d' . $className. '&7.');
					$worker->run();
					App::sendOutputWithNewLine('&7Finished running &d' . $className . '&7.');
				} else {
					App::sendOutputWithNewLine('&7Class &d' . $className . '&7 not found');
				}
			} catch (\Exception $e) {
				App::sendOutputWithNewLine('&7Error running &d' . $className . '&7: &c' . $e->getMessage());
			}
		}
	}
}

App::sendOutputWithNewLine("|----");
App::sendOutputWithNewLine("");
App::sendOutputWithNewLine('&7Finished running all cron workers.');
App::sendOutputWithNewLine('&7Total execution time: &d' . round(microtime(true) - APP_STARTUP, 2) . 's');