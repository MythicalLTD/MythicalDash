<?php
include(__DIR__ . "/../base.php");
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['code']) && !$_POST['code'] == "") {
            $code = mysqli_real_escape_string($conn, $_POST['code']);
            if (isset($_POST['coins']) && is_numeric($_POST['coins']) && $_POST['coins'] >= 0) {
                $coins = mysqli_real_escape_string($conn, $_POST['coins']);
                if (isset($_POST['ram']) && is_numeric($_POST['ram']) && $_POST['ram'] >= 0) {
                    $ram = mysqli_real_escape_string($conn, $_POST['ram']);
                    if (isset($_POST['disk']) && is_numeric($_POST['disk']) && $_POST['disk'] >= 0) {
                        $disk = mysqli_real_escape_string($conn, $_POST['disk']);
                        if (isset($_POST['cpu']) && is_numeric($_POST['cpu']) && $_POST['cpu'] >= 0) {
                            $cpu = mysqli_real_escape_string($conn, $_POST['cpu']);
                            if (isset($_POST['server_limit']) && is_numeric($_POST['server_limit']) && $_POST['server_limit'] >= 0) {
                                $server_limit = mysqli_real_escape_string($conn, $_POST['server_limit']);
                                if (isset($_POST['ports']) && is_numeric($_POST['ports']) && $_POST['ports'] >= 0) {
                                    $ports = mysqli_real_escape_string($conn, $_POST['ports']);
                                    if (isset($_POST['databases']) && is_numeric($_POST['databases']) && $_POST['databases'] >= 0) {
                                        $databases = mysqli_real_escape_string($conn, $_POST['databases']);
                                        if (isset($_POST['backups']) && is_numeric($_POST['backups']) && $_POST['backups'] >= 0) {
                                            $backups = mysqli_real_escape_string($conn, $_POST['backups']);
                                            if (isset($_POST['uses']) && is_numeric($_POST['uses']) && $_POST['uses'] >= 0) {
                                                $uses = mysqli_real_escape_string($conn, $_POST['uses']);
                                                $conn->query("INSERT INTO `mythicaldash_redeem` (
                                                    `code`, 
                                                    `uses`, 
                                                    `coins`, 
                                                    `ram`, 
                                                    `disk`, 
                                                    `cpu`, 
                                                    `server_limit`, 
                                                    `ports`, 
                                                    `databases`, 
                                                    `backups` 
                                                    ) VALUES (
                                                    '" . $code . "', 
                                                    '" . $uses . "', 
                                                    '" . $coins . "', 
                                                    '" . $ram . "', 
                                                    '" . $disk . "', 
                                                    '" . $cpu . "', 
                                                    '" . $server_limit . "', 
                                                    '" . $ports . "', 
                                                    '" . $databases . "', 
                                                    '" . $backups . "'
                                                    );");
                                                $conn->close();
                                                $rsp = array(
                                                    "code" => 200,
                                                    "error" => null,
                                                    "message" => "Added a new redeem key in the database!"
                                                );
                                                http_response_code(200);
                                                die(json_encode($rsp, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                                                
                                            } else {
                                                $rsp = array(
                                                    "code" => 400,
                                                    "error" => "Bad request syntax",
                                                    "message" => "Uses is required, but not provided."
                                                );
                                                http_response_code(400);
                                                die(json_encode($rsp, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                                            }
                                        } else {
                                            $rsp = array(
                                                "code" => 400,
                                                "error" => "Bad request syntax",
                                                "message" => "Backups is required, but not provided."
                                            );
                                            http_response_code(400);
                                            die(json_encode($rsp, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                                        }
                                    } else {
                                        $rsp = array(
                                            "code" => 400,
                                            "error" => "Bad request syntax",
                                            "message" => "Databases is required, but not provided."
                                        );
                                        http_response_code(400);
                                        die(json_encode($rsp, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                                    }
                                } else {
                                    $rsp = array(
                                        "code" => 400,
                                        "error" => "Bad request syntax",
                                        "message" => "Ports is required, but not provided."
                                    );
                                    http_response_code(400);
                                    die(json_encode($rsp, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                                }
                            } else {
                                $rsp = array(
                                    "code" => 400,
                                    "error" => "Bad request syntax",
                                    "message" => "Server Limit is required, but not provided."
                                );
                                http_response_code(400);
                                die(json_encode($rsp, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                            }
                        } else {
                            $rsp = array(
                                "code" => 400,
                                "error" => "Bad request syntax",
                                "message" => "Cpu is required, but not provided."
                            );
                            http_response_code(400);
                            die(json_encode($rsp, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                        }
                    } else {
                        $rsp = array(
                            "code" => 400,
                            "error" => "Bad request syntax",
                            "message" => "Disk is required, but not provided."
                        );
                        http_response_code(400);
                        die(json_encode($rsp, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                    }
                } else {
                    $rsp = array(
                        "code" => 400,
                        "error" => "Bad request syntax",
                        "message" => "Ram is required, but not provided."
                    );
                    http_response_code(400);
                    die(json_encode($rsp, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                }
            } else {
                $rsp = array(
                    "code" => 400,
                    "error" => "Bad request syntax",
                    "message" => "Coins is required, but not provided."
                );
                http_response_code(400);
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
        "message" => "Please use a post request"
    );
    http_response_code(405);
    die(json_encode($rsp, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}
?>