<?php 
if (isset($_GET['username']))
{
    $username = $_GET['username'];
    $url = "https://robohash.org/".$username.".png?set=set4 ";
    $avatar = file_get_contents($url);
    echo '<img src="data:image/png;base64,' . base64_encode($avatar) . '" alt="Avatar for '.$username.'">';
}
else
{
    echo "Please add ?username=<your name> after the url to gen an avatar";
}
?>