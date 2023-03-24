<?php 

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require("../core/require/page.php");
require("../core/require/addons.php");

if($getperms['cancreate'] == "true")
{

}
else
{
    echo '<script>window.location.replace("/");</script>';
    $_SESSION['error'] = "You are not allowed to create servers";
    die;
}
//$userdb = mysqli_query($cpconn, "SELECT * FROM users where user_id = '". $_SESSION["uid"]. "'")->fetch_object();
$userdb = $cpconn->query("SELECT * FROM users WHERE user_id = '" . mysqli_real_escape_string($cpconn, $_SESSION["uid"]) . "'")->fetch_array();

?>


 <head>
    <title><?= $getsettingsdb['name'] ?> | Edit</title>
    <?php include('../core/imports/header.php');?>
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
                        <li class="nav-item active">
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

                        <li class="nav-item ">
                            <a data-toggle="collapse" href="#earn">
                                <i class="fas fa-coins"></i>
                                <p>Earn</p>
                                <span class="caret"></span>
                            </a>
                            <div class="collapse" id="earn">
                                <ul class="nav nav-collapse">
                                    <li class="nav-item ">
                                        <a href="/earn/afk" class="collapsed">
                                            <p class="">AFK</p>
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
                        <?php if (isset($_SESSION["error"])) {
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
            <div class="alert alert-success text-danger" role="alert">
                <strong>Success!</strong> <?= $_SESSION["success"] ?>
            </div>
            <?php
            unset($_SESSION["success"]);
        }
        ?>                   
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <div class="card-title">Create Server</div>
                                </div>
                                <div class="card-body">
                                    <input type="text" class="form-control" id="name" placeholder="Name">
                                    <br>
                                    <?php
$locations = mysqli_query($cpconn, "SELECT * FROM locations")->fetch_all(MYSQLI_ASSOC);
?>

<label for="location">Location:</label>
<select class="form-control" id="location">
  <?php foreach ($locations as $location): ?>
    <?php
      $serversOnLoc = mysqli_query($cpconn, "SELECT * FROM servers WHERE location='" . $location["id"] . "'")->fetch_all(MYSQLI_ASSOC);
      $serversInQueue = mysqli_query($cpconn, "SELECT * FROM servers_queue WHERE location='" . $location["id"] . "'")->fetch_all(MYSQLI_ASSOC);
      $availableSlots = $location['slots'] - count($serversOnLoc) - count($serversInQueue);
    ?>
    <option value="<?= $location["id"] ?>">
      <?= $location["name"] ?> (<?= $availableSlots ?>/<?= $location["slots"] ?> slots) [<?= $location["status"] === "MAINTENANCE" ? "MAINTENANCE" : ($availableSlots > 0 ? "ONLINE" : "OFFLINE") ?>]
    </option>
  <?php endforeach; ?>
</select>

<?php if (count($locations) == 0): ?>
  <p>No nodes are available at the moment; Server creation might currently be disabled.</p>
<?php endif; ?>



                                    <br>
                                    <label for="location">Egg:</label>
                                    <select class="form-control" id="egg">
                                      <% for (let [name, value] of Object.entries(settings.api.client.eggs)) { %>
                                        <option value="<%= name %>"><%= value.display %></option>
                                      <% } %>
                                    </select>
                                    <br>
                                    <label for="ram">RAM:</label>
                                    <input type="text" class="form-control" id="ram" placeholder="RAM">
                                    <br>
                                    <label for="disk">DISK:</label>
                                    <input type="text" class="form-control" id="disk" placeholder="DISK">
                                    <br>
                                    <label for="cpu">CPU:</label>
                                    <input type="text" class="form-control" id="cpu" placeholder="CPU">
                                    <br>
                                    <button onclick="submitForm()" class="btn btn-primary" style="background-color: #33404D;">Create</button>
                                <br></div>
                                <script>
                                    async function submitForm() {
                                        let name = encodeURIComponent(document.getElementById("name").value);
                                        let egg = encodeURIComponent(document.getElementById("egg").value);
                                        let ram = encodeURIComponent(document.getElementById("ram").value);
                                        let disk = encodeURIComponent(document.getElementById("disk").value);
                                        let cpu = encodeURIComponent(document.getElementById("cpu").value);
                                        let location = encodeURIComponent(document.getElementById("location").value);
                                        document.location.href = `/create?name=${name}&egg=${egg}&ram=${ram}&disk=${disk}&cpu=${cpu}&location=${location}`;
                                    };
                                </script>
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
<?php include('../core/imports/footer.php');?>