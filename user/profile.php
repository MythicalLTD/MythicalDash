<?php 
require('../core/require/page.php');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$userdb = $cpconn->query("SELECT * FROM users WHERE user_id = '" . $_SESSION['uid'] . "'")->fetch_object();
$newuser = $cpconn->query("SELECT * FROM users WHERE user_id = '" . $_SESSION['uid'] . "'")->fetch_assoc();
$avatar = $newuser['avatar'];
if ($newuser['staff'] == "1")
{
    $isstaff = "true";
}
else
{
    $isstaff = "false";
}
if (isset($_GET['submit']))
{
  $navatar = mysqli_real_escape_string($cpconn, $_GET['avatar']);
  $nbackground = mysqli_real_escape_string($cpconn, $_GET['background']);
  $nbio = mysqli_real_escape_string($cpconn, $_GET['bio']);
  $nv = mysqli_real_escape_string($cpconn, $_GET['accountstatus']);
  echo $nv;
  $cpconn->query("UPDATE `users` SET `avatar` = '".$navatar."' WHERE `users`.`id` = ".$newuser['id'].";");
  $cpconn->query("UPDATE `users` SET `background` = '".$nbackground."' WHERE `users`.`id` = ".$newuser['id'].";");
  $cpconn->query("UPDATE `users` SET `aboutme` = '".$nbio."' WHERE `users`.`id` = ".$newuser['id'].";");
  $cpconn->query("UPDATE `users` SET `visibility` = '".$nv."' WHERE `users`.`id` = ".$newuser['id'].";");
  echo '<script>window.location.replace("/");</script>';
  $_SESSION['success'] = "Success";
  die;

}
?>
<style>

</style>

    <!-- Header -->
    <div class="header pb-8 pt-5 pt-lg-8 d-flex align-items-center" style="min-height: 600px; background-image: url(<?= $newuser['background'] ?>); background-size: cover; background-position: center top;">
      <!-- Mask -->
      <span class="mask bg-gradient-default opacity-8"></span>
      <!-- Header container -->
      
    </div>
    <!-- Page content -->
    <div class="container-fluid mt--9">
      <div class="row">
        <div class="col-xl-4 order-xl-2 mb-5 mb-xl-0">
          <div class="card card-profile shadow">
            <div class="row justify-content-center">
              <div class="col-lg-3 order-lg-2">
                <div class="card-profile-image">
                  <a href="#">
                    <img src="<?= $avatar ?>" class="rounded-circle">
                  </a>
                </div>
              </div>
            </div>
            <div class="card-header text-center border-0 pt-8 pt-md-4 pb-0 pb-md-4">
              <div class="d-flex justify-content-between">
                <!-- I dont know if i will use this design-->
                <a href="profile" class="btn btn-sm btn-default mr-4">Reload</a>
                <a href="credentials" class="btn btn-sm btn-default float-right">Credentials</a>
              </div>
            </div>
            <div class="card-body pt-0 pt-md-4">
              <div class="text-center">
                <h3>
                  <?= $newuser['username']?><span class="font-weight-light">
                </h3>
                <div class="h5 font-weight-300">
                  <?= $newuser['role']?>
                </div>
                <div class="h5 mt-4">
                  Account Created: <?= $newuser['registered']?>
                </div>
                <hr class="my-4" />
                <p><?= $newuser['aboutme'] ?></p>
              </div>
            </div>
          </div>
        </div>
        <div class="col-xl-8 order-xl-1">
          <div class="card bg-secondary shadow">
            <div class="card-header bg-dark border-0">
              <div class="row align-items-center">
                <div class="col-8">
                  <h3 class="mb-0">Your account:</h3>
                </div>
              </div>
            </div>
            <div class="card-body bg-dark text-white">
              <form method='GET'>
                <h6 class="heading-small mb-4 text-white text-center">User information</h6>
                <div class="pl-lg-4">
                  <div class="row">
                    <div class="col-lg-6">
                      <div class="form-group">
                        <label class="form-control-label text-white" for="input-username">Username</label>
                        <h1 class="form-control"><?= $newuser['username']?></h1>
                      </div>
                    </div>
                    <div class="col-lg-6">
                      <div class="form-group">
                        <label class="form-control-label text-white" for="input-email">Email address</label>
                        <h1 class="form-control"><?= $newuser['email']?></h1>
                      </div>
                    </div>
                  </div>
                </div>
                <hr class="my-4" />
                <h6 class="heading-small text-muted mb-4 text-center text-white">About me</h6>
                <div class="pl-lg-4">
                  <div class="form-group focused">
                    <label for="accountstatus">Account Visibility</label>
                    <select class="form-control" value="<?php $newuser['visibility'] ?>" name="accountstatus">
                    <?php 
                    if ($newuser['visibility'] == "Public")
                    {
                        echo '
                        <option value="Public">Public</option>
                        <option value="Private">Private</option>
                        ';
                    }
                    else 
                    {
                        echo '
                        <option value="Private">Private</option>
                        <option value="Public">Public</option>
                        ';
                    }
                    ?>
                     
                    </select>
                    <br>
                    <label>Profile Picture</label>
                    <input type="text" id="input-last-name" class="form-control" name="avatar" placeholder="https://yourcdn.net/pic/cutecatto.png" value="<?= $newuser['avatar']?>">
                    <br>
                    <label>Profile Background</label>
                    <input type="text" id="input-last-name" class="form-control" name="background" placeholder="https://yourcdn.net/pic/cutecattoback.png" value="<?= $newuser['background']?>">
                    <br>
                    <label>About Me</label>
                    <textarea rows="3" class="form-control" name="bio" placeholder="A few words about you ..."><?= $newuser['aboutme'] ?></textarea>
                  </div>
                </div>
                <hr class="my-4" />
                <button class="btn btn-lg btn-primary" style="width:100%;" name="submit" type="submit">Update!</button>
              </form>
            </div>
          </div>
        </div>
      </div>
      <!-- Footer -->
      <footer class="footer pt-0">
        <div class="row align-items-center justify-content-lg-between">
            <div class="col-lg-6">
                <div class="copyright text-center  text-lg-left  text-white">
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
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/argon/js/jquery.min.js"></script>
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/argon/js/bootstrap.bundle.min.js"></script>
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/argon/js/js.cookie.js"></script>
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/argon/js/jquery.scrollbar.min.js"></script>
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/argon/js/jquery-scrollLock.min.js"></script>
  <!-- Argon JS -->
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/argon/js/argon.js?v=1.2.0"></script>
</body>

</html>
