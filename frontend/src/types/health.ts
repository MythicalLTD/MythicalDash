export interface MemoryUsage {
    total: number;
    used: number;
    free: number;
    usage_percentage: number;
    status: 'ok' | 'warning' | 'error';
    current: number;
    limit: string;
}

export interface DiskSpace {
    total: number;
    used: number;
    free: number;
    usage_percentage: number;
    status: 'ok' | 'warning' | 'error';
    current: number;
}

export interface GithubData {
    version: string;
    latest_version: string;
    update_available: boolean;
    last_check: string;
    stars: number;
    forks: number;
    open_issues: number;
    last_update: string;
}

export interface CronJob {
    id: number;
    task_name: string;
    last_run_at: string | null;
    last_run_success: boolean;
    last_run_message: string | null;
    expected_interval_seconds: number;
    late: boolean;
}

export interface CronData {
    recent: CronJob[];
    summary: string | null;
}

export interface SystemConfig {
    php_version: {
        current: string;
        recommended: string;
        status: 'ok' | 'warning' | 'error';
    };
    max_execution_time: {
        current: number;
        recommended: number;
        status: 'ok' | 'warning' | 'error';
    };
    upload_max_filesize: {
        current: string;
        recommended: string;
        status: 'ok' | 'warning' | 'error';
    };
    post_max_size: {
        current: string;
        recommended: string;
        status: 'ok' | 'warning' | 'error';
    };
}

export interface DatabaseInfo {
    size: {
        current: number;
        formatted: string;
        status: 'ok' | 'warning' | 'error';
    };
    connections: {
        active: number;
        max: number;
        status: 'ok' | 'warning' | 'error';
    };
    tables: {
        count: number;
        status: 'ok' | 'warning' | 'error';
    };
    slow_queries: {
        count: number;
        status: 'ok' | 'warning' | 'error';
    };
}

export interface HealthData {
    status: 'healthy' | 'warning' | 'unhealthy';
    timestamp: number;
    system: SystemConfig;
    memory_usage: MemoryUsage;
    disk_space: DiskSpace;
    database: DatabaseInfo;
    github_data: GithubData;
    extensions: {
        required: {
            [key: string]: {
                installed: boolean;
                status: 'ok' | 'warning' | 'error';
            };
        };
    };
    permissions: {
        [key: string]: {
            writable: boolean;
            status: 'ok' | 'warning' | 'error';
        };
    };
}
