<?php
include(__DIR__ . '/../../requirements/page.php');
include(__DIR__ . '/../../requirements/admin.php');
if (isset($_GET['id']) && !$_GET['id'] == "") {
    $user_query = "SELECT * FROM mythicaldash_locations WHERE id = ?";
    $stmt = mysqli_prepare($conn, $user_query);
    mysqli_stmt_bind_param($stmt, "s", $_GET['id']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if (mysqli_num_rows($result) > 0) {
        $check_query = "SELECT * FROM mythicaldash_servers WHERE location = '".mysqli_real_escape_string($conn,$_GET['id'])."'";
        $resultl = mysqli_query($conn, $check_query);
        if (mysqli_num_rows($resultl) > 0) {
            header("location: /admin/locations?e=Sorry but there are some servers on this location please delete them first!");
            $conn->close();
            die();
        }
        $check_query2 = "SELECT * FROM mythicaldash_servers_queue WHERE location = '".mysqli_real_escape_string($conn,$_GET['id'])."'";
        $result2 = mysqli_query($conn, $check_query2);
        if (mysqli_num_rows($result2) > 0) {
            header("location: /admin/locations?e=Sorry but there are some servers on this location please delete them first!");
            $conn->close();
            die();
        }
        $conn->query("DELETE FROM `mythicaldash_locations` WHERE `mythicaldash_locations`.`id` = '".mysqli_real_escape_string($conn,$_GET['id'])."'");
        header("location: /admin/locations?s=Done");
        $conn->close();
        die();
    } else {
        header("location: /admin/locations?e=Cannot find the location in the database.");
        $conn->close();
        die();
    }
} else {
    header('location: /admin/locations');
    die();
}
?>