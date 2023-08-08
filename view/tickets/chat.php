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
    <style>
        .badge.requestor-type {
            font-size: 10px;
            vertical-align: middle;
            background-color: #28a745;
        }
    </style>
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
                        <div class="card ticket-reply">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-6 user">
                                        <div class="d-flex align-items-center">
                                            <span class="name">
                                                NaysKutzu
                                                <span class="badge bg-danger requestor-type ms-2">
                                                    Owner
                                                </span>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col-6 text-end date">
                                        <small>08/08/2023 (20:50)</small>
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-md-12 message">
                                        <p>daddy</p>
                                        <hr>
                                        <p><small>IP Address: 178.165.161.181</small></p>
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
                                                TyeIsGay
                                                <span class="badge bg-sucess requestor-type ms-2">
                                                    Member
                                                </span>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col-6 text-end date">
                                        <small>08/08/2023 (23:10)</small>
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-md-12 message">
                                        <p>Please offer me cook</p>
                                        <hr>
                                        <p><small>IP Address: 247.98.215.67</small></p>
                                    </div>
                                </div>
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