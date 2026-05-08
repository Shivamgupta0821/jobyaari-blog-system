<?php
require_once __DIR__ . '/../includes/config.php';
requireAdmin();

$id = intval($_GET['id'] ?? 0);

if (!$id) {
    redirect(SITE_URL . '/admin/blogs.php');
}

$db = getDB();
$blog = $db->prepare("SELECT * FROM blogs WHERE id = ?");
$blog->execute([$id]);
$blog = $blog->fetch();

if ($blog) {
    // Delete image file if exists
    if ($blog['image'] && file_exists(UPLOAD_PATH . $blog['image'])) {
        unlink(UPLOAD_PATH . $blog['image']);
    }
    
    $db->prepare("DELETE FROM blogs WHERE id = ?")->execute([$id]);
    $_SESSION['success'] = 'Blog deleted successfully.';
} else {
    $_SESSION['error'] = 'Blog not found.';
}

redirect(SITE_URL . '/admin/blogs.php');
