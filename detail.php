<?php
require 'includes/auth.php';
require 'config/database.php';
requireLogin();

$id = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare(
    'SELECT r.*, u.nama AS pelapor_nama, u.email AS pelapor_email
     FROM reports r JOIN users u ON u.id = r.user_id
     WHERE r.id = ?'
);
$stmt->execute([$id]);
$r = $stmt->fetch();

if (!$r || (!isAdmin() && $r['user_id'] !== $_SESSION['user_id'])) {
    header('Location: ' . (isAdmin() ? 'admin/dashboard.php' : 'dashboard.php'));
    exit;
}

$active = isAdmin() ? 'admin_dashboard' : 'dashboard';
$balikUrl = isAdmin() ? 'admin/dashboard.php' : 'dashboard.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= bersih($r['kode_tiket']) ?> — Lapor Fasilitas</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="shell">
    <?php include 'includes/sidebar.php'; ?>

    <main class="main">
        <div class="topbar">
            <div>
                <span class="eyebrow mono"><?= bersih($r['kode_tiket']) ?></span>
                <h1><?= bersih($r['nama_fasilitas']) ?></h1>
            </div>
            <a href="<?= $balikUrl ?>" class="btn btn-outline">&larr; Kembali</a>
        </div>

        <div class="form-card" style="max-width:760px;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;">
                <span class="<?= statusClass($r['status']) ?>" style="font-size:.8rem;padding:6px 14px;"><?= $r['status'] ?></span>
                <span style="font-size:.8rem;color:var(--ink-soft);">Dilaporkan <?= date('d M Y, H:i', strtotime($r['created_at'])) ?></span>
            </div>

            <?php if ($r['foto']): ?>
                <img src="assets/uploads/<?= bersih($r['foto']) ?>" alt="Foto kerusakan"
                     style="width:100%;max-height:340px;object-fit:cover;border-radius:8px;margin-bottom:18px;border:1px solid var(--line);">
            <?php endif; ?>

            <div class="grid-2" style="margin-bottom:18px;">
                <div>
                    <div class="field"><label>Kategori</label><div><?= bersih($r['kategori']) ?></div></div>
                    <div class="field"><label>Tingkat kerusakan</label><div><?= bersih($r['tingkat_kerusakan']) ?></div></div>
                </div>
                <div>
                    <div class="field"><label>Lokasi</label><div><?= bersih($r['lokasi']) ?></div></div>
                    <div class="field"><label>Pelapor</label><div><?= bersih($r['pelapor_nama']) ?></div></div>
                </div>
            </div>

            <div class="field">
                <label>Deskripsi</label>
                <div style="white-space:pre-line;"><?= bersih($r['deskripsi']) ?></div>
            </div>

            <?php if ($r['catatan_admin']): ?>
                <div class="field">
                    <label>Catatan petugas</label>
                    <div style="white-space:pre-line;background:#F3F5F8;border-radius:6px;padding:12px 14px;"><?= bersih($r['catatan_admin']) ?></div>
                </div>
            <?php endif; ?>

            <?php if (isAdmin()): ?>
                <hr style="border:none;border-top:1px dashed var(--line);margin:22px 0;">
                <h3 style="font-size:1rem;margin-bottom:12px;">Perbarui status</h3>
                <form method="post" action="admin/update_status.php">
                    <input type="hidden" name="id" value="<?= $r['id'] ?>">
                    <div class="field">
                        <label for="status">Status</label>
                        <select name="status" id="status">
                            <?php foreach (['Menunggu','Diproses','Selesai','Ditolak'] as $s): ?>
                                <option value="<?= $s ?>" <?= $r['status'] === $s ? 'selected' : '' ?>><?= $s ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="field">
                        <label for="catatan_admin">Catatan untuk pelapor (opsional)</label>
                        <textarea name="catatan_admin" id="catatan_admin" placeholder="Contoh: Teknisi dijadwalkan Senin depan"><?= bersih($r['catatan_admin'] ?? '') ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Simpan perubahan</button>
                </form>
            <?php endif; ?>
        </div>
    </main>
</div>
</body>
</html>
