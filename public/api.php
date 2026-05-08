<?php
require_once __DIR__ . '/../includes/config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$db = getDB();

$action = $_GET['action'] ?? $_POST['action'] ?? 'get_blogs';

switch ($action) {
    case 'get_blogs':
        getBlogs($db);
        break;
    case 'get_counts':
        getCounts($db);
        break;
    default:
        echo json_encode(['error' => 'Invalid action']);
}

function getBlogs($db) {
    $category = $_GET['category'] ?? '';
    $search = trim($_GET['search'] ?? '');
    $date = $_GET['date'] ?? '';
    $page = max(1, intval($_GET['page'] ?? 1));
    $perPage = 6;
    $offset = ($page - 1) * $perPage;

    $conditions = ["b.status = 'published'"];
    $params = [];

    if (!empty($category)) {
        $conditions[] = "c.slug = ?";
        $params[] = $category;
    }

    if (!empty($search)) {
        $conditions[] = "(b.title LIKE ? OR b.short_description LIKE ? OR b.content LIKE ?)";
        $searchTerm = "%{$search}%";
        $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
    }

    if (!empty($date)) {
        switch ($date) {
            case 'today':
                $conditions[] = "DATE(b.created_at) = CURDATE()";
                break;
            case 'week':
                $conditions[] = "b.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
                break;
            case 'month':
                $conditions[] = "b.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
                break;
            case 'year':
                $conditions[] = "b.created_at >= DATE_SUB(NOW(), INTERVAL 1 YEAR)";
                break;
        }
    }

    $whereClause = implode(' AND ', $conditions);

    // Get total count
    $countStmt = $db->prepare("
        SELECT COUNT(*) 
        FROM blogs b 
        LEFT JOIN categories c ON b.category_id = c.id 
        WHERE {$whereClause}
    ");
    $countStmt->execute($params);
    $total = $countStmt->fetchColumn();

    // Get blogs
    $stmt = $db->prepare("
        SELECT b.id, b.title, b.slug, b.short_description, b.image, b.views, b.created_at,
               c.name AS category_name, c.slug AS category_slug
        FROM blogs b 
        LEFT JOIN categories c ON b.category_id = c.id 
        WHERE {$whereClause}
        ORDER BY b.created_at DESC
        LIMIT {$perPage} OFFSET {$offset}
    ");
    $stmt->execute($params);
    $blogs = $stmt->fetchAll();

    // Format blogs for JSON
    $blogsData = array_map(function($blog) {
        return [
            'id' => $blog['id'],
            'title' => htmlspecialchars($blog['title']),
            'slug' => $blog['slug'],
            'short_description' => htmlspecialchars($blog['short_description'] ?? ''),
            'image' => $blog['image'] ? UPLOAD_URL . $blog['image'] : null,
            'views' => number_format($blog['views']),
            'date' => date('M d, Y', strtotime($blog['created_at'])),
            'time_ago' => timeAgo($blog['created_at']),
            'category_name' => htmlspecialchars($blog['category_name'] ?? ''),
            'category_slug' => $blog['category_slug'] ?? '',
        ];
    }, $blogs);

    echo json_encode([
        'success' => true,
        'blogs' => $blogsData,
        'total' => $total,
        'page' => $page,
        'per_page' => $perPage,
        'total_pages' => ceil($total / $perPage),
    ]);
}

function getCounts($db) {
    // Total
    $total = $db->query("SELECT COUNT(*) FROM blogs WHERE status = 'published'")->fetchColumn();

    // Per category
    $cats = $db->query("
        SELECT c.slug, COUNT(b.id) AS cnt
        FROM categories c
        LEFT JOIN blogs b ON b.category_id = c.id AND b.status = 'published'
        GROUP BY c.id, c.slug
    ")->fetchAll();

    $counts = ['all' => $total];
    foreach ($cats as $cat) {
        $counts[$cat['slug']] = $cat['cnt'];
    }

    echo json_encode(['success' => true, 'counts' => $counts]);
}
