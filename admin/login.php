<?php
require_once '../config.php';

if (isset($_SESSION['admin_id'])) { header('Location: index.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = trim($_POST['username'] ?? '');
    $pass = $_POST['password'] ?? '';

    $stmt = db()->prepare("SELECT * FROM admins WHERE username = ? OR email = ? LIMIT 1");
    $stmt->execute([$user, $user]);
    $admin = $stmt->fetch();

    if ($admin && (md5($pass) === $admin['password'] || password_verify($pass, $admin['password']))) {
        $_SESSION['admin_id']   = $admin['id'];
        $_SESSION['admin_user'] = $admin['username'];
        header('Location: index.php');
        exit;
    } else {
        $error = 'Invalid username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login | JobYaari</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/admin.css">
</head>
<body class="login-body">
<div class="login-wrap">
  <div class="login-card">
    <div class="login-logo">⚡ Job<span class="accent">Yaari</span></div>
    <h2>Admin Login</h2>
    <p class="login-sub">Enter your credentials to access the panel</p>

    <?php if ($error): ?>
    <div class="alert alert-error"><?= sanitize($error) ?></div>
    <?php endif; ?>

    <form method="POST">
      <div class="form-group">
        <label>Username / Email</label>
        <input type="text" name="username" class="form-control" placeholder="admin" required autofocus>
      </div>
      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" class="form-control" placeholder="••••••••" required>
      </div>
      <button type="submit" class="btn btn-primary btn-full">Login to Panel</button>
    </form>

    <div class="login-hint">
      <strong>Default:</strong> admin / Admin@123
    </div>
  </div>
</div>
</body>
</html>
