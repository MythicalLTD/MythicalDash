<?php 
require("../core/require/page.php");


$usrdb = $cpconn->query("SELECT * FROM users WHERE user_id = '" . mysqli_real_escape_string($cpconn, $_SESSION["uid"]) . "'")->fetch_array();
$perms = $cpconn->query("SELECT * FROM roles WHERE name='".$usrdb['role']."'")->fetch_array();



if ($perms['canseeadminhomepage'] == "true" || $perms['fullperm'] == "true")
{

}
else
{
  echo '<script>window.location.replace("/");</script>';
  $_SESSION['error'] = "You are not allowed to see this page";
  die;
}

$sqlurs = "SELECT COUNT(*) FROM users";
$resultusrs = mysqli_query($cpconn, $sqlurs);
$countusrs = mysqli_fetch_array($resultusrs)[0];

$sqlsvs = "SELECT COUNT(*) FROM servers";
$resultsvs = mysqli_query($cpconn, $sqlsvs);
$countsvs = mysqli_fetch_array($resultsvs)[0];

$sqlsvsq = "SELECT COUNT(*) FROM servers_queue";
$resultsvsq = mysqli_query($cpconn, $sqlsvsq);
$countsvsq = mysqli_fetch_array($resultsvsq)[0];

$sqlloc = "SELECT COUNT(*) FROM tickets";
$resultloc = mysqli_query($cpconn, $sqlloc);
$counttickets = mysqli_fetch_array($resultloc)[0];

?>
<head><title><?= $getsettingsdb['name']?> | Admin</title><?php include('../core/imports/header.php');?></head>
<body data-background-color="dark">
  <div class="container-fluid text-center bg-dark text-light py-4">
    <h1>Welcome to the statistics page</h1>
    <p>Here you can see the statistics from user/servers/tickets.</p>
    
  </div>
<div class="container mt-5">
  <div class="row">
    <div class="col-md-3">
      <div class="card text-center">
        <div class="card-body">
          <h5 class="card-title">Users</h5>
          <!-- Display the total number of users -->
          <h1 class="card-text text-white"><?= $countusrs ?></h1>
          
          <?php 
          if ($perms['canseeusers'] == "true" || $perms['fullperm'] == "true" || $perms['caneditusers'] == "true" || $perms['candeleteusers'] == "true")
          {
            ?>
            <a href="users.php" class="btn btn-primary">View all</a>
            <?php
          }
          else
          {
            
          }          
          ?>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card text-center">
        <div class="card-body">
          <h5 class="card-title">Servers</h5>
          <h1 class="card-text text-white"><?= $countsvs ?></h1>
          <?php 
          if ($perms['canseeservers'] == "true" || $perms['fullperm'] == "true" || $perms['caneditservers'] == "true" || $perms['candeleteservers'] == "true")
          {
            ?>
              <a href="servers.php" class="btn btn-primary">View all</a>
            <?php
          }
          ?>
          
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card text-center">
        <div class="card-body">
          <h5 class="card-title text-white">Servers In queue</h5>
          <h1 class="card-text text-white"><?= $countsvsq ?></h1>
          <?php 
          if ($perms['canseeservers'] == "true" || $perms['fullperm'] == "true" || $perms['caneditservers'] == "true" || $perms['candeleteservers'] == "true")
          {
            ?>
              <a href="servers_queue.php" class="btn btn-primary">View all</a>
            <?php
          }
          ?>
          
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card text-center">
        <div class="card-body">
          <h5 class="card-title text-white">Tickets</h5>
          <h1 class="card-text text-white"><?= $counttickets ?></h1>
          <?php 
          if ($perms['issupport'] == "true" || $perms['fullperm'] == "true" || $perms['canseeusers'] == "true" || $perms['caneditusers'] == "true" || $perms['candeleteusers'] == "true")
          {
            ?>
              <a href="tickets.php" class="btn btn-primary">View all</a>
            <?php
          }
          ?>
        </div>
      </div>
    </div>
        </div></div>
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/argon/js/jquery.min.js"></script>
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/argon/js/bootstrap.bundle.min.js"></script>
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/argon/js/js.cookie.js"></script>
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/argon/js/jquery.scrollbar.min.js"></script>
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/argon/js/jquery-scrollLock.min.js"></script>
  <!-- Argon JS -->
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/argon/js/argon.js?v=1.2.0"></script>
  <!-- Demo JS - remove this in your project -->
  
</body>

</html>