/**
 * Permission Nodes Constants
 * Auto-generated from permission_nodes.txt
 */

/**
 * ⚠️  WARNING: Do not modify this file manually!
 * This file is auto-generated from permission_nodes.txt
 * Use 'php mythicaldash permissionExport' to regenerate this file
 * Manual modifications will be overwritten on next generation.
 */

class Permissions {
    // Admin Root Permissions
    /** Full access to everything */
    public static ADMIN_ROOT = 'admin.root';

    // Admin Dashboard View Permissions
    /** Access to view the admin dashboard */
    public static ADMIN_DASHBOARD_VIEW = 'admin.dashboard.view';

    // Admin Dashboard Components Permissions
    /** View system overview component */
    public static ADMIN_DASHBOARD_COMPONENTS_SYSTEM_OVERVIEW = 'admin.dashboard.components.system.overview';
    /** View system updates component */
    public static ADMIN_DASHBOARD_COMPONENTS_SYSTEM_UPDATES = 'admin.dashboard.components.system.updates';
    /** View system logs component */
    public static ADMIN_DASHBOARD_COMPONENTS_SYSTEM_LOGS = 'admin.dashboard.components.system.logs';
    /** View analytics at-a-glance component */
    public static ADMIN_DASHBOARD_COMPONENTS_ANALYTICS_VIEW = 'admin.dashboard.components.ataglanceanalytics.view';
    /** View support component */
    public static ADMIN_DASHBOARD_COMPONENTS_SUPPORT_VIEW = 'admin.dashboard.components.support.view';
    /** View GitHub repository component */
    public static ADMIN_DASHBOARD_COMPONENTS_GITHUB_VIEW = 'admin.dashboard.components.githubrepo.view';
    /** View quick actions component */
    public static ADMIN_DASHBOARD_COMPONENTS_QUICK_ACTIONS = 'admin.dashboard.components.quickactions.view';
    /** View activity feed component */
    public static ADMIN_DASHBOARD_COMPONENTS_ACTIVITY_VIEW = 'admin.dashboard.components.activity.view';

    // Admin Health Permissions
    /** See the health of the dashboard! */
    public static ADMIN_HEALTH_VIEW = 'admin.health.view';

    // Admin Users Permissions
    /** Edit existing users */
    public static ADMIN_USERS_EDIT = 'admin.users.edit';
    /** Delete users */
    public static ADMIN_USERS_DELETE = 'admin.users.delete';
    /** List all users */
    public static ADMIN_USERS_LIST = 'admin.users.list';

    // Admin J4R Servers Permissions
    /** Create new J4R (Join for Rewards) servers */
    public static ADMIN_J4R_SERVERS_CREATE = 'admin.j4r.servers.create';
    /** Edit existing J4R (Join for Rewards) servers */
    public static ADMIN_J4R_SERVERS_EDIT = 'admin.j4r.servers.edit';
    /** Delete J4R (Join for Rewards) servers */
    public static ADMIN_J4R_SERVERS_DELETE = 'admin.j4r.servers.delete';
    /** List all J4R (Join for Rewards) servers */
    public static ADMIN_J4R_SERVERS_LIST = 'admin.j4r.servers.list';

    // Admin Locations Permissions
    /** Create new locations */
    public static ADMIN_LOCATIONS_CREATE = 'admin.locations.create';
    /** Edit existing locations */
    public static ADMIN_LOCATIONS_EDIT = 'admin.locations.edit';
    /** Delete locations */
    public static ADMIN_LOCATIONS_DELETE = 'admin.locations.delete';
    /** List all locations */
    public static ADMIN_LOCATIONS_LIST = 'admin.locations.list';

    // Admin Nests Permissions
    /** Create new nests */
    public static ADMIN_NESTS_CREATE = 'admin.nests.create';
    /** Edit existing nests */
    public static ADMIN_NESTS_EDIT = 'admin.nests.edit';
    /** Delete nests */
    public static ADMIN_NESTS_DELETE = 'admin.nests.delete';
    /** List all nests */
    public static ADMIN_NESTS_LIST = 'admin.nests.list';

