<?php
use MythicalDash\SettingsManager;

include(__DIR__ . '/../requirements/page.php');
if (isset($_GET['unlink_discord'])) {
    $conn->query("UPDATE `mythicaldash_users` SET `discord_linked` = 'false' WHERE `mythicaldash_users`.`api_key` = '" . mysqli_real_escape_string($conn, $_COOKIE['token']) . "';");
    $conn->close();
    header('location: /user/connections');
}

$paymentsPerPage = 20;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $paymentsPerPage;

$searchKeyword = isset($_GET['search']) ? $_GET['search'] : '';
$searchCondition = '';
if (!empty($searchKeyword)) {
    $searchCondition = " WHERE `code` LIKE '%$searchKeyword%' OR `getaway` LIKE '%$searchKeyword%'";
}
$payments_query = 'SELECT * FROM mythicaldash_payments' . $searchCondition . " ORDER BY `id` LIMIT $offset, $paymentsPerPage";
$result = $conn->query($payments_query);
$totalPaymentsQuery = 'SELECT COUNT(*) AS total_payments FROM mythicaldash_payments' . $searchCondition;
$totalResult = $conn->query($totalPaymentsQuery);
$totalPayments = $totalResult->fetch_assoc()['total_payments'];
$totalPages = ceil($totalPayments / $paymentsPerPage);
?>
<!DOCTYPE html>
<html lang="en" class="dark-style layout-navbar-fixed layout-menu-fixed" dir="ltr" data-theme="theme-semi-dark"
    data-assets-path="<?= $appURL ?>/assets/" data-template="vertical-menu-template">

<head>
    <?php include(__DIR__ . '/../requirements/head.php'); ?>
    <title>
        <?= SettingsManager::getSetting("name") ?> - Payments
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
                                        <a href="/user/edit" class="nav-link"><i class="ti-xs ti ti-users me-1"></i>
                                            Account</a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="/user/edit" class="nav-link"><i class="ti-xs ti ti-link me-1"></i>
                                            Connections</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link active" href="/user/payments"><i
                                                class="ti-xs ti ti-currency-euro me-1"></i> Payments</a>
                                    </li>
                                </ul>

                                <!-- Search Form -->
                                <form class="mt-4">
                                    <div class="input-group mb-3">
                                        <input type="text" class="form-control" placeholder="Search users..."
                                            name="search" value="<?= $searchKeyword ?>">
                                        <button class="btn btn-outline-secondary" type="submit">Search</button>
                                    </div>
                                </form>
                                <!-- Users List Table -->
                                <div class="card">
                                    <h5 class="card-header">
                                        Users
                                    </h5>
                                    <div class="table-responsive text-nowrap">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Code</th>
                                                    <th>Coins</th>
                                                    <th>Getaway</th>
                                                    <th>Status</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody class="table-border-bottom-0">
                                                <?php
                                                if ($result->num_rows > 0) {
                                                    while ($row = $result->fetch_assoc()) {
                                                        echo '<tr>';
                                                        echo '<td>#' . $row['id'] . '</td>';
                                                        echo '<td><code>' . $row['code'] . '</code></td>';
                                                        echo '<td>' . $row['coins'] . '</td>';
                                                        echo '<td>' . $row['getaway'] . '</td>';
                                                        echo '<td>' . $row['status'] . '</td>';
                                                        if ($row['status'] == "paid") {
                                                            echo '<td><a href="/store/buy/stripe/coins" class="btn btn-primary">Buy Again</a></td>';
                                                        } else {
                                                            echo '<td><a href="/store/buy/stripe/coins" class="btn btn-primary">Buy Again</a>&nbsp;<a href="/user/payments/cancel/?id=' . $row['code'] . '" class="btn btn-danger">Cancel</a></td>';
                                                        }
                                                        echo '</tr>';
                                                    }
                                                } else {
                                                    echo "<tr><td colspan='5'>No payments found.</td></tr>";
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <nav aria-label="Page navigation">
                                    <ul class="pagination justify-content-center mt-4">
                                        <?php
                                        for ($i = 1; $i <= $totalPages; $i++) {
                                            echo '<li class="page-item ' . ($i == $page ? 'active' : '') . '"><a class="page-link" href="?page=' . $i . '&search=' . $searchKeyword . '">' . $i . '</a></li>';
                                        }
                                        ?>
                                    </ul>
                                </nav>
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