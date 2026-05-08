<div class="admin-topbar">
  <button class="sidebar-toggle" id="sidebarToggle">☰</button>
  <div class="topbar-right">
    <span class="topbar-user">👤 <?= sanitize($_SESSION['admin_user'] ?? 'Admin') ?></span>
    <a href="logout.php" class="topbar-logout">Logout</a>
  </div>
</div>
