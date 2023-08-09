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
                    "first_name" => $userdb['first_name'],
                    "last_name" => $userdb['last_name'],
                    "avatar" => $userdb['avatar'],
                    "role" => $userdb['role'],
                    "last_login" => $userdb['last_login'],
                    "banned" => $userdb['banned'],
                    "registred_at" => $userdb['registred']
                ),
            );
            $conn->close();
            http_response_code(200);
            die(json_encode($rsp, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        } else {
            http_response_code(404);
            $rsp = array(
                "code" => 404,
                "error" => "We can't find this user in the database."
            );
            $conn->close();
            die(json_encode($rsp, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        }

    } else {
        http_response_code(400);
        $rsp = array(
            "code" => 400,
            "error" => "Email is required, but not provided."
        );
        die(json_encode($rsp, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
} else {
    http_response_code(400);
    $rsp = array(
        "code" => 400,
        "error" => "Email is required, but not provided."
    );
    die(json_encode($rsp, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

?>