<?php
use Symfony\Component\Yaml\Yaml;
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Application Installer</title>
  <link rel="shortcut icon" type="image/x-icon" href="https://avatars.githubusercontent.com/u/117385445">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous">
  <link rel="stylesheet" href="/assets/css/install.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <a class="navbar-brand" href="#">mythicaldash Installer</a>
  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
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
<?php
if (isset($_GET['submit'])) {
  $host = $_GET['host'];
  $port = $_GET['port'];
  $username = $_GET['username'];
  $password = $_GET['password'];
  $database = $_GET['database'];
  
  $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
  $serverHost = $_SERVER['HTTP_HOST'];

  $fullURL = $protocol . '://' . $serverHost;
  $curl = curl_init();
  curl_setopt($curl, CURLOPT_URL, $fullURL.'/api/mysql?host='.$host.'&port='.$port.'&username='.$username.'&password='.$password.'&database='.$database.'');
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

  $response = curl_exec($curl);

  curl_close($curl);

  if ($response !== false) {
      $data = json_decode($response, true);
      $rsp = json_encode($data);
      if ($rsp == "null") {
        ?><div class="alert alert-danger">
          <strong>Error:</strong> There was a problem whie we checked for the MySQL connection please refresh
        </div><?php
      }
      else if ($rsp == "") {
        ?><div class="alert alert-danger">
          <strong>Error:</strong> There was a problem whie we checked for the MySQL connection please refresh
        </div><?php
      }
      else if ($rsp == '{"status":"success","message":"The connection was successful"}') {
        try {
          $data = [
            'database' => [
              'host' => $host,
              'port' => $port,
              'username' => $username,
              'password' => $password,
              'database' => $database,
            ],
          ];     
          $yaml = Yaml::dump($data);
          $file = '../config.yml';
          file_put_contents($file, $yaml, FILE_APPEND);
          header('location: /server/config');
        }
        catch (Exception $ex) {
          ?><div class="alert alert-danger">
          <strong>Error:</strong> There was a problem whie we wrote the MySQL connection info to config please refresh
        </div><?php
        }
      }
      else 
      {
        ?><div class="alert alert-danger">
          <strong>Error:</strong> There was a problem whie we checked for the MySQL connection please refresh
        </div><?php
      }
    } else {
      ?>
      <div class="alert alert-danger">
        <strong>Error:</strong> There was a problem whie we checked for the MySQL connection please refresh
      </div>
      <?php
  }
}

?>
<h1 class="text-center">MySQL Connection</h1>
  <p class="text-center">Enter your MySQL database information and click "Next" to check if the server can connect and set this as the main MySQL db.</p>
  <form method="get" action="/server/mysql">
    <div class="row g-3">
      <div class="mb-3 col-md-9">
        <label class="form-label" for="host">Host</label>
        <input name="host" type="text" class="form-control" id="host" placeholder="127.0.0.1" value="<?php if (isset($_GET['host'])) { echo $_GET['host']; } else {  }?>" required>
      </div>
      <div class="mb-3 col-md-3">
        <label class="form-label" for="port">Port</label>
        <input name="port" type="number" class="form-control" id="port" placeholder="3306" value="<?php if (isset($_GET['port'])) { echo $_GET['port']; } else { echo '3306'; }?>" required>
      </div>
    </div>
    <div class="form-group">
      <label for="username">Username</label>
      <input type="text" class="form-control" id="username" name="username" value="<?php if (isset($_GET['username'])) { echo $_GET['username']; } else { }?>" placeholder="Enter username" required>
    </div>
    <div class="form-group">
      <label for="password">Password</label>
      <input type="password" class="form-control" id="password" name="password" value="<?php if (isset($_GET['password'])) { echo $_GET['password']; } else {  }?>" placeholder="Enter password">
    </div>
    <div class="form-group">
      <label for="database">Database</label>
      <input type="text" class="form-control" id="database" name="database" placeholder="Enter database name" value="<?php if (isset($_GET['database'])) { echo $_GET['database']; } else {  }?>" required>
    </div>
    <div class="text-center">
      <br>
      <button type="submit" class="btn btn-primary" name="submit">&nbsp; Next &nbsp;</button>
    </div>
  </form>
</div>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/2.11.8/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.min.js"></script>
</body>
</html>
