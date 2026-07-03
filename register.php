<?php
require 'includes/auth.php';
require 'config/database.php';

if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama    = bersih($_POST['nama'] ?? '');
    $email   = bersih($_POST['email'] ?? '');
    $pass    = $_POST['password'] ?? '';
    $pass2   = $_POST['password2'] ?? '';

    if ($nama === '' || $email === '' || $pass === '') {
        $error = 'Semua kolom wajib diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid.';
    } elseif (strlen($pass) < 6) {
        $error = 'Password minimal 6 karakter.';
    } elseif ($pass !== $pass2) {
        $error = 'Konfirmasi password tidak cocok.';
    } else {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'Email sudah terdaftar. Silakan login.';
        } else {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO users (nama, email, password, role) VALUES (?, ?, ?, "pelapor")');
            $stmt->execute([$nama, $email, $hash]);
            header('Location: login.php?registered=1');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Daftar Akun — Lapor Fasilitas</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="auth-wrap">
    <div class="auth-card">
        <div class="brand"><span class="tag"></span> Lapor Fasilitas</div>
        <div class="tagline">Buat akun untuk mulai melaporkan fasilitas kampus yang rusak.</div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= $error ?></div>
        <?php endif; ?>

        <form method="post" novalidate>
            <div class="field">
                <label for="nama">Nama lengkap</label>
                <input type="text" id="nama" name="nama" value="<?= bersih($_POST['nama'] ?? '') ?>" required>
            </div>
            <div class="field">
                <label for="email">Email kampus</label>
                <input type="email" id="email" name="email" value="<?= bersih($_POST['email'] ?? '') ?>" required>
            </div>
            <div class="field">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
                <div class="hint">Minimal 6 karakter.</div>
            </div>
            <div class="field">
                <label for="password2">Ulangi password</label>
                <input type="password" id="password2" name="password2" required>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">Daftar</button>
        </form>

        <div class="switch">Sudah punya akun? <a href="login.php">Masuk di sini</a></div>
    </div>
</div>
</body>
</html>