    // Admin Eggs Permissions
    /** Create new eggs */
    public static ADMIN_EGG_CREATE = 'admin.egg.create';
    /** Edit existing eggs */
    public static ADMIN_EGG_EDIT = 'admin.egg.edit';
    /** Delete eggs */
    public static ADMIN_EGG_DELETE = 'admin.egg.delete';
    /** List all eggs */
    public static ADMIN_EGG_LIST = 'admin.egg.list';

    // Admin Departments Permissions
    /** Create new departments */
    public static ADMIN_DEPARTMENTS_CREATE = 'admin.departments.create';
    /** Edit existing departments */
    public static ADMIN_DEPARTMENTS_EDIT = 'admin.departments.edit';
    /** Delete departments */
    public static ADMIN_DEPARTMENTS_DELETE = 'admin.departments.delete';
    /** List all departments */
    public static ADMIN_DEPARTMENTS_LIST = 'admin.departments.list';

    // Admin Roles Permissions
    /** Create new roles */
    public static ADMIN_ROLES_CREATE = 'admin.roles.create';
    /** Edit existing roles */
    public static ADMIN_ROLES_EDIT = 'admin.roles.edit';
    /** Delete roles */
    public static ADMIN_ROLES_DELETE = 'admin.roles.delete';
    /** List all roles */
    public static ADMIN_ROLES_LIST = 'admin.roles.list';

    // Admin Permissions Permissions
    /** Create new permissions */
    public static ADMIN_PERMISSIONS_CREATE = 'admin.permissions.create';
    /** Edit existing permissions */
    public static ADMIN_PERMISSIONS_EDIT = 'admin.permissions.edit';
    /** Delete permissions */
    public static ADMIN_PERMISSIONS_DELETE = 'admin.permissions.delete';
    /** List all permissions */
    public static ADMIN_PERMISSIONS_LIST = 'admin.permissions.list';

    // Admin Servers Permissions
    /** Edit existing servers */
    public static ADMIN_SERVERS_EDIT = 'admin.servers.edit';
    /** Delete servers */
    public static ADMIN_SERVERS_DELETE = 'admin.servers.delete';
    /** List all servers */
    public static ADMIN_SERVERS_LIST = 'admin.servers.list';

    // Admin Server Queue Permissions
    /** Create new server queue entries */
    public static ADMIN_SERVER_QUEUE_CREATE = 'admin.server.queue.create';
    /** Delete server queue entries */
    public static ADMIN_SERVER_QUEUE_DELETE = 'admin.server.queue.delete';
    /** List all server queue entries */
    public static ADMIN_SERVER_QUEUE_LIST = 'admin.server.queue.list';
    /** View server queue details */
    public static ADMIN_SERVER_QUEUE_VIEW = 'admin.server.queue.view';

    // Admin Server Queue Logs Permissions
    /** View server queue logs */
    public static ADMIN_SERVER_QUEUE_LOGS_VIEW = 'admin.server.queue.logs.view';

    // Admin Tickets Permissions
    /** List all tickets */
    public static ADMIN_TICKETS_LIST = 'admin.tickets.list';
    /** View ticket details */
    public static ADMIN_TICKETS_VIEW = 'admin.tickets.view';

    // Admin Announcements Permissions
    /** Create new announcements */
    public static ADMIN_ANNOUNCEMENTS_CREATE = 'admin.announcements.create';
    /** Edit existing announcements */
    public static ADMIN_ANNOUNCEMENTS_EDIT = 'admin.announcements.edit';
    /** Delete announcements */
    public static ADMIN_ANNOUNCEMENTS_DELETE = 'admin.announcements.delete';
    /** List all announcements */
    public static ADMIN_ANNOUNCEMENTS_LIST = 'admin.announcements.list';

    // Admin Settings Permissions
    /** View system settings */
    public static ADMIN_SETTINGS_VIEW = 'admin.settings.view';
    /** Edit system settings */
    public static ADMIN_SETTINGS_EDIT = 'admin.settings.edit';

