<?php 
require('../core/require/page.php');
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
    <p>Here you can find your friends to give them coins or get info about some users</p>
  </div>
<div class="container text-center bg-dark text-light">
  <form method="get">
    <div class="form-group">
      <input type="text" class="form-control" name="query" value="'.$txtsrch.'" placeholder="Search">
    </div>
  </form>
</div>
';



$usersdb = mysqli_query($cpconn, "SELECT * FROM users")->fetch_object();
$result = mysqli_query($cpconn, "SELECT * FROM users");



if (isset($_GET['query'])) {
  $search_term = '%' . mysqli_real_escape_string($cpconn, $_GET['query']) . '%';
  $sresult = mysqli_query($cpconn, "SELECT * FROM users WHERE username LIKE '$search_term'");

  if ($sresult) {
    echo "<table id='users' class='table table-sm table-borderless'>";
    echo "<thead class='thead-dark'><tr><th>ID</th><th>Username</th><th>User id</th><th>Role</th><th>Actions</th></tr></thead>";
    echo "<tbody>";
    while ($row1 = mysqli_fetch_assoc($sresult)) {
      echo "<tr>";
      echo "<td class='text-white'>" . $row1["id"] . "</td>";
      echo "<td class='text-white'>" . $row1["username"] . "</td>";
      echo "<td class='text-white'>" . $row1["user_id"] . "</td>";
      echo "<td class='text-white'>" . $row1["role"] . "</td>";
      echo "<td class='text-white'>";
      echo "<a href='profile?id=" . $row1["user_id"] . "' class='btn btn-sm btn-primary'>Info</a>";
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
    echo "<thead class='thead-dark'><tr><th>ID</th><th>Username</th><th>User id</th><th>Role</th><th>Actions</th></tr></thead>";
    echo "<tbody>";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td class='text-white'>" . $row["id"] . "</td>";
        echo "<td class='text-white'>" . $row["username"] . "</td>";
        echo "<td class='text-white'>" . $row["user_id"] . "</td>";
        echo "<td class='text-white'>" . $row["role"] . "</td>";
        echo "<td class='text-white'>";
        echo "<a href='profile?id=" . $row["user_id"] . "' class='btn btn-sm btn-primary'>Profile</a>";
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