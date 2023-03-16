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


include('core/imports/resources.php');

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
<div class="container">
    <div class="content">
        <div class="page-inner">
            <div class="page-header"></div>
            <div class="container-fluid py-4">
                <div class="row mt-4">
                <div class="card" id="loadingCard" style="display: none;">
                <div class="card-header">
                    <h4 class="card-title"></h4>
                </div>
                <div class="card-content collapse show" aria-expanded="true">
                    <div class="card-body">
                        <p class="card-text">
                        <center>
                            <h3>
                                <img src="https://i.imgur.com/UxVBmZl.png" /><br/>
                                Please wait
                                <br/><br/>
                                <img src="/assets/img/loading.gif" width="64">
                            </h3>
                        </center>
                        </p>
                    </div>
                </div>
            </div>
            <!--
                STEP 1
            --->
            <div class="card" id="step1">
                <div class="card-header">
                    <h4 class="card-title">Set your server info and specs.</h4>
                </div>
                <div class="container">
    <div class="content">
        <div class="page-inner">
            <div class="page-header"></div>
            <div class="container-fluid py-4">
                <div class="row mt-4">
                    <div class="col-12 col-md-12 mb-4 mb-md-0">
                        <div class="card">
                            <div class="card-header">
                                <h3>Create a Server</h3>
                            </div>

                            <div class="card" id="step1">
                                <div class="card-header">
                                    <h4 class="card-title">Set your server info and specs.</h4>
                                </div>
                                <div class="card-body">
                                    <br />
                                    <div class="row">
                                        <div class="col mx-3">
                                            <label style="font-size: 20px;">Server Name:</label>
                                            <input type="text" name="server-name" class="form-control" value="My Server" style="background-color: #293445; color: white; border: none;" required="" />
                                            <br />
                                            <div class="divider"></div>

                                            <div style="background-color: #051a10 !important; border-color: #0f5132 !important; color: #75b798 !important; background-image: none !important;" class="alert alert-success" role="alert">
                                                We've automatically entered your available resources.
                                            </div>
                                            <label style="font-size: 20px;">Memory:</label>
                                            <input name="memory" type="number" value="2048" min="256" max="65536" class="form-control" style="background-color: #293445; color: white; border: none;" required="" />
                                            <br />
                                            <label style="font-size: 20px;">Storage:</label>
                                            <input name="storage" type="number" value="5120" min="256" max="65536" class="form-control" style="background-color: #293445; color: white; border: none;" required="" />
                                            <br />
                                            <label style="font-size: 20px;">CPU cores:</label>
                                            <input name="cpu-cores" type="number" value="1" min="0.1" step=".01" class="form-control" style="background-color: #293445; color: white; border: none;" required="" />
                                            <br />
                                            <label style="font-size: 20px;">Ports:</label>
                                            <input name="ports" type="number" value="1" min="0" max="12" class="form-control" style="background-color: #293445; color: white; border: none;" required="" />
                                            <br />
                                            <label style="font-size: 20px;">Databases:</label>
                                            <input name="databases" type="number" value="1" min="0" max="12" class="form-control" style="background-color: #293445; color: white; border: none;" required="" /><br />
                                            <div class="divider"></div>

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
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

            </div>
            <!---
                Step 2
            --->
            <div class="card" id="step2" style="display: none;">
                <div class="card-header">
                    <h4 class="card-title" id="selectNodeText">Select the location for your server</h4>
                </div>
                <div class="card-content collapse show" aria-expanded="true">
                    <div class="card-body">
                        <p class="card-text">
                            <div style="text-align: center;">
                                <div class="container">
                                    <div class="row">
                                    <?php
                                    $locations = mysqli_query($cpconn, "SELECT * FROM locations")->fetch_all(MYSQLI_ASSOC);
                                    foreach ($locations as $location) {
                                    $serversOnLoc = mysqli_query($cpconn, "SELECT * FROM servers WHERE location='" . $location["id"] . "'")->fetch_all(MYSQLI_ASSOC);
                                    $availableSlots = $location['slots'] - count($serversOnLoc);
                                    $serversInQueue = mysqli_query($cpconn, "SELECT * FROM servers_queue WHERE location='" . $location["id"] . "'")->fetch_all(MYSQLI_ASSOC);
                                    ?><div class="col-sm">
                                        <?php
                                        if ($location["status"] == "PRIVATE") {
                                            echo "<span class='badge badge-warning badge-glow' style='font-size: 15px;'>Private node</span>";
                                        }
                                        if ($location["status"] == "MAINTENANCE") {
                                            echo "<span class='badge badge-danger badge-glow' style='font-size: 15px;'>In maintenance</span>";
                                        }
                                        ?>
                                        <br/>
                                        <img src="<?=$location["icon"] ?>" width="70">
                                        <h3><?= $location["name"] ?></h3>
                        <p><b><?= $availableSlots ?></b> out of <b><?php echo $location["slots"]; ?></b> slots left.<br/>
                            <b><?php echo count($serversInQueue); ?></b> servers in queue.</p>
                        <br/><br/>
                        <?php
                        if ($location["status"] == "PRIVATE") {
                            if ($isDonator == false) {
                                echo '<button type="button" class="btn btn-danger" style="cursor: not-allowed;" disabled="1">Private node users only</button><br/><a href="/billing/buy/private">Buy private node access</a>';
                            }
                            else {
                                echo '<button type="button" class="btn btn-primary" id="btnnode' . $location["id"] . '" onclick="selectNode(' . $location["id"] . ', this);">SELECT</button>';
                            }

                        } elseif ($location["status"] == "MAINTENANCE") {
                            echo '<button type="button" class="btn btn-danger" style="cursor: not-allowed;" disabled="1">Maintenance</button>';
                        } else {
                            echo '<button type="button" class="btn btn-primary" id="btnnode' . $location["id"] . '" tag="nodeselectionbutton" onclick="selectNode(' . $location["id"] . ', this);">SELECT</button>';
                        }
                        ?>
                    </div>
                    <?php
                    }
		    if (count($locations) == 0) {
			echo "No nodes are available at the moment; Server creation might currently be disabled.";
		    }
                    ?>
                </div>
                </div>
                </center>
                </p>
                <button class="btn btn-primary" id="prevstep2btn" onclick="previousStep()">« Previous</button>
                <button class="btn btn-primary" id="step2btn" disabled="1" onClick="nextStep();">Next »</button>
            </div>
        </div>
    </div>
