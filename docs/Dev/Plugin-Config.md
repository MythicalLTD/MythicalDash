# Plugin Configuration (`conf.yml`)

Every plugin must have a `conf.yml` file in its root directory. This file describes your plugin’s metadata, dependencies, flags, and (optionally) custom settings.

## Example

```yaml
plugin:
  name: MyPlugin
  identifier: myplugin
  description: My awesome plugin
  flags:
    - hasEvents
    - hasCrons
  version: 1.0.0
  target: v3
  author:
    - YourName
  icon: https://example.com/icon.png
  dependencies:
    - php=8.1
    - php-ext=pdo
    - plugin=otherplugin
  settings:
    - key: myplugin_option
      label: "Enable MyPlugin Feature"
      type: boolean
      default: true
```

## Custom Admin Settings

You can add custom settings to the admin panel by adding a `settings` section. Each setting can have a key, label, type, and default value.

### Example: Adding a Setting

```yaml
settings:
  - key: myplugin_option
    label: "Enable MyPlugin Feature"
    type: boolean
    default: true
```

### Reading Settings in PHP

```php
$config = $app->getConfig();
$enabled = $config->getSetting('myplugin_option', false);
```

### Writing Settings in PHP

```php
$config->setSetting('myplugin_option', true);
```

## Flags

- `hasEvents`: Enables event hooks
- `hasCrons`: Enables scheduled tasks
- `hasInstallScript`, `hasRemovalScript`, `hasUpdateScript`: Enables lifecycle scripts
- ...and more (see Plugin-Backend.md)
