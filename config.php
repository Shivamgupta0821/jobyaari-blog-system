<?php
define('DB_HOST', 'mysql.railway.internal');
define('DB_USER', 'root');
define('DB_PASS', 'YVxNtHKwGuXbJSuDBXSTfoLewoJpXgfx');
define('DB_NAME', 'railway');
define('SITE_URL', 'https://jobyaari-blog-system-production.up.railway.app');
define('SITE_NAME', 'JobYaari');
define('UPLOAD_DIR', __DIR__ . '/public/images/uploads/');
define('UPLOAD_URL', SITE_URL . '/public/images/uploads/');

function db() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                "mysql:host=" . DB_HOST . ";port=3306;dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER, DB_PASS,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                 PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
            );
        } catch (PDOException $e) {
            die(json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]));
        }
    }
    return $pdo;
}

function slugify($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = trim($text, '-');
    $text = strtolower($text);
    return $text ?: 'n-a';
}

function sanitize($input) {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

session_start();
