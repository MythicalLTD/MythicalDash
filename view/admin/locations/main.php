<?php
use MythicalDash\SettingsManager;

include(__DIR__ . '/../../requirements/page.php');
include(__DIR__ . '/../../requirements/admin.php');
$locationsPerPage = 20;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $locationsPerPage;

$searchKeyword = isset($_GET['search']) ? $_GET['search'] : '';
$searchCondition = '';
if (!empty($searchKeyword)) {
    $searchCondition = " WHERE `name` LIKE '%$searchKeyword%' OR `locationid` LIKE '%$searchKeyword%'";
}
$location_query = "SELECT * FROM mythicaldash_locations" . $searchCondition . " ORDER BY `id` LIMIT $offset, $locationsPerPage";
$result = $conn->query($location_query);
$totalLocationsQuery = "SELECT COUNT(*) AS total_locations FROM mythicaldash_locations" . $searchCondition;
$totalResult = $conn->query($totalLocationsQuery);
$totalLocations = $totalResult->fetch_assoc()['total_locations'];
$totalPages = ceil($totalLocations / $locationsPerPage);
?>

<!DOCTYPE html>
<html lang="en" class="dark-style layout-navbar-fixed layout-menu-fixed" dir="ltr" data-theme="theme-semi-dark"
    data-assets-path="<?= $appURL ?>/assets/" data-template="vertical-menu-template">

<head>
    <?php include(__DIR__ . '/../../requirements/head.php'); ?>
    <title>
        <?= SettingsManager::getSetting("name") ?> - Locations
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
                    <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Admin / Locations / </span> List</h4>
                        <?php include(__DIR__ . '/../../components/alert.php') ?>
                        <!-- Search Form -->
                        <form class="mt-4">
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" placeholder="Search location..." name="search"
                                    value="<?= $searchKeyword ?>">
                                <button class="btn btn-outline-secondary" type="submit">Search</button>
                            </div>
                        </form>
                        <!-- Users List Table -->
                        <div class="card">
                            <h5 class="card-header">
                                Locations
                                <button type="button" data-bs-toggle="modal" data-bs-target="#addLocation"
                                    class="btn btn-primary float-end">Add a new Location</button>
                            </h5>
                            <div class="table-responsive text-nowrap">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Status</th>
                                            <th>Location id</th>
                                            <th>Slots</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="table-border-bottom-0">
                                        <?php
                                        if ($result->num_rows > 0) {
                                            while ($row = $result->fetch_assoc()) {
                                                echo "<tr>";
                                                echo "<td>" . $row['name'] . "</td>";
                                                echo "<td>" . $row['status'] . "</td>";
                                                echo "<td>" . $row['locationid'] . "</td>";
                                                echo "<td>" . $row['slots'] . "</td>";
                                                echo "<td><!--<a href=\"/admin/locations/edit?id=" . $row['id'] . "\" class=\"btn btn-primary\">Edit</a>-->&nbsp;<a href=\"/admin/locations/delete?id=" . $row['id'] . "\" class=\"btn btn-danger\">Delete</a></td>";
                                                echo "</tr>";
                                            }
                                        } else {
                                            echo "<tr><br<center><td class='text-center'colspan='5'><br>No location found.<br><br>&nbsp;</td></center></tr>";
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
                <div class="modal fade" id="addLocation" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-simple modal-edit-user">
                        <div class="modal-content p-3 p-md-5">
                            <div class="modal-body">
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                                <div class="text-center mb-4">
                                    <h3 class="mb-2">Add a new location!</h3>
                                    <p class="text-muted">Remember you have to test if the server queue works with this
                                        location id we have no system for checking that when you add the location</p>
                                </div>
                                <form method="GET" action="/admin/locations/create" class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label" for="name">Name</label>
                                        <input type="text" id="name" name="name" class="form-control" placeholder=""
                                            required />
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label" for="locationid">Location id</label>
                                        <input type="number" id="locationid" name="locationid" class="form-control"
                                            placeholder="" required value="1" />
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label" for="slots">Slots</label>
                                        <input type="number" id="slots" name="slots" class="form-control" placeholder=""
                                            required value="50" />
                                    </div>
                                    <div class="col-12 text-center">
                                        <button type="submit" name="create_location" value="create_location"
                                            class="btn btn-primary me-sm-3 me-1">Add location</button>
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