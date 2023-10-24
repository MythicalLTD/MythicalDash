<?php
use MythicalDash\Encryption;
use MythicalDash\SettingsManager;
include(__DIR__ . '/../requirements/page.php');

if (isset($_GET['ticketuuid']) && $_GET['ticketuuid'] !== "") {
    $ticketquery_db = "SELECT * FROM mythicaldash_tickets WHERE ticketuuid = ?";
    $stmt = mysqli_prepare($conn, $ticketquery_db);

    if (!$stmt) {
        header("location: /help-center/tickets?e=Sorry, but we can't talk with the backend at this moment, please try again!");
        die();
    }

    mysqli_stmt_bind_param($stmt, "s", $_GET['ticketuuid']);
    mysqli_stmt_execute($stmt);

    if (mysqli_stmt_error($stmt)) {
        header("location: /help-center/tickets?e=Sorry, but we can't talk with the backend at this moment, please try again!");
        mysqli_stmt_close($stmt);
        $conn->close();
        die();
    }

    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        $ticket_db = mysqli_fetch_assoc($result);
        if ($ticket_db['status'] == "deleted") {
            if ($session->getUserInfo("role") == "User" || $session->getUserInfo("role") == "Support") {
                header('location: /help-center/tickets?e=We are sorry, but this ticket is archived. You can\'t access it anymore!');
                $conn->close();
                die();
            }
        }
    } else {
        header('location: /help-center/tickets?e=We can\'t find this ticket in the database');
        mysqli_stmt_close($stmt);
        $conn->close();
        die();
    }
    if (isset($_GET['export']) && $_GET['export'] === "true") {
        $filename = SettingsManager::getSetting("name") . '_ticket_export_' . $_GET['ticketuuid'] . '.txt';
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Type: text/plain');

        ob_start();
        echo "Ticket Subject: " . $ticket_db['subject'] . "\r\n";
        echo "Ticket Status: " . $ticket_db['status'] . "\r\n";
        echo "Ticket Priority: " . $ticket_db['priority'] . "\r\n";
        echo "Ticket Description: " . $ticket_db['description'] . "\r\n";
        echo "Ticket Attachment: " . $ticket_db['attachment'] . "\r\n";
        echo "Ticket Creation Date: " . $ticket_db['created'] . "\r\n";
        echo "------------------------\r\n";

        $query = "SELECT * FROM mythicaldash_tickets_messages WHERE ticketuuid=?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "s", $_GET['ticketuuid']);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($result)) {
            $tickedusdb = $conn->query("SELECT * FROM mythicaldash_users WHERE api_key = '" . $row['userkey'] . "'")->fetch_array();
            echo "User: " . $tickedusdb['username'] . " " . $tickedusdb['role'] . " (" . $row['created'] . ")\r\n";
            echo "Message: " . $row['message'] . "\r\n";
            if (!empty($row['attachment'])) {
                echo "Attachment: " . $row['attachment'] . "\r\n";
            }
            echo "------------------------\r\n";
        }
        echo "This is an archive of a ticket with the id: " . $_GET['ticketuuid'] . " created on " . SettingsManager::getSetting("name") . "\r\n";
        echo "Archived ticket signed key: " . Encryption::generateticket_key($_GET['ticketuuid']) . "\r\n";
        $exportData = ob_get_clean();
        echo $exportData;
        mysqli_stmt_close($stmt);
        $conn->close();
        die();
    }
} else {
    header('location: /help-center/tickets?e=We can\'t find this ticket in the database');
    die();
}
?>

<!DOCTYPE html>
<html lang="en" class="dark-style layout-navbar-fixed layout-menu-fixed" dir="ltr" data-theme="theme-semi-dark"
    data-assets-path="<?= $appURL ?>/assets/" data-template="vertical-menu-template">

<head>
    <?php include(__DIR__ . '/../requirements/head.php'); ?>
    <title>
        <?= SettingsManager::getSetting("name") ?> - Tickets
    </title>
    <link rel="stylesheet" href="../../assets/vendor/css/pages/app-chat.css" />
</head>

