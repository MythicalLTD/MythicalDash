<?php 
include(__DIR__."/base.php");

if (isset($_GET['uuid'])) {
    $ticket_uuid = mysqli_real_escape_string($conn, $_GET['uuid']);

    $query = "SELECT t.*, m.userkey, m.message, m.attachment 
              FROM mythicaldash_tickets t
              LEFT JOIN mythicaldash_tickets_messages m ON t.ticketuuid = m.ticketuuid
              WHERE t.ticketuuid = '$ticket_uuid'";

    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $ticket_info = null;
        $messages = array();

        while ($row = mysqli_fetch_assoc($result)) {
            if (!$ticket_info) {
                $ticket_info = array(
                    "id" => $row["id"],
                    "ticketuuid" => $row["ticketuuid"],
                    "subject" => $row["subject"],
                    "priority" => $row["priority"],
                    "description" => $row["description"],
                    "attachment" => $row["attachment"],
                    "status" => $row["status"],
                    "timestamp" => $row["created"]
                );
            }

            if ($row["userkey"]) {
                $messages[] = array(
                    "message" => $row["message"],
                    "attachment" => $row["attachment"],
                    "timestamp" => $row["created"]
                );
            }
        }

        $response = array(
            "ticket_info" => $ticket_info,
            "messages" => $messages
        );

        header('Content-Type: application/json');
        echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    } else {
        $rsp = array(
            "code" => 403,
            "error" => "The server understood the request, but it refuses to authorize it.",
            "message" => "We can't find the ticket in the database"
        );
        http_response_code(403);
        die(json_encode($rsp, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
} else {
    $rsp = array(
        "code" => 400,
        "error" => "The server cannot understand the request due to a client error.",
        "message" => "Please provide a ticket uuid"
    );
    http_response_code(400);
    die(json_encode($rsp, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}
?>
