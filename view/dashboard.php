<?php
use MythicalDash\ErrorHandler;
use MythicalDash\SettingsManager;
use MythicalDash\Pterodactyl\Server;

try {
   include('requirements/page.php');
   $nuserdb = $conn->query("SELECT * FROM mythicaldash_users WHERE api_key = '" . mysqli_real_escape_string($conn, $_COOKIE['token']) . "'")->fetch_array();
   $servers = mysqli_query($conn, "SELECT * FROM mythicaldash_servers WHERE uid = '" . mysqli_real_escape_string($conn, $_COOKIE['token']) . "'");
   $servers_in_queue = mysqli_query($conn, "SELECT * FROM mythicaldash_servers_queue WHERE ownerid = '" . mysqli_real_escape_string($conn, $_COOKIE['token']) . "'");
   $serversnumber = $servers->num_rows + $servers_in_queue->num_rows;
   $usedRam = 0;
   $usedDisk = 0;
   $usedCpu = 0;
   $usedPorts = 0;
   $usedDatabase = 0;
   $usedBackup = 0;
   $uservers = array();
   foreach ($servers as $serv) {
      $ptid = $serv["pid"];
      if (Server::checkServerExists($ptid) == true) {
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
            ErrorHandler::ShowCritical($lang['pterodactyl_connection_error']);
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
      } else {
         //i hope this works else server is bye bye and only in panel it will show :)
         $conn->query("DELETE FROM mythicaldash_servers WHERE `mythicaldash_servers`.`pid` = '" . mysqli_real_escape_string($conn, $ptid) . "'");
         $conn->close();
         header('location: /dashboard');
      }

   }
   foreach ($servers_in_queue as $server) {
      $usedRam = $usedRam + $server['ram'];
      $usedDisk = $usedDisk + $server['disk'];
      $usedPorts = $usedPorts + $server['xtra_ports'];
      $usedBackup = $usedBackup + $server['backuplimit'];
      $usedDatabase = $usedDatabase + $server['databases'];
      $usedCpu = $usedCpu + $server["cpu"];
   }

} catch (Exception $e) {
   echo '<script>alert("There was an error while loading this page please contact your admin!")</script>';
}
?>
<!DOCTYPE html>
<html lang="en" class="dark-style layout-navbar-fixed layout-menu-fixed" dir="ltr" data-theme="theme-semi-dark"
   data-assets-path="<?= $appURL ?>/assets/" data-template="vertical-menu-template">

