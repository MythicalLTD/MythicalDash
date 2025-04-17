class Locations {
    /**
     * Get the dashboard data
     *
     * @returns
     */
    public static async getPterodactylLocations() {
        const response = await fetch('/api/admin/locations/pterodactyl', {
            method: 'GET',
        });
        return await response.json();
    }

    /**
     * Create a new location
     *
     * @param name The name of the location
     * @param description The description of the location
     * @param pterodactylLocationId The Pterodactyl location ID
     * @param nodeIp The IP address of the node
     * @param status The status of the location
     * @param slots The number of slots available for the location
     *
     * @returns The response from the API
     */
    public static async createLocation(
        name: string,
        description: string,
        pterodactylLocationId: number,
        nodeIp: string,
        status: string,
        slots: number,
    ) {
        const formData = new FormData();
        formData.append('name', name);
        formData.append('description', description);
        formData.append('pterodactyl_location_id', pterodactylLocationId.toString());
        formData.append('node_ip', nodeIp);
        formData.append('status', status);
        formData.append('slots', slots.toString());

        const response = await fetch('/api/admin/locations/create', {
            method: 'POST',
            body: formData,
        });
        return await response.json();
    }
}

export default Locations;
