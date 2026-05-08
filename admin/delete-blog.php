<?php
require_once '../config.php';
if (!isset($_SESSION['admin_id'])) { header('Location: login.php'); exit; }

$id   = (int)($_GET['id'] ?? 0);
$stmt = db()->prepare("SELECT * FROM blogs WHERE id = ?");
$stmt->execute([$id]);
$blog = $stmt->fetch();

if ($blog) {
    if ($blog['image'] && file_exists(UPLOAD_DIR . $blog['image'])) {
        unlink(UPLOAD_DIR . $blog['image']);
    }
    db()->prepare("DELETE FROM blogs WHERE id = ?")->execute([$id]);
}
header('Location: blogs.php');
exit;
