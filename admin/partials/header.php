<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - JobYaari Blog</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- Quill Editor -->
    <link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>

<!-- Sidebar -->
<aside class="admin-sidebar" id="adminSidebar">
    <div class="sidebar-brand">
        <i class="fas fa-briefcase"></i>
        <span>Job<strong>Yaari</strong></span>
    </div>
    
    <nav class="sidebar-nav">
        <div class="nav-section">
            <span class="nav-label">Main</span>
            <a href="dashboard.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : ''; ?>">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
        </div>
        
        <div class="nav-section">
            <span class="nav-label">Content</span>
            <a href="blogs.php" class="nav-item <?php echo in_array(basename($_SERVER['PHP_SELF']), ['blogs.php','blog-add.php','blog-edit.php']) ? 'active' : ''; ?>">
                <i class="fas fa-file-alt"></i> All Blogs
            </a>
            <a href="blog-add.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'blog-add.php' ? 'active' : ''; ?>">
                <i class="fas fa-plus-circle"></i> Add New Blog
            </a>
            <a href="categories.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'categories.php' ? 'active' : ''; ?>">
                <i class="fas fa-tags"></i> Categories
            </a>
        </div>
        
        <div class="nav-section">
            <span class="nav-label">System</span>
            <a href="<?php echo SITE_URL; ?>" target="_blank" class="nav-item">
                <i class="fas fa-external-link-alt"></i> View Website
            </a>
            <a href="logout.php" class="nav-item nav-logout">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </nav>
</aside>

<!-- Main Content -->
<div class="admin-main" id="adminMain">
    <!-- Top Bar -->
    <header class="admin-topbar">
        <button class="sidebar-toggle" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>
        <div class="topbar-right">
            <span class="admin-user">
                <i class="fas fa-user-circle"></i>
                <?php echo sanitize($_SESSION['admin_username'] ?? 'Admin'); ?>
            </span>
            <a href="logout.php" class="btn btn-sm btn-logout">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </header>
    
    <!-- Page Content -->
    <div class="admin-content">
