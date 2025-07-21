# Plugin Cron Jobs

MythicalDash plugins can register cron jobs (scheduled tasks) that run automatically at specified intervals. This is useful for background tasks, cleanup operations, scheduled notifications, and more.

## Required Structure

Every cron job in MythicalDash MUST use the `Cron` class with:
1. A unique identifier
2. An interval specification

### Basic Structure

```php
<?php

namespace MythicalDash\Addons\yourplugin\Cron;

use MythicalDash\Cron\TimeTask;
use MythicalDash\Cron\Cron;
use MythicalDash\App;

class YourCronJob implements TimeTask
{
    public function run(): void
    {
        $app = App::getInstance(true);
        $logger = $app->getLogger();
        
        try {
            // Create cron with unique ID and interval
            $cron = new Cron("your-cron-id", "1M");  // Runs every 1 minute
            $cron->runIfDue(function() {
                $this->processTask();
            });
        } catch (\Exception $e) {
            $logger->error('Cron job failed: ' . $e->getMessage());
        }
    }

    private function processTask(): void
    {
        // Your actual task logic here
    }
}
```

## Interval Specifications

The interval parameter accepts these formats:
- `"1M"` - Every minute
- `"5M"` - Every 5 minutes
- `"1H"` - Every hour
- `"1D"` - Every day
- `"1W"` - Every week
- `"1MO"` - Every month

## Examples

### Hourly Cleanup
```php
public function run(): void
{
    $cron = new Cron("cleanup-temp-files", "1H");
    $cron->runIfDue(function() {
        $this->cleanupTempFiles();
    });
}
```

### Daily Report
```php
public function run(): void
{
    $cron = new Cron("daily-report", "1D");
    $cron->runIfDue(function() {
        $this->generateAndSendReport();
    });
}
```

### Weekly Maintenance
```php
public function run(): void
{
    $cron = new Cron("weekly-maintenance", "1W");
    $cron->runIfDue(function() {
        $this->performMaintenance();
    });
}
```

## Best Practices

### Unique Identifiers
- Use descriptive, plugin-specific IDs
- Format: `"pluginname-task-description"`
- Examples:
  - `"discorduserhook-send-webhooks"`
  - `"imageplugin-cleanup-temp"`
  - `"backupplugin-daily-backup"`

### Error Handling
```php
public function run(): void
{
    try {
        $cron = new Cron("your-task-id", "1H");
        $cron->runIfDue(function() {
            try {
                $this->processTask();
            } catch (\Exception $e) {
                // Log task-specific error
                $app->getLogger()->error('Task failed: ' . $e->getMessage());
            }
        });
    } catch (\Exception $e) {
        // Log cron system error
        $app->getLogger()->error('Cron system error: ' . $e->getMessage());
    }
}
```

### Performance Monitoring
```php
public function run(): void
{
    $cron = new Cron("performance-task", "1H");
    $cron->runIfDue(function() {
        $startTime = microtime(true);
        
        $this->processTask();
        
        $duration = microtime(true) - $startTime;
        $app->getLogger()->info('Task completed', [
            'duration' => $duration,
            'memory' => memory_get_usage(true)
        ]);
    });
}
```

## Common Use Cases

### Database Cleanup
```php
public function run(): void
{
    $cron = new Cron("db-cleanup", "1D");
    $cron->runIfDue(function() {
        $app = App::getInstance(true);
        $db = $app->getDatabase();
        
        $db->query('DELETE FROM your_table WHERE created_at < ?', [
            date('Y-m-d', strtotime('-30 days'))
        ]);
    });
}
```

### API Synchronization
```php
public function run(): void
{
    $cron = new Cron("api-sync", "5M");
    $cron->runIfDue(function() {
        $app = App::getInstance(true);
        $config = $app->getConfig();
        
        $lastSync = $config->getSetting('last_sync');
        $this->syncWithExternalAPI($lastSync);
        $config->setSetting('last_sync', time());
    });
}
```

## Troubleshooting

### Common Issues
1. **Cron not running at expected interval**
   - Verify interval format is correct
   - Check if cron ID is unique
   - Ensure MythicalDash cron system is running

2. **Multiple executions**
   - Use unique cron IDs
   - Check for duplicate registrations
   - Verify interval settings

3. **Task taking too long**
   - Consider using shorter intervals with batch processing
   - Implement timeouts
   - Monitor execution time

### Logging Best Practices
```php
public function run(): void
{
    $cron = new Cron("logged-task", "1H");
    $cron->runIfDue(function() {
        $app = App::getInstance(true);
        $logger = $app->getLogger();
        
        $logger->info('Starting scheduled task', [
            'cron_id' => 'logged-task',
            'time' => date('Y-m-d H:i:s')
        ]);
        
        // Your task logic
        
        $logger->info('Task completed');
    });
}
``` 