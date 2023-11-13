<?php
use MythicalDash\SettingsManager;

include(__DIR__ . '/../../requirements/page.php');
include(__DIR__ . '/../../requirements/admin.php');
$eggsPerPage = 20;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $eggsPerPage;

$searchKeyword = isset($_GET['search']) ? $_GET['search'] : '';
$searchCondition = '';
if (!empty($searchKeyword)) {
    $searchCondition = " WHERE `setting_name` LIKE '%$searchKeyword%' OR `setting_value` LIKE '%$searchKeyword%'";
}
$egg_query = "SELECT * FROM mythicaldash_eggs_config" . $searchCondition . " ORDER BY `id` LIMIT $offset, $eggsPerPage";
$result = $conn->query($egg_query);
$totalEggsQuery = "SELECT COUNT(*) AS total_eggs FROM mythicaldash_eggs_config" . $searchCondition;
$totalResult = $conn->query($totalEggsQuery);
$totalEggs = $totalResult->fetch_assoc()['total_eggs'];
$totalPages = ceil($totalEggs / $eggsPerPage);
?>

<!DOCTYPE html>
<html lang="en" class="dark-style layout-navbar-fixed layout-menu-fixed" dir="ltr" data-theme="theme-semi-dark"
    data-assets-path="<?= $appURL ?>/assets/" data-template="vertical-menu-template">

<head>
    <?php include(__DIR__ . '/../../requirements/head.php'); ?>
    <title>
        <?= SettingsManager::getSetting("name") ?> - Eggs Config
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
                    <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Admin / Eggs / </span> List</h4>
                        <?php include(__DIR__ . '/../../components/alert.php') ?>
                        <!-- Search Form -->
                        <form class="mt-4">
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" placeholder="Search in eggs config..."
                                    name="search" value="<?= $searchKeyword ?>">
                                <button class="btn btn-outline-secondary" type="submit">Search</button>
                            </div>
                        </form>
                        <!-- Users List Table -->
                        <div class="card">
                            <h5 class="card-header">
                                Eggs Configuration
                                <button type="button" data-bs-toggle="modal" data-bs-target="#addeggcfg"
                                    class="btn btn-primary float-end">Add a new egg config</button>
                            </h5>
                            <div class="table-responsive text-nowrap">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Name</th>
                                            <th>Value</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="table-border-bottom-0">
                                        <?php
                                        if ($result->num_rows > 0) {
                                            while ($row = $result->fetch_assoc()) {
                                                echo "<tr>";
                                                echo "<td>#" . $row['id'] . "</td>";
                                                echo "<td>" . $row['setting_name'] . "</td>";
                                                echo "<td>" . $row['setting_value'] . "</td>";
                                                echo "<td><!--<a href=\"/admin/eggs/edit?id=" . $row['id'] . "\" class=\"btn btn-primary\">Edit</a>-->&nbsp;<a href=\"/admin/eggs/config/delete?name=" . $row['setting_name'] . "\" class=\"btn btn-danger\">Delete</a></td>";
                                                echo "</tr>";
                                            }
                                        } else {
                                            echo "<tr><br<center><td class='text-center'colspan='5'><br>No users found.<br><br>&nbsp;</td></center></tr>";
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
                    <?php include(__DIR__ . '/../../components/footer.php') ?>
                    <div class="content-backdrop fade"></div>
                </div>
                <div class="modal fade" id="addeggcfg" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-simple modal-edit-user">
                        <div class="modal-content p-3 p-md-5">
                            <div class="modal-body">
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                                <div class="text-center mb-4">
                                    <h3 class="mb-2">Add a new egg config!</h3>
                                    <p class="text-muted">Remember you have to test if the server queue works with this
                                        config we have no system for checking that.</p>
                                </div>
                                <form method="GET" action="/admin/eggs/config/create" class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label" for="name">Name</label>
                                        <input type="text" id="name" name="name" class="form-control"
                                            placeholder="SERVER_NAME" required />
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label" for="value">Value</label>
                                        <input type="text" id="value" name="value" class="form-control"
                                            placeholder="Minecraft" required />
                                    </div>
                                    <div class="col-12 text-center">
                                        <button type="submit" name="create_egg" value="create_egg"
                                            class="btn btn-primary me-sm-3 me-1">Add egg</button>
                                        <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal"
                                            aria-label="Close">Cancel </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="layout-overlay layout-menu-toggle"></div>
        <div class="drag-target"></div>
    </div>
    <?php include(__DIR__ . '/../../requirements/footer.php') ?>
    <script src="<?= $appURL ?>/assets/js/app-user-list.js"></script>
</body>

</html>