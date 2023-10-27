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
}