declare module '@/stores/health' {
    import { HealthData, MemoryUsage, DiskSpace } from '@/types/health';

    interface HealthStore {
        cronData:
            | import('/var/www/mythicaldash-v3/frontend/src/types/health').CronData
            | {
                  recent: {
                      id: number;
                      task_name: string;
                      last_run_at: string | null;
                      last_run_success: boolean;
                      last_run_message: string | null;
                      expected_interval_seconds: number;
                      late: boolean;
                  }[];
                  summary: string | null;
              }
            | null;
        healthData: HealthData | null;
        memoryUsage: MemoryUsage | null;
        diskSpace: DiskSpace | null;
        systemStatus: 'healthy' | 'warning' | 'unhealthy';
        fetchHealthData: () => Promise<void>;
    }

    export function useHealthStore(): HealthStore;
}
