<?php 
use MythicalDash\SettingsManager;
include (__DIR__ . '/../../requirements/page.php');
include (__DIR__ . '/../../requirements/admin.php');
if (isset($_GET['pid']) && !$_GET['pid'] == "") {
    $sv_id = mysqli_real_escape_string($conn, $_GET['pid']);
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
        header('location: /admin/servers?e=There was an error while deleting your server.');
        $conn->close();
        die();
    }
    if (mysqli_query($conn, "DELETE FROM mythicaldash_servers WHERE pid = '" . mysqli_real_escape_string($conn, $_GET["pid"]) . "'")) {
        header('location: /admin/servers?s=We removed this server!');
        $conn->close();
        die();
    } else {
        header('location: /admin/servers?e=There was an error while deleting your server.');
        $conn->close();
        die();
    }
} else {
    header('location: /admin/servers?e=You did not provide any server id');
    die();
}
?>