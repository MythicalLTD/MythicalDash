<?php
include(__DIR__ . '/../base.php');
include(__DIR__ . '/base.php');

$userCountQuery = "SELECT COUNT(*) AS user_count FROM mythicaldash_users"; 
$userCountResult = $conn->query($userCountQuery);
$userCount = $userCountResult->fetch_assoc()['user_count'];

$ticketCountQuery = "SELECT COUNT(*) AS ticket_count FROM mythicaldash_tickets"; 
$ticketCountResult = $conn->query($ticketCountQuery);
$ticketCount = $ticketCountResult->fetch_assoc()['ticket_count'];

$Servers = "SELECT COUNT(*) AS servers FROM mythicaldash_servers"; 
$serverCountResult = $conn->query($Servers);
$serverCount = $serverCountResult->fetch_assoc()['servers'];

$servers_queue = "SELECT COUNT(*) AS serversq FROM mythicaldash_servers_queue"; 
$serverqCountResult = $conn->query($servers_queue);
$servers_queueCount = $serverqCountResult->fetch_assoc()['serversq'];

$locations = "SELECT COUNT(*) AS locations FROM mythicaldash_locations"; 
$locationsCountResult = $conn->query($locations);
$locationsCount = $locationsCountResult->fetch_assoc()['locations'];

$eggs = "SELECT COUNT(*) AS eggs FROM mythicaldash_eggs"; 
$eggsCountResult = $conn->query($eggs);
$eggCount = $eggsCountResult->fetch_assoc()['eggs'];

$rsp = array(
    "code" => 200,
    "error" => null,
    "message" => null,
    "statistics" => array(
        "servers" => $serverCount,
        "servers_queue" => $servers_queueCount,
        "eggs" => $eggCount,
        "locations" => $locationsCount,
        "users" => $userCount,
        "tickets" => $ticketCount,
    )
);
http_response_code(200);
$conn->close();
die(json_encode($rsp, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
?>