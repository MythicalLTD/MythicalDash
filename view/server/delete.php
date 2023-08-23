<?php
include(__DIR__ . '/../requirements/page.php');
if (isset($_GET['server'])) {
    $ownsServer = mysqli_query($conn, "SELECT * FROM mythicaldash_servers WHERE pid = '" . mysqli_real_escape_string($conn, $_GET["server"]) . "' AND uid = '" . mysqli_real_escape_string($conn, $_COOKIE['token']) . "'");
    if ($ownsServer->num_rows == 0) {
        header('location: /dashboard?e=Sorry but you don`t own this server');
        $conn->close();
        die();
    }

    $delete_server = curl_init($settings['PterodactylURL'] . "/api/application/servers/" . $_GET["server"] . "/force");
    curl_setopt($delete_server, CURLOPT_CUSTOMREQUEST, "DELETE");
    $headers = array(
        'Accept: application/json',
        'Content-Type: application/json',
        "Authorization: Bearer " . $settings['PterodactylAPIKey']
    );
    curl_setopt($delete_server, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($delete_server, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($delete_server);
    curl_close($delete_server);
    if (!empty($result)) {
        header('location: /dashboard?e=There was an error while deleting your server.');
        $conn->close();
        die();
    }
    if (mysqli_query($conn, "DELETE FROM mythicaldash_servers WHERE pid = '" . mysqli_real_escape_string($conn, $_GET["server"]) . "'")) {
        header('location: /dashboard?s=Done we deleted your server!');
        $conn->close();
        die();
    } else {
        header('location: /dashboard?e=There was an error while deleting your server.');
        $conn->close();
        die();
    }
} else {
    header('location: /dashboard');
    die();
}
?>