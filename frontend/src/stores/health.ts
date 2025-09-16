import { defineStore } from 'pinia';
import { ref } from 'vue';
import type { HealthData, MemoryUsage, DiskSpace, CronData } from '@/types/health';

export const useHealthStore = defineStore('health', () => {
    const healthData = ref<HealthData | null>(null);
    const memoryUsage = ref<MemoryUsage | null>(null);
    const diskSpace = ref<DiskSpace | null>(null);
    const systemStatus = ref<'healthy' | 'warning' | 'unhealthy'>('healthy');
    const cronData = ref<CronData | null>(null);

    const fetchHealthData = async () => {
        try {
            const response = await fetch('/api/admin/health');
            const data = await response.json();
            if (data.success && data.health) {
                healthData.value = data.health;
                memoryUsage.value = data.health.system.memory_usage;
                diskSpace.value = data.health.system.disk_space;
                systemStatus.value = data.health.status;
                cronData.value = data.cron || null;
            }
        } catch (error) {
            console.error('Error fetching health data:', error);
            throw error;
        }
    };

    return {
        healthData,
        memoryUsage,
        diskSpace,
        systemStatus,
        cronData,
        fetchHealthData,
    };
});
