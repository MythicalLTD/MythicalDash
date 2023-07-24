<?php
header('Content-Type: application/json');
if (isset($_GET['pwd'])) {
    $pwd = $_GET['pwd'];
    $hpwd = password_hash($pwd, PASSWORD_DEFAULT);
    http_response_code(200); 
    echo json_encode(array('status' => 'success', 'hashed_password' => $hpwd), JSON_PRETTY_PRINT);
} else {
    http_response_code(400); 
    echo json_encode(array('status' => 'error', 'message' => 'Please specify a password by appending ?pwd=<yourpwd> to the URL'), JSON_PRETTY_PRINT);
}
?>