<?php
include(__DIR__ . '/../../requirements/page.php');
include(__DIR__ . '/../../requirements/admin.php');

if (isset($_POST['create_user'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $firstName = mysqli_real_escape_string($conn, $_POST['firstName']);
    $lastName = mysqli_real_escape_string($conn, $_POST['lastName']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $upassword = mysqli_real_escape_string($conn, $_POST['password']);
    $password = password_hash($upassword, PASSWORD_DEFAULT);
    $skey = generate_key($email, $password);
    if ($username == "" || $firstName == "" || $lastName == "" || $email == "" || $upassword == "") {
        header('location: /admin/users/new?e=Please fill in all info');
    } else {
        $check_query = "SELECT * FROM mythicaldash_users WHERE username = '$username' OR email = '$email'";
        $result = mysqli_query($conn, $check_query);
        if (mysqli_num_rows($result) > 0) {
            header('location: /admin/users/new?e=Username or email already exists. Please choose a different one');
            $conn->close();
            die();
        } else {
            $conn->query("INSERT INTO `mythicaldash_users` (`email`, `username`, `first_name`, `last_name`, `password`, `api_key`) VALUES ('" . $email . "', '" . $username . "', '" . $firstName . "', '" . $lastName . "', '" . $password . "', '" . $skey . "');");
            NewUser();
            header('location: /admin/users');
            $conn->close();
            die();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="dark-style layout-navbar-fixed layout-menu-fixed" dir="ltr" data-theme="theme-semi-dark"
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
                        <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Admin / Users /</span> New</h4>
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
                        <div class="row">
                            <div class="col-md-12">
                                <div class="card mb-4">
                                    <h5 class="card-header">Profile Details</h5>
                                    <hr class="my-0" />
                                    <div class="card-body">
                                        <form action="/admin/users/new" method="POST">
                                            <div class="row">
                                                <div class="mb-3 col-md-6">
                                                    <label for="username" class="form-label">Username</label>
                                                    <input class="form-control" type="text" id="username"
                                                        name="username" value="" placeholder="jhondoe" />
                                                </div>
                                                <div class="mb-3 col-md-6">
                                                    <label for="firstName" class="form-label">First Name</label>
                                                    <input class="form-control" type="text" id="firstName"
                                                        name="firstName" value="" autofocus placeholder="Jhon" />
                                                </div>
                                                <div class="mb-3 col-md-6">
                                                    <label for="lastName" class="form-label">Last Name</label>
                                                    <input class="form-control" type="text" name="lastName"
                                                        id="lastName" value="" placeholder="Doe" />
                                                </div>
                                                <div class="mb-3 col-md-6">
                                                    <label for="email" class="form-label">E-mail</label>
                                                    <input class="form-control" type="email" id="email" name="email"
                                                        value="" placeholder="john.doe@example.com" />
                                                </div>
                                                <div class="mb-3 col-md-6">
                                                    <label for="password" class="form-label">Password</label>
                                                    <input class="form-control" type="password" id="password"
                                                        name="password" value="" />
                                                </div>
                                            </div>
                                            <div class="mt-2">
                                                <button type="submit" name="create_user" class="btn btn-primary me-2"
                                                    value="true">Create user</button>
                                                <a href="/admin/users" class="btn btn-label-secondary">Cancel</a>
                                            </div>
                                        </form>
                                    </div>
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
    <script src="../../assets/js/pages-account-settings-account.js"></script>
</body>

</html>