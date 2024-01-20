<?php
namespace MythicalDash;

use Symfony\Component\Yaml\Yaml;
use MythicalDash\SettingsManager;
class Main
{
    public static function isHTTPS()
    {
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            return true;
        }
        return false;
    }
    public static function generatePassword($length = 12) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_-=+;:,.?";
        $password = "";
        
        $charArrayLength = strlen($chars) - 1;
        
        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[mt_rand(0, $charArrayLength)];
        }
        
        return $password;
    }
    public static function getAppUrl() {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'];
        $url = $protocol . $host . $_SERVER['REQUEST_URI'];
        return $url;
    }

    /**
     * Get info from our github api
     * 
     * @return string The json file
     */
    public static function getLatestReleaseInfo() {
        $cacheFile = '../caches/github.json';
        $cacheDuration = 15 * 60;
    
        if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheDuration) {
            $cachedData = file_get_contents($cacheFile);
            return json_decode($cachedData, true);
        } else {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://api.github.com/repos/mythicalltd/mythicaldash/releases/latest");
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['User-Agent: MythicalDash']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $response = curl_exec($ch);
            curl_close($ch);
    
            file_put_contents($cacheFile, $response);
    
            return json_decode($response, true);
        }
    }   

    public static function getLang()
    {
        $langConfig = SettingsManager::getSetting('lang');

        if ($langConfig == null) {
            self::handleLanguageError("Failed to start the dash. Please use a valid language file.");
        } else {
            $langFilePath = __DIR__ . '/../lang/' . $langConfig . '.php';

            if (file_exists($langFilePath)) {
                return include($langFilePath);
            } else {
                self::handleLanguageError("Failed to start the dash. Please use a valid language file.");
            }
        }
    }
    private static function handleLanguageError($errorMessage)
    {
        ErrorHandler::ShowCritical($errorMessage);
        die();
    }
}
?>