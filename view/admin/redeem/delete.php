<?php
include(__DIR__ . '/../../requirements/page.php');
include(__DIR__ . '/../../requirements/admin.php');

if (isset($_GET['code'])) {
    if (!$_GET['code'] == "") {
        $user_query = "SELECT * FROM mythicaldash_redeem WHERE code = ?";
        $stmt = mysqli_prepare($conn, $user_query);
        mysqli_stmt_bind_param($stmt, "s", $_GET['code']);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if (mysqli_num_rows($result) > 0) {
            $conn->query("DELETE FROM `mythicaldash_redeem` WHERE `mythicaldash_redeem`.`code` = '".mysqli_real_escape_string($conn,$_GET['code'])."';");
            header('location: /admin/redeem?s=We deleted the coupon code from the database');
        } else {
            header("location: /admin/redeem?e=Can't find a coupon code in the database.");
            exit();
        }
    } else {
        header("location: /admin/redeem?e=Can't find a coupon code in the database.");
        exit();
    }

} else {
    header("location: /admin/redeem?e=Can't find a coupon code in the database.");
    exit();
}
?>