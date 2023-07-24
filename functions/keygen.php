<?php 
function generate_key($email,$password) {
    $timestamp = time();
    $formatted_timestamp = date("HisdmY", $timestamp);
    $encoded_timestamp = base64_encode($formatted_timestamp);
    $key = base64_encode("mythicaldash_".$encoded_timestamp.base64_encode($email).password_hash(base64_encode($password),PASSWORD_DEFAULT).generatePassword(12));
    return $key;
}
?>