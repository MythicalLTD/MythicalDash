<?php
use MythicalDash\SettingsManager;
include(__DIR__ . '/../requirements/page.php');

?>
<!DOCTYPE html>

<html lang="en" class="dark-style layout-navbar-fixed layout-menu-fixed" dir="ltr" data-theme="theme-semi-dark"
  data-assets-path="<?= $appURL ?>/assets/" data-template="vertical-menu-template">

<head>
  <meta charset="utf-8" />
  <meta name="viewport"
    content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
  <?php include(__DIR__ . '/../requirements/head.php'); ?>
  <title>
    <?= SettingsManager::getSetting("name") ?> - Privacy Policy
  </title>
  <link rel="stylesheet" href="<?= $appURL ?>/assets/vendor/css/pages/page-help-center.css" />
</head>

<body>
<?php
  if (SettingsManager::getSetting("show_snow") == "true") {
    include(__DIR__ . '/../components/snow.php');
  }
  ?>
  <div id="preloader" class="discord-preloader">
    <div class="spinner"></div>
  </div>
  <div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
      <?php include(__DIR__ . '/../components/sidebar.php') ?>
      <div class="layout-page">
        <?php include(__DIR__ . '/../components/navbar.php') ?>
        <div class="content-wrapper">
          <div class="container-xxl flex-grow-1 container-p-y">
            <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Help Center /</span> Privacy Policy</h4>
            <div class="row">
              <div class="col-xl-3 col-lg-4 col-md-4 mb-lg-0 mb-4">
                <h6>Support</h6>
                <div class="nav-align-left">
                  <ul class="nav nav-pills border-0 w-100 gap-1">
                    <li class="nav-item">
                      <a href="/help-center" class="nav-link">Home</a>
                    </li>
                    <li class="nav-item">
                      <a href="/help-center/tickets" class="nav-link">Tickets</a>
                    </li>
                    <li class="nav-item">
                      <a href="/help-center/tos" class="nav-link">Terms of Service</a>
                    </li>
                    <li class="nav-item">
                      <button class="nav-link active" data-bs-target="javascript:void(0);">Privacy Policy</button>
                    </li>
                  </ul>
                </div>
              </div>
              <div class="col-xl-9 col-lg-8 col-md-8">
                <div class="card overflow-hidden">
                  <div class="card-body">
                    <a class="btn btn-label-primary mb-4" href="/help-center">
                      <i class="ti ti-chevron-left scaleX-n1-rtl me-1 me-1"></i>
                      <span class="align-middle">Back</span>
                    </a>
                    <h4 class="d-flex align-items-center mt-2 mb-4">
                      <span class="badge bg-label-secondary p-2 rounded me-3">
                        <i class="fa-sharp fa-solid fa-shield"></i>
                      </span>
                      Privacy Policy
                    </h4>
                    <?= SettingsManager::getSetting("privacy_policy") ?>

                    <hr class="container-m-nx my-4" />
                    <div class="d-flex justify-content-between flex-wrap gap-3 mb-3">
                      <h5>Still need help? <a href="/help-center/tickets">Contact us?</a></h5>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <?php include(__DIR__ . '/../components/footer.php') ?>
          <div class="content-backdrop fade"></div>
        </div>
      </div>
    </div>
    <div class="layout-overlay layout-menu-toggle"></div>
    <div class="drag-target"></div>
  </div>
  <?php include(__DIR__ . '/../requirements/footer.php') ?>
  <script src="<?= $appURL ?>/assets/js/dashboards-ecommerce.js"></script>
</body>

</html>