<?php
require("core/require/page.php");

$servers = mysqli_query($cpconn, "SELECT * FROM servers WHERE uid = '" . mysqli_real_escape_string($cpconn, $_SESSION["uid"]) . "'");
$servers_in_queue = mysqli_query($cpconn, "SELECT * FROM servers_queue WHERE ownerid = '" . mysqli_real_escape_string($cpconn, $_SESSION["uid"]) . "'");
$serversnumber = $servers->num_rows + $servers_in_queue->num_rows;
function percentage($number,$total,$outof) {
    $result = ($number/$total) * $outof;
    return round($result);
}
// GET USED DISK, RAM IN TOTAL
$usedRam = 0;
$usedDisk = 0;
$usedCpu = 0;
$usedPorts = 0;
$usedDatabase = 0;
$usedBackup = 0;
$uservers = array();
foreach($servers as $serv) {
    $ptid = $serv["pid"];
    $ch = curl_init($getsettingsdb["ptero_url"] . "/api/application/servers/" . $ptid);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "Authorization: Bearer " . $getsettingsdb["ptero_apikey"],
        "Content-Type: application/json",
        "Accept: Application/vnd.pterodactyl.v1+json"
    ));
    $result1 = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($httpcode != 200) {
        echo '<div style="background-color: red; width: 100%; color: white; text-align:center;"> Unable to connect to the game panel! Please contact one of the server administrators.<br/><br/><small>Details: [' . $httpcode . '] ' . $result1 . '</small></div>';
        die();
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
foreach($servers_in_queue as $server) {
    $usedRam = $usedRam + $server['ram'];
    $usedDisk = $usedDisk + $server['disk'];
    $usedPorts = $usedPorts + $server['xtra_ports'];
    $usedBackup = $usedBackup + $server['backuplimit'];
    $usedDatabase = $usedDatabase + $server['databases'];
    $usedCpu = $usedCpu + $server["cpu"];
}

$colour = "dash";
?>
<style>
.bg-gradient-dash
{
    background: #172b4d;
}
</style>
    <div class="header pb-6 d-flex align-items-center" style="min-height: 500px; background-image: url(<?= $getsettingsdb["home_background"] ?>); background-size: cover; background-position: center top;">
      <!-- Mask -->
      <span class="mask bg-gradient-default opacity-8"></span>
      <!-- Header container -->
      <div class="container">
        <div class="row">
          <div class="col">
            <h1 class="display-2 text-white">Hello <?= $userdb["username"] ?></h1>
            <p class="text-white mt-0 mb-5">Welcome to <?= $getsettingsdb["name"] ?>! Get your server below!</p>
            <a href="server/create" class="btn btn-neutral">Create a new server</a>
          </div>
          <?php
          if ($getsettingsdb["homeNews_show"]) {
            ?>
            <div class="col">
              <div class="card bg-gradient-default">
                  <div class="card-body" style="background-image: url('<?= $getsettingsdb["homeNews_bgimage"] ?>'); background-repeat: no-repeat; background-size: cover; background-position: center center; background-color: <?= $getsettingsdb["homeNews_bgcolor"] ?>;">
                      <h3 class="card-title text-white"><?= $getsettingsdb["homeNews_title"] ?></h3>
                      <blockquote class="blockquote text-white mb-0">
                          <p><?= $getsettingsdb["homeNews_content"] ?></p>
                          <a href="<?= $getsettingsdb["homeNews_buttonLink"]; ?>" class="btn btn-neutral btn-sm"><?= $getsettingsdb["homeNews_buttonText"] ?></a>
                      </blockquote>
                  </div>
              </div>
            </div>
            <?php
          }
          ?>
        </div>
      </div>
    </div>
    <!-- Page content -->
    <div class="container-fluid mt--6">
          <div class="row">
            <div class="col-lg-3">
              <div class="card bg-gradient-<?= $colour ?> border-0">
                <!-- Card body -->
                <div class="card-body">
                  <div class="row">
                    <div class="col">
                      <h5 class="card-title text-uppercase text-muted mb-0 text-white">Processor limit</h5>
                      <span class="h2 font-weight-bold mb-0 text-white"><?= $usedCpu . "/" . $userdb["cpu"] ?>%</span>
                      <?php
                      $percentage = percentage($usedCpu, $userdb["cpu"], 100);
                      $progresscolor = "white";
                      if ($percentage >= 85) {
                          $progresscolor = "warning";
                      }
                      if ($percentage >= 100) {
                          $progresscolor = "danger";
                      }
                      ?>
                      <div class="progress" style="height: 5px; background-color: rgba(255, 255, 255, .2);">
                          <div class="progress-bar bg-<?= $progresscolor ?>" role="progressbar" aria-valuenow="<?= $percentage ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?= $percentage ?>%;"></div>
                      </div>
                    </div>
                    <div class="col-auto">
                      <div class="icon icon-shape bg-white text-dark rounded-circle shadow">
                          <i class="fa fa-microchip"></i>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-lg-3">
              <div class="card bg-gradient-<?= $colour ?> border-0">
                <!-- Card body -->
                <div class="card-body">
                  <div class="row">
                    <div class="col">
                      <h5 class="card-title text-uppercase text-muted mb-0 text-white">RAM usage</h5>
                      <span class="h2 font-weight-bold mb-0 text-white"><?= $usedRam . "MB/" . $userdb["memory"] ?>MB</span>
                      <?php
                      $percentage = percentage($usedRam, $userdb["memory"], 100);
                      $progresscolor = "white";
                      if ($percentage >= 85) {
                          $progresscolor = "warning";
                      }
                      if ($percentage >= 100) {
                          $progresscolor = "danger";
                      }
                      ?>
                      <div class="progress" style="height: 5px; background-color: rgba(255, 255, 255, .2);">
                          <div class="progress-bar bg-<?= $progresscolor ?>" role="progressbar" aria-valuenow="<?= $percentage ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?= $percentage ?>%;"></div>
                      </div>
                    </div>
                    <div class="col-auto">
                      <div class="icon icon-shape bg-white text-dark rounded-circle shadow">
                          <i class="fa fa-memory"></i>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-lg-3">
                <div class="card bg-gradient-<?= $colour ?> border-0">
                    <!-- Card body -->
                    <div class="card-body">
                        <div class="row">
                            <div class="col">
                                <h5 class="card-title text-uppercase text-muted mb-0 text-white">Disk usage</h5>
                                <span class="h2 font-weight-bold mb-0 text-white"><?= $usedDisk . "MB/" . $userdb["disk_space"] ?>MB</span>
                                <?php
                                $percentage = percentage($usedDisk, $userdb["disk_space"], 100);
                                $progresscolor = "white";
                                if ($percentage >= 85) {
                                    $progresscolor = "warning";
                                }
                                if ($percentage >= 100) {
                                    $progresscolor = "danger";
                                }
                                ?>
                                <div class="progress" style="height: 5px; background-color: rgba(255, 255, 255, .2);">
                                    <div class="progress-bar bg-<?= $progresscolor ?>" role="progressbar" aria-valuenow="<?= $percentage ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?= $percentage ?>%;"></div>
                                </div>
                            </div>
                            <div class="col-auto">
                                <div class="icon icon-shape bg-white text-dark rounded-circle shadow">
                                    <i class="fa fa-hdd"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="card bg-gradient-<?= $colour ?> border-0">
                    <!-- Card body -->
                    <div class="card-body">
                        <div class="row">
                            <div class="col">
                                <h5 class="card-title text-uppercase text-muted mb-0 text-white">Server slots</h5>
                                <span class="h2 font-weight-bold mb-0 text-white"><?= $serversnumber . "/" . $userdb["server_limit"] ?></span>
                                <?php
                                $percentage = percentage($serversnumber, $userdb["server_limit"], 100);
                                $progresscolor = "white";
                                if ($percentage >= 85) {
                                    $progresscolor = "warning";
                                }
                                if ($percentage >= 100) {
                                    $progresscolor = "danger";
                                }
                                ?>
                                <div class="progress" style="height: 5px; background-color: rgba(255, 255, 255, .2);">
                                    <div class="progress-bar bg-<?= $progresscolor ?>" role="progressbar" aria-valuenow="<?= $percentage ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?= $percentage ?>%;"></div>
                                </div>
                            </div>
                            <div class="col-auto">
                                <div class="icon icon-shape bg-white text-dark rounded-circle shadow">
                                    <i class="fa fa-server"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
          </div>
        <!---
        CARD 2
        -->
        <div class="row">
            <div class="col-lg-3">
                <div class="card bg-gradient-<?= $colour ?> border-0">
                    <!-- Card body -->
                    <div class="card-body">
                        <div class="row">
                            <div class="col">
                                <h5 class="card-title text-uppercase text-muted mb-0 text-white">ADDITIONAL BACKUPS</h5>
                                <span class="h2 font-weight-bold mb-0 text-white"><?= $usedBackup . "/" . $userdb["backup_limit"] ?></span>
                                <?php
                                $percentage = percentage($usedBackup, $userdb["backup_limit"], 100);
                                $progresscolor = "white";
                                if ($percentage >= 85) {
                                    $progresscolor = "warning";
                                }
                                if ($percentage >= 100) {
                                    $progresscolor = "danger";
                                }
                                ?>
                                <div class="progress" style="height: 5px; background-color: rgba(255, 255, 255, .2);">
                                    <div class="progress-bar bg-<?= $progresscolor ?>" role="progressbar" aria-valuenow="<?= $percentage ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?= $percentage ?>%;"></div>
                                </div>
                            </div>
                            <div class="col-auto">
                                <div class="icon icon-shape bg-white text-dark rounded-circle shadow">
                                    <i class="fa fa-retweet"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="card bg-gradient-<?= $colour ?> border-0">
                    <!-- Card body -->
                    <div class="card-body">
                        <div class="row">
                            <div class="col">
                                <h5 class="card-title text-uppercase text-muted mb-0 text-white">Additional ports</h5>
                                <span class="h2 font-weight-bold mb-0 text-white"><?= $usedPorts . "/" . $userdb["ports"] ?></span>
                                <?php
                                $percentage = percentage($usedPorts, $userdb["ports"], 100);
                                $progresscolor = "white";
                                if ($percentage >= 85) {
                                    $progresscolor = "warning";
                                }
                                if ($percentage >= 100) {
                                    $progresscolor = "danger";
                                }
                                ?>
                                <div class="progress" style="height: 5px; background-color: rgba(255, 255, 255, .2);">
                                    <div class="progress-bar bg-<?= $progresscolor ?>" role="progressbar" aria-valuenow="<?= $percentage ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?= $percentage ?>%;"></div>
                                </div>
                            </div>
                            <div class="col-auto">
                                <div class="icon icon-shape bg-white text-dark rounded-circle shadow">
                                    <i class="fas fa-network-wired"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="card bg-gradient-<?= $colour ?> border-0">
                    <!-- Card body -->
                    <div class="card-body">
                        <div class="row">
                            <div class="col">
                                <h5 class="card-title text-uppercase text-muted mb-0 text-white">Databases limit</h5>
                                <span class="h2 font-weight-bold mb-0 text-white"><?= $usedDatabase . "/" . $userdb["databases"] ?></span>
                                <?php
                                $percentage = percentage($usedDatabase, $userdb["databases"], 100);
                                $progresscolor = "white";
                                if ($percentage >= 85) {
                                    $progresscolor = "warning";
                                }
                                if ($percentage >= 100) {
                                    $progresscolor = "danger";
                                }
                                ?>
                                <div class="progress" style="height: 5px; background-color: rgba(255, 255, 255, .2);">
                                    <div class="progress-bar bg-<?= $progresscolor ?>" role="progressbar" aria-valuenow="<?= $percentage ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?= $percentage ?>%;"></div>
                                </div>
                            </div>
                            <div class="col-auto">
                                <div class="icon icon-shape bg-white text-dark rounded-circle shadow">
                                    <i class="fa fa-database"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="card bg-gradient-<?= $colour ?> border-0">
                    <!-- Card body -->
                    <div class="card-body">
                        <div class="row">
                            <div class="col">
                                <h5 class="card-title text-uppercase text-muted mb-0 text-white">Current plan</h5>
                                <span class="h2 font-weight-bold mb-0 text-white">Free</span>
                                <br/><br/>
                            </div>
                            <div class="col-auto">
                                <div class="icon icon-shape bg-white text-dark rounded-circle shadow">
                                    <i class="fa fa-dollar-sign"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
        if (isset($_SESSION["error"])) {
            ?>
            <div class="alert alert-danger" role="alert">
                <strong>Error!</strong> <?= $_SESSION["error"] ?>
            </div>
            <?php
            unset($_SESSION["error"]);
        }
        ?>
        <?php
        if (isset($_SESSION["success"])) {
            ?>
            <div class="alert alert-success" role="alert">
                <strong>Success!</strong> <?= $_SESSION["success"] ?>
            </div>
            <?php
            unset($_SESSION["success"]);
        }
        ?>
        <div class="row">
            <div class="col">
                <div class="card bg-default shadow">
                    <div class="card-header bg-transparent border-0">
                        <h3 class="text-white mb-0">Your servers</h3>
                    </div>
                    <div class="table-responsive">
                        <table class="table align-items-center table-dark table-flush">
                            <tbody class="list">
                            <?php
                            if (count($uservers) == 0 && $servers_in_queue->num_rows == 0) {
                                // No servers
                                ?>
                                <div style="text-align: center;">
                                    <img src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/img/empty.svg" height="150"/><br/>
                                    <h2 style="color: white;">No servers yet. Why not creating one?</h2>
                                    <a href="server/create" class="btn btn-neutral">Create a new server</a><br/><br/>
                                </div>
                                <?php
                            } else {
                                ?>
                                <thead class="thead-dark">
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
                            foreach($servers_in_queue as $server) {
                                $currentqueue = mysqli_query($cpconn, "SELECT * FROM servers_queue")->num_rows;
                                $egg = $cpconn->query("SELECT * FROM eggs WHERE id = " . $server['egg'])->fetch_array();
                                $location = $cpconn->query("SELECT * FROM locations WHERE id = " . $server['location'])->fetch_array();
                                $currentnodequeue = mysqli_query($cpconn, "SELECT id FROM servers_queue ORDER BY type DESC");
                                $serverpos = 0;
                                foreach($currentnodequeue as $queue) {
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
                                                <span class="name mb-0 text-sm"><?= $server["name"] ?></span>
                                            </div>
                                        </div>
                                    </th>
                                    <td>
                                        <img src="<?= $location["icon"] ?>" width="20"/>
                                        <?= $location["name"] ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-dot mr-4"><i class="bg-danger"></i><span class="status">In queue (Position <?= $serverpos . "/" . $currentnodequeue->num_rows ?>)
                                        <br/>
                                        <?php
                                        if ($server["type"] == 0) {
                                            echo '<img src="https://i.imgur.com/ZJSpUpX.png" width="20"> Normal queue </span></span>';
                                        } elseif ($server["type"] == 1) {
                                            echo '<img src="https://i.imgur.com/Wbxaytz.png" width="20"> VIP queue </span></span>';
                                        } elseif ($server["type"] == 2) {
                                            echo '<img src="https://i.imgur.com/lLQy3gP.png" width="20"> Staff queue </span></span>';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <img src="<?= $egg["icon"] ?>" height="20" />
                                        <?= $egg["name"] ?>
                                    </td>
                                    <td>
                                        <?= $server["cpu"] ?>%
                                    </td>
                                    <td>
                                        <?= $server["ram"] ?>MB
                                    </td>
                                    <td>
                                        <?= $server["disk"] ?>MB
                                    </td>
                                    <td>
                                        <?php
                                        if ($server["type"] == 0) {
                                            echo '<a href="server/buyVip?server=' . $server["id"] . '" class="btn btn-warning btn-sm"><i class="fas fa-long-arrow-alt-up"></i> Upgrade to VIP queue</a>';
                                        } else {
                                            echo '<button type="button" class="btn btn-warning btn-sm" disabled="1" style="cursor: not-allowed;"><i class="fas fa-long-arrow-alt-up"></i> <span style="text-decoration: line-through;">Upgrade to VIP queue</span> </button>';
                                        }
                                        ?>
                                        <a href="server/queueDelete?server=<?= $server["id"] ?>" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i> Delete</a>
                                    </td>
                                </tr>
                            <?php
                            }

                            foreach ($uservers as $server) {
                                $egg = mysqli_query($cpconn, "SELECT * FROM eggs WHERE `eggs`.`egg`='" . $server["egg"] . "'")->fetch_array();
                                $serverinfo = mysqli_query($cpconn, "SELECT * FROM servers WHERE `servers`.`pid`='" . $server["id"] . "'")->fetch_array();
                                $location = mysqli_query($cpconn, "SELECT * FROM locations WHERE `locations`.`id`='" . $serverinfo["location"] . "'")->fetch_array();
                                $uuid = substr($server['uuid'], 0, strpos($server['uuid'], "-"));
                                ?>
                                <tr>
                                    <th scope="row">
                                        <div class="media align-items-center">
                                            <div class="media-body">
                                                <span class="name mb-0 text-sm"><?= $server["name"] ?></span>
                                            </div>
                                        </div>
                                    </th>
                                    <td>
                                        <img src="<?= $location["icon"] ?>" width="20"/>
                                        <?= $location["name"] ?>
                                    </td>
                                    <td>
                                        <?php
                                        if ($server["container"]["installed"] != 1) {
                                            echo '<span class="badge badge-dot mr-4"><i class="bg-warning"></i><span class="status">Installing</span></span>';
                                        } elseif ($server["suspended"] == true) {
                                            echo '<span class="badge badge-dot mr-4"><i class="bg-warning"></i><span class="status">Suspended</span></span>';
                                        } else {
                                            echo '<span class="badge badge-dot mr-4"><i class="bg-success"></i><span class="status">Installed</span></span>';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <img src="<?= $egg["icon"] ?>" height="20" />
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
                                        <a href="<?= $getsettingsdb["ptero_url"] . "/server/" . $server["identifier"] ?>" class="btn btn-primary btn-sm" data-trigger="hover" data-container="body" data-toggle="popover" data-color="default" data-placement="left" data-content="Open in the game panel"><i class="fas fa-external-link-square-alt"></i></a>
                                        <a href="/server/fetch?id=<?= $server["id"] ?>" class="btn btn-primary btn-sm">Fetch</a>
                                        <a href="/server/rename?id=<?= $server["id"] ?>" class="btn btn-primary btn-sm">Rename</a>
                                        <a href="/server/manage?id=<?= $server["id"] ?>" class="btn btn-primary btn-sm">Edit</a>
                                        <a href="/server/delete?server=<?= $server["id"] ?>"><button type="button" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i> Delete</button></a>
                                        
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
      <!-- Footer -->
      <footer class="footer pt-0">
        <div class="row align-items-center justify-content-lg-between">
          <div class="col-lg-6">
            <div class="copyright text-center  text-lg-left  text-muted">
                Copyright &copy;2020-2023 <a href="https://github.com/MythicalLTD/MythicalDash" class="font-weight-bold ml-1" target="_blank">ShadowDash x MythicalDash </a> - Theme by <a href="https://creativetim.com" target="_blank">Creative Tim</a>
            </div>
          </div>
          <div class="col-lg-6">
            <ul class="nav nav-footer justify-content-center justify-content-lg-end">
              <li class="nav-item">
                <a href="<?= $getsettingsdb["website"] ?>" class="nav-link" target="_blank"> Website</a>
              </li>
              <li class="nav-item">
                <a href="<?= $getsettingsdb["statuspage"] ?>" class="nav-link" target="_blank">Uptime / Status</a>
              </li>
              <li class="nav-item">
                <a href="<?= $getsettingsdb["privacypolicy"] ?>" class="nav-link" target="_blank">Privacy policy</a>
              </li>
              <li class="nav-item">
                <a href="<?= $getsettingsdb["termsofservice"] ?>" class="nav-link" target="_blank">Terms of service</a>
              </li>
            </ul>
          </div>
        </div>
      </footer>
    </div>
  </div>

  <script>
      $("#gamepanelopen").popover({ trigger: "hover" });
  </script>
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/js/jquery.min.js"></script>
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/js/bootstrap.bundle.min.js"></script>
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/js/js.cookie.js"></script>
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/js/jquery.scrollbar.min.js"></script>
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/js/jquery-scrollLock.min.js"></script>
  <!-- Argon JS -->
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/js/argon.js?v=1.2.0"></script>
  <!-- Demo JS - remove this in your project -->
  
</body>

</html>

