<?php
include(__DIR__ . '/../../requirements/page.php');
include(__DIR__ . '/../../requirements/admin.php');

if (isset($_GET['edit_user'])) {
    if (!$_GET['id'] == "") {
        $user_query = "SELECT * FROM mythicaldash_users WHERE id = ?";
        $stmt = mysqli_prepare($conn, $user_query);
        mysqli_stmt_bind_param($stmt, "s", $_GET['id']);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if (mysqli_num_rows($result) > 0) {
            $user_info = $conn->query("SELECT * FROM mythicaldash_users WHERE id = '" . $_GET['id'] . "'")->fetch_array();
            $username = mysqli_real_escape_string($conn, $_GET['username']);
            $firstName = mysqli_real_escape_string($conn, $_GET['firstName']);
            $lastName = mysqli_real_escape_string($conn, $_GET['lastName']);
            $email = mysqli_real_escape_string($conn, $_GET['email']);
            $avatar = mysqli_real_escape_string($conn, $_GET['avatar']);
            $role = mysqli_real_escape_string($conn, $_GET['role']);
            $coins = mysqli_real_escape_string($conn,$_GET['coins']);
            $ram = mysqli_real_escape_string($conn, $_GET['ram']);
            $disk = mysqli_real_escape_string($conn,$_GET['disk']);
            $cpu = mysqli_real_escape_string($conn,$_GET['cpu']);
            $server_limit = mysqli_real_escape_string($conn,$_GET['server_limit']);
            $ports = mysqli_real_escape_string($conn,$_GET['ports']);
            $databases = mysqli_real_escape_string($conn,$_GET['databases']);
            $backups = mysqli_real_escape_string($conn,$_GET['backups']);
            $banned = mysqli_real_escape_string($conn,$_GET['banned']);
            if (!$username == "" || $firstName == "" || $lastName == "" || $email == "" || $avatar == "" || $role == "") {
                if (!$user_info['username'] == $username || !$email == $user_info['email']) {
                    $check_query = "SELECT * FROM mythicaldash_users WHERE username = '$username' OR email = '$email'";
                    $result = mysqli_query($conn, $check_query);
                    if (mysqli_num_rows($result) > 0) {
                        header('location: /admin/users/edit?e=Username or email already exists. Please choose a different one&id=' . $_GET['id']);
                        $conn->close();
                        die();
                    }
                } else {
                    if ($role == "Admin") {
                        $conn->query("UPDATE `mythicaldash_users` SET `role` = 'Administrator' WHERE `mythicaldash_users`.`id` = " . $_GET['id'] . ";");
                    } else if ($role == "User") {
                        $conn->query("UPDATE `mythicaldash_users` SET `role` = 'User' WHERE `mythicaldash_users`.`id` = " . $_GET['id'] . ";");
                    } else {
                        $conn->query("UPDATE `mythicaldash_users` SET `role` = 'User' WHERE `mythicaldash_users`.`id` = " . $_GET['id'] . ";");
                    }
                    $conn->query("UPDATE `mythicaldash_users` SET `username` = '" . $username . "' WHERE `mythicaldash_users`.`id` = " . mysqli_real_escape_string($conn, $_GET['id']) . ";");
                    $conn->query("UPDATE `mythicaldash_users` SET `first_name` = '" . encrypt($firstName,$ekey) . "' WHERE `mythicaldash_users`.`id` = " . mysqli_real_escape_string($conn, $_GET['id']) . ";");
                    $conn->query("UPDATE `mythicaldash_users` SET `last_name` = '" . encrypt($lastName,$ekey) . "' WHERE `mythicaldash_users`.`id` = " . mysqli_real_escape_string($conn, $_GET['id']) . ";");
                    $conn->query("UPDATE `mythicaldash_users` SET `avatar` = '" . $avatar . "' WHERE `mythicaldash_users`.`id` = " . mysqli_real_escape_string($conn, $_GET['id']) . ";");
                    $conn->query("UPDATE `mythicaldash_users` SET `email` = '" . $email . "' WHERE `mythicaldash_users`.`id` = " . mysqli_real_escape_string($conn, $_GET['id']) . ";");
                    $conn->query("UPDATE `mythicaldash_users` SET `coins` = '" . $coins . "' WHERE `mythicaldash_users`.`id` = " . mysqli_real_escape_string($conn, $_GET['id']) . ";");
                    $conn->query("UPDATE `mythicaldash_users` SET `ram` = '" . $ram . "' WHERE `mythicaldash_users`.`id` = " . mysqli_real_escape_string($conn, $_GET['id']) . ";");
                    $conn->query("UPDATE `mythicaldash_users` SET `disk` = '" . $disk . "' WHERE `mythicaldash_users`.`id` = " . mysqli_real_escape_string($conn, $_GET['id']) . ";");
                    $conn->query("UPDATE `mythicaldash_users` SET `cpu` = '" . $cpu . "' WHERE `mythicaldash_users`.`id` = " . mysqli_real_escape_string($conn, $_GET['id']) . ";");
                    $conn->query("UPDATE `mythicaldash_users` SET `server_limit` = '" . $server_limit . "' WHERE `mythicaldash_users`.`id` = " . mysqli_real_escape_string($conn, $_GET['id']) . ";");
                    $conn->query("UPDATE `mythicaldash_users` SET `ports` = '" . $ports . "' WHERE `mythicaldash_users`.`id` = " . mysqli_real_escape_string($conn, $_GET['id']) . ";");
                    $conn->query("UPDATE `mythicaldash_users` SET `databases` = '" . $databases . "' WHERE `mythicaldash_users`.`id` = " . mysqli_real_escape_string($conn, $_GET['id']) . ";");
                    $conn->query("UPDATE `mythicaldash_users` SET `backups` = '" . $backups . "' WHERE `mythicaldash_users`.`id` = " . mysqli_real_escape_string($conn, $_GET['id']) . ";");
                    $conn->query("UPDATE `mythicaldash_users` SET `banned` = '" . $banned . "' WHERE `mythicaldash_users`.`id` = " . mysqli_real_escape_string($conn, $_GET['id']) . ";");
                    $conn->close();
                    header('location: /admin/users/edit?id=' . $_GET['id'] . '&s=We updated the user settings in the database');
                    die();
                }
            } else {
                header('location: /admin/users/edit?e=Please fill in all the info&id=' . $_GET['id']);
                $conn->close();
                die();
            }
        } else {
            header('location: /admin/users/view?e=Can`t find this user in the database');
            $conn->close();
            die();
        }
    } else {
        header('location: /admin/users/view?e=Can`t find this user in the database');
        die();
    }
} else if (isset($_GET['id'])) {
    if (!$_GET['id'] == "") {
        $user_query = "SELECT * FROM mythicaldash_users WHERE id = ?";
        $stmt = mysqli_prepare($conn, $user_query);
        mysqli_stmt_bind_param($stmt, "s", $_GET['id']);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if (mysqli_num_rows($result) > 0) {
            $user_info = $conn->query("SELECT * FROM mythicaldash_users WHERE id = '" . $_GET['id'] . "'")->fetch_array();
        } else {
            header('location: /admin/users/view?e=Can`t find this user in the database');
            die();
        }
    } else {
        header('location: /admin/users/view?e=Can`t find this user in the database');
        die();
    }
} else {
    header('location: /admin/users/view');
    die();
}
?>
<!DOCTYPE html>
<html lang="en" class="light-style layout-navbar-fixed layout-menu-fixed" dir="ltr" data-theme="theme-semi-dark"
    data-assets-path="<?= $appURL ?>/assets/" data-template="vertical-menu-template">

