<?php
use MythicalDash\EggManagerConfig;
include(__DIR__ . '/../../requirements/page.php');
include(__DIR__ . '/../../requirements/admin.php');
if (isset($_GET['name']) && !$_GET['name'] == "") {
    $user_query = "SELECT * FROM mythicaldash_eggs_config WHERE setting_name = ?";
    $stmt = mysqli_prepare($conn, $user_query);
    mysqli_stmt_bind_param($stmt, "s", $_GET['name']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if (mysqli_num_rows($result) > 0) {
        if (EggManagerConfig::deleteConfig($_GET['name'])) {
            header("location: /admin/eggs/config?s=We removed the egg config from the database.");
            $conn->close();
            die();
        } else {
            header("location: /admin/eggs/config?e=Cannot find the egg in the database.");
            $conn->close();
            die();
        }
    } else {
        header("location: /admin/eggs/config?s=Cannot find the egg in the database.");
        $conn->close();
        die();
    }
} else {
    header('location: /admin/eggs/config');
    die();
}
?>