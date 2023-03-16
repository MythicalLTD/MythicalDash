<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require('../core/require/page.php');

$usrdb = $cpconn->query("SELECT * FROM users WHERE user_id = '" . mysqli_real_escape_string($cpconn, $_SESSION["uid"]) . "'")->fetch_array();
$perms = $cpconn->query("SELECT * FROM roles WHERE name='".$usrdb['role']."'")->fetch_array();


if (isset($_GET['id'])) {
  $ticket_id = $_GET['id'];
  $query = "SELECT * FROM tickets WHERE id='$ticket_id'";
  $result = mysqli_query($cpconn, $query);
  if (mysqli_num_rows($result) > 0) {
    $query = "SELECT * FROM tickets WHERE id='$ticket_id'";
    $result = mysqli_query($cpconn, $query);
    $ticket = mysqli_fetch_assoc($result);
    $query = "SELECT * FROM messages WHERE ticket_id='$ticket_id' ORDER BY created_at DESC";
    $result = mysqli_query($cpconn, $query);
    $messages = mysqli_fetch_all($result, MYSQLI_ASSOC);
    $ticket_db = $cpconn->query("SELECT * FROM tickets WHERE id = '" . $ticket_id . "'")->fetch_array();
    if ($ticket_db['user_id'] == $_SESSION['uid'])
    {

    }
    else
    {
      if ($perms['issupport'] == "true" || $perms['fullperm'] == "true")
      {

      }
      else
      {
        $_SESSION['error'] = "You are not in the support team!";
        echo '<script>window.location.replace("/");</script>';
        die();
      }
      
    }
    if (isset($_POST['add_message'])) {
      $ticket_id = $_POST['ticket_id'];
      $content = mysqli_real_escape_string($cpconn, $_POST['content']);
      $user_id = $_SESSION['uid'];
      $ticketdb = $cpconn->query("SELECT * FROM users WHERE user_id = '" . mysqli_real_escape_string($cpconn, $_SESSION["uid"]) . "'")->fetch_array();
      $query = "INSERT INTO messages (ticket_id, user_id, username, content) VALUES ('$ticket_id', '$user_id', '".$ticketdb['username']."', '$content')";
      mysqli_query($cpconn, $query);
      echo '<script>window.location.replace("view_ticket.php?id='.$ticket_id.'");</script>';
      exit;
    }

    if (isset($_POST['reopen_ticket'])) {
      $query = "UPDATE tickets SET status='open' WHERE id='$ticket_id'";
      mysqli_query($cpconn, $query);
      echo '<script>window.location.replace("view_ticket.php?id='.$ticket_id.'");</script>';
      exit;
    }

    if (isset($_POST['close_ticket'])) {
      $query = "UPDATE tickets SET status='closed' WHERE id='$ticket_id'";
      mysqli_query($cpconn, $query);
      echo '<script>window.location.replace("view_ticket.php?id='.$ticket_id.'");</script>';
      exit;
    }

    if (isset($_POST['delete_ticket'])) {
      $query = "DELETE FROM tickets WHERE id='$ticket_id'";
      mysqli_query($cpconn, $query);
      $query = "DELETE FROM messages WHERE ticket_id='$ticket_id'";
      mysqli_query($cpconn, $query);
      echo '<script>window.location.replace("/");</script>';
      exit;
    }
    
  }
  else
  {
    $_SESSION['error'] = "We can`t find this ticket!";
    echo '<script>window.location.replace("/");</script>';
    die();
  }
  
}
else
{
  $_SESSION['error'] = "You are not allowed to see this page";
  echo '<script>window.location.replace("/");</script>';
  die();
}
?>
<!-- Header -->
<div class="header bg-primary pb-6">
    <div class="container-fluid">
        <div class="header-body">
            <div class="row align-items-center py-4">
                <div class="col-lg-6 col-7">
                    <h6 class="h2 text-white d-inline-block mb-0">Support</h6>
                    <nav aria-label="breadcrumb" class="d-none d-md-inline-block ml-md-4">
                        <ol class="breadcrumb breadcrumb-links breadcrumb-dark">
                            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
                            <li class="breadcrumb-item active" aria-current="page">Tickets</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>
<input id="node" name="node" type="hidden" value="">
<div class="container-fluid mt--6">
    <div class="row justify-content-center">
        <div class="col-lg-8 card-wrapper">
            <div class="card">
                <div class="card-header text-center">
                  <h1>Ticket #<?php echo $_GET['id']; ?> | <?= $ticket_db['status']?></h1>
                  <p>Your user ID is: <?php echo $_SESSION['uid']; ?></p>
                </div>
                <div class="card-body">
                  <div class="card-header">
                    Messages:
                  </div>
                  <ul class="list-group list-group-flush">
                    <?php
                    $ticket_id = $_GET['id'];
                    $query = "SELECT * FROM messages WHERE ticket_id='$ticket_id' ORDER BY created_at ASC";
                    $result = mysqli_query($cpconn, $query);
                    while ($row = mysqli_fetch_array($result)) {
                      echo '<li class="list-group-item">';
                      echo '<p>' . $row['content'] . '</p>';
                      echo '<p class="text-muted">Sent by: <code>' . $row['username'] . '</code> at ' . $row['created_at'] . '</p>';
                      echo '</li>';
                    }
                    ?>
                  </ul>
                </div>
                <div class="card-body">
                <form method="post">
                  <input type="hidden" name="ticket_id" value="<?php echo $ticket_id; ?>">
                  <div class="form-group">
                    <label for="content">Message</label>
                    <textarea class="form-control" name="content" id="content" rows="3"></textarea>
                  </div>
                  <?php 
                  if ($ticket_db['status'] == "closed")
                  {
                      ?>
                        <button type="submit" name="reopen_ticket" class="btn btn-primary">Open again</button>
                        <button type="submit" name="delete_ticket" class="btn btn-danger">Delete ticket</button>
                      <?php
                  }
                  else if ($ticket_db['status'] == "open")
                  {
                    ?>
                      <button type="submit" name="add_message" class="btn btn-primary">Add Message</button>
                      <button type="submit" name="close_ticket" class="btn btn-danger">Close ticket</button>
                    <?php
                  }
                  else
                  {

                  }
                  ?>
                </form>
                  </div>
            </div>
        </div>
    </div>
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

</html>
