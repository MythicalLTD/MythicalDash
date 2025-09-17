class Users {
    /**
     * Get all users with pagination and optional search
     */
    public static async getUsers(page: number = 1, limit: number = 20, search: string = '') {
        const params = new URLSearchParams({ page: String(page), limit: String(limit) });
        if (search && search.trim()) params.set('search', search.trim());
        const response = await fetch(`/api/admin/users?${params.toString()}`, {
            method: 'GET',
        });
        return await response.json();
    }

    /**
     * Get a specific user by ID
     *
     * @param userId The UUID of the user to get
     * @returns Promise with the user
     */
    public static async getUser(userId: string) {
        const response = await fetch(`/api/admin/user/${userId}/info`, {
            method: 'GET',
        });
        return await response.json();
    }

    /**
     * Update a user's information
     *
     * @param userId The UUID of the user to update
     * @param column The column to update
     * @param value The new value
     * @param encrypted Whether the value should be encrypted
     *
     * @returns Promise with the response
     */
    public static async updateUser(userId: string, column: string, value: string) {
        const formData = new FormData();
        formData.append('column', column);
        formData.append('value', value);

        const response = await fetch(`/api/admin/user/${userId}/update`, {
            method: 'POST',
            body: formData,
        });
        return await response.json();
    }

    /**
     * Delete a user
     *
     * @param userId The UUID of the user to delete
     *
     * @returns Promise with the response
     */
    public static async deleteUser(userId: string) {
        const response = await fetch(`/api/admin/user/${userId}/delete`, {
            method: 'POST',
        });
        return await response.json();
    }
}

export default Users;
