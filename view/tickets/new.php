<?php
use MythicalDash\Encryption;

include(__DIR__ . '/../requirements/page.php');
if (isset($_GET['subject']) && isset($_GET['priority']) && isset($_GET['description'])) {
    if (!$_GET['subject'] == "" && !$_GET['priority'] == "" && !$_GET['description'] == "") {
        $subject = mysqli_real_escape_string($conn, $_GET['subject']);
        $priority = mysqli_real_escape_string($conn, $_GET['priority']);
        $description = mysqli_real_escape_string($conn, $_GET['description']);
        $attachment = mysqli_real_escape_string($conn, $_GET['attachment']);
        $api_key = mysqli_real_escape_string($conn, $_COOKIE['token']);
        $conn->query("INSERT INTO `mythicaldash_tickets` (`ownerkey`, `ticketuuid`, `subject`, `priority`, `description`, `attachment`) VALUES ('" . $api_key . "', '" . Encryption::generate_keynoinfo() . "', '" . $subject . "', '" . $priority . "', '" . $description . "', '" . $attachment . "');");
        $conn->close();
        header('location: /help-center/tickets');
        die();
    } else {
        header('location: /help-center?e=Missing the required information to create a ticket.');
        die();
    }
} else {
    header('location: /help-center?e=Missing the required information to create a ticket.');
    die();
}
?>