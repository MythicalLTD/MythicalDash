<?php
include(__DIR__ . '/../requirements/page.php');
if (isset($_GET['message']) && isset($_GET['ticketuuid']) && isset($_GET['userkey']) && !$_GET['userkey'] == "" && !$_GET['ticketuuid'] == "" && !$_GET['message'] == "") {
    $user_query = "SELECT * FROM mythicaldash_tickets WHERE ticketuuid = ?";
    $stmt = mysqli_prepare($conn, $user_query);
    mysqli_stmt_bind_param($stmt, "s", $_GET['ticketuuid']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if (mysqli_num_rows($result) > 0) {
        $conn->query("INSERT INTO `mythicaldash_tickets_messages` (`ticketuuid`, `userkey`, `message`, `attachment`) VALUES ('" . mysqli_real_escape_string($conn, $_GET['ticketuuid']) . "', '" . mysqli_real_escape_string($conn, $_GET['userkey']) . "', '" . mysqli_real_escape_string($conn, $_GET['message']) . "', '" . mysqli_real_escape_string($conn, $_GET['attachment']) . "');");
        $conn->close();
        header('location: /help-center/tickets/view?ticketuuid=' . $_GET['ticketuuid']);
        die();
    } else {
        header('location: /help-center/tickets?e=We can\'t find this ticket in the database');
        die();
    }
} else {
    header('location: /help-center/tickets?e=Can\'t find the ticket in the database');
    die();
}
?>