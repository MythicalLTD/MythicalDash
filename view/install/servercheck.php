<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
use MythicalDash\Telemetry;

if (isset($_GET['rr'])) {
  Telemetry::NewInstall();
  unlink("FIRST_INSTALL");
  header('location: /');
}
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

if ($phpVersion >= '8.0' && $phpVersion <= '8.3') {

} else {
  ?>
  <!DOCTYPE html>
  <html lang="en" class="dark-style layout-menu-fixed" dir="ltr" data-theme="theme-default" data-assets-path="/assets/"
    data-template="horizontal-menu-template">

  <head>
    <meta charset="utf-8" />
    <meta name="viewport"
      content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <title>MythicalDash - Server Check</title>
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
                        <a href="https://discord.gg/xUGNwePZmY" class="menu-link">
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
                    </ul>
                  </li>
            </aside>
            <div class="container-xxl flex-grow-1 container-p-y">
              <br>
              <h1 class="text-center">Wrong php version</h1>
              <p class="text-center">We don't support this php version make sure to install <code>php8.3</code> version to
                run
                MythicalDash:</p>
              <div class="alert alert-danger text-center">apt -y install php8.3
                php8.3-{common,cli,gd,mysql,opcache,soap,mbstring,bcmath,xml,fpm,curl,zip,xmlrpc,imagick,dev,imap,intl} &&
                sudo
                a2enmod php8.1</div>
              <div class="text-center">
                <br>
                <a href="/server/check" class="btn btn-primary">&nbsp; Reload &nbsp;</a>
              </div>
              <br>
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
                    <a href="https://discord.gg/xUGNwePZmY" target="_blank"
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
  <?php
  die;
}

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$current_url = $protocol . $_SERVER['HTTP_HOST'];

$requiredExtensions = ["gd", "mbstring", "bcmath", "xml", "curl", "zip"];
foreach ($requiredExtensions as $ext) {
  if (!extension_loaded($ext)) {
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
                          <a href="https://discord.gg/xUGNwePZmY" class="menu-link">
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
                      </ul>
                    </li>
              </aside>
              <div class="container-xxl flex-grow-1 container-p-y">
                <br>
                <h1 class="text-center">This server cannot run MythicalDash yet.</h1>
                <p class="text-center">The extension: <code><?= $ext ?></code> cannot be found on your server, make sure to
                  install all
                  extension by: </p>
                <div class="alert alert-danger text-center">
                  apt -y install php8.3
                  php8.3-{common,cli,gd,mysql,opcache,soap,mbstring,bcmath,xml,fpm,curl,zip,xmlrpc,imagick,dev,imap,intl}
                </div>
                <div class="text-center">
                  <br>
                  <a href="/server/check" class="btn btn-primary">&nbsp; Reload &nbsp;</a>
                </div>
                <br>
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
                      <a href="https://discord.gg/xUGNwePZmY" target="_blank"
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
    <?php
    die;
  }
}

if (!is_writable(__DIR__)) {
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
                        <a href="https://discord.gg/xUGNwePZmY" class="menu-link">
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
                    </ul>
                  </li>
            </aside>
            <div class="container-xxl flex-grow-1 container-p-y">
              <br>
              <h1 class="text-center">No write permission</h1>
              <p class="text-center">We don't have access to the folder, make sure to give us permission by:</p>
              <div class="alert alert-danger text-center">chown -R www-data:www-data /var/www/mythicaldash/*</div>
              <div class="text-center">
                <br>
                <a href="/server/check" class="btn btn-primary">&nbsp; Reload &nbsp;</a>
              </div>
              <br>
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
                    <a href="https://discord.gg/xUGNwePZmY" target="_blank"
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
  <?php
  die;
}
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
  <meta http-equiv="refresh" content="6;url=/server/check?rr">

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
                      <a href="https://discord.gg/xUGNwePZmY" class="menu-link">
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
                  </ul>
                </li>
          </aside>
          <div class="container-xxl flex-grow-1 container-p-y">
            <br>
            <div class="alert alert-success" role="alert">
              <h4 class="alert-heading">Server checking in progress</h4>
              <p>Server checking in progress. Please do not close this window or interrupt the installation process.</p>
              <div class="progress">
                <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
                  aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%"></div>
              </div>
            </div>
            <br>
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
                  <a href="https://discord.gg/xUGNwePZmY" target="_blank"
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
          window.location = url.replace("/server/config");
        }
      }, 50);
    </script>
</body>