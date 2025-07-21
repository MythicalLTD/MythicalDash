# MythicalDash Plugin System: Overview

Welcome to the MythicalDash plugin system documentation! This is your master entry point for all plugin development topics.

## Table of Contents

- [What is a Plugin?](./Plugin-Overview.md#what-is-a-plugin)
- [Directory Structure](./Plugin-Structure.md)
- [Configuration (`conf.yml`)](./Plugin-Config.md)
- [Backend Features](./Plugin-Backend.md)
  - CLI Commands
  - Event Hooks
  - API Endpoints
  - Crons (Scheduled Tasks)
  - Database Integration
  - Lifecycle Scripts
  - Dependency Management
  - Plugin Flags
  - Security & Permissions
- [Frontend Features](./Plugin-Frontend.md)
  - JavaScript & CSS
  - Custom Assets
  - Vue Component Injection
  - Communication with Main App
- [Best Practices](./Plugin-BestPractices.md)
- [Example: discorduserhook](./Plugin-Example-discorduserhook.md)

---

## What is a Plugin?

A plugin is a modular extension that can add new features, integrations, and customizations to MythicalDash. Plugins can:

- Add CLI commands
- Hook into backend events
- Add API endpoints
- Inject frontend JS/CSS
- Register crons (scheduled tasks)
- Manage their own database tables
- Provide mixins for other plugins
- Add custom admin settings
- Provide custom assets (images, icons, etc.)
- Run code on install, update, or uninstall

See the sections above for details and examples!