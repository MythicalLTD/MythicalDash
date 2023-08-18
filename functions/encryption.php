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
function encrypt($data, $encryptionKey) {
    $iv = random_bytes(16);
    $encryptedData = openssl_encrypt($data, 'AES-256-CBC', $encryptionKey, 0, $iv);
    return base64_encode($iv . $encryptedData);
}

function decrypt($encryptedData, $encryptionKey) {
    $data = base64_decode($encryptedData);
    $iv = substr($data, 0, 16);
    $encryptedText = substr($data, 16);
    return openssl_decrypt($encryptedText, 'AES-256-CBC', $encryptionKey, 0, $iv);
}
?>