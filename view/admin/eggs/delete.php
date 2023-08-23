<?php
include(__DIR__ . '/../../requirements/page.php');
include(__DIR__ . '/../../requirements/admin.php');
if (isset($_GET['id'])) {
    $user_query = "SELECT * FROM mythicaldash_eggs WHERE id = ?";
    $stmt = mysqli_prepare($conn, $user_query);
    mysqli_stmt_bind_param($stmt, "s", $_GET['id']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if (mysqli_num_rows($result) > 0) {
        $check_query = "SELECT * FROM mythicaldash_servers WHERE egg_id = '".mysqli_real_escape_string($conn,$_GET['id'])."'";
        $resultl = mysqli_query($conn, $check_query);
        if (mysqli_num_rows($resultl) > 0) {
            header("location: /admin/eggs?e=Sorry but there are some servers on this egg please delete them first!");
            $conn->close();
            die();
        }
        $check_query2 = "SELECT * FROM mythicaldash_servers_queue WHERE egg = '".mysqli_real_escape_string($conn,$_GET['id'])."'";
        $result2 = mysqli_query($conn, $check_query2);
        if (mysqli_num_rows($result2) > 0) {
            header("location: /admin/eggs?e=Sorry but there are some servers on this egg please delete them first!");
            $conn->close();
            die();
        }
        $conn->query("DELETE FROM `mythicaldash_eggs` WHERE `mythicaldash_eggs`.`id` = '".mysqli_real_escape_string($conn,$_GET['id'])."'");
        header("location: /admin/eggs?s=Done");
        $conn->close();
        die();
    } else {
        header("location: /admin/eggs?e=Can't find a location in the database.");
        $conn->close();
        die();
    }
} else {
    header('location: /admin/eggs');
    die();
}
?>