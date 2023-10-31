<?php
use MythicalDash\ErrorHandler;
include("base.php");
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $userCountQuery = "SELECT COUNT(*) AS user_count FROM mythicaldash_users";
        $userCountResult = $conn->query($userCountQuery);
        $userCount = $userCountResult->fetch_assoc()['user_count'];

        $ticketCountQuery = "SELECT COUNT(*) AS ticket_count FROM mythicaldash_tickets";
        $ticketCountResult = $conn->query($ticketCountQuery);
        $ticketCount = $ticketCountResult->fetch_assoc()['ticket_count'];

        $serverCountQuery = "SELECT COUNT(*) AS servers FROM mythicaldash_servers";
        $serverCountResult = $conn->query($serverCountQuery);
        $serverCount = $serverCountResult->fetch_assoc()['servers'];

        $serverQueueQuery = "SELECT COUNT(*) AS serversq FROM mythicaldash_servers_queue";
        $serverQueueCountResult = $conn->query($serverQueueQuery);
        $serverQueueCount = $serverQueueCountResult->fetch_assoc()['serversq'];

        $locationsQuery = "SELECT COUNT(*) AS locations FROM mythicaldash_locations";
        $locationsCountResult = $conn->query($locationsQuery);
        $locationsCount = $locationsCountResult->fetch_assoc()['locations'];

        $eggsQuery = "SELECT COUNT(*) AS eggs FROM mythicaldash_eggs";
        $eggsCountResult = $conn->query($eggsQuery);
        $eggCount = $eggsCountResult->fetch_assoc()['eggs'];
        $rsp = array(
            "code" => 200,
            "error" => null,
            "message" => null,
            "data" => array(
                "users" => $userCount,
                "tickets" => $ticketCount,
                "servers" => $serverCount,
                "servers_queue" => $serverQueueCount,
                "locations" => $locationsCount,
                "eggs" => $eggCount,
            )
        );
        http_response_code(200);
        die(json_encode($rsp, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    } catch (Exception $e) {
        $rsp = array(
            "code" => 500,
            "error" => "The server encountered a situation it doesn't know how to handle.",
            "message" => "We are sorry, but our server can't handle this request. Please do not try again!"
        );
        http_response_code(500);
        ErrorHandler::Critical("Statistics ",$e);
        die(json_encode($rsp, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
} else {
    $rsp = array(
        "code" => 405,
        "error" => "A request was made of a page using a request method not supported by that page",
        "message" => "Please use a get request"
    );
    http_response_code(405);
    die(json_encode($rsp, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}
?>