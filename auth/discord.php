<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require("../core/require/sql.php");
require("../core/require/addons.php");
require("../core/require/config.php");

if (!$cpconn->ping()) {
    $_SESSION['error'] = "There was an error communicating with MYSQL";
    echo '<script>window.location.replace("/auth/login");</script>';
    die();
    
}
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
if (isset($_GET['code'])) {
    $tokenUrl = 'https://discord.com/api/oauth2/token';

    $data = array(
        'client_id' => $_CONFIG["dc_clientid"],
        'client_secret' => $_CONFIG["dc_clientsecret"],
        'grant_type' => 'authorization_code',
        'code' => $_GET['code'],
        'redirect_uri' => $getsettingsdb["proto"] . $_SERVER['SERVER_NAME'] . '/auth/discord',
        'scope' => 'identify guilds email guilds.join'
    );

    $options = array(
        'http' => array(
            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query($data),
        ),
    );

    $context = stream_context_create($options);
    $result = file_get_contents($tokenUrl, false, $context);

    $accessToken = json_decode($result, true)['access_token'];

    $userUrl = 'https://discord.com/api/users/@me';

    $options = array(
        'http' => array(
            'header' => "Authorization: Bearer $accessToken\r\n",
            'method' => 'GET',
        ),
    );

    $context = stream_context_create($options);
    $result = file_get_contents($userUrl, false, $context);
    
    $userInfo = json_decode($result, true);
    echo $result;
    if (isset($userInfo)) {
        $username = $userInfo['username'];
        $email = $userInfo['email'];
        $avatarUrl = "https://cdn.discordapp.com/avatars/{$userInfo['id']}/{$userInfo['avatar']}.png";
        $d_id = $userInfo['id'];
        $background = $userInfo['banner'];
        if (!$background == null)
        {
            $cpconn->query("UPDATE `users` SET `background` = '".$background."' WHERE `users`.`session_id` = '".$_COOKIE['remember_token']."';");
        }
        $cpconn->query("UPDATE `users` SET `discord_id` = '".$d_id."' WHERE `users`.`session_id` = '".$_COOKIE['remember_token']."';");
        $cpconn->query("UPDATE `users` SET `discord_email` = '".$email."' WHERE `users`.`session_id` = '".$_COOKIE['remember_token']."';");
        $cpconn->query("UPDATE `users` SET `avatar` = '".$avatarUrl."' WHERE `users`.`session_id` = '".$_COOKIE['remember_token']."';");
        $cpconn->query("UPDATE `users` SET `discord_username` = '".$username."' WHERE `users`.`session_id` = '".$_COOKIE['remember_token']."';");
        header('Location: /');
    }
    

} else {
    $authorizeUrl = 'https://discord.com/api/oauth2/authorize?client_id=' . $_CONFIG["dc_clientid"] . '&redirect_uri=' . urlencode($getsettingsdb["proto"] . $_SERVER['SERVER_NAME'] . '/auth/discord') . '&response_type=code&scope=' . urlencode('identify guilds email guilds.join');
    header('Location: ' . $authorizeUrl);
}
?>
