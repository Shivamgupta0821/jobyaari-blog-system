<?php
require_once __DIR__ . '/../includes/config.php';
requireAdmin();

$db = getDB();

$success = $_SESSION['success'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);

// Handle add
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        $name = sanitize(trim($_POST['name'] ?? ''));
        if (!empty($name)) {
            $slug = generateSlug($name);
            $base = $slug; $c = 1;
            while ($db->query("SELECT COUNT(*) FROM categories WHERE slug = '$slug'")->fetchColumn() > 0) {
                $slug = $base . '-' . $c++;
            }
            $db->prepare("INSERT INTO categories (name, slug) VALUES (?, ?)")->execute([$name, $slug]);
            $_SESSION['success'] = "Category '$name' added!";
        } else {
            $_SESSION['error'] = 'Category name is required.';
        }
        redirect(SITE_URL . '/admin/categories.php');
    }
    
    if ($_POST['action'] === 'delete') {
        $catId = intval($_POST['cat_id'] ?? 0);
        // Unset from blogs first
        $db->prepare("UPDATE blogs SET category_id = NULL WHERE category_id = ?")->execute([$catId]);
        $db->prepare("DELETE FROM categories WHERE id = ?")->execute([$catId]);
        $_SESSION['success'] = 'Category deleted.';
        redirect(SITE_URL . '/admin/categories.php');
    }
}

$categories = $db->query("
    SELECT c.*, COUNT(b.id) AS blog_count 
    FROM categories c 
    LEFT JOIN blogs b ON b.category_id = c.id 
    GROUP BY c.id 
    ORDER BY c.name ASC
")->fetchAll();

require_once 'partials/header.php';
?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-tags"></i> Categories</h1>
        <p class="page-subtitle">Manage blog categories</p>
    </div>
</div>

<?php if ($success): ?>
<div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $success; ?></div>
<?php endif; ?>
<?php if ($error): ?>
<div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div>
<?php endif; ?>

<div class="form-layout" style="grid-template-columns: 1fr 2fr">
    <!-- Add Category -->
    <div class="admin-card">
        <h2 class="card-title"><i class="fas fa-plus"></i> Add Category</h2>
        <form method="POST" action="">
            <input type="hidden" name="action" value="add">
            <div class="form-group">
                <label>Category Name <span class="req">*</span></label>
                <input type="text" name="name" placeholder="e.g. Admit Card" required>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Category
            </button>
        </form>
    </div>
    
    <!-- All Categories -->
    <div class="admin-card">
        <h2 class="card-title"><i class="fas fa-list"></i> All Categories</h2>
        <?php if (empty($categories)): ?>
        <div class="empty-state">
            <i class="fas fa-tags"></i>
            <p>No categories yet. Add one!</p>
        </div>
        <?php else: ?>
        <div class="table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Slug</th>
                        <th>Blogs</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $i => $cat): ?>
                    <tr>
                        <td class="text-muted"><?php echo $i + 1; ?></td>
                        <td><strong><?php echo sanitize($cat['name']); ?></strong></td>
                        <td><code><?php echo sanitize($cat['slug']); ?></code></td>
                        <td>
                            <span class="badge badge-cat"><?php echo $cat['blog_count']; ?></span>
                        </td>
                        <td>
                            <form method="POST" action="" style="display:inline" 
                                  onsubmit="return confirm('Delete this category? Blogs will be uncategorized.')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="cat_id" value="<?php echo $cat['id']; ?>">
                                <button type="submit" class="btn btn-sm btn-delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'partials/footer.php'; ?>
