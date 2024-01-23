<?php
use MythicalDash\SettingsManager;

include(__DIR__ . '/../requirements/page.php');

$paymentsPerPage = 20;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $paymentsPerPage;

$searchKeyword = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$token = mysqli_real_escape_string($conn, $_COOKIE['token']);
$searchCondition = '';

if (!empty($searchKeyword)) {
    $searchKeyword = '%' . $searchKeyword . '%';
    $searchCondition = " WHERE (`code` LIKE '$searchKeyword' OR `getaway` LIKE '$searchKeyword') AND `ownerkey` = '$token'";
} else {
    $searchCondition = " WHERE `ownerkey` = '$token'";
}

$payments_query = "SELECT * FROM mythicaldash_payments" . $searchCondition . " ORDER BY `id` LIMIT $offset, $paymentsPerPage";
$result = $conn->query($payments_query);

$totalPaymentsQuery = "SELECT COUNT(*) AS total_payments FROM mythicaldash_payments" . $searchCondition;
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
        <?= SettingsManager::getSetting("name") ?> - <?= $lang['payments']?>
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
                        <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light"><?= $lang['users']?> /</span> <?= $lang['payments']?></h4>
                        <?php include(__DIR__ . '/../components/alert.php') ?>
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
                        <div class="row">
                            <div class="col-md-12">
                                <ul class="nav nav-pills flex-column flex-md-row mb-4">
                                    <li class="nav-item">
                                        <a href="/user/edit" class="nav-link"><i class="ti-xs ti ti-users me-1"></i>
                                            <?= $lang['account']?></a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="/user/connections" class="nav-link"><i class="ti-xs ti ti-link me-1"></i>
                                            <?= $lang['connections']?></a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link active" href="/user/payments"><i
                                                class="ti-xs ti ti-currency-euro me-1"></i> <?= $lang['payments']?></a>
                                    </li>
                                </ul>

                                <!-- Search Form -->
                                <form class="mt-4">
                                    <div class="input-group mb-3">
                                        <input type="text" class="form-control" placeholder="<?= $lang['search']?> <?= $lang['payments']?>..."
                                        <?php $displaySearchKeyword = str_replace("%", "", $searchKeyword);?>

                                            name="search" value="<?= $displaySearchKeyword  ?>">
                                        <button class="btn btn-outline-secondary" type="submit"><?= $lang['search']?></button>
                                    </div>
                                </form>
                                <!-- Users List Table -->
                                <div class="card">
                                    <h5 class="card-header">
                                        <?= $lang['payments']?>
                                    </h5>
                                    <div class="table-responsive text-nowrap">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th><?= $lang['table_id']?></th>
                                                    <th><?= $lang['code']?></th>
                                                    <th><?= $lang['coins']?></th>
                                                    <th><?= $lang['getaway']?></th>
                                                    <th><?= $lang['status']?></th>
                                                    <th><?= $lang['actions']?></th>
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
                                                            echo '<td><a href="/store/buy/stripe/coins" class="btn btn-primary">'.$lang['buy_again'].'</a></td>';
                                                        } else {
                                                            echo '<td><a href="/store/buy/stripe/coins" class="btn btn-primary">'.$lang['buy_again'].'</a>&nbsp;<a href="/user/payments/cancel/?id=' . $row['code'] . '" class="btn btn-danger">Cancel</a></td>';
                                                        }
                                                        echo '</tr>';
                                                    }
                                                } else {
                                                    echo "<tr><td colspan='5'><center>".$lang['no_data_found_in_this_table']."</center></td></tr>";
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