import { ref, computed } from 'vue';
import {
    LayoutDashboard,
    Users,
    PaperclipIcon,
    InfoIcon,
    SettingsIcon,
    ServerCrash,
    EggIcon,
    Building,
    BellIcon,
    MailIcon,
    Coins as Coin,
    Server as Servers,
    Package as Plugins,
    LogsIcon,
    Database,
    HeartHandshakeIcon,
    Image as ImageIcon,
    LinkIcon,
    Shield,
    Server,
} from 'lucide-vue-next';
import type { MenuGroup, ProfileMenuItem } from '../types';
import Session from '@/mythicaldash/Session';
import Permissions from '@/mythicaldash/Permissions';
import { useFeatureFlags } from '@/composables/useFeatureFlags';

// Define the dashboard data type inline to avoid import issues
interface DashboardCounts {
    user_count: number;
    locations_count: number;
    tickets_count: number;
    eggs_count: number;
    departments_count: number;
    announcements_count: number;
    server_queue_count: number;
    mail_templates_count: number;
    settings_count: number;
    redeem_codes_count: number;
    servers_count: number;
    plugins_count: number;
    backups_count: number;
    images_count: number;
    redirect_links_count: number;
    roles_count: number;
    j4r_servers_count: number;
    image_reports_count: number;
}

interface DashboardData {
    count: DashboardCounts;
}

