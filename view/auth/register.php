<?php
include('../include/php-csrf.php');
session_start();
$csrf = new CSRF();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($csrf->validate('register-form')) {
        if (!$settings['reCAPTCHA_sitekey'] == "") {
            // CAPTCHA verification only if reCAPTCHA is enabled
            $response = $_POST["g-recaptcha-response"];
            $url = 'https://www.google.com/recaptcha/api/siteverify';
            $data = array(
                'secret' => $settings['reCAPTCHA_secretkey'],
                'response' => $_POST["g-recaptcha-response"]
            );
            $options = array(
                'http' => array(
                    'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method' => 'POST',
                    'content' => http_build_query($data)
                )
            );
            $context = stream_context_create($options);
            $verify = file_get_contents($url, false, $context);
            $captcha_success = json_decode($verify);

            if ($captcha_success->success == false) {
                writeLog("auth", "Failed to login: 'reCAPTCHA failed'", $conn);
                header('location: /auth/login?e=reCAPTCHA Verification Failed');
                die();
            }
        }
        if (isset($_POST['sign_up'])) {
            if (!$settings['PterodactylURL'] == "" && !$settings['PterodactylAPIKey'] == "") {
                $ip_addres = getclientip();
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
                $blocked_usernames = array("password", "1234", "qwerty", "letmein", "admin", "pass", "123456789", "dad", "mom", "kek", "fuck", "pussy", "plexed", "badsk", "username", "sex", "porn", "nudes", "nude", "ass", "hacker", "bigdick");
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
                $password = password_hash($upassword, PASSWORD_DEFAULT);
                $skey = generate_key($email, $password);
                if (!$username == "" || !$email == "" || !$first_name == "" || !$last_name == "" || !$upassword == "") {
                    $check_query = "SELECT * FROM mythicaldash_users WHERE username = '$username' OR email = '$email'";
                    $result = mysqli_query($conn, $check_query);
                    if (!mysqli_num_rows($result) > 0) {
                        $aquery = "SELECT * FROM mythicaldash_login_logs WHERE ipaddr = '$ip_addres'";
                        $aresult = mysqli_query($conn, $aquery);
                        $acount = mysqli_num_rows($aresult);
                        if ($acount >= 1) {
                            header('location: /auth/register?e=Hmmm it looks like you are trying to abuse. You are trying to use temporary accounts, which is not allowed.');
                            exit();
                        } else {
                            if ($ip_addres == "127.0.0.1") {
                                $ip_addres = "12.34.56.78";
                            }
                            $vpn = false;
                            $response = file_get_contents("http://ip-api.com/json/" . $ip_addres . "?fields=status,message,country,regionName,city,timezone,isp,org,as,mobile,proxy,hosting,query");
                            $response = json_decode($response, true);
                            if (isset($response['proxy'])) {
                                if ($response['proxy'] == true || $response['hosting'] == true) {
                                    $vpn = true;
                                }
                            }
                            if ($response['type'] = !"Residential") {
                                $vpn = true;
                            }
                            if ($ip_addres == "51.161.152.218" || $ip_addres == "66.220.20.165") {
                                $vpn = false;
                            }
                            if ($vpn == true) {
                                header('location: /auth/register?e=Hmmm it looks like you are trying to abuse. You are trying to use a VPN, which is not allowed.');
                                die();
                            }
                            $ch = curl_init();
                            curl_setopt($ch, CURLOPT_URL, $settings["PterodactylURL"] . '/api/application/users');
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                                'Accept: application/json',
                                'Authorization: Bearer ' . $settings["PterodactylAPIKey"],
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
                            $conn->query("INSERT INTO mythicaldash_login_logs (ipaddr, userkey) VALUES ('$ip_addres', '$skey')");
                            $default = "https://www.gravatar.com/avatar/00000000000000000000000000000000";
                            $grav_url = "https://www.gravatar.com/avatar/" . md5(strtolower(trim($email))) . "?d=" . urlencode($default);
                            $conn->query("INSERT INTO `mythicaldash_users` 
                            (`panel_id`,
                            `email`,
                            `username`,
                            `first_name`,
                            `last_name`,
                            `password`,
                            `api_key`,
                            `avatar`,
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
                            '" . $first_name . "',
                            '" . $last_name . "',
                            '" . $password . "',
                            '" . $skey . "',
                            '" . $grav_url . "',
                            '" . $settings['def_coins'] . "',
                            '" . $settings['def_memory'] . "',
                            '" . $settings['def_disk_space'] . "',
                            '" . $settings['def_cpu'] . "',
                            '" . $settings['def_server_limit'] . "',
                            '" . $settings['def_port'] . "',
                            '" . $settings['def_db'] . "',
                            '" . $settings['def_backups'] . "',
                            '" . $ip_addres . "'
                            );");
                            $conn->close();
                            header('location: /auth/login');
                            die();
                        }
                    } else {
                        header('location: /auth/register?e=Username or email already exists. Please choose a different one');
                        exit();
                    }
                } else {
                    header('location: /auth/register?e=Please fill in all the required info');
                    exit();
                }
            } else {
                header("location: /auth/register?e=I'm sorry, but it looks like the pterodactyl panel is not linked to the dash.");
                exit();
            }
        }
    }
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
        <?= $settings['name'] ?> | Register
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
                        <?= $settings['name'] ?>!
                    </h3>
                    <p class="mb-4">Please create an account and embark on your adventure!</p>
                    <form id="formAuthentication" class="mb-3" method="POST">
                        <div class="mb-3">
                            <label for="first_name" class="form-label">First name</label>
                            <input type="text" class="form-control" id="first_name" required name="first_name"
                                placeholder="Jhon" autofocus />
                        </div>
                        <div class="mb-3">
                            <label for="last_name" class="form-label">Last name</label>
                            <input type="text" class="form-control" id="last_name" required name="last_name"
                                placeholder="Doe" autofocus />
                        </div>
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" required name="username"
                                placeholder="jhondoe" autofocus />
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
                        if (!$settings['reCAPTCHA_sitekey'] == "") {
                            ?>
                            <center>
                                <div class="g-recaptcha" data-sitekey="<?= $settings['reCAPTCHA_sitekey'] ?>"></div>
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
                            <p class="text-big">Legal Agreements:</p>

                            When we refer to "
                            <?= $settings['name'] ?>" or use pronouns such as "we", "us" or "our", we mean
                            <?= $settings['name'] ?>
                            When we refer to "User", we are talking about you and we will also use words like "you" and
                            "your" to refer to you.
                            By registering/purchasing services or any activity on our site, you should agree to the
                            <?= $settings['name'] ?> Terms and Conditions
                            We reserve the rights to terminate any of your services without notice if they breach any of
                            our
                            Terms And Conditions.
                            </p>
                            <p>
                            <p class="text-big"> Accounts:</p>

                            The minimum age to create an account or any activity that requires your personal data is 13
                            years old! If you are under 13, please contact us and request account deletion.
                            Your personal data must be real. If not, your services may be suspended and your account
                            banned.
                            We reserve the right to request your identity card/passport/any document proving your
                            details to
                            verify your account details. If you do not provide it when we ask, your services will be
                            suspended without refund and your account will be banned.
                            We are not responsible for the security of your account. We recommend that you use a strong
                            password and do not give it to anyone. If your account gets hacked/lost, we can only help
                            you
                            recover it.
                            We will never sell your personal data.
                            </p>
                            <p>
                            <p class="text-big"> Support:</p>

                            We are under no obligation to install software / help you code on your site or any service
                            got
                            from
                            <?= $settings['name'] ?> / other host! We only provide the service, you have to work on it.
                            We are not responsible for your files.
                            You are not allowed to SPAM / make jokes / offend our staff or any activity that wastes our
                            time. We may suspend your services and ban your account.
                            We are not offering support in Discord DM / any other social media, support is only offered
                            through our Website. Any contact in Discord DM or any other social media will be ignored,
                            further insistence will get you banned and your services suspended.
                            You may not lie or slander
                            <?= $settings['name'] ?>! You risk having our services suspended.
                            </p>
                            <p>
                            <p class="text-big"> Service Activity:</p>

                            You are not allowed to use our servers for illegal purposes like DDoS, mining etc. Your
                            server
                            will be suspended!
                            We are not required to install software (Pterodactyl, GameCP, etc.) on your server. We only
                            offer you the service. You have to work at it.
                            You may not use our web hosting services or any hosting services to sell
                            people/children/weapons/copyrighted content or any illegal activity. You will get your
                            services
                            suspended!
                            We are under no obligation to help you code your site!
                            Resource intensive users will not be supported in any form (eg: p2p communities - torrent
                            trackers, file hosting sites, image hosting sites, site statistics installations (above),
                            game
                            statistics, installations external counters (redirecting players, etc.) will be suspended
                            without refund!
                            You may not disrupt the services of other customers. You risk having your services suspended
                            and
                            your account banned!
                            We are not responsible for your website downtime. We will always let you know if there is a
                            problem.
                            We reserve the right to adapt the hardware and software used to provide the services to the
                            current state of the art and to inform you in good time of any additional requirements that
                            may
                            arise from this for the content you have stored on our servers. We undertake to make such
                            adjustments only to a reasonable extent and taking your interests into account.
                            You are not allowed to resell our products, if you do so your service will be suspended
                            without
                            refund.
                            </p>
                            <p>
                            <p class="text-big"> Data:</p>

                            We are not responsible for any backup/file or any data loss. You are responsible for backups
                            and
                            expired, suspended, canceled, refunded, prohibited services.
                            We will never sell your personal data.
                            </p>
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
                            <p>
                            <p class="text-big">The type of personal information we collect:</p>

                            We collect certain personal information about visitors and users of our Sites.
                            The most common types of information we collect include things like: user-names, member
                            names,
                            email addresses, IP addresses, other contact details, survey responses, blogs, photos,
                            payment
                            information such as payment agent details, transactional details, tax information, support
                            queries, forum comments, content you direct us to make available on our Sites (such as item
                            descriptions) and web analytics data. We will also collect personal information from job
                            applications (such as, your CV, the application form itself, cover letter and interview
                            notes).
                            </p>
                            <p>
                            <p class="text-big">How we collect personal information:</p>

                            We collect personal information directly when you provide it to us, automatically as you
                            navigate through the Sites, or through other people when you use services associated with
                            the
                            Sites.
                            We collect your personal information when you provide it to us when you complete membership
                            registration and buy or provide items or services on our Sites, subscribe to a newsletter,
                            email
                            list, submit feedback, enter a contest, fill out a survey, or send us a communication.
                            </p>
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