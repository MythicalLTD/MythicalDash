<?php
use MythicalDash\ErrorHandler;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['code']) && !$_POST['code'] == "") {
            $redeem_query = "SELECT * FROM mythicaldash_redeem WHERE code = ?";
            $stmt = mysqli_prepare($conn, $redeem_query);
            mysqli_stmt_bind_param($stmt, "s", $_POST['code']);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            if (mysqli_num_rows($result) > 0) {
                $conn->query("DELETE FROM `mythicaldash_redeem` WHERE `mythicaldash_redeem`.`code` = '" . mysqli_real_escape_string($conn, $_POST['code']) . "';");
                $conn->close();
                $rsp = array(
                    "code" => 200,
                    "error" => null,
                    "message" => "Code removed from the database"
                );
                http_response_code(200);
                die(json_encode($rsp, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            } else {
                $rsp = array(
                    "code" => 403,
                    "error" => "The server understood the request, but it refuses to authorize it.",
                    "message" => "We cannot find the code in our database!"
                );
                http_response_code(403);
                die(json_encode($rsp, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            }
        } else {
            $rsp = array(
                "code" => 400,
                "error" => "Bad request syntax",
                "message" => "Code is required, but not provided."
            );
            http_response_code(400);
            die(json_encode($rsp, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        }
    } catch (Exception $e) {
        ErrorHandler::Critical("Redeem Delete ",$e);
        $rsp = array(
            "code" => 500,
            "error" => "The server encountered a situation it doesn't know how to handle.",
            "message" => "We are sorry, but our server cannot handle this request. Please do not try again!"
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