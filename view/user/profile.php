<?php
use MythicalDash\SettingsManager;

include(__DIR__ . '/../requirements/page.php');
if (isset($_GET['id']) && !$_GET['id'] == "") {
    $user_query = "SELECT * FROM mythicaldash_users WHERE id = ?";
    $stmt = mysqli_prepare($conn, $user_query);
    mysqli_stmt_bind_param($stmt, "s", $_GET['id']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if (mysqli_num_rows($result) > 0) {
        $udb = $conn->query("SELECT * FROM mythicaldash_users WHERE id = '" . mysqli_real_escape_string($conn, $_GET['id']) . "'")->fetch_array();

    } else {
        header("location: /dashboard?e=We cannot find this user in the database");
        die();
    }
} else {
    header("location: /dashboard?e=We cannot find this user in the database");
    die();
}
?>
<!DOCTYPE html>
<html lang="en" class="dark-style layout-navbar-fixed layout-menu-fixed" dir="ltr" data-theme="theme-semi-dark"
    data-assets-path="<?= $appURL ?>/assets/" data-template="vertical-menu-template">

<head>
    <?php include(__DIR__ . '/../requirements/head.php'); ?>
    <title>
        <?= SettingsManager::getSetting("name") ?> - Profile
    </title>
    <link rel="stylesheet" href="/assets/vendor/css/pages/page-profile.css" />
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
                        <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Users /</span> Profile</h4>
                        <?php include(__DIR__ . '/../components/alert.php') ?>

                        <div class="row">
                            <div class="col-12">
                                <div class="card mb-4">
                                    <div class="user-profile-header-banner">
                                        <img src="<?= $udb['banner'] ?>" alt="Banner image" class="rounded-top" />
                                    </div>
                                    <div
                                        class="user-profile-header d-flex flex-column flex-sm-row text-sm-start text-center mb-4">
                                        <div class="flex-shrink-0 mt-n2 mx-sm-0 mx-auto">
                                            <img src="<?= $udb['avatar'] ?>" alt="user image"
                                                class="d-block h-auto ms-0 ms-sm-4 rounded user-profile-img" />
                                        </div>
                                        <div class="flex-grow-1 mt-3 mt-sm-5">
                                            <div
                                                class="d-flex align-items-md-end align-items-sm-start align-items-center justify-content-md-between justify-content-start mx-4 flex-md-row flex-column gap-4">
                                                <div class="user-profile-info">
                                                    <h4>
                                                        <?= $udb['username'] ?>
                                                    </h4>
                                                    <ul
                                                        class="list-inline mb-0 d-flex align-items-center flex-wrap justify-content-sm-start justify-content-center gap-2">
                                                        <li class="list-inline-item"><i class="ti ti-star"></i>
                                                            <?= $udb['role'] ?>
                                                        </li>
                                                        <li class="list-inline-item"><i class="ti ti-cash"></i>
                                                            <?= $udb['coins'] ?>
                                                        </li>
                                                        <li class="list-inline-item"><i class="ti ti-calendar"></i>
                                                            Joined
                                                            <?= $udb['registred'] ?>
                                                        </li>
                                                    </ul>
                                                </div>
                                                <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                                    data-bs-target="#giftCoins">
                                                    <i class="ti ti-gift me-1"></i>Gift Coins
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="ads">
                            <?php
                            if (SettingsManager::getSetting("enable_ads") == "true") {
                                ?>
                                <br>
                                <?= SettingsManager::getSetting("ads_code") ?>
                                <br>
                                <?php
                            }
                            ?>
                        </div>
                    </div>
                    <?php include(__DIR__ . '/../components/footer.php') ?>
                    <div class="content-backdrop fade"></div>
                    <div class="modal fade" id="giftCoins" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-simple modal-edit-user">
                            <div class="modal-content p-3 p-md-5">
                                <div class="modal-body">
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                    <div class="text-center mb-4">
                                        <h3 class="mb-2">Gift user coins!</h3>
                                        <p class="text-muted">Remember, once you send a user coins, you cannot take this
                                            action back! Please do not open a ticket to get your coins back! We won't
                                            help you!!!</p>
                                    </div>
                                    <form method="GET" action="/user/gift" class="row g-3">
                                        <input type="hidden" name="userid" value="<?= $_GET['id'] ?>">
                                        <div class="col-12">
                                            <label class="form-label" for="coins">Coins</label>
                                            <input type="number" id="coins" name="coins" class="form-control"
                                                placeholder="" required />
                                        </div>
                                        <div class="col-12 text-center">
                                            <button type="submit" name="key" value="<?= $_COOKIE['token'] ?>"
                                                class="btn btn-primary me-sm-3 me-1">Send Coins</button>
                                            <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal"
                                                aria-label="Close">Cancel </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="layout-overlay layout-menu-toggle"></div>
        <div class="drag-target"></div>
    </div>
    <?php include(__DIR__ . '/../requirements/footer.php') ?>
    <!-- Page JS -->
    <script src="<?= $appURL ?>/assets/js/pages-account-settings-account.js"></script>
    <script src="<?= $appURL ?>/assets/js/pages-profile.js"></script>
</body>

</html>