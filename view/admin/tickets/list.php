<?php
use MythicalDash\SettingsManager;

include(__DIR__ . '/../../requirements/page.php');
if ($session->getUserInfo("role") == "Administrator" || $session->getUserInfo("role") == "Support") {

} else {
    header('location: /e/401');
    die();
}


$ticketsPerPage = 20;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $ticketsPerPage;

$searchKeyword = isset($_GET['search']) ? $_GET['search'] : '';
$searchCondition = '';
if (!empty($searchKeyword)) {
    $searchCondition = " WHERE `subject` LIKE '%$searchKeyword%' OR `description` LIKE '%$searchKeyword%'";
}
$tickets_query = "SELECT * FROM mythicaldash_tickets" . $searchCondition . " ORDER BY `id` LIMIT $offset, $ticketsPerPage";
$result = $conn->query($tickets_query);
$totalTicketsQuery = "SELECT COUNT(*) AS total_tickets FROM mythicaldash_tickets" . $searchCondition;
$totalResult = $conn->query($totalTicketsQuery);
$totalTickets = $totalResult->fetch_assoc()['total_tickets'];
$totalPages = ceil($totalTickets / $ticketsPerPage);
?>
<!DOCTYPE html>
<html lang="en" class="dark-style layout-navbar-fixed layout-menu-fixed" dir="ltr" data-theme="theme-semi-dark"
    data-assets-path="<?= $appURL ?>/assets/" data-template="vertical-menu-template">

<head>
    <?php include(__DIR__ . '/../../requirements/head.php'); ?>
    <title>
        <?= SettingsManager::getSetting("name") ?> - Tickets
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
                        <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Help-Center / </span>List</h4>
                        <?php include(__DIR__ . '/../../components/alert.php') ?>
                        <!-- Search Form -->
                        <form class="mt-4">
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" placeholder="Search tickets..." name="search"
                                    value="<?= $searchKeyword ?>">
                                <button class="btn btn-outline-secondary" type="submit">Search</button>
                            </div>
                        </form>
                        <!-- Users List Table -->
                        <div class="card">
                            <h5 class="card-header">
                                Tickets
                                <button class="btn btn-primary float-end" data-bs-toggle="modal"
                                    data-bs-target="#createticket">Create New Ticket</button>
                            </h5>
                            <div class="table-responsive text-nowrap">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Subject</th>
                                            <th>Priority</th>
                                            <th>Status</th>
                                            <th>Created</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="table-border-bottom-0">
                                        <?php
                                        if ($result->num_rows > 0) {
                                            while ($row = $result->fetch_assoc()) {
                                                echo "<tr>";
                                                echo "<td>" . $row['id'] . "</td>";
                                                echo "<td>" . $row['subject'] . "</td>";
                                                echo "<td>" . $row['priority'] . "</td>";
                                                echo "<td>" . $row['status'] . "</td>";
                                                echo "<td>" . $row['created'] . "</td>";
                                                echo "<td><a href=\"/help-center/tickets/view?ticketuuid=" . $row['ticketuuid'] . "\" class=\"btn btn-primary\">Open</a></td>";
                                                echo "</tr>";
                                            }
                                        } else {
                                            echo "<tr><br<center><td class='text-center'colspan='5'><br>No tickets found.<br><br>&nbsp;</td></center></tr>";
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
                    <?php include(__DIR__ . '/../../components/modals.php') ?>
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