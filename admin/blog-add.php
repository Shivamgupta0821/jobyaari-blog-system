<?php
require_once __DIR__ . '/../includes/config.php';
requireAdmin();

$db = getDB();
$categories = $db->query("SELECT * FROM categories ORDER BY name")->fetchAll();

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $shortDesc = trim($_POST['short_description'] ?? '');
    $content = $_POST['content'] ?? '';
    $categoryId = intval($_POST['category_id'] ?? 0);
    $status = in_array($_POST['status'] ?? '', ['published', 'draft']) ? $_POST['status'] : 'published';
    
    // Validation
    if (empty($title)) $errors[] = 'Title is required.';
    if (empty($content) || $content === '<p><br></p>') $errors[] = 'Content is required.';
    
    // Handle image upload
    $imageName = null;
    if (!empty($_FILES['image']['name'])) {
        $file = $_FILES['image'];
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'File upload error.';
        } elseif ($file['size'] > MAX_FILE_SIZE) {
            $errors[] = 'Image must be under 5MB.';
        } elseif (!in_array($file['type'], ALLOWED_TYPES)) {
            $errors[] = 'Only JPG, PNG, WebP, GIF images are allowed.';
        } else {
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $imageName = uniqid('img_') . '.' . $ext;
            
            if (!is_dir(UPLOAD_PATH)) {
                mkdir(UPLOAD_PATH, 0755, true);
            }
            
            if (!move_uploaded_file($file['tmp_name'], UPLOAD_PATH . $imageName)) {
                $errors[] = 'Failed to save image. Check upload directory permissions.';
                $imageName = null;
            }
        }
    }
    
    if (empty($errors)) {
        // Generate unique slug
        $slug = generateSlug($title);
        $baseSlug = $slug;
        $counter = 1;
        while ($db->prepare("SELECT id FROM blogs WHERE slug = ?")->execute([$slug]) && 
               $db->prepare("SELECT id FROM blogs WHERE slug = ?")->execute([$slug]) &&
               $db->query("SELECT COUNT(*) FROM blogs WHERE slug = '$slug'")->fetchColumn() > 0) {
            $slug = $baseSlug . '-' . $counter++;
        }
        
        $stmt = $db->prepare("
            INSERT INTO blogs (title, slug, short_description, content, category_id, image, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $title,
            $slug,
            $shortDesc,
            $content,
            $categoryId ?: null,
            $imageName,
            $status
        ]);
        
        $_SESSION['success'] = 'Blog published successfully!';
        redirect(SITE_URL . '/admin/blogs.php');
    }
}

require_once 'partials/header.php';
?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-plus-circle"></i> Add New Blog</h1>
        <p class="page-subtitle">Create and publish a new blog post</p>
    </div>
    <a href="blogs.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Blogs</a>
</div>

<?php if (!empty($errors)): ?>
<div class="alert alert-error">
    <i class="fas fa-exclamation-circle"></i>
    <ul style="margin:8px 0 0 20px">
        <?php foreach ($errors as $e): ?>
        <li><?php echo sanitize($e); ?></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data" id="blogForm">
    <div class="form-layout">
        <!-- Main -->
        <div class="form-main">
            <div class="admin-card">
                <div class="form-group">
                    <label>Blog Title <span class="req">*</span></label>
                    <input type="text" name="title" id="titleInput"
                           value="<?php echo sanitize($_POST['title'] ?? ''); ?>"
                           placeholder="Enter blog title..." required>
                    <small class="slug-preview">Slug: <code id="slugPreview">auto-generated</code></small>
                </div>
                
                <div class="form-group">
                    <label>Short Description</label>
                    <textarea name="short_description" rows="3" 
                              placeholder="Brief summary shown on listing page..."><?php echo sanitize($_POST['short_description'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label>Content <span class="req">*</span></label>
                    <div id="quillEditor" style="min-height:400px;"></div>
                    <input type="hidden" name="content" id="contentInput">
                </div>
            </div>
        </div>
        
        <!-- Sidebar -->
        <div class="form-sidebar">
            <div class="admin-card">
                <h3 class="card-section-title">Publish</h3>
                
                <div class="form-group">
                    <label>Status</label>
                    <select name="status">
                        <option value="published" <?php echo ($_POST['status'] ?? 'published') === 'published' ? 'selected' : ''; ?>>
                            Published
                        </option>
                        <option value="draft" <?php echo ($_POST['status'] ?? '') === 'draft' ? 'selected' : ''; ?>>
                            Draft
                        </option>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-primary btn-full">
                    <i class="fas fa-paper-plane"></i> Publish Blog
                </button>
                <button type="submit" name="status" value="draft" class="btn btn-secondary btn-full" style="margin-top:8px">
                    <i class="fas fa-save"></i> Save as Draft
                </button>
            </div>
            
            <div class="admin-card">
                <h3 class="card-section-title">Category</h3>
                <div class="form-group">
                    <select name="category_id">
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>"
                            <?php echo (intval($_POST['category_id'] ?? 0) === $cat['id']) ? 'selected' : ''; ?>>
                            <?php echo sanitize($cat['name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <a href="categories.php" class="link-small">+ Manage Categories</a>
            </div>
            
            <div class="admin-card">
                <h3 class="card-section-title">Featured Image</h3>
                <div class="image-upload-area" id="imageUploadArea">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <p>Click to upload image</p>
                    <small>JPG, PNG, WebP, GIF · Max 5MB</small>
                    <input type="file" name="image" id="imageInput" accept="image/*" style="display:none">
                </div>
                <div class="image-preview" id="imagePreview" style="display:none">
                    <img id="previewImg" src="" alt="Preview">
                    <button type="button" class="remove-image" onclick="removeImage()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
// Slug preview
document.getElementById('titleInput').addEventListener('input', function() {
    const slug = this.value.toLowerCase().trim()
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/[\s-]+/g, '-');
    document.getElementById('slugPreview').textContent = slug || 'auto-generated';
});

// Image upload
document.getElementById('imageUploadArea').addEventListener('click', function() {
    document.getElementById('imageInput').click();
});

document.getElementById('imageInput').addEventListener('change', function() {
    if (this.files && this.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('previewImg').src = e.target.result;
            document.getElementById('imagePreview').style.display = 'block';
            document.getElementById('imageUploadArea').style.display = 'none';
        };
        reader.readAsDataURL(this.files[0]);
    }
});

function removeImage() {
    document.getElementById('imageInput').value = '';
    document.getElementById('imagePreview').style.display = 'none';
    document.getElementById('imageUploadArea').style.display = 'flex';
}

// Save content before submit
document.getElementById('blogForm').addEventListener('submit', function() {
    document.getElementById('contentInput').value = quill.root.innerHTML;
});
</script>

<?php require_once 'partials/footer.php'; ?>
