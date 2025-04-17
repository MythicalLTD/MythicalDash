class EggCategories {
    /**
     * Get all egg categories
     *
     * @returns Promise with the egg categories
     */
    public static async getCategories() {
        const response = await fetch('/api/admin/egg-categories', {
            method: 'GET',
        });
        return await response.json();
    }

    /**
     * Get all Pterodactyl nests
     *
     * @returns Promise with the Pterodactyl nests
     */
    public static async getPterodactylNests() {
        const response = await fetch('/api/admin/egg-categories/pterodactyl-nests', {
            method: 'GET',
        });
        return await response.json();
    }

    /**
     * Get all Pterodactyl eggs for a specific nest
     *
     * @param nestId The ID of the nest to get eggs for
     * @returns Promise with the Pterodactyl eggs
     */
    public static async getPterodactylEggs(nestId: number) {
        const response = await fetch(`/api/admin/egg-categories/pterodactyl-nests/${nestId}/eggs`, {
            method: 'GET',
        });
        return await response.json();
    }

    /**
     * Get all Pterodactyl eggs
     *
     * @returns Promise with all Pterodactyl eggs
     */
    public static async getAllPterodactylEggs() {
        const response = await fetch('/api/admin/egg-categories/pterodactyl-eggs', {
            method: 'GET',
        });
        return await response.json();
    }

    /**
     * Get a specific Pterodactyl egg by ID
     *
     * @param eggId The ID of the egg to get
     * @returns Promise with the Pterodactyl egg
     */
    public static async getPterodactylEgg(eggId: number) {
        const response = await fetch(`/api/admin/egg-categories/pterodactyl-eggs/${eggId}`, {
            method: 'GET',
        });
        return await response.json();
    }

    /**
     * Create a new egg category
     *
     * @param name The name of the category
     * @param description The description of the category
     * @param pterodactylNestId The Pterodactyl nest ID
     * @param enabled Whether the category is enabled
     * @param locked Whether the category is locked
     *
     * @returns Promise with the response
     */
    public static async createCategory(
        name: string,
        description: string,
        pterodactylNestId: number,
        enabled: string = 'true',
    ) {
        const formData = new FormData();
        formData.append('name', name);
        formData.append('description', description);
        formData.append('pterodactyl_nest_id', pterodactylNestId.toString());
        formData.append('enabled', enabled.toString());

        const response = await fetch('/api/admin/egg-categories/create', {
            method: 'POST',
            body: formData,
        });
        return await response.json();
    }

    /**
     * Update an existing egg category
     *
     * @param id The ID of the category to update
     * @param name The new name of the category
     * @param description The new description of the category
     * @param pterodactylNestId The new Pterodactyl nest ID
     * @param enabled The new enabled status
     * @param locked The new locked status
     *
     * @returns Promise with the response
     */
    public static async updateCategory(
        id: number,
        name: string,
        description: string,
        pterodactylNestId: number,
        enabled: boolean = true,
        locked: boolean = false,
    ) {
        const formData = new FormData();
        formData.append('name', name);
        formData.append('description', description);
        formData.append('pterodactyl_nest_id', pterodactylNestId.toString());
        formData.append('enabled', enabled.toString());
        formData.append('locked', locked.toString());

        const response = await fetch(`/api/admin/egg-categories/${id}/update`, {
            method: 'POST',
            body: formData,
        });
        return await response.json();
    }

    /**
     * Delete an egg category
     *
     * @param id The ID of the category to delete
     *
     * @returns Promise with the response
     */
    public static async deleteCategory(id: number) {
        const response = await fetch(`/api/admin/egg-categories/${id}/delete`, {
            method: 'POST',
        });
        return await response.json();
    }

    /**
     * Associate eggs with a category
     *
     * @param categoryId The ID of the category
     * @param eggIds Array of egg IDs to associate
     *
     * @returns Promise with the response
     */
    public static async associateEggs(categoryId: number, eggIds: number[]) {
        const formData = new FormData();
        formData.append('category_id', categoryId.toString());
        eggIds.forEach((eggId, index) => {
            formData.append(`egg_ids[${index}]`, eggId.toString());
        });

        const response = await fetch(`/api/admin/egg-categories/${categoryId}/eggs/associate`, {
            method: 'POST',
            body: formData,
        });
        return await response.json();
    }

    /**
     * Disassociate eggs from a category
     *
     * @param categoryId The ID of the category
     * @param eggIds Array of egg IDs to disassociate
     *
     * @returns Promise with the response
     */
    public static async disassociateEggs(categoryId: number, eggIds: number[]) {
        const formData = new FormData();
        formData.append('category_id', categoryId.toString());
        eggIds.forEach((eggId, index) => {
            formData.append(`egg_ids[${index}]`, eggId.toString());
        });

        const response = await fetch(`/api/admin/egg-categories/${categoryId}/eggs/disassociate`, {
            method: 'POST',
            body: formData,
        });
        return await response.json();
    }

    /**
     * Get all eggs associated with a category
     *
     * @param categoryId The ID of the category
     *
     * @returns Promise with the response
     */
    public static async getCategoryEggs(categoryId: number) {
        const response = await fetch(`/api/admin/egg-categories/${categoryId}/eggs`, {
            method: 'GET',
        });
        return await response.json();
    }

    /**
     * Get all categories with their associated eggs
     *
     * @returns Promise with the response
     */
    public static async getCategoriesWithEggs() {
        const response = await fetch('/api/admin/egg-categories/with-eggs', {
            method: 'GET',
        });
        return await response.json();
    }
}

export default EggCategories;
