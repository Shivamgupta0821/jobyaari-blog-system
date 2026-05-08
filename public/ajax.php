<?php
require_once '../config.php';
header('Content-Type: application/json');

$cat  = $_GET['category'] ?? '';
$srch = $_GET['search'] ?? '';
$date = $_GET['date'] ?? '';

$where  = ["b.status = 'published'"];
$params = [];

if ($cat) {
    $where[] = "c.slug = :cat";
    $params[':cat'] = $cat;
}
if ($srch) {
    $where[] = "(b.title LIKE :s OR b.short_description LIKE :s2)";
    $params[':s']  = "%$srch%";
    $params[':s2'] = "%$srch%";
}
if ($date) {
    $where[] = "DATE(b.created_at) = :date";
    $params[':date'] = $date;
}

$sql = "SELECT b.*, c.name as cat_name, c.slug as cat_slug
        FROM blogs b JOIN categories c ON b.category_id = c.id
        WHERE " . implode(' AND ', $where) . "
        ORDER BY b.created_at DESC";

$stmt = db()->prepare($sql);
$stmt->execute($params);
$blogs = $stmt->fetchAll();

$html = '';
if (empty($blogs)) {
    $html = '<div class="no-posts"><div class="no-posts-icon">📭</div><h3>No posts found</h3><p>Try a different filter.</p></div>';
} else {
    foreach ($blogs as $b) {
        $img = '';
        if ($b['image'] && file_exists(UPLOAD_DIR . $b['image'])) {
            $img = '<img src="' . UPLOAD_URL . htmlspecialchars($b['image']) . '" alt="' . htmlspecialchars($b['title']) . '" class="card-img" loading="lazy">';
        } else {
            $img = '<div class="card-img-placeholder"><span class="placeholder-cat">' . htmlspecialchars($b['cat_name']) . '</span></div>';
        }
        $html .= '
        <article class="blog-card" data-category="' . htmlspecialchars($b['cat_slug']) . '">
          <a href="blog.php?slug=' . urlencode($b['slug']) . '" class="card-img-link">' . $img . '</a>
          <div class="card-body">
            <span class="badge badge-' . htmlspecialchars($b['cat_slug']) . '">' . htmlspecialchars($b['cat_name']) . '</span>
            <h3 class="card-title"><a href="blog.php?slug=' . urlencode($b['slug']) . '">' . htmlspecialchars($b['title']) . '</a></h3>
            <p class="card-desc">' . htmlspecialchars($b['short_description']) . '</p>
            <div class="card-footer">
              <span class="card-date">📅 ' . date('d M Y', strtotime($b['created_at'])) . '</span>
              <a href="blog.php?slug=' . urlencode($b['slug']) . '" class="read-more">Read More →</a>
            </div>
          </div>
        </article>';
    }
}

echo json_encode(['html' => $html, 'count' => count($blogs)]);
