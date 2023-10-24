<?php
include(__DIR__ . '/../requirements/page.php');

if (isset($_GET['userid']) && isset($_GET['coins']) && is_numeric($_GET['coins'])) {
    $userid = mysqli_real_escape_string($conn, $_GET['userid']);
    $coins = intval($_GET['coins']);

    $userQuery = "SELECT * FROM mythicaldash_users WHERE `id` = '$userid'";
    $userResult = mysqli_query($conn, $userQuery);

    if (mysqli_num_rows($userResult) > 0) {
        if ($userdb['id'] == $_GET['userid']) {
            header("location: /user/profile?e=You can't send coins to yourself!&id=" . $_GET['userid']);
            die();
        }
        if ($coins <= 0) {
            header("location: /user/profile?e=Please enter a valid number of coins to send&id=" . $_GET['userid']);
            die();
        }
        if ($coins <= $userdb['coins']) {
            $giftUserQuery = "SELECT * FROM mythicaldash_users WHERE id = '$userid'";
            $giftUserResult = mysqli_query($conn, $giftUserQuery);
            $giftUser = mysqli_fetch_assoc($giftUserResult);

            $u_new_coins = $userdb['coins'] - $coins;
            $g_new_coins = $giftUser['coins'] + $coins;

            $updateGiftUserQuery = "UPDATE `mythicaldash_users` SET `coins` = '$g_new_coins' WHERE `id` = {$giftUser['id']}";
            $updateSenderQuery = "UPDATE `mythicaldash_users` SET `coins` = '$u_new_coins' WHERE `id` = {$userdb['id']}";

            mysqli_query($conn, $updateSenderQuery);
            mysqli_query($conn, $updateGiftUserQuery);

            header("location: /user/profile?id={$giftUser['id']}&s=Sent $coins coin(s) to {$giftUser['username']}");
            die();
        } else {
            header("location: /user/profile?e=You don't have enough coins to send that many&id=$userid");
            die();
        }
    } else {
        header("location: /dashboard?e=We can't find this user in our database");
        die();
    }
} else {
    header("location: /dashboard?e=Invalid input");
    die();
}
?>