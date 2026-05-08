<?php
require_once '../config.php';

$slug = $_GET['slug'] ?? '';
if (!$slug) { header('Location: index.php'); exit; }

$stmt = db()->prepare("SELECT b.*, c.name as cat_name, c.slug as cat_slug
    FROM blogs b JOIN categories c ON b.category_id = c.id
    WHERE b.slug = ? AND b.status = 'published'");
$stmt->execute([$slug]);
$blog = $stmt->fetch();
if (!$blog) { header('Location: index.php'); exit; }

// Related blogs
$rel = db()->prepare("SELECT b.*, c.name as cat_name, c.slug as cat_slug
    FROM blogs b JOIN categories c ON b.category_id = c.id
    WHERE b.category_id = ? AND b.id != ? AND b.status = 'published'
    ORDER BY b.created_at DESC LIMIT 3");
$rel->execute([$blog['category_id'], $blog['id']]);
$related = $rel->fetchAll();

$cats = db()->query("SELECT * FROM categories ORDER BY name")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= sanitize($blog['title']) ?> | <?= SITE_NAME ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Tiro+Devanagari+Hindi:ital@0;1&family=DM+Sans:wght@300;400;500;600;700&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<nav class="navbar">
  <div class="container nav-inner">
    <a href="index.php" class="logo">
      <span class="logo-icon">⚡</span>
      <span class="logo-text">Job<span class="accent">Yaari</span></span>
    </a>
    <button class="hamburger" id="hamburger">&#9776;</button>
    <ul class="nav-links" id="navLinks">
      <li><a href="index.php">Home</a></li>
      <?php foreach ($cats as $c): ?>
      <li><a href="index.php?category=<?= $c['slug'] ?>"><?= sanitize($c['name']) ?></a></li>
      <?php endforeach; ?>
    </ul>
  </div>
</nav>

<div class="blog-detail-page">
  <div class="container">
    <div class="breadcrumb">
      <a href="index.php">Home</a> &rsaquo;
      <a href="index.php?category=<?= sanitize($blog['cat_slug']) ?>"><?= sanitize($blog['cat_name']) ?></a> &rsaquo;
      <span><?= sanitize($blog['title']) ?></span>
    </div>

    <div class="detail-layout">
      <article class="detail-main">
        <span class="badge badge-<?= sanitize($blog['cat_slug']) ?>"><?= sanitize($blog['cat_name']) ?></span>
        <h1 class="detail-title"><?= sanitize($blog['title']) ?></h1>
        <div class="detail-meta">
          <span>📅 <?= date('d M Y', strtotime($blog['created_at'])) ?></span>
          <span>🕐 <?= date('h:i A', strtotime($blog['created_at'])) ?></span>
        </div>

        <?php if ($blog['image'] && file_exists(UPLOAD_DIR . $blog['image'])): ?>
        <div class="detail-img-wrap">
          <img src="<?= UPLOAD_URL . sanitize($blog['image']) ?>" alt="<?= sanitize($blog['title']) ?>" class="detail-img">
        </div>
        <?php endif; ?>

        <div class="detail-content">
          <?= $blog['content'] /* Already sanitized at input, HTML allowed */ ?>
        </div>

        <div class="share-section">
          <span>Share:</span>
          <a href="https://wa.me/?text=<?= urlencode($blog['title'] . ' ' . SITE_URL . '/public/blog.php?slug=' . $blog['slug']) ?>" target="_blank" class="share-btn whatsapp">WhatsApp</a>
          <a href="https://twitter.com/intent/tweet?text=<?= urlencode($blog['title']) ?>&url=<?= urlencode(SITE_URL . '/public/blog.php?slug=' . $blog['slug']) ?>" target="_blank" class="share-btn twitter">Twitter</a>
        </div>
      </article>

      <aside class="detail-sidebar">
        <div class="sidebar-widget">
          <h3>Categories</h3>
          <ul class="sidebar-cats">
            <?php foreach ($cats as $c): ?>
            <li><a href="index.php?category=<?= $c['slug'] ?>"><?= sanitize($c['name']) ?></a></li>
            <?php endforeach; ?>
          </ul>
        </div>

        <?php if ($related): ?>
        <div class="sidebar-widget">
          <h3>Related Posts</h3>
          <?php foreach ($related as $r): ?>
          <div class="sidebar-post">
            <span class="sidebar-date"><?= date('d M Y', strtotime($r['created_at'])) ?></span>
            <a href="blog.php?slug=<?= urlencode($r['slug']) ?>"><?= sanitize($r['title']) ?></a>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </aside>
    </div>
  </div>
</div>

<footer class="footer">
  <div class="container">
    <div class="footer-bottom">
      <p>&copy; <?= date('Y') ?> JobYaari. All rights reserved. | <a href="index.php">Back to Home</a></p>
    </div>
  </div>
</footer>
<script src="js/main.js"></script>
</body>
</html>
