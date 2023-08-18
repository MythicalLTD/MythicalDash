<?php

function encryptbase($data) {
    return base64_encode($data);
}

function decryptbase($data) {
    return base64_decode($data);
}

?>
