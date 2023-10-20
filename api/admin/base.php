<?php
include(__DIR__ . "/../base.php");
$headers = getallheaders();

if (isset($headers['Authorization']) && !$headers['Authorization'] == "") {
    $authorizationHeader = $headers['Authorization'];
    $api_key = mysqli_real_escape_string($conn, $authorizationHeader);
    $query = "SELECT * FROM mythicaldash_apikeys WHERE `skey` = '$api_key'";
    $result = mysqli_query($conn, $query);
    if (mysqli_num_rows($result) > 0) {
        //CONTINUE CODE HERE

    } else {
        $response = [
            "code" => 403,
            "error" => "Unauthorized",
            "message" => "Please make sure your API key is valid."
        ];
        http_response_code(403);
        die(json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
} else {
    $response = [
        "code" => 401,
        "error" => "Authentication required",
        "message" => "Please provide your API key."
    ];
    http_response_code(401);
    die(json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

?>