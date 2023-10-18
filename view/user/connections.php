<?php
include(__DIR__ . '/../requirements/page.php');
include(__DIR__ . '/../../include/php-csrf.php');
$csrf = new CSRF();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($csrf->validate('profile-form')) {
        if (isset($_POST['edit_user'])) {
            $userdb = $conn->query("SELECT * FROM mythicaldash_users WHERE api_key = '" . $_COOKIE['token'] . "'")->fetch_array();
            $username = mysqli_real_escape_string($conn, $_POST['username']);
            $firstName = mysqli_real_escape_string($conn, $_POST['firstName']);
            $lastName = mysqli_real_escape_string($conn, $_POST['lastName']);
            $email = mysqli_real_escape_string($conn, $_POST['email']);
            $avatar = mysqli_real_escape_string($conn, $_POST['avatar']);
            if (!$username == "" || $firstName == "" || $lastName == "" || $email == "" || $avatar == "") {
                if (!$userdb['username'] == $username || !$email == $userdb['email']) {
                    $check_query = "SELECT * FROM mythicaldash_users WHERE username = '$username' OR email = '$email'";
                    $result = mysqli_query($conn, $check_query);
                    if (mysqli_num_rows($result) > 0) {
                        header('location: /user/profile?e=Username or email already exists. Please choose a different one');
                        die();
                    }
                } else {
                    $conn->query("UPDATE `mythicaldash_users` SET `username` = '" . $username . "' WHERE `mythicaldash_users`.`api_key` = '" . $_COOKIE['token'] . "';");
                    $conn->query("UPDATE `mythicaldash_users` SET `first_name` = '" . $firstName . "' WHERE `mythicaldash_users`.`api_key` = '" . $_COOKIE['token'] . "';");
                    $conn->query("UPDATE `mythicaldash_users` SET `last_name` = '" . $lastName . "' WHERE `mythicaldash_users`.`api_key` = '" . $_COOKIE['token'] . "';");
                    $conn->query("UPDATE `mythicaldash_users` SET `avatar` = '" . $avatar . "' WHERE `mythicaldash_users`.`api_key` = '" . $_COOKIE['token'] . "';");
                    $conn->query("UPDATE `mythicaldash_users` SET `email` = '" . $email . "' WHERE `mythicaldash_users`.`api_key` = '" . $_COOKIE['token'] . "';");
                    $conn->close();
                    header('location: /user/profile?s=We updated the user settings in the database');
                }
            } else {
                header('location: /user/profile?e=Please fill in all the info');
                die();
            }
        }
    } else {
        header('location: /user/profile?e=CSRF Verification Failed');
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="dark-style layout-navbar-fixed layout-menu-fixed" dir="ltr" data-theme="theme-semi-dark"
    data-assets-path="<?= $appURL ?>/assets/" data-template="vertical-menu-template">

<head>
    <?php include(__DIR__ . '/../requirements/head.php'); ?>
    <title>
        <?= $settings['name'] ?> | Edit
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
                        <br>
                        <div id="ads">
                            <?php
                            if ($settings['enable_ads'] == "true") {
                                echo $settings['ads_code'];
                            }
                            ?>
                        </div>
                        <br>
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

                                                        if ($userdb['discord_linked'] == "true") {
                                                            ?> 
                                                                <small class="text-muted"><?= $userdb['discord_username']?></small>
                                                            <?php
                                                        } else {
                                                            ?>
                                                            <small class="text-muted">Not Connected</small>
                                                        <?php
                                                        }
                                                        ?>

                                                    </div>
                                                    <?php

                                                    if ($userdb['discord_linked'] == "true") {
                                                        ?>
                                                        <div class="col-sm-5 text-sm-end mt-sm-0 mt-2">
                                                            <button class="btn btn-label-danger btn-icon waves-effect"><i
                                                                    class="ti ti-trash ti-sm"></i></button>
                                                        </div>
                                                    <?php
                                                    } else {
                                                        ?>
                                                        <div class="col-sm-5 text-sm-end mt-sm-0 mt-2">
                                                            <button class="btn btn-label-secondary btn-icon waves-effect">
                                                                <i class="ti ti-link ti-sm"></i>
                                                            </button>
                                                        </div>
                                                    <?php
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
                                                        <?php

                                                        if ($userdb['discord_linked'] == "true") {

                                                        } else {
                                                            ?>
                                                            <small class="text-muted">Not Connected</small>
                                                        <?php
                                                        }
                                                        ?>

                                                    </div>

                                                    <div class="col-sm-5 text-sm-end mt-sm-0 mt-2">
                                                        <button class="btn btn-label-secondary btn-icon waves-effect">
                                                            <i class="ti ti-link ti-sm"></i>
                                                        </button>
                                                    </div>
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
                                                        <?php

                                                        if ($userdb['discord_linked'] == "true") {

                                                        } else {
                                                            ?>
                                                            <small class="text-muted">Not Connected</small>
                                                        <?php
                                                        }
                                                        ?>

                                                    </div>

                                                    <div class="col-sm-5 text-sm-end mt-sm-0 mt-2">
                                                        <button class="btn btn-label-secondary btn-icon waves-effect">
                                                            <i class="ti ti-link ti-sm"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="d-flex mb-3">
                                                <div class="flex-shrink-0">
                                                    <img src="https://avatars.githubusercontent.com/u/117385445"
                                                        alt="mythicalsystems" class="me-3" height="55">
                                                </div>
                                                <div class="flex-grow-1 row">
                                                    <div class="col-sm-7">
                                                        <h6 class="mb-0">MythicalSystems</h6>
                                                        <?php

                                                        if ($userdb['discord_linked'] == "true") {

                                                        } else {
                                                            ?>
                                                            <small class="text-muted">Not Connected</small>
                                                        <?php
                                                        }
                                                        ?>

                                                    </div>

                                                    <div class="col-sm-5 text-sm-end mt-sm-0 mt-2">
                                                        <button class="btn btn-label-secondary btn-icon waves-effect">
                                                            <i class="ti ti-link ti-sm"></i>
                                                        </button>
                                                    </div>
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