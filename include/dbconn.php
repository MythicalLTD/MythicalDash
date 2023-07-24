<?php
//DATABASE CONNECTION FILE
$dbsettings = $config['database'];
$dbhost = $dbsettings['host'];
$dbport = $dbsettings['port'];
$dbusername = $dbsettings['username'];
$dbpassword = $dbsettings['password'];
$dbname = $dbsettings['database'];
$conn = new mysqli($dbhost . ':' . $dbport, $dbusername, $dbpassword, $dbname);
if ($conn->connect_error) {
    throw new Exception('<script>
    window.location.href = "/e/critical?e="'.$conn->connect_error.';
</script>');
}
?>