<?php 
if (isset($_GET['api_key'])) {
    if (!$_GET['api_key'] == "") {
        $api_key = mysqli_real_escape_string($conn,$_GET['api_key']);
        $query = "SELECT * FROM mythicaldash_apikeys WHERE `skey` = '$api_key'";
        $result = mysqli_query($conn, $query);
        if (mysqli_num_rows($result) > 0) {
            //CONTINUE CODE HERE
            
        } else {
            http_response_code(401);
            $rsp = array(
                "code" => 401,
                "error" => "The authentication credentials are invalid"
            );
            die(json_encode($rsp, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        }
    } else {
        http_response_code(401);
        $rsp = array(
            "code" => 401,
            "error" => "The request requires authentication or the provided credentials"
        );
        die(json_encode($rsp, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
}
else {
    http_response_code(401);
    $rsp = array(
        "code" => 401,
        "error" => "The request requires authentication or the provided credentials"
    );
    die(json_encode($rsp, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}
?>