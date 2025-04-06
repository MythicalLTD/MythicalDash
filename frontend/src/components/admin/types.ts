export interface MenuItem {
    name: string;
    path?: string;
    icon: unknown;
    active?: boolean;
    count?: unknown;
    subMenu?: MenuItem[];
    isOpen?: boolean;
}

export interface MenuGroup {
    title: string;
    items: MenuItem[];
}

export interface SearchResult {
    id: number;
    name: string;
    path: string;
}

export interface ProfileMenuItem {
    name: string;
    path: string;
}

export interface FooterLink {
    name: string;
    path: string;
}

export interface DashboardCounts {
    user_count: number;
    locations_count: number;
    tickets_count: number;
    eggs_count: number;
    departments_count: number;
    announcements_count: number;
    server_queue_count: number;
    mail_templates_count: number;
    settings_count: number;
}

export interface DashboardData {
    count: DashboardCounts;
}

export interface SessionInfo {
    getInfo(key: string): string;
}

export interface SettingsStore {
    getSetting(key: string): string;
}
