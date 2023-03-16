<?php 
require("../core/require/page.php");


$usrdb = $cpconn->query("SELECT * FROM users WHERE user_id = '" . mysqli_real_escape_string($cpconn, $_SESSION["uid"]) . "'")->fetch_array();
$perms = $cpconn->query("SELECT * FROM roles WHERE name='".$usrdb['role']."'")->fetch_array();



if ($perms['caneditappsettings'] == "true" || $perms['fullperm'] == "true")
{

}
else
{
  echo '<script>window.location.replace("/");</script>';
  $_SESSION['error'] = "You are not allowed to see this page";
  die;
}

if (isset($_GET['l_submit']))
{
    $mainw_status = $_GET['mainwebstatus'];
    $mainw_url = $_GET['mainurl'];
    $discordinv_status = $_GET['discordstatus'];
    $discordinv_url = $_GET['discordinv'];
    $statuspage_status = $_GET['statusstatus'];
    $statuspage_url = $_GET['statusurl'];
    $phpmyadmin_status = $_GET['phpmyadminstatus'];
    $phpmyadmin_url = $_GET['phpmyadminurl'];
    $terms_of_service = $_GET['termsofservice'];
    $privacy_policy = $_GET['privacypolicy'];
    $cpconn->query("UPDATE `settings` SET `enable_mainwebsite` = '$mainw_status' WHERE `settings`.`id` = 1;");
    $cpconn->query("UPDATE `settings` SET `enable_discord` = '$discordinv_status' WHERE `settings`.`id` = 1;");
    $cpconn->query("UPDATE `settings` SET `enable_status` = '$statuspage_status' WHERE `settings`.`id` = 1;");
    $cpconn->query("UPDATE `settings` SET `enable_phpmyadmin` = '$phpmyadmin_status' WHERE `settings`.`id` = 1;");
    $cpconn->query("UPDATE `settings` SET `website` = '$mainw_url' WHERE `settings`.`id` = 1;");
    $cpconn->query("UPDATE `settings` SET `statuspage` = '$statuspage_url' WHERE `settings`.`id` = 1;");
    $cpconn->query("UPDATE `settings` SET `discordserver` = '$discordinv_url' WHERE `settings`.`id` = 1;");
    $cpconn->query("UPDATE `settings` SET `phpmyadmin` = '$phpmyadmin_url' WHERE `settings`.`id` = 1;");
    $cpconn->query("UPDATE `settings` SET `privacypolicy` = '$privacy_policy' WHERE `settings`.`id` = 1;");
    $cpconn->query("UPDATE `settings` SET `termsofservice` = '$terms_of_service' WHERE `settings`.`id` = 1;");
    echo '<script>window.location.replace("/admin/settings");</script>';
}
if (isset($_GET['a_submit'])) 
{
    $status = $_GET['homeNews_show'];
    $title = $_GET['homeNews_title'];
    $content = $_GET['homeNews_content'];
    $bgimage = $_GET['homeNews_bgimage'];
    $bgcolor = $_GET['homeNews_bgcolor'];
    $buttonLink = $_GET['homeNews_buttonLink'];
    $buttonText = $_GET['homeNews_buttonText'];
    if ($stauts == "false")
    {
      $cpconn->query(" UPDATE `settings` SET `homeNews_show` = '' WHERE `settings`.`id` = 1;");
    }
    else if ($status == "true")
    {
      $cpconn->query(" UPDATE `settings` SET `homeNews_show` = 'true' WHERE `settings`.`id` = 1;");
    }
    else
    {
      $cpconn->query(" UPDATE `settings` SET `homeNews_show` = '' WHERE `settings`.`id` = 1;");
    }
    $cpconn->query("UPDATE `settings` SET `homeNews_title` = '$title' WHERE `settings`.`id` = 1;");
    $cpconn->query("UPDATE `settings` SET `homeNews_content` = '$content' WHERE `settings`.`id` = 1;");
    $cpconn->query("UPDATE `settings` SET `homeNews_bgimage` = '$bgimage' WHERE `settings`.`id` = 1;");
    $cpconn->query("UPDATE `settings` SET `homeNews_bgcolor` = '$bgcolor' WHERE `settings`.`id` = 1;");
    $cpconn->query("UPDATE `settings` SET `homeNews_buttonLink` = '$buttonLink' WHERE `settings`.`id` = 1;");
    $cpconn->query("UPDATE `settings` SET `homeNews_buttonText` = '$buttonText' WHERE `settings`.`id` = 1;");
    echo '<script>window.location.replace("/admin/settings");</script>';
}

