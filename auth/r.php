<?php
session_start();
require("../core/require/sql.php");
if (isset($_SESSION['loggedin'])) {
    header("location: /");
    die();
}
if (!isset($_GET['ref'])) {
    die("Referral is unset.");
} else {
     $referral = $_GET['ref'];
     $a = $cpconn->query("SELECT * FROM referral_codes WHERE referral = '" . mysqli_real_escape_string($cpconn, $referral) . "'");
     if ($a->num_rows == 0) {
         die("Referral doesn't exist.");
     }
     $_SESSION['referral'] = $referral;
     $_SESSION['success'] = "You're nearly done, just sign up to receive 30 coins!.";
     header("location: login");
     die();

}
?>