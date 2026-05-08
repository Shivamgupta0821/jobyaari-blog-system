<?php
require_once __DIR__ . '/includes/config.php';
$db = getDB();

$slug = isset($_GET['slug']) ? sanitize($_GET['slug']) : '';

if (empty($slug)) {
    redirect(SITE_URL);
}

// Fetch blog
$stmt = $db->prepare("
    SELECT b.*, c.name AS category_name, c.slug AS category_slug 
    FROM blogs b 
    LEFT JOIN categories c ON b.category_id = c.id 
    WHERE b.slug = ? AND b.status = 'published'
");
$stmt->execute([$slug]);
$blog = $stmt->fetch();

if (!$blog) {
    redirect(SITE_URL);
}

// Increment views
$db->prepare("UPDATE blogs SET views = views + 1 WHERE id = ?")->execute([$blog['id']]);

// Related blogs
$related = $db->prepare("
    SELECT b.*, c.name AS category_name 
    FROM blogs b 
    LEFT JOIN categories c ON b.category_id = c.id 
    WHERE b.category_id = ? AND b.id != ? AND b.status = 'published' 
    LIMIT 3
");
$related->execute([$blog['category_id'], $blog['id']]);
$relatedBlogs = $related->fetchAll();

$categories = $db->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo sanitize($blog['title']); ?> - JobYaari Blog</title>
    <meta name="description" content="<?php echo sanitize($blog['short_description']); ?>">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body>

<!-- Header -->
<header class="site-header">
    <div class="header-inner container">
        <div class="logo">
            <a href="index.php">
                <span class="logo-icon"><i class="fas fa-briefcase"></i></span>
                <span class="logo-text">Job<strong>Yaari</strong></span>
            </a>
        </div>
        <nav class="main-nav">
            <a href="index.php">Home</a>
            <a href="index.php?category=latest-jobs">Jobs</a>
            <a href="index.php?category=results">Results</a>
            <a href="index.php?category=admit-card">Admit Cards</a>
        </nav>
        <div class="header-search">
            <input type="text" id="searchInput" placeholder="Search blogs..." autocomplete="off">
            <i class="fas fa-search"></i>
        </div>
        <button class="hamburger" id="hamburger">
            <span></span><span></span><span></span>
        </button>
    </div>
    <div class="mobile-nav" id="mobileNav">
        <a href="index.php">Home</a>
        <a href="index.php?category=latest-jobs">Jobs</a>
        <a href="index.php?category=results">Results</a>
        <a href="index.php?category=admit-card">Admit Cards</a>
    </div>
</header>

<!-- Breadcrumb -->
<div class="breadcrumb-bar">
    <div class="container">
        <a href="index.php"><i class="fas fa-home"></i> Home</a>
        <span class="sep"><i class="fas fa-chevron-right"></i></span>
        <?php if ($blog['category_slug']): ?>
        <a href="index.php?category=<?php echo $blog['category_slug']; ?>"><?php echo sanitize($blog['category_name']); ?></a>
        <span class="sep"><i class="fas fa-chevron-right"></i></span>
        <?php endif; ?>
        <span class="current"><?php echo sanitize(substr($blog['title'], 0, 50)) . (strlen($blog['title']) > 50 ? '...' : ''); ?></span>
    </div>
</div>

<!-- Blog Detail -->
<main class="main-content">
    <div class="container">
        <div class="content-layout">
            <!-- Article -->
            <article class="blog-article">
                <!-- Category Badge -->
                <?php if ($blog['category_name']): ?>
                <div class="article-category-badge">
                    <a href="index.php?category=<?php echo $blog['category_slug']; ?>"><?php echo sanitize($blog['category_name']); ?></a>
                </div>
                <?php endif; ?>
                
                <!-- Title -->
                <h1 class="article-title"><?php echo sanitize($blog['title']); ?></h1>
                
                <!-- Meta -->
                <div class="article-meta">
                    <span><i class="fas fa-calendar-alt"></i> <?php echo formatDate($blog['created_at']); ?></span>
                    <span><i class="fas fa-eye"></i> <?php echo number_format($blog['views']); ?> views</span>
                    <?php if ($blog['category_name']): ?>
                    <span><i class="fas fa-folder"></i> <?php echo sanitize($blog['category_name']); ?></span>
                    <?php endif; ?>
                </div>
                
                <!-- Featured Image -->
                <?php if ($blog['image']): ?>
                <div class="article-featured-image">
                    <img src="<?php echo UPLOAD_URL . sanitize($blog['image']); ?>" alt="<?php echo sanitize($blog['title']); ?>" loading="lazy">
                </div>
                <?php else: ?>
                <div class="article-image-placeholder">
                    <i class="fas fa-newspaper"></i>
                </div>
                <?php endif; ?>
                
                <!-- Short Description -->
                <?php if ($blog['short_description']): ?>
                <div class="article-summary">
                    <p><?php echo sanitize($blog['short_description']); ?></p>
                </div>
                <?php endif; ?>
                
                <!-- Full Content -->
                <div class="article-body">
                    <?php echo $blog['content']; ?>
                </div>
                
                <!-- Share -->
                <div class="article-share">
                    <span>Share this:</span>
                    <a href="https://wa.me/?text=<?php echo urlencode($blog['title'] . ' - ' . SITE_URL . '/blog.php?slug=' . $blog['slug']); ?>" target="_blank" class="share-btn whatsapp">
                        <i class="fab fa-whatsapp"></i> WhatsApp
                    </a>
                    <a href="https://t.me/share/url?url=<?php echo urlencode(SITE_URL . '/blog.php?slug=' . $blog['slug']); ?>&text=<?php echo urlencode($blog['title']); ?>" target="_blank" class="share-btn telegram">
                        <i class="fab fa-telegram"></i> Telegram
                    </a>
                    <button onclick="copyLink()" class="share-btn copy">
                        <i class="fas fa-copy"></i> Copy Link
                    </button>
                </div>
            </article>
            
            <!-- Sidebar -->
            <aside class="sidebar">
                <div class="sidebar-widget">
                    <h3 class="widget-title">Categories</h3>
                    <ul class="cat-list">
                        <li><a href="index.php"><i class="fas fa-layer-group"></i> All Posts</a></li>
                        <?php foreach ($categories as $cat): ?>
                        <li><a href="index.php?category=<?php echo $cat['slug']; ?>">
                            <i class="fas fa-tag"></i> <?php echo sanitize($cat['name']); ?>
                        </a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                
                <?php if (!empty($relatedBlogs)): ?>
                <div class="sidebar-widget">
                    <h3 class="widget-title">Related Articles</h3>
                    <?php foreach ($relatedBlogs as $rel): ?>
                    <div class="related-item">
                        <a href="blog.php?slug=<?php echo $rel['slug']; ?>">
                            <?php if ($rel['image']): ?>
                            <img src="<?php echo UPLOAD_URL . sanitize($rel['image']); ?>" alt="<?php echo sanitize($rel['title']); ?>">
                            <?php else: ?>
                            <div class="rel-img-placeholder"><i class="fas fa-newspaper"></i></div>
                            <?php endif; ?>
                            <div class="related-info">
                                <span class="rel-title"><?php echo sanitize(substr($rel['title'], 0, 60)) . (strlen($rel['title']) > 60 ? '...' : ''); ?></span>
                                <span class="rel-date"><?php echo timeAgo($rel['created_at']); ?></span>
                            </div>
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </aside>
        </div>
    </div>
</main>

<!-- Footer -->
<footer class="site-footer">
    <div class="container footer-grid">
        <div class="footer-brand">
            <div class="logo">
                <span class="logo-icon"><i class="fas fa-briefcase"></i></span>
                <span class="logo-text">Job<strong>Yaari</strong></span>
            </div>
            <p>Your trusted platform for latest government job notifications, admit cards, and exam results.</p>
        </div>
        <div class="footer-links">
            <h4>Categories</h4>
            <?php foreach(array_slice($categories, 0, 5) as $cat): ?>
            <a href="index.php?category=<?php echo $cat['slug']; ?>"><?php echo sanitize($cat['name']); ?></a>
            <?php endforeach; ?>
        </div>
        <div class="footer-links">
            <h4>Quick Links</h4>
            <a href="index.php">Home</a>
            <a href="admin/login.php">Admin Panel</a>
            <a href="#">Privacy Policy</a>
        </div>
    </div>
    <div class="footer-bottom">
        <p>&copy; <?php echo date('Y'); ?> JobYaari Blog. All rights reserved.</p>
    </div>
</footer>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="public/js/main.js"></script>
<script>
function copyLink() {
    navigator.clipboard.writeText(window.location.href).then(() => {
        alert('Link copied to clipboard!');
    });
}

// Hamburger
$('#hamburger').click(function() {
    $('#mobileNav').toggleClass('open');
    $(this).toggleClass('active');
});

// Search redirect
$('#searchInput').on('keypress', function(e) {
    if (e.which === 13 && $(this).val().trim()) {
        window.location.href = 'index.php?search=' + encodeURIComponent($(this).val().trim());
    }
});
</script>
</body>
</html>
