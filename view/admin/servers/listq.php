<?php
use MythicalDash\SettingsManager;
include(__DIR__ . '/../../requirements/page.php');
include(__DIR__ . '/../../requirements/admin.php');

$serversPerPage = 20;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $serversPerPage;

$searchKeyword = isset($_GET['search']) ? $_GET['search'] : '';
$searchCondition = '';
if (!empty($searchKeyword)) {
  $searchCondition = " WHERE `ram` LIKE '%$searchKeyword%' OR `ownerid` LIKE '%$searchKeyword%' OR `puid` LIKE '%$searchKeyword%'";
}
$server_query = 'SELECT * FROM mythicaldash_servers_queue' . $searchCondition . " ORDER BY `id` LIMIT $offset, $serversPerPage";
$result = $conn->query($server_query);
$totalServersQuery = 'SELECT COUNT(*) AS total_servers FROM mythicaldash_servers_queue' . $searchCondition;
$totalResult = $conn->query($totalServersQuery);
$totalServers = $totalResult->fetch_assoc()['total_servers'];
$totalPages = ceil($totalServers / $serversPerPage);
?>
<!DOCTYPE html>

<html lang="en" class="dark-style layout-navbar-fixed layout-menu-fixed" dir="ltr" data-theme="theme-semi-dark"
  data-assets-path="<?= $appURL ?>/assets/" data-template="vertical-menu-template">

<head>
  <meta charset="utf-8" />
  <meta name="viewport"
    content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
  <?php include(__DIR__ . '/../../requirements/head.php'); ?>
  <title>
    <?= SettingsManager::getSetting("name") ?> - Servers
  </title>
  <link rel="stylesheet" href="<?= $appURL ?>/assets/vendor/css/pages/page-help-center.css" />
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
            <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Admin /</span> Servers Queue</h4>
            <?php include(__DIR__ . '/../../components/alert.php') ?>
            <!-- Search Form -->
            <form class="mt-4">
              <div class="input-group mb-3">
                <input type="text" class="form-control" placeholder="Search servers..." name="search"
                  value="<?= $searchKeyword ?>">
                <button class="btn btn-outline-secondary" type="submit">Search</button>
              </div>
            </form>
            <!-- Servers List Table -->
            <div class="card">
              <h5 class="card-header">
                Servers
              </h5>
              <div class="table-responsive text-nowrap">
                <table class="table">
                  <thead>
                    <tr>
                      <th>Name</th>
                      <th>Location ID</th>
                      <th>Egg ID</th>
                      <th>Created</th>
                      <th>Action</th>
                    </tr>
                  </thead>
                  <tbody class="table-border-bottom-0">
                    <?php
                    if ($result->num_rows > 0) {
                      while ($row = $result->fetch_assoc()) {
                        echo '<tr>';
                        echo '<td>' . $row['name'] . '</td>';
                        echo '<td>' . $row['location'] . '</td>';
                        echo '<td>' . $row['egg'] . '</td>';
                        echo '<td>' . $row['created'] . '</td>';
                        echo '<td><a href="/admin/server/queue/delete?id=' . $row['id'] . '" class="btn btn-danger">Delete</a></td>';
                        echo '</tr>';
                      }
                    } else {
                        echo "<tr><br<center><td class='text-center'colspan='5'><br>No servers found.<br><br>&nbsp;</td></center></tr>";
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
      </div>
    </div>
    <div class="layout-overlay layout-menu-toggle"></div>
    <div class="drag-target"></div>
  </div>
  <?php include(__DIR__ . '/../../requirements/footer.php') ?>
  <script src="<?= $appURL ?>/assets/js/dashboards-ecommerce.js"></script>
</body>

</html>