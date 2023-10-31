<?php
use MythicalDash\ErrorHandler;
try {
    if (isset($_SERVER['HTTP_COOKIE'])) {
        $cookies = explode(';', $_SERVER['HTTP_COOKIE']);
        foreach ($cookies as $cookie) {
            $parts = explode('=', $cookie);
            $name = trim($parts[0]);
            setcookie($name, '', time() - 1000);
            setcookie($name, '', time() - 1000, '/');
        }
    }
    header('location: /auth/login');
} catch (Exception $e) {
    header("location: /auth/login?e=An unexpected error occurred!");
    ErrorHandler::Error("Logout ", $e);
    die();
}
?>