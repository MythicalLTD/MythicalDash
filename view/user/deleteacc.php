<?php
include(__DIR__ . '/../requirements/page.php');

if (isset($_COOKIE['token']) && !$_COOKIE['token'] == "") {
    $user_query = "SELECT * FROM mythicaldash_users WHERE api_key = ?";
    $stmt = mysqli_prepare($conn, $user_query);
    mysqli_stmt_bind_param($stmt, "s", $_COOKIE['token']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if (mysqli_num_rows($result) > 0) {
        $conn->query("DELETE FROM `mythicaldash_users` WHERE `mythicaldash_users`.`api_key` = '" . mysqli_real_escape_string($conn,$_COOKIE['token']) . "';");
        header('location: /auth/logout');
        die();
    } else {
        header('location: /user/profile?e=Can`t find this user in the database');
        die();
    }
} else {
    header('location: /user/profile');
    die();
}
?>