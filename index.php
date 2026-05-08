<?php
require_once __DIR__ . '/includes/config.php';
$db = getDB();

// Fetch categories for filter
$categories = $db->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

// Get page title
$pageTitle = "JobYaari Blog - Latest Government Jobs, Admit Cards & Results";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <meta name="description" content="Stay updated with latest government jobs, admit cards, results, and exam notifications on JobYaari Blog.">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,400&display=swap" rel="stylesheet">
    
    <!-- Icons -->
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
            <a href="index.php" class="active">Home</a>
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
    <!-- Mobile Nav -->
    <div class="mobile-nav" id="mobileNav">
        <a href="index.php">Home</a>
        <a href="index.php?category=latest-jobs">Jobs</a>
        <a href="index.php?category=results">Results</a>
        <a href="index.php?category=admit-card">Admit Cards</a>
    </div>
</header>

<!-- Hero Section -->
<section class="hero">
    <div class="hero-bg"></div>
    <div class="container hero-content">
        <div class="hero-badge"><i class="fas fa-bolt"></i> #1 Job Alert Platform</div>
        <h1>Find Your Dream<br><span class="accent">Government Job</span></h1>
        <p>Stay updated with latest notifications, admit cards, results & more</p>
        <div class="hero-search">
            <input type="text" id="heroSearch" placeholder="Search jobs, results, admit cards...">
            <button onclick="triggerSearch()"><i class="fas fa-search"></i> Search</button>
        </div>
        <div class="hero-stats">
            <div class="stat"><strong id="totalBlogs">0</strong><span>Articles</span></div>
            <div class="stat"><strong>50K+</strong><span>Readers</span></div>
            <div class="stat"><strong>Daily</strong><span>Updates</span></div>
        </div>
    </div>
</section>

<!-- Filter Bar -->
<section class="filter-section">
    <div class="container">
        <div class="filter-bar">
            <div class="filter-tabs" id="filterTabs">
                <button class="filter-tab active" data-category="">All Posts</button>
                <?php foreach ($categories as $cat): ?>
                <button class="filter-tab" data-category="<?php echo $cat['slug']; ?>">
                    <?php echo sanitize($cat['name']); ?>
                </button>
                <?php endforeach; ?>
            </div>
            <div class="filter-right">
                <select id="dateFilter" class="date-filter">
                    <option value="">All Time</option>
                    <option value="today">Today</option>
                    <option value="week">This Week</option>
                    <option value="month">This Month</option>
                    <option value="year">This Year</option>
                </select>
            </div>
        </div>
    </div>
</section>

<!-- Blog Grid -->
<main class="main-content">
    <div class="container">
        <div class="content-layout">
            <!-- Blogs -->
            <div class="blogs-area">
                <div class="section-header">
                    <h2 id="sectionTitle">Latest Articles</h2>
                    <span class="result-count" id="resultCount"></span>
                </div>
                
                <!-- Loading Spinner -->
                <div class="loading-spinner" id="loadingSpinner">
                    <div class="spinner"></div>
                    <p>Loading articles...</p>
                </div>
                
                <!-- Blog Grid -->
                <div class="blog-grid" id="blogGrid">
                    <!-- Blogs will be loaded via AJAX -->
                </div>
                
                <!-- No results -->
                <div class="no-results" id="noResults" style="display:none;">
                    <i class="fas fa-search"></i>
                    <h3>No articles found</h3>
                    <p>Try different filters or search terms</p>
                    <button onclick="resetFilters()" class="btn-reset">Clear Filters</button>
                </div>
                
                <!-- Pagination -->
                <div class="pagination" id="pagination"></div>
            </div>
            
            <!-- Sidebar -->
            <aside class="sidebar">
                <div class="sidebar-widget">
                    <h3 class="widget-title">Categories</h3>
                    <ul class="cat-list">
                        <li><a href="#" onclick="filterByCategory(''); return false;">
                            <i class="fas fa-layer-group"></i> All Posts
                            <span class="cat-count" id="count-all"></span>
                        </a></li>
                        <?php foreach ($categories as $cat): ?>
                        <li><a href="#" onclick="filterByCategory('<?php echo $cat['slug']; ?>'); return false;">
                            <i class="fas fa-tag"></i> <?php echo sanitize($cat['name']); ?>
                            <span class="cat-count" id="count-<?php echo $cat['slug']; ?>"></span>
                        </a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                
                <div class="sidebar-widget">
                    <h3 class="widget-title">Quick Links</h3>
                    <ul class="quick-links">
                        <li><a href="#"><i class="fas fa-file-alt"></i> Latest Notifications</a></li>
                        <li><a href="#"><i class="fas fa-id-card"></i> Download Admit Cards</a></li>
                        <li><a href="#"><i class="fas fa-trophy"></i> Check Results</a></li>
                        <li><a href="#"><i class="fas fa-book"></i> Exam Syllabus</a></li>
                        <li><a href="#"><i class="fas fa-key"></i> Answer Keys</a></li>
                    </ul>
                </div>
                
                <div class="sidebar-widget newsletter-widget">
                    <h3 class="widget-title">Get Job Alerts</h3>
                    <p>Subscribe to receive daily job notifications</p>
                    <input type="email" placeholder="Your email address">
                    <button class="btn-subscribe">Subscribe <i class="fas fa-paper-plane"></i></button>
                </div>
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
            <div class="social-links">
                <a href="#"><i class="fab fa-telegram"></i></a>
                <a href="#"><i class="fab fa-whatsapp"></i></a>
                <a href="#"><i class="fab fa-youtube"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
            </div>
        </div>
        <div class="footer-links">
            <h4>Categories</h4>
            <?php foreach(array_slice($categories, 0, 5) as $cat): ?>
            <a href="#" onclick="filterByCategory('<?php echo $cat['slug']; ?>'); return false;">
                <?php echo sanitize($cat['name']); ?>
            </a>
            <?php endforeach; ?>
        </div>
        <div class="footer-links">
            <h4>Quick Links</h4>
            <a href="index.php">Home</a>
            <a href="admin/login.php">Admin Panel</a>
            <a href="#">About Us</a>
            <a href="#">Privacy Policy</a>
            <a href="#">Contact Us</a>
        </div>
    </div>
    <div class="footer-bottom">
        <p>&copy; <?php echo date('Y'); ?> JobYaari Blog. All rights reserved. Built with ❤️ for Job Seekers.</p>
    </div>
</footer>

<!-- jQuery -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="public/js/main.js"></script>

<script>
// Initialize on page load
$(document).ready(function() {
    // Check URL params for initial filter
    const urlParams = new URLSearchParams(window.location.search);
    const initCategory = urlParams.get('category') || '';
    
    if (initCategory) {
        $(`.filter-tab[data-category="${initCategory}"]`).addClass('active').siblings().removeClass('active');
    }
    
    loadBlogs();
    loadCategoryCounts();
});
</script>

</body>
</html>
