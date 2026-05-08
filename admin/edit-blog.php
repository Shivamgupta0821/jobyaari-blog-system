<?php
require_once '../config.php';
if (!isset($_SESSION['admin_id'])) { header('Location: login.php'); exit; }

$id   = (int)($_GET['id'] ?? 0);
$blog = db()->prepare("SELECT * FROM blogs WHERE id = ?");
$blog->execute([$id]);
$blog = $blog->fetch();
if (!$blog) { header('Location: blogs.php'); exit; }

$cats    = db()->query("SELECT * FROM categories ORDER BY name")->fetchAll();
$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title     = trim($_POST['title'] ?? '');
    $content   = $_POST['content'] ?? '';
    $short_desc= trim($_POST['short_description'] ?? '');
    $cat_id    = (int)($_POST['category_id'] ?? 0);
    $status    = $_POST['status'] ?? 'published';

    $image = $blog['image'];
    if (!empty($_FILES['image']['name'])) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg','jpeg','png','gif','webp'])) {
            $filename = 'blog-' . time() . '.' . $ext;
            $dest = UPLOAD_DIR . $filename;
            if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);
            if (move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
                // Delete old image
                if ($blog['image'] && file_exists(UPLOAD_DIR . $blog['image'])) {
                    unlink(UPLOAD_DIR . $blog['image']);
                }
                $image = $filename;
            }
        } else {
            $error = 'Only JPG, PNG, GIF, WEBP images allowed.';
        }
    }

    if (!$error && $title && $content && $cat_id) {
        $stmt = db()->prepare("UPDATE blogs SET title=?, short_description=?, content=?, category_id=?, image=?, status=?, updated_at=NOW() WHERE id=?");
        $stmt->execute([$title, $short_desc, $content, $cat_id, $image, $status, $id]);
        $blog = db()->prepare("SELECT * FROM blogs WHERE id=?");
        $blog->execute([$id]);
        $blog = $blog->fetch();
        $success = 'Blog updated successfully!';
    } elseif (!$error) {
        $error = 'Please fill all required fields.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Blog | JobYaari Admin</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/admin.css">
<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
</head>
<body>
<?php include 'includes/sidebar.php'; ?>
<main class="admin-main">
  <?php include 'includes/topbar.php'; ?>
  <div class="admin-content">
    <div class="section-header-row">
      <h1 class="page-title">Edit Blog</h1>
      <a href="blogs.php" class="btn btn-outline">← Back to Blogs</a>
    </div>

    <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-error"><?= $error ?></div><?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="blog-form">
      <div class="form-row">
        <div class="form-col-main">
          <div class="form-card">
            <div class="form-group">
              <label>Blog Title <span class="req">*</span></label>
              <input type="text" name="title" class="form-control" value="<?= sanitize($blog['title']) ?>" required>
            </div>
            <div class="form-group">
              <label>Short Description</label>
              <textarea name="short_description" class="form-control" rows="3"><?= sanitize($blog['short_description']) ?></textarea>
            </div>
            <div class="form-group">
              <label>Content <span class="req">*</span></label>
              <div id="quill-editor" style="height: 400px;"></div>
              <input type="hidden" name="content" id="content-input">
            </div>
          </div>
        </div>
        <div class="form-col-side">
          <div class="form-card">
            <h3 class="form-card-title">Update</h3>
            <div class="form-group">
              <label>Status</label>
              <select name="status" class="form-control">
                <option value="published" <?= $blog['status']==='published'?'selected':'' ?>>✅ Published</option>
                <option value="draft" <?= $blog['status']==='draft'?'selected':'' ?>>📋 Draft</option>
              </select>
            </div>
            <button type="submit" class="btn btn-primary btn-full">Update Blog</button>
          </div>

          <div class="form-card">
            <h3 class="form-card-title">Category <span class="req">*</span></h3>
            <select name="category_id" class="form-control" required>
              <?php foreach ($cats as $c): ?>
              <option value="<?= $c['id'] ?>" <?= $c['id']==$blog['category_id']?'selected':'' ?>><?= sanitize($c['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-card">
            <h3 class="form-card-title">Featured Image</h3>
            <?php if ($blog['image'] && file_exists(UPLOAD_DIR . $blog['image'])): ?>
            <img src="<?= UPLOAD_URL . sanitize($blog['image']) ?>" id="imgPreview" style="margin-bottom:12px;border-radius:8px;max-height:200px;width:100%;object-fit:cover;">
            <?php else: ?>
            <img id="imgPreview" src="" alt="" style="display:none;margin-bottom:12px;border-radius:8px;max-height:200px;width:100%;object-fit:cover;">
            <?php endif; ?>
            <div class="img-upload-zone" style="position:relative;">
              <span>📷 Click to change image</span>
              <input type="file" name="image" id="imageInput" accept="image/*" style="opacity:0;position:absolute;inset:0;cursor:pointer;">
            </div>
          </div>
        </div>
      </div>
    </form>
  </div>
</main>
<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
<script src="js/admin.js"></script>
<script>
const quill = new Quill('#quill-editor', {
  theme: 'snow',
  modules: { toolbar: [[{'header':[1,2,3,4,false]}],['bold','italic','underline','strike'],[{'color':[]},{'background':[]}],[{'list':'ordered'},{'list':'bullet'}],['blockquote','code-block'],['link','image'],[{'align':[]}],['clean']] }
});
quill.root.innerHTML = <?= json_encode($blog['content']) ?>;

document.querySelector('form').addEventListener('submit', function () {
  document.getElementById('content-input').value = quill.root.innerHTML;
});

document.getElementById('imageInput').addEventListener('change', function () {
  const file = this.files[0];
  if (file) {
    const reader = new FileReader();
    reader.onload = e => {
      const prev = document.getElementById('imgPreview');
      prev.src = e.target.result;
      prev.style.display = 'block';
    };
    reader.readAsDataURL(file);
  }
});
</script>
</body>
</html>
