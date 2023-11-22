<?php
use MythicalDash\SettingsManager;
use MythicalDash\Main;
include(__DIR__ . '/../requirements/page.php');
include(__DIR__ . '/../requirements/admin.php');

$userCountQuery = "SELECT COUNT(*) AS user_count FROM mythicaldash_users";
$userCountResult = $conn->query($userCountQuery);
$userCount = $userCountResult->fetch_assoc()['user_count'];
$ticketCountQuery = "SELECT COUNT(*) AS ticket_count FROM mythicaldash_tickets";
$ticketCountResult = $conn->query($ticketCountQuery);
$ticketCount = $ticketCountResult->fetch_assoc()['ticket_count'];
$serverCountQuery = "SELECT COUNT(*) AS servers FROM mythicaldash_servers";
$serverCountResult = $conn->query($serverCountQuery);
$serverCount = $serverCountResult->fetch_assoc()['servers'];
$serverQueueQuery = "SELECT COUNT(*) AS serversq FROM mythicaldash_servers_queue";
$serverQueueCountResult = $conn->query($serverQueueQuery);
$serverQueueCount = $serverQueueCountResult->fetch_assoc()['serversq'];
$locationsQuery = "SELECT COUNT(*) AS locations FROM mythicaldash_locations";
$locationsCountResult = $conn->query($locationsQuery);
$locationsCount = $locationsCountResult->fetch_assoc()['locations'];
$eggsQuery = "SELECT COUNT(*) AS eggs FROM mythicaldash_eggs";
$eggsCountResult = $conn->query($eggsQuery);
$eggCount = $eggsCountResult->fetch_assoc()['eggs'];
$TotalServers = $serverCount + $serverQueueCount;
?>
<!DOCTYPE html>

<html lang="en" class="dark-style layout-navbar-fixed layout-menu-fixed" dir="ltr" data-theme="theme-semi-dark"
    data-assets-path="<?= $appURL ?>/assets/" data-template="vertical-menu-template">

<head>
    <meta charset="utf-8" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <?php include(__DIR__ . '/../requirements/head.php'); ?>
    <title>
        <?= SettingsManager::getSetting("name") ?> - Health
    </title>
</head>

<body>
    <!--<div id="preloader" class="discord-preloader">
      <div class="spinner"></div>
   </div>-->
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <?php include(__DIR__ . '/../components/sidebar.php') ?>
            <div class="layout-page">
                <?php include(__DIR__ . '/../components/navbar.php') ?>
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Admin /</span> Health</h4>
                        <?php include(__DIR__ . '/../components/alert.php') ?>
                        <?php
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, "https://api.github.com/repos/mythicalltd/mythicaldash/releases/latest");
                        curl_setopt($ch, CURLOPT_HTTPHEADER, ['User-Agent: MythicalDash']);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                        $response = curl_exec($ch);
                        curl_close($ch);
                        $data = json_decode($response, true);
                        if ($data && isset($data['tag_name'])) {
                            $latestVersion = $data['tag_name'];
                            $pr = $data['prerelease'];
                            if ($pr == true) {
                                $pre = " (Prerelease)";
                            } else {
                                $pre = null;
                            }
                            if ($latestVersion == SettingsManager::getSetting("version")) {
                                ?>
                                <div class="alert alert-success " role="alert">
                                    You are up to date! 
                                    <br><br> Branch: <code><?= $data['target_commitish'] ?></code> <br>Version:
                                    <code><?php echo $data['tag_name'] . $pre ?></code>
                                </div>
                                <?php
                            } else {
                                ?>
                                <div class="alert alert-danger " role="alert">
                                    You are not up-to-date with your MythicalDash installation, make sure to update <a
                                        href="https://docs.mythicalsystems.me/docs/MythicalDash/upgrade">here</a>.
                                </div>
                                <?php
                            }
                        } else {
                            ?>
                            <div class="alert alert-danger " role="alert">
                                Failed to get the info about MythicalDash version system: <br>
                                <code><?= $data['message'] ?></code>
                            </div>
                            <?php
                        }
                        ?>
                        <?php
                        /**
                         * Parse the PHP version to x.x format.
                         *
                         * @return string
                         */
                        function parse_php_version()
                        {
                            preg_match('/^(\d+)\.(\d+)/', PHP_VERSION, $matches);

                            if (count($matches) > 2) {
                                return "{$matches[1]}.{$matches[2]}";
                            }

                            return PHP_VERSION;
                        }

                        $phpVersion = parse_php_version();
                        if ($phpVersion >= '8.1' && $phpVersion <= '8.3') {
                            ?>
                            <div class="alert alert-success " role="alert">
                                    Your php version "<?= $phpVersion ?>" is supported by MythicalDash.
                            </div>
                            <?php
                        } else {
                            ?>
                            <div class="alert alert-danger " role="alert">
                                You are using an outdated version of PHP please update.
                            </div>
                            <?php
                        }
                        ?>
                        <?php 
                        if (is_writable(__DIR__)) {
                            ?>
                            <div class="alert alert-success " role="alert">
                                    You have the right permissions for the MythicalDash directory.
                            </div>
                            <?php
                        } else {
                            ?>
                            <div class="alert alert-danger " role="alert">
                                Please give us permission to the dashbaord directory <code>chown -R www-data:www-data /var/www/mythicaldash/*</code>.
                            </div>
                            <?php
                        }
                        ?>
                        <?php 
                        if (Main::isHTTPS()) {
                            ?>
                            <div class="alert alert-success " role="alert">
                                You are using HTTPS for a secure connection!
                            </div>
                            <?php
                        } else {
                            ?>
                            <div class="alert alert-danger " role="alert">
                                You are not using HTTPS!
                            </div>
                            <?php
                        }
                        ?>
                        <?php 
                        if (file_exists(__DIR__.'/../../public/assets/js/MythicalGuard.js')) {
                            ?>
                            <div class="alert alert-success " role="alert">
                                You are using MythicalGuard!
                            </div>
                            <?php
                        } else {
                            ?>
                            <div class="alert alert-danger " role="alert">
                                You are not using MythicalGuard!
                            </div>
                            <?php
                        }
                        ?>
                        <?php 
                        if (SettingsManager::getSetting("enable_anti_vpn") == "true") {
                            ?>
                            <div class="alert alert-success " role="alert">
                                You are using Anti-VPN Protection!
                            </div>
                            <?php
                        } else {
                            ?>
                            <div class="alert alert-danger " role="alert">
                                You are not using Anti-VPN Protection!
                            </div>
                            <?php
                        }
                        ?>
                        <?php 
                        if (SettingsManager::getSetting("enable_alting") == "true") {
                            ?>
                            <div class="alert alert-success " role="alert">
                                You are using anti alting protection!
                            </div>
                            <?php
                        } else {
                            ?>
                            <div class="alert alert-danger " role="alert">
                                You are not using anti alting protection!                     
                            </div>
                            <?php
                        }
                        ?>
                        <?php 
                        if (SettingsManager::getSetting("enable_turnstile") == "true") {
                            ?>
                            <div class="alert alert-success " role="alert">
                                You are using anti bot protection!
                            </div>
                            <?php
                        } else {
                            ?>
                            <div class="alert alert-danger " role="alert">
                                You are not using anti bot protection!                     
                            </div>
                            <?php
                        }
                        ?>
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
    <script src="<?= $appURL ?>/assets/js/dashboards-ecommerce.js"></script>
</body>

</html>