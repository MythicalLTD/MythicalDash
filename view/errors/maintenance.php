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
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"/>
  <title><?= $settings['name']?> | Not Authorized</title>
  <?php include(__DIR__ . '/../requirements/head.php'); ?>
  <link rel="stylesheet" href="<?= $appURL?>/assets/vendor/css/pages/page-misc.css" />
</head>
<body>
<div class="container-xxl container-p-y">
  <div class="misc-wrapper">
    <h2 class="mb-1 mx-2">Under Maintenance!</h2>
    <p class="mb-4 mx-2">Sorry for the inconvenience but we're performing some maintenance at the moment</p>
    <a href="/" class="btn btn-primary mb-4">Back to home</a>
    <div class="mt-4">
      <img
        src="<?= $appURL?>/assets/img/illustrations/page-misc-under-maintenance.png"
        alt="page-misc-under-maintenance"
        width="550"
        class="img-fluid"
      />
    </div>
  </div>
</div>
<div class="container-fluid misc-bg-wrapper misc-under-maintenance-bg-wrapper">
  <img
    src="<?= $appURL?>/assets/img/illustrations/bg-shape-image-light.png"
    alt="page-misc-under-maintenance"
    data-app-light-img="illustrations/bg-shape-image-light.png"
    data-app-dark-img="illustrations/bg-shape-image-dark.png"
  />
</div>
<?php include(__DIR__ . '/../requirements/footer.php'); ?>
</body>
</html>