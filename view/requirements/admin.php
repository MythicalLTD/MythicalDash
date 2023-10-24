<?php 
if ($session->getUserInfo("role") == "User") {
    header('location: /e/401');
}
?>