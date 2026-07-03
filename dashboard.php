<?php
require 'includes/auth.php';
require 'config/database.php';
requireLogin();

if (isAdmin()) {
    header('Location: admin/dashboard.php');
    exit;
}

$active = 'dashboard';

$stmt = $pdo->prepare('SELECT * FROM reports WHERE user_id = ? ORDER BY created_at DESC');
$stmt->execute([$_SESSION['user_id']]);
$laporan = $stmt->fetchAll();

$total    = count($laporan);
$menunggu = count(array_filter($laporan, fn($r) => $r['status'] === 'Menunggu'));
$proses   = count(array_filter($laporan, fn($r) => $r['status'] === 'Diproses'));
$selesai  = count(array_filter($laporan, fn($r) => $r['status'] === 'Selesai'));
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Laporan Saya — Lapor Fasilitas</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="shell">
    <?php include 'includes/sidebar.php'; ?>

    <main class="main">
        <div class="topbar">
            <div>
                <span class="eyebrow">Ringkasan</span>
                <h1>Laporan saya</h1>
            </div>
            <a href="lapor.php" class="btn btn-primary">+ Buat laporan baru</a>
        </div>

        <?php if (isset($_GET['sukses'])): ?>
            <div class="alert alert-success">Laporan berhasil dikirim. Petugas akan segera meninjau.</div>
        <?php endif; ?>

        <div class="stat-row">
            <div class="stat"><div class="n"><?= $total ?></div><div class="l">Total</div></div>
            <div class="stat"><div class="n"><?= $menunggu ?></div><div class="l">Menunggu</div></div>
            <div class="stat"><div class="n"><?= $proses ?></div><div class="l">Diproses</div></div>
            <div class="stat"><div class="n"><?= $selesai ?></div><div class="l">Selesai</div></div>
        </div>

        <?php if (empty($laporan)): ?>
            <div class="empty-state">
                <div class="mark">WO-0000</div>
                <p>Belum ada laporan. Temukan fasilitas yang rusak? Laporkan sekarang.</p>
                <a href="lapor.php" class="btn btn-primary">Buat laporan pertama</a>
            </div>
        <?php else: ?>
            <div class="ticket-grid">
                <?php foreach ($laporan as $r): ?>
                    <a class="ticket" href="detail.php?id=<?= $r['id'] ?>" style="display:block;color:inherit;">
                        <div class="ticket-head">
                            <span class="ticket-code mono"><?= bersih($r['kode_tiket']) ?></span>
                            <span class="<?= statusClass($r['status']) ?>"><?= $r['status'] ?></span>
                        </div>
                        <h3><?= bersih($r['nama_fasilitas']) ?></h3>
                        <div class="loc">📍 <?= bersih($r['lokasi']) ?> · <?= bersih($r['kategori']) ?></div>
                        <p class="desc"><?= bersih($r['deskripsi']) ?></p>
                        <div class="ticket-foot">
                            <span>Tingkat: <?= $r['tingkat_kerusakan'] ?></span>
                            <span><?= date('d M Y', strtotime($r['created_at'])) ?></span>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>
</div>
</body>
</html>
