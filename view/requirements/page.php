<?php
use MythicalDash\ErrorHandler;
use MythicalDash\SessionManager;
use MythicalDash\Database\Connect;
use MythicalDash\SettingsManager;
$conn = new Connect();
$conn = $conn->connectToDatabase();
$session = new SessionManager();
$session->authenticateUser();
if (!$session->getUserInfo('banned') == "") {
    header('location: /auth/login?e=Sorry but you are banned for using our services');
    $conn->close();
    die();
}
if (SettingsManager::getSetting("maintenance") == "true") {
    ErrorHandler::ShowCritical("We are so sorry but our client is down for maintenance.");
    die();
}
?>