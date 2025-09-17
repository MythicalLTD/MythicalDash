# MythicalDash Permission Nodes

This document provides a comprehensive overview of all permission nodes used in MythicalDash.

## Overview

- **Total Permissions:** 88
- **Categories:** 30
- **With Descriptions:** 88

## Format

Each permission node follows this format:
```
CONSTANT_NAME=permission.node.value | Category | Description
```

## Usage

### PHP
```php
use MythicalDash\Permissions;

// Check if user has permission
if (auth()->user()->hasPermission(Permissions::ADMIN_DASHBOARD_VIEW)) {
    // User can view dashboard
}
```

### TypeScript/JavaScript
```typescript
import Permissions from '@/mythicaldash/Permissions';

// Check if user has permission
if (auth.user.hasPermission(Permissions.ADMIN_DASHBOARD_VIEW)) {
    // User can view dashboard
}
```

## Admin Root

| Permission | Node | Description |
|------------|------|-------------|
| `ADMIN_ROOT` | `admin.root` | Full access to everything |

## Admin Dashboard View

| Permission | Node | Description |
|------------|------|-------------|
| `ADMIN_DASHBOARD_VIEW` | `admin.dashboard.view` | Access to view the admin dashboard |

## Admin Dashboard Components

| Permission | Node | Description |
|------------|------|-------------|
| `ADMIN_DASHBOARD_COMPONENTS_SYSTEM_OVERVIEW` | `admin.dashboard.components.system.overview` | View system overview component |
| `ADMIN_DASHBOARD_COMPONENTS_SYSTEM_UPDATES` | `admin.dashboard.components.system.updates` | View system updates component |
| `ADMIN_DASHBOARD_COMPONENTS_SYSTEM_LOGS` | `admin.dashboard.components.system.logs` | View system logs component |
| `ADMIN_DASHBOARD_COMPONENTS_ANALYTICS_VIEW` | `admin.dashboard.components.ataglanceanalytics.view` | View analytics at-a-glance component |
| `ADMIN_DASHBOARD_COMPONENTS_SUPPORT_VIEW` | `admin.dashboard.components.support.view` | View support component |
| `ADMIN_DASHBOARD_COMPONENTS_GITHUB_VIEW` | `admin.dashboard.components.githubrepo.view` | View GitHub repository component |
| `ADMIN_DASHBOARD_COMPONENTS_QUICK_ACTIONS` | `admin.dashboard.components.quickactions.view` | View quick actions component |
| `ADMIN_DASHBOARD_COMPONENTS_ACTIVITY_VIEW` | `admin.dashboard.components.activity.view` | View activity feed component |

## Admin Health

| Permission | Node | Description |
|------------|------|-------------|
| `ADMIN_HEALTH_VIEW` | `admin.health.view` | See the health of the dashboard! |

## Admin Users

| Permission | Node | Description |
|------------|------|-------------|
| `ADMIN_USERS_EDIT` | `admin.users.edit` | Edit existing users |
| `ADMIN_USERS_DELETE` | `admin.users.delete` | Delete users |
| `ADMIN_USERS_LIST` | `admin.users.list` | List all users |

## Admin J4R Servers

| Permission | Node | Description |
|------------|------|-------------|
| `ADMIN_J4R_SERVERS_CREATE` | `admin.j4r.servers.create` | Create new J4R (Join for Rewards) servers |
| `ADMIN_J4R_SERVERS_EDIT` | `admin.j4r.servers.edit` | Edit existing J4R (Join for Rewards) servers |
| `ADMIN_J4R_SERVERS_DELETE` | `admin.j4r.servers.delete` | Delete J4R (Join for Rewards) servers |
| `ADMIN_J4R_SERVERS_LIST` | `admin.j4r.servers.list` | List all J4R (Join for Rewards) servers |

## Admin Locations

| Permission | Node | Description |
|------------|------|-------------|
| `ADMIN_LOCATIONS_CREATE` | `admin.locations.create` | Create new locations |
| `ADMIN_LOCATIONS_EDIT` | `admin.locations.edit` | Edit existing locations |
| `ADMIN_LOCATIONS_DELETE` | `admin.locations.delete` | Delete locations |
| `ADMIN_LOCATIONS_LIST` | `admin.locations.list` | List all locations |

## Admin Nests

| Permission | Node | Description |
|------------|------|-------------|
| `ADMIN_NESTS_CREATE` | `admin.nests.create` | Create new nests |
| `ADMIN_NESTS_EDIT` | `admin.nests.edit` | Edit existing nests |
| `ADMIN_NESTS_DELETE` | `admin.nests.delete` | Delete nests |
| `ADMIN_NESTS_LIST` | `admin.nests.list` | List all nests |

