<?php
use MythicalDash\SettingsManager;

include(__DIR__ . '/requirements/page.php');
?>
<!DOCTYPE html>
<html lang="en" class="dark-style layout-navbar-fixed layout-menu-fixed" dir="ltr" data-theme="theme-semi-dark"
   data-assets-path="<?= $appURL ?>/assets/" data-template="vertical-menu-template">

<head>
   <meta charset="utf-8" />
   <meta name="viewport"
      content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
   <?php include(__DIR__ . '/requirements/head.php'); ?>
   <title>
      <?= SettingsManager::getSetting("name") ?> - Leaderboard
   </title>
   <link rel="stylesheet" href="<?= $appURL ?>/assets/vendor/css/pages/page-help-center.css" />
</head>

<body>
   <div id="preloader" class="discord-preloader">
      <div class="spinner"></div>
   </div>
   <div class="layout-wrapper layout-content-navbar">
      <div class="layout-container">
         <?php include(__DIR__ . '/components/sidebar.php') ?>
         <div class="layout-page">
            <?php include(__DIR__ . '/components/navbar.php') ?>
            <div class="content-wrapper">
               <div class="container-xxl flex-grow-1 container-p-y">
                  <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Dashboard /</span> Leaderboard</h4>
                  <?php include(__DIR__ . '/components/alert.php') ?>
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
                  <div class="card bg-default shadow">
                     <div class="card-header">
                        <h3 class="card-title mb-0">Coins Leaderboard</h3>
                     </div>
                     <hr class="my-0">
                     <div class="card-body">
                        <?php
                        $users = array();
                        $result = mysqli_query($conn, "SELECT * FROM mythicaldash_users");
                        while ($row = mysqli_fetch_assoc($result)) {
                           $users[] = $row;
                        }
                        usort($users, function ($a, $b) {
                           return $b['coins'] - $a['coins'];
                        });
                        $top5Users = array_slice($users, 0, 5);
                        ?>
                        <table class="table table-striped table-bordered">
                           <thead>
                              <tr>
                                 <th scope="col">Place</th>
                                 <th scope="col">Username</th>
                                 <th scope="col">Role</th>
                                 <th scope="col">Coins</th>
                              </tr>
                           </thead>
                           <tbody>
                              <?php foreach ($top5Users as $i => $user) { ?>
                                 <tr>
                                    <td>
                                       <?php echo $i + 1; ?>
                                    </td>
                                    <td><a href="/user/profile?id=<?php echo $user['id']; ?>">
                                          <?php echo $user['username']; ?>
                                       </a></td>
                                    <td>
                                       <?php echo $user['role']; ?>
                                    </td>
                                    <td>
                                       <?php echo $user['coins']; ?>
                                    </td>
                                 </tr>
                              <?php } ?>
                           </tbody>
                        </table>
                     </div>
                  </div>
                  <br>
                  <div class="card bg-default shadow">
                     <div class="card-header">
                        <h3 class="card-title mb-0">AFK Leaderboard</h3>
                     </div>
                     <hr class="my-0">
                     <div class="card-body">
                        <?php
                        $users = array();
                        $result = mysqli_query($conn, "SELECT * FROM mythicaldash_users");
                        while ($row = mysqli_fetch_assoc($result)) {
                           $users[] = $row;
                        }
                        usort($users, function ($a, $b) {
                           return $b['minutes_afk'] - $a['minutes_afk'];
                        });
                        $top5Users = array_slice($users, 0, 5);
                        ?>
                        <table class="table table-striped table-bordered">
                           <thead>
                              <tr>
                                 <th scope="col">Place</th>
                                 <th scope="col">Username</th>
                                 <th scope="col">Role</th>
                                 <th scope="col">Minutes</th>
                              </tr>
                           </thead>
                           <tbody>
                              <?php foreach ($top5Users as $i => $user) { ?>
                                 <tr>
                                    <td>
                                       <?php echo $i + 1; ?>
                                    </td>
                                    <td><a href="/user/profile?id=<?php echo $user['id']; ?>">
                                          <?php echo $user['username']; ?>
                                       </a></td>
                                    <td>
                                       <?php echo $user['role']; ?>
                                    </td>
                                    <td>
                                       <?php echo $user['minutes_afk']; ?>
                                    </td>
                                 </tr>
                              <?php } ?>
                           </tbody>
                        </table>
                     </div>
                  </div>
               </div>
               <?php include(__DIR__ . '/components/footer.php') ?>
               <div class="content-backdrop fade"></div>
            </div>
         </div>
      </div>
      <div class="layout-overlay layout-menu-toggle"></div>
      <div class="drag-target"></div>
   </div>
   <?php include(__DIR__ . '/requirements/footer.php') ?>
   <script src="<?= $appURL ?>/assets/js/dashboards-ecommerce.js"></script>
</body>

</html>