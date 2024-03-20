<?php
/* 
Cautionary Note: Exercise discretion when handling this file, as any modifications made without a comprehensive understanding of its functionality could lead to unintended consequences.

It's crucial to comprehend that this file plays a pivotal role in ensuring the security and confidentiality of user settings stored in the database. 
The mechanism it employs is akin to end-to-end encryption, providing an unparalleled level of protection.
The data is transformed into an unreadable format using a specific key, meticulously configured within the confines of the 'config.yml' file.

It is of paramount importance to underscore that altering the encryption key without a profound understanding of the implications can result in detrimental outcomes. 
Specifically, this action has the potential to disrupt the encryption and decryption processes. 
In such a scenario, the historical data, which was initially safeguarded using the previous key, would become irrecoverable.

In light of these intricacies, it is advised that only individuals with an extensive grasp of the encryption mechanisms and key management delve into the modification of this file. 
Without the requisite knowledge, well-intentioned efforts to enhance the system could inadvertently lead to the loss of critical data. 
Thus, the utmost caution and expertise are indispensable when engaging with this pivotal component of the system.
*/
namespace MythicalDash;

use MythicalSystems\Utils\EncryptionHandler;

class Encryption
{
    /**
     * Deprecated 
     * 
     * Please use the MythicalSystems\Utils\EncryptionHandler!
     */
    public static function encrypt($encryptedData, $encryptionKey)
    {
        return (EncryptionHandler::encrypt($encryptedData, $encryptionKey));
    }
    /**
     * Deprecated 
     * 
     * Please use the MythicalSystems\Utils\EncryptionHandler!
     */
    public static function decrypt($encryptedData, $encryptionKey)
    {
        return (EncryptionHandler::decrypt($encryptedData, $encryptionKey));
    }
    public static function generate_key($email, $password)
    {
        $timestamp = time();
        $formatted_timestamp = date("HisdmY", $timestamp);
        $encoded_timestamp = base64_encode($formatted_timestamp);
        $key = base64_encode("mythicaldash_c_" . $encoded_timestamp . base64_encode($email) . password_hash(base64_encode($password), PASSWORD_DEFAULT) . Main::generatePassword(12));
        return $key;
    }
    public static function generate_keynoinfo()
    {
        $timestamp = time();
        $formatted_timestamp = date("HisdmY", $timestamp);
        $encoded_timestamp = base64_encode($formatted_timestamp);
        $key = base64_encode("mythicaldash_" . Main::generatePassword(12) . $encoded_timestamp . Main::generatePassword(12));
        return $key;
    }

    public static function generate_key_redeem()
    {
        $timestamp = time();
        $formatted_timestamp = date("HisdmY", $timestamp);
        $encoded_timestamp = base64_encode($formatted_timestamp);
        $key = "mythicaldash_redeem_" . base64_encode(Main::generatePassword(12) . $encoded_timestamp . Main::generatePassword(12));
        return $key;
    }
    public static function generateticket_key($tickeduuid)
    {
        $timestamp = time();
        $formatted_timestamp = date("HisdmY", $timestamp);
        $encoded_timestamp = base64_encode($formatted_timestamp);
        $key = base64_encode("mythicaldash_ticket_" . $encoded_timestamp . base64_encode($tickeduuid) . Main::generatePassword(12));
        return $key;
    }
}
?>