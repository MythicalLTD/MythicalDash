<?php 
require("../core/require/page.php");
$usrdb = $cpconn->query("SELECT * FROM users WHERE user_id = '" . mysqli_real_escape_string($cpconn, $_SESSION["uid"]) . "'")->fetch_array();
$perms = $cpconn->query("SELECT * FROM roles WHERE name='".$usrdb['role']."'")->fetch_array();


if ($perms['canseeservers'] == "true" || $perms['caneditservers'] == "true" || $perms['candeleteservers'] == "true" || $perms['fullperm'] == "true")
{

}
else
{
  echo '<script>window.location.replace("/");</script>';
  $_SESSION['error'] = "You are not allowed to see this page";
  die;
}

if (isset($_GET['id'])) {
  $svdatalol = $cpconn->query("SELECT * FROM servers_queue where id = '".$_GET['id']. "'")->fetch_array();
  $usrdb = $cpconn->query("SELECT * FROM users where user_id = '".$svdatalol['uid']."'")->fetch_array();
  $dbalter = 'Server Name: '.$svdatalol['name'].'\nServer Owner: '.$usrdb['username']. '\nPanel ID: '.$svdatalol['pid'].'\nLocation: '.$svdatalol['location'].'\nCreated on: '.$svdatalol['created'].'\n';
  echo '<script>';
  echo '  alert("' . $dbalter . '");';
  echo '</script>';
}


if ($_GET['query'] == "")
{
  $txtsrch = "";
}
else
{
  $txtsrch = $_GET['query'];
}

echo '
<body class="bg-dark text-light">
<div class="container-fluid text-center bg-dark text-light py-4">
    <h1>Welcome to the servers page</h1>
    <p>Here you can see and manage users that use your host.</p>
  </div>
<div class="container text-center bg-dark text-light">
  <form action="servers_queue.php" method="get">
    <div class="form-group">
      <input type="text" class="form-control" name="query" value="'.$txtsrch.'" placeholder="Search">
    </div>
  </form>
</div>
';

$serversdb = mysqli_query($cpconn, "SELECT * FROM servers_queue")->fetch_object();
$result = mysqli_query($cpconn, "SELECT * FROM servers_queue");

if (isset($_GET['query'])) {
    $search_term = '%' . mysqli_real_escape_string($cpconn, $_GET['query']) . '%';
    $sresult = mysqli_query($cpconn, "SELECT * FROM servers_queue WHERE name LIKE '$search_term'");
  
    if ($sresult) {
      echo "<table id='users' class='table table-sm table-borderless'>";
      echo "<thead class='thead-dark'><tr><th>ID</th><th>Server name</th><th>Owner</th><th>Location</th><th>Created On</th><th>Actions</th></tr></thead>";
      echo "<tbody>";
      while ($row1 = mysqli_fetch_assoc($sresult)) {
        echo "<tr>";
        echo "<td class='text-white'>" . $row1["id"] . "</td>";
        echo "<td class='text-white'>" . $row1["name"] . "</td>";
        echo "<td class='text-white'>" . $row1["ownerid"] . "</td>";
        echo "<td class='text-white'>" . $row1["location"] . "</td>";
        echo "<td class='text-white'>" . $row1["created"] . "</td>";
        echo "<td class='text-white'>";
        if ($perms['candeleteusers'] == "true" || $perms['fullperm'] == "true")
        {
          echo "<a href='server_queue/delete?id=" . $row1["id"] . "' class='btn btn-sm btn-danger'>Delete</a>";
        }
        echo "</td>";
        echo "</tr>";
    }
    echo "</tbody></table>";
    } else {
        echo "Error: " . mysqli_error($cpconn);
    }
  
  }
  else
  {
    if ($result) {
      echo "<table id='users' class='table table-sm table-borderless'>";
      echo "<thead class='thead-dark'><tr><th>ID</th><th>Server name</th><th>Owner</th><th>Location</th><th>Created On</th><th>Actions</th></tr></thead>";
      echo "<tbody>";
      while ($row = mysqli_fetch_assoc($result)) {
          echo "<tr>";
          echo "<td class='text-white'>" . $row["id"] . "</td>";
          echo "<td class='text-white'>" . $row["name"] . "</td>";
          echo "<td class='text-white'>" . $row["ownerid"] . "</td>";
          echo "<td class='text-white'>" . $row["location"] . "</td>";
          echo "<td class='text-white'>" . $row["created"] . "</td>";
          echo "<td class='text-white'>";
          if ($perms['candeleteusers'] == "true" || $perms['fullperm'] == "true")
          {
            echo "<a href='server_queue/delete?id=" . $row["id"] . "' class='btn btn-sm btn-danger'>Delete</a>";
          }
          echo "</td>";
          echo "</tr>";
      }
      echo "</tbody></table>";
    } else {
        echo "Error: " . mysqli_error($cpconn);
    }
  }
  
  
?>



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