<?php 
require("../../core/require/page.php");

if(isset($_GET['key'])) {
    $key = $_GET['key'];
    $result = mysqli_query($cpconn, "SELECT * FROM adfoc WHERE sckey='$key'");
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        if ($row['claim'] == "true") {
            echo "Error: Key has already been used.";
        } else {
            $usr_coins = $userdb['coins'];
            $newcoins = $usr_coins + $getsettingsdb['adfoc_coins'];
            mysqli_query($cpconn, "UPDATE `users` SET `coins` = '" . $newcoins . "' WHERE `users`.`user_id` = " . $_SESSION["uid"]);
            echo "Success: Key is valid.";
            mysqli_query($cpconn, "DELETE FROM adfoc WHERE sckey='$key'");
            echo '<script>window.location.replace("/earn/select");</script>';
        }
    } else {
        echo "Error: Key not found.";
    }
    mysqli_close($cpconn);
}

?>