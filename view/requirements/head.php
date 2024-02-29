<?php
use MythicalDash\SettingsManager;

?>
<meta charset="utf-8">
<meta name="viewport"
    content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
<meta name="author" content="MythicalSystems">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />
<meta name="apple-mobile-web-app-capable" content="yes" />
<meta name="mobile-web-app-capable" content="yes" />
<meta name="HandheldFriendly" content="True" />
<meta name="MobileOptimized" content="320" />
<link rel="apple-touch-icon" href="<?= SettingsManager::getSetting("logo") ?>">
<meta name="twitter:description" content="<?= SettingsManager::getSetting("seo_description") ?>">
<meta name="twitter:card" content="summary">
<meta name="twitter:image" content="<?= SettingsManager::getSetting("logo") ?>">
<meta property="og:image" content="<?= SettingsManager::getSetting("logo") ?>">
<meta name="description" content="<?= SettingsManager::getSetting("seo_description") ?>">
<meta property="og:type" content="website">
<meta name="twitter:title" content="<?= SettingsManager::getSetting("name") ?>">
<meta name="og:title" content="<?= SettingsManager::getSetting("name") ?>">
<meta name="keywords" content="<?= SettingsManager::getSetting("seo_keywords") ?>" />
<link rel="shortcut icon" type="image/x-icon" href="<?= SettingsManager::getSetting("logo") ?>">
<link rel="preconnect" href="https://fonts.googleapis.com" />
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
<link
    href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
    rel="stylesheet" />
<link rel="stylesheet" href="<?= $appURL ?>/assets/vendor/fonts/fontawesome.css" />
<link rel="stylesheet" href="<?= $appURL ?>/assets/vendor/fonts/tabler-icons.css" />
<link rel="stylesheet" href="<?= $appURL ?>/assets/vendor/fonts/flag-icons.css" />
<link rel="stylesheet" href="<?= $appURL ?>/assets/vendor/css/rtl/core.css" class="template-customizer-core-css" />
<link rel="stylesheet" href="<?= $appURL ?>/assets/vendor/css/rtl/theme-default.css"
    class="template-customizer-theme-css" />
<link rel="stylesheet" href="<?= $appURL ?>/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />
<link rel="stylesheet" href="<?= $appURL ?>/assets/vendor/libs/node-waves/node-waves.css" />
<link rel="stylesheet" href="<?= $appURL ?>/assets/vendor/libs/typeahead-js/typeahead.css" />
<link rel="stylesheet" href="<?= $appURL ?>/assets/vendor/libs/formvalidation/dist/css/formValidation.min.css" />
<link rel="stylesheet" href="<?= $appURL ?>/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css" />
<link rel="stylesheet" href="<?= $appURL ?>/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css" />
<link rel="stylesheet" href="<?= $appURL ?>/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css" />
<link rel="stylesheet" href="<?= $appURL ?>/assets/vendor/libs/bs-stepper/bs-stepper.css" />
<link rel="stylesheet" href="<?= $appURL ?>/assets/vendor/libs/bootstrap-select/bootstrap-select.css" />
<link rel="stylesheet" href="<?= $appURL ?>/assets/vendor/libs/select2/select2.css" />
<script src="<?= $appURL ?>/assets/vendor/js/helpers.js"></script>
<script src="<?= $appURL ?>/assets/vendor/js/template-customizer.js"></script>
<script src="<?= $appURL ?>/assets/js/config.js"></script>
<link rel="stylesheet" href="<?= $appURL ?>/assets/css/preloader.css" />
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
    #template-customizer .template-customizer-open-btn {
        visibility: hidden;
    }

    .badge.requestor-type {
        font-size: 10px;
        vertical-align: middle;
        background-color: #28a745;
    }

    body {
        background-image: url('<?= SettingsManager::getSetting('bg') ?>');
        background-repeat: no-repeat;
        background-attachment: fixed;
        background-size: cover;
    }

</style>
<link rel="stylesheet" href="<?= $appURL ?>/assets/css/demo.css" />
<link rel="stylesheet" href="<?= $appURL ?>/assets/css/v3.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/animejs/3.2.1/anime.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/cursor.js/dist/cursor.min.js"></script>

<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
<?php
if ($cfg_is_console_on == 'true') {
    ?>
    <script src="<?= $appURL ?>/assets/js/disable-console.js"></script>
    <?php
}

if (SettingsManager::getSetting("customcss_enabled") == 'true') {
    ?>
    <style type="text/css">
        <?= SettingsManager::getSetting("customcss_code") ?>
    </style>
    <?php
}

if (SettingsManager::getSetting("customhead_enabled") == 'true') {
    ?>
    <?= SettingsManager::getSetting("customhead_code") ?>
    <?php
}

?>
