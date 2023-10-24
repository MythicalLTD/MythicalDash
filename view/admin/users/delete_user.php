<?php
use MythicalDash\SettingsManager;
include(__DIR__ . '/../../requirements/page.php');
include(__DIR__ . '/../../requirements/admin.php');

if (isset($_GET['id']) && !$_GET['id'] == "") {
    $user_query = "SELECT * FROM mythicaldash_users WHERE id = ?";
    $stmt = mysqli_prepare($conn, $user_query);
    mysqli_stmt_bind_param($stmt, "s", $_GET['id']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if (mysqli_num_rows($result) > 0) {
        $user_info = $conn->query("SELECT * FROM mythicaldash_users WHERE id = '" . $_GET['id'] . "'")->fetch_array();
        deleteUserServers($conn, $user_info['api_key'], SettingsManager::getSetting("PterodactylURL"), SettingsManager::getSetting("PterodactylAPIKey"));
        deleteUserServersInQueue($conn, $user_info['api_key'], SettingsManager::getSetting("PterodactylURL"), SettingsManager::getSetting("PterodactylAPIKey"));
        deleteApiKeys($conn, $user_info['api_key']);
        deleteLoginLogs($conn, $user_info['api_key']);
        deleteTickets($conn, $user_info['api_key']);
        deleteTicketsMsgs($conn, $user_info['api_key']);
        deletePasswordsReset($conn, $user_info['api_key']);
        deleteUserFromPterodactyl(SettingsManager::getSetting("PterodactylURL"), $user_info['panel_id'], SettingsManager::getSetting("PterodactylAPIKey"));
        deleteUserFromDb($conn, $user_info['api_key']);
        header('location: /admin/users?s=We removed the user');
        $conn->close();
        die();
    } else {
        header('location: /admin/users?e=Can`t find this user in the database');
        $conn->close();
        die();
    }
} else {
    header('location: /admin/users');
    die();
}


function deleteUserFromDb($dbconn, $userkey)
{
    $query = "SELECT * FROM mythicaldash_users WHERE mythicaldash_users.api_key='" . $userkey . "'";
    $result = mysqli_query($dbconn, $query);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $key = $row["id"];
            if (mysqli_query($dbconn, "DELETE FROM mythicaldash_users WHERE id = '" . mysqli_real_escape_string($dbconn, $key) . "'")) {

            } else {
                $dbconn->close();
                header('location: /admin/users?e=Failed to remove from database');
                die();
            }
        }
    } else {
        $dbconn->close();
        header('location: /admin/users?e=Database query error');
        die();
    }
}

function deleteUserFromPterodactyl($panel_url, $user_id, $api_key)
{
    $url = $panel_url . "/api/application/users/" . $user_id;
    $ch = curl_init($url);
    $headers = array(
        'Accept: application/json',
        'Content-Type: application/json',
        'Authorization: Bearer ' . $api_key
    );
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
    $response = curl_exec($ch);
    curl_close($ch);

    if ($response === false) {
        header('location: /admin/users?e=Failed to remove from pterodactyl');
        die();
    } else {

    }
}

function deleteTickets($dbconn, $userkey)
{
    $query = "SELECT * FROM mythicaldash_tickets WHERE mythicaldash_tickets.ownerkey='" . $userkey . "'";
    $result = mysqli_query($dbconn, $query);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $key = $row["id"];
            $ticketuuid = $row['ticketuuid'];
            if (mysqli_query($dbconn, "DELETE FROM mythicaldash_tickets WHERE id = '" . mysqli_real_escape_string($dbconn, $key) . "'")) {
                $query_t = "SELECT * FROM mythicaldash_tickets_messages WHERE mythicaldash_tickets_messages.ticketuuid='" . $ticketuuid . "'";
                $result_t = mysqli_query($dbconn, $query_t);
                if ($result_t) {
                    while ($row_t = mysqli_fetch_assoc($result_t)) {
                        $key = $row["id"];
                        if (mysqli_query($dbconn, "DELETE FROM mythicaldash_tickets_messages WHERE id = '" . mysqli_real_escape_string($dbconn, $key) . "'")) {

                        } else {
                            $dbconn->close();
                            header('location: /admin/users?e=Failed to remove from database');
                            die();
                        }
                    }
                } else {
                    $dbconn->close();
                    header('location: /admin/users?e=Database query error');
                    die();
                }
            } else {
                $dbconn->close();
                header('location: /admin/users?e=Failed to remove from database');
                die();
            }
        }
    } else {
        $dbconn->close();
        header('location: /admin/users?e=Database query error');
        die();
    }
}

