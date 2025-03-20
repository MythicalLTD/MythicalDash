# Pterodactyl API Client

A comprehensive PHP client for interacting with the Pterodactyl Panel API, including both the Application API and Client API and WINGS!!!.

## Features

### Application API

#### Server Management
- Create, update, and delete servers
- List servers with filtering and pagination
- Get detailed server information
- Manage server resources and configurations

#### User Management
- Create, update, and delete users
- List users with filtering and pagination
- Get detailed user information
- Manage user permissions and roles

#### Location Management
- Create, update, and delete locations
- List locations with filtering and pagination
- Get detailed location information

#### Node Management
- Create, update, and delete nodes
- List nodes with filtering and pagination
- Get detailed node information
- Manage node resources and configurations

#### Nest Management
- Create, update, and delete nests
- List nests with filtering and pagination
- Get detailed nest information

#### Egg Management
- Create, update, and delete eggs
- List eggs with filtering and pagination
- Get detailed egg information

### Client API

#### Server Management
- List user's servers
- Get server details
- Get server resources
- Send power signals (start, stop, restart, kill)
- Send console commands

#### File Management
- List files in a directory
- Get file contents
- Write file contents
- Rename files and directories
- Copy files and directories
- Delete files and directories
- Compress files
- Decompress archives

#### Backup Management
- List server backups
- Create new backups
- Get backup details
- Download backups
- Restore backups
- Delete backups

#### Server Transfer
- Start server transfer
- Get transfer status
- Cancel transfer

#### Server Installation
- Get installation status
- Start installation
- Cancel installation

#### WebSocket Support
- Connect to server WebSocket
- Send console commands
- Send power signals
- Subscribe to server stats
- Unsubscribe from server stats

#### Account Management
- Get account details
- Update email address
- Update password
- Enable/disable 2FA
- Manage API keys

#### SSH Key Management
- List SSH keys
- Get key details
- Create new SSH keys
- Delete SSH keys

#### Activity Logs
- Get server activity logs
- Get user activity logs
- Get server audit logs

### Wings API

```php
use MythicalDash\Services\Pterodactyl\Wings\WingsClient;
use MythicalDash\Services\Pterodactyl\Wings\Resources\FileManager;
use MythicalDash\Services\Pterodactyl\Wings\Resources\BackupManager;
use MythicalDash\Services\Pterodactyl\Wings\Resources\TransferManager;
use MythicalDash\Services\Pterodactyl\Wings\Resources\InstallationManager;
use MythicalDash\Services\Pterodactyl\Wings\Resources\WebSocketManager;

// Initialize the Wings client
$client = new WingsClient('https://your-wings-url', 'your-api-token');

// Get resource managers
$fileManager = new FileManager($client);
$backupManager = new BackupManager($client);
$transferManager = new TransferManager($client);
$installationManager = new InstallationManager($client);
$webSocketManager = new WebSocketManager($client);
```

#### File Management

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

#### Backup Management

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

#### Server Transfer

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

#### Server Installation

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

#### WebSocket Connection

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

#### Server Management

```php
// Get server details
$server = $client->getServer('server-id');

// Get server resource usage
$resources = $client->getServerResources('server-id');

// Send power signal
$client->sendPowerSignal('server-id', 'start'); // start, stop, restart, kill

// Send console command
$client->sendCommand('server-id', 'say Hello World');
```

## Usage Examples

### Application API

```php
use MythicalDash\Services\Pterodactyl\Application\PterodactylClient;
use MythicalDash\Services\Pterodactyl\Application\Resources\ServerResource;

$client = new PterodactylClient('https://panel.example.com', 'your-api-key');
$serverResource = new ServerResource($client);

// Create a new server
$server = $serverResource->create([
    'name' => 'My Server',
    'user' => 1,
    'egg' => 1,
    'docker_image' => 'quay.io/pterodactyl/games:minecraft',
    'startup' => 'java -Xms128M -Xmx512M -jar server.jar',
    'environment' => [
        'SERVER_JARFILE' => 'server.jar',
        'BUILD_NUMBER' => 'latest',
    ],
    'limits' => [
        'memory' => 512,
        'swap' => 0,
        'disk' => 5120,
        'io' => 500,
        'cpu' => 0,
    ],
    'feature_limits' => [
        'databases' => 0,
        'backups' => 0,
        'allocations' => 1,
    ],
    'allocation' => [
        'default' => 1,
    ],
]);
```

### Client API

```php
use MythicalDash\Services\Pterodactyl\Client\PterodactylClient;
use MythicalDash\Services\Pterodactyl\Client\Resources\ServerResource;
use MythicalDash\Services\Pterodactyl\Client\Resources\FileManager;
use MythicalDash\Services\Pterodactyl\Client\Resources\WebSocketManager;

$client = new PterodactylClient('https://panel.example.com', 'your-api-key');

// Get server details
$serverResource = new ServerResource($client);
$server = $serverResource->getDetails('server-id');

// Manage files
$fileManager = new FileManager($client);
$files = $fileManager->listFiles('server-id');

// WebSocket connection
$wsManager = new WebSocketManager($client);
$wsManager->connect('server-id', 
    function($msg) {
        echo "Received: " . $msg . "\n";
    },
    function($error) {
        echo "Error: " . $error . "\n";
    }
)->then(function($conn) use ($wsManager) {
    $wsManager->subscribeToStats($conn);
    $wsManager->sendCommand($conn, 'say Hello World!');
});

// Account management
$accountResource = new AccountResource($client);
$accountResource->updateEmail('new@example.com', 'current-password');
$accountResource->updatePassword('current-password', 'new-password');
$accountResource->enable2FA();
$accountResource->disable2FA('2fa-code', 'password');

// SSH key management
$sshResource = new SSHKeyResource($client);
$sshResource->createKey('My Key', 'ssh-rsa AAAA...');

// Activity logs
$activityResource = new ActivityResource($client);
$serverActivity = $activityResource->getServerActivity('server-id');
$userActivity = $activityResource->getUserActivity();
```

## Error Handling

The client includes comprehensive error handling for various scenarios:

- `AuthenticationException`: Invalid API credentials
- `PermissionException`: Insufficient permissions
- `ResourceNotFoundException`: Requested resource not found
- `ValidationException`: Invalid input data
- `RateLimitException`: API rate limit exceeded
- `ServerException`: Server-side errors

Example error handling:

```php
try {
    $server = $serverResource->getDetails('server-id');
} catch (AuthenticationException $e) {
    // Handle invalid credentials
} catch (PermissionException $e) {
    // Handle permission issues
} catch (ResourceNotFoundException $e) {
    // Handle missing resource
} catch (ValidationException $e) {
    // Handle validation errors
} catch (RateLimitException $e) {
    // Handle rate limiting
} catch (ServerException $e) {
    // Handle server errors
}
```

## Requirements

- PHP 8.0 or higher
- Guzzle HTTP Client
- Ratchet WebSocket Client (for WebSocket support)
- React Event Loop (for WebSocket support)

## License

This project is licensed under the MIT License - see the LICENSE file for details.