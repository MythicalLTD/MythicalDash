<?php
use MythicalDash\CloudFlare\Captcha;
use MythicalDash\ErrorHandler;
use MythicalDash\SettingsManager;
use MythicalDash\Encryption;
use MythicalDash\Telemetry;
use MythicalDash\SessionManager;
use MythicalDash\Database\Connect;

try {
    $conn = new Connect();
    $conn = $conn->connectToDatabase();
    $session = new SessionManager();
    session_start();
    $csrf = new MythicalDash\CSRF();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if ($csrf->validate('register-form')) {
            if (isset($_POST['sign_up'])) {
                if (SettingsManager::getSetting("enable_turnstile") == "false") {
                    $captcha_success = 1;
                } else {
                    $captcha_success = Captcha::validate_captcha($_POST["cf-turnstile-response"], $session->getIP(), SettingsManager::getSetting("turnstile_secretkey"));
                }
                if ($captcha_success) {
                    if (!SettingsManager::getSetting("PterodactylURL") == "" && !SettingsManager::getSetting("PterodactylAPIKey") == "") {
                        $username = mysqli_real_escape_string($conn, $_POST['username']);
                        $email = mysqli_real_escape_string($conn, $_POST['email']);
                        $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
                        $last_name = mysqli_real_escape_string($conn, $_POST['last_name']);
                        $upassword = mysqli_real_escape_string($conn, $_POST['password']);
                        $insecure_passwords = array("password", "1234", "qwerty", "letmein", "admin", "pass", "123456789", "dad", "mom", "kek", "fuck", "pussy");
                        if (in_array($upassword, $insecure_passwords)) {
                            header('location: /auth/register?e=Password is not secure. Please choose a different one');
                            die();
                        }
                        $blocked_usernames = array("password", "1234", "qwerty", "letmein", "admin", "pass", "123456789", "dad", "mom", "kek", "fuck", "pussy", "plexed", "badsk", "username", "Username", "Admin", "sex", "porn", "nudes", "nude", "ass", "hacker", "bigdick");
                        if (in_array($username, $blocked_usernames)) {
                            header('location: /auth/register?e=It looks like we blocked this username from being used. Please choose another username.');
                            die();
                        }
                        if (preg_match("/[^a-zA-Z]+/", $username)) {
                            header('location: /auth/register?e=Please only use characters from A-Z in your username!');
                            die();
                        }
                        if (preg_match("/[^a-zA-Z]+/", $first_name)) {
                            header('location: /auth/register?e=Please only use characters from A-Z in your first name!');
                            die();
                        }
                        if (preg_match("/[^a-zA-Z]+/", $last_name)) {
                            header('location: /auth/register?e=Please only use characters from A-Z in your last name!');
                            die();
                        }
                        $password = password_hash($upassword, PASSWORD_BCRYPT);
                        $skey = Encryption::generate_key($email, $password);
                        if (!$username == "" || !$email == "" || !$first_name == "" || !$last_name == "" || !$upassword == "") {
                            $check_query = "SELECT * FROM mythicaldash_users WHERE username = '$username' OR email = '$email'";
                            $result = mysqli_query($conn, $check_query);
                            if (!mysqli_num_rows($result) > 0) {
                                $aquery = "SELECT * FROM mythicaldash_login_logs WHERE ipaddr = '" . $session->getIP() . "'";
                                $aresult = mysqli_query($conn, $aquery);
                                $acount = mysqli_num_rows($aresult);
                                if (SettingsManager::getSetting("enable_alting") == "true") { 
                                    if ($acount >= 1) {
                                        header('location: /auth/register?e=Hmmm it looks like you are trying to abuse. You are trying to use temporary accounts, which is not allowed.');
                                        die();
                                    }
                                }
                                $vpn = false;
                                $response = file_get_contents("http://ip-api.com/json/" . $session->getIP() . "?fields=status,message,country,regionName,city,timezone,isp,org,as,mobile,proxy,hosting,query");
                                $response = json_decode($response, true);
                                if (isset($response['proxy'])) {
                                    if ($response['proxy'] == true || $response['hosting'] == true) {
                                        $vpn = true;
                                    }
                                }
                                if ($response['type'] = !"Residential") {
                                    $vpn = true;
                                }
                                if ($session->getIP() == "51.161.152.218" || $session->getIP() == "66.220.20.165") {
                                    $vpn = false;
                                }
                                if (SettingsManager::getSetting("enable_anti_vpn") == "true") {
                                    if ($vpn == true) {
                                        header('location: /auth/register?e=Hmmm it looks like you are trying to abuse. You are trying to use a VPN, which is not allowed.');
                                        die();
                                    }
                                }
                                $ch = curl_init();
                                curl_setopt($ch, CURLOPT_URL, SettingsManager::getSetting("PterodactylURL") . '/api/application/users');
                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                                    'Accept: application/json',
                                    'Authorization: Bearer ' . SettingsManager::getSetting("PterodactylAPIKey"),
                                    'Content-Type: application/json',
                                ]);
                                curl_setopt($ch, CURLOPT_POST, true);
                                $data = [
                                    'email' => $email,
                                    'username' => $username,
                                    'first_name' => $first_name,
                                    'last_name' => $last_name,
                                    'password' => $upassword,
                                    'language' => 'en',
                                ];
                                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                                $response = curl_exec($ch);
                                $responseData = json_decode($response, true);
                                $panelId = $responseData['attributes']['id'];
                                $panel_username = $responseData['attributes']['username'];
                                if (curl_errno($ch)) {

                                } else {

                                }
                                curl_close($ch);
                                $conn->query("INSERT INTO mythicaldash_login_logs (ipaddr, userkey) VALUES ('" . $session->getIP() . "', '$skey')");
                                $default = "https://www.gravatar.com/avatar/00000000000000000000000000000000";
                                $grav_url = "https://www.gravatar.com/avatar/" . md5(strtolower(trim($email))) . "?d=" . urlencode($default);
                                if (file_exists("FIRST_USER")) {
                                    $role = "Administrator";
                                } else {
                                    $role = "User";
                                }
                                $conn->query("INSERT INTO `mythicaldash_users` 
                                (`panel_id`,
                                `email`,
                                `username`,
                                `first_name`,
                                `last_name`,
                                `password`,
                                `api_key`,
                                `avatar`,
                                `role`,
                                `coins`,
                                `ram`,
                                `disk`,
                                `cpu`,
                                `server_limit`,
                                `ports`,
                                `databases`,
                                `backups`,
                                `first_ip`
                                ) VALUES (
                                '" . $panelId . "',
                                '" . $email . "', 
                                '" . $username . "',
                                '" . Encryption::encrypt($first_name, $ekey) . "',
                                '" . Encryption::encrypt($last_name, $ekey) . "',
                                '" . $password . "',
                                '" . $skey . "',
                                '" . $grav_url . "',
                                '" . $role . "',
                                '" . SettingsManager::getSetting("def_coins") . "',
                                '" . SettingsManager::getSetting("def_memory") . "',
                                '" . SettingsManager::getSetting("def_disk_space") . "',
                                '" . SettingsManager::getSetting("def_cpu") . "',
                                '" . SettingsManager::getSetting("def_server_limit") . "',
                                '" . SettingsManager::getSetting("def_port") . "',
                                '" . SettingsManager::getSetting("def_db") . "',
                                '" . SettingsManager::getSetting("def_backups") . "',
                                '" . $session->getIP() . "'
                                );");
                                $conn->close();
                                if (file_exists("FIRST_USER")) {
                                    unlink("FIRST_USER");
                                }
                                Telemetry::NewUser();
                                header('location: /auth/login');
                                die();

                            } else {
                                header('location: /auth/register?e=Username or email already exists. Please choose a different one');
                                die();
                            }
                        } else {
                            header('location: /auth/register?e=Please fill in all the required info');
                            die();
                        }
                    } else {
                        header("location: /auth/register?e=I'm sorry, but it looks like the pterodactyl panel is not linked to the dash.");
                        die();
                    }
                } else {
                    header("location: /auth/register?e=Captcha verification failed; please refresh!");
                    die();
                }
            }
        }
    }
} catch (Exception $e) {
    header("location: /auth/register?e=An unexpected error occurred!");
    ErrorHandler::Error("Register ", $e);
    die();
}
?>
<!DOCTYPE html>
<html lang="en" class="dark-style customizer-hide" dir="ltr" data-theme="theme-semi-dark"
    data-assets-path="<?= $appURL ?>/assets/" data-template="horizontal-menu-template">

