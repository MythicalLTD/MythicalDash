<?php 
include('base.php');
if ($_SERVER['REQUEST_METHOD'] === 'GET') { 
    $rsp = array(
        "code" => 200,
        "error" => null,
        "message" => "Hi, and welcome to MythicalDash main api this is the main path of our API. Make sure to check our docs for the requests you can make!"
    );
    http_response_code(200);
    die(json_encode($rsp, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
} else {
    $rsp = array(
        "code" => 405,
        "error" => "A request was made of a page using a request method not supported by that page",
        "message" => "Please use a get request"
    );
    http_response_code(405);
    die(json_encode($rsp, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}
?>