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
<script>
    document.addEventListener('keydown', function (event) {
        if (event.ctrlKey && event.keyCode === 68) {
            event.preventDefault();
            var userInput = prompt("Please enter a dialog number:");
            if (userInput === null || userInput.trim() === "") {
                window.location.href = "/dashboard";
            } else {
                switch (userInput) {
                    case "1":
                        window.location.href = "/dashboard";
                        break;
                    case "2":
                        window.location.href = "/server/create";
                        break;
                    case "3":
                        window.location.href = "/earn/afk";
                        break;
                    case "4":
                        window.location.href = "/earn/redeem";
                        break;
                    case "5":
                        window.location.href = "/earn/linkvertise";
                        break;
                    case "6":
                        window.location.href = "/store";
                        break;
                    case "7":
                        window.location.href = "/help-center";
                        break;
                    case "8":
                        window.location.href = "/help-center/tickets";
                        break;
                    case "9":
                        window.location.href = "/help-center/tos";
                        break;
                    case "10":
                        window.location.href = "/help-center/pp";
                        break;
                    case "11":
                        window.location.href = "/leaderboard";
                        break;
                    case "12":
                        window.location.href = "/users/list";
                        break;
                    case "13":
                        window.location.href = "/user/edit";
                        break;
                    case "14":
                        window.location.href = "/user/connections";
                        break;
                    case "15":
                        window.location.href = "/user/payments";
                        break;
                    case "0":
                        window.location.href = "/auth/logout";
                        break;
                    case "100":
                        window.location.href = "/admin/overview";
                        break;
                    case "101":
                        window.location.href = "/admin/health";
                        break;
                    case "102":
                        window.location.href = "/admin/api";
                        break;
                    case "103":
                        window.location.href = "/admin/users";
                        break;
                    case "104":
                        window.location.href = "/admin/servers/list";
                        break;
                    case "105":
                        window.location.href = "/admin/servers/queue/list";
                        break;
                    case "106":
                        window.location.href = "/admin/servers/queue/logs";
                        break;
                    case "107":
                        window.location.href = "/admin/redeem";
                        break;
                    case "108":
                        window.location.href = "/admin/locations";
                        break;
                    case "109":
                        window.location.href = "/admin/eggs/list";
                        break;
                    case "110":
                        window.location.href = "/admin/eggs/config";
                        break;
                    case "111":
                        window.location.href = "/admin/settings";
                        break;
                    case "112":
                        window.location.href = "/admin/tickets";
                        break;
                    default:
                        alert("Invalid dialog number. Please enter a valid dialog number.");
                }

            }
        }
    });
</script>
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