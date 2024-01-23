<?php
use MythicalDash\Encryption;
use MythicalDash\SettingsManager;
use MythicalDash\Database\Connect;
include(__DIR__ . '/../requirements/page.php');

if (isset($_GET['ticketuuid']) && $_GET['ticketuuid'] !== "") {
    $ticketquery_db = "SELECT * FROM mythicaldash_tickets WHERE ticketuuid = ?";
    $stmt = mysqli_prepare($conn, $ticketquery_db);

    if (!$stmt) {
        header("location: /help-center/tickets?e=".$lang['login_error_unknown']);
        die();
    }

    mysqli_stmt_bind_param($stmt, "s", $_GET['ticketuuid']);
    mysqli_stmt_execute($stmt);

    if (mysqli_stmt_error($stmt)) {
        header("location: /help-center/tickets?e=".$lang['login_error_unknown']);
        mysqli_stmt_close($stmt);
        $conn->close();
        die();
    }

    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        $ticket_db = mysqli_fetch_assoc($result);
        if ($ticket_db['status'] == "deleted") {
            if ($session->getUserInfo("role") == "User" || $session->getUserInfo("role") == "Support") {
                header('location: /help-center/tickets?e='.$lang['ticket_deleted']);
                $conn->close();
                die();
            }
        }
    } else {
        header("location: /help-center/tickets?e=".$lang['error_not_found_in_database']);
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
            $tickedusdb = $conn->query("SELECT * FROM mythicaldash_users WHERE api_key = '" . mysqli_real_escape_string($conn,$row['userkey']) . "'")->fetch_array();
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
    header("location: /help-center/tickets?e=".$lang['error_not_found_in_database']);
    die();
}
?>

<!DOCTYPE html>
<html lang="en" class="dark-style layout-navbar-fixed layout-menu-fixed" dir="ltr" data-theme="theme-semi-dark"
    data-assets-path="<?= $appURL ?>/assets/" data-template="vertical-menu-template">

<head>
    <?php include(__DIR__ . '/../requirements/head.php'); ?>
    <title>
        <?= SettingsManager::getSetting("name") ?> - <?= $lang['ticket'] ?>
    </title>
    <link rel="stylesheet" href="../../assets/vendor/css/pages/app-chat.css" />
</head>

