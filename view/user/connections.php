<?php
include(__DIR__ . '/../requirements/page.php');
if (isset($_GET['unlink_discord'])) {
    $conn->query("UPDATE `mythicaldash_users` SET `discord_linked` = 'false' WHERE `mythicaldash_users`.`api_key` = '" . mysqli_real_escape_string($conn, $_COOKIE['token']) . "';");
    $conn->close();
    header('location: /user/connections');
}
?>
<!DOCTYPE html>
<html lang="en" class="dark-style layout-navbar-fixed layout-menu-fixed" dir="ltr" data-theme="theme-semi-dark"
    data-assets-path="<?= $appURL ?>/assets/" data-template="vertical-menu-template">

<head>
    <?php include(__DIR__ . '/../requirements/head.php'); ?>
    <title>
        <?= $settings['name'] ?> - Edit
    </title>
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
                        <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Users /</span> Edit</h4>
                        <?php include(__DIR__ . '/../components/alert.php') ?>
                        <div id="ads">
                            <?php
                            if ($settings['enable_ads'] == "true") {
                                ?>
                                <br>
                                <?= $settings['ads_code'] ?>
                                <br>
                                <?php
                            }
                            ?>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <ul class="nav nav-pills flex-column flex-md-row mb-4">
                                    <li class="nav-item">
                                        <a href="/user/edit" class="nav-link"><i class="ti-xs ti ti-users me-1"></i>
                                            Account</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link active" href="/user/connections"><i
                                                class="ti-xs ti ti-link me-1"></i> Connections</a>
                                    </li>
                                </ul>

                                <div class="col-md-6 col-12">
                                    <div class="card">
                                        <h5 class="card-header pb-1">Linked Accounts</h5>
                                        <div class="card-body">
                                            <p>Here you can link your accounts for easy login</p>
                                            <div class="d-flex mb-3">
                                                <div class="flex-shrink-0">
                                                    <img src="https://assets-global.website-files.com/6257adef93867e50d84d30e2/636e0a6a49cf127bf92de1e2_icon_clyde_blurple_RGB.png"
                                                        alt="discord" class="me-3" height="38">
                                                </div>
                                                <div class="flex-grow-1 row">
                                                    <div class="col-sm-7">
                                                        <h6 class="mb-0">Discord</h6>
                                                        <?php
                                                        if (!$settings['discord_clientid'] == "" && !$settings['discord_clientsecret'] == "") {
                                                            if ($userdb['discord_linked'] == "true") {
                                                                ?>
                                                                <small class="text-muted">
                                                                    <?= $userdb['discord_username'] ?>
                                                                </small>
                                                                <?php
                                                            } else {
                                                                ?>
                                                                <small class="text-muted">Not Connected</small>
                                                                <?php
                                                            }
                                                        } else {
                                                            ?>
                                                            <small class="text-muted">Disabled by host</small>
                                                            <?php
                                                        }
                                                        ?>
                                                    </div>
                                                    <?php
                                                    if (!$settings['discord_clientid'] == "" && !$settings['discord_clientsecret'] == "") {
                                                        if ($userdb['discord_linked'] == "true") {
                                                            ?>
                                                            <div class="col-sm-5 text-sm-end mt-sm-0 mt-2">
                                                                <a href="/user/connections?unlink_discord=yes"
                                                                    class="btn btn-label-danger btn-icon waves-effect"><i
                                                                        class="ti ti-trash ti-sm"></i></a>
                                                            </div>
                                                            <?php
                                                        } else {
                                                            ?>
                                                            <div class="col-sm-5 text-sm-end mt-sm-0 mt-2">
                                                                <a href="/auth/link/discord"
                                                                    class="btn btn-label-secondary btn-icon waves-effect">
                                                                    <i class="ti ti-link ti-sm"></i>
                                                                </a>
                                                            </div>
                                                            <?php
                                                        }
                                                    } else {

                                                    }
                                                    ?>
                                                </div>
                                            </div>
                                            <div class="d-flex mb-3">
                                                <div class="flex-shrink-0">
                                                    <img src="https://assets.stickpng.com/images/5847f98fcef1014c0b5e48c0.png"
                                                        alt="github" class="me-3" height="50">
                                                </div>
                                                <div class="flex-grow-1 row">
                                                    <div class="col-sm-7">
                                                        <h6 class="mb-0">GitHub</h6>
                                                        <small class="text-muted">Soon</small>
                                                    </div>

                                                    <!--<div class="col-sm-5 text-sm-end mt-sm-0 mt-2">
                                                        <button class="btn btn-label-secondary btn-icon waves-effect">
                                                            <i class="ti ti-link ti-sm"></i>
                                                        </button>
                                                    </div>-->
                                                </div>
                                            </div>
                                            <div class="d-flex mb-3">
                                                <div class="flex-shrink-0">
                                                    <img src="https://assets.stickpng.com/images/5847f9cbcef1014c0b5e48c8.png"
                                                        alt="google" class="me-3" height="52">
                                                </div>
                                                <div class="flex-grow-1 row">
                                                    <div class="col-sm-7">
                                                        <h6 class="mb-0">Google</h6>
                                                        <small class="text-muted">Soon</small>
                                                    </div>

                                                    <!--<div class="col-sm-5 text-sm-end mt-sm-0 mt-2">
                                                        <button class="btn btn-label-secondary btn-icon waves-effect">
                                                            <i class="ti ti-link ti-sm"></i>
                                                        </button>
                                                    </div>-->
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php include(__DIR__ . '/../components/footer.php') ?>
                    <div class="content-backdrop fade"></div>
                </div>
            </div>
        </div>
        <div class="layout-overlay layout-menu-toggle"></div>
        <div class="drag-target"></div>
    </div>
    <?php include(__DIR__ . '/../requirements/footer.php') ?>
    <!-- Page JS -->
    <script src="<?= $appURL ?>/assets/js/pages-account-settings-account.js"></script>
</body>

</html>