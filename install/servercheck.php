<?php
/**
 * Parse the PHP version to x.x format.
 *
 * @return string
 */
function parse_php_version()
{
  preg_match('/^(\d+)\.(\d+)/', PHP_VERSION, $matches);

  if (count($matches) > 2) {
    return "{$matches[1]}.{$matches[2]}";
  }

  return PHP_VERSION;
}

$phpVersion = parse_php_version();

if ($phpVersion >= '8.0' && $phpVersion <= '8.2') {

} else {
  ?>
  <!doctype html>
  <html lang="en">

  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Application Installer</title>
    <link rel="shortcut icon" type="image/x-icon" href="https://avatars.githubusercontent.com/u/117385445">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/install.css">
  </head>
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <a class="navbar-brand" href="#">mythicaldash Installer</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav"
      aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ml-auto">
        <li class="nav-item">
          <a class="nav-link" href="https://discord.gg/7BZTmSK2D8">Support</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="https://docs.mythicalsystems.me">Docs</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="https://github.com/mythicalltd">Github</a>
        </li>
      </ul>
    </div>
  </nav>
  <div class="container mt-5">
    <h1 class="text-center">Wrong php version</h1>
    <p class="text-center">We don't support this php version make sure to install <code>php8.x</code> version to run
      MythicalDash:</p>
    <div class="alert alert-danger text-center">apt -y install php8.2
      php8.2-{common,cli,gd,mysql,opcache,soap,mbstring,bcmath,xml,fpm,curl,zip,xmlrpc,imagick,dev,imap,intl} && sudo
      a2enmod php8.1</div>
  </div>
  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/2.11.8/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.min.js"></script>
  </body>

  </html>
  <?php
  die;
}

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$current_url = $protocol . $_SERVER['HTTP_HOST'];

$requiredExtensions = ["gd", "soap", "mbstring", "bcmath", "xml", "curl", "zip", "xmlrpc", "imagick", "imap", "intl"];
foreach ($requiredExtensions as $ext) {
  if (!extension_loaded($ext)) {
    ?>
    <!doctype html>
    <html lang="en">

    <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
      <title>Application Installer</title>
      <link rel="shortcut icon" type="image/x-icon" href="https://avatars.githubusercontent.com/u/117385445">
      <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
      <link rel="stylesheet" href="/assets/css/install.css">
    </head>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
      <a class="navbar-brand" href="#">MythicalDash Installer</a>
      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav"
        aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ml-auto">

          <li class="nav-item">
            <a class="nav-link" href="https://discord.gg/7BZTmSK2D8">Support</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="https://docs.mythicalsystems.me">Docs</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="https://github.com/mythicalltd">Github</a>
          </li>
        </ul>
      </div>
    </nav>
    <div class="container mt-5">
      <h1 class="text-center">This server can't run MythicalDash yet.</h1>
      <p class="text-center">The extension: <code><?= $ext ?></code> can't be found on your server, make sure to install all
        extension by: </p>
      <div class="alert alert-danger text-center">
        apt -y install php8.2
        php8.2-{common,cli,gd,mysql,opcache,soap,mbstring,bcmath,xml,fpm,curl,zip,xmlrpc,imagick,dev,imap,intl}
      </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/2.11.8/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.min.js"></script>
    </body>

    </html>
    <?php
    die;
  }
}

if (!is_writable(__DIR__)) {
  ?>
  <!doctype html>
  <html lang="en">

  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Application Installer</title>
    <link rel="shortcut icon" type="image/x-icon" href="https://avatars.githubusercontent.com/u/117385445">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/install.css">
  </head>
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <a class="navbar-brand" href="#">MythicalDash Installer</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav"
      aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ml-auto">
        <li class="nav-item">
          <a class="nav-link" href="https://discord.gg/7BZTmSK2D8">Support</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="https://docs.mythicalsystems.me">Docs</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="https://github.com/mythicalltd">Github</a>
        </li>
      </ul>
    </div>
  </nav>
  <div class="container mt-5">
    <h1 class="text-center">No write permission</h1>
    <p class="text-center">We don't have access to the folder, make sure to give us permission by:</p>
    <div class="alert alert-danger text-center">chown -R www-data:www-data /var/www/client/*</div>
  </div>
  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/2.11.8/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.min.js"></script>
  </body>

  </html>
  <?php
  die;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Application Installer</title>
  <link rel="shortcut icon" type="image/x-icon" href="https://avatars.githubusercontent.com/u/117385445">
  <meta http-equiv="refresh" content="6;url=/server/mysql">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <link rel="stylesheet" href="/assets/css/install.css">
</head>

<body>
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <a class="navbar-brand" href="#">MythicalDash Installer</a>
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
          <a class="nav-link" href="https://docs.mythicalsystems.me">Docs</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="https://github.com/mythicalltd">Github</a>
        </li>
      </ul>
    </div>
  </nav>
  <div class="container mt-3">
  </div>
  <div class="container mt-3">
    <div class="alert alert-success" role="alert">
      <h4 class="alert-heading">Server checking in progress</h4>
      <p>Server checking in progress. Please do not close this window or interrupt the installation process.</p>
      <div class="progress">
        <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
          aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%"></div>
      </div>
    </div>
  </div>
  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/2.11.8/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
  <script>
    let progress = 0;
    const progressBar = document.getElementById("progressBar");
    setInterval(function () {
      if (progress < 100) {
        progress++;
        progressBar.style.width = progress + "%";
        progressBar.setAttribute("aria-valuenow", progress);
      }
      if (progress == 100) {
        window.location = url.replace("/server/mysql");
      }
    }, 50);
  </script>
</body>

</html>