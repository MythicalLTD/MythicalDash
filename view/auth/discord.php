<?php
use MythicalDash\ErrorHandler;
use MythicalDash\SettingsManager;
use MythicalDash\SessionManager;

$session = new SessionManager();
use MythicalDash\Database\Connect;

try {
    $conn = new Connect();
    $conn = $conn->connectToDatabase();
    if (SettingsManager::getSetting("enable_discord_link") == "true") {
        if (isset($_GET['code'])) {
            $tokenUrl = 'https://discord.com/api/oauth2/token';
            $data = array(
                'client_id' => SettingsManager::getSetting("discord_clientid"),
                'client_secret' => SettingsManager::getSetting("discord_clientsecret"),
                'grant_type' => 'authorization_code',
                'code' => $_GET['code'],
                'redirect_uri' => $appURL . '/auth/discord',
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
            if (isset($userInfo)) {
                $discord_email = $userInfo['email'];
                $discord_id = $userInfo['id'];
                $query = "SELECT * FROM mythicaldash_users WHERE discord_id = '".mysqli_real_escape_string($conn,$discord_id)."'";
                $result = mysqli_query($conn, $query);
                if ($result) {
                    if (mysqli_num_rows($result) == 1) {
                        $row = mysqli_fetch_assoc($result);
                        $token = $row['api_key'];
                        $email = $row['email'];
                        $banned = $row['banned'];
                        if (!$banned == "") {
                            header('location: /auth/login?e='.$lang['login_banned']);
                            die();
                        } else {
                            $usr_id = $row['api_key'];
                            $url = "http://ipinfo.io/" . $session->getIP() . "/json";
                            $data = json_decode(file_get_contents($url), true);

                            if (isset($data['error']) || $data['org'] == "AS1221 Telstra Pty Ltd") {
                                header('location: /auth/login?e='.$lang['login_please_no_vpn']);
                                die();
                            }
                            $userids = array();
                            $loginlogs = mysqli_query($conn, "SELECT * FROM mythicaldash_login_logs WHERE userkey = '".mysqli_real_escape_string($conn,$usr_id)."'");
                            foreach ($loginlogs as $login) {
                                $ip = $login['ipaddr'];
                                if ($ip == "12.34.56.78") {
                                    continue;
                                }
                                $saio = mysqli_query($conn, "SELECT * FROM mythicaldash_login_logs WHERE ipaddr = '" .mysqli_real_escape_string($conn, $ip) . "'");
                                foreach ($saio as $hello) {
                                    if (in_array($hello['userkey'], $userids)) {
                                        continue;
                                    }
                                    if ($hello['userkey'] == $usr_id) {
                                        continue;
                                    }
                                    array_push($userids, $hello['userkey']);
                                }
                            }
                            if (count($userids) !== 0) {
                                header('location: /auth/login?e='.$lang['login_please_no_alts']);
                                die();
                            }
                            $conn->query("INSERT INTO mythicaldash_login_logs (ipaddr, userkey) VALUES ('" . mysqli_real_escape_string($conn,$session->getIP()) . "', '".mysqli_real_escape_string($conn,$usr_id)."')");

                            $cookie_name = 'token';
                            $cookie_value = $token;
                            setcookie($cookie_name, $cookie_value, time() + (10 * 365 * 24 * 60 * 60), '/');
                            $conn->query("UPDATE `mythicaldash_users` SET `last_ip` = '" . mysqli_real_escape_string($conn,$session->getIP()) . "' WHERE `mythicaldash_users`.`api_key` = '" . mysqli_real_escape_string($conn,$usr_id) . "';");
                            header('location: /dashboard');
                        }
                    } else {
                        header('location: /auth/login?e='.$lang['discord_oath2_no_acc_found']);
                        $conn->close();
                        die();
                    }
                } else {
                    header('location: /auth/login?e='.$lang['discord_oath2_no_acc_found']);
                    $conn->close();
                    die();
                }
                $conn->close();

            } else {
                header('location: /auth/discord');
            }
        } else {
            $authorizeUrl = 'https://discord.com/api/oauth2/authorize?client_id=' . SettingsManager::getSetting("discord_clientid") . '&redirect_uri=' . urlencode($appURL . '/auth/discord') . '&response_type=code&scope=' . urlencode('identify guilds email guilds.join');
            header('Location: ' . $authorizeUrl);
        }
    } else {
        header("location: /auth/login?e=".$lang['discord_oath2_link_failed']);
        die();
    }
} catch (Exception $e) {
    header('location: /auth/login?e='.$lang['discord_oath2_link_failed']);
    ErrorHandler::Error("Discord ",$e);
    die();
}
?>