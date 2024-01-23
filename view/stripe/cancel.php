<?php
use MythicalDash\ErrorHandler;
use MythicalDash\SettingsManager;


include(__DIR__ . '/../requirements/page.php');
if (SettingsManager::getSetting("enable_stripe") == "false") {
    header('location: /');
}
try {
    if (isset($_GET['id']) && !$_GET['id'] == "") {
        $user_query = "SELECT * FROM mythicaldash_payments WHERE code = ?";
        $stmt = mysqli_prepare($conn, $user_query);
        mysqli_stmt_bind_param($stmt, "s", $_GET['id']);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if (mysqli_num_rows($result) > 0) {
            $paymentsdb = $conn->query("SELECT * FROM mythicaldash_payments WHERE code = '" . mysqli_real_escape_string($conn, $_GET['id']) . "'")->fetch_array();
            if ($paymentsdb['status'] == "pending") {
                if ($paymentsdb['ownerkey'] == $_COOKIE['token']) {
                    $code = mysqli_real_escape_string($conn, $_GET['id']);
                    $conn->query("UPDATE `mythicaldash_payments` SET `status` = 'paid' WHERE `code` = '".mysqli_real_escape_string($conn,$code)."'");
                    header('location: /user/payments?s='.$lang['payment_request_cancel']);
                    $conn->close();
                    die();
                } else {
                    header('location: /user/payments?e='.$lang['you_not_paid_for_this']);
                    $conn->close();
                    die();
                }
            } else {
                header('location: /user/payments?e='.$lang['code_not_valid']);
                $conn->close();
                die();
            }
        } else {
            header('location: /user/payments?e='.$lang['code_not_valid']);
            $conn->close();
            die();
        }
    } else {
        header('location: /user/payments?e='.$lang['code_not_valid']);
        die();
    }
} catch (Exception $e) {
    header("location: /user/payments?e=".$lang['login_error_unknown'].$e->getMessage() .'');
    ErrorHandler::Error("Coins ",$e);
    die();
}
?>