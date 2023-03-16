<?php 
/**
 * @var array $_ 
 */
#$_ [] = array("name" => "", "path" => "");


//require_once 'vendor/autoload.php';
//use PHPMailer\PHPMailer\PHPMailer;
//use PHPMailer\PHPMailer\Exception;

function logClient($message) {
    include("config.php");
    $cpconn = new mysqli($_CONFIG["db_host"], $_CONFIG["db_username"], $_CONFIG["db_password"], $_CONFIG["db_name"] );
    $getsettingsdb = $cpconn->query("SELECT * FROM settings")->fetch_array();
    $url = $getsettingsdb['webhook'];
    $headers = [ 'Content-Type: application/json; charset=utf-8' ];
    $POST = [ 'username' => 'Client logs', 'content' => $message ];
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($POST));
    $response   = curl_exec($ch);
}

//function sendEmail($toEmail, $subject, $message) {
//    require("sql.php");
//    $getsettingsdb = $cpconn->query("SELECT * FROM settings")->fetch_array();
//    $smtpHost = $getsettingsdb['smtpHost'];
//    $smtpPort = $getsettingsdb['smtpPort'];
//    $smtpSecure = $getsettingsdb['smtpSecure'];
//    $smtpUsername = $getsettingsdb['smtpUsername'];
//    $smtpPassword = $getsettingsdb['smtpPassword'];
//    $fromEmail = $getsettingsdb['fromEmail'];
//
//    $mail = new PHPMailer(true);
//
//    try {
//        $mail->isSMTP();
//        $mail->Host = $smtpHost;
//        $mail->Port = $smtpPort;
//        $mail->SMTPAuth = true;
//        $mail->Username = $smtpUsername;
//        $mail->Password = $smtpPassword;
//        $mail->SMTPSecure = $smtpSecure;
//        $mail->setFrom($fromEmail);
//        $mail->addAddress($toEmail);
//        $mail->isHTML(false);
//        $mail->Subject = $subject;
//        $mail->Body = $message;
//        $mail->send();
//        return true;
//    } catch (Exception $e) {
//        return $e->getMessage(); 
//    }
//}

?>