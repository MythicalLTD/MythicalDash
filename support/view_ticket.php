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
      if ($content == "") {
        $_SESSION['error'] = "Message can't be empty";
        echo '<script>window.location.replace("view_ticket.php?id='.$ticket_id.'");</script>';
        die();
      }
      else
      {
        $user_id = $_SESSION['uid'];
        $ticketdb = $cpconn->query("SELECT * FROM users WHERE user_id = '" . mysqli_real_escape_string($cpconn, $_SESSION["uid"]) . "'")->fetch_array();
        $query = "INSERT INTO messages (ticket_id, user_id, username, content) VALUES ('$ticket_id', '$user_id', '".$ticketdb['username']."', '$content')";
        mysqli_query($cpconn, $query);
        echo '<script>window.location.replace("view_ticket.php?id='.$ticket_id.'");</script>';
        exit;
      }

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
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">

    <title><?= $getsettingsdb["name"] ?></title>
    <?php 
		include('../core/imports/header.php');
  ?>
</head>

<body data-background-color="dark">
    <div class="wrapper">
        <div class="main-header">
            <div class="logo-header" data-background-color="dark2">
                <a href="/" class="logo">
                    <p style="color:white;" class="navbar-brand"><?= $getsettingsdb["name"]?></p>
                </a>
                <button class="navbar-toggler sidenav-toggler ml-auto" type="button" data-toggle="collapse"
                    data-target="collapse" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon">
                        <i class="icon-menu"></i>
                    </span>
                </button>
                <button class="topbar-toggler more"><i class="icon-options-vertical"></i></button>
                <div class="nav-toggle">
                    <button class="btn btn-toggle toggle-sidebar">
                        <i class="icon-menu"></i>
                    </button>
                </div>
            </div>
            <nav class="navbar navbar-header navbar-expand-lg" data-background-color="dark">

                <div class="container-fluid">
                    <ul class="navbar-nav topbar-nav ml-md-auto align-items-center">
                        <li class="nav-item dropdown hidden-caret">
                            <a class="dropdown-toggle profile-pic" data-toggle="dropdown" href="#"
                                aria-expanded="false">
                                <div class="avatar-sm">
                                    <img src="<?= $userdb['avatar']?>" alt="..." class="avatar-img rounded-circle">
                                </div>
                            </a>
                            <ul class="dropdown-menu dropdown-user animated fadeIn">
                                <div class="dropdown-user-scroll scrollbar-outer">
                                    <li>
                                        <div class="user-box">
                                            <div class="avatar-lg"><img src="<?= $userdb['avatar']?>"
                                                    alt="image profile" class="avatar-img rounded"></div>
                                            <div class="u-text">
                                                <h4><?= $userdb['username']?></h4>
                                                <p class="text-muted"><?= $userdb['role']?></p>
                                                <p class="text-muted">Coins: <?= $userdb['coins']?></p>
                                            </div>
                                        </div>
                                    </li>
                                    <li>
                                        <?php 
                                        if(!$userdb['discord_id'] == null || !$userdb['discord_username'] == null || !$userdb['discord_discriminator'] == null || !$userdb['discord_email'] == null)
                                        {
                                            ?>
                                                <div class="dropdown-divider"></div>
                                                <a class="dropdown-item" href="/auth/discord">Relink discord</a>
                                            <?php
                                        }
                                        else
                                        {
                                            ?>
                                                <div class="dropdown-divider"></div>
                                                <a class="dropdown-item" href="/auth/discord">Link discord</a>
                                            <?php
                                        }
                                        ?>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="/auth/logout">Logout</a>
                                    </li>
                                </div>
                            </ul>
                        </li>
                    </ul>
                </div>
            </nav>
        </div>
        <div class="sidebar sidebar-style-2" data-background-color="dark2">
            <div class="sidebar-wrapper scrollbar scrollbar-inner">
                <div class="sidebar-content">
                    <div class="user">
                        <div class="avatar-sm float-left mr-2">
                            <img src="<?= $userdb['avatar']?>" alt="..." class="avatar-img rounded-circle">
                        </div>
                        <div class="info">
                            <a data-toggle="collapse" href="#collapseExample" aria-expanded="true">
                                <span>
                                    <?= $userdb['username']?>

                                    <span class="user-level"><?= $userdb['role']?></span>

                                </span>
                            </a>
                        </div>
                    </div>
                    <ul class="nav nav-primary">
                        <li class="nav-section">
                            <span class="sidebar-mini-icon">
                                <i class="fa fa-ellipsis-h"></i>
                            </span>
                            <h4 class="text-section">Overview</h4>
                        </li>
                        <li class="nav-item">
                            <a href="/" class="collapsed">
                                <i class="fas fa-home"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/server/create" class="collapsed">
                                <i class="fas fa-plus-square"></i>
                                <p>New Server</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/store" class="collapsed">
                                <i class="fas fa-shopping-bag"></i>
                                <p>Shop</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a data-toggle="collapse" href="#earn">
                                <i class="fas fa-coins"></i>
                                <p>Earn</p>
                                <span class="caret"></span>
                            </a>
                            <div class="collapse" id="earn">
                                <ul class="nav nav-collapse">
                                    <li class="nav-item">
                                        <a href="/earn/afk" class="collapsed">
                                            <p>AFK</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="/earn/linkvertise" class="collapsed">
                                            <p>Linkvertise</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="/earn/redeem" class="collapsed">
                                            <p>Redeem</p>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        <li class="nav-item active">
                            <a href="/support/select" class="collapsed">
                                <i class="fa-solid fa-ticket" style="color: #b9babf;"></i>
                                <p>Support</p>
                            </a>
                        </li>
                        <li class="nav-section">
                            <span class="sidebar-mini-icon">
                                <i class="fa fa-ellipsis-h"></i>
                            </span>
                            <h4 class="text-section">Links</h4>
                        </li>
                        <?php 
                        if ($getsettingsdb['enable_mainwebsite'] == "false")
                        {

                        }
                        else
                        {
                            ?>
                        <li class="nav-item">
                            <a href="<?= $getsettingsdb['website']?>" class="collapsed">
                                <i class="bi bi-house-fill"></i>
                                <p>Website</p>
                            </a>
                        </li>
                        <?php 
                            
                        }
                        
                        if ($getsettingsdb['enable_discord'] == "false")
                        {

                        }
                        else
                        {
                            ?>
                        <li class="nav-item">
                            <a href="<?= $getsettingsdb['discordserver']?>" class="collapsed">
                                <i class="bi bi-discord"></i>
                                <p>Discord</p>
                            </a>
                        </li>
                        <?php
                        }

                        if ($getsettingsdb['enable_phpmyadmin'] == "false")
                        {

                        }
                        else
                        {
                            
                            ?>
                            <li class="nav-item">
                            <a href="<?= $getsettingsdb['phpmyadmin']?>" class="collapsed">
                                <i class="bi bi-server"></i>
                                <p>PhpMyAdmin</p>
                            </a>
                            </li>
                            <?php
                        }
                        if ($getsettingsdb['enable_status'] == "false")
                        {

                        }
                        else
                        {
                            ?>
                            <li class="nav-item">
                            <a href="<?= $getsettingsdb['statuspage']?>" class="collapsed">
                                <i class="fas fa-signal"></i>
                                <p>Status</p>
                            </a>
                            </li>
                            <?php
                        }
                        ?>

                        <li class="nav-item">
                            <a href="<?= $getsettingsdb['ptero_url']?>" class="collapsed">
                                <i class="fas fa-external-link-square-alt"></i>
                                <p>Panel</p>
                            </a>
                        </li>


                        <li class="nav-section">
                            <span class="sidebar-mini-icon">
                                <i class="fa fa-ellipsis-h"></i>
                            </span>
                            <h4 class="text-section">Administrative Overview</h4>
                        </li>

                    </ul>
                </div>
            </div>
        </div>
        <div class="main-panel">
            <div class="container">
                <div class="content">
                    <div class="page-inner">
                        <div class="mt-2 mb-4">
                            <h2 class="text-white pb-2">Welcome back, <?= $userdb['username']?>!</h2>
                        </div>
                        <div class="row">
                            <?php include('../core/imports/resources.php');?>
                        </div>
                        <?php         if (isset($_SESSION["error"])) {
            ?>
                        <div class="alert alert-danger text-danger" role="alert">
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
                        
<!-- Header -->

<style>
  ::-webkit-scrollbar {
  width: 3px;
}

/* Handle */
::-webkit-scrollbar-thumb {
  background: white;
  border-radius: 10px;
}

/* Handle on hover */
::-webkit-scrollbar-thumb:hover {
  background: #191C21;
}
</style>

<div class="container-fluid mt--6">
    <div class="row justify-content-center">
        <div class="col-lg-8 card-wrapper">
            <div class="card">
                <div class="card-header text-center">
                    <br>
                            <div class="message-header">
								<div class="message-title">
                                    <h1>Support Page</h1>
									<div class="user ml-2">
										<div class="info-user ml-2">
											<span class="name text-white"><?php echo $_SESSION['uid']; ?></span>
											<span class="last-active text-white">Ticket #<?php echo $_GET['id']; ?> | <?= $ticket_db['status']?></span>
										</div>
									</div>
									<div class="ml-auto">
										<button class="btn btn-secondary btn-link page-sidebar-toggler d-xl-none">
											<i class="fa fa-angle-double-left"></i>
										</button>
									</div>
								</div>
							</div>
                </div>
                <div class="card-body">
                  <ul class="list-group list-group-flush">
                    <?php
                    $ticket_id = $_GET['id'];
                    $query = "SELECT * FROM messages WHERE ticket_id='$ticket_id' ORDER BY created_at ASC";
                    $result = mysqli_query($cpconn, $query);
                    while ($row = mysqli_fetch_array($result)) {
                        echo '<li class="list-group-item">';
                        echo '<div>';
                        echo '<p class="text-white">' . $row['content'] . '</p>';
                        echo '<p class="text-muted">Sent by: <code>' . $row['username'] . '</code> at ' . $row['created_at'] . '</p>';
                        echo '</div>';
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
                                <input class="form-control" name="content" id="content" rows="3" ></input>
                                <br>
                            <?php 
                            if ($ticket_db['status'] == "closed")
                            {
                                ?>
                                    <button type="submit" name="reopen_ticket" class="btn btn-sm btn-primary">Open again</button>
                                    <button type="submit" name="delete_ticket" class="btn btn-sm btn-danger">Delete ticket</button>
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
<?php 
include('../core/imports/footer.php')
?>
</html>

</html>
