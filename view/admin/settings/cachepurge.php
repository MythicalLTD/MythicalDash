<?php
use MythicalDash\ErrorHandler;
use MythicalDash\NotificationHandler;
include(__DIR__ . '/../../requirements/page.php');
include(__DIR__ . '/../../requirements/admin.php');

try {
    try {
        try {
            $conn->query("TRUNCATE `mythicaldash_servers_logs`");
            $conn->query("TRUNCATE `mythicaldash_resetpasswords`");
            $conn->query("TRUNCATE `mythicaldash_login_logs`");
            NotificationHandler::deleteAll();
            $message = 'Query executed successfully';
        } catch (Exception $e) {
            $message = $e->getMessage();
        }
        header('location: /admin/settings?s=' . $message);
        die();
    } catch (Exception $e) {
        $message = 'Failed to run SQL script: ' . $e->getMessage();
        header('location: /admin/settings?e=' . $message);
        die();
    }
} catch (Exception $e) {
    ErrorHandler::Critical("MYSQL", "Failed to run SQL script: " . $e->getMessage());
    header('location: /admin/settings?e=Failed to run SQL script: ' . $e->getMessage());
    die();
}
?>