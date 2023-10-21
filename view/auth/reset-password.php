<?php
include('../include/php-csrf.php');
session_start();
$csrf = new CSRF();
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['code'])) {
        if (!$_GET['code'] == "") {
            $code = mysqli_real_escape_string($conn, $_GET['code']);
            $query = "SELECT * FROM mythicaldash_resetpasswords WHERE `resetkeycode` = '$code'";
            $result = mysqli_query($conn, $query);
            if (mysqli_num_rows($result) > 0) {
                if (isset($_GET['password'])) {
                    if ($csrf->validate('reset-password-form')) {
                        $ucode = $conn->query("SELECT * FROM mythicaldash_resetpasswords WHERE `resetkeycode` = '" . $code . "'")->fetch_array();
                        $upassword = mysqli_real_escape_string($conn, $_GET['password']);
                        $password = password_hash($upassword, PASSWORD_BCRYPT);
                        $conn->query("UPDATE `mythicaldash_users` SET `password` = '" . $password . "' WHERE `mythicaldash_users`.`api_key` = '" . $ucode['ownerkey'] . "';");
                        $conn->query("DELETE FROM mythicaldash_resetpasswords WHERE `mythicaldash_resetpasswords`.`id` = " . $ucode['id'] . "");
                        $conn->close();
                        header('location: /auth/login');
                        die();
                    } else {
                        header('location: /auth/forgot-password?e=CSRF Verification Failed');
                        die();
                    }
                } else {
                    ?>
                    <!DOCTYPE html>
                    <html lang="en" class="dark-style customizer-hide" dir="ltr" data-theme="theme-semi-dark"
                        data-assets-path="<?= $appURL ?>/assets/" data-template="horizontal-menu-template">

                    <head>
                        <?php include(__DIR__ . '/../requirements/head.php'); ?>
                        <link rel="stylesheet" href="<?= $appURL ?>/assets/vendor/css/pages/page-auth.css" />
                        <title>
                            <?= $settings['name'] ?> | Reset Password
                        </title>
                    </head>

                    <body>
                        <div class="authentication-wrapper authentication-cover authentication-bg">
                            <div class="authentication-inner row">
                                <div class="d-none d-lg-flex col-lg-7 p-0">
                                    <div class="auth-cover-bg auth-cover-bg-color d-flex justify-content-center align-items-center">
                                        <img src="<?= $appURL ?>/assets/img/illustrations/auth-forgot-password-illustration-light.png"
                                            alt="auth-forgot-password-cover" class="img-fluid my-5 auth-illustration"
                                            data-app-light-img="illustrations/auth-forgot-password-illustration-light.png"
                                            data-app-dark-img="illustrations/auth-forgot-password-illustration-dark.png" />
                                        <img src="<?= $appURL ?>/assets/img/illustrations/bg-shape-image-light.png"
                                            alt="auth-forgot-password-cover" class="platform-bg"
                                            data-app-light-img="illustrations/bg-shape-image-light.png"
                                            data-app-dark-img="illustrations/bg-shape-image-dark.png" />
                                    </div>
                                </div>
                                <div class="d-flex col-12 col-lg-5 align-items-center p-sm-5 p-4">
                                    <div class="w-px-400 mx-auto">
                                        <h3 class="mb-1 fw-bold">Reset password 🔒</h3>
                                        <p class="mb-4">Enter your new password</p>
                                        <form id="formAuthentication" class="mb-3" method="GET">
                                            <div class="mb-3">
                                                <label for="password" class="form-label">Password</label>
                                                <input type="password" class="form-control" id="password" name="password"
                                                    placeholder="Enter your new password" autofocus required />
                                            </div>
                                            <input type="hidden" id="code" name="code" value="<?= $_GET['code'] ?>">
                                            <?= $csrf->input('reset-password-form'); ?>
                                            <p class="font-family-arial font-size-24 text-danger text-center">WARNING: You are not going to
                                                get logged
                                                out of your other devices; you will have to do that from the profile page!</p>
                                            <button name="reset_password" value="true" class="btn btn-primary d-grid w-100">Reset
                                                password</button>
                                        </form>
                                        <div class="text-center">
                                            <a href="/auth/login" class="d-flex align-items-center justify-content-center">
                                                <i class="ti ti-chevron-left scaleX-n1-rtl"></i>
                                                Back to login
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php include(__DIR__ . '/../requirements/footer.php'); ?>
                        <script src="<?= $appURL ?>/assets/js/pages-auth.js"></script>
                    </body>

                    </html>
                    <?php
                }
            } else {
                header('location: /auth/forgot-password?e=The code for resetting your password is wrong. Please try again.');
                die();
            }
        } else {
            header('location: /auth/forgot-password?e=The code for resetting your password is wrong. Please try again.');
            die();
        }

    } else {
        header('location: /auth/forgot-password?e=The code for resetting your password is wrong. Please try again.');
        die();
    }
}
?>