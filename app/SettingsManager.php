<?php

namespace MythicalDash;

use MythicalDash\Database\Connect;

class SettingsManager
{
    /**
     * Get a setting from the settings table
     * 
     * @param string $settingName The value of the database you looking for
     * 
     * @return string|null
     */
    public static function getSetting(string $settingName): string|null
    {
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
    /**
     * Update the settings table
     * 
     * @param string $settingName The name of the colum 
     * @param string $settingValue The new value you want to set
     * 
     * @return bool The status!
     */
    public static function updateSetting(string $settingName, string $settingValue)
    {
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