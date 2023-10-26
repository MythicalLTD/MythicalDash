<?php
use MythicalDash\ErrorHandler;
use MythicalDash\SettingsManager;
include('requirements/page.php');
$nuserdb = $conn->query("SELECT * FROM mythicaldash_users WHERE api_key = '" . mysqli_real_escape_string($conn, $_COOKIE['token']) . "'")->fetch_array();
$servers = mysqli_query($conn, "SELECT * FROM mythicaldash_servers WHERE uid = '" . mysqli_real_escape_string($conn, $_COOKIE['token']) . "'");
$servers_in_queue = mysqli_query($conn, "SELECT * FROM mythicaldash_servers_queue WHERE ownerid = '" . mysqli_real_escape_string($conn, $_COOKIE['token']) . "'");
$serversnumber = $servers->num_rows + $servers_in_queue->num_rows;
function percentage($number, $total, $outof)
{
   $result = ($number / $total) * $outof;
   return round($result);
}
$usedRam = 0;
$usedDisk = 0;
$usedCpu = 0;
$usedPorts = 0;
$usedDatabase = 0;
$usedBackup = 0;
$uservers = array();
foreach ($servers as $serv) {
   $ptid = $serv["pid"];
   $ch = curl_init(SettingsManager::getSetting("PterodactylURL") . "/api/application/servers/" . $ptid);
   curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
   curl_setopt(
      $ch,
      CURLOPT_HTTPHEADER,
      array(
         "Authorization: Bearer " . SettingsManager::getSetting("PterodactylAPIKey"),
         "Content-Type: application/json",
         "Accept: Application/vnd.pterodactyl.v1+json"
      )
   );
   $result1 = curl_exec($ch);
   $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
   if ($httpcode != 200) {
      ErrorHandler::ShowCritical("Unable to connect to the game panel! Please contact one of the server administrators.");
   }
   curl_close($ch);
   $result = json_decode($result1, true);
   $id = $result['attributes']["uuid"];
   $name = $result['attributes']['name'];
   $ram = $result['attributes']['limits']['memory'];
   $disk = $result['attributes']['limits']['disk'];
   $cpuh = $result['attributes']['limits']['cpu'];
   $db = $result['attributes']['feature_limits']['databases'];
   $usedRam = $usedRam + $ram;
   $usedDisk = $usedDisk + $disk;
   $alloc = $result['attributes']['feature_limits']['allocations'] - 1;
   $usedBackup = $result['attributes']['feature_limits']['backups'];
   $usedPorts = $usedPorts + $alloc;
   $usedDatabase = $usedDatabase + $db;
   $usedCpu = $usedCpu + $cpuh;
   array_push($uservers, $result['attributes']);
}
foreach ($servers_in_queue as $server) {
   $usedRam = $usedRam + $server['ram'];
   $usedDisk = $usedDisk + $server['disk'];
   $usedPorts = $usedPorts + $server['xtra_ports'];
   $usedBackup = $usedBackup + $server['backuplimit'];
   $usedDatabase = $usedDatabase + $server['databases'];
   $usedCpu = $usedCpu + $server["cpu"];
}

?>
<!DOCTYPE html>
<html lang="en" class="dark-style layout-navbar-fixed layout-menu-fixed" dir="ltr" data-theme="theme-semi-dark"
   data-assets-path="<?= $appURL ?>/assets/" data-template="vertical-menu-template">
<head>
   <?php include('requirements/head.php'); ?>
   <title>
      <?= SettingsManager::getSetting("name") ?> - Dashboard
   </title>
   
</head>