<body>
    <div id="preloader" class="discord-preloader">
        <div class="spinner"></div>
    </div>

    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <?php include(__DIR__ . '/../components/sidebar.php') ?>
            <div class="layout-page">
                <?php include(__DIR__ . '/../components/navbar.php') ?>
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Help-Center / Tickets
                                /</span>
                            View</h4>
                        <?php include(__DIR__ . '/../components/alert.php') ?>
                        <div id="ads">
                            <?php
                            if (SettingsManager::getSetting("enable_ads") == "true") {
                                ?>
                                <?= SettingsManager::getSetting("ads_code") ?>
                                <br>
                                <?php
                            }
                            ?>
                        </div>
                        <?php
                        if ($ticket_db['status'] == "closed") {
                            ?>
                            <div class="row">
                                <div class="col-md-12 text-start">
                                    <a class="btn btn-primary"
                                        href="/help-center/tickets/reopen?ticketuuid=<?= $_GET['ticketuuid'] ?>">Reopen
                                        ticket</a>
                                    <a href="/help-center/tickets/delete?ticketuuid=<?= $_GET['ticketuuid'] ?>"
                                        class="btn btn-danger">Delete Ticket</a>
                                    <a class="btn btn-secondary"
                                        href="?ticketuuid=<?= $_GET['ticketuuid'] ?>&export=true">Export Ticket</a>
                                </div>
                            </div>
                            <?php
                        } else if ($ticket_db['status'] == "open") {
                            ?>
                                <div class="row">
                                    <div class="col-md-12 text-start">
                                        <button type="button" data-bs-toggle="modal" data-bs-target="#replyticket"
                                            class="btn btn-primary">Reply</button>
                                        <a href="/help-center/tickets/close?ticketuuid=<?= $_GET['ticketuuid'] ?>"
                                            class="btn btn-danger">Close Ticket</a>
                                        <a class="btn btn-secondary"
                                            href="?ticketuuid=<?= $_GET['ticketuuid'] ?>&export=true">Export Ticket</a>
                                    </div>
                                </div>
                            <?php
                        } else if ($ticket_db['status'] == "deleted") {
                            ?>
                                    <div class="row">
                                        <div class="col-md-12 text-start">
                                            <a href="/admin/tickets" class="btn btn-danger">Exit</a>
                                            <a class="btn btn-secondary"
                                                href="?ticketuuid=<?= $_GET['ticketuuid'] ?>&export=true">Export Ticket</a>
                                        </div>
                                    </div>
                            <?php
                        }
                        ?>
                        <br>
                        <div class="card ticket-reply">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-6 user">
                                        <div class="d-flex align-items-center">
                                            <span class="name">
                                                System
                                                <span class="badge bg-danger requestor-type ms-2">
                                                    Administrator
                                                </span>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col-6 text-end date">
                                        <small>
                                            <?= $ticket_db['created'] ?>
                                        </small>
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-md-12 message">
                                        <p>Hi, and welcome to
                                            <?= SettingsManager::getSetting("name") ?>.<br>This is an automated message from the
                                            system to inform you that your ticket is now open.<br>Please do not spam any
                                            staff member by any chance; this will not help you get support, and please
                                            be respectful and make sure you read our terms of service and our rules.
                                            <br>If you feel like you need help quickly, make sure to join our community
                                            <a href="<?= SettingsManager::getSetting("discord_invite") ?>"> here</a><br><br>

                                        </p>
                                        <hr>
                                        <p>
                                            Ticket Subject:
                                            <?= $ticket_db['subject'] ?><br>
                                            Ticket Status:
                                            <?= $ticket_db['status'] ?><br>
                                            Ticket Priority:
                                            <?= $ticket_db['priority'] ?><br>
                                            Ticket Description:
                                            <?= $ticket_db['description'] ?><br>
                                            Ticket Attachment:
                                            <?= $ticket_db['attachment'] ?><br>
                                            Ticket Creation Date:
                                            <?= $ticket_db['created'] ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php
                        $ticket_id = mysqli_real_escape_string($conn, $_GET['ticketuuid']);
                        $query = "SELECT * FROM mythicaldash_tickets_messages WHERE ticketuuid='$ticket_id' ORDER BY created ASC";
                        $result = mysqli_query($conn, $query);
                        while ($row = mysqli_fetch_array($result)) {
                            $tickedusdb = $conn->query("SELECT * FROM mythicaldash_users WHERE api_key = '" . $row['userkey'] . "'")->fetch_array();

                            ?>
                            <br>
                            <div class="card ticket-reply">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-6 user">
                                            <div class="d-flex align-items-center">
                                                <span class="name">
                                                    <?= $tickedusdb['username'] ?>
                                                    <span class="badge bg-<?php if ($tickedusdb['role'] == "Administrator") {
                                                        echo 'danger';
                                                    } else {
                                                        echo 'success';
                                                    } ?> requestor-type ms-2">
                                                        <?= $tickedusdb['role'] ?>
                                                    </span>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="col-6 text-end date">
                                            <small>
                                                <?= $row['created'] ?>
                                            </small>
                                        </div>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-md-12 message">
                                            <p>
                                                <?= $row['message'] ?>
                                            </p>
                                        </div>
                                    </div>
                                    <?php
                                    if (!$row['attachment'] == "") {
                                        ?>
                                        <hr>
                                        <p><small>
                                                <?= $row['attachment'] ?>
                                            </small></p>
                                        <?php
                                    }
                                    ?>
                                </div>
                            </div>
                            <?php
                        }
                        ?>

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