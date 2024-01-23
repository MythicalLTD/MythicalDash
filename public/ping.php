<?php
if (isset($_GET['host'])) {
    $host = $_GET['host'];
    $ping = rand(1, 100);

    header('Content-Type: application/json');
    echo json_encode(['ping' => $ping]);
}

?>