<head>
    <meta charset="utf-8" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <?php include(__DIR__ . '/../requirements/head.php'); ?>
    <title>
        <?= SettingsManager::getSetting("name") ?> - Register
    </title>
    <link rel="stylesheet" href="<?= $appURL ?>/assets/vendor/css/pages/page-auth.css" />
</head>

<body>
    <div id="preloader" class="discord-preloader">
        <div class="spinner"></div>
    </div>
    <div class="authentication-wrapper authentication-cover authentication-bg">
        <div class="authentication-inner row">
            <div class="d-none d-lg-flex col-lg-7 p-0">
                <div class="auth-cover-bg auth-cover-bg-color d-flex justify-content-center align-items-center">
                    <img src="<?= $appURL ?>/assets/img/illustrations/auth-register-illustration-light.png"
                        alt="auth-register-cover" class="img-fluid my-5 auth-illustration"
                        data-app-light-img="illustrations/auth-register-illustration-light.png"
                        data-app-dark-img="illustrations/auth-register-illustration-dark.png" />
                    <img src="<?= $appURL ?>/assets/img/illustrations/bg-shape-image-light.png"
                        alt="auth-register-cover" class="platform-bg"
                        data-app-light-img="illustrations/bg-shape-image-light.png"
                        data-app-dark-img="illustrations/bg-shape-image-dark.png" />
                </div>
            </div>
            <div class="d-flex col-12 col-lg-5 align-items-center p-sm-5 p-4">
                <div class="w-px-400 mx-auto">
                    <h3 class="mb-1 fw-bold">Welcome to
                        <?= SettingsManager::getSetting("name") ?>!
                    </h3>
                    <p class="mb-4">Please create an account and embark on your adventure!</p>
                    <form id="formAuthentication" class="mb-3" method="POST">
                        <div class="mb-3">
                            <label for="first_name" class="form-label">First name</label>
                            <input type="text" class="form-control" id="first_name" required name="first_name"
                                placeholder="John" autofocus />
                        </div>
                        <div class="mb-3">
                            <label for="last_name" class="form-label">Last name</label>
                            <input type="text" class="form-control" id="last_name" required name="last_name"
                                placeholder="Doe" autofocus />
                        </div>
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" required name="username"
                                placeholder="johndoe" autofocus />
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" required name="email"
                                placeholder="Enter your email" />
                        </div>
                        <div class="mb-3 form-password-toggle">
                            <label class="form-label" for="password">Password</label>
                            <div class="input-group input-group-merge">
                                <input type="password" id="password" required class="form-control" name="password"
                                    placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                                    aria-describedby="password" />
                                <span class="input-group-text cursor-pointer"><i class="ti ti-eye-off"></i></span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="terms-conditions" name="terms" />
                                <label class="form-check-label" for="terms-conditions">
                                    I agree to the <a type="button" class="text-primary" data-bs-toggle="modal"
                                        data-bs-target="#tos">terms of service</a> &amp; <a type="button"
                                        class="text-primary" data-bs-toggle="modal" data-bs-target="#pp">privacy
                                        policy</a>
                                </label>
                            </div>
                        </div>
                        <?php
                        if (SettingsManager::getSetting("enable_turnstile") == "true") {
                            ?>
                            <center>
                                <div class="cf-turnstile"
                                    data-sitekey="<?= SettingsManager::getSetting("turnstile_sitekey") ?>"></div>
                            </center>
                            &nbsp;
                            <?php
                        }
                        ?>
                        <?= $csrf->input('register-form'); ?>
                        <button type="submit" value="true" name="sign_up" class="btn btn-primary d-grid w-100">Sign
                            up</button>
                    </form>
                    <?php
                    if (isset($_GET['e'])) {
                        ?>
                        <div class="text-center alert alert-danger" role="alert">
                            <?= $_GET['e'] ?>
                        </div>
                        <?php
                    } else {

                    }
                    ?>
                    <p class="text-center">
                        <span>Already have an account?</span>
                        <a href="/auth/login">
                            <span>Sign in instead</span>
                        </a>
                    </p>
                </div>
            </div>
        </div>
        <div class="modal fade" id="tos" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-simple modal-edit-user">
                <div class="modal-content p-3 p-md-5">
                    <div class="modal-body">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        <div class="text-center mb-4">
                            <h3 class="mb-2">Terms of service</h3>
                            <p>
                                <?= SettingsManager::getSetting("terms_of_service") ?>
                        </div>
                        <div class="col-12 text-center">
                            <button type="button" data-bs-toggle="modal" data-bs-target="#pp"
                                class="btn btn-primary me-sm-3 me-1">Privacy
                                Policy</button>
                            <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal"
                                aria-label="Close">Close </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="pp" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-simple modal-edit-user">
                <div class="modal-content p-3 p-md-5">
                    <div class="modal-body">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        <div class="text-center mb-4">
                            <h3 class="mb-2">Privacy Policy</h3>
                            <p>
                                <?= SettingsManager::getSetting("privacy_policy") ?>
                        </div>
                        <div class="col-12 text-center">
                            <button type="button" data-bs-toggle="modal" data-bs-target="#tos" name="id" value=""
                                class="btn btn-primary me-sm-3 me-1">Terms of Service</button>
                            <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal"
                                aria-label="Close">Close </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include(__DIR__ . '/../requirements/footer.php'); ?>
    <script src="<?= $appURL ?>/assets/js/pages-auth.js"></script>
</body>

</html>