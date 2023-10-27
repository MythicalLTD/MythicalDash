<?php
use MythicalDash\SessionManager;
use MythicalDash\Database\Connect;
$conn = new Connect();
$conn = $conn->connectToDatabase();
$session = new SessionManager();
$session->authenticateUser();
if (!$session->getUserInfo('banned') == "") {
    header('location: /auth/login?e=Sorry but you are banned for using our services');
    $conn->close();
    die();
}
?>