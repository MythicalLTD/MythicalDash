<?php
require("../core/require/page.php");
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
if ($getsettingsdb['disable_earning'] == "true")
{
    echo '<script>window.location.replace("/");</script>';
    $_SESSION['error'] = "You are not allowed to earn coins!";
    die;
}

if($getperms['canredeem'] == "true")
{

}
else
{
    echo '<script>window.location.replace("/");</script>';
    $_SESSION['error'] = "You are not allowed to redeem";
    die;
}

if(isset($_GET['submit'])) {
    $cpcode = mysqli_real_escape_string($cpconn ,$_GET['cpcode']);
    $query = "SELECT * FROM coupons WHERE code = '" . $cpcode . "'";
    $result = mysqli_query($cpconn, $query);
    if (mysqli_num_rows($result) > 0) {
        $couponsdb = $cpconn->query("SELECT * FROM coupons WHERE code = '" . $cpcode . "'")->fetch_array();
        //User stuff
        $u_uid = $_SESSION["uid"];
        $u_coins = $userdb['coins'];
        $u_ram = $userdb['memory'];
        $u_disk = $userdb['disk_space'];
        $u_ports = $userdb['ports'];
        $u_db = $userdb['databases'];
        $u_cpu = $userdb['cpu'];
        $u_svlimit = $userdb['server_limit'];
        $u_backupl = $userdb['backup_limit'];
        //Coupons db stuff
        $c_coins = $couponsdb['coins'];
        $c_ram = $couponsdb['ram'];
        $c_disk = $couponsdb['disk'];
        $c_cpu = $couponsdb['cpu'];
        $c_svslots = $couponsdb['slots'];
        $c_dbs = $couponsdb['dbs'];
        $c_ports = $couponsdb['alloc'];
        $c_claims = $couponsdb['hmtcliams'];
        $c_backupl = $couponsdb['bks'];
        //EZ MATH
        $q_coins = $u_coins + $c_coins;
        $q_ram = $u_ram + $c_ram;
        $q_disk = $u_disk + $c_disk;
        $q_ports = $u_ports + $c_ports;
        $q_db = $c_dbs + $u_db;
        $q_cpu = $c_cpu + $u_cpu;
        $q_svlmt = $u_svlimit + $c_svslots;
        $q_back = $u_backupl + $c_backupl;
        $cpconn->query("UPDATE `users` SET `coins` = '$q_coins' WHERE `users`.`user_id` = $u_uid;");
        $cpconn->query("UPDATE `users` SET `memory` = '$q_ram' WHERE `users`.`user_id` = $u_uid;");
        $cpconn->query("UPDATE `users` SET `disk_space` = '$q_disk' WHERE `users`.`user_id` = $u_uid;");
        $cpconn->query("UPDATE `users` SET `ports` = '$q_ports' WHERE `users`.`user_id` = $u_uid;");
        $cpconn->query("UPDATE `users` SET `databases` = '$q_db' WHERE `users`.`user_id` = $u_uid;");
        $cpconn->query("UPDATE `users` SET `cpu` = '$q_cpu' WHERE `users`.`user_id` = $u_uid;");
        $cpconn->query("UPDATE `users` SET `server_limit` = '$q_svlmt' WHERE `users`.`user_id` = $u_uid;");
        $cpconn->query("UPDATE `users` SET `backup_limit` = '$q_back' WHERE `users`.`user_id` = $u_uid;");
        if ($c_claims > 1) {
            $new_claim = $c_claims - 1;
            $cpconn->query("UPDATE `coupons` SET `hmtcliams` = '".$new_claim."' WHERE `coupons`.`code` = '".$cpcode."';");
            
        }
        else {
            $cpconn->query("DELETE FROM `coupons` WHERE `coupons`.`code`= '".$cpcode."';");
        }
        $_SESSION['success'] = "We added the resources in your account!";  
           
    }
    else
    {
        $_SESSION['error'] = "We can't find this code!";    
    }
    
}
?>

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

                        <li class="nav-item active">
                            <a data-toggle="collapse" href="#earn">
                                <i class="fas fa-coins"></i>
                                <p>Earn</p>
                                <span class="caret"></span>
                            </a>
                            <div class="collapse" id="earn">
                                <ul class="nav nav-collapse">
                                    <li class="nav-item ">
                                        <a href="/earn/afk" class="collapsed">
                                            <p class="active">AFK</p>
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
					<?php include('../core/imports/resources.php');?>
					</div>
					<?php
        if (isset($_SESSION["error"])) {
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
                        <div class="col-md-12 grid-margin stretch-card">
                            <div class="card">
                                <div class="card-header">
                                    <div class="card-title">Redeem A Coupon Code</div>
                                </div>
                                <div class="card-body">
									<form method='	'>
										<center>
											<h4>Please enter the coupon code you would like to redeem below!</h4>
											<input type="text" class="form-control" placeholder="Enter your coupon code here" name="cpcode">
											<br>
											<button name="submit"class="btn btn-primary" style="background-color: #33404D;">Redeem</button>
										</center>
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
  </html>

  <?php include '../core/imports/footer.php'; ?>
