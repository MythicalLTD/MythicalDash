<?php

namespace MythicalDash;
use MythicalDash\Database\Connect;


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
        if (isset($_COOKIE['token'])) {
            $session_id = mysqli_real_escape_string($this->dbConnection,$_COOKIE['token']);
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
        $session_id = mysqli_real_escape_string($this->dbConnection, $_COOKIE["token"]);
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
        $this->deleteCookies();
        header('location: /auth/login?r=' . $fullUrl);
        die();
    }

    private function deleteCookies()
    {
        if (isset($_SERVER['HTTP_COOKIE'])) {
            $cookies = explode(';', $_SERVER['HTTP_COOKIE']);
            foreach ($cookies as $cookie) {
                $parts = explode('=', $cookie);
                $name = trim($parts[0]);
                setcookie($name, '', time() - 1000);
                setcookie($name, '', time() - 1000, '/');
            }
        }
    }
    public function getIP()
    {
        if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
            $_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
            $_SERVER['HTTP_CLIENT_IP'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
        }
        $client = @$_SERVER['HTTP_CLIENT_IP'];
        $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
        $remote = $_SERVER['REMOTE_ADDR'];

        if (filter_var($client, FILTER_VALIDATE_IP)) {
            $ip = $client;
        } elseif (filter_var($forward, FILTER_VALIDATE_IP)) {
            $ip = $forward;
        } else {
            $ip = $remote;
        }

        return $ip;
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