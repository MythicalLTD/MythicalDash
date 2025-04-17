class ServerList {
    public static async getList() {
        const response = await fetch('/api/admin/servers/list', {
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
