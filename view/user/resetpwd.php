<?php
include(__DIR__ . '/../requirements/page.php');

if (isset($_COOKIE['token']))
{ 
    if (!$_COOKIE['token'] == "") {
        $user_query = "SELECT * FROM mythicaldash_users WHERE api_key = ?";
        $stmt = mysqli_prepare($conn, $user_query);
        mysqli_stmt_bind_param($stmt, "s", $_COOKIE['token']);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if (mysqli_num_rows($result) > 0) {
            $user_info = $conn->query("SELECT * FROM mythicaldash_users WHERE api_key = '" . $_COOKIE['token'] . "'")->fetch_array();
            $upassword = mysqli_real_escape_string($conn,$_GET['pwd']);
            $pwd = password_hash($upassword,PASSWORD_DEFAULT);
            $conn->query("UPDATE `mythicaldash_users` SET `password` = '" . $pwd . "' WHERE `mythicaldash_users`.`api_key` = '" . $_COOKIE['token'] . "';");
            $conn->close();
            header('location: /user/profile?s=We updated the user settings in the database');
        } else {
            header('location: /user/profile?e=Can`t find this user in the database');
            exit();
        }
    } else {
        header('location: /user/profile?e=Can`t find this user in the database');
        exit();
    }
} else {
    header('location: /user/profile');
    exit();
}
?>