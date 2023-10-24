<?php
use MythicalDash\SessionManager;
use MythicalDash\Database\Connect;
$conn = new Connect();
$conn = $conn->connectToDatabase();
$session = new SessionManager();
$session->authenticateUser();
?>