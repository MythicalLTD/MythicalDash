import { ref, computed } from 'vue';
import {
    LayoutDashboard,
    Users,
    //Database,
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
} from 'lucide-vue-next';
import type { MenuGroup, ProfileMenuItem } from '../types';

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
}

interface DashboardData {
    count: DashboardCounts;
}

// Use a simpler type for the route
export function useAdminMenu(route: { path: string }, dashBoard: { value: DashboardData }) {
    const adminBaseUri = '/mc-admin';

    const menuGroups = ref<MenuGroup[]>([
        {
            title: 'Main Menu',
            items: [
                {
                    name: 'Dashboard',
                    path: `${adminBaseUri}`,
                    icon: LayoutDashboard,
                    active: route.path === `${adminBaseUri}`,
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
                },
                {
                    name: 'Locations',
                    icon: PaperclipIcon,
                    path: `${adminBaseUri}/locations`,
                    active: route.path === `${adminBaseUri}/locations`,
                    count: computed(() => dashBoard.value.count.locations_count || 0),
                },
                {
                    name: 'Eggs & Nests',
                    icon: EggIcon,
                    count: computed(() => dashBoard.value.count.eggs_count || 0),
                    subMenu: [
                        {
                            name: 'Nests (Categories)',
                            path: `${adminBaseUri}/egg-categories`,
                            icon: EggIcon,
                        },
                        {
                            name: 'Eggs (Eggs)',
                            path: `${adminBaseUri}/eggs`,
                            icon: EggIcon,
                        },
                    ],
                },
                {
                    name: 'Redeem Codes',
                    path: `${adminBaseUri}/redeem-codes`,
                    icon: Coin,
                    count: computed(() => dashBoard.value.count.redeem_codes_count || 0),
                    active: route.path === `${adminBaseUri}/redeem-codes`,
                },
                {
                    name: 'Departments',
                    path: `${adminBaseUri}/departments`,
                    icon: Building,
                    count: computed(() => dashBoard.value.count.departments_count || 0),
                    active: route.path === `${adminBaseUri}/departments`,
                },
            ],
        },
        {
            title: 'Servers',
            items: [
                {
                    name: 'Server Queue',
                    icon: ServerCrash,
                    active: route.path === `${adminBaseUri}/server-queue`,
                    count: computed(() => dashBoard.value.count.server_queue_count || 0),
                    subMenu: [
                        {
                            name: 'Server Queue',
                            path: `${adminBaseUri}/server-queue`,
                            icon: ServerCrash,
                        },
                        {
                            name: 'Server Queue Logs',
                            path: `${adminBaseUri}/server-queue/logs`,
                            icon: LogsIcon,
                        },
                    ],
                },
                {
                    name: 'Servers',
                    icon: Servers,
                    active: route.path === `${adminBaseUri}/servers`,
                    count: computed(() => dashBoard.value.count.servers_count || 0),
                    path: `${adminBaseUri}/servers`,
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
                },
                {
                    name: 'Announcements',
                    path: `${adminBaseUri}/announcements`,
                    icon: BellIcon,
                    active: route.path === `${adminBaseUri}/announcements`,
                    count: computed(() => dashBoard.value.count.announcements_count || 0),
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
                },
                {
                    name: 'Mail Templates',
                    path: `${adminBaseUri}/mail-templates`,
                    icon: MailIcon,
                    active: route.path === `${adminBaseUri}/mail-templates`,
                    count: computed(() => dashBoard.value.count.mail_templates_count || 0),
                },
                {
                    name: 'Plugins',
                    path: `${adminBaseUri}/plugins`,
                    icon: Plugins,
                    active: route.path === `${adminBaseUri}/plugins`,
                    count: computed(() => dashBoard.value.count.plugins_count || 0),
                },
                //{
                //    name: 'MythicalCloud (Synced)',
                //    path: `${adminBaseUri}/mythicalcloud`,
                //    icon: Database,
                //    active: route.path === `${adminBaseUri}/mythicalcloud`,
                //},
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
