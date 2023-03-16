<?php
$length = null;
    if (isset($_GET['length'])) {
        $length = $_GET['length'];

    }
    else {
        $length = 20;
    }
    $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
    $pass = array();
    $alphaLength = strlen($alphabet) - 1;
    for ($i = 0; $i < $length; $i++) {
        $n = rand(0, $alphaLength);
        $pass[] = $alphabet[$n];
    }
    echo implode($pass);

