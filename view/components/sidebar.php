<?php
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
        <?= $settings['name'] ?>
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
    <li class="menu-item <?php echo is_active_page(['/help-center']) ? 'active' : ''; ?>">
      <a href="/help-center" class="menu-link">
        <i class="menu-icon tf-icons ti ti-messages"></i>
        <div>Help-Center</div>
      </a>
    </li>
    <?php
    if ($userdb['role'] == "Administrator") {
      ?>
      <li class="menu-header small text-uppercase">
        <span class="menu-header-text">Administration Tools</span>
      </li>
      <li class="menu-item <?php echo is_active_page(['/admin/users/view', '/admin/users/edit','/admin/users/new']) ? 'active' : ''; ?>">
        <a href="javascript:void(0);" class="menu-link menu-toggle">
          <i class="menu-icon tf-icons ti ti-users"></i>
          <div>Users</div>
        </a>
        <ul class="menu-sub">
          <li class="menu-item <?php echo is_active_page(['/admin/users/view']) ? 'active' : ''; ?>">
            <a href="/admin/users/view" class="menu-link">
              <div>List</div>
            </a>
          </li>
          <li class="menu-item <?php echo is_active_page(['/admin/users/new']) ? 'active' : ''; ?>">
            <a href="/admin/users/new" class="menu-link">
              <div>New</div>
            </a>
          </li>
        </ul>
      </li>
      <?php
    }
    ?>
  </ul>
</aside>