## Admin Eggs

| Permission | Node | Description |
|------------|------|-------------|
| `ADMIN_EGG_CREATE` | `admin.egg.create` | Create new eggs |
| `ADMIN_EGG_EDIT` | `admin.egg.edit` | Edit existing eggs |
| `ADMIN_EGG_DELETE` | `admin.egg.delete` | Delete eggs |
| `ADMIN_EGG_LIST` | `admin.egg.list` | List all eggs |

## Admin Departments

| Permission | Node | Description |
|------------|------|-------------|
| `ADMIN_DEPARTMENTS_CREATE` | `admin.departments.create` | Create new departments |
| `ADMIN_DEPARTMENTS_EDIT` | `admin.departments.edit` | Edit existing departments |
| `ADMIN_DEPARTMENTS_DELETE` | `admin.departments.delete` | Delete departments |
| `ADMIN_DEPARTMENTS_LIST` | `admin.departments.list` | List all departments |

## Admin Roles

| Permission | Node | Description |
|------------|------|-------------|
| `ADMIN_ROLES_CREATE` | `admin.roles.create` | Create new roles |
| `ADMIN_ROLES_EDIT` | `admin.roles.edit` | Edit existing roles |
| `ADMIN_ROLES_DELETE` | `admin.roles.delete` | Delete roles |
| `ADMIN_ROLES_LIST` | `admin.roles.list` | List all roles |

## Admin Permissions

| Permission | Node | Description |
|------------|------|-------------|
| `ADMIN_PERMISSIONS_CREATE` | `admin.permissions.create` | Create new permissions |
| `ADMIN_PERMISSIONS_EDIT` | `admin.permissions.edit` | Edit existing permissions |
| `ADMIN_PERMISSIONS_DELETE` | `admin.permissions.delete` | Delete permissions |
| `ADMIN_PERMISSIONS_LIST` | `admin.permissions.list` | List all permissions |

## Admin Servers

| Permission | Node | Description |
|------------|------|-------------|
| `ADMIN_SERVERS_EDIT` | `admin.servers.edit` | Edit existing servers |
| `ADMIN_SERVERS_DELETE` | `admin.servers.delete` | Delete servers |
| `ADMIN_SERVERS_LIST` | `admin.servers.list` | List all servers |

## Admin Server Queue

| Permission | Node | Description |
|------------|------|-------------|
| `ADMIN_SERVER_QUEUE_CREATE` | `admin.server.queue.create` | Create new server queue entries |
| `ADMIN_SERVER_QUEUE_DELETE` | `admin.server.queue.delete` | Delete server queue entries |
| `ADMIN_SERVER_QUEUE_LIST` | `admin.server.queue.list` | List all server queue entries |
| `ADMIN_SERVER_QUEUE_VIEW` | `admin.server.queue.view` | View server queue details |

## Admin Server Queue Logs

| Permission | Node | Description |
|------------|------|-------------|
| `ADMIN_SERVER_QUEUE_LOGS_VIEW` | `admin.server.queue.logs.view` | View server queue logs |

## Admin Tickets

| Permission | Node | Description |
|------------|------|-------------|
| `ADMIN_TICKETS_LIST` | `admin.tickets.list` | List all tickets |
| `ADMIN_TICKETS_VIEW` | `admin.tickets.view` | View ticket details |

## Admin Announcements

| Permission | Node | Description |
|------------|------|-------------|
| `ADMIN_ANNOUNCEMENTS_CREATE` | `admin.announcements.create` | Create new announcements |
| `ADMIN_ANNOUNCEMENTS_EDIT` | `admin.announcements.edit` | Edit existing announcements |
| `ADMIN_ANNOUNCEMENTS_DELETE` | `admin.announcements.delete` | Delete announcements |
| `ADMIN_ANNOUNCEMENTS_LIST` | `admin.announcements.list` | List all announcements |

## Admin Settings

| Permission | Node | Description |
|------------|------|-------------|
| `ADMIN_SETTINGS_VIEW` | `admin.settings.view` | View system settings |
| `ADMIN_SETTINGS_EDIT` | `admin.settings.edit` | Edit system settings |

## Admin Plugins

| Permission | Node | Description |
|------------|------|-------------|
| `ADMIN_PLUGINS_EDIT` | `admin.plugins.edit` | Edit existing plugins |
| `ADMIN_PLUGINS_LIST` | `admin.plugins.list` | List all plugins |

## Admin Backups

| Permission | Node | Description |
|------------|------|-------------|
| `ADMIN_BACKUPS_CREATE` | `admin.backups.create` | Create new backups |
| `ADMIN_BACKUPS_DELETE` | `admin.backups.delete` | Delete backups |
| `ADMIN_BACKUPS_LIST` | `admin.backups.list` | List all backups |

