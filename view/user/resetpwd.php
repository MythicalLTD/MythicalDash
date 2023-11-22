<?php
use MythicalDash\Encryption;

include(__DIR__ . '/../requirements/page.php');
use MythicalDash\SettingsManager;

if (isset($_COOKIE['token']) && !$_COOKIE['token'] == "") {
    $user_query = "SELECT * FROM mythicaldash_users WHERE api_key = ?";
    $stmt = mysqli_prepare($conn, $user_query);
    mysqli_stmt_bind_param($stmt, "s", $_COOKIE['token']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if (mysqli_num_rows($result) > 0) {
        $user_info = $conn->query("SELECT * FROM mythicaldash_users WHERE api_key = '" . mysqli_real_escape_string($conn, $_COOKIE['token']) . "'")->fetch_array();
        $upassword = mysqli_real_escape_string($conn, $_GET['pwd']);
        $pwd = password_hash($upassword, PASSWORD_BCRYPT);
        $conn->query("UPDATE `mythicaldash_users` SET `password` = '" . mysqli_real_escape_string($conn, $pwd) . "' WHERE `mythicaldash_users`.`api_key` = '" . mysqli_real_escape_string($conn, $_COOKIE['token']) . "';");
        $conn->close();
        $api_url = SettingsManager::getSetting("PterodactylURL") . "/api/application/users/" . $user_info['panel_id'] . "";
        $data = [
            "email" => $user_info['email'],
            "username" => $user_info['username'],
            "first_name" => Encryption::decrypt($user_info['first_name'], $ekey),
            "last_name" => Encryption::decrypt($user_info['last_name'], $ekey),
            "language" => "en",
            "password" => $_GET['pwd']
        ];

        $data_json = json_encode($data);

        $ch = curl_init($api_url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Accept: application/json",
            "Content-Type: application/json",
            "Authorization: Bearer " . SettingsManager::getSetting("PterodactylAPIKey")
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($http_code == 200) {
            $api_response = json_decode($response, true);
            header('location: /user/edit?s=We updated the user settings in the database');
            curl_close($ch);
            die();
        } else {
            header("location: /user/edit?e=Failed to update the user settings inside the panel");
            curl_close($ch);
            die();
        }
    } else {
        header('location: /user/edit?e=Can`t find this user in the database');
        die();
    }
} else {
    header('location: /user/profile');
    die();
}
?>