<?php
include(__DIR__ . '/../base.php');
include(__DIR__ . '/base.php');

$userCountQuery = "SELECT COUNT(*) AS user_count FROM mythicaldash_users"; 
$userCountResult = $conn->query($userCountQuery);
$userCount = $userCountResult->fetch_assoc()['user_count'];

$ticketCountQuery = "SELECT COUNT(*) AS ticket_count FROM mythicaldash_tickets"; 
$ticketCountResult = $conn->query($ticketCountQuery);
$ticketCount = $ticketCountResult->fetch_assoc()['ticket_count'];

$rsp = array(
    "code" => 200,
    "error" => null,
    "message" => "Sure here you go:",
    "statistics" => array(
        "users" => $userCount,
        "tickets" => $ticketCount
    )
);
http_response_code(200);
$conn->close();
die(json_encode($rsp, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
?>