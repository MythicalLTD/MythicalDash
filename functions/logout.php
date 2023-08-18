<?php 
require('session.php');
$userdb = $conn->query("SELECT * FROM mythicaldash_users WHERE api_key = '".$_COOKIE['token']. "'")->fetch_array();
$username = $userdb['username'];
if (!$username == "")
{
    if (isset($_SERVER['HTTP_COOKIE'])) {
        $cookies = explode(';', $_SERVER['HTTP_COOKIE']);
        foreach($cookies as $cookie) {
            $parts = explode('=', $cookie);
            $name = trim($parts[0]);
            setcookie($name, '', time()-1000);
            setcookie($name, '', time()-1000, '/');
        }
    }
    header('location: /auth/login');
}
?>