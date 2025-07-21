# Backend Features

## CLI Commands

Add new CLI commands by placing classes in `Commands/` that implement `CommandBuilder`.

## Event Hooks

React to backend events (user login, registration, etc.) in your main plugin class.

## API Endpoints

Add new API routes using the router in your main plugin class:

```php
$event->on(AppEvent::onRouterReady(), function ($router) {
    $router->add('/api/myplugin/hello', function () {
        $app = App::getInstance(true);
        $app->OK('Hello from MyPlugin!', []);
    });
});
```

## Crons (Scheduled Tasks)

Create scheduled jobs in `Cron/` and register them:

```php
// Cron/MyJob.php
namespace MythicalDash\Addons\myplugin\Cron;
use MythicalDash\Cron\TimeTask;
class MyJob implements TimeTask {
    public function run(): void {
        // Your scheduled code here
    }
}

// In your main plugin class
$event->registerCron(new \MythicalDash\Addons\myplugin\Cron\MyJob());
```

## Database Integration

- Create migration files in a `Migrations/` directory.
- Use the provided DB abstraction for queries.

### Example: Creating a Table

```php
$db = $app->getDatabase();
$db->query('CREATE TABLE IF NOT EXISTS myplugin_table (id INT PRIMARY KEY, value VARCHAR(255))');
```

### Running Migrations

You can provide SQL files or PHP migration scripts and run them in `pluginInstall()`.

## Lifecycle Scripts

Run code on install, update, or uninstall:

```php
public static function pluginInstall(): void {
    // Setup DB, create tables, etc.
}
public static function pluginUninstall(): void {
    // Cleanup DB, remove tables, etc.
}
```

## Dependency Management

Declare dependencies in `conf.yml`:

```yaml
dependencies:
  - php=8.1
  - php-ext=pdo
  - plugin=otherplugin
```

If dependencies are missing, the plugin will not load and an error will be shown.

## Plugin Flags

- `hasEvents`: Enables event hooks
- `hasCrons`: Enables scheduled tasks
- `hasInstallScript`, `hasRemovalScript`, `hasUpdateScript`: Enables lifecycle scripts
- `developerIgnoreInstallScript`, `developerEscalateInstallScript`, `userEscalateInstallScript`: Control install script behavior
- ...and more

## Security & Permissions

- Plugins run in a controlled environment.
- Always validate and sanitize user input.
- Use prepared statements for DB queries.
- Only request the permissions your plugin needs. 