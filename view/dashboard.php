<?php
include('requirements/page.php');
?>
<!DOCTYPE html>

<html lang="en" class="light-style layout-navbar-fixed layout-menu-fixed" dir="ltr" data-theme="theme-semi-dark"
  data-assets-path="<?= $appURL ?>/assets/" data-template="vertical-menu-template">

<head>
  <?php include('requirements/head.php'); ?>
  <title>
    <?= $settings['name'] ?> | Dashboard
  </title>
</head>

<body>
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
            <?php include(__DIR__ . '/components/alert.php') ?>
            <div class="row">
              <!-- Statistics -->
              <div class="card h-100">
                <div class="card-header">
                  <div class="d-flex justify-content-between mb-3">
                    <h5 class="card-title mb-0">Statistics</h5>
                    <small id="updateText" class="text-muted">Updated 0 seconds ago</small>
                  </div>
                </div>
                <div class="card-body">
                  <div class="row gy-3">
                    <div class="col-md-3 col-6">
                      <div class="d-flex align-items-center">
                        <div class="badge rounded-pill bg-label-primary me-3 p-2">
                          <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-world" width="24"
                            height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                            <path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0"></path>
                            <path d="M3.6 9h16.8"></path>
                            <path d="M3.6 15h16.8"></path>
                            <path d="M11.5 3a17 17 0 0 0 0 18"></path>
                            <path d="M12.5 3a17 17 0 0 1 0 18"></path>
                          </svg>
                        </div>
                        <div class="card-info">
                          <h5 class="mb-0">0</h5>
                          <small>Websites</small>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-3 col-6">
                      <div class="d-flex align-items-center">
                        <div class="badge rounded-pill bg-label-info me-3 p-2">
                          <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-cloud" width="24"
                            height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                            <path
                              d="M6.657 18c-2.572 0 -4.657 -2.007 -4.657 -4.483c0 -2.475 2.085 -4.482 4.657 -4.482c.393 -1.762 1.794 -3.2 3.675 -3.773c1.88 -.572 3.956 -.193 5.444 1c1.488 1.19 2.162 3.007 1.77 4.769h.99c1.913 0 3.464 1.56 3.464 3.486c0 1.927 -1.551 3.487 -3.465 3.487h-11.878">
                            </path>
                          </svg>
                        </div>
                        <div class="card-info">
                          <h5 class="mb-0">1024Mb</h5>
                          <small>Free space</small>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-3 col-6">
                      <div class="d-flex align-items-center">
                        <div class="badge rounded-pill bg-label-danger me-3 p-2">
                          <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-users" width="24"
                            height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                            <path d="M9 7m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0"></path>
                            <path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                            <path d="M21 21v-2a4 4 0 0 0 -3 -3.85"></path>
                          </svg>
                        </div>
                        <div class="card-info">
                          <h5 class="mb-0">2</h5>
                          <small>FTP Accounts</small>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-3 col-6">
                      <div class="d-flex align-items-center">
                        <div class="badge rounded-pill bg-label-success me-3 p-2">
                          <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-server" width="24"
                            height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                            <path d="M3 4m0 3a3 3 0 0 1 3 -3h12a3 3 0 0 1 3 3v2a3 3 0 0 1 -3 3h-12a3 3 0 0 1 -3 -3z">
                            </path>
                            <path d="M3 12m0 3a3 3 0 0 1 3 -3h12a3 3 0 0 1 3 3v2a3 3 0 0 1 -3 3h-12a3 3 0 0 1 -3 -3z">
                            </path>
                            <path d="M7 8l0 .01"></path>
                            <path d="M7 16l0 .01"></path>
                          </svg>
                        </div>
                        <div class="card-info">
                          <h5 class="mb-0">1</h5>
                          <small>Databases</small>
                        </div>
                      </div>
                    </div>
                  </div>
                  <br>
                  <div class="row gy-3">
                    <div class="col-md-3 col-6">
                      <div class="d-flex align-items-center">
                        <div class="badge rounded-pill bg-label-primary me-3 p-2">
                          <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-world" width="24"
                            height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                            <path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0"></path>
                            <path d="M3.6 9h16.8"></path>
                            <path d="M3.6 15h16.8"></path>
                            <path d="M11.5 3a17 17 0 0 0 0 18"></path>
                            <path d="M12.5 3a17 17 0 0 1 0 18"></path>
                          </svg>
                        </div>
                        <div class="card-info">
                          <h5 class="mb-0">0</h5>
                          <small>Websites</small>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-3 col-6">
                      <div class="d-flex align-items-center">
                        <div class="badge rounded-pill bg-label-info me-3 p-2">
                          <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-cloud" width="24"
                            height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                            <path
                              d="M6.657 18c-2.572 0 -4.657 -2.007 -4.657 -4.483c0 -2.475 2.085 -4.482 4.657 -4.482c.393 -1.762 1.794 -3.2 3.675 -3.773c1.88 -.572 3.956 -.193 5.444 1c1.488 1.19 2.162 3.007 1.77 4.769h.99c1.913 0 3.464 1.56 3.464 3.486c0 1.927 -1.551 3.487 -3.465 3.487h-11.878">
                            </path>
                          </svg>
                        </div>
                        <div class="card-info">
                          <h5 class="mb-0">1024Mb</h5>
                          <small>Free space</small>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-3 col-6">
                      <div class="d-flex align-items-center">
                        <div class="badge rounded-pill bg-label-danger me-3 p-2">
                          <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-users" width="24"
                            height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                            <path d="M9 7m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0"></path>
                            <path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                            <path d="M21 21v-2a4 4 0 0 0 -3 -3.85"></path>
                          </svg>
                        </div>
                        <div class="card-info">
                          <h5 class="mb-0">2</h5>
                          <small>FTP Accounts</small>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-3 col-6">
                      <div class="d-flex align-items-center">
                        <div class="badge rounded-pill bg-label-success me-3 p-2">
                          <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-server" width="24"
                            height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                            <path d="M3 4m0 3a3 3 0 0 1 3 -3h12a3 3 0 0 1 3 3v2a3 3 0 0 1 -3 3h-12a3 3 0 0 1 -3 -3z">
                            </path>
                            <path d="M3 12m0 3a3 3 0 0 1 3 -3h12a3 3 0 0 1 3 3v2a3 3 0 0 1 -3 3h-12a3 3 0 0 1 -3 -3z">
                            </path>
                            <path d="M7 8l0 .01"></path>
                            <path d="M7 16l0 .01"></path>
                          </svg>
                        </div>
                        <div class="card-info">
                          <h5 class="mb-0">1</h5>
                          <small>Databases</small>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <?php include('components/footer.php') ?>
          <div class="content-backdrop fade"></div>
        </div>
      </div>
    </div>
    <div class="layout-overlay layout-menu-toggle"></div>
    <div class="drag-target"></div>
  </div>
  <?php include('requirements/footer.php') ?>
  <script src="<?= $appURL ?>/assets/js/dashboards-ecommerce.js"></script>
  <script>
    function updateElapsedTime() {
      const updateTextElement = document.getElementById('updateText');
      const startDate = new Date();

      function updateText() {
        const now = new Date();
        const timeDiff = now - startDate;
        let elapsed = '';

        if (timeDiff >= 1000 * 60 * 60 * 24 * 30) {
          const months = Math.floor(timeDiff / (1000 * 60 * 60 * 24 * 30));
          elapsed = months === 1 ? 'month' : 'months';
          updateTextElement.textContent = `Updated ${months} ${elapsed} ago`;
        } else if (timeDiff >= 1000 * 60 * 60 * 24) {
          const days = Math.floor(timeDiff / (1000 * 60 * 60 * 24));
          elapsed = days === 1 ? 'day' : 'days';
          updateTextElement.textContent = `Updated ${days} ${elapsed} ago`;
        } else if (timeDiff >= 1000 * 60 * 60) {
          const hours = Math.floor(timeDiff / (1000 * 60 * 60));
          elapsed = hours === 1 ? 'hour' : 'hours';
          updateTextElement.textContent = `Updated ${hours} ${elapsed} ago`;
        } else if (timeDiff >= 1000 * 60) {
          const minutes = Math.floor(timeDiff / (1000 * 60));
          elapsed = minutes === 1 ? 'minute' : 'minutes';
          updateTextElement.textContent = `Updated ${minutes} ${elapsed} ago`;
        } else {
          const seconds = Math.floor(timeDiff / 1000);
          elapsed = seconds === 1 ? 'second' : 'seconds';
          updateTextElement.textContent = `Updated ${seconds} ${elapsed} ago`;
        }
      }

      setInterval(updateText, 1000);
    }
    window.onload = updateElapsedTime;
  </script>
</body>

</html>