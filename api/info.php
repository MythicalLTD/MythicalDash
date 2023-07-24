<?php
header('Content-Type: application/json');
http_response_code(400); 
echo json_encode(array('status' => 'error', 'message' => 'Hi this is a wrong api endpoint please check: https://github.com/MythicalLTD/MythicalDash#api-reference'),JSON_PRETTY_PRINT);
?>