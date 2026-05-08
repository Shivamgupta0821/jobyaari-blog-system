<?php
require_once '../config.php';
if (!isset($_SESSION['admin_id'])) { header('Location: login.php'); exit; }

$blogs = db()->query("SELECT b.*, c.name as cat_name FROM blogs b JOIN categories c ON b.category_id = c.id ORDER BY b.created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Blogs | JobYaari Admin</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/admin.css">
</head>
<body>
<?php include 'includes/sidebar.php'; ?>
<main class="admin-main">
  <?php include 'includes/topbar.php'; ?>
  <div class="admin-content">
    <div class="section-header-row">
      <h1 class="page-title">All Blogs (<?= count($blogs) ?>)</h1>
      <a href="add-blog.php" class="btn btn-primary">+ Add New Blog</a>
    </div>

    <div class="admin-section">
      <div class="table-search">
        <input type="text" id="tableSearch" class="form-control" placeholder="Search blogs...">
      </div>
      <div class="table-wrap">
        <table class="admin-table" id="blogTable">
          <thead>
            <tr><th>#</th><th>Image</th><th>Title</th><th>Category</th><th>Status</th><th>Date</th><th>Actions</th></tr>
          </thead>
          <tbody>
            <?php foreach ($blogs as $i => $b): ?>
            <tr>
              <td><?= $i+1 ?></td>
              <td>
                <?php if ($b['image'] && file_exists(UPLOAD_DIR . $b['image'])): ?>
                <img src="<?= UPLOAD_URL . sanitize($b['image']) ?>" style="width:60px;height:45px;object-fit:cover;border-radius:6px;">
                <?php else: ?>
                <div style="width:60px;height:45px;background:#eee;border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:1.2rem;">📄</div>
                <?php endif; ?>
              </td>
              <td class="blog-title-cell"><?= sanitize($b['title']) ?></td>
              <td><span class="table-badge"><?= sanitize($b['cat_name']) ?></span></td>
              <td><span class="status-badge <?= $b['status'] ?>"><?= ucfirst($b['status']) ?></span></td>
              <td><?= date('d M Y', strtotime($b['created_at'])) ?></td>
              <td class="actions-cell">
                <a href="edit-blog.php?id=<?= $b['id'] ?>" class="btn-sm btn-edit">Edit</a>
                <a href="../public/blog.php?slug=<?= urlencode($b['slug']) ?>" target="_blank" class="btn-sm btn-view">View</a>
                <a href="delete-blog.php?id=<?= $b['id'] ?>" class="btn-sm btn-delete" onclick="return confirm('Delete this blog permanently?')">Delete</a>
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
<script>
document.getElementById('tableSearch').addEventListener('input', function() {
  const q = this.value.toLowerCase();
  document.querySelectorAll('#blogTable tbody tr').forEach(row => {
    row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
  });
});
</script>
</body>
</html>