## Admin Images

| Permission | Node | Description |
|------------|------|-------------|
| `ADMIN_IMAGES_CREATE` | `admin.images.create` | Create new images |
| `ADMIN_IMAGES_EDIT` | `admin.images.edit` | Edit existing images |
| `ADMIN_IMAGES_DELETE` | `admin.images.delete` | Delete images |
| `ADMIN_IMAGES_LIST` | `admin.images.list` | List all images |

## Admin Mail Templates

| Permission | Node | Description |
|------------|------|-------------|
| `ADMIN_MAIL_TEMPLATES_CREATE` | `admin.mail.templates.create` | Create new mail templates |
| `ADMIN_MAIL_TEMPLATES_EDIT` | `admin.mail.templates.edit` | Edit existing mail templates |
| `ADMIN_MAIL_TEMPLATES_DELETE` | `admin.mail.templates.delete` | Delete mail templates |
| `ADMIN_MAIL_TEMPLATES_LIST` | `admin.mail.templates.list` | List all mail templates |

## Admin Mail

| Permission | Node | Description |
|------------|------|-------------|
| `ADMIN_MAIL_SEND_MASS_MAIL` | `admin.mail.send.mass.mails` | Send mass mails to all users! |

## Admin Redirect Links

| Permission | Node | Description |
|------------|------|-------------|
| `ADMIN_REDIRECT_LINKS_CREATE` | `admin.redirect.links.create` | Create new redirect links |
| `ADMIN_REDIRECT_LINKS_EDIT` | `admin.redirect.links.edit` | Edit existing redirect links |
| `ADMIN_REDIRECT_LINKS_DELETE` | `admin.redirect.links.delete` | Delete redirect links |
| `ADMIN_REDIRECT_LINKS_LIST` | `admin.redirect.links.list` | List all redirect links |

## Admin Redeem Codes

| Permission | Node | Description |
|------------|------|-------------|
| `ADMIN_REDEEM_CODES_CREATE` | `admin.redeem.codes.create` | Create new redeem codes |
| `ADMIN_REDEEM_CODES_EDIT` | `admin.redeem.codes.edit` | Edit existing redeem codes |
| `ADMIN_REDEEM_CODES_DELETE` | `admin.redeem.codes.delete` | Delete redeem codes |
| `ADMIN_REDEEM_CODES_LIST` | `admin.redeem.codes.list` | List all redeem codes |

## VIP

| Permission | Node | Description |
|------------|------|-------------|
| `USER_PERMISSION_VIP` | `user.permission.vip` | The VIP permission that allows you to create servers on vip eggs or nodes |

## Admin Image Reports

| Permission | Node | Description |
|------------|------|-------------|
| `ADMIN_IMAGE_REPORTS_VIEW` | `admin.image.reports.view` | View image reports |
| `ADMIN_IMAGE_REPORTS_LIST` | `admin.image.reports.list` | List image reports |
| `ADMIN_IMAGE_REPORTS_EDIT` | `admin.image.reports.edit` | Edit image reports |
| `ADMIN_IMAGE_REPORTS_DELETE` | `admin.image.reports.delete` | Delete image reports |

## Adblocker

| Permission | Node | Description |
|------------|------|-------------|
| `USER_PERMISSION_BYPASS_ADBLOCKER` | `user.permission.adblocker` | The permission that lets you bypass the antiadblocker! |

## AntiAlting

| Permission | Node | Description |
|------------|------|-------------|
| `USER_PERMISSION_BYPASS_ALTING` | `user.permission.alt` | This permissions bypasses the system that does alt checks! |

## AntiVPN

| Permission | Node | Description |
|------------|------|-------------|
| `USER_PERMISSION_BYPASS_VPN` | `user.permission.vpn` | This permission does a full bypass of the antivpn system! |

## Adding New Permissions

To add a new permission node:

1. Edit `permission_nodes.txt` in the root directory
2. Add your permission in the format: `CONSTANT_NAME=permission.node.value | Category | Description`
3. Run `php mythicaldash permissionExport` to regenerate all files
4. Rebuild the frontend if necessary

## File Locations

- **Source:** `permission_nodes.txt` (root directory)
- **PHP:** `backend/app/Permissions.php`
- **TypeScript:** `frontend/src/mythicaldash/Permissions.ts`
- **Documentation:** `docs/PERMISSIONS.md` (this file)

## Auto-Generation

⚠️ **Important:** All generated files are automatically created from `permission_nodes.txt`. Manual modifications to the generated files will be overwritten when the export command is run.

---

*This documentation was auto-generated on 2025-09-17 22:03:42*
