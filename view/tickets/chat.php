<?php
include(__DIR__ . '/../requirements/page.php');

if (isset($_GET['ticketuuid']) && $_GET['ticketuuid'] !== "") {
    $ticketquery_db = "SELECT * FROM mythicaldash_tickets WHERE ticketuuid = ?";
    $stmt = mysqli_prepare($conn, $ticketquery_db);

    if (!$stmt) {
        header('location: /help-center/tickets?e=Prepare failed: ' . mysqli_error($conn));
        die('Prepare failed: ' . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, "s", $_GET['ticketuuid']);
    mysqli_stmt_execute($stmt);

    if (mysqli_stmt_error($stmt)) {
        header('location: /help-center/tickets?e=Execute failed: ' . mysqli_stmt_error($stmt));
        die('Execute failed: ' . mysqli_stmt_error($stmt));
    }

    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        $ticket_db = mysqli_fetch_assoc($result);
    } else {
        header('location: /help-center/tickets?e=We can\'t find this ticket in the database');
        die();
    }

    mysqli_stmt_close($stmt);
} else {
    header('location: /help-center/tickets?e=We can\'t find this ticket in the database');
    die();
}
?>

<!DOCTYPE html>
<html lang="en" class="light-style layout-navbar-fixed layout-menu-fixed" dir="ltr" data-theme="theme-semi-dark"
    data-assets-path="<?= $appURL ?>/assets/" data-template="vertical-menu-template">

<head>
    <?php include(__DIR__ . '/../requirements/head.php'); ?>
    <title>
        <?= $settings['name'] ?> | Tickets
    </title>
    <link rel="stylesheet" href="../../assets/vendor/css/pages/app-chat.css" />

</head>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <?php include(__DIR__ . '/../components/sidebar.php') ?>
            <div class="layout-page">
                <?php include(__DIR__ . '/../components/navbar.php') ?>
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Help-Center / Tickets /</span>
                            View</h4>
                        <?php include(__DIR__ . '/../components/alert.php') ?>
                        <div class="card">
                            <div class="card-header text-center">
                                <h1>Ticket #
                                    <?= $ticket_db['id']; ?> |
                                    <?= $ticket_db['status'] ?>
                                </h1>

                            </div>
                            <div class="card-body">
                                <div class="card-header">
                                    Messages:
                                </div>
                                <ul class="list-group list-group-flush">
                                    <?php
                                    $ticket_id = mysqli_real_escape_string($conn, $_GET['ticketuuid']);
                                    $query = "SELECT * FROM mythicaldash_tickets_messages WHERE ticketuuid='$ticket_id' ORDER BY created ASC";
                                    $result = mysqli_query($conn, $query);
                                    while ($row = mysqli_fetch_array($result)) {
                                        echo '<li class="list-group-item">';
                                        echo '<p>' . $row['message'] . '</p>';
                                        echo '<p class="text-muted">Sent by: <code>' . $row['userkey'] . '</code> at ' . $row['created'] . '</p>';
                                        echo '</li>';
                                    }
                                    ?>
                                </ul>
                            </div>
                            <div class="card-body">
                                <form method="post">
                                    <div class="form-group">
                                        <label for="content">Message</label>
                                        <textarea class="form-control" name="content" id="content" rows="3"></textarea>
                                    </div>
                                    <?php
                                    if ($ticket_db['status'] == "closed") {
                                        ?>
                                        <button type="submit" name="reopen_ticket" class="btn btn-primary">Open
                                            again</button>
                                        <button type="submit" name="delete_ticket" class="btn btn-danger">Delete
                                            ticket</button>
                                        <?php
                                    } else if ($ticket_db['status'] == "open") {
                                        ?>
                                            <button type="submit" name="add_message" class="btn btn-primary">Add
                                                Message</button>
                                            <button type="submit" name="close_ticket" class="btn btn-danger">Close
                                                ticket</button>
                                        <?php
                                    } else {

                                    }
                                    ?>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php include(__DIR__ . '/../components/footer.php') ?>
                    <div class="content-backdrop fade"></div>
                    <?php include(__DIR__ . '/../components/modals.php') ?>
                </div>
            </div>
        </div>
        <div class="layout-overlay layout-menu-toggle"></div>
        <div class="drag-target"></div>
    </div>
    <?php include(__DIR__ . '/../requirements/footer.php') ?>
    <script src="<?= $appURL ?>/assets/js/app-chat.js"></script>
</body>

</html>