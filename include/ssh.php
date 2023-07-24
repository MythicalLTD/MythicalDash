<?php
use phpseclib3\Net\SSH2;
$sshsettings = $config['ssh'];
$ssh_ip = $sshsettings['host'];
$ssh_port = $sshsettings['port'];
$ssh_username = $sshsettings['user'];
$ssh_password = $sshsettings['password'];
$ssh = new SSH2($ssh_ip,$ssh_port);
if (!$ssh->login($ssh_username, $ssh_password)) {
    throw new \Exception('Login failed');
}

?>