function deleteTicketsMsgs($dbconn, $userkey)
{
    $query = "SELECT * FROM mythicaldash_tickets_messages WHERE mythicaldash_tickets_messages.userkey='" . $userkey . "'";
    $result = mysqli_query($dbconn, $query);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $key = $row["id"];
            if (mysqli_query($dbconn, "DELETE FROM mythicaldash_tickets_messages WHERE id = '" . mysqli_real_escape_string($dbconn, $key) . "'")) {

            } else {
                $dbconn->close();
                header('location: /admin/users?e=Failed to remove from database');
                die();
            }
        }
    } else {
        $dbconn->close();
        header('location: /admin/users?e=Database query error');
        die();
    }
}

function deletePasswordsReset($dbconn, $userkey)
{
    $query = "SELECT * FROM mythicaldash_resetpasswords WHERE mythicaldash_resetpasswords.ownerkey='" . $userkey . "'";
    $result = mysqli_query($dbconn, $query);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $key = $row["id"];
            if (mysqli_query($dbconn, "DELETE FROM mythicaldash_resetpasswords WHERE id = '" . mysqli_real_escape_string($dbconn, $key) . "'")) {

            } else {
                $dbconn->close();
                header('location: /admin/users?e=Failed to remove from database');
                die();
            }
        }
    } else {
        $dbconn->close();
        header('location: /admin/users?e=Database query error');
        die();
    }
}

function deleteApiKeys($dbconn, $userkey)
{
    $query = "SELECT * FROM mythicaldash_apikeys WHERE mythicaldash_apikeys.ownerkey='" . $userkey . "'";
    $result = mysqli_query($dbconn, $query);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $key = $row["id"];
            if (mysqli_query($dbconn, "DELETE FROM mythicaldash_apikeys WHERE id = '" . mysqli_real_escape_string($dbconn, $key) . "'")) {

            } else {
                $dbconn->close();
                header('location: /admin/users?e=Failed to remove from database');
                die();
            }
        }
    } else {
        $dbconn->close();
        header('location: /admin/users?e=Database query error');
        die();
    }
}

function deleteLoginLogs($dbconn, $userkey)
{
    $query = "SELECT * FROM mythicaldash_login_logs WHERE mythicaldash_login_logs.userkey='" . $userkey . "'";
    $result = mysqli_query($dbconn, $query);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $key = $row["id"];
            if (mysqli_query($dbconn, "DELETE FROM mythicaldash_login_logs WHERE id = '" . mysqli_real_escape_string($dbconn, $key) . "'")) {

            } else {
                $dbconn->close();
                header('location: /admin/users?e=Failed to remove from database');
                die();
            }
        }
    } else {
        $dbconn->close();
        header('location: /admin/users?e=Database query error');
        die();
    }
}

function deleteUserServersInQueue($dbconn, $userkey, $panel_url, $panel_apikey)
{
    $query = "SELECT * FROM mythicaldash_servers_queue WHERE mythicaldash_servers_queue.ownerid='" . $userkey . "'";
    $result = mysqli_query($dbconn, $query);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $svid = $row["id"];
            if (mysqli_query($dbconn, "DELETE FROM mythicaldash_servers_queue WHERE id = '" . mysqli_real_escape_string($dbconn, $svid) . "'")) {

            } else {
                $dbconn->close();
                header('location: /admin/users?e=Failed to remove from database');
                die();
            }
        }
    } else {
        $dbconn->close();
        header('location: /admin/users?e=Database query error');
        die();
    }
}

function deleteUserServers($dbconn, $userkey, $panel_url, $panel_apikey)
{
    $query = "SELECT * FROM mythicaldash_servers WHERE mythicaldash_servers.uid='" . $userkey . "'";
    $result = mysqli_query($dbconn, $query);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $panel_id = $row['pid'];
            $delete_server = curl_init($panel_url . "/api/application/servers/" . $panel_id . "/force");
            curl_setopt($delete_server, CURLOPT_CUSTOMREQUEST, "DELETE");
            $headers = array(
                'Accept: application/json',
                'Content-Type: application/json',
                "Authorization: Bearer " . $panel_apikey
            );
            curl_setopt($delete_server, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($delete_server, CURLOPT_RETURNTRANSFER, 1);
            $curl_result = curl_exec($delete_server);
            curl_close($delete_server);
            if (!empty($curl_result)) {
                $dbconn->close();
                header('location: /admin/users?e=Failed to remove from panel');
                die();
            }
            if (mysqli_query($dbconn, "DELETE FROM mythicaldash_servers WHERE pid = '" . mysqli_real_escape_string($dbconn, $panel_id) . "'")) {

            } else {
                $dbconn->close();
                header('location: /admin/users?e=Failed to remove from database');
                die();
            }
        }
        mysqli_free_result($result);
    } else {
        $dbconn->close();
        header('location: /admin/users?e=Database query error');
        die();
    }
}
?>