<?php
include(__DIR__ . '/../base.php');
include(__DIR__ . '/base.php');
$sql = "SELECT * FROM mythicaldash_users";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    $users = array();
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    $conn->close();
    http_response_code(200);
    die(json_encode($users));
} else {
    $conn->close();
    $rsp = array(
        "code" => 403,
        "error" => "The server understood the request, but it refuses to authorize it.",
        "message" => "We can't find any user in the database!"
    );
    http_response_code(403);
    die(json_encode($rsp, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}
?>