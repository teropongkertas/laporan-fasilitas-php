<?php
require 'includes/auth.php';
require 'config/database.php';
requireLogin();

$balikUrl = isAdmin() ? 'admin/dashboard.php' : 'dashboard.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . $balikUrl);
    exit;
}

$id = (int) ($_POST['id'] ?? 0);

$stmt = $pdo->prepare('SELECT * FROM reports WHERE id = ?');
$stmt->execute([$id]);
$r = $stmt->fetch();

if (!$r) {
    setFlash('error', 'Laporan tidak ditemukan.');
    header('Location: ' . $balikUrl);
    exit;
}

// Petugas boleh menghapus laporan apa saja.
// Pelapor hanya boleh menghapus laporannya sendiri selama masih berstatus "Menunggu".
$bolehHapus = isAdmin()
    || ((int) $r['user_id'] === (int) $_SESSION['user_id'] && $r['status'] === 'Menunggu');

if (!$bolehHapus) {
    setFlash('error', 'Anda tidak berhak menghapus laporan ini.');
    header('Location: ' . (isAdmin() ? $balikUrl : 'detail.php?id=' . $id));
    exit;
}

$stmt = $pdo->prepare('DELETE FROM reports WHERE id = ?');
$stmt->execute([$id]);

// Bersihkan file foto terkait, jika ada
if ($r['foto']) {
    $file = __DIR__ . '/assets/uploads/' . $r['foto'];
    if (is_file($file)) {
        @unlink($file);
    }
}

setFlash('success', 'Laporan ' . $r['kode_tiket'] . ' berhasil dihapus.');
header('Location: ' . $balikUrl);
exit;