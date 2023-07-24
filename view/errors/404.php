<?php 
http_response_code(404); 
?>
<!DOCTYPE html>

<html
  lang="en"
  class="dark-style customizer-hide"
  dir="ltr"
  data-theme="theme-semi-dark"
  data-assets-path="<?= $appURL?>/assets/"
  data-template="vertical-menu-template"
>
  <head>
    <meta charset="utf-8" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
    />
    <?php include(__DIR__ . '/../requirements/head.php'); ?>
    <link rel="stylesheet" href="<?= $appURL?>/assets/vendor/css/pages/page-misc.css" />
    <title><?= $settings['name']?> | Not found</title>

  </head>

  <body>
    <!-- Content -->

    <!-- Error -->
    <div class="container-xxl container-p-y">
      <div class="misc-wrapper">
        <h2 class="mb-1 mt-4">Page Not Found :(</h2>
        <p class="mb-4 mx-2">Oops! 😖 The requested URL was not found on this server.</p>
        <a href="/" class="btn btn-primary mb-4">Back to home</a>
        <div class="mt-4">
          <img
            src="<?= $appURL?>/assets/img/illustrations/page-misc-error.png"
            alt="page-misc-error"
            width="225"
            class="img-fluid"
          />
        </div>
      </div>
    </div>
    <div class="container-fluid misc-bg-wrapper">
      <img
        src="<?= $appURL?>/assets/img/illustrations/bg-shape-image-light.png"
        alt="page-misc-error"
        data-app-light-img="illustrations/bg-shape-image-light.png"
        data-app-dark-img="illustrations/bg-shape-image-dark.png"
      />
    </div>
    <?php include(__DIR__ . '/../requirements/footer.php'); ?>
  </body>
</html>


   