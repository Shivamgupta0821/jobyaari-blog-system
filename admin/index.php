<?php
require_once '../config.php';

// Redirect to login if not authenticated
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$stats['blogs']  = db()->query("SELECT COUNT(*) FROM blogs")->fetchColumn();
$stats['draft']  = db()->query("SELECT COUNT(*) FROM blogs WHERE status='draft'")->fetchColumn();
$stats['pub']    = db()->query("SELECT COUNT(*) FROM blogs WHERE status='published'")->fetchColumn();
$stats['cats']   = db()->query("SELECT COUNT(*) FROM categories")->fetchColumn();

$recent = db()->query("SELECT b.*, c.name as cat_name FROM blogs b JOIN categories c ON b.category_id = c.id ORDER BY b.created_at DESC LIMIT 8")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Panel | JobYaari</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/admin.css">
</head>
<body>
<?php include 'includes/sidebar.php'; ?>
<main class="admin-main">
  <?php include 'includes/topbar.php'; ?>
  <div class="admin-content">
    <h1 class="page-title">Dashboard</h1>

    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon">📝</div>
        <div class="stat-info">
          <div class="stat-num"><?= $stats['blogs'] ?></div>
          <div class="stat-label">Total Blogs</div>
        </div>
      </div>
      <div class="stat-card success">
        <div class="stat-icon">✅</div>
        <div class="stat-info">
          <div class="stat-num"><?= $stats['pub'] ?></div>
          <div class="stat-label">Published</div>
        </div>
      </div>
      <div class="stat-card warning">
        <div class="stat-icon">📋</div>
        <div class="stat-info">
          <div class="stat-num"><?= $stats['draft'] ?></div>
          <div class="stat-label">Drafts</div>
        </div>
      </div>
      <div class="stat-card info">
        <div class="stat-icon">🏷️</div>
        <div class="stat-info">
          <div class="stat-num"><?= $stats['cats'] ?></div>
          <div class="stat-label">Categories</div>
        </div>
      </div>
    </div>

    <div class="admin-section">
      <div class="section-header-row">
        <h2>Recent Blogs</h2>
        <a href="add-blog.php" class="btn btn-primary">+ Add New Blog</a>
      </div>
      <div class="table-wrap">
        <table class="admin-table">
          <thead>
            <tr><th>Title</th><th>Category</th><th>Status</th><th>Date</th><th>Actions</th></tr>
          </thead>
          <tbody>
            <?php foreach ($recent as $b): ?>
            <tr>
              <td class="blog-title-cell"><?= sanitize($b['title']) ?></td>
              <td><span class="table-badge"><?= sanitize($b['cat_name']) ?></span></td>
              <td>
                <span class="status-badge <?= $b['status'] === 'published' ? 'published' : 'draft' ?>">
                  <?= ucfirst($b['status']) ?>
                </span>
              </td>
              <td><?= date('d M Y', strtotime($b['created_at'])) ?></td>
              <td class="actions-cell">
                <a href="edit-blog.php?id=<?= $b['id'] ?>" class="btn-sm btn-edit">Edit</a>
                <a href="../public/blog.php?slug=<?= urlencode($b['slug']) ?>" target="_blank" class="btn-sm btn-view">View</a>
                <a href="delete-blog.php?id=<?= $b['id'] ?>" class="btn-sm btn-delete" onclick="return confirm('Delete this blog?')">Delete</a>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</main>
<script src="js/admin.js"></script>
</body>
</html>
