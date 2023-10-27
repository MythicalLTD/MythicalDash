<?php
use MythicalDash\SettingsManager;
include(__DIR__ . '/../../requirements/page.php');
include(__DIR__ . '/../../requirements/admin.php');

function PurgeServers($dbconn)
{
    $dbconn->query("TRUNCATE TABLE `mythicaldash`.`mythicaldash_servers_queue`");
    $query = "SELECT * FROM mythicaldash_servers WHERE mythicaldash_servers.purge = 'true'";
    $resultdb = mysqli_query($dbconn, $query);
    if ($resultdb) {
        while ($row = mysqli_fetch_assoc($resultdb)) {
            $delete_server = curl_init(SettingsManager::getSetting("PterodactylURL") . "/api/application/servers/" . $row['pid'] . "/force");
            curl_setopt($delete_server, CURLOPT_CUSTOMREQUEST, "DELETE");
            $headers = array(
                'Accept: application/json',
                'Content-Type: application/json',
                "Authorization: Bearer " . SettingsManager::getSetting("PterodactylAPIKey")
            );
            curl_setopt($delete_server, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($delete_server, CURLOPT_RETURNTRANSFER, 1);
            $result = curl_exec($delete_server);
            curl_close($delete_server);
            if (!empty($result)) {
                header('location: /admin/settings?e=There was an error while deleting a server.');
                $dbconn->close();
                die();
            }
            if (mysqli_query($dbconn, "DELETE FROM mythicaldash_servers WHERE pid = '" . mysqli_real_escape_string($dbconn, $row['pid']) . "'")) {
                
            } else {
                header('location: /admin/settings?e=There was an error while deleting a server.');
                $dbconn->close();
                die();
            }
        }
    } else {
        $dbconn->close();
        header('location: /admin/settings?e=Database query error');
        die();
    }
}
try {
    PurgeServers($conn);
    header('location: /admin/settings?s=We purged every server that was not active');
    die();
} catch (Exception $ex) {
    header('location: /admin/settings?e=Failed to run the purge');
    die();
}
?>