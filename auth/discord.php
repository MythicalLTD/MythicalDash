<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require("../core/require/sql.php");
require("../core/require/addons.php");
require("../core/require/config.php");
$getsettingsdb = $cpconn->query("SELECT * FROM settings")->fetch_array();
if (!$cpconn->ping()) {
    $_SESSION['error'] = "There was an error communicating with MYSQL";
    echo '<script>window.location.replace("/auth/login");</script>';
    die();
    
}

if (isset($_GET['code'])) {
    $tokenUrl = 'https://discord.com/api/oauth2/token';

    $data = array(
        'client_id' => $_CONFIG["dc_clientid"],
        'client_secret' => $_CONFIG["dc_clientsecret"],
        'grant_type' => 'authorization_code',
        'code' => $_GET['code'],
        'redirect_uri' => $getsettingsdb["proto"] . $_SERVER['SERVER_NAME'] . '/auth/discord',
        'scope' => 'identify guilds email'
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

    if (isset($userInfo)) {
        $username = $userInfo['username'];
        $email = $userInfo['email'];
        $avatarUrl = "https://cdn.discordapp.com/avatars/{$userInfo['id']}/{$userInfo['avatar']}.png";
    
        echo "Welcome, $username! Your email is $email.<br>";
        echo "Your avatar:<br><img src='$avatarUrl'>";
    }
    

} else {
    $authorizeUrl = 'https://discord.com/api/oauth2/authorize?client_id=' . $_CONFIG["dc_clientid"] . '&redirect_uri=' . urlencode($getsettingsdb["proto"] . $_SERVER['SERVER_NAME'] . '/auth/discord') . '&response_type=code&scope=' . urlencode('identify guilds email');
    header('Location: ' . $authorizeUrl);
}
?>
