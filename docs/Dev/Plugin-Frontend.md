# Frontend Features

## JavaScript & CSS

- Place JS in `JavaScript/index.js` and CSS in `CSS/index.css`.
- Both will be loaded automatically by the dashboard.

## Custom Assets

- Place images, icons, or other static files in your plugin directory.
- Reference them in your JS/CSS or expose via custom API endpoints.

## Vue Component Injection

If supported, you can inject Vue components or extend the dashboard UI. (Check the latest MythicalDash docs for current support.)

## Communication with Main App

- Use global events or window variables to communicate between your plugin JS and the main app.
- Example: `window.dispatchEvent(new CustomEvent('myplugin-event', { detail: { ... } }))`

## Advanced JS/CSS Loading Tips

- Use unique class names and IDs to avoid conflicts.
- Use feature detection and guards to avoid breaking the main app.
- Clean up any global state or listeners on plugin unload (if hot-reloading is supported).
