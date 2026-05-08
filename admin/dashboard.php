<?php
require_once __DIR__ . '/../includes/config.php';
requireAdmin();

$db = getDB();

// Stats
$totalBlogs = $db->query("SELECT COUNT(*) FROM blogs")->fetchColumn();
$publishedBlogs = $db->query("SELECT COUNT(*) FROM blogs WHERE status = 'published'")->fetchColumn();
$draftBlogs = $db->query("SELECT COUNT(*) FROM blogs WHERE status = 'draft'")->fetchColumn();
$totalViews = $db->query("SELECT SUM(views) FROM blogs")->fetchColumn();
$totalCategories = $db->query("SELECT COUNT(*) FROM categories")->fetchColumn();

// Recent blogs
$recentBlogs = $db->query("
    SELECT b.*, c.name AS category_name 
    FROM blogs b 
    LEFT JOIN categories c ON b.category_id = c.id 
    ORDER BY b.created_at DESC LIMIT 5
")->fetchAll();

require_once 'partials/header.php';
?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-tachometer-alt"></i> Dashboard</h1>
        <p class="page-subtitle">Welcome back, <strong><?php echo sanitize($_SESSION['admin_username']); ?></strong>!</p>
    </div>
    <a href="blog-add.php" class="btn btn-primary">
        <i class="fas fa-plus"></i> New Blog
    </a>
</div>

<!-- Stats Cards -->
<div class="stats-grid">
    <div class="stat-card blue">
        <div class="stat-icon"><i class="fas fa-file-alt"></i></div>
        <div class="stat-info">
            <h3><?php echo $totalBlogs; ?></h3>
            <p>Total Blogs</p>
        </div>
    </div>
    <div class="stat-card green">
        <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
        <div class="stat-info">
            <h3><?php echo $publishedBlogs; ?></h3>
            <p>Published</p>
        </div>
    </div>
    <div class="stat-card orange">
        <div class="stat-icon"><i class="fas fa-edit"></i></div>
        <div class="stat-info">
            <h3><?php echo $draftBlogs; ?></h3>
            <p>Drafts</p>
        </div>
    </div>
    <div class="stat-card purple">
        <div class="stat-icon"><i class="fas fa-eye"></i></div>
        <div class="stat-info">
            <h3><?php echo number_format($totalViews ?? 0); ?></h3>
            <p>Total Views</p>
        </div>
    </div>
    <div class="stat-card teal">
        <div class="stat-icon"><i class="fas fa-tags"></i></div>
        <div class="stat-info">
            <h3><?php echo $totalCategories; ?></h3>
            <p>Categories</p>
        </div>
    </div>
</div>

<!-- Recent Blogs -->
<div class="admin-card">
    <div class="card-header">
        <h2><i class="fas fa-clock"></i> Recent Blogs</h2>
        <a href="blogs.php" class="btn btn-sm">View All</a>
    </div>
    
    <?php if (empty($recentBlogs)): ?>
    <div class="empty-state">
        <i class="fas fa-file-alt"></i>
        <p>No blogs yet. <a href="blog-add.php">Create your first blog</a></p>
    </div>
    <?php else: ?>
    <div class="table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Views</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentBlogs as $blog): ?>
                <tr>
                    <td>
                        <span class="blog-title-cell"><?php echo sanitize($blog['title']); ?></span>
                    </td>
                    <td>
                        <?php if ($blog['category_name']): ?>
                        <span class="badge badge-cat"><?php echo sanitize($blog['category_name']); ?></span>
                        <?php else: ?>
                        <span class="text-muted">—</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="badge <?php echo $blog['status'] === 'published' ? 'badge-success' : 'badge-warning'; ?>">
                            <?php echo ucfirst($blog['status']); ?>
                        </span>
                    </td>
                    <td><?php echo number_format($blog['views']); ?></td>
                    <td><?php echo date('M d, Y', strtotime($blog['created_at'])); ?></td>
                    <td class="action-btns">
                        <a href="blog-edit.php?id=<?php echo $blog['id']; ?>" class="btn btn-sm btn-edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="<?php echo SITE_URL; ?>/blog.php?slug=<?php echo $blog['slug']; ?>" 
                           target="_blank" class="btn btn-sm btn-view">
                            <i class="fas fa-eye"></i>
                        </a>
                        <button onclick="deleteBlog(<?php echo $blog['id']; ?>)" class="btn btn-sm btn-delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<script>
function deleteBlog(id) {
    if (confirm('Are you sure you want to delete this blog? This action cannot be undone.')) {
        window.location.href = 'blog-delete.php?id=' + id;
    }
}
</script>

<?php require_once 'partials/footer.php'; ?>
