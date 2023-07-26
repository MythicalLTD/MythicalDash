<?php 
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['code'])) {
        if (!$_GET['code'] == "") {
            
        }
        else{
            header('location: /auth/forgot-password?e=The code for resetting your password is wrong. Please try again.');
            exit();
        }
    } else {
        header('location: /auth/forgot-password?e=The code for resetting your password is wrong. Please try again.');
        exit();
    }
}
?>