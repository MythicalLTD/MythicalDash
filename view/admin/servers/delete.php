<?php 
use MythicalDash\NotificationHandler;
use MythicalDash\SettingsManager;
use MythicalDash\Database\Connect;

include (__DIR__ . '/../../requirements/page.php');
include (__DIR__ . '/../../requirements/admin.php');
if (isset($_GET['pid']) && !$_GET['pid'] == "") {
    $sv_id = mysqli_real_escape_string($conn, $_GET['pid']);
    $server_id = Connect::getServerInfo($sv_id, "id");
    $owner_token = Connect::getServerInfo($sv_id, "uid");
    $owner_id = Connect::getUserInfo($owner_token,"id");
    $delete_server = curl_init(SettingsManager::getSetting("PterodactylURL") . "/api/application/servers/" . $sv_id . "/force");
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
        header('location: /admin/servers/list?e=There was an error while deleting your server.');
        $conn->close();
        die();
    }
    NotificationHandler::create($owner_id, $lang['admin_notification_server_title'], str_replace('%placeholder%', $server_id, $lang['admin_notification_server_info']));
    if (mysqli_query($conn, "DELETE FROM mythicaldash_servers WHERE pid = '" . mysqli_real_escape_string($conn, $_GET["pid"]) . "'")) {
        header('location: /admin/servers/list?s=We removed this server!');
        $conn->close();
        die();
    } else {
        header('location: /admin/servers/list?e=There was an error while deleting your server.');
        $conn->close();
        die();
    }
} else {
    header('location: /admin/servers/list?e=You did not provide any server id');
    die();
}
?>