<?php 
use MythicalDash\SettingsManager;

?>
<footer class="content-footer footer bg-footer-theme">
  <div class="container-xxl">
    <div
      class="footer-container d-flex align-items-center justify-content-between py-2 flex-md-row flex-column"
    >
      <div>
        <?= $lang['copyright']?>© 2019 - 
        <script>
          document.write(new Date().getFullYear());
        </script>
        <?= $lang['made_with_love_by']?> <a href="https://github.com/mythicalltd" target="_blank" class="fw-semibold">MythicalSystems</a>
      </div>
      <div>
        <a href="<?= SettingsManager::getSetting("PterodactylURL")?>" target="_blank" class="footer-link me-4">Pterodactyl</a>
        <a href="/help-center/tos" target="_blank" class="footer-link me-4" ><?= $lang['terms_of_service']?></a>
        <a href="/help-center/pp" target="_blank" class="footer-link d-none d-sm-inline-block" ><?= $lang['privacy_policy']?></a>
      </div>
    </div>
  </div>
</footer>