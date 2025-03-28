class Servers {
    public static async getPterodactylServers() {
        const response = await fetch('/api/user/session/servers', {
            method: 'GET',
        });
        const data = await response.json();
        return data.servers;
    }

    public static async getPterodactylResources() {
        const response = await fetch('/api/user/session/pterodactyl/resources', {
            method: 'GET',
        });
        const data = await response.json();
        return data.resources;
    }
}

export default Servers;
