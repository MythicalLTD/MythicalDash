<?php
use MythicalDash\SettingsManager;
include(__DIR__ . '/../requirements/page.php');

$usersPerPage = 20;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $usersPerPage;

$searchKeyword = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$searchCondition = '';

if (!empty($searchKeyword)) {
    $searchKeyword = '%' . $searchKeyword . '%';
    $searchCondition = " WHERE `username` LIKE '$searchKeyword' OR `email` LIKE '$searchKeyword'";
}

$user_query = "SELECT * FROM mythicaldash_users" . $searchCondition . " ORDER BY `id` LIMIT $offset, $usersPerPage";
$result = $conn->query($user_query);

$totalUsersQuery = "SELECT COUNT(*) AS total_users FROM mythicaldash_users" . $searchCondition;
$totalResult = $conn->query($totalUsersQuery);
$totalUsers = $totalResult->fetch_assoc()['total_users'];
$totalPages = ceil($totalUsers / $usersPerPage);

?>

<!DOCTYPE html>
<html lang="en" class="dark-style layout-navbar-fixed layout-menu-fixed" dir="ltr" data-theme="theme-semi-dark"
    data-assets-path="<?= $appURL ?>/assets/" data-template="vertical-menu-template">

<head>
    <?php include(__DIR__ . '/../requirements/head.php'); ?>
    <title>
        <?= SettingsManager::getSetting("name") ?> - <?= $lang['users']?>
    </title>
    <style>
        .avatar-image {
            width: 30px;
            /* Adjust the size as needed */
            height: 30px;
            /* Adjust the size as needed */
            border-radius: 50%;
        }
    </style>
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
                        <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light"><?= $lang['users']?> /</span> <?= $lang['list']?></h4>
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
                        <!-- Search Form -->
                        <form class="mt-4">
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" placeholder="<?= $lang['search']?> <?= $lang['users']?>..." <?php $displaySearchKeyword = str_replace("%", "", $searchKeyword);?>name="search" value="<?= $displaySearchKeyword  ?>">
                                <button class="btn btn-outline-secondary" type="submit"><?= $lang['search']?></button>
                            </div>
                        </form>
                        <div class="card">
                            <h5 class="card-header">
                                <?= $lang['users']?>
                            </h5>
                            <div class="table-responsive text-nowrap">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th><?= $lang['avatar']?></th>
                                            <th><?= $lang['table_id']?></th>
                                            <th><?= $lang['username']?></th>
                                            <th><?= $lang['leaderboard_role']?></th>
                                            <th><?= $lang['actions']?></th>
                                        </tr>
                                    </thead>
                                    <tbody class="table-border-bottom-0">
                                        <?php
                                        if ($result->num_rows > 0) {
                                            while ($row = $result->fetch_assoc()) {
                                                echo "<tr>";
                                                echo "<td><img src='" . $row['avatar'] . "' alt='Avatar' class='rounded-circle avatar-image'></td>";
                                                echo "<td>" . $row['id'] . "</td>";
                                                echo "<td>" . $row['username'] . "</td>";
                                                echo "<td>" . $row['role'] . "</td>";
                                                echo "<td><a href=\"/user/profile?id=" . $row['id'] . "\" class=\"btn btn-primary\">".$lang['show']."</a></td>";
                                                echo "</tr>";
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
    <script src="<?= $appURL ?>/assets/js/app-user-list.js"></script>
</body>

</html>