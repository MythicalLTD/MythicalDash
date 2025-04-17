class Redeem {
    /**
     * Get all redeem codes
     *
     * @returns Promise with all redeem codes
     */
    public static async getRedeemCodes() {
        const response = await fetch('/api/admin/redeem/codes', {
            method: 'GET',
        });
        return await response.json();
    }

    /**
     * Get a specific redeem code
     *
     * @param codeId The ID of the redeem code
     * @returns Promise with the redeem code
     */
    public static async getRedeemCode(codeId: number) {
        const response = await fetch(`/api/admin/redeem/code/${codeId}`, {
            method: 'GET',
        });
        return await response.json();
    }

    /**
     * Create a new redeem code
     *
     * @param code The redeem code string
     * @param coins Number of coins to award
     * @param uses Number of times code can be used
     * @param enabled Whether the code is enabled
     * @returns Promise with the response
     */
    public static async createRedeemCode(code: string, coins: number, uses: number = 1, enabled: boolean = false) {
        const formData = new FormData();
        formData.append('code', code);
        formData.append('coins', coins.toString());
        formData.append('uses', uses.toString());
        formData.append('enabled', enabled ? 'true' : 'false');

        const response = await fetch('/api/admin/redeem/code/create', {
            method: 'POST',
            body: formData,
        });
        return await response.json();
    }

    /**
     * Update an existing redeem code
     *
     * @param codeId The ID of the redeem code to update
     * @param code The new redeem code string
     * @param coins New number of coins to award
     * @param uses New number of times code can be used
     * @param enabled New enabled status
     * @returns Promise with the response
     */
    public static async updateRedeemCode(
        codeId: number,
        code: string,
        coins: number,
        uses: number = 1,
        enabled: boolean = false,
    ) {
        const formData = new FormData();
        formData.append('code', code);
        formData.append('coins', coins.toString());
        formData.append('uses', uses.toString());
        formData.append('enabled', enabled ? 'true' : 'false');

        const response = await fetch(`/api/admin/redeem/code/${codeId}/update`, {
            method: 'POST',
            body: formData,
        });
        return await response.json();
    }

    /**
     * Delete a redeem code
     *
     * @param codeId The ID of the redeem code to delete
     * @returns Promise with the response
     */
    public static async deleteRedeemCode(codeId: number) {
        const response = await fetch(`/api/admin/redeem/code/${codeId}/delete`, {
            method: 'POST',
        });
        return await response.json();
    }
}

export default Redeem;
