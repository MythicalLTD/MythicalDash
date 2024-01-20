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
<link rel="stylesheet" href="<?= $appURL ?>/assets/css/demo.css" />
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

    /* width */
    ::-webkit-scrollbar {
        width: 10px;
    }

    /* Track */
    ::-webkit-scrollbar-track {
        background: #161931;
    }

    /* Handle */
    ::-webkit-scrollbar-thumb {
        background: #3a4078;
    }

    /* Handle on hover */
    ::-webkit-scrollbar-thumb:hover {
        background: #42498f;
    }

    .btn-primary {
        color: #fff;
        background-color: #3d4373;
        border-color: #3d4373;
    }

    .btn-primary:hover {
        color: #fff !important;
        background-color: #28a4f6 !important;
        border-color: #28a4f6 !important;
    }

    .btn-check:focus+.btn-primary,
    .btn-primary:focus,
    .btn-primary.focus {
        color: #fff;
        background-color: #28bdf6;
        border-color: #1eafe7;
        box-shadow: none;
    }

    .btn-check:checked+.btn-primary,
    .btn-check:active+.btn-primary,
    .btn-primary:active,
    .btn-primary.active,
    .btn-primary.show.dropdown-toggle,
    .show>.btn-primary.dropdown-toggle {
        color: #fff !important;
        background-color: #28bdf6 !important;
        border-color: #1eafe7 !important;
    }

    .bg-menu-theme.menu-vertical .menu-item.active>.menu-link:not(.menu-toggle) {
        background: linear-gradient(72.47deg, #3ba3ed 22.16%, rgb(34 105 215 / 70%) 76.47%);
        box-shadow: 0px 2px 6px 0px rgb(103 197 240 / 48%);
        color: #fff !important;
    }

    .bg-label-primary {
        background-color: #3a4764 !important;
        color: #67bcf0 !important;
    }

    .bg-menu-theme .menu-text {
        color: #65c5ff;
    }

    .nav-pills .nav-link.active,
    .nav-pills .nav-link.active:hover,
    .nav-pills .nav-link.active:focus {
        background-color: #28a4f6;
        color: #fff;
    }

    .dropdown-menu {
        --bs-dropdown-zindex: 1000;
        --bs-dropdown-min-width: 10rem;
        --bs-dropdown-padding-x: 0;
        --bs-dropdown-padding-y: 0.5rem;
        --bs-dropdown-spacer: 0.25rem;
        --bs-dropdown-font-size: 0.9375rem;
        --bs-dropdown-color: #b6bee3;
        --bs-dropdown-bg: #2f3349;
        --bs-dropdown-border-color: #434968;
        --bs-dropdown-border-radius: 0.375rem;
        --bs-dropdown-border-width: 0px;
        --bs-dropdown-inner-border-radius: 0px;
        --bs-dropdown-divider-bg: #434968;
        --bs-dropdown-divider-margin-y: 0.5rem;
        --bs-dropdown-box-shadow: 0 0.25rem 1rem rgba(15, 20, 34, 0.55);
        --bs-dropdown-link-color: #cfd3ec;
        --bs-dropdown-link-hover-color: #67c3f0;
        --bs-dropdown-link-hover-bg: rgba(115, 103, 240, 0.08);
        --bs-dropdown-link-active-color: #fff;
        --bs-dropdown-link-active-bg: #67c4f0;
        --bs-dropdown-link-disabled-color: #aab3de;
        --bs-dropdown-item-padding-x: 1rem;
        --bs-dropdown-item-padding-y: 0.42rem;
        --bs-dropdown-header-color: #7983bb;
        --bs-dropdown-header-padding-x: 1rem;
        --bs-dropdown-header-padding-y: 0.5rem;
        position: absolute;
        z-index: var(--bs-dropdown-zindex);
        display: none;
        min-width: var(--bs-dropdown-min-width);
        padding: var(--bs-dropdown-padding-y) var(--bs-dropdown-padding-x);
        margin: 0;
        font-size: var(--bs-dropdown-font-size);
        color: var(--bs-dropdown-color);
        text-align: left;
        list-style: none;
        background-color: var(--bs-dropdown-bg);
        background-clip: padding-box;
        border: var(--bs-dropdown-border-width) solid var(--bs-dropdown-border-color);
        border-radius: var(--bs-dropdown-border-radius);
    }

    a {
        color: #67c9f0;
    }

    .center {
        margin: auto;
        width: auto;
        background: #25283d;
        border-radius: 25px;
        padding: 10px;
    }

    .text-primary {
        color: #67c9f0 !important;
    }

    .card {
        --bs-card-spacer-y: 1.5rem;
        --bs-card-spacer-x: 1.5rem;
        --bs-card-title-spacer-y: 0.875rem;
        --bs-card-border-width: 0;
        --bs-card-border-color: #434968;
        --bs-card-border-radius: 0.375rem;
        --bs-card-box-shadow: 0 0.25rem 1.25rem rgba(15, 20, 34, 0.4);
        --bs-card-inner-border-radius: 0.375rem;
        --bs-card-cap-padding-y: 1.5rem;
        --bs-card-cap-padding-x: 1.5rem;
        --bs-card-cap-bg: transparent;
        --bs-card-cap-color: ;
        --bs-card-height: ;
        --bs-card-color: ;
        --bs-card-bg: #161931;
        --bs-card-img-overlay-padding: 1.5rem;
        --bs-card-group-margin: 0.75rem;
        position: relative;
        display: flex;
        flex-direction: column;
        min-width: 0;
        height: var(--bs-card-height);
        word-wrap: break-word;
        background-color: var(--bs-card-bg);
        background-clip: border-box;
        border: var(--bs-card-border-width) solid var(--bs-card-border-color);
        border-radius: var(--bs-card-border-radius);
    }

    .bg-navbar-theme {
        background-color: rgb(28 31 47 / 95%) !important;
        color: #b6bee3;
    }
</style>
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
