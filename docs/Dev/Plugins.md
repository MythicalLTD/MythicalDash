# MythicalDash Plugins

This guide will help you understand how MythicalDash plugins work and how to create your own plugins to extend the functionality of your dashboard.

## Plugin System Overview

MythicalDash uses an event-driven plugin architecture that allows developers to hook into various system events at runtime. This approach provides flexibility while maintaining core system stability and security.

## How Plugins Work

### Event-Based Architecture

Plugins in MythicalDash operate by subscribing to system events. When specific actions occur in the application, corresponding events are triggered, allowing plugins to:

- Execute custom code
- Modify data
- Integrate with external services
- Add new functionality

### Core Events

MythicalDash provides several core events you can hook into:

#### System Events
- `app::Load` - App load (After db connection!)
- `router::Ready` - Router is ready (Useful to add new api routes for backend)

#### Authentication Events
- `auth::LoginSuccess` - Triggered after successful user login
- `auth::LoginFailed` - Triggered when login attempt fails
- `auth::Logout` - Triggered when user logs out
- `auth::Register` - Triggered when registration is initiated
- `auth::RegisterSuccess` - Triggered after successful registration
- `auth::RegisterFailed` - Triggered when registration fails

#### Password Management Events
- `auth::ForgotPassword` - Triggered when password reset is requested
- `auth::ForgotPasswordSuccess` - Triggered after successful password reset request
- `auth::ForgotPasswordFailed` - Triggered when password reset request fails
- `auth::ResetPasswordSuccess` - Triggered after password is successfully reset
- `auth::ResetPasswordFailed` - Triggered when password reset fails

#### Two-Factor Authentication Events
- `auth::2FAVerifySuccess` - Triggered after successful 2FA verification
- `auth::2FAVerifyFailed` - Triggered when 2FA verification fails

(Note: Those are just examples events and your better choice is to read the documentation for each event itself)

Each event provides relevant context data that your plugin can use to implement custom functionality. For example, the login success event might include the user ID and timestamp of the login.

## Creating Your Own Plugin

To create your own MythicalDash plugin you will need to first go in the addons folder and create a new folder with your plugin identifier!

Make sure you create it only using English chars and you will keep using this name everywhere

Now let's create a config file where we going to define some stuff about our plugins

```yml
plugin:
  name: MythicalCore # The name of your plugin :)
  identifier: mythicalcore # The UUID of your plugin please keep it only [aZ] format
  description: The core plugin for mythicaldash :))
  flags: # Some flags you can use for plugins and for the compiler
    - hasEvents
  version: 1.0.0 # Version of the plugin
  target: v3 # Target mythicaldash core version
  author: 
    - NaysKutzu # The owner and author of the plugin
  icon: https://github.com/mythicalltd.png # The logo of the plugin
  dependencies: 
    - composer=mythicalsystems/core # If you need a specific composer package
    - php=8.1 # The minimum php version to run this
    - php-ext=pdo # The php extension this plugin needs
    #- plugin= # The plugin you need for this plugin to run as an example for your plugin you need mythicalcore :)
```

Now that we have this done we need to create our main plugin php file!

To do that make sure you respect the schema till now and create a new php file with your plugin name NOT identifier!

```php
<?php

namespace MythicalDash\Addons\<identifier>;

use MythicalDash\Plugins\Events\Events\AppEvent;
use MythicalDash\Plugins\MythicalDashPlugin;

class <name> implements MythicalDashPlugin
{

	/**
	 * @inheritDoc
	 */
	public static function processEvents(\MythicalDash\Plugins\PluginEvents $event): void
	{
		$event->on(AppEvent::onRouterReady(), function (\Router\Router $router) : void {
			$router->add('/api/<route you want to add>', function (): void {
				$appInstance = App::getInstance(true);
				$appInstance->OK('My new route',[]);
			});
		});

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
}
```