# Plugin Directory Structure

A typical plugin folder looks like this:

```
backend/storage/addons/
└── yourplugin/
    ├── conf.yml                # Plugin configuration
    ├── YourPlugin.php          # Main plugin class
    ├── Commands/               # CLI commands (optional)
    ├── Events/                 # Event handlers (optional)
    ├── Cron/                   # Scheduled tasks (optional)
    ├── JavaScript/             # Frontend JS (optional)
    │   └── index.js
    ├── CSS/                    # Frontend CSS (optional)
    ├── Migrations/             # DB migrations (optional)
    ├── Assets/                 # Images, icons, etc. (optional)
    └── ... (other folders)
```

## Required Files

### conf.yml
The main configuration file that defines your plugin's metadata, dependencies, and settings.

### YourPlugin.php
The main plugin class that implements `MythicalDashPlugin` and handles events, initialization, etc.

## Optional Directories

### Commands/
Place your CLI command classes here. Each command should:
- Implement `CommandBuilder`
- Be in the `MythicalDash\Addons\yourplugin\Commands` namespace
- Provide `execute`, `getDescription`, and `getSubCommands` methods

### Events/
Event handler classes that respond to system events like:
- User authentication
- Server management
- API requests
- System changes

### Cron/
Scheduled task classes that:
- Implement `TimeTask`
- Run at specified intervals
- Handle background jobs

### JavaScript/
- Place `index.js` here for frontend functionality
- Automatically loaded into the dashboard
- Can interact with the main app

### CSS/
- Custom styles for your plugin
- Automatically loaded into the dashboard
- Use unique class names to avoid conflicts

### Migrations/
- SQL files for database setup
- PHP migration scripts
- Run during plugin installation

### Assets/
- Images, icons, and other static files
- Reference these in your JS/CSS
- Serve via custom API endpoints

## Naming Conventions
- Plugin identifier: lowercase (e.g., 'myplugin')
- Class names: StudlyCase (e.g., 'MyPlugin')
- Files: Match class names (e.g., 'MyPlugin.php')
- Namespaces: `MythicalDash\Addons\yourplugin\*`

## Best Practices
- Keep files organized in appropriate directories
- Use clear, descriptive names
- Follow PSR standards
- Document your code
- Clean up all files on uninstall 