<head>
   <?php include('requirements/head.php'); ?>
   <title>
      <?= SettingsManager::getSetting("name") ?> -
      <?= $lang['dashboard'] ?>
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
                           <div class="text-center justify-content-between mb-3">
                              <h5 class="card-title mb-0">
                                 <?= $lang['statistics'] ?>
                              </h5>
                              <small id="updateText" class="text-muted">Updated 0 seconds ago</small>
                           </div>
                        </div>
                        <div class="card-body">
                           <div class="row gy-3 mb-2">
                              <div class="col-md-3 col-6 text-center">
                                 <span>
                                    <?= $lang['ram'] ?>
                                 </span>
                                 <div class="d-flex align-items-center">
                                    <div class="badge rounded-pill bg-label-primary me-3 p-2">
                                       <i class="fas fa-memory fa-2x"></i>
                                    </div>
                                    <div class="resources"
                                       style="position: relative; overflow: hidden; border-radius: 10px;">

                                       <h5 class="mb-0">
                                          <?= $usedRam . "MB / " . $nuserdb["ram"] ?>MB
                                       </h5>
                                       <div
                                          style="position: absolute; content: ''; background-image: linear-gradient(180deg, rgb(100, 195, 252), rgb(59, 163, 237)); inset: 5px; border-radius: 15px; animation: rotBGimg 3.5s linear infinite;">
                                       </div>
                                    </div>
                                 </div>
                              </div>

                              <div class="col-md-3 col-6 text-center">
                                 <span>
                                    <?= $lang['disk'] ?>
                                 </span>
                                 <div class="d-flex align-items-center">
                                    <div class="badge rounded-pill bg-label-primary me-3 p-2">
                                       <i class="fa fa-save fa-2x"></i>
                                    </div>
                                    <div class="resources text-center">
                                       <h5 class="mb-0">
                                          <?= $usedDisk . "MB / " . $nuserdb["disk"] ?>MB
                                       </h5>
                                       <div
                                          style="position: absolute; content: ''; background-image: linear-gradient(180deg, rgb(100, 195, 252), rgb(59, 163, 237)); inset: 5px; border-radius: 15px; animation: rotBGimg 3.5s linear infinite;">
                                       </div>

                                    </div>
                                 </div>
                              </div>
                              <div class="col-md-3 col-6 text-center">
                                 <span>
                                    <?= $lang['cpu'] ?>
                                 </span>
                                 <div class="d-flex align-items-center">
                                    <div class="badge rounded-pill bg-label-primary me-3 p-2">
                                       <i class="fas fa-microchip fa-2x"></i>
                                    </div>
                                    <div class="resources">
                                       <h5 class="mb-0">
                                          <?= $usedCpu . "% / " . $nuserdb["cpu"] ?>%
                                       </h5>
                                       <div
                                          style="position: absolute; content: ''; background-image: linear-gradient(180deg, rgb(100, 195, 252), rgb(59, 163, 237)); inset: 5px; border-radius: 15px; animation: rotBGimg 3.5s linear infinite;">
                                       </div>

                                    </div>
                                 </div>
                              </div>
                              <div class="col-md-3 col-6 text-center">
                                 <span>
                                    <?= $lang['server_slot'] ?>
                                 </span>
                                 <div class="d-flex align-items-center">
                                    <div class="badge rounded-pill bg-label-primary me-3 p-2">
                                       <i class="fas fa-server fa-2x"></i>
                                    </div>
                                    <div class="resources">
                                       <h5 class="mb-0">
                                          <?= $serversnumber . " / " . $nuserdb["server_limit"] ?>
                                       </h5>
                                       <div
                                          style="position: absolute; content: ''; background-image: linear-gradient(180deg, rgb(100, 195, 252), rgb(59, 163, 237)); inset: 5px; border-radius: 15px; animation: rotBGimg 3.5s linear infinite;">
                                       </div>

                                    </div>
                                 </div>
                              </div>
                           </div>

                           <div class="row gy-3">
                              <div class="col-md-3 col-6 text-center">
                                 <span>
                                    <?= $lang['backup_slot'] ?>
                                 </span>
                                 <div class="d-flex align-items-center">
                                    <div class="badge rounded-pill bg-label-primary me-3 p-2">
                                       <i class="fa fa-network-wired fa-2x"></i>
                                    </div>
                                    <div class="resources">
                                       <h5 class="mb-0">
                                          <?= $usedBackup . " / " . $nuserdb["backups"] ?>
                                       </h5>
                                       <div
                                          style="position: absolute; content: ''; background-image: linear-gradient(180deg, rgb(100, 195, 252), rgb(59, 163, 237)); inset: 5px; border-radius: 15px; animation: rotBGimg 3.5s linear infinite;">
                                       </div>

                                    </div>
                                 </div>
                              </div>
                              <div class="col-md-3 col-6 text-center">
                                 <span>
                                    <?= $lang['server_allocation'] ?>
                                 </span>
                                 <div class="d-flex align-items-center">
                                    <div class="badge rounded-pill bg-label-primary me-3 p-2">
                                       <i class="fas fa-microchip fa-2x"></i>
                                    </div>
                                    <div class="resources">
                                       <h5 class="mb-0">
                                          <?= $usedPorts . " / " . $nuserdb["ports"] ?>
                                       </h5>
                                       <div
                                          style="position: absolute; content: ''; background-image: linear-gradient(180deg, rgb(100, 195, 252), rgb(59, 163, 237)); inset: 5px; border-radius: 15px; animation: rotBGimg 3.5s linear infinite;">
                                       </div>

                                    </div>
                                 </div>
                              </div>
                              <div class="col-md-3 col-6 text-center">
                                 <span>
                                    <?= $lang['mysql'] ?>
                                 </span>
                                 <div class="d-flex align-items-center">
                                    <div class="badge rounded-pill bg-label-primary me-3 p-2">
                                       <i class="fas fa-database fa-2x"></i>
                                    </div>
                                    <div class="resources">
                                       <h5 class="mb-0">
                                          <?= $usedDatabase . " / " . $nuserdb["databases"] ?>
                                       </h5>
                                       <div
                                          style="position: absolute; content: ''; background-image: linear-gradient(180deg, rgb(100, 195, 252), rgb(59, 163, 237)); inset: 5px; border-radius: 15px; animation: rotBGimg 3.5s linear infinite;">
                                       </div>

                                    </div>
                                 </div>
                              </div>
                              <div class="col-md-3 col-6 text-center">
                                 <span>
                                    <?= $lang['coins'] ?>
                                 </span>
                                 <div class="d-flex align-items-center">
                                    <div class="badge rounded-pill bg-label-primary me-3 p-2">
                                       <i class="fas fa-coins fa-2x"></i>
                                    </div>
                                    <div class="resources">
                                       <h5 class="mb-0">
                                          <?= $nuserdb["coins"] ?>
                                       </h5>
                                       <div
                                          style="position: absolute; content: ''; background-image: linear-gradient(180deg, rgb(100, 195, 252), rgb(59, 163, 237)); inset: 5px; border-radius: 15px; animation: rotBGimg 3.5s linear infinite;">
                                       </div>

                                    </div>
                                 </div>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
                  <br>
                  <div class="row">
                     <div class="col">
                        <div class="card bg-default shadow">
                           <div class="card-header bg-transparent border-0 text-center">
                              <h5 class="card-title mb-0">
                                 <?= $lang['your_servers'] ?>
                              </h5>
                           </div>
                           <div class="table-responsive">
                              <table class="table align-items-center table-flush">
                                 <tbody class="list">
                                    <?php
                                    if (count($uservers) == 0 && $servers_in_queue->num_rows == 0) {
                                       ?>
                                       <div style="text-align: center;">
                                          <img src="/assets/img/empty.svg" height="150" />
                                          <br>
                                          <h4 style="">
                                             <?= $lang['no_servers_1'] ?>
                                          </h4>
                                          <a href="/server/create" class="btn btn-primary">
                                             <?= $lang['no_servers_1'] ?>
                                          </a><br /><br />
                                       </div>
                                       <?php
                                    } else {
                                       ?>
                                       <thead class="">
                                          <tr>
                                             <th scope="col">
                                                <?= $lang['server_name'] ?>
                                             </th>
                                             <th scope="col">
                                                <?= $lang['server_node'] ?>
                                             </th>
                                             <th scope="col">
                                                <?= $lang['server_status'] ?>
                                             </th>
                                             <th scope="col">
                                                <?= $lang['server_type'] ?>
                                             </th>
                                             <th scope="col">
                                                <?= $lang['cpu'] ?>
                                             </th>
                                             <th scope="col">
                                                <?= $lang['ram'] ?>
                                             </th>
                                             <th scope="col">
                                                <?= $lang['disk'] ?>
                                             </th>
                                             <th scope="col">
                                                <?= $lang['actions'] ?>
                                             </th>
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
                                          <td class="">
                                             <code><?= $server["name"] ?></code>
                                          </td>
                                          <td class="">
                                             <?= $location["name"] ?>
                                          </td>
                                          <td>
                                             <code> <?= str_replace("%placeholder_1%", $serverpos . "/" . $currentnodequeue->num_rows, $lang['server_waiting_list']) ?></code>
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
                                                class="btn btn-danger btn-sm">
                                                <?= $lang['delete'] ?>
                                             </a>
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
                                          <td class="">
                                             <code><?= $server["name"] ?></code>
                                          </td>
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
                                             <?php
                                             if (SettingsManager::getSetting("server_purge") == "true") {
                                                if ($serverinfo["purge"] == "true") {
                                                   ?>
                                                   <a href="/server/active?id=<?= $serverinfo["id"] ?>"
                                                      class="btn btn-warning btn-sm">
                                                      <?= $lang['active'] ?>
                                                   </a>
                                                   <?php
                                                }
                                             }
                                             ?>
                                             <a href="/server/edit?id=<?= $server["id"] ?>" class="btn btn-primary btn-sm">
                                                <?= $lang['edit'] ?>
                                             </a>
                                             <a class="btn btn-danger btn-sm"
                                                href="/server/delete?server=<?= $server["id"] ?>"
                                                onclick="event.preventDefault(); confirmDeleteAction(<?php echo $server['id'] ?>);">
                                                <?= $lang['delete'] ?>
                                             </a>
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
   <script>
      function confirmDeleteAction(serverId) {
         // Use SweetAlert2 for a custom confirmation dialog
         Swal.fire({
            title: '<?= $lang['alert_are_you_sure'] ?>',
            text: '<?= $lang['alert_this_undo_none'] ?>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: '<?= $lang['alert_yes'] ?>'
         }).then((result) => {
            if (result.isConfirmed) {
               // If the user clicks "Yes, delete it!", redirect to the delete URL
               window.location.href = `/server/delete?server=${serverId}`;
            }
         });
      }
   </script>
</body>

</html>