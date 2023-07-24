<?php
include(__DIR__.'/../requirements/page.php');
?>
<!DOCTYPE html>

<html lang="en" class="light-style layout-navbar-fixed layout-menu-fixed" dir="ltr" data-theme="theme-semi-dark"
  data-assets-path="<?= $appURL ?>/assets/" data-template="vertical-menu-template">
  <head>
    <meta charset="utf-8" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
    />
    <?php include(__DIR__ . '/../requirements/head.php'); ?>
    <title><?= $settings['name']?> | Terms of Service</title>
    <link rel="stylesheet" href="<?= $appURL ?>/assets/vendor/css/pages/page-help-center.css" />
  </head>

  <body>
    <div class="layout-wrapper layout-content-navbar">
      <div class="layout-container">
        <?php include(__DIR__.'/../components/sidebar.php')?>
        <div class="layout-page">
          <?php include(__DIR__.'/../components/navbar.php')?> 
          <div class="content-wrapper">
            <div class="container-xxl flex-grow-1 container-p-y">
              <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Help Center /</span> Terms of Service</h4>
              <div class="row">
                <div class="col-xl-3 col-lg-4 col-md-4 mb-lg-0 mb-4">
                  <h6>Support</h6>
                  <div class="nav-align-left">
                    <ul class="nav nav-pills border-0 w-100 gap-1">
                      <li class="nav-item" >
                        <a href="/help-center" class="nav-link">Home</a>
                      </li>
                      <li class="nav-item" >
                        <a href="/help-center/tickets" class="nav-link">Tickets</a>
                      </li>
                      <li class="nav-item">
                        <button class="nav-link active" data-bs-target="javascript:void(0);">Terms of service</button>
                      </li>
                      <li class="nav-item">
                        <a href="/help-center/pp" class="nav-link">Privacy Policy</a>
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
                        Terms of Service
                      </h4>
                      <p>
                        <p class="text-big">Legal Agreements:</p>

                        When we refer to "<?= $settings['name']?>" or use pronouns such as "we", "us" or "our", we mean <?= $settings['name']?>
                        When we refer to "User", we are talking about you and we will also use words like "you" and "your" to refer to you.
                        By registering/purchasing services or any activity on our site, you should agree to the <?= $settings['name']?> Terms and Conditions
                        We reserve the rights to terminate any of your services without notice if they breach any of our Terms And Conditions.
                      </p>
                      <p>
                        <p class="text-big"> Accounts:</p>

                        The minimum age to create an account or any activity that requires your personal data is 13 years old! If you are under 13, please contact us and request account deletion.
                        Your personal data must be real. If not, your services may be suspended and your account banned.
                        We reserve the right to request your identity card/passport/any document proving your details to verify your account details. If you do not provide it when we ask, your services will be suspended without refund and your account will be banned.
                        We are not responsible for the security of your account. We recommend that you use a strong password and do not give it to anyone. If your account gets hacked/lost, we can only help you recover it.
                        We will never sell your personal data.
                      </p>
                      <p>
                        <p class="text-big"> Support:</p>

                        We are under no obligation to install software / help you code on your site or any service got from <?= $settings['name']?> / other host! We only provide the service, you have to work on it.
                        We are not responsible for your files.
                        You are not allowed to SPAM / make jokes / offend our staff or any activity that wastes our time. We may suspend your services and ban your account.
                        We are not offering support in Discord DM / any other social media, support is only offered through our Website. Any contact in Discord DM or any other social media will be ignored, further insistence will get you banned and your services suspended.
                        You may not lie or slander <?= $settings['name']?>! You risk having our services suspended.
                      </p>
                      <p>
                        <p class="text-big"> Service Activity:</p>

                        You are not allowed to use our servers for illegal purposes like DDoS, mining etc. Your server will be suspended!
                        We are not required to install software (Pterodactyl, GameCP, etc.) on your server. We only offer you the service. You have to work at it.
                        You may not use our web hosting services or any hosting services to sell people/children/weapons/copyrighted content or any illegal activity. You will get your services suspended!
                        We are under no obligation to help you code your site!
                        Resource intensive users will not be supported in any form (eg: p2p communities - torrent trackers, file hosting sites, image hosting sites, site statistics installations (above), game statistics, installations external counters (redirecting players, etc.) will be suspended without refund!
                        You may not disrupt the services of other customers. You risk having your services suspended and your account banned!
                        We are not responsible for your website downtime. We will always let you know if there is a problem.
                        We reserve the right to adapt the hardware and software used to provide the services to the current state of the art and to inform you in good time of any additional requirements that may arise from this for the content you have stored on our servers. We undertake to make such adjustments only to a reasonable extent and taking your interests into account.
                        You are not allowed to resell our products, if you do so your service will be suspended without refund.
                      </p>
                      <p>
                        <p class="text-big"> Data:</p>

                        We are not responsible for any backup/file or any data loss. You are responsible for backups and expired, suspended, canceled, refunded, prohibited services.
                        We will never sell your personal data.
                      </p>
                      
                      <hr class="container-m-nx my-4" />
                      <div class="d-flex justify-content-between flex-wrap gap-3 mb-3">
                        <h5>Still need help? <a href="/help-center/tickets">Contact us?</a></h5>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <?php include(__DIR__.'/../components/footer.php')?>
            <div class="content-backdrop fade"></div>
          </div>
        </div>
      </div>
      <div class="layout-overlay layout-menu-toggle"></div>
      <div class="drag-target"></div>
    </div>
    <?php include(__DIR__.'/../requirements/footer.php')?>
    <script src="<?= $appURL ?>/assets/js/dashboards-ecommerce.js"></script>
  </body>
</html>
