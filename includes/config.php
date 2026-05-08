<?php
// =============================================================
// DATABASE CONFIGURATION
// Edit these values to match your hosting environment
// =============================================================

define('DB_HOST', 'localhost');
define('DB_USER', 'root');         // Your MySQL username
define('DB_PASS', '');             // Your MySQL password
define('DB_NAME', 'blogsystem');   // Your database name

define('SITE_URL', 'http://localhost/blog-system'); // Your site URL (no trailing slash)
define('SITE_NAME', 'JobYaari Blog');
define('ADMIN_EMAIL', 'admin@blogsystem.com');

// Upload settings
define('UPLOAD_PATH', __DIR__ . '/../public/uploads/images/');
define('UPLOAD_URL', SITE_URL . '/public/uploads/images/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_TYPES', ['image/jpeg', 'image/png', 'image/webp', 'image/gif']);

// Session settings
define('SESSION_NAME', 'blogsystem_session');

// Create database connection
function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            die(json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]));
        }
    }
    return $pdo;
}

// Start session
session_name(SESSION_NAME);
session_start();

// Helper functions
function sanitize($input) {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

function generateSlug($text) {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', $text);
    return $text;
}

function redirect($url) {
    header("Location: $url");
    exit;
}

function isAdmin() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

function requireAdmin() {
    if (!isAdmin()) {
        redirect(SITE_URL . '/admin/login.php');
    }
}

function timeAgo($datetime) {
    $time = time() - strtotime($datetime);
    if ($time < 60) return 'Just now';
    if ($time < 3600) return floor($time/60) . ' minutes ago';
    if ($time < 86400) return floor($time/3600) . ' hours ago';
    if ($time < 604800) return floor($time/86400) . ' days ago';
    return date('M d, Y', strtotime($datetime));
}

function formatDate($datetime) {
    return date('F d, Y', strtotime($datetime));
}
