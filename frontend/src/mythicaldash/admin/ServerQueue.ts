class ServerQueue {
    /**
     * Get all server queue items
     *
     * @returns Promise with all server queue items
     */
    public static async getServerQueue() {
        const response = await fetch('/api/admin/server-queue', {
            method: 'GET',
        });
        return await response.json();
    }

    /**
     * Get a specific server queue item by ID
     *
     * @param id The ID of the server queue item to get
     * @returns Promise with the server queue item
     */
    public static async getServerQueueItem(id: number) {
        const response = await fetch(`/api/admin/server-queue/${id}`, {
            method: 'GET',
        });
        return await response.json();
    }

    /**
     * Create a new server queue item
     *
     * @param name The name of the server
     * @param description The description of the server
     * @param ram The RAM allocation in MB
     * @param disk The disk allocation in MB
     * @param cpu The CPU allocation percentage
     * @param ports The number of ports
     * @param databases The number of databases
     * @param backups The number of backups
     * @param location The location ID
     * @param user The user ID
     * @param nest The nest ID
     * @param egg The egg ID
     *
     * @returns Promise with the response
     */
    public static async createServerQueueItem(
        name: string,
        description: string,
        ram: number,
        disk: number,
        cpu: number,
        ports: number,
        databases: number,
        backups: number,
        location: number,
        user: string,
        nest: number,
        egg: number,
    ) {
        const formData = new FormData();
        formData.append('name', name);
        formData.append('description', description);
        formData.append('ram', ram.toString());
        formData.append('disk', disk.toString());
        formData.append('cpu', cpu.toString());
        formData.append('ports', ports.toString());
        formData.append('databases', databases.toString());
        formData.append('backups', backups.toString());
        formData.append('location', location.toString());
        formData.append('user', user.toString());
        formData.append('nest', nest.toString());
        formData.append('egg', egg.toString());

        const response = await fetch('/api/admin/server-queue/create', {
            method: 'POST',
            body: formData,
        });
        return await response.json();
    }

    /**
     * Update a server queue item's status
     *
     * @param id The ID of the server queue item to update
     * @param status The new status (pending, building, failed)
     *
     * @returns Promise with the response
     */
    public static async updateServerQueueItemStatus(id: number, status: 'pending' | 'building' | 'failed') {
        const formData = new FormData();
        formData.append('status', status);

        const response = await fetch(`/api/admin/server-queue/${id}/update-status`, {
            method: 'POST',
            body: formData,
        });
        return await response.json();
    }

    /**
     * Delete a server queue item
     *
     * @param id The ID of the server queue item to delete
     *
     * @returns Promise with the response
     */
    public static async deleteServerQueueItem(id: number) {
        const response = await fetch(`/api/admin/server-queue/${id}/delete`, {
            method: 'POST',
        });
        return await response.json();
    }

    /**
     * Process a server queue item (start building)
     *
     * @param id The ID of the server queue item to process
     *
     * @returns Promise with the response
     */
    public static async processServerQueueItem(id: number) {
        const response = await fetch(`/api/admin/server-queue/${id}/process`, {
            method: 'POST',
        });
        return await response.json();
    }

    /**
     * Get server queue statistics
     *
     * @returns Promise with the statistics
     */
    public static async getServerQueueStats() {
        const response = await fetch('/api/admin/server-queue/stats', {
            method: 'GET',
        });
        return await response.json();
    }

    /**
     * Get server queue logs
     *
     * @returns Promise with the logs
     */
    public static async getServerQueueLogs() {
        const response = await fetch('/api/admin/server-queue/logs', {
            method: 'GET',
        });
        return await response.json();
    }
}

export default ServerQueue;
