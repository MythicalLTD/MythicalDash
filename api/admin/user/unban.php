<?php
use MythicalDash\ErrorHandler;
include(__DIR__ . "/../base.php");
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['email']) && !$_POST['email'] == "") {
            $email = mysqli_real_escape_string($conn, $_POST['email']);
            $query = "SELECT * FROM mythicaldash_users WHERE `email` = '$email'";
            $result = mysqli_query($conn, $query);
            if (mysqli_num_rows($result) > 0) {
                $userdb = $conn->query("SELECT * FROM mythicaldash_users WHERE email = '" . $email . "'")->fetch_array();
                if (!$userdb["banned"] == "") {
                    $conn->query("UPDATE `mythicaldash_users` SET `banned` = '' WHERE `mythicaldash_users`.`email` = '$email';");
                    $rsp = array(
                        "code" => 200,
                        "error" => null,
                        "message" => "We unbanned " . $userdb['username'],
                    );
                    http_response_code(200);
                    die(json_encode($rsp, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                } else {
                    $rsp = array(
                        "code" => 403,
                        "error" => "The server understood the request, but it refuses to authorize it.",
                        "message" => "User is not banned"
                    );
                    http_response_code(403);
                    die(json_encode($rsp, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                }
            } else {
                $rsp = array(
                    "code" => 403,
                    "error" => "The server understood the request, but it refuses to authorize it.",
                    "message" => "We can't find this user in our database!"
                );
                http_response_code(403);
                die(json_encode($rsp, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            }
        } else {
            $rsp = array(
                "code" => 400,
                "error" => "Bad request syntax",
                "message" => "Email is required, but not provided."
            );
            http_response_code(400);
            die(json_encode($rsp, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        }

    } catch (Exception $e) {
        ErrorHandler::Critical("User UnBan ",$e);
        $rsp = array(
            "code" => 500,
            "error" => "The server encountered a situation it doesn't know how to handle.",
            "message" => "We are sorry, but our server can't handle this request. Please do not try again!"
        );
        http_response_code(500);
        die(json_encode($rsp, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
} else {
    $rsp = array(
        "code" => 405,
        "error" => "A request was made of a page using a request method not supported by that page",
        "message" => "Please use a post request"
    );
    http_response_code(405);
    die(json_encode($rsp, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}
?>