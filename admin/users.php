<?php 
require("../core/require/page.php");
$usrdb = $cpconn->query("SELECT * FROM users WHERE user_id = '" . mysqli_real_escape_string($cpconn, $_SESSION["uid"]) . "'")->fetch_array();
$perms = $cpconn->query("SELECT * FROM roles WHERE name='".$usrdb['role']."'")->fetch_array();

if ($perms['canseeusers'] == "true" || $perms['fullperm'] == "true" || $perms['caneditusers'] == "true" || $perms['candeleteusers'] == "true")
{

}
else
{
  echo '<script>window.location.replace("/");</script>';
  $_SESSION['error'] = "You are not allowed to see this page";
  die;
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
    <h1>Welcome to the users page</h1>
    <p>Here you can see and manage users that use your host.</p>
  </div>
<div class="container text-center bg-dark text-light">
  <form action="users.php" method="get">
    <div class="form-group">
      <input type="text" class="form-control" name="query" value="'.$txtsrch.'" placeholder="Search">
    </div>
  </form>
</div>
';



$usersdb = mysqli_query($cpconn, "SELECT * FROM users")->fetch_object();
$result = mysqli_query($cpconn, "SELECT * FROM users");


if (isset($_GET['id'])) {
  $db = $cpconn->query("SELECT * FROM users WHERE id=".$_GET['id']."")->fetch_array();
  if ($db['banned'] == 0)
  {
    $isbanned = "false";
  }
  else
  {
    $isbanned = "true";
  }
  if ($db['staff'] = "1")
  {
    $isstaff = "false";
  }
  else
  {
    $isstaff = "true";
  }
  $dbalter = 'ID: '.$_GET['id'].'\nPanel ID: '.$db['panel_id'].'\n User ID: '.$db['user_id'].'\nUsername: '.$db['username'].'\nPanel Username: '.$db['panel_username'].'\nEmail: '.$db['email'].'\nRole: '.$db['role'].'\nMinutes AFK: '.$db['minutes_idle'].'m\nCoins: '.$db['coins'].'\nBalance: '.$db['balance'].'\nRam: '.$db['memory'].'mb\nCPU: '.$db['cpu'].'%\nDisk: '.$db['disk_space'].'mb\nPorts: '.$db['ports'].'\nDatabases: '.$db['databases'].'\nServer Limit: '.$db['server_limit'].'\nLast ip: '.$db['lastlogin_ip'].'\nRegister ip: '.$db['register_ip'].'\nBanned: '.$isbanned.'\nBan reason: '.$db['banned_reason'].'\nStaff: '.$isstaff.'\nRegistered at: '.$db['registered'].'\n';
  echo '<script>';
  echo '  alert("' . $dbalter . '");';
  echo '</script>';
}



if (isset($_GET['query'])) {
  $search_term = '%' . mysqli_real_escape_string($cpconn, $_GET['query']) . '%';
  $sresult = mysqli_query($cpconn, "SELECT * FROM users WHERE username LIKE '$search_term'");

  if ($sresult) {
    echo "<table id='users' class='table table-sm table-borderless'>";
    echo "<thead class='thead-dark'><tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th><th>Banned</th><th>Actions</th></tr></thead>";
    echo "<tbody>";
    while ($row1 = mysqli_fetch_assoc($sresult)) {
      echo "<tr>";
      echo "<td class='text-white'>" . $row1["id"] . "</td>";
      echo "<td class='text-white'>" . $row1["username"] . "</td>";
      echo "<td class='text-white'>" . $row1["email"] . "</td>";
      echo "<td class='text-white'>" . $row1["role"] . "</td>";
      echo "<td class='text-white'>" . $row1["banned"] . "</td>";
      echo "<td class='text-white'>";
      if ($perms['fullperm'] == "true")
      {
        echo "<a href='".$getsettingsdb['ptero_url']."/admin/users/view/" . $row1["panel_id"] . "' class='btn btn-sm btn-primary' target='_blank'><i class='fas fa-external-link-square-alt'></i></a>";
      }
      echo "<a href='?id=" . $row1["id"] . "' class='btn btn-sm btn-primary'>Info</a>";
      if ($perms['caneditusers'] == "true" || $perms['fullperm'] == "true")
      {
        echo "<a href='edit_user?id=" . $row1["id"] . "' class='btn btn-sm btn-warning'>Edit</a>";
      }

      if ($perms['candeleteusers'] == "true" || $perms['fullperm'] == "true")
      {
        echo "<a href='delete_user.php?id=" . $row1["id"] . "' class='btn btn-sm btn-danger'>Delete</a>";
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
    echo "<thead class='thead-dark'><tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th><th>Banned</th><th>Actions</th></tr></thead>";
    echo "<tbody>";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td class='text-white'>" . $row["id"] . "</td>";
        echo "<td class='text-white'>" . $row["username"] . "</td>";
        echo "<td class='text-white'>" . $row["email"] . "</td>";
        echo "<td class='text-white'>" . $row["role"] . "</td>";
        echo "<td class='text-white'>" . $row["banned"] . "</td>";
        echo "<td class='text-white'>";
        if ($perms['fullperm'] == "true")
        {
          echo "<a href='".$getsettingsdb['ptero_url']."/admin/users/view/" . $row["panel_id"] . "' class='btn btn-sm btn-primary' target='_blank'><i class='fas fa-external-link-square-alt'></i></a>";
        }
        echo "<a href='?id=" . $row["id"] . "' class='btn btn-sm btn-primary'>Info</a>";
        if ($perms['caneditusers'] == "true" || $perms['fullperm'] == "true")
        {
          echo "<a href='edit_user?id=" . $row["id"] . "' class='btn btn-sm btn-warning'>Edit</a>";
        }
  
        if ($perms['candeleteusers'] == "true" || $perms['fullperm'] == "true")
        {
          echo "<a href='delete_user.php?id=" . $row["id"] . "' class='btn btn-sm btn-danger'>Delete</a>";
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

<script>
  const table = document.getElementById('users');
  const rows = table.getElementsByTagName('tr');

  for (const row of rows) {
    const cells = row.getElementsByTagName('td');
    for (const cell of cells) {
      if (cell.innerHTML === '0') {
        cell.innerHTML = 'false';
      }
      if (cell.innerHTML === '1') {
        cell.innerHTML = 'true';
      }
    }
  }
</script>
<script>
  document.querySelector('input[name="query"]').addEventListener('keydown', function (event) {
    if (event.key === 'Enter') {
      event.preventDefault();
      this.form.submit();
    }
  });
</script>
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