    // Admin Plugins Permissions
    /** Edit existing plugins */
    public static ADMIN_PLUGINS_EDIT = 'admin.plugins.edit';
    /** List all plugins */
    public static ADMIN_PLUGINS_LIST = 'admin.plugins.list';

    // Admin Backups Permissions
    /** Create new backups */
    public static ADMIN_BACKUPS_CREATE = 'admin.backups.create';
    /** Delete backups */
    public static ADMIN_BACKUPS_DELETE = 'admin.backups.delete';
    /** List all backups */
    public static ADMIN_BACKUPS_LIST = 'admin.backups.list';

    // Admin Images Permissions
    /** Create new images */
    public static ADMIN_IMAGES_CREATE = 'admin.images.create';
    /** Edit existing images */
    public static ADMIN_IMAGES_EDIT = 'admin.images.edit';
    /** Delete images */
    public static ADMIN_IMAGES_DELETE = 'admin.images.delete';
    /** List all images */
    public static ADMIN_IMAGES_LIST = 'admin.images.list';

    // Admin Mail Templates Permissions
    /** Create new mail templates */
    public static ADMIN_MAIL_TEMPLATES_CREATE = 'admin.mail.templates.create';
    /** Edit existing mail templates */
    public static ADMIN_MAIL_TEMPLATES_EDIT = 'admin.mail.templates.edit';
    /** Delete mail templates */
    public static ADMIN_MAIL_TEMPLATES_DELETE = 'admin.mail.templates.delete';
    /** List all mail templates */
    public static ADMIN_MAIL_TEMPLATES_LIST = 'admin.mail.templates.list';

    // Admin Mail Permissions
    /** Send mass mails to all users! */
    public static ADMIN_MAIL_SEND_MASS_MAIL = 'admin.mail.send.mass.mails';

    // Admin Redirect Links Permissions
    /** Create new redirect links */
    public static ADMIN_REDIRECT_LINKS_CREATE = 'admin.redirect.links.create';
    /** Edit existing redirect links */
    public static ADMIN_REDIRECT_LINKS_EDIT = 'admin.redirect.links.edit';
    /** Delete redirect links */
    public static ADMIN_REDIRECT_LINKS_DELETE = 'admin.redirect.links.delete';
    /** List all redirect links */
    public static ADMIN_REDIRECT_LINKS_LIST = 'admin.redirect.links.list';

    // Admin Redeem Codes Permissions
    /** Create new redeem codes */
    public static ADMIN_REDEEM_CODES_CREATE = 'admin.redeem.codes.create';
    /** Edit existing redeem codes */
    public static ADMIN_REDEEM_CODES_EDIT = 'admin.redeem.codes.edit';
    /** Delete redeem codes */
    public static ADMIN_REDEEM_CODES_DELETE = 'admin.redeem.codes.delete';
    /** List all redeem codes */
    public static ADMIN_REDEEM_CODES_LIST = 'admin.redeem.codes.list';

    // VIP Permissions
    /** The VIP permission that allows you to create servers on vip eggs or nodes */
    public static USER_PERMISSION_VIP = 'user.permission.vip';

    // Admin Image Reports Permissions
    /** View image reports */
    public static ADMIN_IMAGE_REPORTS_VIEW = 'admin.image.reports.view';
    /** List image reports */
    public static ADMIN_IMAGE_REPORTS_LIST = 'admin.image.reports.list';
    /** Edit image reports */
    public static ADMIN_IMAGE_REPORTS_EDIT = 'admin.image.reports.edit';
    /** Delete image reports */
    public static ADMIN_IMAGE_REPORTS_DELETE = 'admin.image.reports.delete';

    // Adblocker Permissions
    /** The permission that lets you bypass the antiadblocker! */
    public static USER_PERMISSION_BYPASS_ADBLOCKER = 'user.permission.adblocker';

    // AntiAlting Permissions
    /** This permissions bypasses the system that does alt checks! */
    public static USER_PERMISSION_BYPASS_ALTING = 'user.permission.alt';

    // AntiVPN Permissions
    /** This permission does a full bypass of the antivpn system! */
    public static USER_PERMISSION_BYPASS_VPN = 'user.permission.vpn';

