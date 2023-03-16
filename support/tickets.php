<?php 
require('../core/require/page.php');
?>
<?php
$query = "SELECT * FROM tickets WHERE status='open'";
$result = mysqli_query($cpconn, $query);
echo '
<body class="bg-dark text-light">
<div class="container-fluid text-center bg-dark text-light py-4">
    <h1>Welcome to the tickets page</h1>
    <p>Here you can see your support tickets!</p>
  </div>
<div class="container text-center bg-dark text-light">
  <form method="get">
    <div class="form-group">
      <input type="text" class="form-control" name="query" value="'.$txtsrch.'" placeholder="Search">
    </div>
  </form>
</div>
';

echo "<table id='tickets' class='table table-sm table-borderless'>";
echo "<thead class='thead-dark'><tr><th>ID</th><th>Owner</th><th>Content</th><th>Status</th><th>Created at</th><th>Actions</th></tr></thead>";
echo "<tbody>";
$result = mysqli_query($cpconn, "SELECT * FROM tickets WHERE user_id='".$_SESSION['uid']."'");
while ($row = mysqli_fetch_assoc($result)) {
    echo "<tr>";
    echo "<td class='text-white'>" . $row["id"] . "</td>";
    echo "<td class='text-white'>" . $row["username"] . "</td>";
    echo "<td class='text-white'>" . $row["content"] . "</td>";
    echo "<td class='text-white'>" . $row["status"] . "</td>";
    echo "<td class='text-white'>" . $row["created_at"] . "</td>";
    echo "<td class='text-white'>";
    echo "<a href='view_ticket?id=".$row["id"]."' class='btn btn-sm btn-primary'>Open</a>";
    echo "</td>";
    echo "</tr>";
}
echo "</tbody></table>";

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
  <!-- Argon JS -->
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/argon/js/argon.js?v=1.2.0"></script>
</html>
