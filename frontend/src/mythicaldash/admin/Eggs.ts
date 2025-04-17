class Eggs {
    /**
     * Get all eggs
     *
     * @returns Promise with all eggs
     */
    public static async getEggs() {
        const response = await fetch('/api/admin/eggs', {
            method: 'GET',
        });
        return await response.json();
    }

    /**
     * Get a specific egg by ID
     *
     * @param id The ID of the egg to get
     * @returns Promise with the egg
     */
    public static async getEgg(id: number) {
        const response = await fetch(`/api/admin/eggs/${id}/info`, {
            method: 'GET',
        });
        return await response.json();
    }

    /**
     * Create a new egg
     *
     * @param name The name of the egg
     * @param description The description of the egg
     * @param categoryId The category ID this egg belongs to
     * @param pterodactylEggId The Pterodactyl egg ID
     * @param enabled Whether the egg is enabled
     *
     * @returns Promise with the response
     */
    public static async createEgg(
        name: string,
        description: string,
        categoryId: number,
        pterodactylEggId: number,
        enabled: string = 'false',
    ) {
        const formData = new FormData();
        formData.append('name', name);
        formData.append('description', description);
        formData.append('category', categoryId.toString());
        formData.append('pterodactyl_egg_id', pterodactylEggId.toString());
        formData.append('enabled', enabled);

        const response = await fetch('/api/admin/eggs/create', {
            method: 'POST',
            body: formData,
        });
        return await response.json();
    }

    /**
     * Update an existing egg
     *
     * @param id The ID of the egg to update
     * @param name The new name of the egg
     * @param description The new description of the egg
     * @param categoryId The new category ID
     * @param pterodactylEggId The new Pterodactyl egg ID
     * @param enabled The new enabled status
     *
     * @returns Promise with the response
     */
    public static async updateEgg(
        id: number,
        name: string,
        description: string,
        categoryId: number,
        pterodactylEggId: number,
        enabled: string = 'false',
    ) {
        const formData = new FormData();
        formData.append('name', name);
        formData.append('description', description);
        formData.append('category', categoryId.toString());
        formData.append('pterodactyl_egg_id', pterodactylEggId.toString());
        formData.append('enabled', enabled);

        const response = await fetch(`/api/admin/eggs/${id}/update`, {
            method: 'POST',
            body: formData,
        });
        return await response.json();
    }

    /**
     * Delete an egg
     *
     * @param id The ID of the egg to delete
     *
     * @returns Promise with the response
     */
    public static async deleteEgg(id: number) {
        const response = await fetch(`/api/admin/eggs/${id}/delete`, {
            method: 'POST',
        });
        return await response.json();
    }

    /**
     * Get eggs by category ID
     *
     * @param categoryId The category ID to get eggs for
     *
     * @returns Promise with the eggs in the category
     */
    public static async getEggsByCategory(categoryId: number) {
        const response = await fetch(`/api/admin/eggs/category/${categoryId}`, {
            method: 'GET',
        });
        return await response.json();
    }
}

export default Eggs;
