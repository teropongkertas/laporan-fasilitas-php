<?php
require 'includes/auth.php';
require 'config/database.php';

if (isLoggedIn()) {
    header('Location: ' . (isAdmin() ? 'admin/dashboard.php' : 'dashboard.php'));
    exit;
}

$error = '';
$justRegistered = isset($_GET['registered']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = bersih($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';

    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($pass, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['nama']    = $user['nama'];
        $_SESSION['role']    = $user['role'];
        header('Location: ' . ($user['role'] === 'admin' ? 'admin/dashboard.php' : 'dashboard.php'));
        exit;
    }
    $error = 'Email atau password salah.';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Masuk — Lapor Fasilitas</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="auth-wrap">
    <div class="auth-card">
        <div class="brand"><span class="tag"></span> Lapor Fasilitas</div>
        <div class="tagline">Sistem pelaporan fasilitas rusak di lingkungan kampus.</div>

        <?php if ($justRegistered): ?>
            <div class="alert alert-success">Akun berhasil dibuat. Silakan masuk.</div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error"><?= $error ?></div>
        <?php endif; ?>

        <form method="post" novalidate>
            <div class="field">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?= bersih($_POST['email'] ?? '') ?>" required>
            </div>
            <div class="field">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">Masuk</button>
        </form>

        <div class="switch">Belum punya akun? <a href="register.php">Daftar</a></div>
        <div class="switch" style="margin-top:6px;font-size:.75rem;">Akun petugas demo: admin@kampus.ac.id / admin123</div>
    </div>
</div>
</body>
</html>
