<?php
use MythicalDash\SettingsManager;

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
      <?= SettingsManager::getSetting("name") ?> - Admin
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
                  <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Admin /</span> Statistics</h4>
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
                           <br><br> Branch: <code><?= $data['target_commitish'] ?></code> <br>Version: <code><?php echo $data['tag_name'].$pre ?></code>
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
                        Failed to get the info about MythicalDash version system.
                     </div>
                     <?php
                  }
                  ?>
                  <div class="">
                     <!-- Statistics -->
                     <div class="card h-100">
                        <div class="card-header">
                           <div class="d-flex justify-content-between mb-3">
                              <h5 class="card-title mb-0">Statistics</h5>
                              <small id="updateText" class="text-muted">Updated 0 seconds ago</small>
                           </div>
                        </div>
                        <div class="card-body">
                           <div class="row gy-3">
                              <div class="col-md-3 col-6">
                                 <div class="d-flex align-items-center">
                                    <div class="badge rounded-pill bg-label-primary me-3 p-2">
                                       <i class="fas fa-users fa-2x"></i>
                                    </div>
                                    <div class="card-info">
                                       <h5 class="mb-0">
                                          <?= $userCount ?>
                                       </h5>
                                       <small>Users</small>
                                    </div>
                                 </div>
                              </div>
                              <div class="col-md-3 col-6">
                                 <div class="d-flex align-items-center">
                                    <div class="badge rounded-pill bg-label-primary me-3 p-2">
                                       <i class="fa fa-server fa-2x"></i>
                                    </div>
                                    <div class="card-info">
                                       <h5 class="mb-0">
                                          <?= $TotalServers ?>
                                       </h5>
                                       <small>Servers</small>
                                    </div>
                                 </div>
                              </div>
                              <div class="col-md-3 col-6">
                                 <div class="d-flex align-items-center">
                                    <div class="badge rounded-pill bg-label-primary me-3 p-2">
                                       <i class="ti ti-messages fa-2x"></i>
                                    </div>
                                    <div class="card-info">
                                       <h5 class="mb-0">
                                          <?= $ticketCount ?>
                                       </h5>
                                       <small>Tickets</small>
                                    </div>
                                 </div>
                              </div>
                              <div class="col-md-3 col-6">
                                 <div class="d-flex align-items-center">
                                    <div class="badge rounded-pill bg-label-primary me-3 p-2">
                                       <i class="fas fa-flag fa-2x"></i>
                                    </div>
                                    <div class="card-info">
                                       <h5 class="mb-0">
                                          <?= $locationsCount ?>
                                       </h5>
                                       <small>Locations</small>
                                    </div>
                                 </div>
                              </div>
                           </div>
                        </div>
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
   <script src="<?= $appURL ?>/assets/js/dashboards-ecommerce.js"></script>
   <script>
      function updateElapsedTime() {
         const updateTextElement = document.getElementById('updateText');
         const startDate = new Date();

         function updateText() {
            const now = new Date();
            const timeDiff = now - startDate;
            let elapsed = '';

            if (timeDiff >= 1000 * 60 * 60 * 24 * 30) {
               const months = Math.floor(timeDiff / (1000 * 60 * 60 * 24 * 30));
               elapsed = months === 1 ? 'month' : 'months';
               updateTextElement.textContent = `Updated ${months} ${elapsed} ago`;
            } else if (timeDiff >= 1000 * 60 * 60 * 24) {
               const days = Math.floor(timeDiff / (1000 * 60 * 60 * 24));
               elapsed = days === 1 ? 'day' : 'days';
               updateTextElement.textContent = `Updated ${days} ${elapsed} ago`;
            } else if (timeDiff >= 1000 * 60 * 60) {
               const hours = Math.floor(timeDiff / (1000 * 60 * 60));
               elapsed = hours === 1 ? 'hour' : 'hours';
               updateTextElement.textContent = `Updated ${hours} ${elapsed} ago`;
            } else if (timeDiff >= 1000 * 60) {
               const minutes = Math.floor(timeDiff / (1000 * 60));
               elapsed = minutes === 1 ? 'minute' : 'minutes';
               updateTextElement.textContent = `Updated ${minutes} ${elapsed} ago`;
            } else {
               const seconds = Math.floor(timeDiff / 1000);
               elapsed = seconds === 1 ? 'second' : 'seconds';
               updateTextElement.textContent = `Updated ${seconds} ${elapsed} ago`;
            }
         }

         setInterval(updateText, 1000);
      }
      window.onload = updateElapsedTime;
   </script>
</body>

</html>