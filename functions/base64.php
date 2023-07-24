<?php

function encrypt($data) {
    return base64_encode($data);
}

function decrypt($data) {
    return base64_decode($data);
}

?>
