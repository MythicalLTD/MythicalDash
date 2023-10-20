<?php
include(__DIR__ . "/../base.php");
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        if (isset($_GET['page']) && is_numeric($_GET['page'])) {
            $page = intval($_GET['page']);
            if ($page < 1) {
                $page = 1;
            }
        } else {
            $page = 1;
        }

        $usersPerPage = 15;
        $offset = ($page - 1) * $usersPerPage;

        $sql = "SELECT * FROM mythicaldash_users";
        $totalUsersResult = $conn->query($sql);
        $totalUsers = $totalUsersResult->num_rows;
        $totalPages = ceil($totalUsers / $usersPerPage);

        $sql = "SELECT * FROM mythicaldash_users LIMIT $usersPerPage OFFSET $offset";
        $result = $conn->query($sql);

        if ($result) {
            if ($result->num_rows > 0) {
                $users = array();
                while ($row = $result->fetch_assoc()) {
                    $users[] = $row;
                }
                $conn->close();

                $response = array(
                    "page" => $page,
                    "message" => null,
                    "users" => $users,
                    "total_pages" => $totalPages
                );

                http_response_code(200);
                die(json_encode($response));
            } else {
                $conn->close();
                $rsp = array(
                    "code" => 403,
                    "error" => "The server understood the request, but it refuses to authorize it.",
                    "message" => "No users found for this page."
                );
                http_response_code(403);
                die(json_encode($rsp, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            }
        } else {
            $conn->close();
            $rsp = array(
                "code" => 500,
                "error" => "The server encountered a situation it doesn't know how to handle.",
                "message" => "We are sorry, but our server can't handle this request. Please do not try again!"
            );
            http_response_code(500);
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