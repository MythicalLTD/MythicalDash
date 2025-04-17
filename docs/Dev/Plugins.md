# MythicalDash Plugin System Documentation

This comprehensive guide will help you understand how MythicalDash's plugin system works and how to create your own plugins to extend the functionality of your dashboard.

## Table of Contents

1. [Plugin System Overview](#plugin-system-overview)
2. [How Plugins Work](#how-plugins-work)
3. [Available Events](#available-events)
4. [Creating a Plugin](#creating-a-plugin)
5. [Plugin Configuration](#plugin-configuration)
6. [Plugin Dependencies](#plugin-dependencies)
7. [Plugin Flags](#plugin-flags)
8. [Working with Events](#working-with-events)
9. [Advanced: Using Mixins](#advanced-using-mixins)
10. [Best Practices](#best-practices)

## Plugin System Overview

MythicalDash uses an event-driven plugin architecture that allows developers to hook into various system events at runtime. This approach provides flexibility while maintaining core system stability and security. The plugin system is designed to be:

- **Modular**: Each plugin operates independently
- **Event-driven**: Plugins react to system events
- **Extensible**: Developers can create new functionality without modifying core code
- **Secure**: Plugins operate within a controlled environment

## How Plugins Work

### Event-Based Architecture

Plugins in MythicalDash operate by subscribing to system events. When specific actions occur in the application, corresponding events are triggered, allowing plugins to:

- Execute custom code
- Modify data
- Integrate with external services
- Add new functionality
- Extend the dashboard interface

The plugin system uses a manager (`PluginManager`) that loads and initializes all plugins during application startup. Each plugin can register handlers for various events that it wants to respond to through the `PluginEvents` system.

### Plugin Lifecycle

1. **Discovery**: The system finds all installed plugins in the `addons` directory
2. **Validation**: Plugins are validated (configuration, dependencies, etc.)
3. **Loading**: Valid plugins are loaded into memory
4. **Initialization**: Plugins register their event handlers
5. **Execution**: Plugins respond to events during normal application operation

## Available Events

MythicalDash provides numerous events that plugins can hook into. Here are the main event categories:

### System Events
- `app::Load` - Triggered after the app loads (after database connection is established)
- `router::Ready` - Triggered when the router is ready (useful for adding new API routes)

### Authentication Events
- `auth::LoginSuccess` - Triggered after successful user login
- `auth::LoginFailed` - Triggered when login attempt fails
- `auth::Logout` - Triggered when user logs out
- `auth::Register` - Triggered when registration is initiated
- `auth::RegisterSuccess` - Triggered after successful registration
- `auth::RegisterFailed` - Triggered when registration fails

### Password Management Events
- `auth::ForgotPassword` - Triggered when password reset is requested
- `auth::ForgotPasswordSuccess` - Triggered after successful password reset request
- `auth::ForgotPasswordFailed` - Triggered when password reset request fails
- `auth::ResetPasswordSuccess` - Triggered after password is successfully reset
- `auth::ResetPasswordFailed` - Triggered when password reset fails

### Two-Factor Authentication Events
- `auth::2FAVerifySuccess` - Triggered after successful 2FA verification
- `auth::2FAVerifyFailed` - Triggered when 2FA verification fails

### User Events
- `user:update` - Triggered when a user is updated
- `user:delete` - Triggered when a user is deleted
- `user:new_support_pin` - Triggered when a new support PIN is generated
- `user:reset_api_key` - Triggered when an API key is reset

### Ticket Events
- `ticket::create` - Triggered when a ticket is created
- `ticket::update` - Triggered when a ticket is updated
- `ticket::reply` - Triggered when a reply is added to a ticket
- `ticket::view` - Triggered when a ticket is viewed
- `ticket::attachment::upload` - Triggered when an attachment is uploaded to a ticket

Each event provides relevant context data that your plugin can use to implement custom functionality. For example, the login success event might include the user ID and timestamp of the login.

## Creating a Plugin

### Directory Structure

To create a MythicalDash plugin, follow these steps:

1. Create a new directory in the `backend/storage/addons` folder with your plugin identifier (use only English alphabet characters in lowercase)
2. Create a configuration file (`plugin.yml`) in your plugin directory
3. Create your main plugin class file

### Basic Plugin Structure

Here's a minimal directory structure for a plugin:

```
backend/storage/addons/
└── myplugin/
    ├── plugin.yml
    ├── MyPlugin.php
    └── ... (additional files)
```

## Plugin Configuration

The `plugin.yml` file defines your plugin's metadata and requirements. Here's a complete example:

```yml
plugin:
  name: MyPlugin                 # The display name of your plugin
  identifier: myplugin           # The unique identifier (lowercase, only letters)
  description: My awesome plugin # A short description
  flags:                         # Special flags for the plugin
    - hasEvents                  # Indicates this plugin uses events
  version: 1.0.0                 # Your plugin version 
  target: v3                     # Target MythicalDash version
  author:                        # Author information (can be multiple)
    - YourName
  icon: https://example.com/icon.png # URL to your plugin icon
  dependencies:                  # List of dependencies
    - composer=vendor/package    # Required Composer package
    - php=8.1                    # Minimum PHP version
    - php-ext=pdo               # Required PHP extension
    - plugin=otherplugin        # Required plugin
  mixins:                       # Optional mixins configuration
    - identifier: logging       # Mixin identifier
      config:                   # Mixin-specific configuration
        level: debug
```

## Plugin Dependencies

MythicalDash plugins can specify dependencies that must be satisfied before the plugin can be loaded. Supported dependency types:

- **Composer Packages**: `composer=vendor/package`
- **PHP Version**: `php=8.1` (minimum PHP version)
- **PHP Extensions**: `php-ext=extension_name`
- **Other Plugins**: `plugin=plugin_identifier`

The system automatically validates dependencies during plugin loading.

## Plugin Flags

Flags provide additional information about your plugin's capabilities and requirements:

- `hasInstallScript` - Plugin has an installation script
- `hasRemovalScript` - Plugin has a removal script
- `hasUpdateScript` - Plugin has an update script
- `developerIgnoreInstallScript` - Ignore install script in development mode
- `developerEscalateInstallScript` - Escalate privileges during install in development mode
- `userEscalateInstallScript` - Escalate privileges during install for end-users
- `hasEvents` - Plugin uses the event system (most common)

## Working with Events

### Main Plugin Class

Your main plugin class should implement the `MythicalDashPlugin` interface:

```php
<?php

namespace MythicalDash\Addons\myplugin;

use MythicalDash\Plugins\MythicalDashPlugin;
use MythicalDash\Plugins\PluginEvents;
use MythicalDash\Plugins\Events\Events\AppEvent;
use MythicalDash\Plugins\Events\Events\AuthEvent;
use MythicalDash\App;

class MyPlugin implements MythicalDashPlugin
{
    /**
     * Process events for the plugin.
     */
    public static function processEvents(PluginEvents $event): void
    {
        // Register a handler for the router ready event
        $event->on(AppEvent::onRouterReady(), function (\Router\Router $router): void {
            // Add a custom API route
            $router->add('/api/myplugin/hello', function(): void {
                $app = App::getInstance(true);
                $app->OK('Hello from MyPlugin!', []);
            });
        });
        
        // Register a handler for successful login
        $event->on(AuthEvent::onAuthLoginSuccess(), function($userId, $timestamp): void {
            // Do something when a user logs in
            $app = App::getInstance(true);
            $app->getLogger()->info("User {$userId} logged in at {$timestamp}");
        });
    }
    
    /**
     * Code to run when the plugin is installed.
     */
    public static function pluginInstall(): void
    {
        // Plugin installation logic
        // For example: create database tables
    }
    
    /**
     * Code to run when the plugin is uninstalled.
     */
    public static function pluginUninstall(): void
    {
        // Plugin uninstallation logic
        // For example: remove database tables
    }
}
```

### Handling Events

To handle events, register callbacks in your plugin's `processEvents` method:

```php
$event->on(EventClass::eventMethod(), function(...$args) {
    // Your event handling code here
});
```

Each event type provides methods that return the appropriate event name string. For example, instead of manually typing `'auth::LoginSuccess'`, you should use `AuthEvent::onAuthLoginSuccess()`.

## Advanced: Using Mixins

Mixins provide reusable functionality that can be shared across plugins. They help you avoid duplicate code and create modular components.

### Using Existing Mixins

To use a mixin in your plugin:

1. Specify the mixin in your `plugin.yml` configuration:

```yml
plugin:
  # ... other settings ...
  mixins:
    - identifier: logging
      config:
        level: debug
```

2. The mixin functionality will be automatically available to your plugin

### Creating Custom Mixins

To create a custom mixin:

1. Create a class that implements the `MythicalDashMixin` interface:

```php
<?php

namespace MythicalDash\Addons\myplugin\Mixins;

use MythicalDash\Plugins\Mixins\MythicalDashMixin;

class LoggingMixin implements MythicalDashMixin
{
    private string $pluginIdentifier;
    private array $config;
    
    public function initialize(string $pluginIdentifier, array $config = []): void
    {
        $this->pluginIdentifier = $pluginIdentifier;
        $this->config = $config;
    }
    
    public static function getMixinIdentifier(): string
    {
        return 'logging';
    }
    
    public static function getMixinVersion(): string
    {
        return '1.0.0';
    }
    
    public function log(string $message, string $level = 'info'): void
    {
        // Implement logging logic
    }
}
```

2. Register your mixin with the system so other plugins can use it

## Best Practices

### Security Considerations

- **Validate User Input**: Always validate and sanitize user input
- **Use Prepared Statements**: For database queries to prevent SQL injection
- **Limit Privileges**: Request only the permissions your plugin needs
- **Secure Storage**: Use secure methods for storing sensitive data

### Performance Optimization

- **Efficient Event Handlers**: Keep event handlers light and efficient
- **Lazy Loading**: Load resources only when needed
- **Caching**: Cache results when appropriate to reduce load
- **Clean Uninstall**: Remove all data during uninstallation

### Code Quality

- **Follow PSR Standards**: Adhere to PHP coding standards
- **Documentation**: Document your code, especially public interfaces
- **Error Handling**: Implement proper error handling and logging
- **Testing**: Test your plugin thoroughly before distribution

### Compatibility

- **Version Checking**: Verify compatibility with the MythicalDash version
- **Graceful Degradation**: Handle missing dependencies gracefully
- **Forward Compatibility**: Design with future versions in mind

## Conclusion

The MythicalDash plugin system offers a powerful way to extend and customize your dashboard. By following this guide and best practices, you can create high-quality plugins that enhance functionality while maintaining security and performance.

For specific questions or advanced usage, refer to the API documentation or contact the MythicalDash development team.