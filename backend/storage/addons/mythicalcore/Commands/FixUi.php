<?php

namespace MythicalDash\Addons\mythicalcore\Commands;

use MythicalDash\Addons\mythicalcore\MythicalCore;
use MythicalDash\Cli\App;
use MythicalDash\Cli\CommandBuilder;

class FixUi extends MythicalCore implements CommandBuilder {
	
	/**
	 * @inheritDoc
	 */
	public static function execute(array $args): void {
		$app = App::getInstance();
		$app->send("&aFixing UI...");
		exit;
	}
	
	/**
	 * @inheritDoc
	 */
	public static function getDescription(): string {
		return "Fix common UI issues";
	}
	
	/**
	 * @inheritDoc
	 */
	public static function getSubCommands(): array {
		return [];
	}
}