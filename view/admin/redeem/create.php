<?php
include(__DIR__ . '/../../requirements/page.php');
include(__DIR__ . '/../../requirements/admin.php');

if (isset($_GET['key']) && !$_GET['key'] == "") {
    $code = mysqli_real_escape_string($conn, $_GET['code']);
    $uses = mysqli_real_escape_string($conn, $_GET['uses']);
    $coins = mysqli_real_escape_string($conn, $_GET['coins']);
    $ram = mysqli_real_escape_string($conn, $_GET['ram']);
    $disk = mysqli_real_escape_string($conn, $_GET['disk']);
    $cpu = mysqli_real_escape_string($conn, $_GET['cpu']);
    $server_limit = mysqli_real_escape_string($conn, $_GET['server_limit']);
    $ports = mysqli_real_escape_string($conn, $_GET['ports']);
    $databases = mysqli_real_escape_string($conn, $_GET['databases']);
    $backups = mysqli_real_escape_string($conn, $_GET['backups']);
    $conn->query("INSERT INTO `mythicaldash_redeem` (
    `code`, 
    `uses`, 
    `coins`, 
    `ram`, 
    `disk`, 
    `cpu`, 
    `server_limit`, 
    `ports`, 
    `databases`, 
    `backups` 
    ) VALUES (
    '" . $code . "', 
    '" . $uses . "', 
    '" . $coins . "', 
    '" . $ram . "', 
    '" . $disk . "', 
    '" . $cpu . "', 
    '" . $server_limit . "', 
    '" . $ports . "', 
    '" . $databases . "', 
    '" . $backups . "'
    );");
    header("location: /admin/redeem?s=Done we created a new redeem key");
    $conn->close();
    die();
} else {
    header("location: /admin/redeem?e=Please fill in all required information");
    die();
}
?>