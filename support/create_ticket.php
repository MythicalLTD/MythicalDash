<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require('../core/require/page.php');


if (isset($_POST['create_ticket'])) {
    $user_id = $_SESSION['uid'];
    $query = "SELECT COUNT(*) FROM tickets WHERE user_id = '$user_id' AND status='open'";
    $result = mysqli_query($cpconn, $query);
    $count = mysqli_fetch_array($result)[0];
    if ($count >= 3) {
      $_SESSION['error'] = "Please close your old tickets";
      echo '<script>window.location.replace("/");</script>';
      die();
    } else {
      $title = mysqli_real_escape_string($cpconn, $_POST['title']);
      $content = mysqli_real_escape_string($cpconn, $_POST['content']);
      $ticketdb = $cpconn->query("SELECT * FROM users WHERE user_id = '" . mysqli_real_escape_string($cpconn, $_SESSION["uid"]) . "'")->fetch_array();

      $query = "INSERT INTO tickets (user_id, username, content, status) VALUES ('$user_id', '".$ticketdb['username']."','$content', 'open')";
      mysqli_query($cpconn, $query);
      $ticket_id = mysqli_insert_id($cpconn);
    
      $query = "INSERT INTO messages (user_id, username, ticket_id, content) VALUES ('$user_id', '".$ticketdb['username']."','$ticket_id', '$content')";
      mysqli_query($cpconn, $query);
      $current_date_and_time = date("Y-m-d H:i");
      $hex = $getsettingsdb['seo_color'];

      $r = hexdec(substr($hex, 1, 2));
      $g = hexdec(substr($hex, 3, 2));
      $b = hexdec(substr($hex, 5, 2));

      $rgb = ($r << 16) + ($g << 8) + $b;

      $json_data = json_encode([
        "content" => "@everyone <".$getsettingsdb["proto"] . $_SERVER['SERVER_NAME']."/support/view_ticket.php?id=$ticket_id>",
        "embeds" => [
            [
               "title" => $title."",
                "description" => "A new ticket has been created on the dashboard.",
                "color" => $rgb,
                "fields" => [
                    [
                        "name" => "Ticket owner",
                        "value" => $ticketdb['username'],
                    ],
                    [
                        "name" => "Ticket Title",
                        "value" => $title,
                    ],
                    [
                        "name" => "Ticket Description",
                        "value" => $content,
                    ],
                    [
                      "name" => "Ticket creation date",
                      "value" => $current_date_and_time,
                    ],
                ],
            ],
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $getsettingsdb['webhook'],
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $json_data,
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json"
        ],
    ]);
    
    $response = curl_exec($ch);
    curl_close($ch);
    echo '<script>window.location.replace("view_ticket.php?id='.$ticket_id.'");</script>';
    mysqli_close($cpconn);
    exit;
    }
   
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
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="/regen">Reset Password</a>
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
<div class="container">
    <div class="content">
        <div class="page-inner">
            <div class="page-header"></div>
            <div class="container-fluid py-4">
                <div class="row mt-4">
                    <div class="col-12 col-md-12 mb-4 mb-md-0">
                        <div class="">
                            <div class="" >
                                <br><br><br>
                                <div class="row justify-content-center">
                                    <h4 class="card-title">Create A Ticket</h4>
                                </div>
                                <div class="">
                                    <br />
                                    <form action="create_ticket.php" method="post">
                                        <div class="row ">
                                            <div class="col mx-3">
                                                <label style="font-size: 20px;" class="text-white">Ticket Name</label>
                                                <input type="text" name="title" class="form-control" placeholder="Title of Ticket" style="background-color: #293445; color: white; border: none;" required="" />
                                                <br />
                                                <div class="divider"></div>

                                                <div style="background-color: #051a10 !important; border-color: #0f5132 !important; color: #75b798 !important; background-image: none !important;" class="alert alert-danger" role="alert">
                                                    Please describe your issue with a good description.
                                                </div>
                                                <label style="font-size: 20px;" class="text-white">Description:</label>
                                                <textarea name="content" type="text" placeholder="Tell us your problem here ..." rows="9"  class="form-control" style="background-color: #293445; color: white; border: none;" required=""></textarea>
                                                <p class="text-white">Your user ID is: <?php echo $_SESSION['uid']; ?></p>
                                                <div class="row justify-content-center">
                                                    <button type="submit" name="create_ticket" class="btn btn-primary row ">Create Ticket</button>
                                                </div>
                                                <br />
                                                <style>
                                                    .e {
                                                        margin-bottom: 10px;
                                                        margin-top: 10px;
                                                    }
                                                    .e:hover {
                                                        -webkit-user-select: none;
                                                        transform: scale(1.03);
                                                        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.12), 0 4px 8px rgba(0, 0, 0, 0.06);
                                                    }
                                                    .padding-0 {
                                                        padding-right: -20px !important;
                                                        padding-left: 0 !important;
                                                    }
                                                </style>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
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
