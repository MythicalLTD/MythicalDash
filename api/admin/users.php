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
    http_response_code(404);
    echo json_encode(array('message' => 'No users found'));
}
?>