<body>
   <div id="preloader" class="discord-preloader">
      <div class="spinner"></div>
   </div>
   <div class="layout-wrapper layout-content-navbar">
      <div class="layout-container">
         <?php include('components/sidebar.php') ?>
         <div class="layout-page">
            <?php include('components/navbar.php') ?>
            <div class="content-wrapper">
               <div class="container-xxl flex-grow-1 container-p-y">
                  <?php include(__DIR__ . '/components/alert.php') ?>
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
                                       <i class="fas fa-memory fa-2x"></i>
                                    </div>
                                    <div class="card-info">
                                       <h5 class="mb-0">
                                          <?= $usedRam . "MB/" . $nuserdb["ram"] ?>MB
                                       </h5>
                                       <small>Memory</small>
                                    </div>
                                 </div>
                              </div>
                              <div class="col-md-3 col-6">
                                 <div class="d-flex align-items-center">
                                    <div class="badge rounded-pill bg-label-primary me-3 p-2">
                                       <i class="fa fa-save fa-2x"></i>
                                    </div>
                                    <div class="card-info">
                                       <h5 class="mb-0">
                                          <?= $usedDisk . "MB/" . $nuserdb["disk"] ?>MB
                                       </h5>
                                       <small>Disk</small>
                                    </div>
                                 </div>
                              </div>
                              <div class="col-md-3 col-6">
                                 <div class="d-flex align-items-center">
                                    <div class="badge rounded-pill bg-label-primary me-3 p-2">
                                       <i class="fas fa-microchip fa-2x"></i>
                                    </div>
                                    <div class="card-info">
                                       <h5 class="mb-0">
                                          <?= $usedCpu . "%/" . $nuserdb["cpu"] ?>%
                                       </h5>
                                       <small>Cores</small>
                                    </div>
                                 </div>
                              </div>
                              <div class="col-md-3 col-6">
                                 <div class="d-flex align-items-center">
                                    <div class="badge rounded-pill bg-label-primary me-3 p-2">
                                       <i class="fas fa-server fa-2x"></i>
                                    </div>
                                    <div class="card-info">
                                       <h5 class="mb-0">
                                          <?= $serversnumber . "/" . $nuserdb["server_limit"] ?>
                                       </h5>
                                       <small>Servers</small>
                                    </div>
                                 </div>
                              </div>
                           </div>

                           <div class="row gy-3">
                              <div class="col-md-3 col-6">
                                 <div class="d-flex align-items-center">
                                    <div class="badge rounded-pill bg-label-primary me-3 p-2">
                                       <i class="fa fa-network-wired fa-2x"></i>
                                    </div>
                                    <div class="card-info">
                                       <h5 class="mb-0">
                                          <?= $usedBackup . "/" . $nuserdb["backups"] ?>
                                       </h5>
                                       <small>Backups</small>
                                    </div>
                                 </div>
                              </div>
                              <div class="col-md-3 col-6">
                                 <div class="d-flex align-items-center">
                                    <div class="badge rounded-pill bg-label-primary me-3 p-2">
                                       <i class="fas fa-microchip fa-2x"></i>
                                    </div>
                                    <div class="card-info">
                                       <h5 class="mb-0">
                                          <?= $usedPorts . "/" . $nuserdb["ports"] ?>
                                       </h5>
                                       <small>Allocations</small>
                                    </div>
                                 </div>
                              </div>
                              <div class="col-md-3 col-6">
                                 <div class="d-flex align-items-center">
                                    <div class="badge rounded-pill bg-label-primary me-3 p-2">
                                       <i class="fas fa-database fa-2x"></i>
                                    </div>
                                    <div class="card-info">
                                       <h5 class="mb-0">
                                          <?= $usedDatabase . "/" . $nuserdb["databases"] ?>
                                       </h5>
                                       <small>Databases</small>
                                    </div>
                                 </div>
                              </div>
                              <div class="col-md-3 col-6">
                                 <div class="d-flex align-items-center">
                                    <div class="badge rounded-pill bg-label-primary me-3 p-2">
                                       <i class="fas fa-coins fa-2x"></i>
                                    </div>
                                    <div class="card-info">
                                       <h5 class="mb-0">
                                          <?= $nuserdb["coins"] ?>
                                       </h5>
                                       <small>Coins</small>
                                    </div>
                                 </div>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
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
                  <div class="row">
                     <div class="col">
                        <div class="card bg-default shadow">
                           <div class="card-header bg-transparent border-0">
                              <h5 class="card-title mb-0">Your servers</h5>
                           </div>
                           <div class="table-responsive">
                              <table class="table align-items-center table-flush">
                                 <tbody class="list">
                                    <?php
                                    if (count($uservers) == 0 && $servers_in_queue->num_rows == 0) {
                                       ?>
                                       <div style="text-align: center;">
                                          <img src="<?= $appURL ?>/assets/img/empty.svg" height="150" />
                                          <br>
                                          <h4 style="">No servers yet. Why not create one?</h4>
                                          <a href="/server/create" class="btn btn-primary">Create a new
                                             server</a><br /><br />
                                       </div>
                                       <?php
                                    } else {
                                       ?>
                                       <thead class="">
                                          <tr>
                                             <th scope="col">Server name</th>
                                             <th scope="col">Node</th>
                                             <th scope="col">Status</th>
                                             <th scope="col">Server type</th>
                                             <th scope="col">CPU</th>
                                             <th scope="col">RAM</th>
                                             <th scope="col">Disk</th>
                                             <th scope="col">Actions</th>
                                          </tr>
                                       </thead>
                                       <?php
                                    }
                                    foreach ($servers_in_queue as $server) {
                                       $currentqueue = mysqli_query($conn, "SELECT * FROM mythicaldash_servers_queue")->num_rows;
                                       $egg = $conn->query("SELECT * FROM mythicaldash_eggs WHERE id = " . $server['egg'])->fetch_array();
                                       $location = $conn->query("SELECT * FROM mythicaldash_locations WHERE id = " . $server['location'])->fetch_array();
                                       $currentnodequeue = mysqli_query($conn, "SELECT id FROM mythicaldash_servers_queue ORDER BY type DESC");
                                       $serverpos = 0;
                                       foreach ($currentnodequeue as $queue) {
                                          $serverpos++;
                                          if ($queue['id'] == $server['id']) {
                                             break;
                                          }
                                       }
                                       ?>
                                       <tr>
                                          <th scope="row">
                                             <div class="media align-items-center">
                                                <div class="media-body">
                                                   <span class="name mb-0 text-sm ">
                                                      <?= $server["name"] ?>
                                                   </span>
                                                </div>
                                             </div>
                                          </th>
                                          <td class="">
                                             <?= $location["name"] ?>
                                          </td>
                                          <td>
                                             <code> In queue (Position <?= $serverpos . "/" . $currentnodequeue->num_rows ?>)</code>
                                          </td>
                                          <td class="">
                                             <?= $egg["name"] ?>
                                          </td>
                                          <td class="">
                                             <?= $server["cpu"] ?>%
                                          </td>
                                          <td class="">
                                             <?= $server["ram"] ?>MB
                                          </td>
                                          <td class="">
                                             <?= $server["disk"] ?>MB
                                          </td>
                                          <td>
                                             <a href="/server/queue/delete?server=<?= $server["id"] ?>"
                                                class="btn btn-danger btn-sm">Delete</a>
                                          </td>
                                       </tr>
                                       <?php
                                    }

                                    foreach ($uservers as $server) {
                                       $egg = mysqli_query($conn, "SELECT * FROM mythicaldash_eggs WHERE `mythicaldash_eggs`.`egg`='" . $server["egg"] . "'")->fetch_array();
                                       $serverinfo = mysqli_query($conn, "SELECT * FROM mythicaldash_servers WHERE `mythicaldash_servers`.`pid`='" . $server["id"] . "'")->fetch_array();
                                       $location = mysqli_query($conn, "SELECT * FROM mythicaldash_locations WHERE `mythicaldash_locations`.`id`='" . $serverinfo["location"] . "'")->fetch_array();
                                       $uuid = substr($server['uuid'], 0, strpos($server['uuid'], "-"));
                                       ?>
                                       <tr>
                                          <th scope="row">
                                             <div class="media align-items-center">
                                                <div class="media-body">
                                                   <span class="name mb-0 text-sm">
                                                      <?= $server["name"] ?>
                                                   </span>
                                                </div>
                                             </div>
                                          </th>
                                          <td>
                                             <?= $location["name"] ?>
                                          </td>
                                          <td>
                                             <?php
                                             if ($server["container"]["installed"] != 1) {
                                                echo '<span class="badge badge-dot mr-4"><span class="status">Installing</span></span>';
                                             } elseif ($server["suspended"] == true) {
                                                echo '<span class="badge badge-dot mr-4"><span class="status">Suspended</span></span>';
                                             } else {
                                                echo '<span class="badge badge-dot mr-4"><span class="status">Installed</span></span>';
                                             }
                                             ?>
                                          </td>
                                          <td>
                                             <?= $egg["name"] ?>
                                          </td>
                                          <td>
                                             <?= $server["limits"]["cpu"] ?>%
                                          </td>
                                          <td>
                                             <?= $server["limits"]["memory"] ?>MB
                                          </td>
                                          <td>
                                             <?= $server["limits"]["disk"] ?>MB
                                          </td>
                                          <td>
                                             <a href="<?= SettingsManager::getSetting("PterodactylURL") . "/server/" . $server["identifier"] ?>"
                                                class="btn btn-primary btn-sm" data-trigger="hover" data-container="body"
                                                data-toggle="popover" data-color="default" data-placement="left"
                                                data-content="Open in the game panel"><i
                                                   class="fas fa-external-link-square-alt"></i></a>
                                             <a href="/server/edit?id=<?= $server["id"] ?>"
                                                class="btn btn-primary btn-sm">Edit</a>
                                             <a href="/server/delete?server=<?= $server["id"] ?>"><button type="button"
                                                   class="btn btn-danger btn-sm">Delete</button></a>
                                          </td>
                                       </tr>
                                       <?php
                                    }
                                    ?>
                                 </tbody>
                              </table>
                           </div>
                        </div>
                     </div>
                  </div>
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
               <?php include('components/footer.php') ?>
               <div class="content-backdrop fade"></div>
            </div>
         </div>
      </div>
      <div class="layout-overlay layout-menu-toggle"></div>
      <div class="drag-target"></div>
   </div>
   <?php include('requirements/footer.php') ?>
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