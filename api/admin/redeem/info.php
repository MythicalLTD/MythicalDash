<?php 
include(__DIR__.'/../base.php');
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {   
        if (isset($_GET['code']) && !$_GET['code'] == "") {
            $redeem_query = "SELECT * FROM mythicaldash_redeem WHERE code = ?";
            $stmt = mysqli_prepare($conn, $redeem_query);
            mysqli_stmt_bind_param($stmt, "s", $_GET['code']);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $code = mysqli_real_escape_string($conn, $_GET['code']);
            if (mysqli_num_rows($result) > 0) { 
                $redeemDb = $conn->query("SELECT * FROM mythicaldash_redeem WHERE code = '" . $code . "'")->fetch_array();
                $rsp = array(
                    "code" => 200,
                    "error" => null,
                    "message" => null,
                    "data" => array(
                        "database_id" => $redeemDb['id'],
                        "code" => $redeemDb['code'],
                        "resources" => array( 
                            "coins" => $redeemDb['coins'],
                            "ram" => $redeemDb['ram'],
                            "disk" => $redeemDb['disk'],
                            "cpu" => $redeemDb['cpu'],
                            "server_limit" => $redeemDb['server_limit'],
                            "ports" => $redeemDb['ports'],
                            "databases" => $redeemDb['databases'],
                            "backups" => $redeemDb['backups'],
                        ),
                        "dateinfo" => $redeemDb['created']                     
                    ),
                );
                http_response_code(200);
                $conn->close();
                die(json_encode($rsp, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            } else {
                $rsp = array(
                    "code" => 403,
                    "error" => "The server understood the request, but it refuses to authorize it.",
                    "message" => "We can't find the code in our database!"
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
        "message" => "Please use a get request"
    );
    http_response_code(405);
    die(json_encode($rsp, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

?>