<body>
<?php
  if (SettingsManager::getSetting("show_snow") == "true") {
    include(__DIR__ . '/../components/snow.php');
  }
  ?>
    <!--<div id="preloader" class="discord-preloader">
        <div class="spinner"></div>
    </div>-->

    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <?php include(__DIR__ . '/../components/sidebar.php') ?>
            <div class="layout-page">
                <?php include(__DIR__ . '/../components/navbar.php') ?>
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light"><?= $lang['help_center']?> / <?= $lang['ticket']?></span></h4>
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
                                        href="/help-center/tickets/reopen?ticketuuid=<?= $_GET['ticketuuid'] ?>"><?= $lang['reopen_ticket']?></a>
                                    <a href="/help-center/tickets/delete?ticketuuid=<?= $_GET['ticketuuid'] ?>"
                                        class="btn btn-danger"><?= $lang['delete_ticket']?></a>
                                    <a class="btn btn-secondary"
                                        href="?ticketuuid=<?= $_GET['ticketuuid'] ?>&export=true"><?= $lang['export_ticket']?></a>
                                </div>
                            </div>
                            <?php
                        } else if ($ticket_db['status'] == "open") {
                            ?>
                                <div class="row">
                                    <div class="col-md-12 text-start">
                                        <button type="button" data-bs-toggle="modal" data-bs-target="#replyticket"
                                            class="btn btn-primary"><?= $lang['reply_ticket']?></button>
                                        <a href="/help-center/tickets/close?ticketuuid=<?= $_GET['ticketuuid'] ?>"
                                            class="btn btn-danger"><?= $lang['close_ticket']?></a>
                                        <a class="btn btn-secondary"
                                            href="?ticketuuid=<?= $_GET['ticketuuid'] ?>&export=true"><?= $lang['export_ticket']?></a>
                                    </div>
                                </div>
                            <?php
                        } else if ($ticket_db['status'] == "deleted") {
                            ?>
                                    <div class="row">
                                        <div class="col-md-12 text-start">
                                            <a href="/admin/tickets" class="btn btn-danger"><?= $lang['back']?></a>
                                            <a class="btn btn-secondary"
                                                href="?ticketuuid=<?= $_GET['ticketuuid'] ?>&export=true"><?= $lang['export_ticket']?></a>
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
                                        <p><?= $lang['ticket_open_msg_1']?>
                                            <?= SettingsManager::getSetting("name") ?>.<br><?= $lang['ticket_open_msg_2']?>
                                            <a href="<?= SettingsManager::getSetting("discord_invite") ?>">
                                            <?= SettingsManager::getSetting("discord_invite") ?></a><br><br>

                                        </p>
                                        <hr>
                                        <p>
                                            <?= $lang['ticket_subject']?>
                                            <?= $ticket_db['subject'] ?><br>
                                            <?= $lang['ticket_status']?>
                                            <?= $ticket_db['status'] ?><br>
                                            <?= $lang['ticket_priority']?>
                                            <?= $ticket_db['priority'] ?><br>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <br>
                            <div class="card ticket-reply">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-6 user">
                                            <div class="d-flex align-items-center">
                                                <span class="name">
                                                    <a href="/user/profile?id=<?= Connect::getUserInfo($ticket_db['ownerkey'], "id") ?>"><?= Connect::getUserInfo($ticket_db['ownerkey'], "username") ?></a>
                                                    <span class="badge bg-<?php if (Connect::getUserInfo($ticket_db['ownerkey'], "role") == "Administrator") {
                                                        echo 'danger';
                                                    } else {
                                                        echo 'success';
                                                    } ?> requestor-type ms-2">
                                                        <?= Connect::getUserInfo($ticket_db['ownerkey'], "role") ?>
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
                                            <p>
                                                <?= $ticket_db['description'] ?>
                                            </p>
                                        </div>
                                    </div>
                                    <?php
                                    if (!$ticket_db['attachment'] == "") {
                                        ?>
                                        <hr>
                                        <a href="<?= $ticket_db['attachment'] ?>"><small>
                                                <?= $ticket_db['attachment'] ?>
                                            </small></a>
                                        <?php
                                    }
                                    ?>
                                </div>
                            </div>
                        <?php
                        $ticket_id = mysqli_real_escape_string($conn, $_GET['ticketuuid']);
                        $query = "SELECT * FROM mythicaldash_tickets_messages WHERE ticketuuid='".mysqli_real_escape_string($conn,$ticket_id)."' ORDER BY created ASC";
                        $result = mysqli_query($conn, $query);
                        while ($row = mysqli_fetch_array($result)) {
                            $tickedusdb = $conn->query("SELECT * FROM mythicaldash_users WHERE api_key = '" . mysqli_real_escape_string($conn,$row['userkey']) . "'")->fetch_array();

                            ?>
                            <br>
                            <div class="card ticket-reply">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-6 user">
                                            <div class="d-flex align-items-center">
                                                <span class="name">
                                                    <a href="/user/profile?id=<?= Connect::getUserInfo($ticket_db['ownerkey'], "id") ?>"><?= $tickedusdb['username'] ?></a>
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
                                        <a href="<?= $row['attachment'] ?>"><small>
                                                <?= $row['attachment'] ?>
                                            </small></a>
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
                    <!-- START OF #replyticket -->
                    <div class="modal fade" id="replyticket" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-simple modal-edit-user">
                            <div class="modal-content p-3 p-md-5">
                                <div class="modal-body">
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                    <div class="text-center mb-4">
                                        <h3 class="mb-2"><?= $lang['reply_ticket']?></h3>
                                        <p class="text-muted"><?= $lang['reply_ticket_2']?></p>
                                    </div>
                                    <form method="GET" action="/help-center/tickets/reply" class="row g-3">
                                        <div class="col-12">
                                            <label class="form-label" for="username"><?= $lang['username']?></label>
                                            <input type="text" id="username" name="username" class="form-control"
                                                value="<?= $session->getUserInfo("username") ?>" disabled="" />
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label" for="message"><?= $lang['ticket_message']?></label>
                                            <textarea required class="form-control" name="message" id="message" rows="3"
                                                placeholder="Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book."></textarea>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label" for="attachment"><?= $lang['ticket_attachment']?></label>
                                            <input type="text" id="attachment" name="attachment" class="form-control"
                                                placeholder="https://i.imgur.com/yed5Zfk.gif" />
                                        </div>
                                        <input type="hidden" name="ticketuuid"
                                            value="<?= mysqli_real_escape_string($conn, $_GET['ticketuuid']) ?>">
                                        <input type="hidden" name="userkey"
                                            value="<?= $session->getUserInfo("api_key") ?>">

                                        <div class="col-12 text-center">
                                            <button type="submit" class="btn btn-primary me-sm-3 me-1"><?= $lang['save']?></button>
                                            <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal"
                                                aria-label="Close">
                                                <?= $lang['back']?>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- END OF #replyticket -->
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