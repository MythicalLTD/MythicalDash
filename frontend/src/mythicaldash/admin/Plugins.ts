class Plugins {
    public static async getList() {
        const response = await fetch('/api/admin/plugins/list', {
            method: 'GET',
        });
        return await response.json();
    }

    public static async getConfig(identifier: string) {
        const response = await fetch(`/api/admin/plugins/${identifier}/config`, {
            method: 'GET',
        });
        return await response.json();
    }

    public static async setSetting(identifier: string, key: string, value: string) {
        const formData = new FormData();
        formData.append('key', key);
        formData.append('value', value);

        const response = await fetch(`/api/admin/plugins/${identifier}/settings/set`, {
            method: 'POST',
            body: formData,
        });
        return await response.json();
    }

    public static async removeSetting(identifier: string, key: string) {
        const formData = new FormData();
        formData.append('key', key);

        const response = await fetch(`/api/admin/plugins/${identifier}/settings/remove`, {
            method: 'POST',
            body: formData,
        });
        return await response.json();
    }
}

export default Plugins;
