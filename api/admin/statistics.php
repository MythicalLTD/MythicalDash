<?php
include(__DIR__ . '/../base.php');
include(__DIR__ . '/base.php');


$sql = "SELECT COUNT(*) AS total_users FROM mythicaldash_users";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $totalUsers = $row['total_users'];
    $rsp = array(
        "code" => 200,
        "error" => null,
        "statistics" => array(
            "users" => $totalUsers,
        )
    );
    $conn->close();
    http_response_code(200);
    die(json_encode($rsp, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

} else {
    $rsp = array(
        "code" => 404,
        "error" => "We can't find any user in the database",
    );
    http_response_code(404);
    die(json_encode($rsp, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

?>