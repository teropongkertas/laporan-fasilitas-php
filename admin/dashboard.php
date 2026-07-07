<?php
require '../includes/auth.php';
require '../config/database.php';
requireAdmin();

$active = 'admin_dashboard';

$filterStatus = bersih($_GET['status'] ?? '');
$cari         = bersih($_GET['cari'] ?? '');

$sql = 'SELECT r.*, u.nama AS pelapor_nama FROM reports r JOIN users u ON u.id = r.user_id WHERE 1=1';
$params = [];

if ($filterStatus !== '' && in_array($filterStatus, ['Menunggu','Diproses','Selesai','Ditolak'], true)) {
    $sql .= ' AND r.status = ?';
    $params[] = $filterStatus;
}
if ($cari !== '') {
    $sql .= ' AND (r.nama_fasilitas LIKE ? OR r.lokasi LIKE ? OR r.kode_tiket LIKE ?)';
    $like = "%$cari%";
    array_push($params, $like, $like, $like);
}
$sql .= ' ORDER BY FIELD(r.status, "Menunggu","Diproses","Selesai","Ditolak"), r.created_at DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$laporan = $stmt->fetchAll();

// Statistik keseluruhan (tidak terpengaruh filter)
$stat = $pdo->query(
    "SELECT
        COUNT(*) AS total,
        SUM(status='Menunggu') AS menunggu,
        SUM(status='Diproses') AS diproses,
        SUM(status='Selesai') AS selesai
     FROM reports"
)->fetch();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Semua Laporan — Panel Petugas</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="shell">
    <?php include '../includes/sidebar.php'; ?>

    <main class="main">
        <?php
        $pageEyebrow = 'Panel petugas';
        $pageTitle   = 'Semua laporan kerusakan';
        include '../includes/header.php';
        ?>

        <div class="stat-row">
            <div class="stat"><div class="n"><?= (int) $stat['total'] ?></div><div class="l">Total</div></div>
            <div class="stat"><div class="n"><?= (int) $stat['menunggu'] ?></div><div class="l">Menunggu</div></div>
            <div class="stat"><div class="n"><?= (int) $stat['diproses'] ?></div><div class="l">Diproses</div></div>
            <div class="stat"><div class="n"><?= (int) $stat['selesai'] ?></div><div class="l">Selesai</div></div>
        </div>

        <form method="get" style="display:flex;gap:10px;margin-bottom:18px;flex-wrap:wrap;">
            <input type="text" name="cari" placeholder="Cari nama fasilitas, lokasi, atau kode tiket..."
                   value="<?= bersih($cari) ?>" style="max-width:320px;">
            <select name="status" style="max-width:180px;">
                <option value="">Semua status</option>
                <?php foreach (['Menunggu','Diproses','Selesai','Ditolak'] as $s): ?>
                    <option value="<?= $s ?>" <?= $filterStatus === $s ? 'selected' : '' ?>><?= $s ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-outline">Terapkan</button>
            <?php if ($filterStatus || $cari): ?>
                <a href="dashboard.php" class="btn btn-outline">Reset</a>
            <?php endif; ?>
        </form>

        <?php if (empty($laporan)): ?>
            <div class="empty-state">
                <div class="mark">— kosong —</div>
                <p>Tidak ada laporan yang cocok dengan pencarian ini.</p>
            </div>
        <?php else: ?>
            <table class="data">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Fasilitas</th>
                        <th>Lokasi</th>
                        <th>Pelapor</th>
                        <th>Tingkat</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($laporan as $r): ?>
                    <tr>
                        <td class="mono" style="cursor:pointer;" onclick="window.location='../detail.php?id=<?= $r['id'] ?>'"><?= bersih($r['kode_tiket']) ?></td>
                        <td style="cursor:pointer;" onclick="window.location='../detail.php?id=<?= $r['id'] ?>'"><?= bersih($r['nama_fasilitas']) ?></td>
                        <td style="cursor:pointer;" onclick="window.location='../detail.php?id=<?= $r['id'] ?>'"><?= bersih($r['lokasi']) ?></td>
                        <td style="cursor:pointer;" onclick="window.location='../detail.php?id=<?= $r['id'] ?>'"><?= bersih($r['pelapor_nama']) ?></td>
                        <td style="cursor:pointer;" onclick="window.location='../detail.php?id=<?= $r['id'] ?>'"><?= bersih($r['tingkat_kerusakan']) ?></td>
                        <td style="cursor:pointer;" onclick="window.location='../detail.php?id=<?= $r['id'] ?>'"><span class="<?= statusClass($r['status']) ?>"><?= $r['status'] ?></span></td>
                        <td style="cursor:pointer;" onclick="window.location='../detail.php?id=<?= $r['id'] ?>'"><?= date('d M Y', strtotime($r['created_at'])) ?></td>
                        <td onclick="event.stopPropagation()">
                            <form method="post" action="../hapus.php" style="display:inline;"
                                  onsubmit="return confirm('Hapus laporan <?= bersih($r['kode_tiket']) ?> secara permanen?');">
                                <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                <button type="submit" class="btn btn-outline btn-sm" style="color:var(--red);border-color:var(--red);">Hapus</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        <?php include '../includes/footer.php'; ?>