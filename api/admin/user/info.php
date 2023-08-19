<?php
include(__DIR__ . '/../../base.php');
include(__DIR__ . '/../base.php');
if (isset($_GET['email'])) {
    if (!$_GET['email'] == "") {
        $email = mysqli_real_escape_string($conn, $_GET['email']);
        $query = "SELECT * FROM mythicaldash_users WHERE `email` = '$email'";
        $result = mysqli_query($conn, $query);
        if (mysqli_num_rows($result) > 0) {
            $userdb = $conn->query("SELECT * FROM mythicaldash_users WHERE email = '" . $email . "'")->fetch_array();
            $rsp = array(
                "code" => 200,
                "error" => null,
                "info" => array(
                    "database_id" => $userdb['id'],
                    "username" => $userdb['username'],
                    "email" => $userdb['email'],
                    "first_name" => decrypt($userdb['first_name'],$ekey),
                    "last_name" => decrypt($userdb['last_name'],$ekey),
                    "avatar" => $userdb['avatar'],
                    "role" => $userdb['role'],
                    "banned" => $userdb['banned'],
                    "registred_at" => $userdb['registred']
                ),
                "resources" => array(
                    "coins" => $userdb['coins'],
                    "ram" => $userdb['ram'],
                    "disk" => $userdb['disk'],
                    "cpu" => $userdb['cpu'],
                    "server_limit" => $userdb['server_limit'],
                    "ports" => $userdb['ports'],
                    "databases" => $userdb['databases'],
                    "backups" => $userdb['backups'],
                ),
            );
            $conn->close();
            http_response_code(200);
            die(json_encode($rsp, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        } else {
            $rsp = array(
                "code" => 403,
                "error" => "The server understood the request, but it refuses to authorize it.",
                "message" => "We can't find this user in our database!"
            );
            http_response_code(403);
            die(json_encode($rsp, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        }

    } else {
        $rsp = array(
            "code" => 400,
            "error" => "The server cannot understand the request due to a client error.",
            "message" => "Email is required, but not provided."
        );
        http_response_code(400);
        die(json_encode($rsp, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));      
    }
} else {
    $rsp = array(
        "code" => 400,
        "error" => "The server cannot understand the request due to a client error.",
        "message" => "Email is required, but not provided."
    );
    http_response_code(400);
    die(json_encode($rsp, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));   
}

?>