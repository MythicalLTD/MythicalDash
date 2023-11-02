<?php

namespace MythicalDash;

use MythicalDash\Database\Connect;

class SettingsManager {
    public static function getSetting($settingName) {
        $connect = new Connect();
        $conn = $connect->connectToDatabase();
        $safeSettingName = $conn->real_escape_string($settingName);
        
        $query = "SELECT `$safeSettingName` FROM mythicaldash_settings LIMIT 1";
        $result = $conn->query($query);

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $conn->close();
            return $row[$settingName];
        } else {
            $conn->close();
            return null; 
        }
    }
    public static function updateSetting($settingName, $settingValue) {
        $connect = new Connect();
        $conn = $connect->connectToDatabase();
        $safeSettingName = $conn->real_escape_string($settingName);
        $safeSettingValue = $conn->real_escape_string($settingValue);
    
        $query = "UPDATE mythicaldash_settings SET `$safeSettingName` = '$safeSettingValue'";
    
        if ($conn->query($query)) {
            $conn->close();
            return true; 
        } else {
            $conn->close();
            return false; 
        }
    }
    
}