if (isset($_GET['submit']))
{
  echo $_GET['color'];
}
?>
<body class="bg-dark text-light">
  <div class="container-fluid text-center bg-dark text-light py-4">
    <h1>Welcome to the settings page</h1>
    <p>Here you can manage the dashboard change settings and update the files and the database!</p>
  </div>
</body>
<main class="content">
  <div class="container-fluid p-0">
      <h1 class="h3 mb-3">Global settings</h1>
      <div id="status-message"></div>
          <div class="card shadow mb-4">
          <div class="card-body">
              <form method="GET">
                  <div class="row g-3">
                      <div class="mb-3 col-md-2">
                          <label class="form-label text-white" for="nameInput">Dashboard Name</label>
                          <input type="text" class="form-control " id="nameInput" name="name" value="<?= $getsettingsdb['name'] ?>" required>
                      </div>
                      <div class="mb-3 col-md-3">
                        <label class="form-label text-white" for="descriptionInput">Dashboard logo</label>
                        <input type="text" class="form-control " id="descriptionInput" name="logo" value="<?= $getsettingsdb['logo'] ?>">        
                      </div>
                      <div class="mb-3 col-md-3">
                        <label class="form-label text-white" for="descriptionInput">Dashboard background</label>
                        <input type="text" class="form-control " id="descriptionInput" name="logo" value="<?= $getsettingsdb['home_background'] ?>">        
                      </div>
                      <div class="form-group col-md-2">
                        <label for="example-color-input" class="form-label">Dashboard Color</label>
                        <input class="form-control" type="color" name="color" value="<?= $getsettingsdb['seo_color']?>" id="example-color-input">
                      </div>
                      <div class="mb-3 col-md-2">
                          <label class="form-label text-white" for="urlInput">Secure connection</label>
                          <select class="form-control" name="proto">
                            <?php 
                            if ($getsettingsdb['proto'] == "https://") 
                            {
                              ?>
                                <option value="true">True (https://)</option>
                                <option value="false">False (http://)</option>
                              <?php
                            }
                            else
                            {
                              ?>
                              <option value="false">False (http://)</option>
                              <option value="true">True (https://)</option>
                              <?php
                            }
                            ?>
                           
                         </select>
                      </div>
                  </div> 
                  <div class="mb-3">
                      <label class="form-label text-white" for="descriptionInput">Seo Description</label>
                      <input type="text" class="form-control " id="descriptionInput" name="description" value="<?= $getsettingsdb['seo_description'] ?>">        
                  </div>
                  <div class="mb-3">
                      <label class="form-label text-white" for="keywordsInput">Meta keywords</label>
                      <input type="text" class="form-control " id="keywordsInput" name="keywords" placeholder="word1, word2" value="<?= $getsettingsdb['seo_keywords']?>" aria-describedby="keywordsInfo">
                      <small id="keywordsInfo" class="form-text">The keywords must be separated with a comma.</small>
                  </div>         
                  <div class="row g-3">
                     <div class="mb-3 col-md-4">
                          <label class="form-label text-white" for="ptero_url">Pterodactyl Url</label>
                          <input type="text" class="form-control" name="ptero_url" value="<?= $getsettingsdb['ptero_url'] ?>">    
                      </div>
                      <div class="mb-3 col-md-4">
                          <label class="form-label text-white" for="ptero_apikey">Pterodactyl Key</label>
                          <input type="password" class="form-control" name="ptero_apikey" value="<?= $getsettingsdb['ptero_apikey'] ?>">    
                      </div>
                          
                      <div class="mb-3 col-md-4">
                          <label class="form-label text-white" for="moneyNameInput">Discord WebHook</label>
                          <input type="password" class="form-control " name="webhook" value="<?= $getsettingsdb['webhook'] ?>">
                      </div>
                  </div>
                          
                 <div class="form-group">
                    <label for="example-color-input" name="maintenance" class="form-label">Maintenance</label>
                      <select class="form-control">
                       <option value="false">False</option>
                       <option value="true">True</option>
                     </select>
                 </div>
                  <button type="submit" name="g_submit" class="btn btn-primary">
                      <i class="bi bi-save"></i> Save
                  </button>
              </form>
          </div>
    </div>
    
</div>
<div class="container-fluid p-0">
      <h1 class="h3 mb-3">Url settings</h1>
      <div id="status-message"></div>
          <div class="card shadow mb-4">
          <div class="card-body">
              <form method="GET">
                    <div class="row g-3">
                      <div class="mb-3 col-md-10">
                          <label class="form-label text-white" for="nameInput">Main Website</label>
                          <input type="text" class="form-control " id="nameInput" name="mainurl" value="<?= $getsettingsdb['website'] ?>" required>
                      </div>
                      <div class="mb-3 col-md-2">
                          <label class="form-label text-white" for="urlInput">Enabled</label>
                          <select class="form-control" name="mainwebstatus">
                            <?php 
                            if ($getsettingsdb['enable_mainwebsite'] == "true") 
                            {
                              ?>
                                <option value="true">True</option>
                                <option value="false">False</option>
                              <?php
                            }
                            else
                            {
                              ?>
                              <option value="false">False</option>
                              <option value="true">True</option>
                              <?php
                            }
                            ?>
                         </select>
                      </div>
                    </div>
                    <div class="row g-3">
                      <div class="mb-3 col-md-10">
                          <label class="form-label text-white" for="nameInput">Discord Invite</label>
                          <input type="text" class="form-control " id="nameInput" name="discordinv" value="<?= $getsettingsdb['discordserver'] ?>" required>
                      </div>
                      <div class="mb-3 col-md-2">
                          <label class="form-label text-white" for="urlInput">Enabled</label>
                          <select class="form-control" name="discordstatus">
                            <?php 
                            if ($getsettingsdb['enable_discord'] == "true") 
                            {
                              ?>
                                <option value="true">True</option>
                                <option value="false">False</option>
                              <?php
                            }
                            else
                            {
                              ?>
                              <option value="false">False</option>
                              <option value="true">True</option>
                              <?php
                            }
                            ?>
                         </select>
                      </div>
                    </div>  
                    <div class="row g-3">
                      <div class="mb-3 col-md-10">
                          <label class="form-label text-white" for="nameInput">Status Page:</label>
                          <input type="text" class="form-control " id="nameInput" name="statusurl" value="<?= $getsettingsdb['statuspage'] ?>" required>
                      </div>
                      <div class="mb-3 col-md-2">
                          <label class="form-label text-white" for="urlInput">Enabled</label>
                          <select class="form-control" name="statusstatus">
                            <?php 
                            if ($getsettingsdb['enable_status'] == "true") 
                            {
                              ?>
                                <option value="true">True</option>
                                <option value="false">False</option>
                              <?php
                            }
                            else
                            {
                              ?>
                              <option value="false">False</option>
                              <option value="true">True</option>
                              <?php
                            }
                            ?>
                         </select>
                      </div>
                    </div>
                    <div class="row g-3">
                      <div class="mb-3 col-md-10">
                          <label class="form-label text-white" for="nameInput">PhpMyAdmin</label>
                          <input type="text" class="form-control " id="nameInput" name="phpmyadminurl" value="<?= $getsettingsdb['phpmyadmin'] ?>" required>
                      </div>
                      <div class="mb-3 col-md-2">
                          <label class="form-label text-white" for="urlInput">Enabled</label>
                          <select class="form-control" name="phpmyadminstatus">
                            <?php 
                            if ($getsettingsdb['enable_phpmyadmin'] == "true") 
                            {
                              ?>
                                <option value="true">True</option>
                                <option value="false">False</option>
                              <?php
                            }
                            else
                            {
                              ?>
                              <option value="false">False</option>
                              <option value="true">True</option>
                              <?php
                            }
                            ?>
                         </select>
                      </div>
                    </div> 
                    <div class="mb-3">
                        <div class="mb-3">
                              <label class="form-label text-white" for="nameInput">Terms of Service</label>
                              <input type="text" class="form-control " id="nameInput" name="termsofservice" value="<?= $getsettingsdb['termsofservice'] ?>" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="mb-3">
                              <label class="form-label text-white" for="nameInput">Privacy Policy</label>
                              <input type="text" class="form-control " id="nameInput" name="privacypolicy" value="<?= $getsettingsdb['privacypolicy'] ?>" required>
                        </div>
                    </div>
                  <button type="submit" name="l_submit" class="btn btn-primary">
                      <i class="bi bi-save"></i> Save
                  </button>
              </form>
          </div>
    </div>
</div>
<div class="container-fluid p-0">
      <h1 class="h3 mb-3">Announcements settings</h1>
      <div id="status-message"></div>
          <div class="card shadow mb-4">
          <div class="card-body">
              <form method="GET">
                    <div class="row g-3">
                      <div class="mb-3 col-md-10">
                          <label class="form-label text-white" for="nameInput">Title</label>
                          <input type="text" class="form-control " id="nameInput" name="homeNews_title" value="<?= $getsettingsdb['homeNews_title'] ?>" required>
                      </div>
                      <div class="mb-3 col-md-2">
                          <label class="form-label text-white" for="urlInput">Enabled</label>
                          <select class="form-control" name="homeNews_show">
                            <?php 
                            if ($getsettingsdb['homeNews_show'] == "true") 
                            {
                              ?>
                                <option value="true">True</option>
                                <option value="false">False</option>
                              <?php
                            }
                            else
                            {
                              ?>
                              <option value="false">False</option>
                              <option value="true">True</option>
                              <?php
                            }
                            ?>
                         </select>
                      </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-white" for="nameInput">Content</label>
                        <input type="text" class="form-control " id="nameInput" name="homeNews_content" value="<?= $getsettingsdb['homeNews_content'] ?>" required>
                    </div>
                    <div class="row g-3">
                      <div class="mb-3 col-md-4">
                        <label class="form-label text-white" for="descriptionInput">Announcement background</label>
                        <input type="text" class="form-control " id="descriptionInput" name="homeNews_bgimage" value="<?= $getsettingsdb['homeNews_bgimage'] ?>">        
                      </div>
                      <div class="form-group col-md-2">
                        <label for="example-color-input" class="form-label">Announcement Color</label>
                        <input class="form-control" type="color" name="homeNews_bgcolor" value="<?= $getsettingsdb['homeNews_bgcolor']?>" id="example-color-input">
                      </div>
                      <div class="mb-3 col-md-3">
                          <label class="form-label text-white" for="moneyNameInput">Announcement button link</label>
                          <input type="text" class="form-control " name="homeNews_buttonLink" value="<?= $getsettingsdb['homeNews_buttonLink'] ?>">
                      </div>
                      <div class="mb-3 col-md-3">
                          <label class="form-label text-white" for="moneyNameInput">Announcement button text</label>
                          <input type="text" class="form-control " name="homeNews_buttonText" value="<?= $getsettingsdb['homeNews_buttonText'] ?>">
                      </div>
                    </div>
                    
                  <button type="submit" name="a_submit" class="btn btn-primary">
                      <i class="bi bi-save"></i> Save
                  </button>
              </form>
          </div>
    </div>
</div>
<div class="container-fluid p-0">
      <h1 class="h3 mb-3">Resources / Coins / Earning settings</h1>
      <div id="status-message"></div>
          <div class="card shadow mb-4">
          <div class="card-body">
              <form method="GET">
              <div class="row g-3">
                      <div class="mb-3 col-md-8">
                          <label class="form-label text-white" for="nameInput">Linkvertise earning</label>
                          <input type="text" class="form-control " id="nameInput" name="linkvertiseapi" value='<?= $getsettingsdb['linkvertise'] ?>' required>
                      </div>
                      <div class="mb-3 col-md-2">
                          <label class="form-label text-white" for="nameInput">Coins per link</label>
                          <input type="number" class="form-control " id="nameInput" name="adfoc_coins" value='<?= $getsettingsdb['adfoc_coins'] ?>' required>
                      </div>
                      <div class="mb-3 col-md-2">
                          <label class="form-label text-white" for="urlInput">Enabled</label>
                          <select class="form-control" name="linkvertise_status">
                            <?php 
                            if ($getsettingsdb['linkvertise_status'] == "true") 
                            {
                              ?>
                                <option value="true">True</option>
                                <option value="false">False</option>
                              <?php
                            }
                            else
                            {
                              ?>
                              <option value="false">False</option>
                              <option value="true">True</option>
                              <?php
                            }
                            ?>
                         </select>
                      </div>
                    </div> 
                    <div class="row g-3">
                      <div class="mb-3 col-md-5">
                          <label class="form-label text-white" for="nameInput">Afk Minutes</label>
                          <input type="number" class="form-control " id="nameInput" name="name" value="<?= $getsettingsdb['afk_min'] ?>" required>
                      </div>
                      <div class="mb-3 col-md-5">
                        <label class="form-label text-white" for="descriptionInput">Coins per minute</label>
                        <input type="number" class="form-control " id="descriptionInput" name="logo" value="<?= $getsettingsdb['afk_coins_per_min'] ?>">        
                      </div>
                      <div class="mb-3 col-md-2">
                          <label class="form-label text-white" for="urlInput">Enabled</label>
                          <select class="form-control" name="proto">
                          <?php 
                            if ($getsettingsdb['enable_afk'] == "true") 
                            {
                              ?>
                                <option value="true">True</option>
                                <option value="false">False</option>
                              <?php
                            }
                            else
                            {
                              ?>
                              <option value="false">False</option>
                              <option value="true">True</option>
                              <?php
                            }
                            ?>
                           
                         </select>
                      </div>
                   </div>  
                   <div class="mb-3">
                          <label class="form-label text-white" for="urlInput">Disable earning</label>
                          <select class="form-control" name="proto">
                          <?php 
                            if ($getsettingsdb['disable_earning'] == "true") 
                            {
                              ?>
                                <option value="true">True</option>
                                <option value="false">False</option>
                              <?php
                            }
                            else
                            {
                              ?>
                              <option value="false">False</option>
                              <option value="true">True</option>
                              <?php
                            }
                            ?>
                           
                         </select>
                      </div> 
                      <div class="row g-3">
                      <div class="mb-3 col-md-2">
                          <label class="form-label text-white" for="nameInput">1 Cpu price</label>
                          <input type="number" class="form-control " id="nameInput" name="cpuprice" value="<?= $getsettingsdb['cpuprice'] ?>" required>
                      </div>
                      <div class="mb-3 col-md-2">
                        <label class="form-label text-white" for="descriptionInput">1GB Ram price</label>
                        <input type="number" class="form-control " id="descriptionInput" name="ramprice" value="<?= $getsettingsdb['ramprice'] ?>">        
                      </div>
                      <div class="mb-3 col-md-2">
                        <label class="form-label text-white" for="descriptionInput">1GB Disk price</label>
                        <input type="number" class="form-control " id="descriptionInput" name="diskprice" value="<?= $getsettingsdb['diskprice'] ?>">        
                      </div>
                      <div class="mb-3 col-md-2">
                          <label class="form-label text-white" for="nameInput">1 Server slots price</label>
                          <input type="number" class="form-control " id="nameInput" name="svslotprice" value="<?= $getsettingsdb['svslotprice'] ?>" required>
                      </div>
                      <div class="mb-3 col-md-2">
                          <label class="form-label text-white" for="nameInput">1 Backup price</label>
                          <input type="number" class="form-control " id="nameInput" name="backupprice" value="<?= $getsettingsdb['backupprice'] ?>" required>
                      </div>
                      <div class="mb-3 col-md-2">
                          <label class="form-label text-white" for="nameInput">1 Database price</label>
                          <input type="number" class="form-control " id="nameInput" name="databaseprice" value="<?= $getsettingsdb['databaseprice'] ?>" required>
                      </div>
                      <div class="mb-3 col-md-2">
                          <label class="form-label text-white" for="nameInput">1 Port price</label>
                          <input type="number" class="form-control " id="nameInput" name="portsprice" value="<?= $getsettingsdb['portsprice'] ?>" required>
                      </div>
                  </div>       
                      <div class="row g-3">
                      <div class="mb-3 col-md-1">
                          <label class="form-label text-white" for="nameInput">Default ram</label>
                          <input type="number" class="form-control " id="nameInput" name="def_memory" value="<?= $getsettingsdb['def_memory'] ?>" required>
                      </div>
                      <div class="mb-3 col-md-1">
                        <label class="form-label text-white" for="descriptionInput">Default disk</label>
                        <input type="number" class="form-control " id="descriptionInput" name="def_disk_space" value="<?= $getsettingsdb['def_disk_space'] ?>">        
                      </div>
                      <div class="mb-3 col-md-1">
                        <label class="form-label text-white" for="descriptionInput">Default CPU</label>
                        <input type="number" class="form-control " id="descriptionInput" name="def_cpu" value="<?= $getsettingsdb['def_cpu'] ?>">        
                      </div>
                      <div class="mb-3 col-md-2">
                          <label class="form-label text-white" for="nameInput">Default ports</label>
                          <input type="number" class="form-control " id="nameInput" name="def_port" value="<?= $getsettingsdb['def_port'] ?>" required>
                      </div>
                      <div class="mb-3 col-md-2">
                          <label class="form-label text-white" for="nameInput">Default databases</label>
                          <input type="number" class="form-control " id="nameInput" name="def_data" value="<?= $getsettingsdb['def_data'] ?>" required>
                      </div>
                      <div class="mb-3 col-md-2">
                          <label class="form-label text-white" for="nameInput">Default backups</label>
                          <input type="number" class="form-control " id="nameInput" name="def_back" value="<?= $getsettingsdb['def_back'] ?>" required>
                      </div>
                      <div class="mb-3 col-md-2">
                          <label class="form-label text-white" for="nameInput">Default server limit</label>
                          <input type="number" class="form-control " id="nameInput" name="def_server_limit" value="<?= $getsettingsdb['def_server_limit'] ?>" required>
                      </div>
                  </div> 
   
                  <button type="submit" name="r_submit" class="btn btn-primary">
                      <i class="bi bi-save"></i> Save
                  </button>
              </form>
          </div>
    </div>
</div>
<script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/argon/js/jquery.min.js"></script>
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/argon/js/bootstrap.bundle.min.js"></script>
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/argon/js/js.cookie.js"></script>
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/argon/js/jquery.scrollbar.min.js"></script>
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/argon/js/jquery-scrollLock.min.js"></script>
  <!-- Argon JS -->
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/argon/js/argon.js?v=1.2.0"></script>
  <!-- Demo JS - remove this in your project -->
  
</body>

</html>