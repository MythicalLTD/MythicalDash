<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
use Symfony\Component\Yaml\Yaml;

$config = Yaml::parseFile('../config.yml');


$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$serverHost = $_SERVER['HTTP_HOST'];

$fullURL = $protocol . '://' . $serverHost;
function generatePassword($length = 10)
{
  $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
  $password = '';

  for ($i = 0; $i < $length; $i++) {
    $randomIndex = rand(0, strlen($characters) - 1);
    $password .= $characters[$randomIndex];
  }

  return $password;
}

function generate_key($email, $password)
{
  $timestamp = time();
  $formatted_timestamp = date("HisdmY", $timestamp);
  $encoded_timestamp = base64_encode($formatted_timestamp);
  $key = base64_encode("mythicaldash_" . $encoded_timestamp . base64_encode($email) . password_hash(base64_encode($password), PASSWORD_DEFAULT) . generatePassword(12));
  return $key;
}
$password = generatePassword(16);
if (isset($_GET['submit'])) {
  $s_name = $_GET['servername'];
  $s_passwd = $_GET['password'];
  $s_logo = $_GET['serverlogo'];
  $dbsettings = $config['database'];
  $dbhost = $dbsettings['host'];
  $dbport = $dbsettings['port'];
  $dbusername = $dbsettings['username'];
  $dbpassword = $dbsettings['password'];
  $dbname = $dbsettings['database'];
  $iconn = new mysqli($dbhost . ':' . $dbport, $dbusername, $dbpassword, $dbname);
  $sql = file_get_contents("mythicaldash.sql");
  $statements = explode(";", $sql);
  foreach ($statements as $statement) {
    $statement = trim($statement);
    if (!empty($statement)) {
      if ($iconn->query($statement) === TRUE) {

      } else {
        echo "Error executing query: " . $iconn->error . "<br>";
      }
    }
  }
  $sqlin = "INSERT INTO `mythicaldash_settings` (`name`, `logo`) VALUES ('$s_name','$s_logo');";
  if ($iconn->query($sqlin) === TRUE) {

  } else {
    echo "Error inserting data: " . $iconn->error;
  }
  $hpwd = password_hash($s_passwd, PASSWORD_DEFAULT);
  $token = generate_key($password, "admin@mythicalsystems.tech");
  $sql_usr = "INSERT INTO `mythicaldash_users` (`email`, `username`, `first_name`, `last_name`, `password`, `api_key`, `role`) VALUES ('admin@mythicalsystems.tech', 'Administrator', 'MythicalSystems', 'MythicalSystems', '$hpwd', '$token', 'Administrator');";
  if ($iconn->query($sql_usr) === TRUE) {

  } else {
    echo "Error inserting data: " . $iconn->error;
  }
  unlink("mythicaldash.sql");
  unlink("FIRST_INSTALL");
  header('location: /');
  $iconn->close();
}
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Application Installer</title>
  <link rel="shortcut icon" type="image/x-icon" href="https://avatars.githubusercontent.com/u/117385445">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css"
    integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous">
  <link rel="stylesheet" href="/assets/css/install.css">
</head>

<body>
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <a class="navbar-brand" href="#">mythicaldash Installer</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav"
      aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ml-auto">
        <li class="nav-item">
          <a class="nav-link" href="https://discord.gg/7BZTmSK2D8">Support</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="https://github.com/MythicalLTD/mythicaldash#installation">Docs</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="https://github.com/mythicalltd">Github</a>
        </li>
      </ul>
    </div>
  </nav>
  <div class="container mt-5">
    <h1 class="text-center">MythicalDash Configuration</h1>
    <p class="text-center">Here you can config you MythicalDash settings as you like</p>
    <form method="get" action="/server/config">
      <div class="form-group">
        <label for="servername">Server name:</label>
        <input type="text" class="form-control" id="servername" name="servername" placeholder="MythicalSystems"
          required>
      </div>
      <div class="form-group">
        <label for="serverlogo">Logo</label>
        <input type="text" class="form-control" id="serverlogo" name="serverlogo"
          value="<?= $fullURL ?>/assets/images/logo.png" placeholder="<?= $fullURL ?>/assets/images/logo.png">
      </div>
      <br>
      <h1 class="text-center">MythicalDash Default Account</h1>
      <p class="text-center">Here you can find the login info for the default MythicalDash account:</p>
      <div class="form-group">
        <label for="username">Email:</label>
        <input type="email" class="form-control" id="username" name="username" value="admin@mythicalsystems.tech"
          readonly="true">
      </div>
      <div class="form-group">
        <label for="password">Password:</label>
        <input type="text" class="form-control" id="password" name="password" value="<?= $password ?>" readonly="true">
      </div>

      <div class="text-center">
        <br>
        <button type="submit" class="btn btn-primary" name="submit">&nbsp; Finish &nbsp;</button>
      </div>
    </form>
  </div>
  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/2.11.8/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.min.js"></script>
</body>

</html>