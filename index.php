<?php
include_once("core/require/sql.php");
$getsettingsdb = $cpconn->query("SELECT * FROM settings")->fetch_array();
if (isset($_COOKIE['remember_token'])) {
  $session_id = $_COOKIE['remember_token'];
  $query = "SELECT * FROM users WHERE session_id='".$session_id."'";
  $result = mysqli_query($cpconn, $query);
  if (mysqli_num_rows($result) > 0) {
      session_start();
      $userdbd = $cpconn->query("SELECT * FROM users WHERE session_id='$session_id'")->fetch_array();
      $_SESSION['username'] = $userdbd['username'];
      $_SESSION['email'] = $userdbd['email'];
      $_SESSION["uid"] = $userdbd['user_id'];
  }
  else
  {
      setcookie('remember_token', '', time() - 3600, '/');
      setcookie('phpsessid', '', time() - 3600, '/');
      session_destroy();
      echo '<script>window.location.replace("/auth/login");</script>';
  }

}
else
{
  setcookie('remember_token', '', time() - 3600, '/');
  setcookie('phpsessid', '', time() - 3600, '/');
  session_destroy();
  echo '<script>window.location.replace("/auth/login");</script>';
}

$userdb = $cpconn->query("SELECT * FROM users WHERE user_id = '" . mysqli_real_escape_string($cpconn, $_SESSION["uid"]) . "'")->fetch_array();
$getperms = $cpconn->query("SELECT * FROM roles WHERE name= '". $userdb['role']. "'")->fetch_array();
if ($getsettingsdb['maintenance'] == "false")
{

}
else
{
  if ($getperms['canbypassmaintenance'] == "false")
  {
    if ($getperms['fullperm'] == "true")
    {

    }
    else
    {
      if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')   
      {
        $url = "https://";  
      }
      else 
      {
        $url = "http://"; 
      }         
      $url.= $_SERVER['HTTP_HOST'];                      
      echo '<script>window.location.replace("'.$url.'/auth/errors/maintenance");</script>';

      die;
    }
  }
  else
  {

  }
  
}



?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">

    <title><?= $getsettingsdb["name"] ?></title>
    <?php 
		include('core/imports/header.php');
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
                        <li class="nav-item active">
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
                        <li class="nav-item">
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
                            <?php include('core/imports/resources.php');?>
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
                        <div class="row">
                            <div class="col">
                                <div class="card bg-default shadow">
                                    <div class="card-header bg-transparent border-0">
                                        <h3 class="text-white mb-0">Your servers</h3>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table align-items-center table-flush">
                                            <tbody class="list">
                                                <?php
                            if (count($uservers) == 0 && $servers_in_queue->num_rows == 0) {
                                ?>
                                                <div style="text-align: center;">
                                                    <img src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/img/empty.svg"
                                                        height="150" /><br />
                                                    <h2 style="color: white;">No servers yet. Why not create one?</h2>
                                                    <a href="server/create" class="btn btn-info">Create a new
                                                        server</a><br /><br />
                                                </div>
                                                <?php
                            } else {
                                ?>
                                                <thead class="">
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
                                                                <span
                                                                    class="name mb-0 text-sm"><?= $server["name"] ?></span>
                                                            </div>
                                                        </div>
                                                    </th>
                                                    <td>
                                                        <?= $location["name"] ?>
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-dot mr-4"><i
                                                                class="bg-danger"></i><span class="status">In queue
                                                                (Position
                                                                <?= $serverpos . "/" . $currentnodequeue->num_rows ?>)
                                                                <br />
                                                    </td>
                                                    <td>
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
                                                        <a href="server/queueDelete?server=<?= $server["id"] ?>"
                                                            class="btn btn-danger btn-sm"><i class="fas fa-trash"></i>
                                                            Delete</a>
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
                                                                <span
                                                                    class="name mb-0 text-sm"><?= $server["name"] ?></span>
                                                            </div>
                                                        </div>
                                                    </th>
                                                    <td>
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
                                                        <a href="<?= $getsettingsdb["ptero_url"] . "/server/" . $server["identifier"] ?>"
                                                            class="btn btn-primary btn-sm" data-trigger="hover"
                                                            data-container="body" data-toggle="popover"
                                                            data-color="default" data-placement="left"
                                                            data-content="Open in the game panel"><i
                                                                class="fas fa-external-link-square-alt"></i></a>
                                                        <a href="/server/fetch?id=<?= $server["id"] ?>"
                                                            class="btn btn-primary btn-sm">Fetch</a>
                                                        <a href="/server/rename?id=<?= $server["id"] ?>"
                                                            class="btn btn-primary btn-sm">Rename</a>
                                                        <a href="/server/manage?id=<?= $server["id"] ?>"
                                                            class="btn btn-primary btn-sm">Edit</a>
                                                        <a href="/server/delete?server=<?= $server["id"] ?>"><button
                                                                type="button" class="btn btn-danger btn-sm"><i
                                                                    class="fas fa-trash"></i> Delete</button></a>

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
                    </div>
                    <script src="core/test.js" theme="blurple" defer=""></script>
                </div>
            </div>

            <?php 
            include('core/imports/credits.php')
            ?>
        </div>
    </div>
    
</body>
<?php 
include('core/imports/footer.php')
?>

</html>