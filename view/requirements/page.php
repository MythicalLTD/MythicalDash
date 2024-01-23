<?php
use MythicalDash\ErrorHandler;
use MythicalDash\SessionManager;
use MythicalDash\Database\Connect;
use MythicalDash\SettingsManager;
use MythicalDash\Pterodactyl\Connection;
$conn = new Connect();
$conn = $conn->connectToDatabase();
$session = new SessionManager();
$session->authenticateUser();
if (!$session->getUserInfo('banned') == "") {
    header('location: /auth/login?e='.$lang['login_banned']);
    $conn->close();
    die();
}
if (SettingsManager::getSetting("maintenance") == "true") {
    ErrorHandler::ShowCritical("".$lang['maintenance_description']);
    die();
}
Connection::initializeSettings();
if (!Connection::checkConnection() == true) {
    ErrorHandler::ShowCritical($lang['pterodactyl_connection_error']);
    die();
}
?>