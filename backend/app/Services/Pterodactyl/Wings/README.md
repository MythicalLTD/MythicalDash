# Pterodactyl Wings API Client

A PHP client for interacting with the Pterodactyl Wings API.

## Installation

```bash
composer require mythicaldash/wings-api-client
```

## Usage

### Basic Setup

```php
use MythicalDash\Services\Pterodactyl\Wings\WingsClient;
use MythicalDash\Services\Pterodactyl\Wings\Resources\FileManager;
use MythicalDash\Services\Pterodactyl\Wings\Resources\BackupManager;
use MythicalDash\Services\Pterodactyl\Wings\Resources\TransferManager;
use MythicalDash\Services\Pterodactyl\Wings\Resources\InstallationManager;
use MythicalDash\Services\Pterodactyl\Wings\Resources\WebSocketManager;

// Initialize the client
$client = new WingsClient('https://your-wings-url', 'your-api-token');

// Get resource managers
$fileManager = new FileManager($client);
$backupManager = new BackupManager($client);
$transferManager = new TransferManager($client);
$installationManager = new InstallationManager($client);
$webSocketManager = new WebSocketManager($client);
```

### File Management

```php
// List files in a directory
$files = $fileManager->listFiles('server-id', '/');

// Get file contents
$contents = $fileManager->getFileContents('server-id', 'path/to/file');

// Write to a file
$fileManager->writeFileContents('server-id', 'path/to/file', 'content');

// Rename/move a file
$fileManager->rename('server-id', 'old/path', 'new/path');

// Copy a file
$fileManager->copy('server-id', 'path/to/copy');

// Delete files
$fileManager->delete('server-id', ['file1.txt', 'file2.txt']);

// Compress files
$fileManager->compress('server-id', ['file1.txt', 'file2.txt'], '/');

// Decompress an archive
$fileManager->decompress('server-id', 'archive.zip');
```

### Backup Management

```php
// List backups
$backups = $backupManager->listBackups('server-id');

// Create a backup
$backup = $backupManager->createBackup('server-id', [
    'name' => 'My Backup',
    'ignored' => ['node_modules', 'vendor']
]);

// Get backup details
$details = $backupManager->getBackup('server-id', 'backup-id');

// Download a backup
$download = $backupManager->downloadBackup('server-id', 'backup-id');

// Restore a backup
$backupManager->restoreBackup('server-id', 'backup-id');

// Delete a backup
$backupManager->deleteBackup('server-id', 'backup-id');
```

### Server Transfer

```php
// Start a transfer
$transfer = $transferManager->startTransfer('server-id', [
    'node_id' => 'target-node-id'
]);

// Get transfer status
$status = $transferManager->getTransferStatus('server-id');

// Cancel a transfer
$transferManager->cancelTransfer('server-id');
```

### Server Installation

```php
// Get installation status
$status = $installationManager->getStatus('server-id');

// Start installation
$installation = $installationManager->startInstallation('server-id', [
    'container_image' => 'ghcr.io/pterodactyl/games:steamcmd',
    'startup' => './srcds_run -game csgo -console -usercon +game_type 0 +game_mode 0 +mapgroup mg_basic +map de_dust2 +sv_setsteamaccount ${STEAM_ACC} -tickrate 128'
]);

// Cancel installation
$installationManager->cancelInstallation('server-id');
```

### WebSocket Connection

```php
// Connect to WebSocket
$ws = $webSocketManager->connect('server-id', 
    function($msg) {
        echo "Received: " . $msg . "\n";
    },
    function($error) {
        echo "Error: " . $error . "\n";
    }
);

// Send a command
$webSocketManager->sendWebSocketCommand($ws, 'say Hello World');

// Send a power signal
$webSocketManager->sendWebSocketPowerSignal($ws, 'restart');

// Subscribe to stats
$webSocketManager->subscribeToStats($ws);

// Unsubscribe from stats
$webSocketManager->unsubscribeFromStats($ws);
```

## Error Handling

The client throws specific exceptions for different error scenarios:

- `AuthenticationException`: Invalid API credentials
- `PermissionException`: Insufficient permissions
- `ResourceNotFoundException`: Resource not found
- `ValidationException`: Invalid request data
- `RateLimitException`: Rate limit exceeded
- `ServerException`: Server state conflicts

```php
try {
    $files = $fileManager->listFiles('server-id');
} catch (AuthenticationException $e) {
    // Handle authentication error
} catch (PermissionException $e) {
    // Handle permission error
} catch (ResourceNotFoundException $e) {
    // Handle not found error
} catch (ValidationException $e) {
    // Handle validation error
} catch (RateLimitException $e) {
    // Handle rate limit error
} catch (ServerException $e) {
    // Handle server error
}
```

## Requirements

- PHP 8.0 or higher
- Guzzle HTTP Client
- Ratchet WebSocket Client
- React Event Loop

## License

This project is licensed under the MIT License.