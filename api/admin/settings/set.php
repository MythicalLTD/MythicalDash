<?php
use MythicalDash\ErrorHandler;
use MythicalDash\SettingsManager;

include(__DIR__ . "/../base.php");
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['key']) && !$_POST['key'] == "") {
            if (isset($_POST['value']) && !$_POST['value'] == "") {
                $key = mysqli_real_escape_string($conn, $_POST['key']);
                $value = mysqli_real_escape_string($conn, $_POST['value']);
                try {
                    if (mysqli_query($conn, "UPDATE `mythicaldash_settings` SET `" . $key . "` = '" . $value . "' WHERE `mythicaldash_settings`.`id` = 1;")) {
                        $rsp = array(
                            "code" => 200,
                            "error" => null,
                            "message" => "We updated the database value!"
                        );
                        $conn->close();
                        http_response_code(200);
                        die(json_encode($rsp, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                    } else {
                        $rsp = array(
                            "code" => 403,
                            "error" => "The server understood the request, but it refuses to authorize it.",
                            "message" => "Failed to execute set action please try again."
                        );
                        http_response_code(403);
                        die(json_encode($rsp, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                    }
                } catch (Exception $e) { 
                    $rsp = array(
                        "code" => 403,
                        "error" => "The server understood the request, but it refuses to authorize it.",
                        "message" => $e->getMessage()
                    );
                    http_response_code(403);
                    die(json_encode($rsp, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                }
                
            } else {
                $rsp = array(
                    "code" => 400,
                    "error" => "Bad request syntax",
                    "message" => "Value is required, but not provided."
                );
                http_response_code(400);
                die(json_encode($rsp, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            }
        } else {
            $rsp = array(
                "code" => 400,
                "error" => "Bad request syntax",
                "message" => "Key is required, but not provided."
            );
            http_response_code(400);
            die(json_encode($rsp, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        }

    } catch (Exception $e) {
        ErrorHandler::Critical("Settings DB ", $e);
        $rsp = array(
            "code" => 500,
            "error" => "The server encountered a situation it doesn't know how to handle.",
            "message" => "We are sorry, but our server cannot handle this request. Please do not try again!",
        );
        http_response_code(500);
        die(json_encode($rsp, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
} else {
    $rsp = array(
        "code" => 405,
        "error" => "A request was made of a page using a request method not supported by that page",
        "message" => "Please use a get request"
    );
    http_response_code(405);
    die(json_encode($rsp, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}