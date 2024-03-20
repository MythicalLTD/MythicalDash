<?php

namespace MythicalDash;
use MythicalDash\Database\Connect;
use MythicalSystems\User\Cookies as Cookie; 
use MythicalSystems\CloudFlare\CloudFlare;

class SessionManager
{
    private $dbConnection;
    private $encryption; 
    public function __construct()
    {
        $dbConnector = new Connect();
        $this->dbConnection = $dbConnector->connectToDatabase();
        $this->encryption = new Encryption(); 
    }

    public function authenticateUser()
    {
        if (!Cookie::getCookie('token') == null) {
            $session_id = mysqli_real_escape_string($this->dbConnection,Cookie::getCookie('token'));
            $query = "SELECT * FROM mythicaldash_users WHERE api_key='" . $session_id . "'";
            $result = mysqli_query($this->dbConnection, $query);

            if (mysqli_num_rows($result) > 0) {
                session_start();
                $_SESSION["token"] = $session_id;
                $_SESSION['loggedin'] = true;
            } else {
                $this->redirectToLogin($this->getFullUrl());
            }
        } else {
            $this->redirectToLogin($this->getFullUrl());
        }
    }

    public function getUserInfo($info)
    {
        $session_id = mysqli_real_escape_string($this->dbConnection, Cookie::getCookie('token'));
        $safeInfo = $this->dbConnection->real_escape_string($info);
        $query = "SELECT `$safeInfo` FROM mythicaldash_users WHERE api_key='$session_id' LIMIT 1";
        $result = $this->dbConnection->query($query);

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row[$info];
        } else {
            return null; // User or data not found
        }
    }

    private function redirectToLogin($fullUrl)
    {
        Cookie::deleteAllCookies();
        header('location: /auth/login?r=' . $fullUrl);
        die();
    }

    public function getIP()
    {
        return mysqli_real_escape_string($this->dbConnection, CloudFlare::getRealUserIP());
    }
    private function getFullUrl()
    {
        $fullUrl = "http";
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) {
            $fullUrl .= "s";
        }
        $fullUrl .= "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        return $fullUrl;
    }
}
?>