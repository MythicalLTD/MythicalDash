<?php
use MythicalDash\ErrorHandler;
include(__DIR__ . "/../../base.php");
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['email']) && !$_POST['email'] == "") {
            $email = mysqli_real_escape_string($conn, $_POST['email']);
            $query = "SELECT * FROM mythicaldash_users WHERE `email` = '$email'";
            $result = mysqli_query($conn, $query);
            if (mysqli_num_rows($result) > 0) {
                $userdb = $conn->query("SELECT * FROM mythicaldash_users WHERE email = '" . $email . "'")->fetch_array();
                $userdb_coins = $userdb["coins"];
                $userdb_ram = $userdb["ram"];
                $userdb_disk = $userdb["disk"];
                $userdb_cpu = $userdb["cpu"];
                $userdb_server_limit = $userdb["server_limit"];
                $userdb_ports = $userdb["ports"];
                $userdb_databases = $userdb["databases"];
                $userdb_backups = $userdb["backups"];
                if (isset($_POST["coins"]) && $_POST["coins"] >= 0 && is_numeric($_POST['coins'])) {
                    $request_coins = mysqli_real_escape_string($conn, $_POST['coins']);
                    if (isset($_POST["ram"]) && $_POST["ram"] >= 0 && is_numeric($_POST['ram'])) {
                        $request_ram = mysqli_real_escape_string($conn, $_POST['ram']);
                        if (isset($_POST["disk"]) && $_POST["disk"] >= 0 && is_numeric($_POST['disk'])) {
                            $request_disk = mysqli_real_escape_string($conn, $_POST['disk']);
                            if (isset($_POST["cpu"]) && $_POST["cpu"] >= 0 && is_numeric($_POST['cpu'])) {
                                $request_cpu = mysqli_real_escape_string($conn, $_POST['cpu']);
                                if (isset($_POST["server_limit"]) && $_POST["server_limit"] >= 0 && is_numeric($_POST['server_limit'])) {
                                    $request_server_limit = mysqli_real_escape_string($conn, $_POST['server_limit']);
                                    if (isset($_POST["ports"]) && $_POST["ports"] >= 0 && is_numeric($_POST['ports'])) {
                                        $request_ports = mysqli_real_escape_string($conn, $_POST['ports']);
                                        if (isset($_POST["databases"]) && $_POST["databases"] >= 0 && is_numeric($_POST['databases'])) {
                                            $request_databases = mysqli_real_escape_string($conn, $_POST['databases']);
                                            if (isset($_POST["backups"]) && $_POST["backups"] >= 0 && is_numeric($_POST['backups'])) {
                                                $request_backups = mysqli_real_escape_string($conn, $_POST['backups']);
                                                $new_coins = $userdb_coins + $request_coins;
                                                $new_ram = $userdb_ram + $request_ram;
                                                $new_disk = $userdb_disk + $request_disk;
                                                $new_cpu = $userdb_cpu + $request_cpu;
                                                $new_server_limit = $userdb_server_limit + $request_server_limit;
                                                $new_ports = $userdb_ports + $request_ports;
                                                $new_databases = $userdb_databases + $request_databases;
                                                $new_backups = $userdb_backups + $request_backups;
                                                $conn->query("UPDATE `mythicaldash_users` SET `coins` = '$new_coins' WHERE `mythicaldash_users`.`email` = '$email';");
                                                $conn->query("UPDATE `mythicaldash_users` SET `ram` = '$new_ram' WHERE `mythicaldash_users`.`email` = '$email';");
                                                $conn->query("UPDATE `mythicaldash_users` SET `disk` = '$new_disk' WHERE `mythicaldash_users`.`email` = '$email';");
                                                $conn->query("UPDATE `mythicaldash_users` SET `cpu` = '$new_cpu' WHERE `mythicaldash_users`.`email` = '$email';");
                                                $conn->query("UPDATE `mythicaldash_users` SET `server_limit` = '$new_server_limit' WHERE `mythicaldash_users`.`email` = '$email';");
                                                $conn->query("UPDATE `mythicaldash_users` SET `ports` = '$new_ports' WHERE `mythicaldash_users`.`email` = '$email';");
                                                $conn->query("UPDATE `mythicaldash_users` SET `databases` = '$new_databases' WHERE `mythicaldash_users`.`email` = '$email';");
                                                $conn->query("UPDATE `mythicaldash_users` SET `backups` = '$new_backups' WHERE `mythicaldash_users`.`email` = '$email';");
                                                $conn->close();
                                                $rsp = array(
                                                    "code" => 200,
                                                    "error" => null,
                                                    "message" => "We updated the resources for " . $userdb['username'],
                                                );
                                                http_response_code(200);
                                                die(json_encode($rsp, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                                            } else {
                                                $rsp = array(
                                                    "code" => 400,
                                                    "error" => "Bad request syntax",
                                                    "message" => "Backups is required, but not provided or wrong value."
                                                );
                                                http_response_code(400);
                                                die(json_encode($rsp, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                                            }
                                        } else {
                                            $rsp = array(
                                                "code" => 400,
                                                "error" => "Bad request syntax",
                                                "message" => "Databases is required, but not provided or wrong value."
                                            );
                                            http_response_code(400);
                                            die(json_encode($rsp, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                                        }
                                    } else {
                                        $rsp = array(
                                            "code" => 400,
                                            "error" => "Bad request syntax",
                                            "message" => "Ports is required, but not provided or wrong value."
                                        );
                                        http_response_code(400);
                                        die(json_encode($rsp, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                                    }
                                } else {
                                    $rsp = array(
                                        "code" => 400,
                                        "error" => "Bad request syntax",
                                        "message" => "Server Limit is required, but not provided or wrong value."
                                    );
                                    http_response_code(400);
                                    die(json_encode($rsp, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                                }
                            } else {
                                $rsp = array(
                                    "code" => 400,
                                    "error" => "Bad request syntax",
                                    "message" => "Cpu is required, but not provided or wrong value."
                                );
                                http_response_code(400);
                                die(json_encode($rsp, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                            }
                        } else {
                            $rsp = array(
                                "code" => 400,
                                "error" => "Bad request syntax",
                                "message" => "Disk is required, but not provided or wrong value."
                            );
                            http_response_code(400);
                            die(json_encode($rsp, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                        }
                    } else {
                        $rsp = array(
                            "code" => 400,
                            "error" => "Bad request syntax",
                            "message" => "Ram is required, but not provided or wrong value."
                        );
                        http_response_code(400);
                        die(json_encode($rsp, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                    }
                } else {
                    $rsp = array(
                        "code" => 400,
                        "error" => "Bad request syntax",
                        "message" => "Coins is required, but not provided or wrong value."
                    );
                    http_response_code(400);
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
        ErrorHandler::Critical("User Resources Add ",$e);
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