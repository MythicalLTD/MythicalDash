<?php
namespace MythicalDash\Pterodactyl;
use MythicalDash\Pterodactyl\Connection;
class User extends Connection
{
    /**
     * Create a user inside pterodactyl panel
     * 
     * @param string $first_name First name
     * @param string $last_name Last name
     * @param string $username Username
     * @param string $email Email
     * @param string $password Password
     * 
     * @return string
     */
    public static function Create(string $first_name, string $last_name, string $username, string $email, string $password): string
    {
        self::initializeSettings();
        if (self::checkConnection() == true) {
            $url = self::$pterodactyl_url . "/api/application/users";
            $headers = array(
                'Accept: application/json',
                'Content-Type: application/json',
                'Authorization: Bearer ' . self::$pterodactyl_api_key
            );

            $data = array(
                'email' => $email,
                'username' => $username,
                'first_name' => $first_name,
                'last_name' => $last_name,
                'password' => $password
            );

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            $response = curl_exec($ch);
            $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            curl_close($ch);

            if ($statusCode == 201) {
                $responseData = json_decode($response, true);
                return $responseData['attributes']['id'];
            } elseif ($statusCode == 422) {
                $errorResponse = json_decode($response, true);
                $errorMessages = array();
                
                foreach ($errorResponse['errors'] as $error) {
                    $errorMessages[] = $error['detail'];
                }
        
                return implode("|", $errorMessages);
            } else {
                return "Unexpected error: " . $statusCode;
            }
        } else {
            return false;
        }
    }


    /**
     * Delete a user from inside pterodactyl panel!
     */
    public static function Delete(string $id): bool
    {
        return false;
    }
}
?>