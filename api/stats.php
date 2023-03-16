<?php
require("../core/require/sql.php");
$users = $cpconn->query("SELECT * FROM users");
$servers = $cpconn->query("SELECT * FROM servers");
$serversq = $cpconn->query("SELECT * FROM servers_queue");
$tickets = $cpconn->query("SELECT * FROM tickets");
$data = array(
    "users" => $users->num_rows,
    "servers" => $servers->num_rows,
    "servers_queue" => $serversq->num_rows,
    "tickets" => $tickets->num_rows
  );
  
die(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
?>