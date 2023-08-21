<?php
use Symfony\Component\Yaml\Yaml;

?>
<!DOCTYPE html>
<html lang="en" class="dark-style layout-menu-fixed" dir="ltr" data-theme="theme-default" data-assets-path="/assets/"
  data-template="horizontal-menu-template">

<head>
  <meta charset="utf-8" />
  <meta name="viewport"
    content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
  <title>MythicalDash - Welcome</title>
  <meta name="description" content="" />
  <link rel="icon" type="image/x-icon" href="https://avatars.githubusercontent.com/u/117385445" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link
    href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
    rel="stylesheet" />
  <link rel="stylesheet" href="/assets/vendor/fonts/fontawesome.css" />
  <link rel="stylesheet" href="/assets/vendor/fonts/tabler-icons.css" />
  <link rel="stylesheet" href="/assets/vendor/fonts/flag-icons.css" />
  <link rel="stylesheet" href="/assets/vendor/css/rtl/core.css" class="template-customizer-core-css" />
  <link rel="stylesheet" href="/assets/vendor/css/rtl/theme-default.css" class="template-customizer-theme-css" />
  <link rel="stylesheet" href="/assets/css/demo.css" />
  <link rel="stylesheet" href="/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />
  <link rel="stylesheet" href="/assets/vendor/libs/node-waves/node-waves.css" />
  <link rel="stylesheet" href="/assets/vendor/libs/typeahead-js/typeahead.css" />
  <link rel="stylesheet" href="/assets/vendor/libs/fullcalendar/fullcalendar.css" />
  <link rel="stylesheet" href="/assets/vendor/libs/flatpickr/flatpickr.css" />
  <link rel="stylesheet" href="/assets/vendor/libs/select2/select2.css" />
  <link rel="stylesheet" href="/assets/vendor/libs/quill/editor.css" />
  <link rel="stylesheet" href="/assets/vendor/libs/formvalidation/dist/css/formValidation.min.css" />
  <link rel="stylesheet" href="/assets/vendor/css/pages/app-calendar.css" />
  <script src="/assets/vendor/js/helpers.js"></script>
  <script src="/assets/vendor/js/template-customizer.js"></script>
  <script src="/assets/js/config.js"></script>
</head>

