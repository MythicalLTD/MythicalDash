<?php
require("../../../core/require/page.php");
if ($getsettingsdb['disable_earning'] == "true")
{
    echo '<script>window.location.replace("/");</script>';
    $_SESSION['error'] = "You are not allowed to earn coins!";
    die;
}

if($getperms['canafk'] == "true")
{

}
else
{
    echo '<script>window.location.replace("/");</script>';
    $_SESSION['error'] = "You are not allowed to be afk";
    die;
}
if ($getsettingsdb['enable_afk'] == "true")
{

}
else
{
    echo '<script>window.location.replace("/");</script>';
    $_SESSION['error'] = "You are not allowed to be afk";
    die;
}
$userdb = $cpconn->query("SELECT * FROM users WHERE user_id = '" . mysqli_real_escape_string($cpconn, $_SESSION["uid"]) . "'")->fetch_array();

$usrid = $userdb['id'];
$coins = $userdb['coins'];
$idlemins = $userdb['minutes_idle'];
$lastseen = $userdb['last_seen'];
$username = $userdb['username'];

function minutesToSeconds($minutes) {
    return $minutes * 60;
}
$minutes = $getsettingsdb['afk_min'];
$seconds = minutesToSeconds($minutes);

$idlecheck = $lastseen + $seconds;

$currenttime = new DateTime();
$currenttimestamp = $currenttime->getTimestamp();


if ($idlecheck <= $currenttimestamp)
{
    $data1 = $coins + $getsettingsdb['afk_coins_per_min'];
    $data2 = $idlemins + $getsettingsdb['afk_min'];
    try {  
        $cpconn->query("UPDATE `users` SET `coins` = '$data1' WHERE `users`.`id` = $usrid;");
        $cpconn->query("UPDATE `users` SET `minutes_idle` = '$data2' WHERE `users`.`id` = $usrid;");
        $cpconn->query("UPDATE `users` SET `last_seen` = '$currenttimestamp' WHERE `users`.`id` = $usrid;");
        echo '<script>window.location.replace("/earn/afk");</script>';
    }   
            //catch block  
    catch (Exception $e) {  
        //code to print exception caught in the block  
        echo $e;
    }  

}
else
{
    echo "Dont try to abuse to get coins";
    echo '<script>window.location.replace("/");</script>';
}
?>