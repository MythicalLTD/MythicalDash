<?php 
function generate_key($email,$password) {
    $timestamp = time();
    $formatted_timestamp = date("HisdmY", $timestamp);
    $encoded_timestamp = base64_encode($formatted_timestamp);
    $key = base64_encode("mythicaldash_c_".$encoded_timestamp.base64_encode($email).password_hash(base64_encode($password),PASSWORD_DEFAULT).generatePassword(12));
    return $key;
}
function generate_keynoinfo() {
    $timestamp = time();
    $formatted_timestamp = date("HisdmY", $timestamp);
    $encoded_timestamp = base64_encode($formatted_timestamp);
    $key = base64_encode("mythicaldash_".generatePassword(12).$encoded_timestamp.generatePassword(12));
    return $key;
}
function generateticket_key($tickeduuid) {
    $timestamp = time();
    $formatted_timestamp = date("HisdmY", $timestamp);
    $encoded_timestamp = base64_encode($formatted_timestamp);
    $key = base64_encode("mythicaldash_ticket_".$encoded_timestamp.base64_encode($tickeduuid).generatePassword(12));
    return $key;
}

?>