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
      <span class="menu-header-text">Home</span>
    </li>
    <li class="menu-item <?php echo is_active_page(['/dashboard']) ? 'active' : ''; ?>">
      <a href="/dashboard" class="menu-link">
        <i class="menu-icon tf-icons ti ti-home"></i>
        <div>Dashboard</div>
      </a>
    </li>
    <li class="menu-item <?php echo is_active_page(['/server/create']) ? 'active' : ''; ?>">
      <a href="/server/create" class="menu-link">
        <i class="menu-icon tf-icons ti ti-server"></i>
        <div>New server</div>
      </a>
    </li>
    <li class="menu-item <?php echo is_active_page(['/earn', '/earn/afk', '/earn/redeem']) ? 'active' : ''; ?>">
      <a href="javascript:void(0);" class="menu-link menu-toggle">
        <i class="menu-icon tf-icons ti ti-currency-euro"></i>
        <div>Earning</div>
      </a>
      <ul class="menu-sub">
        <?php if (SettingsManager::getSetting("enable_afk") == "true") {
          ?>
          <li class="menu-item <?php echo is_active_page(['/earn/afk']) ? 'active' : ''; ?>">
            <a href="/earn/afk" class="menu-link">
              <div>Afk</div>
            </a>
          </li>
          <?php
        } ?>

        <li class="menu-item <?php echo is_active_page(['/earn/redeem']) ? 'active' : ''; ?>">
          <a href="/earn/redeem" class="menu-link">
            <div>Redeem</div>
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
        <div data-i18n="Help-Center">Help-Center</div>
      </a>
      <ul class="menu-sub">
        <li class="menu-item">
          <a href="/help-center" class="menu-link">
            <div data-i18n="Home">Home</div>
          </a>
        </li>
        <li class="menu-item <?php echo is_active_page(['/help-center/tickets']) ? 'active' : ''; ?>">
          <a href="/help-center/tickets" class="menu-link">
            <div data-i18n="Tickets">Tickets</div>
          </a>
        </li>
        <li class="menu-item <?php echo is_active_page(['/help-center/tos']) ? 'active' : ''; ?>">
          <a href="/help-center/tos" class="menu-link">
            <div data-i18n="Terms of service">Terms of Service</div>
          </a>
        </li>
        <li class="menu-item <?php echo is_active_page(['/help-center/pp']) ? 'active' : ''; ?>">
          <a href="/help-center/pp" class="menu-link">
            <div data-i18n="Privacy Policy">Privacy Policy</div>
          </a>
        </li>
      </ul>
    </li>
    <li class="menu-item <?php echo is_active_page(['/leaderboard']) ? 'active' : ''; ?>">
      <a href="/leaderboard" class="menu-link">
        <i class="menu-icon tf-icons ti ti-star"></i>
        <div>Leaderboard</div>
      </a>
    </li>
    <li class="menu-item <?php echo is_active_page(['/users/list']) ? 'active' : ''; ?>">
      <a href="/users/list" class="menu-link">
        <i class="menu-icon tf-icons ti ti-users"></i>
        <div>Users</div>
      </a>
    </li>
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
      <li class="menu-item  <?php echo is_active_page(['/admin/servers', '/admin/servers/logs', '/admin/servers/queue']) ? 'active' : ''; ?>">
        <a href="javascript:void(0);" class="menu-link menu-toggle">
          <i class="menu-icon tf-icons ti ti-server"></i>
          <div data-i18n="Servers">Servers</div>
        </a>
        <ul class="menu-sub">
          <li class="menu-item <?php echo is_active_page(['/admin/servers/list']) ? 'active' : ''; ?>">
            <a href="/admin/servers/list" class="menu-link">
              <div data-i18n="Servers List">Server List</div>
            </a>
          </li>
          <li class="menu-item <?php echo is_active_page(['/admin/servers/queue/list']) ? 'active' : ''; ?>">
            <a href="/admin/servers/queue/list" class="menu-link">
              <div data-i18n="Queue List">Queue List</div>
            </a>
          </li>
          <li class="menu-item <?php echo is_active_page(['/admin/servers/queue/logs']) ? 'active' : ''; ?>">
            <a href="/admin/servers/queue/logs" class="menu-link">
              <div data-i18n="Queue Logs">Queue Logs</div>
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
          <div data-i18n="Eggs">Eggs</div>
        </a>
        <ul class="menu-sub">
          <li class="menu-item <?php echo is_active_page(['/admin/eggs/list']) ? 'active' : ''; ?>">
            <a href="/admin/eggs/list" class="menu-link">
              <div data-i18n="Eggs List">Eggs List</div>
            </a>
          </li>
          <li class="menu-item <?php echo is_active_page(['/admin/eggs/config']) ? 'active' : ''; ?>">
            <a href="/admin/eggs/config" class="menu-link">
              <div data-i18n="Config List">Config List</div>
            </a>
          </li>
        </ul>
      </li>
      <li class="menu-item <?php echo is_active_page(['/admin/tickets']) ? 'active' : ''; ?>">
        <a href="/admin/tickets" class="menu-link">
          <i class="menu-icon tf-icons ti ti-messages"></i>
          <div>Tickets</div>
        </a>
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
    if ($session->getUserInfo("role") == "Support") {
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