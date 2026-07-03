<?php
require '../includes/auth.php';
require '../config/database.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard.php');
    exit;
}

$id            = (int) ($_POST['id'] ?? 0);
$status        = bersih($_POST['status'] ?? '');
$catatan_admin = bersih($_POST['catatan_admin'] ?? '');

$statusValid = ['Menunggu','Diproses','Selesai','Ditolak'];

if ($id > 0 && in_array($status, $statusValid, true)) {
    $stmt = $pdo->prepare('UPDATE reports SET status = ?, catatan_admin = ? WHERE id = ?');
    $stmt->execute([$status, $catatan_admin, $id]);
}

header('Location: ../detail.php?id=' . $id);
exit;
