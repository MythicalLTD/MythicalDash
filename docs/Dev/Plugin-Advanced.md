# Advanced Plugin Features

This section covers advanced topics for MythicalDash plugin development.

---

## Mixins

**What are Mixins?**  
Mixins are reusable code modules that can be shared across plugins. They help you avoid code duplication and add modular features.

**Declaring a Mixin in conf.yml**
```yaml
plugin:
  # ... other settings ...
  mixins:
    - identifier: logging
      config:
        level: debug
```

**Using a Mixin in Your Plugin**
```php
use MythicalDash\Plugins\Mixins\LoggingMixin;

class MyPlugin implements MythicalDashPlugin
{
    public static function processEvents(PluginEvents $event): void
    {
        $logger = new LoggingMixin();
        $logger->initialize('myplugin', ['level' => 'debug']);
        $logger->log('Hello from mixin!');
    }
}
```

---

## Custom Admin Settings UI

**Adding Custom Settings to the Admin Panel**  
Add a `settings` section in your `conf.yml` (see Plugin-Config.md).

**Custom Forms/UI for Plugin Settings**  
If supported, you can create a Vue component or custom page for your plugin’s settings.

**Example: Adding a Settings Page**
```js
// JavaScript: Register a settings page (if supported by MythicalDash)
window.dispatchEvent(new CustomEvent('register-plugin-settings', {
  detail: {
    plugin: 'myplugin',
    component: MyPluginSettingsComponent
  }
}));
```

---

## Custom Routes/Pages

**Backend Routes**  
Add new backend routes in your main plugin class using the router.

**Frontend Pages/Widgets**  
If supported, register new Vue components or dashboard widgets.

**Example: Custom Admin Page**
```php
$event->on(AppEvent::onRouterReady(), function ($router) {
    $router->add('/admin/myplugin', function () {
        // Render your custom admin page
    });
});
```

---

## Plugin-to-Plugin Communication

**How Plugins Can Interact**
- Use events to broadcast/subscribe to actions.
- Use mixins for shared logic.
- Use service discovery if available.

**Example: Calling Another Plugin’s Method**
```php
$otherPlugin = PluginManager::getPlugin('otherplugin');
if ($otherPlugin) {
    $otherPlugin->doSomething();
}
```

---

## Testing & Debugging Plugins

**Best Practices**
- Use logging for debugging.
- Test in a development environment.
- Use MythicalDash’s debug panel/tools.

**Example: Logging**
```php
$app->getLogger()->debug('Debug message from my plugin');
```

---

## Plugin Distribution & Updates

**Packaging Plugins**
- Zip your plugin directory (including conf.yml, PHP, JS, etc.).
- Distribute via your own site or a plugin marketplace (if available).

**Updating Plugins**
- Follow MythicalDash’s update process (if supported).
- Use version numbers in conf.yml.

---

For more details, see the main plugin documentation and MythicalDash developer resources.
