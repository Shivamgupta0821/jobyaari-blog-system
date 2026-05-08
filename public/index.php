<?php
require_once '../config.php';

// Get categories
$cats = db()->query("SELECT * FROM categories ORDER BY name")->fetchAll();

// Filters
$cat_slug = $_GET['category'] ?? '';
$search   = $_GET['search'] ?? '';
$date     = $_GET['date'] ?? '';

$where = ["b.status = 'published'"];
$params = [];

if ($cat_slug) {
    $where[] = "c.slug = :cat";
    $params[':cat'] = $cat_slug;
}
if ($search) {
    $where[] = "(b.title LIKE :search OR b.short_description LIKE :search2)";
    $params[':search']  = "%$search%";
    $params[':search2'] = "%$search%";
}
if ($date) {
    $where[] = "DATE(b.created_at) = :date";
    $params[':date'] = $date;
}

$sql = "SELECT b.*, c.name as cat_name, c.slug as cat_slug
        FROM blogs b
        JOIN categories c ON b.category_id = c.id
        WHERE " . implode(' AND ', $where) . "
        ORDER BY b.created_at DESC";

$stmt = db()->prepare($sql);
$stmt->execute($params);
$blogs = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= SITE_NAME ?> - Latest Jobs, Results & Admit Cards</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Tiro+Devanagari+Hindi:ital@0;1&family=DM+Sans:wght@300;400;500;600;700&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<link rel="stylesheet" href="css/style.css">
</head>
<body>

<!-- NAVBAR -->
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

<!-- HERO -->
<section class="hero">
  <div class="container">
    <div class="hero-tag">🔔 India's #1 Government Job Portal</div>
    <h1 class="hero-title">Sarkari Jobs, <span class="accent">Results</span> & Admit Cards</h1>
    <p class="hero-sub">Latest updates on SSC, UPSC, IBPS, Railway & State Govt Jobs</p>
    <form class="search-bar" id="searchForm">
      <input type="text" id="searchInput" name="search" placeholder="Search jobs, results, admit cards..." value="<?= sanitize($search) ?>">
      <button type="submit">Search</button>
    </form>
  </div>
  <div class="hero-ticker">
    <span class="ticker-label">🔴 LIVE</span>
    <div class="ticker-track">
      <?php foreach ($blogs as $b): ?><span><?= sanitize($b['title']) ?> &nbsp;|&nbsp; </span><?php endforeach; ?>
    </div>
  </div>
</section>

<!-- FILTER BAR -->
<div class="filter-bar" id="filterBar">
  <div class="container">
    <div class="filter-scroll">
      <button class="filter-btn <?= !$cat_slug ? 'active' : '' ?>" data-cat="">All Posts</button>
      <?php foreach ($cats as $c): ?>
      <button class="filter-btn <?= $cat_slug === $c['slug'] ? 'active' : '' ?>"
              data-cat="<?= $c['slug'] ?>"><?= sanitize($c['name']) ?></button>
      <?php endforeach; ?>
      <div class="date-filter-wrap">
        <input type="date" id="dateFilter" class="date-input" value="<?= sanitize($date) ?>" placeholder="Filter by date">
      </div>
    </div>
  </div>
</div>

<!-- BLOGS GRID -->
<main class="main-content">
  <div class="container">
    <div class="section-header">
      <h2><?= $cat_slug ? sanitize(array_column($cats, 'name', 'slug')[$cat_slug] ?? 'Posts') : 'Latest Updates' ?></h2>
      <span class="post-count" id="postCount"><?= count($blogs) ?> posts</span>
    </div>
    <div class="blogs-grid" id="blogsGrid">
      <?php if (empty($blogs)): ?>
      <div class="no-posts">
        <div class="no-posts-icon">📭</div>
        <h3>No posts found</h3>
        <p>Try a different filter or search term.</p>
      </div>
      <?php else: ?>
      <?php foreach ($blogs as $b): ?>
      <article class="blog-card" data-category="<?= sanitize($b['cat_slug']) ?>">
        <a href="blog.php?slug=<?= urlencode($b['slug']) ?>" class="card-img-link">
          <?php if ($b['image'] && file_exists(UPLOAD_DIR . $b['image'])): ?>
          <img src="<?= UPLOAD_URL . sanitize($b['image']) ?>" alt="<?= sanitize($b['title']) ?>" class="card-img" loading="lazy">
          <?php else: ?>
          <div class="card-img-placeholder">
            <span class="placeholder-cat"><?= sanitize($b['cat_name']) ?></span>
          </div>
          <?php endif; ?>
        </a>
        <div class="card-body">
          <span class="badge badge-<?= sanitize($b['cat_slug']) ?>"><?= sanitize($b['cat_name']) ?></span>
          <h3 class="card-title"><a href="blog.php?slug=<?= urlencode($b['slug']) ?>"><?= sanitize($b['title']) ?></a></h3>
          <p class="card-desc"><?= sanitize($b['short_description']) ?></p>
          <div class="card-footer">
            <span class="card-date">📅 <?= date('d M Y', strtotime($b['created_at'])) ?></span>
            <a href="blog.php?slug=<?= urlencode($b['slug']) ?>" class="read-more">Read More →</a>
          </div>
        </div>
      </article>
      <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</main>

<!-- FOOTER -->
<footer class="footer">
  <div class="container">
    <div class="footer-grid">
      <div>
        <div class="footer-logo">⚡ Job<span class="accent">Yaari</span></div>
        <p>Your trusted source for government job updates, results and admit cards.</p>
      </div>
      <div>
        <h4>Categories</h4>
        <ul>
          <?php foreach ($cats as $c): ?>
          <li><a href="index.php?category=<?= $c['slug'] ?>"><?= sanitize($c['name']) ?></a></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <div>
        <h4>Quick Links</h4>
        <ul>
          <li><a href="index.php">Home</a></li>
          <li><a href="../admin/">Admin Panel</a></li>
        </ul>
      </div>
    </div>
    <div class="footer-bottom">
      <p>&copy; <?= date('Y') ?> JobYaari. All rights reserved.</p>
    </div>
  </div>
</footer>

<script src="js/main.js"></script>
</body>
</html>
