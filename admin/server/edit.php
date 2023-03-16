<?php 
require("../../core/require/page.php");
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$usrdb = $cpconn->query("SELECT * FROM users WHERE user_id = '" . mysqli_real_escape_string($cpconn, $_SESSION["uid"]) . "'")->fetch_array();
//Looks into perms if users has acces to see this page!
$perms = $cpconn->query("SELECT * FROM roles WHERE name='".$usrdb['role']."'")->fetch_array();

if ($perms['caneditservers'] == "true" || $perms['fullperm'] == "true")
{
  //Do nothing
}
else
{
  echo '<script>window.location.replace("/");</script>';
  die;
}
$svid = $_GET['id'];
$svinfo = $cpconn->query("SELECT * FROM servers WHERE id='".$svid."'")->fetch_array();
$currentName = $svinfo['name'];

if (isset($_GET['submit'])) {
    
}



?>
<div class="header bg-primary pb-6">
    <div class="container-fluid">
        <div class="header-body">
            <div class="row align-items-center py-4">
                <div class="col-lg-6 col-7">
                    <h6 class="h2 text-white d-inline-block mb-0">Manage Server</h6>
                    <nav aria-label="breadcrumb" class="d-none d-md-inline-block ml-md-4">
                        <ol class="breadcrumb breadcrumb-links breadcrumb-dark">
                            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
                            <li class="breadcrumb-item active" aria-current="page">Admin / Servers</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>
<input id="node" name="node" type="hidden" value="">

<div class="container-fluid mt--6">
<div class="app-content content">
    <div class="content-overlay"></div>
    <div class="header-navbar-shadow"></div>
    <div class="content-wrapper">
        <div class="content-header row">
        </div>
        <div class="content-body">
            <section id="dashboard-analytics">
                <div class="row">

                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="mb-0 text-center">Set resources for server "<?= htmlspecialchars($currentName) ?>"</h4>
                                <h5 class="mb-0 text-center">Hi these resources shall be in mb binary, you can convert it <a href="https://www.gbmb.org/gb-to-mb">here</a></h5>
                            </div>
                            <div class="card-body">
                                <form action="" method="GET">
                                    <label for="svid">Server ID:</label>
                                    <input type="number" class="form-control" name="svid" value="<?= $svid ?>" required>
                                    <br>
                                    <label for="memory">Memory:</label>
                                    <input type="number" min="256" class="form-control" name="memory" value="2048" required>
                                    <br>
                                    <label for="cores">CPU limit (%): </label>
                                    <input type="number" min="10%" step="any" class="form-control" name="cores" value="100" required>
                                    <br>
                                    <label for="disk">Disk:</label>
                                    <input type="number" min="256" step="any" class="form-control" name="disk" value="2048" required>
                                    <br>
                                    <label for="ports">Ports:</label>
                                    <input type="number" class="form-control" name="ports" value="1" required>
                                    <br>
                                    <label for="databases">Databases:</label>
                                    <input type="number" class="form-control" name="databases" value="1" required>
                                    <br>
                                    <label for="backups">Backups:</label>
                                    <input type="number" class="form-control" name="backups" value="1" required>
                                    <br>
                                    <button class="btn btn-lg btn-primary" style="width:100%;" name="submit" value="true" type="submit">Change Server</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
            
        </div>
                </div>
            </section>


        </div>
    </div>
</div>

<div class="sidenav-overlay"></div>
<div class="drag-target"></div>

<footer class="footer pt-0">
        <div class="row align-items-center justify-content-lg-between">
            <div class="col-lg-6">
                <div class="copyright text-center  text-lg-left  text-muted">
                    Copyright &copy;2022-2023 <a href="https://github.com/MythicalLTD/MythicalDash" class="font-weight-bold ml-1" target="_blank">ShadowDash x MythicalDash </a> - Theme by <a href="https://creativetim.com" target="_blank">Creative Tim</a>
                </div>
            </div>
            <div class="col-lg-6">
                <ul class="nav nav-footer justify-content-center justify-content-lg-end">
                    <li class="nav-item">
                        <a href="<?= $getsettingsdb["website"] ?>" class="nav-link" target="_blank"> Website</a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= $getsettingsdb["statuspage"] ?>" class="nav-link" target="_blank">Uptime / Status</a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= $getsettingsdb["privacypolicy"] ?>" class="nav-link" target="_blank">Privacy policy</a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= $getsettingsdb["termsofservice"] ?>" class="nav-link" target="_blank">Terms of service</a>
                    </li>
                </ul>
            </div>
        </div>
    </footer>
<script src="/app-assets/vendors/js/vendors.min.js"></script>
<script src="/app-assets/js/core/app-menu.js"></script>
<script src="/app-assets/js/core/app.js"></script>
<script src="/app-assets/js/scripts/components.js"></script>
<script src="/app-assets/vendors/js/forms/select/select2.full.min.js"></script>
<script src="/app-assets/js/scripts/forms/select/form-select2.js"></script>
</body>

</html>



