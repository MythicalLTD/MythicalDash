<?php 
if ($session->getUserInfo("role") == "Administrator") {
    
} else {
    header('location: /e/401');
    die();
}
?>