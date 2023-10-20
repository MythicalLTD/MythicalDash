<?php
include(__DIR__ . '/../../requirements/page.php');
include(__DIR__ . '/../../requirements/admin.php');

if (isset($_GET['id']) && !$_GET['id'] == "") {
    $user_query = "SELECT * FROM mythicaldash_users WHERE id = ?";
    $stmt = mysqli_prepare($conn, $user_query);
    mysqli_stmt_bind_param($stmt, "s", $_GET['id']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if (mysqli_num_rows($result) > 0) {
        //header('location: /admin/users?e=This function is disabled please wait for a update');
        //$conn->close();
        //die();
        $user_info = $conn->query("SELECT * FROM mythicaldash_users WHERE id = '" . $_GET['id'] . "'")->fetch_array();
        deleteUserServers($conn, $user_info['api_key'], $settings['PterodactylURL'], $settings['PterodactylAPIKey']);
        //if ($user_info['api_key'] == $_COOKIE['token']) {
        //    header('location: /admin/users?e=Can`t delete your own account');
        //    die();
        //}
        //$conn->query('DELETE FROM `mythicaldash_users` WHERE `mythicaldash_users`.`id` = '.$_GET['id'].';');
        //$conn->close();
        //header('location: /admin/users?s=We updated the user settings in the database');
        //die();
    } else {
        header('location: /admin/users?e=Can`t find this user in the database');
        $conn->close();
        die();
    }
} else {
    header('location: /admin/users');
    die();
}



function deleteUsersInQueue() {
    
}

function deleteUserServers($dbconn, $userkey, $panel_url, $panel_apikey) {
    $query = "SELECT pid FROM mythicaldash_servers WHERE mythicaldash_servers.uid='".$userkey."'";
    $result = mysqli_query($dbconn, $query);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $panel_id = $row['pid'];
            $delete_server = curl_init($panel_url. "/api/application/servers/" . $panel_id . "/force");
            curl_setopt($delete_server, CURLOPT_CUSTOMREQUEST, "DELETE");
            $headers = array(
                'Accept: application/json',
                'Content-Type: application/json',
                "Authorization: Bearer " . $panel_apikey
            );
            curl_setopt($delete_server, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($delete_server, CURLOPT_RETURNTRANSFER, 1);
            $result = curl_exec($delete_server);
            curl_close($delete_server);
            if (!empty($result)) {
                $dbconn->close();
                header('location: /admin/users?e=Failed to remove server from panel');
                die();
            }
            if (mysqli_query($dbconn, "DELETE FROM mythicaldash_servers WHERE pid = '" . mysqli_real_escape_string($dbconn, $panel_id) . "'")) {
                $dbconn->close();
            } else {
                $dbconn->close();
                header('location: /admin/users?e=Failed to remove server from database');
                die();
            }
        }
        mysqli_free_result($result);
    }
}
?>