<?php
use MythicalDash\Encryption;
use MythicalDash\SettingsManager;

include(__DIR__ . '/../requirements/page.php');
$csrf = new MythicalDash\CSRF();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($csrf->validate('profile-form')) {
        if (isset($_POST['edit_user'])) {
            $userdb = $conn->query("SELECT * FROM mythicaldash_users WHERE api_key = '" . mysqli_real_escape_string($conn, $_COOKIE['token']) . "'")->fetch_array();
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
                        header('location: /user/edit?e=Username or email already exists. Please choose a different one');
                        die();
                    }
                } else {
                    $conn->query("UPDATE `mythicaldash_users` SET `username` = '" . $username . "' WHERE `mythicaldash_users`.`api_key` = '" . mysqli_real_escape_string($conn, $_COOKIE['token']) . "';");
                    $conn->query("UPDATE `mythicaldash_users` SET `first_name` = '" . Encryption::encrypt($firstName, $ekey) . "' WHERE `mythicaldash_users`.`api_key` = '" . mysqli_real_escape_string($conn, $_COOKIE['token']) . "';");
                    $conn->query("UPDATE `mythicaldash_users` SET `last_name` = '" . Encryption::encrypt($lastName, $ekey) . "' WHERE `mythicaldash_users`.`api_key` = '" . mysqli_real_escape_string($conn, $_COOKIE['token']) . "';");
                    $conn->query("UPDATE `mythicaldash_users` SET `avatar` = '" . $avatar . "' WHERE `mythicaldash_users`.`api_key` = '" . mysqli_real_escape_string($conn, $_COOKIE['token']) . "';");
                    $conn->query("UPDATE `mythicaldash_users` SET `email` = '" . $email . "' WHERE `mythicaldash_users`.`api_key` = '" . mysqli_real_escape_string($conn, $_COOKIE['token']) . "';");
                    $conn->close();
                    $api_url = SettingsManager::getSetting("PterodactylURL") . "/api/application/users/" . $userdb['panel_id'] . "";
                    $data = [
                        "email" => $_POST['email'],
                        "username" => $_POST['username'],
                        "first_name" => $_POST['firstName'],
                        "last_name" => $_POST['lastName'],
                        "language" => "en"
                    ];

                    $data_json = json_encode($data);

                    $ch = curl_init($api_url);
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
                    curl_setopt($ch, CURLOPT_HTTPHEADER, [
                        "Accept: application/json",
                        "Content-Type: application/json",
                        "Authorization: Bearer " . SettingsManager::getSetting("PterodactylAPIKey")
                    ]);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                    $response = curl_exec($ch);
                    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

                    if ($http_code == 200) {
                        $api_response = json_decode($response, true);
                        header('location: /user/edit?s=We updated the user settings in the database');
                        curl_close($ch);
                        die();
                    } else {
                        header("location: /user/edit?e=Failed to update the user settings inside the panel");
                        curl_close($ch);
                        die();
                    }
                }
            } else {
                header('location: /user/edit?e=Please fill in all the info');
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
        <?= SettingsManager::getSetting("name") ?> - Edit
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
                            if (SettingsManager::getSetting("enable_ads") == "true") {
                                ?>
                                <br>
                                <?= SettingsManager::getSetting("ads_code") ?>
                                <br>
                                <?php
                            }
                            ?>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <ul class="nav nav-pills flex-column flex-md-row mb-4">
                                    <li class="nav-item">
                                        <a href="/user/edit" class="nav-link active"><i
                                                class="ti-xs ti ti-users me-1"></i> Account</a>
                                    </li>
                                    <!--<li class="nav-item">
                      <a class="nav-link" href="pages-account-settings-billing.html"
                        ><i class="ti-xs ti ti-file-description me-1"></i> Billing & Plans</a
                      >
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" href="pages-account-settings-notifications.html"
                        ><i class="ti-xs ti ti-bell me-1"></i> Notifications</a
                      >
                    </li>-->
                                    <li class="nav-item">
                                        <a class="nav-link" href="/user/connections"><i
                                                class="ti-xs ti ti-link me-1"></i> Connections</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="/user/payments"><i
                                                class="ti-xs ti ti-currency-euro me-1"></i> Payments</a>
                                    </li>
                                </ul>
                                <div class="card mb-4">
                                    <h5 class="card-header">Profile Details</h5>
                                    <!-- Account -->
                                    <div class="card-body">
                                        <div class="d-flex align-items-start align-items-sm-center gap-4">
                                            <img src="<?= $session->getUserInfo("avatar") ?>" alt="user-avatar"
                                                class="d-block w-px-100 h-px-100 rounded" id="uploadedAvatar" />
                                        </div>
                                    </div>
                                    <hr class="my-0" />
                                    <div class="card-body">
                                        <form action="/user/edit" method="POST">
                                            <div class="row">
                                                <div class="mb-3 col-md-6">
                                                    <label for="username" class="form-label">Username</label>
                                                    <input class="form-control" type="text" id="username"
                                                        name="username" value="<?= $session->getUserInfo("username") ?>"
                                                        placeholder="jhondoe" />
                                                </div>
                                                <div class="mb-3 col-md-6">
                                                    <label for="firstName" class="form-label">First Name</label>
                                                    <input class="form-control" type="text" id="firstName"
                                                        name="firstName"
                                                        value="<?= Encryption::decrypt($session->getUserInfo('first_name'), $ekey) ?>"
                                                        autofocus />
                                                </div>
                                                <div class="mb-3 col-md-6">
                                                    <label for="lastName" class="form-label">Last Name</label>
                                                    <input class="form-control" type="text" name="lastName"
                                                        id="lastName"
                                                        value="<?= Encryption::decrypt($session->getUserInfo('last_name'), $ekey) ?>" />
                                                </div>
                                                <div class="mb-3 col-md-6">
                                                    <label for="email" class="form-label">E-mail</label>
                                                    <input class="form-control" type="email" id="email" name="email"
                                                        value="<?= $session->getUserInfo("email") ?>"
                                                        placeholder="john.doe@example.com" />
                                                </div>
                                                <div class="mb-3 col-md-6">
                                                    <label for="avatar" class="form-label">Avatar</label>
                                                    <input class="form-control" type="text" id="avatar" name="avatar"
                                                        value="<?= $session->getUserInfo("avatar") ?>" />
                                                </div>

                                                <div class="mb-3 col-md-6">
                                                    <label for="avatar" class="form-label">Secret Key</label><br>
                                                    <button type="button" data-bs-toggle="modal"
                                                        data-bs-target="#viewkey" class="btn btn-primary btn-sm me-2"
                                                        value="true">View secret key</button>
                                                </div>
                                            </div>
                                            <?= $csrf->input('profile-form'); ?>

                                            <div class="mt-2">
                                                <button type="submit" name="edit_user" class="btn btn-primary me-2"
                                                    value="true">Save changes</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
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
                                <div class="card">
                                    <h5 class="card-header">Danger Zone</h5>
                                    <div class="card-body">
                                        <div class="mb-3 col-12 mb-0">
                                            <div class="alert alert-warning">
                                                <h5 class="alert-heading mb-1">Make sure you read what the button does!
                                                </h5>
                                                <p class="mb-0">Once you press a button, there is no going back. Please
                                                    be certain.</p>
                                            </div>
                                        </div>
                                        <button type="button" data-bs-toggle="modal" data-bs-target="#resetPwd"
                                            class="btn btn-danger deactivate-account">Reset Password</button>
                                        <button type="button" data-bs-toggle="modal" data-bs-target="#resetKey"
                                            class="btn btn-danger deactivate-account">Reset Secret Key</button>
                                        <button type="button" data-bs-toggle="modal" data-bs-target="#deleteacc"
                                            class="btn btn-danger deactivate-account">Delete Account</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal fade" id="viewkey" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-simple modal-edit-user">
                            <div class="modal-content p-3 p-md-5">
                                <div class="modal-body">
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                    <div class="text-center mb-4">
                                        <h3 class="mb-2">View secret key</h3>
                                        <p class="text-muted">Here is your secret key that can be used to access our
                                            client API and this is your login security token, so make sure not to share
                                            it!
                                        </p>
                                        <code><?= $session->getUserInfo("api_key") ?></code>
                                    </div>
                                    <div class="col-12 text-center">
                                        <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal"
                                            aria-label="Close">Cancel </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal fade" id="deleteacc" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-simple modal-edit-user">
                            <div class="modal-content p-3 p-md-5">
                                <div class="modal-body">
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                    <div class="text-center mb-4">
                                        <h3 class="mb-2">Delete this user?</h3>
                                        <p class="text-muted">When you choose to delete this user, please be aware that
                                            all associated user data will be permanently wiped. This action is
                                            irreversible, so proceed with caution!
                                        </p>
                                    </div>
                                    <form method="GET" action="/user/security/delete_account" class="row g-3">
                                        <div class="col-12 text-center">
                                            <button type="submit" name="key"
                                                value="<?= mysqli_real_escape_string($conn, $_COOKIE['token']) ?>"
                                                class="btn btn-danger me-sm-3 me-1">Delete user</button>
                                            <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal"
                                                aria-label="Close">Cancel </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal fade" id="resetKey" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-simple modal-edit-user">
                            <div class="modal-content p-3 p-md-5">
                                <div class="modal-body">
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                    <div class="text-center mb-4">
                                        <h3 class="mb-2">Reset user secret key?</h3>
                                        <p class="text-muted">After updating the key, the user will have to login again.
                                        </p>
                                    </div>
                                    <form method="GET" action="/user/security/resetkey" class="row g-3">
                                        <div class="col-12 text-center">
                                            <button type="submit" name="key"
                                                value="<?= mysqli_real_escape_string($conn, $_COOKIE['token']) ?>"
                                                class="btn btn-danger me-sm-3 me-1">Reset key</button>
                                            <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal"
                                                aria-label="Close">Cancel </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal fade" id="resetPwd" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-simple modal-edit-user">
                            <div class="modal-content p-3 p-md-5">
                                <div class="modal-body">
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                    <div class="text-center mb-4">
                                        <h3 class="mb-2">Reset user password?</h3>
                                        <p class="text-muted">After updating the key, the user will stay logged in!!</p>
                                    </div>
                                    <form method="GET" action="/user/security/resetpwd" class="row g-3">
                                        <div class="col-12">
                                            <label class="form-label" for="resetPwd">New Password</label>
                                            <input type="password" id="pwd" name="pwd" class="form-control"
                                                placeholder="" required />
                                        </div>
                                        <div class="col-12 text-center">
                                            <button type="submit" name="key"
                                                value="<?= mysqli_real_escape_string($conn, $_COOKIE['token']) ?>"
                                                class="btn btn-danger me-sm-3 me-1">Reset password</button>
                                            <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal"
                                                aria-label="Close">Cancel </button>
                                        </div>
                                    </form>
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