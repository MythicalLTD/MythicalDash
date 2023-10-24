<?php
namespace MythicalDash\CloudFlare;

class Captcha
{
    public static function validate_captcha($cf_turnstile_response, $cf_connecting_ip, $cf_secret_key)
    {
        $data = array(
            "secret" => $cf_secret_key,
            "response" => $cf_turnstile_response,
            "remoteip" => $cf_connecting_ip
        );

        $url = "https://challenges.cloudflare.com/turnstile/v0/siteverify";

        $options = array(
            "http" => array(
                "header" => "Content-Type: application/x-www-form-urlencoded\r\n",
                "method" => "POST",
                "content" => http_build_query($data)
            )
        );
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);

        if ($result == false) {
            return false;
        }

        $result = json_decode($result, true);

        return $result["success"];
    }

}
?>