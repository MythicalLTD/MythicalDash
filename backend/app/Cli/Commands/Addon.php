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
use MythicalDash\Cli\CommandBuilder;

class Addon extends App implements CommandBuilder
{
    public static function execute(array $args): void
    {

        /**
         * Initialize the plugin manager.
         */
        require __DIR__ . '/../../../boot/kernel.php';
        global $pluginManager;
        define('APP_ADDONS_DIR', __DIR__ . '/../../../storage/addons');
        define('APP_DEBUG', false);
        $pluginManager->loadKernel();

        if (count($args) > 0) {
            switch ($args[1]) {
                case 'install':
                    // Install an addon.
                    break;
                case 'uninstall':
                    // Uninstall an addon.
					self::uninstallPlugin();
                    break;
                case 'list':
                    self::getInstance()->send('&5&lMythical&d&lDash &7- &d&lAddons');
                    self::getInstance()->send('');
                    $addons = $pluginManager->getLoadedMemoryPlugins();
                    foreach ($addons as $plugin) {
                        $addonConfig = \MythicalDash\Plugins\PluginConfig::getConfig($plugin);
                        $name = $addonConfig['plugin']['name'];
                        $version = $addonConfig['plugin']['version'];
                        $description = $addonConfig['plugin']['description'];
						$identifier = $addonConfig['plugin']['identifier'];
                        self::getInstance()->send("&7#&e{$identifier}&7 | &b{$name} &8> &d{$version} &8> &7{$description}");
                    }
                    self::getInstance()->send('');
                    break;
                case 'create':
                    // Create an addon.
                    self::createPlugin();
                    break;
                default:
                    self::getInstance()->send('&cInvalid subcommand!');
                    break;
            }
        } else {
            self::getInstance()->send('&cPlease provide a subcommand!');
        }
    }

	public static function uninstallPlugin() : void {
		self::getInstance()->send('&5&lMythical&d&lDash &7- &d&lAddons');
		self::getInstance()->send('');
		self::getInstance()->send('&7Please enter the plugin identifier:');
		$identifier = trim(fgets(STDIN));

		if (!file_exists(APP_ADDONS_DIR . '/' . $identifier)) {
			self::getInstance()->send('&cPlugin not found!');
			return;
		}

		self::getInstance()->send('&7Are you sure you want to uninstall this plugin? (y/n)');
		$confirm = trim(fgets(STDIN));

		if ($confirm !== 'y') {
			self::getInstance()->send('&cUninstall cancelled!');
			return;
		}

		// Load and call the plugin's uninstall method
		$pluginDir = APP_ADDONS_DIR . '/' . $identifier;
		$pluginFile = glob($pluginDir . '/*.php')[0] ?? null;
		
		if ($pluginFile) {
			require_once $pluginFile;
			$className = basename($pluginFile, '.php');
			$namespace = "MythicalDash\\Addons\\" . $identifier;
			$fullClassName = $namespace . "\\" . $className;
			
			if (class_exists($fullClassName) && method_exists($fullClassName, 'pluginUninstall')) {
				$fullClassName::pluginUninstall();
			}
		}

		// Remove the plugin directory
		if (!self::removeDirectory($pluginDir)) {
			self::getInstance()->send('&cFailed to uninstall plugin!');
			return;
		}

		self::getInstance()->send('&aPlugin uninstalled successfully!');
	}

	private static function removeDirectory($dir) {
		if (!file_exists($dir)) {
			return true;
		}

		if (!is_dir($dir)) {
			return unlink($dir);
		}

		foreach (scandir($dir) as $item) {
			if ($item == '.' || $item == '..') {
				continue;
			}

			if (!self::removeDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
				return false;
			}
		}

		return rmdir($dir);
	}