<head>
    <?php include(__DIR__ . '/../../requirements/head.php'); ?>
    <title>
        <?= $settings['name'] ?> | Users
    </title>
</head>

<body>
    <div id="preloader" class="discord-preloader">
        <div class="spinner"></div>
    </div>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <?php include(__DIR__ . '/../../components/sidebar.php') ?>
            <div class="layout-page">
                <?php include(__DIR__ . '/../../components/navbar.php') ?>
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Admin / Users /</span> Edit</h4>
                        <?php
                        if (isset($_GET['e'])) {
                            ?>
                            <div class="alert alert-danger alert-dismissible" role="alert">
                                <?= $_GET['e'] ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                            <?php
                        }
                        ?>
                        <?php
                        if (isset($_GET['s'])) {
                            ?>
                            <div class="alert alert-success alert-dismissible" role="alert">
                                <?= $_GET['s'] ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                            <?php
                        }
                        ?>
                        <div class="row">
                            <div class="col-md-12">
                                <ul class="nav nav-pills flex-column flex-md-row mb-4">
                                    <li class="nav-item">
                                        <a href="/admin/users/edit?id=<?= $_GET['id'] ?>" class="nav-link active"><i
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
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" href="pages-account-settings-connections.html"
                        ><i class="ti-xs ti ti-link me-1"></i> Connections</a
                      >
                    </li>-->
                                </ul>
                                <div class="card mb-4">
                                    <h5 class="card-header">Profile Details</h5>
                                    <!-- Account -->
                                    <div class="card-body">
                                        <div class="d-flex align-items-start align-items-sm-center gap-4">
                                            <img src="<?= $user_info['avatar'] ?>" alt="user-avatar"
                                                class="d-block w-px-100 h-px-100 rounded" id="uploadedAvatar" />
                                        </div>
                                    </div>
                                    <hr class="my-0" />
                                    <div class="card-body">
                                        <form action="/admin/users/edit" method="GET">
                                            <div class="row">
                                                <div class="mb-3 col-md-6">
                                                    <label for="username" class="form-label">Username</label>
                                                    <input class="form-control" type="text" id="username"
                                                        name="username" value="<?= $user_info['username'] ?>"
                                                        placeholder="jhondoe" />
                                                </div>
                                                <div class="mb-3 col-md-6">
                                                    <label for="role" class="form-label">Role</label>
                                                    <select id="role" name="role" class="select2 form-select">
                                                        <?php
                                                        if ($user_info['role'] == "Administrator") {
                                                            ?>
                                                            <option value="Admin">Administrator</option>
                                                            <option value="User">User</option>
                                                            <?php
                                                        } else {
                                                            ?>
                                                            <option value="User">User</option>
                                                            <option value="Admin">Administrator</option>
                                                            <?php
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                                <div class="mb-3 col-md-6">
                                                    <label for="firstName" class="form-label">First Name</label>
                                                    <input class="form-control" type="text" id="firstName"
                                                        name="firstName"
                                                        value="<?= decrypt($user_info['first_name'], $ekey) ?>"
                                                        autofocus />
                                                </div>
                                                <div class="mb-3 col-md-6">
                                                    <label for="lastName" class="form-label">Last Name</label>
                                                    <input class="form-control" type="text" name="lastName"
                                                        id="lastName"
                                                        value="<?= decrypt($user_info['last_name'], $ekey) ?>" />
                                                </div>
                                                <div class="mb-3 col-md-6">
                                                    <label for="email" class="form-label">E-mail</label>
                                                    <input class="form-control" type="email" id="email" name="email"
                                                        value="<?= $user_info['email'] ?>"
                                                        placeholder="john.doe@example.com" />
                                                </div>
                                                <div class="mb-3 col-md-6">
                                                    <label for="avatar" class="form-label">Avatar</label>
                                                    <input class="form-control" type="text" id="avatar" name="avatar"
                                                        value="<?= $user_info['avatar'] ?>" />
                                                </div>
                                                <div class="mb-3 col-md-6">
                                                    <label for="banner" class="form-label">Banner</label>
                                                    <input class="form-control" type="text" id="banner" name="banner"
                                                        value="<?= $user_info['banner'] ?>" />
                                                </div>
                                                <input class="form-control" type="hidden" id="id" name="id"
                                                    value="<?= $_GET['id'] ?>">

                                            </div>
                                    </div>
                                    <hr class="my-0" />
                                    <h5 class="card-header">User info</h5>
                                    <div class="card-body">
                                            <div class="row">
                                                <div class="mb-3 col-md-3">
                                                    <label for="banned" class="form-label">Banned</label>
                                                    <input class="form-control" type="text" id="banned" name="banned"
                                                        value="<?= $user_info['banned'] ?>" placeholder="User is not banned" />
                                                </div>
                                                <div class="mb-3 col-md-1">
                                                    <label for="panel_id" class="form-label">Panel ID</label>
                                                    <input class="form-control" type="text" id="panel_id" name="panel_id"
                                                        value="<?= $user_info['panel_id'] ?>" disabled readonly/>
                                                </div>
                                                <div class="mb-3 col-md-1">
                                                    <label for="minutes_afk" class="form-label">Minutes AFK</label>
                                                    <input class="form-control" type="number" id="minutes_afk" name="minutes_afk"
                                                        value="<?= $user_info['minutes_afk'] ?>" disabled readonly/>
                                                </div>
                                                <div class="mb-3 col-md-2">
                                                    <label for="first_ip" class="form-label">First IP</label>
                                                    <input class="form-control" type="text" id="first_ip" name="first_ip"
                                                        value="<?= $user_info['first_ip'] ?>" disabled readonly/>
                                                </div>
                                                <div class="mb-3 col-md-2">
                                                    <label for="last_ip" class="form-label">Last IP</label>
                                                    <input class="form-control" type="text" id="last_ip" name="last_ip"
                                                        value="<?= $user_info['last_ip'] ?>" disabled readonly/>
                                                </div>
                                                <div class="mb-3 col-md-2">
                                                    <label for="registred" class="form-label">Registred at</label>
                                                    <input class="form-control" type="text" id="registred" name="registred"
                                                        value="<?= $user_info['registred'] ?>" disabled readonly/>
                                                </div>
                                            </div>
                                    </div>
                                    <hr class="my-0" />
                                    <h5 class="card-header">Resources</h5>
                                    <div class="card-body">
                                            <div class="row">
                                                <div class="mb-3 col-md-6">
                                                    <label for="coins" class="form-label">Coins</label>
                                                    <input class="form-control" type="number" id="coins" name="coins"
                                                        value="<?= $user_info['coins'] ?>" placeholder="3" />
                                                </div>
                                                <div class="mb-3 col-md-6">
                                                    <label for="ram" class="form-label">Ram (MB)</label>
                                                    <input class="form-control" type="number" id="ram" name="ram"
                                                        value="<?= $user_info['ram'] ?>" autofocus />
                                                </div>
                                                <div class="mb-3 col-md-6">
                                                    <label for="disk" class="form-label">Disk (MB)</label>
                                                    <input class="form-control" type="number" name="disk" id="disk"
                                                        value="<?= $user_info['disk'] ?>" />
                                                </div>
                                                <div class="mb-3 col-md-6">
                                                    <label for="cpu" class="form-label">VCore (%)</label>
                                                    <input class="form-control" type="number" id="cpu" name="cpu"
                                                        value="<?= $user_info['cpu'] ?>" placeholder="100" />
                                                </div>
                                                <div class="mb-3 col-md-6">
                                                    <label for="server_limit" class="form-label">Server Limit</label>
                                                    <input class="form-control" type="number" id="server_limit"
                                                        name="server_limit" value="<?= $user_info['server_limit'] ?>" />
                                                </div>
                                                <div class="mb-3 col-md-6">
                                                    <label for="ports" class="form-label">Ports</label>
                                                    <input class="form-control" type="number" id="ports" name="ports"
                                                        value="<?= $user_info['ports'] ?>" />
                                                </div>
                                                <div class="mb-3 col-md-6">
                                                    <label for="databases" class="form-label">Databases</label>
                                                    <input class="form-control" type="number" id="databases"
                                                        name="databases" value="<?= $user_info['databases'] ?>" />
                                                </div>

                                                <div class="mb-3 col-md-6">
                                                    <label for="backups" class="form-label">Backups</label>
                                                    <input class="form-control" type="number" id="backups"
                                                        name="backups" value="<?= $user_info['backups'] ?>" />
                                                </div>
                                                <input class="form-control" type="hidden" id="id" name="id"
                                                    value="<?= $_GET['id'] ?>">

                                            </div>
                                            <div class="mt-2">
                                                <button type="submit" name="edit_user" class="btn btn-primary me-2"
                                                    value="true">Save changes</button>
                                                <a href="/admin/users/view" class="btn btn-label-secondary">Cancel</a>
                                            </div>
                                        </form>
                                    </div>
                                </div>
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
                                <form method="GET" action="/admin/users/delete" class="row g-3">
                                    <div class="col-12 text-center">
                                        <button type="submit" name="id" value="<?= $_GET['id'] ?>"
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
                                <form method="GET" action="/admin/users/security/resetkey" class="row g-3">
                                    <div class="col-12 text-center">
                                        <button type="submit" name="id" value="<?= $_GET['id'] ?>"
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
                                <form method="GET" action="/admin/users/security/resetpwd" class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label" for="resetPwd">New Password</label>
                                        <input type="password" id="pwd" name="pwd" class="form-control" placeholder=""
                                            required />
                                    </div>
                                    <div class="col-12 text-center">
                                        <button type="submit" name="id" value="<?= $_GET['id'] ?>"
                                            class="btn btn-danger me-sm-3 me-1">Reset password</button>
                                        <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal"
                                            aria-label="Close">Cancel </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <?php include(__DIR__ . '/../../components/footer.php') ?>
                <div class="content-backdrop fade"></div>
            </div>
        </div>
    </div>
    <div class="layout-overlay layout-menu-toggle"></div>
    <div class="drag-target"></div>
    </div>
    <?php include(__DIR__ . '/../../requirements/footer.php') ?>
    <!-- Page JS -->
    <script src="<?= $appURL ?>/assets/js/pages-account-settings-account.js"></script>
</body>

</html>