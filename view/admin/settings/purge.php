<?php
include(__DIR__ . '/../../requirements/page.php');
include(__DIR__ . '/../../requirements/admin.php');
try {
    if (isset($_GET['update_settings'])) {
        $enable_purge = mysqli_real_escape_string($conn, $_GET['purge:enabled']);
        if ($enable_purge == "true") {
            $conn->query("UPDATE mythicaldash_servers SET `purge` = 'true'");
        } else {
            $conn->query("UPDATE mythicaldash_servers SET `purge` = 'false'");
        }
        mysqli_query($conn, "UPDATE `mythicaldash_settings` SET `server_purge` = '" . $enable_purge . "' WHERE `mythicaldash_settings`.`id` = 1;");
        
        $conn->close();
        header('location: /admin/settings?s=We updated the settings inside the database');
        die();
    } else {
        header('location: /admin/settings');
        die();
    }
} catch (Exception $ex) {
    header('location: /admin/settings?e=Failed to update the settings inside the database');
    die();
}

?>