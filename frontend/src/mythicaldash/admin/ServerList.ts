class ServerList {
    public static async getList(page: number = 1, limit: number = 20) {
        const params = new URLSearchParams({ page: String(page), limit: String(limit) });
        const response = await fetch(`/api/admin/servers/list?${params.toString()}`, {
            method: 'GET',
        });
        return await response.json();
    }

    public static async toggleSuspend(id: number) {
        const response = await fetch(`/api/admin/servers/toggle-suspend/${id}`, {
            method: 'POST',
        });
        return await response.json();
    }

    public static async deleteServer(id: number) {
        const response = await fetch(`/api/admin/servers/delete/${id}`, {
            method: 'POST',
        });
        return await response.json();
    }
}

export default ServerList;
