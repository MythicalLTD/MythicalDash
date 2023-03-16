<?php 
require("../core/require/page.php");
require("../core/require/addons.php");
if (isset($_POST['p_submit'])) {
    $password = mysqli_real_escape_string($cpconn, $_POST['password']);

    $api_key = $getsettingsdb['ptero_apikey'];

    // Replace with the actual domain of your Pterodactyl panel
    $panel_domain = $getsettingsdb['ptero_url'];

    // Replace with the actual user ID of the user whose password you want to reset
    $user_id = $userdb['panel_id'];

    // Set up the cURL request
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $panel_domain . "/api/application/users/$user_id");
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
      "Authorization: Bearer $api_key",
      "Content-Type: application/json"
    ));
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(array(
        'username' => $userdb['username'],
        'first_name' =>  $userdb['first_name'],
        'last_name' => $userdb["last_name"],
        'email' => $userdb["email"],
        'password' => $password,
        'language' => 'en'
    )));

    // Execute the request
    $result = curl_exec($ch);
    curl_close($ch);
    if ($password == "")
    {
        $_SESSION["error"] = "Password can't be blank";
    }
    $cpconn->query("UPDATE `users` SET `password` = '$password' WHERE `users`.`user_id` = ".$_SESSION['uid'].";");
    logClient("[RESET INFO] ".$userdb['username']." changed his password!");
    $_SESSION["success"] = "Password has been changed";
    echo '<script>window.location.replace("/user/reset_info");</script>';
}

?>