<?php
use MythicalDash\ErrorHandler;
use MythicalDash\SettingsManager;
use MythicalDash\Encryption;

include(__DIR__ . '/../requirements/page.php');
if (SettingsManager::getSetting("enable_stripe") == "false") {
    header('location: /');
}
try {
    if (isset($_GET['paypal']) && !$_GET['paypal'] == "") {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $coins = mysqli_real_escape_string($conn, $_POST['coins']);
            $status = mysqli_real_escape_string($conn, $_POST['status']);
            $payment_id = mysqli_real_escape_string($conn, $_POST['payment_id']);
            $date = mysqli_real_escape_string($conn, $_POST['date']);

            $conn->query("INSERT INTO `mythicaldash_payments` (`code`, `coins`, `ownerkey`, `getaway`, `status`) VALUES ('" . mysqli_real_escape_string($conn, $payment_id) . "', '" . mysqli_real_escape_string($conn, $coins) . "', '" . mysqli_real_escape_string($conn, $_COOKIE['token']) . "', 'paypal', 'paid');");
            $update_coins = $session->getUserInfo('coins') + $coins;
            $conn->query("UPDATE `mythicaldash_users` SET `coins` = '" . mysqli_real_escape_string($conn, $update_coins) . "' WHERE `mythicaldash_users`.`api_key` = '" . mysqli_real_escape_string($conn, $_COOKIE['token']) . "'");
            $conn->close();
            http_response_code(201);
            die();
        } else {
            header('location: /dashboard?e=Nice try kiddo but nahh!');
            die();
        }
    }
    if (isset($_GET['code']) && !$_GET['code'] == "") {
        $user_query = "SELECT * FROM mythicaldash_payments WHERE code = ?";
        $stmt = mysqli_prepare($conn, $user_query);
        mysqli_stmt_bind_param($stmt, "s", $_GET['code']);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if (mysqli_num_rows($result) > 0) {
            $paymentsdb = $conn->query("SELECT * FROM mythicaldash_payments WHERE code = '" . mysqli_real_escape_string($conn, $_GET['code']) . "'")->fetch_array();
            if ($paymentsdb['status'] == "pending") {
                if ($paymentsdb['ownerkey'] == $_COOKIE['token']) {
                    $code = mysqli_real_escape_string($conn, $_GET['code']);
                    $conn->query("UPDATE `mythicaldash_payments` SET `status` = 'paid' WHERE `code` = '" . mysqli_real_escape_string($conn, $code) . "'");
                    $coins = $session->getUserInfo('coins') + $paymentsdb['coins'];
                    $conn->query("UPDATE `mythicaldash_users` SET `coins` = '" . mysqli_real_escape_string($conn, $coins) . "' WHERE `mythicaldash_users`.`api_key` = '" . mysqli_real_escape_string($conn, $_COOKIE['token']) . "'");
                    header('location: /user/payments?s=Thanks for buying from ' . SettingsManager::getSetting("name"));
                    $conn->close();
                    die();
                } else {
                    header('location: /user/payments?e=' . $lang['you_not_paid_for_this']);
                    $conn->close();
                    die();
                }
            } else {
                header('location: /user/payments?e=' . $lang['already_claimed']);
                $conn->close();
                die();
            }
        } else {
            header('location: /user/payments?e=' . $lang['error_not_found_in_database']);
            $conn->close();
            die();
        }
    } else {
        header('location: /user/payments');
        die();
    }
} catch (Exception $e) {
    header("location: /user/payments?e=" . $lang['login_error_unknown'] . $e->getMessage() . '');
    ErrorHandler::Error("Coins ", $e);
    die();
}
?>