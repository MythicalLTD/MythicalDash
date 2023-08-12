<?php 
if ($settings['enable_discord_link'] == "true") {
    if (isset($_GET['code'])) {
        $tokenUrl = 'https://discord.com/api/oauth2/token';
        $data = array(
            'client_id' => $settings["discord_clientid"],
            'client_secret' => $settings["discord_clientsecret"],
            'grant_type' => 'authorization_code',
            'code' => $_GET['code'],
            'redirect_uri' => $appURL . '/auth/link/discord',
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
    
        }
    } else {
        $authorizeUrl = 'https://discord.com/api/oauth2/authorize?client_id=' . $settings["discord_clientid"] . '&redirect_uri=' . urlencode($appURL. '/auth/link/discord') . '&response_type=code&scope=' . urlencode('identify guilds email guilds.join');
        header('Location: ' . $authorizeUrl);
    }
} else {
    header("location: /dashboard?e=We are sorry but we don't provide support for discord link right now");
    die();
}

?>