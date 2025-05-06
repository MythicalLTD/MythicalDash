# MythicalDash Pterodactyl API Client

A comprehensive PHP client for interacting with the Pterodactyl Panel API, including both the Application API and Client API and WINGS.


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
use MythicalDash\Pterodactyl\Wings\WingsClient;
use MythicalDash\Pterodactyl\Wings\Resources\FileManager;
use MythicalDash\Pterodactyl\Wings\Resources\BackupManager;
use MythicalDash\Pterodactyl\Wings\Resources\TransferManager;
use MythicalDash\Pterodactyl\Wings\Resources\InstallationManager;
use MythicalDash\Pterodactyl\Wings\Resources\WebSocketManager;

// Initialize the Wings client
$client = new WingsClient('https://your-wings-url', 'your-api-token');

// Get resource managers
$fileManager = new FileManager($client);
$backupManager = new BackupManager($client);
$transferManager = new TransferManager($client);
$installationManager = new InstallationManager($client);
$webSocketManager = new WebSocketManager($client);
```

## Usage Examples

### File Management

```php
// List files in a directory
$files = $fileManager->listFiles('server-id', '/');

// Get file contents
$contents = $fileManager->getFileContents('server-id', 'path/to/file');

// Write to a file
$fileManager->writeFileContents('server-id', 'path/to/file', 'content');
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
```

### Server Management

```php
// Get server details
$server = $client->getServer('server-id');

// Get server resource usage
$resources = $client->getServerResources('server-id');

// Send power signal
$client->sendPowerSignal('server-id', 'start'); // start, stop, restart, kill
```

## Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.