#!/usr/bin/env php
<?php

use MythicalDash\Cli\App;
if (!empty($_SERVER['DOCUMENT_ROOT'])) {
    define('APP_PUBLIC', $_SERVER['DOCUMENT_ROOT'].'/backend');
} else {
    define('APP_PUBLIC', __DIR__.'/backend');
}

define("ENV_PATH", APP_PUBLIC."/storage/");
define('APP_START', microtime(true));
define('APP_DIR', APP_PUBLIC.'/');
define('APP_CRON_DIR', APP_PUBLIC.'/storage/cron/');
define('SYSTEM_KERNEL_NAME', php_uname('s'));
define('APP_VERSION', '3.5.3-aurora');
define("TELEMETRY", true);
define('IS_CLI', true);

require_once APP_DIR . "/boot/kernel.php";
try {
    $args = array_slice($argv, 1); // Exclude the command name and the first argument
    new App(isset($argv[1]) ? $argv[1] : '', $args);
} catch (Exception $e) {
    echo $e->getMessage();
}
