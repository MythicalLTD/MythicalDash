<?php
use MythicalDash\SettingsManager;
include('requirements/page.php');
?>
<!DOCTYPE html>

<html lang="en" class="dark-style layout-navbar-fixed layout-menu-fixed" dir="ltr" data-theme="theme-semi-dark"
  data-assets-path="<?= $appURL ?>/assets/" data-template="vertical-menu-template">

<head>
  <?php include('requirements/head.php'); ?>
  <link rel="stylesheet" href="<?= $appURL ?>/assets/vendor/css/pages/page-help-center.css" />
  <title>
    <?= SettingsManager::getSetting("name") ?> - Help-Center
  </title>
</head>

<body>
<?php
  if (SettingsManager::getSetting("show_snow") == "true") {
    include(__DIR__ . '/components/snow.php');
  }
  ?>
  <div id="preloader" class="discord-preloader">
    <div class="spinner"></div>
  </div>
  <div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
      <?php include('components/sidebar.php') ?>
      <div class="layout-page">
        <?php include('components/navbar.php') ?>
        <div class="content-wrapper">
          <div class="container-xxl flex-grow-1 container-p-y">
            <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Help-Center /</span> Home</h4>
            <?php include(__DIR__ . '/components/alert.php') ?>
            <div
              class="help-center-header rounded d-flex flex-column justify-content-center align-items-center bg-help-center">
              <h3 class="text-center">Hello, how can we help?</h3>
              <p class="text-center px-3 mb-0">Common topics: <a href="help-center/tos">Terms of Service</a>, <a
                  href="help-center/pp">Privacy Policy</a></p>
            </div>
            <div id="ads">
              <?php
              if (SettingsManager::getSetting("enable_ads") == "true") {
                ?>
                <br>
                <?= SettingsManager::getSetting("ads_code") ?>
                <br>
                <?php
              }
              ?>
            </div>
            <div class="help-center-popular-articles bg-help-center py-5">
              <div class="container-xl">
                <h3 class="text-center my-4">Popular Articles</h3>
                <div class="row">
                  <div class="col-lg-10 mx-auto mb-4">
                    <div class="row">
                      <div class="col-md-4 mb-md-0 mb-4">
                        <div class="card border shadow-none">
                          <div class="card-body text-center">
                            <svg xmlns="http://www.w3.org/2000/svg"
                              class="icon icon-tabler icon-tabler-shield-checkered-filled" width="56" height="56"
                              viewBox="0 0 24 24" stroke-width="1.5" stroke="#2c3e50" fill="none" stroke-linecap="round"
                              stroke-linejoin="round">
                              <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                              <path
                                d="M11.013 12v9.754a13 13 0 0 1 -8.733 -9.754h8.734zm9.284 3.794a13 13 0 0 1 -7.283 5.951l-.001 -9.745h8.708a12.96 12.96 0 0 1 -1.424 3.794zm-9.283 -13.268l-.001 7.474h-8.986c-.068 -1.432 .101 -2.88 .514 -4.282a1 1 0 0 1 1.005 -.717a11 11 0 0 0 7.192 -2.256l.276 -.219zm1.999 7.474v-7.453l-.09 -.073a11 11 0 0 0 7.189 2.537l.342 -.01a1 1 0 0 1 1.005 .717c.413 1.403 .582 2.85 .514 4.282h-8.96z"
                                stroke-width="0" fill="currentColor" />
                            </svg>
                            <h5 class="my-2">Terms of Service</h5>
                            <p> Read and agree to our Terms of Service before using our platforms.</p>
                            <a class="btn btn-sm btn-label-primary" href="/help-center/tos">Read More</a>
                          </div>
                        </div>
                      </div>
                      <div class="col-md-4 mb-md-0 mb-4">
                        <div class="card border shadow-none">
                          <div class="card-body text-center">
                            <svg xmlns="http://www.w3.org/2000/svg"
                              class="icon icon-tabler icon-tabler-lock-square-rounded-filled" width="56" height="56"
                              viewBox="0 0 24 24" stroke-width="1.5" stroke="#2c3e50" fill="none" stroke-linecap="round"
                              stroke-linejoin="round">
                              <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                              <path
                                d="M12 2c-.218 0 -.432 .002 -.642 .005l-.616 .017l-.299 .013l-.579 .034l-.553 .046c-4.785 .464 -6.732 2.411 -7.196 7.196l-.046 .553l-.034 .579c-.005 .098 -.01 .198 -.013 .299l-.017 .616l-.004 .318l-.001 .324c0 .218 .002 .432 .005 .642l.017 .616l.013 .299l.034 .579l.046 .553c.464 4.785 2.411 6.732 7.196 7.196l.553 .046l.579 .034c.098 .005 .198 .01 .299 .013l.616 .017l.642 .005l.642 -.005l.616 -.017l.299 -.013l.579 -.034l.553 -.046c4.785 -.464 6.732 -2.411 7.196 -7.196l.046 -.553l.034 -.579c.005 -.098 .01 -.198 .013 -.299l.017 -.616l.005 -.642l-.005 -.642l-.017 -.616l-.013 -.299l-.034 -.579l-.046 -.553c-.464 -4.785 -2.411 -6.732 -7.196 -7.196l-.553 -.046l-.579 -.034a28.058 28.058 0 0 0 -.299 -.013l-.616 -.017l-.318 -.004l-.324 -.001zm0 4a3 3 0 0 1 2.995 2.824l.005 .176v1a2 2 0 0 1 1.995 1.85l.005 .15v3a2 2 0 0 1 -1.85 1.995l-.15 .005h-6a2 2 0 0 1 -1.995 -1.85l-.005 -.15v-3a2 2 0 0 1 1.85 -1.995l.15 -.005v-1a3 3 0 0 1 3 -3zm3 6h-6v3h6v-3zm-3 -4a1 1 0 0 0 -.993 .883l-.007 .117v1h2v-1a1 1 0 0 0 -1 -1z"
                                fill="currentColor" stroke-width="0" />
                            </svg>
                            <h5 class="my-2">Privacy Policy</h5>
                            <p>Discover how we safeguard your personal information through our Privacy Policy.</p>
                            <a class="btn btn-sm btn-label-primary" href="/help-center/pp">Read More</a>
                          </div>
                        </div>
                      </div>
                      <div class="col-md-4">
                        <div class="card border shadow-none">
                          <div class="card-body text-center">
                            <svg xmlns="http://www.w3.org/2000/svg"
                              class="icon icon-tabler icon-tabler-help-octagon-filled" width="56" height="56"
                              viewBox="0 0 24 24" stroke-width="1.5" stroke="#2c3e50" fill="none" stroke-linecap="round"
                              stroke-linejoin="round">
                              <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                              <path
                                d="M14.897 1a4 4 0 0 1 2.664 1.016l.165 .156l4.1 4.1a4 4 0 0 1 1.168 2.605l.006 .227v5.794a4 4 0 0 1 -1.016 2.664l-.156 .165l-4.1 4.1a4 4 0 0 1 -2.603 1.168l-.227 .006h-5.795a3.999 3.999 0 0 1 -2.664 -1.017l-.165 -.156l-4.1 -4.1a4 4 0 0 1 -1.168 -2.604l-.006 -.227v-5.794a4 4 0 0 1 1.016 -2.664l.156 -.165l4.1 -4.1a4 4 0 0 1 2.605 -1.168l.227 -.006h5.793zm-2.897 14a1 1 0 0 0 -.993 .883l-.007 .117l.007 .127a1 1 0 0 0 1.986 0l.007 -.117l-.007 -.127a1 1 0 0 0 -.993 -.883zm1.368 -6.673a2.98 2.98 0 0 0 -3.631 .728a1 1 0 0 0 1.44 1.383l.171 -.18a.98 .98 0 0 1 1.11 -.15a1 1 0 0 1 -.34 1.886l-.232 .012a1 1 0 0 0 .111 1.994a3 3 0 0 0 1.371 -5.673z"
                                stroke-width="0" fill="currentColor" />
                            </svg>
                            <h5 class="my-2">Support</h5>
                            <p>You feel like you need more help?</p>
                            <button type="button" class="btn btn-sm btn-label-primary" data-bs-toggle="modal"
                              data-bs-target="#createticket">Open a ticket</button>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div id="ads">
              <?php
              if (SettingsManager::getSetting("enable_ads") == "true") {
                ?>
                <br>
                <?= SettingsManager::getSetting("ads_code") ?>
                <br>
                <?php
              }
              ?>
            </div>
            <div class="help-center-contact-us help-center-bg-alt">
              <div class="container-xl">
                <div class="row justify-content-center py-5">
                  <div class="col-md-8 col-lg-6 text-center mt-4">
                    <h3>Still need help?</h3>
                    <p class="mb-3">
                      Our specialists are always happy to help. Contact us during standard business hours or email us
                      24/7 and we'll get back to you.
                    </p>
                    <div class="d-flex justify-content-center flex-wrap gap-4">
                      <a href="<?= SettingsManager::getSetting("discord_invite") ?>" class="btn btn-primary">Visit our community</a>
                      <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                        data-bs-target="#createticket">Open a ticket</button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div id="ads">
              <?php
              if (SettingsManager::getSetting("enable_ads") == "true") {
                ?>
                <br>
                <?= SettingsManager::getSetting("ads_code") ?>
                <br>
                <?php
              }
              ?>
            </div>
          </div>
          <?php include('components/footer.php') ?>
          <div class="content-backdrop fade"></div>
          <?php include('components/modals.php') ?>
        </div>
      </div>
    </div>
    <div class="layout-overlay layout-menu-toggle">
      <div class="drag-target"></div>
    </div>
    <?php include('requirements/footer.php') ?>
</body>

</html>