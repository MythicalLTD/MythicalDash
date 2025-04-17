/**
 * Redeem module for handling redemption codes in MythicalDash
 */
class Redeem {
    /**
     * Check if a redeem code is valid without using it
     *
     * @param code The redeem code to check
     * @returns Response with information about the code
     */
    public static async checkCode(code: string) {
        const response = await fetch(`/api/user/earn/redeem/check/${code}`, {
            method: 'GET',
        });
        return await response.json();
    }

    /**
     * Redeem a code to gain coins
     *
     * @param code The redeem code to use
     * @returns Response with information about the redemption result
     */
    public static async redeemCode(code: string) {
        const formData = new FormData();
        formData.append('code', code);

        const response = await fetch('/api/user/redeem', {
            method: 'POST',
            body: formData,
        });
        return await response.json();
    }
}

export default Redeem;
