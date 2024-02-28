<?php 
include(__DIR__ . '/../requirements/page.php');
use MythicalDash\NotificationHandler;
if (isset($_GET['id'])) { 
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    NotificationHandler::delete($id);
    header('location: /dashboard');
    die();
} else {
    header('location: /dashboard');
    die();
}
?>