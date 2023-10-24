<?php
include(__DIR__ . '/../requirements/page.php');

if (isset($_GET['ticketuuid']) && !$_GET['ticketuuid'] == "") {
    $user_query = "SELECT * FROM mythicaldash_tickets WHERE ticketuuid = ?";
    $stmt = mysqli_prepare($conn, $user_query);
    mysqli_stmt_bind_param($stmt, "s", $_GET['ticketuuid']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if (mysqli_num_rows($result) > 0) {
        $conn->query("UPDATE `mythicaldash_tickets` SET `status` = 'open' WHERE `mythicaldash_tickets`.`ticketuuid` = '" . mysqli_real_escape_string($conn, $_GET['ticketuuid']) . "';");
        $conn->close();
        header('location: /help-center/tickets');
        die();
    } else {
        header("location: /help-center/tickets?e=We can't find this ticket in the database");
        $conn->close();
        die();
    }
} else {
    header("location: /help-center/tickets?e=We can't find this ticket in the database");
    die();
}
?>