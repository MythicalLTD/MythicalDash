<?php 
function writeLog($type, $log, $conn)
{
    $ip_address = getclientip();
    if ($ip_address == "") {
        $ip_address = "Unknown";
    }
    try {
        if ($log == "") {
            $query = "INSERT INTO mythicaldash_logs (log, type) VALUES ('Server sent blank value to logs', 'error')";
        } else {
            $w_log = '[' . $ip_address . '] ' . $log;
            $query = "INSERT INTO mythicaldash_logs (log, type) VALUES ('$w_log', '$type')";
            $conn->query($query);
            $conn->close();
        }
    } catch (Exception $e) {
        
    }
}
?>