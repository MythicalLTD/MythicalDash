<!-- Core JS -->
<script src="<?= $appURL ?>/assets/vendor/libs/jquery/jquery.js"></script>
<script src="<?= $appURL ?>/assets/vendor/libs/popper/popper.js"></script>
<script src="<?= $appURL ?>/assets/vendor/js/bootstrap.js"></script>
<script src="<?= $appURL ?>/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
<script src="<?= $appURL ?>/assets/vendor/libs/node-waves/node-waves.js"></script>
<script src="<?= $appURL ?>/assets/vendor/libs/hammer/hammer.js"></script>
<script src="<?= $appURL ?>/assets/vendor/libs/i18n/i18n.js"></script>
<script src="<?= $appURL ?>/assets/vendor/libs/typeahead-js/typeahead.js"></script>
<script src="<?= $appURL ?>/assets/vendor/js/menu.js"></script>
<script src="<?= $appURL ?>/assets/vendor/libs/moment/moment.js"></script>
<script src="<?= $appURL ?>/assets/vendor/libs/select2/select2.js"></script>
<script src="<?= $appURL ?>/assets/vendor/libs/apex-charts/apexcharts.js"></script>
<script src="<?= $appURL ?>/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js"></script>
<script src="<?= $appURL ?>/assets/vendor/libs/formvalidation/dist/js/FormValidation.min.js"></script>
<script src="<?= $appURL ?>/assets/vendor/libs/formvalidation/dist/js/plugins/Bootstrap5.min.js"></script>
<script src="<?= $appURL ?>/assets/vendor/libs/formvalidation/dist/js/plugins/AutoFocus.min.js"></script>
<script src="<?= $appURL ?>/assets/vendor/libs/cleavejs/cleave.js"></script>
<script src="<?= $appURL ?>/assets/vendor/libs/cleavejs/cleave-phone.js"></script>
<script src="<?= $appURL ?>/assets/js/main.js"></script>
<script src="<?= $appURL ?>/assets/vendor/libs/bs-stepper/bs-stepper.js"></script>
<script src="<?= $appURL ?>/assets/js/MythicalGuard.js"></script>
<script src="<?= $appURL ?>/assets/js/preloader.js"></script>
<?php
function fis_active_page($page_urls)
{
    foreach ($page_urls as $page_url) {
        if (strpos($_SERVER['REQUEST_URI'], $page_url) !== false) {
            return true;
        }
    }
    return false;
}
use MythicalDash\SettingsManager;

if (!fis_active_page(['/e/adblock'])) {
    if (SettingsManager::getSetting("enable_adblocker_detection") == "true") {
        ?>
        <script>
            let fakeAd = document.createElement("div");
            fakeAd.className =
                "textads banner-ads banner_ads ad-unit ad-zone ad-space adsbox"

            fakeAd.style.height = "1px"

            document.body.appendChild(fakeAd)

            let x_width = fakeAd.offsetHeight;
            let msg = document.getElementById("msg")


            if (x_width) {

            } else {
                window.location.replace("/e/adblock");
            }

        </script>
        <?php
    }
}

?>