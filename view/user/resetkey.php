<?php
use MythicalDash\Encryption;
include(__DIR__ . '/../requirements/page.php');

if (isset($_COOKIE['token']) && !$_COOKIE['token'] == "") {
    $user_query = "SELECT * FROM mythicaldash_users WHERE api_key = ?";
    $stmt = mysqli_prepare($conn, $user_query);
    mysqli_stmt_bind_param($stmt, "s", $_COOKIE['token']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if (mysqli_num_rows($result) > 0) {
        $user_info = $conn->query("SELECT * FROM mythicaldash_users WHERE api_key = '" . mysqli_real_escape_string($conn, $_COOKIE['token']) . "'")->fetch_array();
        $email = $user_info['email'];
        $password = $user_info['password'];
        $skey = Encryption::generate_key($email, $password);
        $conn->query("UPDATE `mythicaldash_users` SET `api_key` = '" . mysqli_real_escape_string($conn, $skey) . "' WHERE `mythicaldash_users`.`api_key` = '" . mysqli_real_escape_string($conn, $_COOKIE['token']) . "';");
        $conn->close();
        header('location: /user/profile?s=We updated the user settings in the database');
    } else {
        header('location: /user/profile?e=Can`t find this user in the database');
        die();
    }

} else {
    header('location: /user/profile');
    die();
}
?>