// Use a simpler type for the route
export function useAdminMenu(route: { path: string }, dashBoard: { value: DashboardData }) {
    const adminBaseUri = '/mc-admin';
    const {
        isAllowTickets,
        isAllowServers,
        isImageHostingEnabled,
        isJ4REnabled,
        isCodeRedemptionEnabled,
        isDailyBackupEnabled,
        isSMTPEnabled,
    } = useFeatureFlags();

    const menuGroups = ref<MenuGroup[]>([
        {
            title: 'Main Menu',
            items: [
                {
                    name: 'Dashboard',
                    path: `${adminBaseUri}`,
                    icon: LayoutDashboard,
                    active: route.path === `${adminBaseUri}`,
                    visible: computed(() => Session.Permission.Has(Permissions.ADMIN_DASHBOARD_VIEW)),
                },
                {
                    name: 'Health',
                    path: `${adminBaseUri}/health`,
                    icon: HeartHandshakeIcon,
                    active: route.path === `${adminBaseUri}/health`,
                    visible: computed(() => Session.Permission.Has(Permissions.ADMIN_HEALTH_VIEW)),
                },
            ],
        },
        {
            title: 'Management',
            items: [
                {
                    name: 'Users',
                    path: `${adminBaseUri}/users`,
                    icon: Users,
                    count: computed(() => dashBoard.value.count.user_count || 0),
                    active: route.path === `${adminBaseUri}/users`,
                    visible: computed(() => Session.Permission.Has(Permissions.ADMIN_USERS_LIST)),
                },
                {
                    name: 'Locations',
                    icon: PaperclipIcon,
                    path: `${adminBaseUri}/locations`,
                    active: route.path === `${adminBaseUri}/locations`,
                    count: computed(() => dashBoard.value.count.locations_count || 0),
                    visible: computed(() => Session.Permission.Has(Permissions.ADMIN_LOCATIONS_LIST)),
                },
                {
                    name: 'Eggs & Nests',
                    icon: EggIcon,
                    count: computed(() => dashBoard.value.count.eggs_count || 0),
                    active: route.path === `${adminBaseUri}/egg-categories` || route.path === `${adminBaseUri}/eggs`,
                    visible: computed(
                        () =>
                            Session.Permission.Has(Permissions.ADMIN_EGG_LIST) ||
                            Session.Permission.Has(Permissions.ADMIN_NESTS_LIST),
                    ),
                    subMenu: [
                        {
                            name: 'Nests (Categories)',
                            path: `${adminBaseUri}/egg-categories`,
                            icon: EggIcon,
                            visible: computed(() => Session.Permission.Has(Permissions.ADMIN_EGG_LIST)),
                        },
                        {
                            name: 'Eggs (Eggs)',
                            path: `${adminBaseUri}/eggs`,
                            icon: EggIcon,
                            visible: computed(() => Session.Permission.Has(Permissions.ADMIN_EGG_LIST)),
                        },
                    ],
                },
                {
                    name: 'Departments',
                    path: `${adminBaseUri}/departments`,
                    icon: Building,
                    count: computed(() => dashBoard.value.count.departments_count || 0),
                    active: route.path === `${adminBaseUri}/departments`,
                    visible: computed(() => Session.Permission.Has(Permissions.ADMIN_DEPARTMENTS_LIST)),
                },
                {
                    name: 'Roles',
                    path: `${adminBaseUri}/roles`,
                    icon: Shield,
                    count: computed(() => dashBoard.value.count.roles_count || 0),
                    active: route.path === `${adminBaseUri}/roles`,
                    visible: computed(() => Session.Permission.Has(Permissions.ADMIN_ROLES_LIST)),
                },
            ],
        },
        {
            title: 'Earning',
            items: [
                {
                    name: 'J4R Servers',
                    path: `${adminBaseUri}/j4r-servers`,
                    icon: Server,
                    count: computed(() => dashBoard.value.count.j4r_servers_count || 0),
                    active: route.path === `${adminBaseUri}/j4r-servers`,
                    visible: computed(
                        () => Session.Permission.Has(Permissions.ADMIN_J4R_SERVERS_LIST) && isJ4REnabled.value,
                    ),
                },
                {
                    name: 'Redeem Codes',
                    path: `${adminBaseUri}/redeem-codes`,
                    icon: Coin,
                    count: computed(() => dashBoard.value.count.redeem_codes_count || 0),
                    active: route.path === `${adminBaseUri}/redeem-codes`,
                    visible: computed(
                        () =>
                            Session.Permission.Has(Permissions.ADMIN_REDEEM_CODES_LIST) &&
                            isCodeRedemptionEnabled.value,
                    ),
                },
            ],
        },
        {
            title: 'Image Hosting',
            items: [
                {
                    name: 'Image Reports',
                    path: `${adminBaseUri}/image-reports`,
                    icon: ImageIcon,
                    count: computed(() => dashBoard.value.count.image_reports_count || 0),
                    active: route.path === `${adminBaseUri}/image-reports`,
                    visible: computed(
                        () =>
                            Session.Permission.Has(Permissions.ADMIN_IMAGE_REPORTS_VIEW) && isImageHostingEnabled.value,
                    ),
                },
            ],
        },
        {
            title: 'Servers',
            items: [
                {
                    name: 'Server Queue',
                    icon: ServerCrash,
                    active:
                        route.path === `${adminBaseUri}/server-queue` ||
                        route.path === `${adminBaseUri}/server-queue/logs`,
                    count: computed(() => dashBoard.value.count.server_queue_count || 0),
                    visible: computed(
                        () =>
                            (Session.Permission.Has(Permissions.ADMIN_SERVER_QUEUE_LIST) ||
                                Session.Permission.Has(Permissions.ADMIN_SERVER_QUEUE_LOGS_VIEW)) &&
                            isAllowServers.value,
                    ),
                    subMenu: [
                        {
                            name: 'Server Queue',
                            path: `${adminBaseUri}/server-queue`,
                            icon: ServerCrash,
                            visible: computed(
                                () =>
                                    Session.Permission.Has(Permissions.ADMIN_SERVER_QUEUE_LIST) && isAllowServers.value,
                            ),
                        },
                        {
                            name: 'Server Queue Logs',
                            path: `${adminBaseUri}/server-queue/logs`,
                            icon: LogsIcon,
                            visible: computed(
                                () =>
                                    Session.Permission.Has(Permissions.ADMIN_SERVER_QUEUE_LOGS_VIEW) &&
                                    isAllowServers.value,
                            ),
                        },
                    ],
                },
                {
                    name: 'Servers',
                    icon: Servers,
                    active: route.path === `${adminBaseUri}/servers`,
                    count: computed(() => dashBoard.value.count.servers_count || 0),
                    path: `${adminBaseUri}/servers`,
                    visible: computed(
                        () => Session.Permission.Has(Permissions.ADMIN_SERVERS_LIST) && isAllowServers.value,
                    ),
                },
            ],
        },
        {
            title: 'Support Buddy',
            items: [
                {
                    name: 'Tickets',
                    path: `${adminBaseUri}/tickets`,
                    icon: InfoIcon,
                    active: route.path === `${adminBaseUri}/tickets`,
                    count: computed(() => dashBoard.value.count.tickets_count || 0),
                    visible: computed(
                        () => Session.Permission.Has(Permissions.ADMIN_TICKETS_LIST) && isAllowTickets.value,
                    ),
                },
                {
                    name: 'Announcements',
                    path: `${adminBaseUri}/announcements`,
                    icon: BellIcon,
                    active: route.path === `${adminBaseUri}/announcements`,
                    count: computed(() => dashBoard.value.count.announcements_count || 0),
                    visible: computed(() => Session.Permission.Has(Permissions.ADMIN_ANNOUNCEMENTS_LIST)),
                },
            ],
        },
        {
            title: 'Advanced',
            items: [
                {
                    name: 'Settings',
                    path: `${adminBaseUri}/settings`,
                    icon: SettingsIcon,
                    active: route.path === `${adminBaseUri}/settings`,
                    count: computed(() => dashBoard.value.count.settings_count || 0),
                    visible: computed(() => Session.Permission.Has(Permissions.ADMIN_SETTINGS_VIEW)),
                },
                {
                    name: 'Plugins',
                    path: `${adminBaseUri}/plugins`,
                    icon: Plugins,
                    active: route.path === `${adminBaseUri}/plugins`,
                    count: computed(() => dashBoard.value.count.plugins_count || 0),
                    visible: computed(() => Session.Permission.Has(Permissions.ADMIN_PLUGINS_LIST)),
                },
                {
                    name: 'Backups',
                    path: `${adminBaseUri}/backups`,
                    icon: Database,
                    active: route.path === `${adminBaseUri}/backups`,
                    count: computed(() => dashBoard.value.count.backups_count || 0),
                    visible: computed(
                        () => Session.Permission.Has(Permissions.ADMIN_BACKUPS_LIST) && isDailyBackupEnabled.value,
                    ),
                },
            ],
        },
        {
            title: 'Meta',
            items: [
                {
                    name: 'Images',
                    path: `${adminBaseUri}/images`,
                    icon: ImageIcon,
                    active: route.path === `${adminBaseUri}/images`,
                    count: computed(() => dashBoard.value.count.images_count || 0),
                    visible: computed(
                        () => Session.Permission.Has(Permissions.ADMIN_IMAGES_LIST) && isImageHostingEnabled.value,
                    ),
                },
                {
                    name: 'Mail Templates',
                    path: `${adminBaseUri}/mail-templates`,
                    icon: MailIcon,
                    active: route.path === `${adminBaseUri}/mail-templates`,
                    count: computed(() => dashBoard.value.count.mail_templates_count || 0),
                    visible: computed(
                        () => Session.Permission.Has(Permissions.ADMIN_MAIL_TEMPLATES_LIST) && isSMTPEnabled.value,
                    ),
                },
                {
                    name: 'Redirect Links',
                    path: `${adminBaseUri}/redirect-links`,
                    icon: LinkIcon,
                    active: route.path === `${adminBaseUri}/redirect-links`,
                    count: computed(() => dashBoard.value.count.redirect_links_count || 0),
                    visible: computed(() => Session.Permission.Has(Permissions.ADMIN_REDIRECT_LINKS_LIST)),
                },
            ],
        },
    ]);

    const profileMenu: ProfileMenuItem[] = [
        { name: 'Profile', path: '/account' },
        { name: 'Exit Admin', path: '/dashboard' },
        { name: 'Sign out', path: '/auth/logout' },
    ];

    return {
        menuGroups,
        profileMenu,
    };
}