	public static function createPlugin() : void {
		self::getInstance()->send('&5&lMythical&d&lDash &7- &d&lAddons');
		self::getInstance()->send('');
		self::getInstance()->send('&7Please enter the plugin name:');
		$name = trim(fgets(STDIN));

		self::getInstance()->send('&7Please enter the plugin identifier (lowercase letters only):'); 
		$identifier = strtolower(trim(fgets(STDIN)));

		self::getInstance()->send('&7Please enter the plugin description:');
		$description = trim(fgets(STDIN));

		self::getInstance()->send('&7Please enter the plugin version:');
		$version = trim(fgets(STDIN));

		self::getInstance()->send('&7Please enter the plugin author:');
		$author = trim(fgets(STDIN));

		if (empty($name) || empty($identifier) || empty($description) || empty($version) || empty($author)) {
			self::getInstance()->send('&cAll fields are required!');
			return;
		}


		// Create conf.yml
		$config = [
			'plugin' => [
				'name' => $name,
				'identifier' => $identifier,
				'description' => $description,
				'flags' => ['hasEvents'],
				'version' => $version,
				'target' => 'v3',
				'author' => [$author],
				'icon' => 'https://github.com/mythicalltd.png',
				'requiredConfigs' => [],
				'dependencies' => [
					'php=8.1',
					'php-ext=pdo'
				]
			]
		];

		if (!preg_match('/^[a-zA-Z0-9\s]+$/', $name)) {
			self::getInstance()->send('&cPlugin name can only contain letters, numbers and spaces!');
			return;
		}

		if (!preg_match('/^[a-z0-9_]+$/', $identifier)) {
			self::getInstance()->send('&cPlugin identifier can only contain lowercase letters, numbers and underscores!');
			return;
		}

		if (strlen($identifier) < 3 || strlen($identifier) > 32) {
			self::getInstance()->send('&cPlugin identifier must be between 3 and 32 characters!');
			return;
		}

		if (strlen($name) < 3 || strlen($name) > 32) {
			self::getInstance()->send('&cPlugin name must be between 3 and 32 characters!');
			return;
		}

		if (strlen($description) < 10 || strlen($description) > 255) {
			self::getInstance()->send('&cPlugin description must be between 10 and 255 characters!');
			return;
		}

		if (!preg_match('/^[a-zA-Z0-9\s]+$/', $author)) {
			self::getInstance()->send('&cAuthor name can only contain letters, numbers and spaces!');
			return;
		}

		if (strlen($author) < 2 || strlen($author) > 32) {
			self::getInstance()->send('&cAuthor name must be between 2 and 32 characters!');
			return;
		}

		if (file_exists(APP_ADDONS_DIR . '/' . $identifier)) {
			self::getInstance()->send('&cA plugin with this identifier already exists!');
			return;
		}

		
		// Create plugin directory
		$pluginDir = APP_ADDONS_DIR . '/' . $identifier;
		if (!mkdir($pluginDir, 0755, true)) {
			self::getInstance()->send('&cFailed to create plugin directory!');
			return;
		}
		$yaml = new \Symfony\Component\Yaml\Yaml();
		file_put_contents($pluginDir . '/conf.yml', $yaml->dump($config, 4));
		mkdir($pluginDir . '/Events', 0755, true);
		$pluginFile = $pluginDir . '/' . $name . '.php';
		$pluginContent = "<?php

namespace MythicalDash\Addons\\" . $identifier . ";

use MythicalDash\Plugins\Events\Events\AppEvent;
use MythicalDash\Plugins\Events\Events\AuthEvent;
use MythicalDash\Plugins\MythicalDashPlugin;

class " . $name . " implements MythicalDashPlugin
{
	/**
	 * @inheritDoc
	 */
	public static function processEvents(\MythicalDash\Plugins\PluginEvents \$event): void
	{

	}

	/**
	 * @inheritDoc
	 */
	public static function pluginInstall(): void
	{
	}

	/**
	 * @inheritDoc
	 */
	public static function pluginUninstall(): void
	{
	}
}";
		file_put_contents($pluginFile, $pluginContent);
		

		self::getInstance()->send('');
		self::getInstance()->send("&7Plugin created successfully:");
		self::getInstance()->send("&7Name: &b$name");
		self::getInstance()->send("&7Identifier: &b$identifier");
		self::getInstance()->send("&7Description: &b$description");
		self::getInstance()->send("&7Version: &b$version");
		self::getInstance()->send("&7Author: &b$author");
		self::getInstance()->send('');
		self::getInstance()->send("&7Plugin directory: &b$pluginDir");
	}
    public static function getDescription(): string
    {
        return 'Manage your addons form the command line.';
    }

    public static function getSubCommands(): array
    {
        return [
            'install' => 'Install an addon.',
            'uninstall ' => 'Uninstall an addon.',
            'list' => 'List all installed addons.',	
            'create' => 'Create a new addon.',
        ];
    }
}
