<?php 
require("../core/require/page.php");
require("../core/require/addons.php");
if (isset($_POST['e_submit'])) {
    $email = mysqli_real_escape_string($cpconn, $_POST['email']);
    $api_key = $getsettingsdb['ptero_apikey'];
    $panel_domain = $getsettingsdb['ptero_url'];
    $user_id = $userdb['panel_id'];
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
        'email' => $email,
        'password' => $userdb['password'],
        'language' => 'en'
    )));

    // Execute the request
    $result = curl_exec($ch);
    curl_close($ch);
    if ($email == "")
    {
        $_SESSION["error"] = "Email can't be blank";
    }
    $cpconn->query("UPDATE `users` SET `email` = '$email' WHERE `users`.`user_id` = ".$_SESSION['uid'].";");
    logClient("[RESET INFO] ".$userdb['username']." changed his email to *$email*!");
    $_SESSION["success"] = "Email has been changed";
    echo '<script>window.location.replace("/user/reset_info");</script>';
}

?>