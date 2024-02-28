<?php
use MythicalDash\SettingsManager;

function is_active_page($page_urls)
{
  foreach ($page_urls as $page_url) {
    if (strpos($_SERVER['REQUEST_URI'], $page_url) !== false) {
      return true;
    }
  }
  return false;
}
?>

<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
  <div class="app-brand demo">
    <a href="/" class="app-brand-link">
      <span class="app-brand-text demo menu-text fw-bold">
        <?= SettingsManager::getSetting("name") ?>
      </span>
    </a>
  </div>

  <div class="menu-inner-shadow"></div>

  <ul class="menu-inner py-1">
    <li class="menu-header small text-uppercase">
      <span class="menu-header-text">
        <?= $lang['home'] ?>
      </span>
    </li>
    <li class="menu-item <?php echo is_active_page(['/dashboard']) ? 'active' : ''; ?>">
      <a href="/dashboard" class="menu-link">
        <i class="menu-icon tf-icons ti ti-home"></i>
        <div>
          <?= $lang['dashboard'] ?>
        </div>
      </a>
    </li>
    <li class="menu-item <?php echo is_active_page(['/server/create']) ? 'active' : ''; ?>">
      <a href="/server/create" class="menu-link">
        <i class="menu-icon tf-icons ti ti-server"></i>
        <div>
          <?= $lang['create_server'] ?>
        </div>
      </a>
    </li>
    <li class="menu-item <?php echo is_active_page(['/earn', '/earn/afk', '/earn/redeem']) ? 'active' : ''; ?>">
      <a href="javascript:void(0);" class="menu-link menu-toggle">
        <i class="menu-icon tf-icons ti ti-currency-euro"></i>
        <div>
          <?= $lang['earn'] ?>
        </div>
      </a>
      <ul class="menu-sub">
        <?php if (SettingsManager::getSetting("enable_afk") == "true") {
          ?>
          <li class="menu-item <?php echo is_active_page(['/earn/afk']) ? 'active' : ''; ?>">
            <a href="/earn/afk" class="menu-link">
              <div>
                <?= $lang['afk'] ?>
              </div>
            </a>
          </li>
          <?php
        } ?>

        <li class="menu-item <?php echo is_active_page(['/earn/redeem']) ? 'active' : ''; ?>">
          <a href="/earn/redeem" class="menu-link">
            <div>
              <?= $lang['redeem'] ?>
            </div>
          </a>
        </li>
        <?php if (SettingsManager::getSetting("linkvertise_enabled") == "true") {
          ?>
          <li class="menu-item <?php echo is_active_page(['/earn/linkvertise']) ? 'active' : ''; ?>">
            <a href="/earn/linkvertise" class="menu-link">
              <div>Linkvertise</div>
            </a>
          </li>
          <?php
        } ?>
      </ul>
    </li>
    <li class="menu-item <?php echo is_active_page(['/store']) ? 'active' : ''; ?>">
      <a href="/store" class="menu-link">
        <i class="menu-icon tf-icons ti ti-shopping-cart"></i>
        <div>Store</div>
      </a>
    </li>
    <li class="menu-item  <?php echo is_active_page(['/help-center']) ? 'active' : ''; ?>">
      <a href="javascript:void(0);" class="menu-link menu-toggle">
        <i class="menu-icon tf-icons ti ti-messages"></i>
        <div>
          <?= $lang['help_center'] ?>
        </div>
      </a>
      <ul class="menu-sub">
        <li class="menu-item">
          <a href="/help-center" class="menu-link">
            <div>
              <?= $lang['home'] ?>
            </div>
          </a>
        </li>
        <li class="menu-item <?php echo is_active_page(['/help-center/tickets']) ? 'active' : ''; ?>">
          <a href="/help-center/tickets" class="menu-link">
            <div>
              <?= $lang['ticket'] ?>
            </div>
          </a>
        </li>
        <li class="menu-item <?php echo is_active_page(['/help-center/tos']) ? 'active' : ''; ?>">
          <a href="/help-center/tos" class="menu-link">
            <div>
              <?= $lang['terms_of_service'] ?>
            </div>
          </a>
        </li>
        <li class="menu-item <?php echo is_active_page(['/help-center/pp']) ? 'active' : ''; ?>">
          <a href="/help-center/pp" class="menu-link">
            <div>
              <?= $lang['privacy_policy'] ?>
            </div>
          </a>
        </li>
      </ul>
    </li>
    <li class="menu-item <?php echo is_active_page(['/leaderboard']) ? 'active' : ''; ?>">
      <a href="/leaderboard" class="menu-link">
        <i class="menu-icon tf-icons ti ti-star"></i>
        <div>
          <?= $lang['leaderboard'] ?>
        </div>
      </a>
    </li>
    <li class="menu-item <?php echo is_active_page(['/users/list']) ? 'active' : ''; ?>">
      <a href="/users/list" class="menu-link">
        <i class="menu-icon tf-icons ti ti-users"></i>
        <div>
          <?= $lang['users'] ?>
        </div>
      </a>
    </li>
    <li class="menu-header small text-uppercase">
      <span class="menu-header-text">
        <?= $lang['sidebar_links'] ?>
      </span>
    </li>
    <?php
    $websiteSetting = SettingsManager::getSetting('website');
    if ($websiteSetting !== "none" && $websiteSetting !== "") {
      ?>
      <li class="menu-item ">
        <a href="<?= $websiteSetting ?>" target="_blank" class="menu-link">
          <i class="menu-icon tf-icons ti ti-globe"></i>
          <div>WebSite</div>
        </a>
      </li>
      <?php
    } ?>
    <li class="menu-item ">
      <a href="<?= SettingsManager::getSetting('PterodactylURL') ?>" target="_blank" class="menu-link">
        <i class="menu-icon tf-icons ti ti-feather"></i>
        <div>Pterodactyl Panel</div>
      </a>
    </li>
    <li class="menu-item ">
      <a href="<?= SettingsManager::getSetting('discord_invite') ?>" target="_blank" class="menu-link">
        <i class="menu-icon tf-icons ti ti-brand-discord"></i>
        <div>Discord Server</div>
      </a>
    </li>
    <?php
    $statusSetting = SettingsManager::getSetting('status');
    if ($statusSetting !== "none" && $statusSetting !== "") {
      ?>
      <li class="menu-item ">
        <a href="<?= $statusSetting ?>" target="_blank" class="menu-link">
          <i class="menu-icon tf-icons ti ti-clock"></i>
          <div>Status Page</div>
        </a>
      </li>
      <?php
    } ?>
    <?php
    $twitterSetting = SettingsManager::getSetting('x');
    if ($twitterSetting !== "none" && $twitterSetting !== "") {
      ?>
      <li class="menu-item ">
        <a href="<?= $twitterSetting ?>" target="_blank" class="menu-link">
          <i class="menu-icon tf-icons ti ti-brand-twitter"></i>
          <div>Twitter (X)</div>
        </a>
      </li>
      <?php
    } ?>
    <?php
    if ($session->getUserInfo("role") == "Administrator") {
      ?>
      <li class="menu-header small text-uppercase">
        <span class="menu-header-text">Administration Tools</span>
      </li>
      <li class="menu-item <?php echo is_active_page(['/admin/overview']) ? 'active' : ''; ?>">
        <a href="/admin/overview" class="menu-link">
          <i class="menu-icon tf-icons ti ti-home"></i>
          <div>Overview</div>
        </a>
      </li>
      <li class="menu-item <?php echo is_active_page(['/admin/health']) ? 'active' : ''; ?>">
        <a href="/admin/health" class="menu-link">
          <i class="menu-icon tf-icons ti ti-heart"></i>
          <div>Health</div>
        </a>
      </li>
      <li class="menu-item <?php echo is_active_page(['/admin/api']) ? 'active' : ''; ?>">
        <a href="/admin/api" class="menu-link">
          <i class="menu-icon tf-icons ti ti-device-gamepad-2"></i>
          <div>Application API</div>
        </a>
      </li>
      <li
        class="menu-item <?php echo is_active_page(['/admin/users', '/admin/users/edit', '/admin/users/new']) ? 'active' : ''; ?>">
        <a href="/admin/users" class="menu-link">
          <i class="menu-icon tf-icons ti ti-users"></i>
          <div>Users</div>
        </a>
      </li>
      <li
        class="menu-item  <?php echo is_active_page(['/admin/servers', '/admin/servers/logs', '/admin/servers/queue']) ? 'active' : ''; ?>">
        <a href="javascript:void(0);" class="menu-link menu-toggle">
          <i class="menu-icon tf-icons ti ti-server"></i>
          <div>Servers</div>
        </a>
        <ul class="menu-sub">
          <li class="menu-item <?php echo is_active_page(['/admin/servers/list']) ? 'active' : ''; ?>">
            <a href="/admin/servers/list" class="menu-link">
              <div>Server List</div>
            </a>
          </li>
          <li class="menu-item <?php echo is_active_page(['/admin/servers/queue/list']) ? 'active' : ''; ?>">
            <a href="/admin/servers/queue/list" class="menu-link">
              <div>Queue List</div>
            </a>
          </li>
          <li class="menu-item <?php echo is_active_page(['/admin/servers/queue/logs']) ? 'active' : ''; ?>">
            <a href="/admin/servers/queue/logs" class="menu-link">
              <div>Queue Logs</div>
            </a>
          </li>
        </ul>
      <li class="menu-item <?php echo is_active_page(['/admin/redeem']) ? 'active' : ''; ?>">
        <a href="/admin/redeem" class="menu-link">
          <i class="menu-icon tf-icons ti ti-key"></i>
          <div>Redeem Keys</div>
        </a>
      </li>
      <li class="menu-item <?php echo is_active_page(['/admin/locations']) ? 'active' : ''; ?>">
        <a href="/admin/locations" class="menu-link">
          <i class="menu-icon tf-icons ti ti-flag"></i>
          <div>Locations</div>
        </a>
      </li>
      <li class="menu-item  <?php echo is_active_page(['/admin/eggs']) ? 'active' : ''; ?>">
        <a href="javascript:void(0);" class="menu-link menu-toggle">
          <i class="menu-icon tf-icons ti ti-egg"></i>
          <div>Eggs</div>
        </a>
        <ul class="menu-sub">
          <li class="menu-item <?php echo is_active_page(['/admin/eggs/list']) ? 'active' : ''; ?>">
            <a href="/admin/eggs/list" class="menu-link">
              <div>Eggs List</div>
            </a>
          </li>
          <li class="menu-item <?php echo is_active_page(['/admin/eggs/config']) ? 'active' : ''; ?>">
            <a href="/admin/eggs/config" class="menu-link">
              <div>Config List</div>
            </a>
          </li>
        </ul>
      </li>
      <li class="menu-item <?php echo is_active_page(['/admin/settings']) ? 'active' : ''; ?>">
        <a href="/admin/settings" class="menu-link">
          <i class="menu-icon tf-icons ti ti-settings"></i>
          <div>Settings</div>
        </a>
      </li>
      <?php
    }
    ?>
    <?php
    if ($session->getUserInfo("role") == "Support" || $session->getUserInfo("role") == "Administrator") {
      ?>
      <li class="menu-header small text-uppercase">
        <span class="menu-header-text">Support Tools</span>
      </li>
      <li class="menu-item <?php echo is_active_page(['/admin/tickets']) ? 'active' : ''; ?>">
        <a href="/admin/tickets" class="menu-link">
          <i class="menu-icon tf-icons ti ti-messages"></i>
          <div>Tickets</div>
        </a>
      </li>
      <?php
    }
    ?>
  </ul>
</aside>