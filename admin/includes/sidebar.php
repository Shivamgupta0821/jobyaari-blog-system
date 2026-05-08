<?php $current = basename($_SERVER['PHP_SELF']); ?>
<aside class="admin-sidebar">
  <div class="sidebar-logo">
    ⚡ Job<span class="accent">Yaari</span>
    <small>Admin</small>
  </div>
  <nav class="sidebar-nav">
    <a href="index.php" class="nav-item <?= $current==='index.php'?'active':'' ?>">
      <span>🏠</span> Dashboard
    </a>
    <a href="blogs.php" class="nav-item <?= in_array($current,['blogs.php','add-blog.php','edit-blog.php'])?'active':'' ?>">
      <span>📝</span> All Blogs
    </a>
    <a href="add-blog.php" class="nav-item <?= $current==='add-blog.php'?'active':'' ?>">
      <span>➕</span> Add New Blog
    </a>
    <div class="nav-divider"></div>
    <a href="../public/index.php" target="_blank" class="nav-item">
      <span>🌐</span> View Website
    </a>
    <a href="logout.php" class="nav-item nav-logout">
      <span>🚪</span> Logout
    </a>
  </nav>
</aside>