</div>
    <!---
        Step 3
    --->
    <div class="card" id="step3" style="display: none;">
        <div class="card-header">
            <h4 class="card-title">Select the server type</h4>
        </div>
        <div class="card-content collapse show" aria-expanded="true">
            <div class="card-body">
                <p class="card-text">
                    <button class="btn btn-primary" onclick="previousStep()">Previous «</button>
                <div style="text-align: center;">
                    <div class="container">
                        <div class="row">
                    <?php
                    $alrCategories = array();
                    $categories = mysqli_query($cpconn, "SELECT category FROM eggs")->fetch_all(MYSQLI_ASSOC);
                    foreach ($categories as $category) {
                        if (in_array($category["category"], $alrCategories)) {
                            continue;
                        }
                        echo "</div><br/><br/>";
                        array_push($alrCategories, $category["category"]);
                        echo "<h3>" . $category["category"] . "</h3> <br/>";
                        $eggs = mysqli_query($cpconn, "SELECT * FROM eggs WHERE category='" . $category["category"] . "'")->fetch_all(MYSQLI_ASSOC);
                        $i = -1;
                        echo '<div class="row">';
                        foreach ($eggs as $egg) {
                            $i++;
                            if ($i == 3) {
                                echo "</div><div class='row' style='align-content: center;'>";
                                $i = 0;
                            }
                            ?>
                            <div class="col-sm">
                                <br/>
                                <img src="<?= $egg["icon"] ?>" width="70">
                                <h3><?= $egg["name"] ?></h3>
                                <br/><br/>

                                <button type="button" onclick="submitForm(<?= $egg['id'] ?>)" class="btn btn-primary">Create!</button>
                            </div>
                            <?php
                        }
                    }
                    ?>
                        </div>
                    </div>
            </div>
            </center>
            </p><br/><br/>
        </div>
    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    var currentStep = 1;
    async function previousStep() {
        currentStep--;
        lastStep = currentStep - 1;
        var w = parseInt(document.getElementById('stepProgress').style.width);
        document.getElementById('stepProgress').style.width= (w - 30) +'%';
        document.getElementById("currentStep").innerHTML = "Step " + currentStep + "/3"
        document.getElementById("stepPercentage").innerHTML = (currentStep*33) + "%"
        if (currentStep == 1) {
            document.getElementById("step1").style.display = "block";
            document.getElementById("step1").style.display = "block";
            document.getElementById("step2").style.display = "none";
        }
        if (currentStep == 2) {
            document.getElementById("step2").style.display = "block";
            document.getElementById("step3").style.display = "none";
        }
        if (currentStep == 3) {
            document.getElementById("step3").style.display = "block";
            document.getElementById("step2").style.display = "none";
        }

    }
    async function nextStep() {
        var div = document.getElementById("alert");
        var error = false;
        if (currentStep == 1) {
            document.getElementById("loadingCard").style.display = "block";
            document.getElementById("step1").style.display = "none";

            var name = document.getElementById("name").value;
            var memory = document.getElementById("memory").value;
            var cores = document.getElementById("cores").value;
            var disk = document.getElementById("disk").value;

            var free_memory = null;
            var free_disk = null;
            var free_cpu = null;
            var cpuLimit = null;
            name = name.toString();
            if (name.length == 0) {
                error = true;
                var button = document.createElement("button");
                button.className = "alert alert-danger";
                button.innerHTML = "The name field is empty.";
                button.style = "width:100%";
                div.appendChild(button);

                document.getElementById("loadingCard").style.display = "none";
                document.getElementById("step1").style.display = "block";
            }
            await $.get("/api/user/freememory?userid=<?= $_SESSION["uid"] ?>", function(data) {
                free_memory = JSON.parse(data).freeMemory;
                console.log(free_memory);
                if (memory > free_memory) {
                    error = true;
                    var button = document.createElement("button");
                    button.className = "alert alert-danger";
                    button.innerHTML = "You don't have enough memory, you only have " + free_memory + "MB left. <span data-dismiss='alert' class='pull-right float-right'>✕</span>";
                    button.style = "width:100%";
                    var div = document.getElementById("alert");
                    div.appendChild(button);

                    document.getElementById("loadingCard").style.display = "none";
                    document.getElementById("step1").style.display = "block";
                }
            });
            await $.get("/api/user/freedisk?userid=<?= $_SESSION["uid"] ?>", function(data) {
                free_disk = JSON.parse(data).freeDisk;

                if (disk > free_disk) {
                    error = true;
                    var button = document.createElement("button");
                    button.className = "alert alert-danger";
                    button.innerHTML = "You don't have enough disk, you only have " + free_disk + "MB left. <span data-dismiss='alert' class='pull-right float-right'>✕</span>";
                    button.style = "width:100%";
                    var div = document.getElementById("alert");
                    div.appendChild(button);

                    document.getElementById("loadingCard").style.display = "none";
                    document.getElementById("step1").style.display = "block";
                }
            });
            await $.get("/api/user/freecpu?userid=<?= $_SESSION["uid"] ?>", function(data) {
                free_cpu = JSON.parse(data).freecpu;

                if (cores > free_cpu) {
                    error = true;
                    var button = document.createElement("button");
                    button.className = "alert alert-danger";
                    button.innerHTML = "You don't have enough cpu limit, you only have <?= $userdb->cpu ?>% limit. <span data-dismiss='alert' class='pull-right float-right'>✕</span>";
                    button.style = "width:100%";
                    var div = document.getElementById("alert");
                    div.appendChild(button);

                    document.getElementById("loadingCard").style.display = "none";
                    document.getElementById("step1").style.display = "block";

                }
            });


            if (memory < 256) {
                error = true;
                var button = document.createElement("button");
                button.className = "alert alert-danger";
                button.innerHTML = "Minimum memory is 256MB <span data-dismiss='alert' class='pull-right float-right'>✕</span>";
                button.style = "width:100%";
                var div = document.getElementById("alert");
                div.appendChild(button);

                document.getElementById("loadingCard").style.display = "none";
                document.getElementById("step1").style.display = "block";

            }
            if (disk < 256) {
                error = true;
                var button = document.createElement("button");
                button.className = "alert alert-danger";
                button.innerHTML = "Minimum disk is 256MB <span data-dismiss='alert' class='pull-right float-right'>✕</span>";
                button.style = "width:100%;";
                div.appendChild(button);

                document.getElementById("loadingCard").style.display = "none";
                document.getElementById("step1").style.display = "block";
            }
            if (cores < 0.15) {
                error = true;
                var button = document.createElement("button");
                button.className = "alert alert-danger";
                button.innerHTML = "Minimum cores is 0.15 <span data-dismiss='alert' class='pull-right float-right'>✕</span>";
                button.style = "width:100%;";
                div.appendChild(button);

                document.getElementById("loadingCard").style.display = "none";
                document.getElementById("step1").style.display = "block";
            }


        }
        currentStep++;
        document.getElementById("stepPercentage").innerHTML = (currentStep*33) + "%"
        if (error == true) {
            currentStep = 1;
        }
        var w = parseInt(document.getElementById('stepProgress').style.width);
        if (error !== true) {document.getElementById('stepProgress').style.width= (w + 30) +'%';
            document.getElementById("currentStep").innerHTML = "Step " + currentStep + "/3"; }
        if (currentStep == 2) {
            document.getElementById("loadingCard").style.display = "none";
            document.getElementById("step1").style.display = "none";
            document.getElementById("step2").style.display = "block";
        }
        if (currentStep == 3) {
            document.getElementById("loadingCard").style.display = "none";
            document.getElementById("step2").style.display = "none";
            document.getElementById("step3").style.display = "block";
        }
        if (currentStep == 4) {
            document.getElementById("loadingCard").style.display = "none";
            document.getElementById("step3").style.display = "block";
            document.getElementById("step4").style.display = "none";
        }
    }
