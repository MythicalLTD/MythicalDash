<?php
use MythicalDash\SettingsManager;

include(__DIR__ . '/../../requirements/page.php');
include(__DIR__ . '/../../requirements/admin.php');

$apiPage = 20;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $apiPage;
$searchCondition = "";
$user_query = "SELECT * FROM mythicaldash_apikeys" . $searchCondition . " ORDER BY `id` LIMIT $offset, $apiPage";
$result = $conn->query($user_query);
$totalapikeysQuery = "SELECT COUNT(*) AS total_apikey FROM mythicaldash_apikeys" . $searchCondition;
$totalResult = $conn->query($totalapikeysQuery);
$totalapikeys = $totalResult->fetch_assoc()['total_apikey'];
$totalPages = ceil($totalapikeys / $apiPage);
?>

<!DOCTYPE html>
<html lang="en" class="dark-style layout-navbar-fixed layout-menu-fixed" dir="ltr" data-theme="theme-semi-dark"
    data-assets-path="/assets/" data-template="vertical-menu-template">

<head>
    <?php include(__DIR__ . '/../../requirements/head.php'); ?>
    <title>
        <?= SettingsManager::getSetting("name") ?> - API Keys
    </title>
    <style>
        .avatar-image {
            width: 30px;
            height: 30px;
            border-radius: 50%;
        }
    </style>
</head>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <?php include(__DIR__ . '/../../components/sidebar.php') ?>
            <div class="layout-page">
                <?php include(__DIR__ . '/../../components/navbar.php') ?>
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <?php include(__DIR__ . '/../../components/alert.php') ?>
                        <div class="card">
                            <h5 class="card-header">
                                API Keys
                                <button type="button" data-bs-toggle="modal" data-bs-target="#createapikey"
                                    class="btn btn-primary float-end">Create New Key</button>
                            </h5>
                            <div class="table-responsive text-nowrap">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Key</th>
                                            <th>Owner</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="table-border-bottom-0">
                                        <?php
                                        if ($result->num_rows > 0) {
                                            while ($row = $result->fetch_assoc()) {
                                                echo "<tr>";
                                                echo "<td>" . $row['name'] . "</td>";
                                                echo "<td><code>" . $row['skey'] . "</code></td>";
                                                $check_query = "SELECT * FROM mythicaldash_users WHERE api_key = '" . $row['ownerkey'] . "'";
                                                $resulta = mysqli_query($conn, $check_query);
                                                if (mysqli_num_rows($resulta) > 0) {
                                                    $userdbinfoa = $resulta->fetch_assoc();
                                                    echo '<td><a href="/admin/users/edit?id=' . $userdbinfoa['id'] . '">' . $userdbinfoa['username'] . '<a></td>';
                                                } else {
                                                    echo '<td>None</td>';
                                                }
                                                echo "<td><a href=\"/admin/api/delete?id=" . $row['id'] . "\" class=\"btn btn-danger\">Delete</a></td>";
                                                echo "</tr>";
                                            }
                                        } else {
                                            echo "<tr><br<center><td class='text-center'colspan='5'><br>No api keys found.<br><br>&nbsp;</td></center></tr>";
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
                                    echo '<li class="page-item ' . ($i == $page ? 'active' : '') . '"><a class="page-link" href="?page=' . $i . '">' . $i . '</a></li>';
                                }
                                ?>
                            </ul>
                        </nav>
                        <div class="modal fade" id="createapikey" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-lg modal-simple modal-edit-user">
                                <div class="modal-content p-3 p-md-5">
                                    <div class="modal-body">
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                        <div class="text-center mb-4">
                                            <h3 class="mb-2">Create new API Key!</h3>
                                            <p class="text-muted">Remember, this is an admin API key and cannot be used
                                                for the client API endpoint.</p>
                                        </div>
                                        <form method="GET" action="/admin/api/create" class="row g-3">
                                            <div class="col-12">
                                                <label class="form-label" for="name">Name</label>
                                                <input type="text" id="name" name="name" class="form-control"
                                                    placeholder="" required />
                                            </div>
                                            <div class="col-12 text-center">
                                                <button type="submit" name="key" value="<?= $_COOKIE['token'] ?>"
                                                    class="btn btn-primary me-sm-3 me-1">Create Key</button>
                                                <button type="reset" class="btn btn-label-secondary"
                                                    data-bs-dismiss="modal" aria-label="Close">Cancel </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
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
    <script src="/assets/js/app-user-list.js"></script>
</body>

</html>