<body>
  <div class="layout-wrapper layout-navbar-full layout-horizontal layout-without-menu">
    <div class="layout-container">
      <nav class="layout-navbar navbar navbar-expand-xl align-items-center bg-navbar-theme" id="layout-navbar">
        <div class="container-xxl">
          <div class="navbar-brand app-brand demo d-none d-xl-flex py-0 me-4">
            <a href="/" class="app-brand-link gap-2">
              <img src="https://avatars.githubusercontent.com/u/117385445" alt="Description" width="35" height="35">
              <span class="app-brand-text demo menu-text fw-bold">MythicalDash</span>
            </a>
            <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-xl-none">
              <i class="ti ti-x ti-sm align-middle"></i>
            </a>
          </div>
          <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
            <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
              <i class="ti ti-menu-2 ti-sm"></i>
            </a>
          </div>
          <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
            <ul class="navbar-nav flex-row align-items-center ms-auto">
              <li class="nav-item me-2 me-xl-0">
                <a class="nav-link style-switcher-toggle hide-arrow" href="javascript:void(0);">
                  <i class="ti ti-md"></i>
                </a>
              </li>
              <li class="nav-item navbar-dropdown dropdown-user dropdown">
                <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                  <div class="avatar avatar-online">
                    <img src="https://avatars.githubusercontent.com/u/117385445" alt class="h-auto rounded-circle" />
                  </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                  <li>
                    <a class="dropdown-item">
                      <div class="d-flex">
                        <div class="flex-shrink-0 me-3">
                          <div class="avatar avatar-online">
                            <img src="https://avatars.githubusercontent.com/u/117385445" alt
                              class="h-auto rounded-circle" />
                          </div>
                        </div>
                        <div class="flex-grow-1">
                          <span class="fw-semibold d-block">Installer</span>
                          <small class="text-muted">Administrator</small>
                        </div>
                      </div>
                    </a>
                  </li>
                </ul>
              </li>
            </ul>
          </div>
        </div>
      </nav>
      <div class="layout-page">
        <div class="content-wrapper">
          <aside id="layout-menu" class="layout-menu-horizontal menu-horizontal menu bg-menu-theme flex-grow-0">
            <div class="container-xxl d-flex h-100">
              <ul class="menu-inner">
                <li class="menu-item">
                  <a href="javascript:void(0)" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons ti ti-help"></i>
                    <div data-i18n="Support">Support</div>
                  </a>
                  <ul class="menu-sub">
                    <li class="menu-item">
                      <a href="https://discord.gg/7BZTmSK2D8" class="menu-link">
                        <i class="menu-icon tf-icons ti ti-brand-discord"></i>
                        <div data-i18n="Discord">Discord</div>
                      </a>
                    </li>
                    <li class="menu-item">
                      <a href="https://docs.mythicalsystems.me" class="menu-link">
                        <i class="menu-icon tf-icons ti ti-3d-cube-sphere"></i>
                        <div data-i18n="Documentation">Documentation</div>
                      </a>
                    </li>
                    <li class="menu-item">
                      <a href="https://auth.mythicalsystems.me" class="menu-link">
                        <i class="menu-icon tf-icons ti ti-atom-2"></i>
                        <div data-i18n="Support Ticket">Support Ticket</div>
                      </a>
                    </li>
                  </ul>
                </li>
                <li class="menu-item">
                  <a href="javascript:void(0)" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons ti ti-star"></i>
                    <div data-i18n="Help the project">Help the project</div>
                  </a>
                  <ul class="menu-sub">
                    <li class="menu-item">
                      <a href="https://github.com/mythicalltd/mythicaldash" class="menu-link">
                        <i class="menu-icon tf-icons ti ti-star"></i>
                        <div data-i18n="Star on Github">Star on Github</div>
                      </a>
                    </li>
                    <li class="menu-item">
                      <a href="https://paypal.me/mythicalsystems" class="menu-link" target="_blank">
                        <i class="menu-icon tf-icons ti ti-brand-paypal"></i>
                        <div data-i18n="PayPal">PayPal</div>
                      </a>
                    </li>
                    <li class="menu-item">
                      <a href="https://github.com/sponsors/NaysKutzu" class="menu-link">
                        <i class="menu-icon tf-icons ti ti-brand-github"></i>
                        <div data-i18n="Sponsor on Github">Sponsor on Github</div>
                      </a>
                    </li>
                    <li class="menu-item">
                      <a href="layouts-container.html" class="menu-link">
                        <i class="menu-icon tf-icons ti ti-cup"></i>
                        <div data-i18n="Buy Me A Coffee">Buy Me A Coffee</div>
                      </a>
                    </li>
                  </ul>
                </li>
          </aside>
          <div class="container-xxl flex-grow-1 container-p-y">
            <br>
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
              curl_setopt($curl, CURLOPT_URL, $fullURL . '/api/mysql?host=' . $host . '&port=' . $port . '&username=' . $username . '&password=' . $password . '&database=' . $database . '');
              curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

              $response = curl_exec($curl);

              curl_close($curl);

              if ($response !== false) {
                $data = json_decode($response, true);
                $rsp = json_encode($data);
                if ($rsp == "null") {
                  ?>
                  <div class="alert alert-danger">
                    <strong>Error:</strong> There was a problem whie we checked for the MySQL connection please refresh
                  </div>
                  <?php
                } else if ($rsp == "") {
                  ?>
                    <div class="alert alert-danger">
                      <strong>Error:</strong> There was a problem whie we checked for the MySQL connection please refresh
                    </div>
                  <?php
                } else if ($rsp == '{"status":"success","message":"The connection was successful"}') {
                  try {
                    $data = [
                      'app' => [
                        'debug' => 'false',
                        'silent_debug' => 'false',
                      ],
                      'database' => [
                        'host' => $host,
                        'port' => $port,
                        'username' => $username,
                        'password' => $password,
                        'database' => $database,
                      ],
                    ];
                    // Convert the data to YAML format
                    $yaml = Yaml::dump($data);

                    // Specify the file path where you want to create the config.yml file
                    $file = '../config.yml';
                    if (file_exists($file)) {

                      if (unlink($file)) {

                      } else {

                      }
                    } else {

                    }
                    // Write the YAML content to the file
                    if (file_put_contents($file, $yaml) !== false) {
                      // Redirect to the desired location after the file creation
                      header('Location: /server/config');
                      exit; // Make sure to exit the script after redirection to prevent further execution
                    } else {
                      echo "Error creating the config.yml file.";
                    }
                  } catch (Exception $ex) {
                    ?>
                        <div class="alert alert-danger">
                          <strong>Error:</strong> There was a problem whie we wrote the MySQL connection info to config please
                          refresh
                        </div>
                    <?php
                  }
                } else {
                  ?>
                      <div class="alert alert-danger">
                        <strong>Error:</strong> There was a problem whie we checked for the MySQL connection please refresh
                      </div>
                  <?php
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
            <p class="text-center">Enter your MySQL database information and click "Next" to check if the server can
              connect and
              set this as the main MySQL db.</p>
            <form method="get" action="/server/mysql">
              <div class="row g-3">
                <div class="mb-3 col-md-9">
                  <label class="form-label" for="host">Host</label>
                  <input name="host" type="text" class="form-control" id="host" placeholder="127.0.0.1" value="<?php if (isset($_GET['host'])) {
                    echo $_GET['host'];
                  } else {
                  } ?>" required>
                </div>
                <div class="mb-3 col-md-3">
                  <label class="form-label" for="port">Port</label>
                  <input name="port" type="number" class="form-control" id="port" placeholder="3306" value="<?php if (isset($_GET['port'])) {
                    echo $_GET['port'];
                  } else {
                    echo '3306';
                  } ?>" required>
                </div>
              </div>
              <div class="form-group">
                <label for="username">Username</label>
                <input type="text" class="form-control" id="username" name="username" value="<?php if (isset($_GET['username'])) {
                  echo $_GET['username'];
                } else {
                } ?>" placeholder="Enter username" required>
              </div>
              <div class="form-group">
                <label for="password">Password</label>
                <input type="password" class="form-control" id="password" name="password" value="<?php if (isset($_GET['password'])) {
                  echo $_GET['password'];
                } else {
                } ?>" placeholder="Enter password">
              </div>
              <div class="form-group">
                <label for="database">Database</label>
                <input type="text" class="form-control" id="database" name="database" placeholder="Enter database name"
                  value="<?php if (isset($_GET['database'])) {
                    echo $_GET['database'];
                  } else {
                  } ?>" required>
              </div>
              <div class="text-center">
                <br>
                <button type="submit" class="btn btn-primary" name="submit">&nbsp; Next &nbsp;</button>
              </div>
            </form>
          </div>

          <footer class="content-footer footer bg-footer-theme">
            <div class="container-xxl">
              <div
                class="footer-container d-flex align-items-center justify-content-between py-2 flex-md-row flex-column">
                <div>
                  Copyright © 2021-
                  <script>
                    document.write(new Date().getFullYear());
                  </script>
                  made with ❤️ by <a href="https://mythicalsystems.me" target="_blank"
                    class="fw-semibold">MythicalSystems</a>
                </div>
                <div>
                  <a href="https://github.com/MythicalLTD/MythicalDash/blob/develop/LICENSE" class="footer-link me-4"
                    target="_blank">License</a>
                  <a href="https://github.com/mythicalltd" target="_blank" class="footer-link me-4">More Projects</a>
                  <a href="https://docs.mythicalsystems.me/" target="_blank" class="footer-link me-4">Documentation</a>
                  <a href="https://discord.gg/7BZTmSK2D8" target="_blank"
                    class="footer-link d-none d-sm-inline-block">Support</a>
                </div>
              </div>
            </div>
          </footer>
          <div class="content-backdrop fade"></div>
        </div>
      </div>
    </div>
    <div class="layout-overlay layout-menu-toggle"></div>
    <div class="drag-target"></div>
    <script src="/assets/vendor/libs/jquery/jquery.js"></script>
    <script src="/assets/vendor/libs/popper/popper.js"></script>
    <script src="/assets/vendor/js/bootstrap.js"></script>
    <script src="/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="/assets/vendor/libs/node-waves/node-waves.js"></script>
    <script src="/assets/vendor/libs/hammer/hammer.js"></script>
    <script src="/assets/vendor/libs/i18n/i18n.js"></script>
    <script src="/assets/vendor/libs/typeahead-js/typeahead.js"></script>
    <script src="/assets/vendor/js/menu.js"></script>
    <script src="/assets/vendor/libs/fullcalendar/fullcalendar.js"></script>
    <script src="/assets/vendor/libs/formvalidation/dist/js/FormValidation.min.js"></script>
    <script src="/assets/vendor/libs/formvalidation/dist/js/plugins/Bootstrap5.min.js"></script>
    <script src="/assets/vendor/libs/formvalidation/dist/js/plugins/AutoFocus.min.js"></script>
    <script src="/assets/vendor/libs/select2/select2.js"></script>
    <script src="/assets/vendor/libs/flatpickr/flatpickr.js"></script>
    <script src="/assets/vendor/libs/moment/moment.js"></script>
    <script src="/assets/js/main.js"></script>
    <script src="/assets/js/app-calendar-events.js"></script>
    <script src="/assets/js/app-calendar.js"></script>
</body>