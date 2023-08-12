<?php
if (isset($_GET['api_key'])) {
    if (!$_GET['api_key'] == "") {
        $api_key = mysqli_real_escape_string($conn, $_GET['api_key']);
        $query = "SELECT * FROM mythicaldash_users WHERE `api_key` = '$api_key'";
        $result = mysqli_query($conn, $query);
        if (mysqli_num_rows($result) > 0) {
            //CONTINUE CODE HERE

        } else {
            $rsp = array(
                "code" => 403,
                "error" => "The server understood the request, but it refuses to authorize it.",
                "message" => "Im sorry but the api key is wrong"
            );
            http_response_code(403);
            die(json_encode($rsp, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        }
    } else {
        $rsp = array(
            "code" => 400,
            "error" => "The server cannot understand the request due to a client error.",
            "message" => "Please provide an api key"
        );
        http_response_code(400);
        die(json_encode($rsp, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
} else {
    $rsp = array(
        "code" => 400,
        "error" => "The server cannot understand the request due to a client error.",
        "message" => "Please provide an api key"
    );
    http_response_code(400);
    die(json_encode($rsp, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}
?>