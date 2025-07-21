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

namespace MythicalDash\Cli\Commands;

use MythicalDash\Cli\App;
use MythicalDash\Chat\Database;
use MythicalDash\Cli\CommandBuilder;
use MythicalDash\Config\ConfigFactory;

class Getsetting extends App implements CommandBuilder
{
	public static function execute(array $args): void
	{
		$cliApp = App::getInstance();
		if (!file_exists(__DIR__ . '/../../../storage/.env')) {
			$cliApp->send('&7The application is not setup!');
			exit;
		}

		$cliApp->send('&aPlease enter the setting you want to read:');
		$setting = readline('> ');

		\MythicalDash\App::getInstance(true)->loadEnv();

		try {
			$db = new Database($_ENV['DATABASE_HOST'], $_ENV['DATABASE_DATABASE'], $_ENV['DATABASE_USER'], $_ENV['DATABASE_PASSWORD'], $_ENV['DATABASE_PORT']);
			$config = new ConfigFactory($db->getPdo());
			$value = $config->getSetting($setting, "DEFAULT");
			if ($value === "DEFAULT") {
				$cliApp->send("&cThe setting &e" . $setting . " &chas no value set!");
			} else {
				$cliApp->send("&7The value of &e" . $setting . " &7is &e" . $value);
			}

		} catch (\Exception $e) {
			$cliApp->send('&cAn error occurred while connecting to the database: ' . $e->getMessage());
			exit;
		}

	}

	public static function getDescription(): string
	{
		return 'Get a setting!';
	}

	public static function getSubCommands(): array
	{
		return [];
	}
}