</script>
<!-- Only check server slots -->
<script>
    function seeServerSlots() {
        document.getElementById("loadingCard").style.display = "none";
        document.getElementById("step1").style.display = "none";
        document.getElementById("step2").style.display = "block";
        document.getElementById("step2btn").style.display = "none";
        document.getElementById("stepsCard").style.display = "none";
        document.getElementById("selectNodeText").innerHTML = "Available nodes and slots";
        document.getElementById("prevstep2btn").style.display = "none";
        document.getElementById("chksrvslots").style.display = "none";
        var ele = document.getElementsByTagName("Button");
        if (ele.length > 0) {
            for (i = 0; i < ele.length; i++) {
                if (ele[i].type == "button")
                    ele[i].style.display = "none";
            }
        }
    }
</script>
<!-- Node selection -->
<script type="text/javascript">
    var lastbutton = "";

    function selectNode(nodeID, btn) {
        document.getElementById("node").value = nodeID;
        document.getElementById("step2btn").disabled = false;
        if (btn == lastbutton) {

        } else {
            lastbutton.textContent = "SELECT";
            lastbutton.className = "btn btn-primary";
            lastbutton = btn;
        }
        btn.textContent = "SELECTED";
        btn.className = "btn btn-success";
    }
    async function submitForm(egg) {
        var w = parseInt(document.getElementById('stepProgress').style.width);
        document.getElementById('stepProgress').style.width= (w + 100) +'%';
        document.getElementById('progressColor').className = "progress progress-bar-success mt-25";
        document.getElementById("loadingCard").style.display = "block";
        document.getElementById("step3").style.display = "none";
        var node = document.getElementById("node").value;
        var name = document.getElementById("name").value;
        var memory = document.getElementById("memory").value;
        var disk = document.getElementById("disk").value;
        var cores = document.getElementById("cores").value;
        var dbs = document.getElementById("databases").value;
        var ports = document.getElementById("ports").value;
        var backups = document.getElementById("backups").value;
        $.post('/api/user/servers/create',   // url
            {
                "name": name,
                "memory": memory,
                "cores": cores,
                "disk": disk,
                "ports": ports,
                "databases": dbs,
                "backups": backups,
                "location": node,
                "egg": egg

            }, // data to be submit
            function(data, status, jqXHR) {// success callback
                console.log(data)
                var response = JSON.parse(data);
                var status = response.success;
                if (status == true) {
                    window.location.replace("/");
                }
                else {
                    var button = document.createElement("button");
                    button.className = "alert alert-danger";
                    button.innerHTML = "<strong>Error:</strong> " + response['errors']['detail'] + "<span data-dismiss='alert' class='pull-right float-right'>✕</span>";
                    button.style = "width:100%";
                    var div = document.getElementById("alert");
                    div.appendChild(button);
                    document.getElementById("loadingCard").style.display = "none";
                    document.getElementById("step3").style.display = "block";
                }
            });


    }
</script>
</body>
<?php 
include('core/imports/footer.php')
?>

</html>