    /**
     * Returns all permission nodes with metadata.
     */
    public static getAll(): Array<{ constant: string; value: string; category: string; description: string }> {
        return [
            {
                constant: 'ADMIN_ROOT',
                value: Permissions.ADMIN_ROOT,
                category: 'Admin Root',
                description: 'Full access to everything',
            },
            {
                constant: 'ADMIN_DASHBOARD_VIEW',
                value: Permissions.ADMIN_DASHBOARD_VIEW,
                category: 'Admin Dashboard View',
                description: 'Access to view the admin dashboard',
            },
            {
                constant: 'ADMIN_DASHBOARD_COMPONENTS_SYSTEM_OVERVIEW',
                value: Permissions.ADMIN_DASHBOARD_COMPONENTS_SYSTEM_OVERVIEW,
                category: 'Admin Dashboard Components',
                description: 'View system overview component',
            },
            {
                constant: 'ADMIN_DASHBOARD_COMPONENTS_SYSTEM_UPDATES',
                value: Permissions.ADMIN_DASHBOARD_COMPONENTS_SYSTEM_UPDATES,
                category: 'Admin Dashboard Components',
                description: 'View system updates component',
            },
            {
                constant: 'ADMIN_DASHBOARD_COMPONENTS_SYSTEM_LOGS',
                value: Permissions.ADMIN_DASHBOARD_COMPONENTS_SYSTEM_LOGS,
                category: 'Admin Dashboard Components',
                description: 'View system logs component',
            },
            {
                constant: 'ADMIN_DASHBOARD_COMPONENTS_ANALYTICS_VIEW',
                value: Permissions.ADMIN_DASHBOARD_COMPONENTS_ANALYTICS_VIEW,
                category: 'Admin Dashboard Components',
                description: 'View analytics at-a-glance component',
            },
            {
                constant: 'ADMIN_DASHBOARD_COMPONENTS_SUPPORT_VIEW',
                value: Permissions.ADMIN_DASHBOARD_COMPONENTS_SUPPORT_VIEW,
                category: 'Admin Dashboard Components',
                description: 'View support component',
            },
            {
                constant: 'ADMIN_DASHBOARD_COMPONENTS_GITHUB_VIEW',
                value: Permissions.ADMIN_DASHBOARD_COMPONENTS_GITHUB_VIEW,
                category: 'Admin Dashboard Components',
                description: 'View GitHub repository component',
            },
            {
                constant: 'ADMIN_DASHBOARD_COMPONENTS_QUICK_ACTIONS',
                value: Permissions.ADMIN_DASHBOARD_COMPONENTS_QUICK_ACTIONS,
                category: 'Admin Dashboard Components',
                description: 'View quick actions component',
            },
            {
                constant: 'ADMIN_DASHBOARD_COMPONENTS_ACTIVITY_VIEW',
                value: Permissions.ADMIN_DASHBOARD_COMPONENTS_ACTIVITY_VIEW,
                category: 'Admin Dashboard Components',
                description: 'View activity feed component',
            },
            {
                constant: 'ADMIN_HEALTH_VIEW',
                value: Permissions.ADMIN_HEALTH_VIEW,
                category: 'Admin Health',
                description: 'See the health of the dashboard!',
            },
            {
                constant: 'ADMIN_USERS_EDIT',
                value: Permissions.ADMIN_USERS_EDIT,
                category: 'Admin Users',
                description: 'Edit existing users',
            },
            {
                constant: 'ADMIN_USERS_DELETE',
                value: Permissions.ADMIN_USERS_DELETE,
                category: 'Admin Users',
                description: 'Delete users',
            },
            {
                constant: 'ADMIN_USERS_LIST',
                value: Permissions.ADMIN_USERS_LIST,
                category: 'Admin Users',
                description: 'List all users',
            },
            {
                constant: 'ADMIN_J4R_SERVERS_CREATE',
                value: Permissions.ADMIN_J4R_SERVERS_CREATE,
                category: 'Admin J4R Servers',
                description: 'Create new J4R (Join for Rewards) servers',
            },
            {
                constant: 'ADMIN_J4R_SERVERS_EDIT',
                value: Permissions.ADMIN_J4R_SERVERS_EDIT,
                category: 'Admin J4R Servers',
                description: 'Edit existing J4R (Join for Rewards) servers',
            },
            {
                constant: 'ADMIN_J4R_SERVERS_DELETE',
                value: Permissions.ADMIN_J4R_SERVERS_DELETE,
                category: 'Admin J4R Servers',
                description: 'Delete J4R (Join for Rewards) servers',
            },
            {
                constant: 'ADMIN_J4R_SERVERS_LIST',
                value: Permissions.ADMIN_J4R_SERVERS_LIST,
                category: 'Admin J4R Servers',
                description: 'List all J4R (Join for Rewards) servers',
            },
            {
                constant: 'ADMIN_LOCATIONS_CREATE',
                value: Permissions.ADMIN_LOCATIONS_CREATE,
                category: 'Admin Locations',
                description: 'Create new locations',
            },
            {
                constant: 'ADMIN_LOCATIONS_EDIT',
                value: Permissions.ADMIN_LOCATIONS_EDIT,
                category: 'Admin Locations',
                description: 'Edit existing locations',
            },
            {
                constant: 'ADMIN_LOCATIONS_DELETE',
                value: Permissions.ADMIN_LOCATIONS_DELETE,
                category: 'Admin Locations',
                description: 'Delete locations',
            },
            {
                constant: 'ADMIN_LOCATIONS_LIST',
                value: Permissions.ADMIN_LOCATIONS_LIST,
                category: 'Admin Locations',
                description: 'List all locations',
            },
            {
                constant: 'ADMIN_NESTS_CREATE',
                value: Permissions.ADMIN_NESTS_CREATE,
                category: 'Admin Nests',
                description: 'Create new nests',
            },
            {
                constant: 'ADMIN_NESTS_EDIT',
                value: Permissions.ADMIN_NESTS_EDIT,
                category: 'Admin Nests',
                description: 'Edit existing nests',
            },
            {
                constant: 'ADMIN_NESTS_DELETE',
                value: Permissions.ADMIN_NESTS_DELETE,
                category: 'Admin Nests',
                description: 'Delete nests',
            },
            {
                constant: 'ADMIN_NESTS_LIST',
                value: Permissions.ADMIN_NESTS_LIST,
                category: 'Admin Nests',
                description: 'List all nests',
            },
            {
                constant: 'ADMIN_EGG_CREATE',
                value: Permissions.ADMIN_EGG_CREATE,
                category: 'Admin Eggs',
                description: 'Create new eggs',
            },
            {
                constant: 'ADMIN_EGG_EDIT',
                value: Permissions.ADMIN_EGG_EDIT,
                category: 'Admin Eggs',
                description: 'Edit existing eggs',
            },
            {
                constant: 'ADMIN_EGG_DELETE',
                value: Permissions.ADMIN_EGG_DELETE,
                category: 'Admin Eggs',
                description: 'Delete eggs',
            },
            {
                constant: 'ADMIN_EGG_LIST',
                value: Permissions.ADMIN_EGG_LIST,
                category: 'Admin Eggs',
                description: 'List all eggs',
            },
            {
                constant: 'ADMIN_DEPARTMENTS_CREATE',
                value: Permissions.ADMIN_DEPARTMENTS_CREATE,
                category: 'Admin Departments',
                description: 'Create new departments',
            },
            {
                constant: 'ADMIN_DEPARTMENTS_EDIT',
                value: Permissions.ADMIN_DEPARTMENTS_EDIT,
                category: 'Admin Departments',
                description: 'Edit existing departments',
            },
            {
                constant: 'ADMIN_DEPARTMENTS_DELETE',
                value: Permissions.ADMIN_DEPARTMENTS_DELETE,
                category: 'Admin Departments',
                description: 'Delete departments',
            },
            {
                constant: 'ADMIN_DEPARTMENTS_LIST',
                value: Permissions.ADMIN_DEPARTMENTS_LIST,
                category: 'Admin Departments',
                description: 'List all departments',
            },
            {
                constant: 'ADMIN_ROLES_CREATE',
                value: Permissions.ADMIN_ROLES_CREATE,
                category: 'Admin Roles',
                description: 'Create new roles',
            },
            {
                constant: 'ADMIN_ROLES_EDIT',
                value: Permissions.ADMIN_ROLES_EDIT,
                category: 'Admin Roles',
                description: 'Edit existing roles',
            },
            {
                constant: 'ADMIN_ROLES_DELETE',
                value: Permissions.ADMIN_ROLES_DELETE,
                category: 'Admin Roles',
                description: 'Delete roles',
            },
            {
                constant: 'ADMIN_ROLES_LIST',
                value: Permissions.ADMIN_ROLES_LIST,
                category: 'Admin Roles',
                description: 'List all roles',
            },
            {
                constant: 'ADMIN_PERMISSIONS_CREATE',
                value: Permissions.ADMIN_PERMISSIONS_CREATE,
                category: 'Admin Permissions',
                description: 'Create new permissions',
            },
            {
                constant: 'ADMIN_PERMISSIONS_EDIT',
                value: Permissions.ADMIN_PERMISSIONS_EDIT,
                category: 'Admin Permissions',
                description: 'Edit existing permissions',
            },
            {
                constant: 'ADMIN_PERMISSIONS_DELETE',
                value: Permissions.ADMIN_PERMISSIONS_DELETE,
                category: 'Admin Permissions',
                description: 'Delete permissions',
            },
            {
                constant: 'ADMIN_PERMISSIONS_LIST',
                value: Permissions.ADMIN_PERMISSIONS_LIST,
                category: 'Admin Permissions',
                description: 'List all permissions',
            },
            {
                constant: 'ADMIN_SERVERS_EDIT',
                value: Permissions.ADMIN_SERVERS_EDIT,
                category: 'Admin Servers',
                description: 'Edit existing servers',
            },
            {
                constant: 'ADMIN_SERVERS_DELETE',
                value: Permissions.ADMIN_SERVERS_DELETE,
                category: 'Admin Servers',
                description: 'Delete servers',
            },
            {
                constant: 'ADMIN_SERVERS_LIST',
                value: Permissions.ADMIN_SERVERS_LIST,
                category: 'Admin Servers',
                description: 'List all servers',
            },
            {
                constant: 'ADMIN_SERVER_QUEUE_CREATE',
                value: Permissions.ADMIN_SERVER_QUEUE_CREATE,
                category: 'Admin Server Queue',
                description: 'Create new server queue entries',
            },
            {
                constant: 'ADMIN_SERVER_QUEUE_DELETE',
                value: Permissions.ADMIN_SERVER_QUEUE_DELETE,
                category: 'Admin Server Queue',
                description: 'Delete server queue entries',
            },
            {
                constant: 'ADMIN_SERVER_QUEUE_LIST',
                value: Permissions.ADMIN_SERVER_QUEUE_LIST,
                category: 'Admin Server Queue',
                description: 'List all server queue entries',
            },
            {
                constant: 'ADMIN_SERVER_QUEUE_VIEW',
                value: Permissions.ADMIN_SERVER_QUEUE_VIEW,
                category: 'Admin Server Queue',
                description: 'View server queue details',
            },
            {
                constant: 'ADMIN_SERVER_QUEUE_LOGS_VIEW',
                value: Permissions.ADMIN_SERVER_QUEUE_LOGS_VIEW,
                category: 'Admin Server Queue Logs',
                description: 'View server queue logs',
            },
            {
                constant: 'ADMIN_TICKETS_LIST',
                value: Permissions.ADMIN_TICKETS_LIST,
                category: 'Admin Tickets',
                description: 'List all tickets',
            },
            {
                constant: 'ADMIN_TICKETS_VIEW',
                value: Permissions.ADMIN_TICKETS_VIEW,
                category: 'Admin Tickets',
                description: 'View ticket details',
            },
            {
                constant: 'ADMIN_ANNOUNCEMENTS_CREATE',
                value: Permissions.ADMIN_ANNOUNCEMENTS_CREATE,
                category: 'Admin Announcements',
                description: 'Create new announcements',
            },
            {
                constant: 'ADMIN_ANNOUNCEMENTS_EDIT',
                value: Permissions.ADMIN_ANNOUNCEMENTS_EDIT,
                category: 'Admin Announcements',
                description: 'Edit existing announcements',
            },
            {
                constant: 'ADMIN_ANNOUNCEMENTS_DELETE',
                value: Permissions.ADMIN_ANNOUNCEMENTS_DELETE,
                category: 'Admin Announcements',
                description: 'Delete announcements',
            },
            {
                constant: 'ADMIN_ANNOUNCEMENTS_LIST',
                value: Permissions.ADMIN_ANNOUNCEMENTS_LIST,
                category: 'Admin Announcements',
                description: 'List all announcements',
            },
            {
                constant: 'ADMIN_SETTINGS_VIEW',
                value: Permissions.ADMIN_SETTINGS_VIEW,
                category: 'Admin Settings',
                description: 'View system settings',
            },
            {
                constant: 'ADMIN_SETTINGS_EDIT',
                value: Permissions.ADMIN_SETTINGS_EDIT,
                category: 'Admin Settings',
                description: 'Edit system settings',
            },
            {
                constant: 'ADMIN_PLUGINS_EDIT',
                value: Permissions.ADMIN_PLUGINS_EDIT,
                category: 'Admin Plugins',
                description: 'Edit existing plugins',
            },
            {
                constant: 'ADMIN_PLUGINS_LIST',
                value: Permissions.ADMIN_PLUGINS_LIST,
                category: 'Admin Plugins',
                description: 'List all plugins',
            },
            {
                constant: 'ADMIN_BACKUPS_CREATE',
                value: Permissions.ADMIN_BACKUPS_CREATE,
                category: 'Admin Backups',
                description: 'Create new backups',
            },
            {
                constant: 'ADMIN_BACKUPS_DELETE',
                value: Permissions.ADMIN_BACKUPS_DELETE,
                category: 'Admin Backups',
                description: 'Delete backups',
            },
            {
                constant: 'ADMIN_BACKUPS_LIST',
                value: Permissions.ADMIN_BACKUPS_LIST,
                category: 'Admin Backups',
                description: 'List all backups',
            },
            {
                constant: 'ADMIN_IMAGES_CREATE',
                value: Permissions.ADMIN_IMAGES_CREATE,
                category: 'Admin Images',
                description: 'Create new images',
            },
            {
                constant: 'ADMIN_IMAGES_EDIT',
                value: Permissions.ADMIN_IMAGES_EDIT,
                category: 'Admin Images',
                description: 'Edit existing images',
            },
            {
                constant: 'ADMIN_IMAGES_DELETE',
                value: Permissions.ADMIN_IMAGES_DELETE,
                category: 'Admin Images',
                description: 'Delete images',
            },
            {
                constant: 'ADMIN_IMAGES_LIST',
                value: Permissions.ADMIN_IMAGES_LIST,
                category: 'Admin Images',
                description: 'List all images',
            },
            {
                constant: 'ADMIN_MAIL_TEMPLATES_CREATE',
                value: Permissions.ADMIN_MAIL_TEMPLATES_CREATE,
                category: 'Admin Mail Templates',
                description: 'Create new mail templates',
            },
            {
                constant: 'ADMIN_MAIL_TEMPLATES_EDIT',
                value: Permissions.ADMIN_MAIL_TEMPLATES_EDIT,
                category: 'Admin Mail Templates',
                description: 'Edit existing mail templates',
            },
            {
                constant: 'ADMIN_MAIL_TEMPLATES_DELETE',
                value: Permissions.ADMIN_MAIL_TEMPLATES_DELETE,
                category: 'Admin Mail Templates',
                description: 'Delete mail templates',
            },
            {
                constant: 'ADMIN_MAIL_TEMPLATES_LIST',
                value: Permissions.ADMIN_MAIL_TEMPLATES_LIST,
                category: 'Admin Mail Templates',
                description: 'List all mail templates',
            },
            {
                constant: 'ADMIN_MAIL_SEND_MASS_MAIL',
                value: Permissions.ADMIN_MAIL_SEND_MASS_MAIL,
                category: 'Admin Mail',
                description: 'Send mass mails to all users!',
            },
            {
                constant: 'ADMIN_REDIRECT_LINKS_CREATE',
                value: Permissions.ADMIN_REDIRECT_LINKS_CREATE,
                category: 'Admin Redirect Links',
                description: 'Create new redirect links',
            },
            {
                constant: 'ADMIN_REDIRECT_LINKS_EDIT',
                value: Permissions.ADMIN_REDIRECT_LINKS_EDIT,
                category: 'Admin Redirect Links',
                description: 'Edit existing redirect links',
            },
            {
                constant: 'ADMIN_REDIRECT_LINKS_DELETE',
                value: Permissions.ADMIN_REDIRECT_LINKS_DELETE,
                category: 'Admin Redirect Links',
                description: 'Delete redirect links',
            },
            {
                constant: 'ADMIN_REDIRECT_LINKS_LIST',
                value: Permissions.ADMIN_REDIRECT_LINKS_LIST,
                category: 'Admin Redirect Links',
                description: 'List all redirect links',
            },
            {
                constant: 'ADMIN_REDEEM_CODES_CREATE',
                value: Permissions.ADMIN_REDEEM_CODES_CREATE,
                category: 'Admin Redeem Codes',
                description: 'Create new redeem codes',
            },
            {
                constant: 'ADMIN_REDEEM_CODES_EDIT',
                value: Permissions.ADMIN_REDEEM_CODES_EDIT,
                category: 'Admin Redeem Codes',
                description: 'Edit existing redeem codes',
            },
            {
                constant: 'ADMIN_REDEEM_CODES_DELETE',
                value: Permissions.ADMIN_REDEEM_CODES_DELETE,
                category: 'Admin Redeem Codes',
                description: 'Delete redeem codes',
            },
            {
                constant: 'ADMIN_REDEEM_CODES_LIST',
                value: Permissions.ADMIN_REDEEM_CODES_LIST,
                category: 'Admin Redeem Codes',
                description: 'List all redeem codes',
            },
            {
                constant: 'USER_PERMISSION_VIP',
                value: Permissions.USER_PERMISSION_VIP,
                category: 'VIP',
                description: 'The VIP permission that allows you to create servers on vip eggs or nodes',
            },
            {
                constant: 'ADMIN_IMAGE_REPORTS_VIEW',
                value: Permissions.ADMIN_IMAGE_REPORTS_VIEW,
                category: 'Admin Image Reports',
                description: 'View image reports',
            },
            {
                constant: 'ADMIN_IMAGE_REPORTS_LIST',
                value: Permissions.ADMIN_IMAGE_REPORTS_LIST,
                category: 'Admin Image Reports',
                description: 'List image reports',
            },
            {
                constant: 'ADMIN_IMAGE_REPORTS_EDIT',
                value: Permissions.ADMIN_IMAGE_REPORTS_EDIT,
                category: 'Admin Image Reports',
                description: 'Edit image reports',
            },
            {
                constant: 'ADMIN_IMAGE_REPORTS_DELETE',
                value: Permissions.ADMIN_IMAGE_REPORTS_DELETE,
                category: 'Admin Image Reports',
                description: 'Delete image reports',
            },
            {
                constant: 'USER_PERMISSION_BYPASS_ADBLOCKER',
                value: Permissions.USER_PERMISSION_BYPASS_ADBLOCKER,
                category: 'Adblocker',
                description: 'The permission that lets you bypass the antiadblocker!',
            },
            {
                constant: 'USER_PERMISSION_BYPASS_ALTING',
                value: Permissions.USER_PERMISSION_BYPASS_ALTING,
                category: 'AntiAlting',
                description: 'This permissions bypasses the system that does alt checks!',
            },
            {
                constant: 'USER_PERMISSION_BYPASS_VPN',
                value: Permissions.USER_PERMISSION_BYPASS_VPN,
                category: 'AntiVPN',
                description: 'This permission does a full bypass of the antivpn system!',
            },
        ];
    }
}

export default Permissions;
