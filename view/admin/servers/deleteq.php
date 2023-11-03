<?php
include(__DIR__ . '/../../requirements/page.php');
include(__DIR__ . '/../../requirements/admin.php');
if (isset($_GET['id']) && !$_GET['id'] == "") {
    $user_query = "SELECT * FROM mythicaldash_servers_queue WHERE id = ?";
    $stmt = mysqli_prepare($conn, $user_query);
    mysqli_stmt_bind_param($stmt, "s", $_GET['id']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if (mysqli_num_rows($result) > 0) {
        $conn->query("DELETE FROM `mythicaldash_servers_queue` WHERE `mythicaldash_servers_queue`.`id` = '".mysqli_real_escape_string($conn,$_GET['id'])."'");
        header("location: /admin/servers/queue?s=Done we removed the server.");
        $conn->close();
        die();
    } else {
        header("location: /admin/servers/queue?e=Cannot find the location in the database.");
        $conn->close();
        die();
    }
} else {
    header('location: /admin/servers/queue');
    die();
}
?>