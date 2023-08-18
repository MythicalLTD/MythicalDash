<?php
include(__DIR__ . '/../../requirements/page.php');

$redeemPages = 20;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $redeemPages;
$searchCondition = "";
$redeem_query = "SELECT * FROM mythicaldash_redeem" . $searchCondition . " ORDER BY `id` LIMIT $offset, $redeemPages";
$result = $conn->query($redeem_query);
$redeem_totalredeemquery = "SELECT COUNT(*) AS total_apikey FROM mythicaldash_redeem" . $searchCondition;
$totalResult = $conn->query($redeem_totalredeemquery);
$redeem_totalredeems = $totalResult->fetch_assoc()['total_apikey'];
$totalPages = ceil($redeem_totalredeems / $redeemPages);
?>

<!DOCTYPE html>
<html lang="en" class="light-style layout-navbar-fixed layout-menu-fixed" dir="ltr" data-theme="theme-semi-dark"
    data-assets-path="/assets/" data-template="vertical-menu-template">

<head>
    <?php include(__DIR__ . '/../../requirements/head.php'); ?>
    <title>
        <?= $settings['name'] ?> | Redeem Keys
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
                        <div class="card">
                            <h5 class="card-header">
                                Redeem Keys
                                <button type="button" data-bs-toggle="modal" data-bs-target="#createkey"
                                    class="btn btn-primary float-end">Create New Key</button>
                            </h5>
                            <div class="table-responsive text-nowrap">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Uses</th>
                                            <th>Coins</th>
                                            <th>Ram</th>
                                            <th>Disk</th>
                                            <th>Cpu/s</th>
                                            <th>Server limit</th>
                                            <th>Ports</th>
                                            <th>Databases</th>
                                            <th>Backups</th>
                                            <th>Created</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="table-border-bottom-0">
                                        <?php
                                        if ($result->num_rows > 0) {
                                            while ($row = $result->fetch_assoc()) {
                                                ?>
                                                <tr>
                                                    <td>
                                                        <?= $row['uses'] ?>
                                                    </td>
                                                    <td>
                                                        <?= $row['coins'] ?>
                                                    </td>
                                                    <td>
                                                        <?= $row['ram'] ?>
                                                    </td>
                                                    <td>
                                                        <?= $row['disk'] ?>
                                                    </td>
                                                    <td>
                                                        <?= $row['cpu'] ?>
                                                    </td>
                                                    <td>
                                                        <?= $row['server_limit'] ?>
                                                    </td>
                                                    <td>
                                                        <?= $row['ports'] ?>
                                                    </td>
                                                    <td>
                                                        <?= $row['databases'] ?>
                                                    </td>
                                                    <td>
                                                        <?= $row['backups'] ?>
                                                    </td>
                                                    <td>
                                                        <?= $row['created'] ?>
                                                    </td>
                                                    <td><button onclick="copyToClipboard('<?= $row['code'] ?>');" class="btn btn-primary">Copy</button>
                                                    &nbsp;
                                                    <a href="/admin/redeem/delete?code=<?= $row['code'] ?>" class="btn btn-danger">Delete</a></td>
                                                </tr>
                                                <?php
                                            }
                                        } else {
                                            echo "<tr><br<center><td class='text-center'colspan='5'><br>No redeem key found.<br><br>&nbsp;</td></center></tr>";
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
                        <div class="modal fade" id="createkey" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-lg modal-simple modal-edit-user">
                                <div class="modal-content p-3 p-md-5">
                                    <div class="modal-body">
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                        <div class="text-center mb-4">
                                            <h3 class="mb-2">Create new redeem Key!</h3>
                                            <p class="text-muted">This redeem key can be claimed at: <a
                                                    href="<?= $appURL ?>/earn/redeem"><?= $appURL ?>/earn/redeem</a></p>
                                        </div>
                                        <form method="GET" action="/admin/redeem/create" class="row g-3">
                                            <div class="col-12">
                                                <label class="form-label" for="code">Code</label>
                                                <input type="text" id="code" name="code" class="form-control"
                                                    placeholder="<?= generate_key_redeem() ?>" required
                                                    value="<?= generate_key_redeem() ?>" />
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label" for="uses">Uses</label>
                                                <input type="number" id="uses" name="uses" class="form-control"
                                                    placeholder="" required value="1" />
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label" for="coins">Coins</label>
                                                <input type="number" id="coins" name="coins" class="form-control"
                                                    placeholder="" required value="1" />
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label" for="ram">Ram</label>
                                                <input type="number" id="ram" name="ram" class="form-control"
                                                    placeholder="" required value="1024" />
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label" for="disk">Disk</label>
                                                <input type="number" id="disk" name="disk" class="form-control"
                                                    placeholder="" required value="2048" />
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label" for="cpu">Cpu</label>
                                                <input type="number" id="cpu" name="cpu" class="form-control"
                                                    placeholder="" required value="100" />
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label" for="server_limit">Server Limit</label>
                                                <input type="number" id="server_limit" name="server_limit"
                                                    class="form-control" placeholder="" required value="1" />
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label" for="ports">Ports</label>
                                                <input type="number" id="ports" name="ports" class="form-control"
                                                    placeholder="" required value="1" />
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label" for="databases">Databases</label>
                                                <input type="number" id="databases" name="databases"
                                                    class="form-control" placeholder="" required value="1" />
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label" for="backups">Backups</label>
                                                <input type="number" id="backups" name="backups" class="form-control"
                                                    placeholder="" required value="1" />
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
    <script>
        function copyToClipboard(text) {
            const textArea = document.createElement("textarea");
            textArea.value = text;

            // Style the textarea to be invisible
            textArea.style.position = "fixed";
            textArea.style.top = 0;
            textArea.style.left = 0;
            textArea.style.opacity = 0;

            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();

            try {
                const success = document.execCommand("copy");
                if (success) {
                    console.log("Text copied to clipboard: ", text);
                } else {
                    console.error("Copying to clipboard failed.");
                }
            } catch (err) {
                console.error("Unable to copy to clipboard:", err);
            }

            document.body.removeChild(textArea);
        }
    </script>
</body>

</html>