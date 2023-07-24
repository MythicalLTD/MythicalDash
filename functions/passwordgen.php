<?php
function generatePassword($length = 12) {
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_-=+;:,.?";
    $password = "";
    
    $charArrayLength = strlen($chars) - 1;
    
    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[mt_rand(0, $charArrayLength)];
    }
    
    return $password;
}
?>