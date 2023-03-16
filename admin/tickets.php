
<?php
require('../core/require/page.php');
$usrdb = $cpconn->query("SELECT * FROM users WHERE user_id = '" . mysqli_real_escape_string($cpconn, $_SESSION["uid"]) . "'")->fetch_array();
$perms = $cpconn->query("SELECT * FROM roles WHERE name='".$usrdb['role']."'")->fetch_array();

if ($perms['canseeadminhomepage'] == "true" || $perms['issupport'] == "true" || $perms['fullperm'] == "true")
{

}
else
{
  echo '<script>window.location.replace("/");</script>';
  $_SESSION['error'] = "You are not allowed to see this page";
  die;
}
if (isset($_GET['close'])) {
  $ticket_id = $_GET['id'];
  $query = "UPDATE tickets SET status='closed' WHERE id='$ticket_id'";
  mysqli_query($cpconn, $query);
  echo '<script>window.location.replace("/admin/tickets");</script>';
  exit;
}
if (isset($_GET['reopen'])) {
  $ticket_id = $_GET['id'];
  $query = "UPDATE tickets SET status='open' WHERE id='$ticket_id'";
  mysqli_query($cpconn, $query);
  echo '<script>window.location.replace("/admin/tickets");</script>';
  exit;
}
if (isset($_GET['delete'])) {
  $ticket_id = $_GET['id'];
  $query = "DELETE FROM tickets WHERE id='$ticket_id'";
  mysqli_query($cpconn, $query);
  $query = "DELETE FROM messages WHERE ticket_id='$ticket_id'";
  mysqli_query($cpconn, $query);
  echo '<script>window.location.replace("/admin/tickets");</script>';
  exit;
}
$query = "SELECT * FROM tickets WHERE status='open'";
$result = mysqli_query($cpconn, $query);
echo '
<body class="bg-dark text-light">
<div class="container-fluid text-center bg-dark text-light py-4">
    <h1>Welcome to the admin tickets page</h1>
    <p>Here you can find every ticket on your host!</p>
  </div>
<div class="container text-center bg-dark text-light">
  <form method="get">
    <div class="form-group">
      <input type="text" class="form-control" name="query" value="'.$txtsrch.'" placeholder="Search">
    </div>
  </form>
</div>
';

if (isset($_GET['query'])) {
  $search_term = '%' . mysqli_real_escape_string($cpconn, $_GET['query']) . '%';
  $sresult = mysqli_query($cpconn, "SELECT * FROM tickets WHERE username LIKE '$search_term'");
  if ($sresult) {
    echo "<table id='tickets' class='table table-sm table-borderless'>";
    echo "<thead class='thead-dark'><tr><th>ID</th><th>Owner</th><th>Content</th><th>Status</th><th>Created at</th><th>Actions</th></tr></thead>";
    echo "<tbody>";
    while ($row1 = mysqli_fetch_assoc($sresult)) {
      echo "<tr>";
      echo "<td class='text-white'>" . $row1["id"] . "</td>";
      echo "<td class='text-white'>" . $row1["username"] . "</td>";
      echo "<td class='text-white'>" . $row1["content"] . "</td>";
      echo "<td class='text-white'>" . $row1["status"] . "</td>";
      echo "<td class='text-white'>" . $row1["created_at"] . "</td>";
      echo "<td class='text-white'>";
      echo "<a href='../support/view_ticket?id=".$row["id"]."' class='btn btn-sm btn-primary'>Open</a>";
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
  echo "<table id='tickets' class='table table-sm table-borderless'>";
  echo "<thead class='thead-dark'><tr><th>ID</th><th>Owner</th><th>Content</th><th>Status</th><th>Created at</th><th>Actions</th></tr></thead>";
  echo "<tbody>";
  $usersdb = mysqli_query($cpconn, "SELECT * FROM tickets")->fetch_object();
  $result = mysqli_query($cpconn, "SELECT * FROM tickets");

  while ($row = mysqli_fetch_assoc($result)) {
      $tkdba = $cpconn->query("SELECT * FROM tickets WHERE id = '" . $row["id"] . "'")->fetch_array();
      echo "<tr>";
      echo "<td class='text-white'>" . $row["id"] . "</td>";
      echo "<td class='text-white'>" . $row["username"] . "</td>";
      echo "<td class='text-white'>" . $row["content"] . "</td>";
      echo "<td class='text-white'>" . $row["status"] . "</td>";
      echo "<td class='text-white'>" . $row["created_at"] . "</td>";
      echo "<td class='text-white'>";
      if ($tkdba['status'] == "open")
      {
        echo "<a href='../support/view_ticket?id=".$row["id"]."' class='btn btn-sm btn-primary'>Open</a>";
        echo "<a href='tickets?id=".$row["id"]."&close' class='btn btn-sm btn-danger'>Close</a>";
      }
      else if ($tkdba['status'] == "closed")
      {
        echo "<a href='tickets?id=".$row["id"]."&reopen' class='btn btn-sm btn-primary'>Open again</a>";
        echo "<a href='tickets?id=".$row["id"]."&delete' class='btn btn-sm btn-danger'>Delete</a>";
      }
      echo "</td>";
      echo "</tr>";
  }
  echo "</tbody></table>";
}
?>
</body>
<script>
      $("#gamepanelopen").popover({ trigger: "hover" });
  </script>
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/argon/js/jquery.min.js"></script>
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/argon/js/bootstrap.bundle.min.js"></script>
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/argon/js/js.cookie.js"></script>
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/argon/js/jquery.scrollbar.min.js"></script>
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/argon/js/jquery-scrollLock.min.js"></script>
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/argon/js/argon.js?v=1.2.0"></script>
</html>
