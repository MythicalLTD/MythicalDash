<?php

namespace MythicalDash;

use MythicalDash\Database\Connect;

class EggManagerConfig
{
    public static function getConfig($settingName)
    {
        $connect = new Connect();
        $conn = $connect->connectToDatabase();
        $sql = "SELECT setting_value FROM mythicaldash_eggs_config WHERE setting_name = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $settingName);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            return $row["setting_value"];
        }
        return null;
    }

    public static function updateConfig($settingName, $settingValue)
    {
        $connect = new Connect();
        $conn = $connect->connectToDatabase();
        $sql = "INSERT INTO mythicaldash_eggs_config (setting_name, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $settingName, $settingValue);
        return $stmt->execute();
    }

    public static function deleteConfig($settingName)
    {
        $connect = new Connect();
        $conn = $connect->connectToDatabase();
        $sql = "DELETE FROM mythicaldash_eggs_config WHERE setting_name = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $settingName);
        return $stmt->execute();
    }

    public static function addConfig($settingName, $settingValue)
    {
        $connect = new Connect();
        $conn = $connect->connectToDatabase();
        $sql = "INSERT INTO mythicaldash_eggs_config (setting_name, setting_value) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $settingName, $settingValue);
        return $stmt->execute();
    }
}
?>