class Roles {
    /**
     * Get all roles
     *
     * @returns Promise with all roles
     */
    public static async getRoles() {
        const response = await fetch('/api/system/roles', {
            method: 'GET',
            credentials: 'include',
        });
        return await response.json();
    }

    /**
     * Get a specific role by ID
     *
     * @param roleId The ID of the role to get
     * @returns Promise with the role
     */
    public static async getRole(roleId: number) {
        const response = await fetch(`/api/admin/roles/${roleId}`, {
            method: 'GET',
            credentials: 'include',
        });
        return await response.json();
    }

    /**
     * Create a new role
     *
     * @param name The role name
     * @param realName The role display name
     * @param color The role color
     * @returns Promise with the response
     */
    public static async createRole(name: string, realName: string, color: string) {
        const formData = new FormData();
        formData.append('name', name);
        formData.append('real_name', realName);
        formData.append('color', color);

        const response = await fetch('/api/admin/roles/create', {
            method: 'POST',
            body: formData,
            credentials: 'include',
        });
        return await response.json();
    }

    /**
     * Update a role
     *
     * @param id The role ID
     * @param name The role name
     * @param realName The role display name
     * @param color The role color
     * @returns Promise with the response
     */
    public static async updateRole(id: number, name: string, realName: string, color: string) {
        const formData = new FormData();
        formData.append('id', id.toString());
        formData.append('name', name);
        formData.append('real_name', realName);
        formData.append('color', color);

        const response = await fetch('/api/admin/roles/update', {
            method: 'POST',
            body: formData,
            credentials: 'include',
        });
        return await response.json();
    }

    /**
     * Delete a role
     *
     * @param id The role ID
     * @returns Promise with the response
     */
    public static async deleteRole(id: number) {
        const formData = new FormData();
        formData.append('id', id.toString());

        const response = await fetch('/api/admin/roles/delete', {
            method: 'POST',
            body: formData,
            credentials: 'include',
        });
        return await response.json();
    }
}

export default Roles;
