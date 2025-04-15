class Plugins {
    public static async getList() {
        const response = await fetch('/api/admin/plugins/list', {
            method: 'GET',
        });
        return await response.json();
    }
}

export default Plugins;
