<?php
include(__DIR__ . '/../../requirements/page.php');
include(__DIR__ . '/../../requirements/admin.php');

if (isset($_GET['id']))
{ 
    if (!$_GET['id'] == "") {
        $user_query = "SELECT * FROM mythicaldash_users WHERE id = ?";
        $stmt = mysqli_prepare($conn, $user_query);
        mysqli_stmt_bind_param($stmt, "s", $_GET['id']);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if (mysqli_num_rows($result) > 0) {
            $user_info = $conn->query("SELECT * FROM mythicaldash_users WHERE id = '" . $_GET['id'] . "'")->fetch_array();
            $email = $user_info['email'];
            $password = $user_info['password'];
            $skey = generate_key($email,$password);
            $conn->query("UPDATE `mythicaldash_users` SET `api_key` = '" . $skey . "' WHERE `mythicaldash_users`.`id` = " . $_GET['id'] . ";");
            header('location: /admin/users/edit?id='.$_GET['id'].'&s=We updated the user settings in the database');
            $conn->close();
            die();
        } else {
            header('location: /admin/users/view?e=Can`t find this user in the database');
            $conn->close();
            die();
        }
    } else {
        header('location: /admin/users/view?e=Can`t find this user in the database');
        die();
    }
} else {
    header('location: /admin/users/view');
    die();
}
?>