<?php 
require('../core/require/page.php');
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
if (isset($_GET['id'])) {

    $userdb = $cpconn->query("SELECT * FROM users WHERE user_id = '" . $_GET['id'] . "'")->fetch_object();
    $newuser = $cpconn->query("SELECT * FROM users WHERE user_id = '" . $_GET['id'] . "'")->fetch_assoc();
    if ($newuser['visibility'] == "Public")
    {

    }
    else
    {
        echo '<script>window.location.replace("/");</script>';
        $_SESSION['error'] = "This profile is not public or it dose not exist";
        die();
    }
    $avatar = $newuser['avatar'];
    if ($newuser['staff'] == "1")
    {
        $isstaff = "true";
    }
    else
    {
        $isstaff = "false";
    }
    
}
else
{
    $_SESSION['error'] = "This profile is not public or it dose not exist";
    echo '<script>window.location.replace("/user/profile");</script>';
    die();
}

?>
<style>
.pbg-primary {
    background-image: url('<?= $newuser['background'] ?>');
    background-size: cover;
    background-repeat: no-repeat;
}

</style>
<body>
    <div class="pbg-primary" style="height: 50vh; position:relative;">
        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%,-50%); display: flex; flex-direction:column; align-items: center;">
            <img src="<?= $newuser['avatar']?>" alt="Profile Picture" class="img-fluid rounded-circle mb-3" style="border: 2px solid white;">
            <h3 style="color:white;"><?= $newuser['username']?></h3>
            <p style="color:white;"><?= $newuser['role']?></p><center>
            <p style="color:white; text-center margin-top:10px;"><?= $newuser['aboutme'] ?></p></center>
        </div>
    </div>
    &nbsp;
    <div class="container text-center">
        <?php 
        if ($newuser['banned'] == "0")
        {
            ?>
            <p>Email: <code><?= $newuser['email'] ?></code></p>
            <p>Coins: <code><?= $newuser['coins']?></code></p>
            <p>Minutes AFK: <code><?= $newuser['minutes_idle'] ?></code></p>
            <?php
        }
        else
        {
            $banr = $newuser['banned_reason'];
            if ($banr == "")
            {
                ?>
                <p>This user got banned for: <code>No reason has set</code></p>
            <?php
            }
            else
            {
                ?>
                <p>This user got banned for: <code><?= $newuser['banned_reason'] ?></code></p>
            <?php
            }
            
        }
        ?>
        
        
    </div>
    <div class="text-center">
            <a href='/users/giftcoins?id=<?= $newuser['user_id'] ?>' class='btn btn-primary'>Gift Coins (SOON)</a>
            
        </div>
</body>
<script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/argon/js/jquery.min.js"></script>
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/argon/js/bootstrap.bundle.min.js"></script>
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/argon/js/js.cookie.js"></script>
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/argon/js/jquery.scrollbar.min.js"></script>
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/argon/js/jquery-scrollLock.min.js"></script>
  <!-- Argon JS -->
  <script src="<?= $getsettingsdb["proto"] . $_SERVER['SERVER_NAME']?>/assets/argon/js/argon.js?v=1.2.0"></script>