declare module '@/stores/health' {
    import { HealthData, MemoryUsage, DiskSpace } from '@/types/health';

    interface HealthStore {
        healthData: HealthData | null;
        memoryUsage: MemoryUsage | null;
        diskSpace: DiskSpace | null;
        systemStatus: 'healthy' | 'warning' | 'unhealthy';
        fetchHealthData: () => Promise<void>;
    }

    export function useHealthStore(): HealthStore;
}
