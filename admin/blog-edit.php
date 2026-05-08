<?php
require_once __DIR__ . '/../includes/config.php';
requireAdmin();

$db = getDB();
$id = intval($_GET['id'] ?? 0);

if (!$id) redirect(SITE_URL . '/admin/blogs.php');

$blog = $db->prepare("SELECT * FROM blogs WHERE id = ?");
$blog->execute([$id]);
$blog = $blog->fetch();

if (!$blog) {
    $_SESSION['error'] = 'Blog not found.';
    redirect(SITE_URL . '/admin/blogs.php');
}

$categories = $db->query("SELECT * FROM categories ORDER BY name")->fetchAll();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $shortDesc = trim($_POST['short_description'] ?? '');
    $content = $_POST['content'] ?? '';
    $categoryId = intval($_POST['category_id'] ?? 0);
    $status = in_array($_POST['status'] ?? '', ['published', 'draft']) ? $_POST['status'] : 'published';
    
    if (empty($title)) $errors[] = 'Title is required.';
    if (empty($content) || $content === '<p><br></p>') $errors[] = 'Content is required.';
    
    // Handle image
    $imageName = $blog['image'];
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
            $newImageName = uniqid('img_') . '.' . $ext;
            
            if (!is_dir(UPLOAD_PATH)) mkdir(UPLOAD_PATH, 0755, true);
            
            if (move_uploaded_file($file['tmp_name'], UPLOAD_PATH . $newImageName)) {
                // Delete old image
                if ($blog['image'] && file_exists(UPLOAD_PATH . $blog['image'])) {
                    unlink(UPLOAD_PATH . $blog['image']);
                }
                $imageName = $newImageName;
            } else {
                $errors[] = 'Failed to save image.';
            }
        }
    }
    
    // Remove image if checked
    if (isset($_POST['remove_image'])) {
        if ($blog['image'] && file_exists(UPLOAD_PATH . $blog['image'])) {
            unlink(UPLOAD_PATH . $blog['image']);
        }
        $imageName = null;
    }
    
    if (empty($errors)) {
        // Update slug if title changed
        $newSlug = $blog['slug'];
        if ($title !== $blog['title']) {
            $newSlug = generateSlug($title);
            $base = $newSlug;
            $c = 1;
            while ($db->query("SELECT COUNT(*) FROM blogs WHERE slug = '$newSlug' AND id != $id")->fetchColumn() > 0) {
                $newSlug = $base . '-' . $c++;
            }
        }
        
        $stmt = $db->prepare("
            UPDATE blogs SET title=?, slug=?, short_description=?, content=?, 
            category_id=?, image=?, status=?, updated_at=NOW() 
            WHERE id=?
        ");
        $stmt->execute([
            $title, $newSlug, $shortDesc, $content,
            $categoryId ?: null, $imageName, $status, $id
        ]);
        
        $_SESSION['success'] = 'Blog updated successfully!';
        redirect(SITE_URL . '/admin/blogs.php');
    }
    
    // Update $blog for repopulation
    $blog['title'] = $title;
    $blog['short_description'] = $shortDesc;
    $blog['content'] = $content;
    $blog['category_id'] = $categoryId;
    $blog['status'] = $status;
}

require_once 'partials/header.php';
?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-edit"></i> Edit Blog</h1>
        <p class="page-subtitle">Editing: <?php echo sanitize($blog['title']); ?></p>
    </div>
    <div style="display:flex;gap:8px">
        <a href="<?php echo SITE_URL; ?>/blog.php?slug=<?php echo $blog['slug']; ?>" 
           target="_blank" class="btn btn-secondary">
            <i class="fas fa-eye"></i> Preview
        </a>
        <a href="blogs.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
    </div>
</div>

<?php if (!empty($errors)): ?>
<div class="alert alert-error">
    <i class="fas fa-exclamation-circle"></i>
    <ul style="margin:8px 0 0 20px">
        <?php foreach ($errors as $e): ?><li><?php echo sanitize($e); ?></li><?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data" id="blogForm">
    <div class="form-layout">
        <div class="form-main">
            <div class="admin-card">
                <div class="form-group">
                    <label>Blog Title <span class="req">*</span></label>
                    <input type="text" name="title" id="titleInput"
                           value="<?php echo sanitize($blog['title']); ?>" required>
                    <small class="slug-preview">Slug: <code id="slugPreview"><?php echo sanitize($blog['slug']); ?></code></small>
                </div>
                
                <div class="form-group">
                    <label>Short Description</label>
                    <textarea name="short_description" rows="3"><?php echo sanitize($blog['short_description'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label>Content <span class="req">*</span></label>
                    <div id="quillEditor" style="min-height:400px;"></div>
                    <input type="hidden" name="content" id="contentInput">
                </div>
            </div>
        </div>
        
        <div class="form-sidebar">
            <div class="admin-card">
                <h3 class="card-section-title">Publish</h3>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status">
                        <option value="published" <?php echo $blog['status'] === 'published' ? 'selected' : ''; ?>>Published</option>
                        <option value="draft" <?php echo $blog['status'] === 'draft' ? 'selected' : ''; ?>>Draft</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary btn-full">
                    <i class="fas fa-save"></i> Update Blog
                </button>
            </div>
            
            <div class="admin-card">
                <h3 class="card-section-title">Category</h3>
                <div class="form-group">
                    <select name="category_id">
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>"
                            <?php echo intval($blog['category_id']) === $cat['id'] ? 'selected' : ''; ?>>
                            <?php echo sanitize($cat['name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="admin-card">
                <h3 class="card-section-title">Featured Image</h3>
                <?php if ($blog['image']): ?>
                <div class="current-image">
                    <img src="<?php echo UPLOAD_URL . sanitize($blog['image']); ?>" alt="Current image">
                    <label class="remove-check">
                        <input type="checkbox" name="remove_image"> Remove image
                    </label>
                </div>
                <?php endif; ?>
                <div class="image-upload-area" id="imageUploadArea" style="<?php echo $blog['image'] ? 'margin-top:10px;' : ''; ?>">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <p><?php echo $blog['image'] ? 'Replace image' : 'Click to upload image'; ?></p>
                    <small>JPG, PNG, WebP, GIF · Max 5MB</small>
                    <input type="file" name="image" id="imageInput" accept="image/*" style="display:none">
                </div>
                <div class="image-preview" id="imagePreview" style="display:none">
                    <img id="previewImg" src="" alt="Preview">
                    <button type="button" class="remove-image" onclick="removeNewImage()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
const existingContent = <?php echo json_encode($blog['content']); ?>;

document.getElementById('titleInput').addEventListener('input', function() {
    const slug = this.value.toLowerCase().trim()
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/[\s-]+/g, '-');
    document.getElementById('slugPreview').textContent = slug || 'auto-generated';
});

document.getElementById('imageUploadArea').addEventListener('click', function() {
    document.getElementById('imageInput').click();
});

document.getElementById('imageInput').addEventListener('change', function() {
    if (this.files && this.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('previewImg').src = e.target.result;
            document.getElementById('imagePreview').style.display = 'block';
        };
        reader.readAsDataURL(this.files[0]);
    }
});

function removeNewImage() {
    document.getElementById('imageInput').value = '';
    document.getElementById('imagePreview').style.display = 'none';
}

document.getElementById('blogForm').addEventListener('submit', function() {
    document.getElementById('contentInput').value = quill.root.innerHTML;
});
</script>

<?php require_once 'partials/footer.php'; ?>
