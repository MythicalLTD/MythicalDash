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
                        header('location: /user/edit?e='.$lang['username_or_email_exists']);
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
                        header('location: /user/edit?s='.$lang['updated_user_info_in_db']);
                        curl_close($ch);
                        die();
                    } else {
                        header("location: /user/edit?e=".$lang['pterodactyl_failed_to_update_info']);
                        curl_close($ch);
                        die();
                    }
                }
            } else {
                header('location: /user/edit?e='.$lang['please_fill_in_all_required_info']);
                die();
            }
        }
    } else {
        header('location: /user/profile?e='.$lang['csrf_failed']);
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="dark-style layout-navbar-fixed layout-menu-fixed" dir="ltr" data-theme="theme-semi-dark"
    data-assets-path="<?= $appURL ?>/assets/" data-template="vertical-menu-template">

<head>
    <?php include(__DIR__ . '/../requirements/head.php'); ?>
    <title>
        <?= SettingsManager::getSetting("name") ?> - <?= $lang['account']?>
    </title>
</head>

<body>
    <?php
    if (SettingsManager::getSetting("show_snow") == "true") {
        include(__DIR__ . '/../components/snow.php');
    }
    ?>
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
                        <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light"><?= $lang['account']?> </span></h4>
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
                                                class="ti-xs ti ti-users me-1"></i> <?= $lang['account']?> </a>
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
                                                class="ti-xs ti ti-link me-1"></i> <?= $lang['connections']?> </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="/user/payments"><i
                                                class="ti-xs ti ti-currency-euro me-1"></i> <?= $lang['payments']?> </a>
                                    </li>
                                </ul>
                                <div class="card mb-4">
                                    <h5 class="card-header"><?= $lang['profile']?></h5>
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
                                                    <label for="username" class="form-label"><?= $lang['username']?></label>
                                                    <input class="form-control" type="text" id="username"
                                                        name="username" id="name" value="<?= $session->getUserInfo("username") ?>"
                                                        placeholder="jhondoe" />
                                                </div>
                                                <div class="mb-3 col-md-6">
                                                    <label for="firstName" class="form-label"><?= $lang['first_name']?></label>
                                                    <input class="form-control" type="text" id="firstName"
                                                        name="firstName"
                                                        value="<?= Encryption::decrypt($session->getUserInfo('first_name'), $ekey) ?>"
                                                        autofocus />
                                                </div>
                                                <div class="mb-3 col-md-6">
                                                    <label for="lastName" class="form-label"><?= $lang['last_name']?></label>
                                                    <input class="form-control" type="text" name="lastName"
                                                        id="lastName"
                                                        value="<?= Encryption::decrypt($session->getUserInfo('last_name'), $ekey) ?>" />
                                                </div>
                                                <div class="mb-3 col-md-6">
                                                    <label for="email" class="form-label"><?= $lang['email']?></label>
                                                    <input class="form-control" type="email" id="email" name="email"
                                                        value="<?= $session->getUserInfo("email") ?>"
                                                        placeholder="john.doe@example.com" />
                                                </div>
                                                <div class="mb-3 col-md-6">
                                                    <label for="avatar" class="form-label"><?= $lang['avatar']?></label>
                                                    <input class="form-control" type="text" id="avatar" name="avatar"
                                                        value="<?= $session->getUserInfo("avatar") ?>" />
                                                </div>

                                                <div class="mb-3 col-md-6">
                                                    <label for="avatar" class="form-label"><?= $lang['secret_key']?></label><br>
                                                    <button type="button" data-bs-toggle="modal"
                                                        data-bs-target="#viewkey" class="btn btn-primary btn-sm me-2"
                                                        value="true"><?= $lang['show']?> <?= $lang["secret_key"]?></button>
                                                </div>
                                            </div>
                                            <?= $csrf->input('profile-form'); ?>

                                            <div class="mt-2">
                                                <button type="submit" name="edit_user" class="btn btn-primary me-2"
                                                    value="true"><?= $lang['save']?></button>
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
                                    <h5 class="card-header"><?= $lang['danger_zone']?></h5>
                                    <div class="card-body">
                                        <div class="mb-3 col-12 mb-0">
                                            <div class="alert alert-warning">
                                                <h5 class="alert-heading mb-1">
                                                </h5><?= $lang['danger_zone_read']?>
                                                <p class="mb-0"><?= $lang['danger_zone_warn']?></p>
                                            </div>
                                        </div>
                                        <button type="button" data-bs-toggle="modal" data-bs-target="#resetPwd"
                                            class="btn btn-danger deactivate-account"><?= $lang['reset_password']?></button>
                                        <button type="button" data-bs-toggle="modal" data-bs-target="#resetKey"
                                            class="btn btn-danger deactivate-account"><?= $lang['reset_key']?></button>
                                        <button type="button" data-bs-toggle="modal" data-bs-target="#deleteacc"
                                            class="btn btn-danger deactivate-account"><?= $lang['delete_account']?></button>
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
                                        <h3 class="mb-2"><?= $lang['show']?> <?= $lang["secret_key"]?></h3>
                                        <p class="text-muted"><?= $lang['show_key_description']?>
                                        </p>
                                        <code><?= $session->getUserInfo("api_key") ?></code>
                                    </div>
                                    <div class="col-12 text-center">
                                        <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal"
                                            aria-label="Close"><?= $lang['back']?> </button>
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
                                        <h3 class="mb-2"><?= $lang['delete_account']?>?</h3>
                                        <p class="text-muted"><?= $lang['delete_account_danger']?>
                                        </p>
                                    </div>
                                    <form method="GET" action="/user/security/delete_account" class="row g-3">
                                        <div class="col-12 text-center">
                                            <button type="submit" name="key"
                                                value="<?= mysqli_real_escape_string($conn, $_COOKIE['token']) ?>"
                                                class="btn btn-danger me-sm-3 me-1"><?= $lang['delete_account']?></button>
                                            <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal"
                                                aria-label="Close"><?= $lang['back']?> </button>
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
                                        <h3 class="mb-2"><?= $lang['reset_key']?>?</h3>
                                        <p class="text-muted"><?= $lang['reset_key_desc']?>
                                        </p>
                                    </div>
                                    <form method="GET" action="/user/security/resetkey" class="row g-3">
                                        <div class="col-12 text-center">
                                            <button type="submit" name="key"
                                                value="<?= mysqli_real_escape_string($conn, $_COOKIE['token']) ?>"
                                                class="btn btn-danger me-sm-3 me-1"><?= $lang['reset_key']?></button>
                                            <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal"
                                                aria-label="Close"><?= $lang['back']?> </button>
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
                                        <h3 class="mb-2"><?= $lang['reset_password']?>?</h3>
                                        <p class="text-muted"><?= $lang['reset_pwd_desc']?></p>
                                    </div>
                                    <form method="GET" action="/user/security/resetpwd" class="row g-3">
                                        <div class="col-12">
                                            <label class="form-label" for="resetPwd"><?= $lang['new_password']?></label>
                                            <input type="password" id="pwd" name="pwd" class="form-control"
                                                placeholder="" required />
                                        </div>
                                        <div class="col-12 text-center">
                                            <button type="submit" name="key"
                                                value="<?= mysqli_real_escape_string($conn, $_COOKIE['token']) ?>"
                                                class="btn btn-danger me-sm-3 me-1"><?= $lang['reset_password']?></button>
                                            <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal"
                                                aria-label="Close"><?= $lang['back']?> </button>
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