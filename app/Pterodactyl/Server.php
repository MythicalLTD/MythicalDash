<?php
namespace MythicalDash\Pterodactyl;

class Server extends Connection
{
    /**
     * Check if the server exists in Pterodactyl
     * 
     * @param int $serverIdentifier The server id
     * 
     * @return bool The if yes false if no!
     */
    public static function checkServerExists(int $serverId) {
        $url = self::$pterodactyl_url."/api/application/servers/{$serverId}";
    
        $headers = [
            'Accept: application/json',
            'Content-Type: application/json',
            "Authorization: Bearer ".self::$pterodactyl_api_key
        ];
    
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
        $response = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
        curl_close($ch);
    
        if ($statusCode == 200) {
            $responseData = json_decode($response, true);
            if (isset($responseData['object']) && $responseData['object'] == 'server') {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
    
}
?>