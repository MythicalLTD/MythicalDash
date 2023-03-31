<?php 
require("../core/require/page.php");
require("../core/require/addons.php");
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>
<div class="header bg-primary pb-6">
      <div class="container-fluid">
        <div class="header-body">
          <div class="row align-items-center py-4">
            <div class="col-lg-6 col-7">
              <h6 class="h2 text-white d-inline-block mb-0">Change account info</h6>
              <nav aria-label="breadcrumb" class="d-none d-md-inline-block ml-md-4">
                <ol class="breadcrumb breadcrumb-links breadcrumb-dark">
                  <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
                  <li class="breadcrumb-item"><a href="/user/profile">User</a></li>
                  <li class="breadcrumb-item active" aria-current="page">Edit user</li>
                </ol>
              </nav>
            </div>
          </div>
        </div>
      </div>
    </div>
<input id="node" name="node" type="hidden" value="">

<div class="container-fluid mt--6">
<?php
        if (isset($_SESSION["error"])) {
            ?>
            <div class="alert alert-danger" role="alert">
              <strong>Error!</strong> <?= $_SESSION["error"] ?>
            </div>
            <?php
            unset($_SESSION["error"]);
        }
        if (isset($_SESSION["success"])) {
          ?>
          <div class="alert alert-success" role="alert">
            <strong>Success!</strong> <?= $_SESSION["success"] ?>
          </div>
          <?php
          unset($_SESSION["success"]);
      }
        ?>
<div class="app-content content">
    <div class="content-wrapper">
        <div class="content-header row">
        </div>
        
        <div class="content-body">
            <section id="dashboard-analytics ">
                <div class="row">
                    <div class="col-6">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="mb-0 text-center">Change password for "<?= htmlspecialchars($userdb['username']) ?>"</h4>
                            </div>
                            <div class="card-body">
                                <form action="reset-password" method="post">
                                    <label for="txtpasswrd">Password:</label>
                                    <div class="form-group">
                                      <div class="input-group input-group-alternative">
                                        <div class="input-group-prepend">
                                          <span class="input-group-text bg-white"><i class="ni ni-lock-circle-open"></i></span>
                                        </div>
                                        <input class="form-control bg-white" required name="password" placeholder="Password" value="<?= $userdb['password'] ?>"  id="password" type="password">
                                        <div class="input-group-append">
                                          <span class="input-group-text bg-secondary" id="show-password" style="cursor: pointer;">
                                            <i class="fa fa-eye"></i>
                                          </span>
                                        </div>
                                      </div>
                                    </div>
                                    <br>
                                    <button class="btn btn-lg btn-primary" style="width:100%;" name="p_submit" type="submit">Update</button>
                                </form>
                            </div>
                        </div>
                        
                    </div>
                    <div class="col-6">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="mb-0 text-center">Change email for "<?= htmlspecialchars($userdb['username']) ?>"</h4>
                            </div>
                            <div class="card-body">
                                <form action="reset-email" method="post">
                                    <label for="email">Email:</label>
                                    <div class="form-group">
                                      <div class="input-group input-group-alternative">
                                        <div class="input-group-prepend">
                                          <span class="input-group-text bg-white"><i class="fa fa-envelope"></i></span>
                                        </div>
                                        <input class="form-control bg-white" required name="email" placeholder="Email" value="<?= $userdb['email'] ?>" id="email" type="email">
                                      </div>
                                    </div>
                                    <br>
                                    <button class="btn btn-lg btn-primary" style="width:100%;" name="e_submit" type="submit">Update</button>
                                </form>
                            </div>
                        </div>
                        
                    </div>
                </div>
            </div>
            </section>
        </div>
        <div class="col-md-10">
    </div>
</div>

<!-- END: Content-->

<div class="sidenav-overlay"></div>
<div class="drag-target"></div>

<!-- BEGIN: Footer-->
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
    <script>
    // Get the icon element
    var icon = document.getElementById("show-password");
    // Get the password input
    var passwordInput = document.getElementById("password");

    // Add a click event listener to the icon
    icon.addEventListener("click", function() {
        // If the password input type is "password"
        if (passwordInput.getAttribute("type") === "password") {
            // Change the input type to "text"
            passwordInput.setAttribute("type", "text");
           
        } else {
            // Otherwise, change the input type to "password"
            passwordInput.setAttribute("type", "password");
           
        }
    });
  </script>
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/argon/js/jquery.min.js"></script>
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/argon/js/bootstrap.bundle.min.js"></script>
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/argon/js/js.cookie.js"></script>
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/argon/js/jquery.scrollbar.min.js"></script>
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/argon/js/jquery-scrollLock.min.js"></script>
  <!-- Argon JS -->
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/argon/js/argon.js?v=1.2.0"></script>
</body>

</html>
