<?php
require 'includes/auth.php';
require 'config/database.php';
requireLogin();

if (isAdmin()) {
    header('Location: admin/dashboard.php');
    exit;
}

$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);

$stmt = $pdo->prepare('SELECT * FROM reports WHERE id = ?');
$stmt->execute([$id]);
$r = $stmt->fetch();

// Hanya pemilik laporan yang boleh mengedit
if (!$r || (int) $r['user_id'] !== (int) $_SESSION['user_id']) {
    setFlash('error', 'Laporan tidak ditemukan atau bukan milik Anda.');
    header('Location: dashboard.php');
    exit;
}

// Laporan yang sudah diproses/selesai/ditolak tidak boleh diubah lagi
if ($r['status'] !== 'Menunggu') {
    setFlash('error', 'Laporan yang sudah ditinjau petugas tidak dapat diubah lagi.');
    header('Location: detail.php?id=' . $id);
    exit;
}

$active = 'dashboard';
$error  = '';

$kategoriList = ['Elektronik','Furnitur','Sanitasi','Bangunan','Pendingin Ruangan','Jaringan/Internet','Lainnya'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_fasilitas = bersih($_POST['nama_fasilitas'] ?? '');
    $kategori       = bersih($_POST['kategori'] ?? '');
    $lokasi         = bersih($_POST['lokasi'] ?? '');
    $tingkat        = bersih($_POST['tingkat_kerusakan'] ?? 'Sedang');
    $deskripsi      = bersih($_POST['deskripsi'] ?? '');

    if ($nama_fasilitas === '' || $lokasi === '' || $deskripsi === '' || !in_array($kategori, $kategoriList, true)) {
        $error = 'Mohon lengkapi semua kolom wajib dengan benar.';
    } else {
        $fotoNama = $r['foto'];

        // Ganti foto (opsional)
        if (!empty($_FILES['foto']['name'])) {
            $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];

            if (!in_array($ext, $allowedExt, true)) {
                $error = 'Foto harus berformat JPG, PNG, atau WEBP.';
            } elseif ($_FILES['foto']['size'] > 5 * 1024 * 1024) {
                $error = 'Ukuran foto maksimal 5MB.';
            } else {
                $fotoBaru = uniqid('foto_', true) . '.' . $ext;
                $tujuan   = __DIR__ . '/assets/uploads/' . $fotoBaru;

                if (!move_uploaded_file($_FILES['foto']['tmp_name'], $tujuan)) {
                    $error = 'Gagal mengunggah foto baru. Silakan coba lagi.';
                } else {
                    // Hapus foto lama jika ada, lalu pakai foto baru
                    if ($r['foto'] && is_file(__DIR__ . '/assets/uploads/' . $r['foto'])) {
                        @unlink(__DIR__ . '/assets/uploads/' . $r['foto']);
                    }
                    $fotoNama = $fotoBaru;
                }
            }
        }

        if ($error === '') {
            $stmt = $pdo->prepare(
                'UPDATE reports
                 SET nama_fasilitas = ?, kategori = ?, lokasi = ?, tingkat_kerusakan = ?, deskripsi = ?, foto = ?
                 WHERE id = ?'
            );
            $stmt->execute([$nama_fasilitas, $kategori, $lokasi, $tingkat, $deskripsi, $fotoNama, $id]);

            setFlash('success', 'Laporan ' . $r['kode_tiket'] . ' berhasil diperbarui.');
            header('Location: detail.php?id=' . $id);
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
<title>Edit <?= bersih($r['kode_tiket']) ?> — Lapor Fasilitas</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="shell">
    <?php include 'includes/sidebar.php'; ?>

    <main class="main">
        <?php
        $pageEyebrow  = bersih($r['kode_tiket']);
        $pageTitle    = 'Edit laporan';
        $headerAction = '<a href="detail.php?id=' . $id . '" class="btn btn-outline">&larr; Kembali</a>';
        include 'includes/header.php';
        ?>

        <?php if ($error): ?>
            <div class="alert alert-error"><span><?= $error ?></span></div>
        <?php endif; ?>

        <form class="form-card" method="post" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= $id ?>">

            <div class="field">
                <label for="nama_fasilitas">Nama fasilitas</label>
                <input type="text" id="nama_fasilitas" name="nama_fasilitas"
                       placeholder="Contoh: AC ruang kelas, Kran wastafel, Proyektor"
                       value="<?= bersih($_POST['nama_fasilitas'] ?? $r['nama_fasilitas']) ?>" required>
            </div>

            <div class="grid-2">
                <div class="field">
                    <label for="kategori">Kategori</label>
                    <select id="kategori" name="kategori" required>
                        <option value="">Pilih kategori</option>
                        <?php $kategoriSaatIni = $_POST['kategori'] ?? $r['kategori']; ?>
                        <?php foreach ($kategoriList as $k): ?>
                            <option value="<?= $k ?>" <?= ($kategoriSaatIni === $k) ? 'selected' : '' ?>><?= $k ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field">
                    <label for="tingkat_kerusakan">Tingkat kerusakan</label>
                    <?php $tingkatSaatIni = $_POST['tingkat_kerusakan'] ?? $r['tingkat_kerusakan']; ?>
                    <select id="tingkat_kerusakan" name="tingkat_kerusakan" required>
                        <option value="Ringan" <?= $tingkatSaatIni === 'Ringan' ? 'selected' : '' ?>>Ringan — masih bisa dipakai</option>
                        <option value="Sedang" <?= $tingkatSaatIni === 'Sedang' ? 'selected' : '' ?>>Sedang — mengganggu aktivitas</option>
                        <option value="Berat" <?= $tingkatSaatIni === 'Berat' ? 'selected' : '' ?>>Berat — tidak bisa dipakai</option>
                    </select>
                </div>
            </div>

            <div class="field">
                <label for="lokasi">Lokasi</label>
                <input type="text" id="lokasi" name="lokasi"
                       placeholder="Contoh: Gedung B, Lantai 2, Ruang 204"
                       value="<?= bersih($_POST['lokasi'] ?? $r['lokasi']) ?>" required>
            </div>

            <div class="field">
                <label for="deskripsi">Deskripsi kerusakan</label>
                <textarea id="deskripsi" name="deskripsi" placeholder="Jelaskan kondisi kerusakan yang Anda temukan..." required><?= bersih($_POST['deskripsi'] ?? $r['deskripsi']) ?></textarea>
            </div>

            <?php if ($r['foto']): ?>
                <div class="field">
                    <label>Foto saat ini</label>
                    <img src="assets/uploads/<?= bersih($r['foto']) ?>" alt="Foto kerusakan saat ini"
                         style="max-width:220px;border-radius:8px;border:1px solid var(--line);display:block;">
                </div>
            <?php endif; ?>

            <div class="field">
                <label for="foto">Ganti foto (opsional)</label>
                <input type="file" id="foto" name="foto" accept=".jpg,.jpeg,.png,.webp">
                <div class="hint">Kosongkan jika tidak ingin mengganti foto. Format JPG/PNG/WEBP, maksimal 5MB.</div>
            </div>

            <button type="submit" class="btn btn-primary">Simpan perubahan</button>
            <a href="detail.php?id=<?= $id ?>" class="btn btn-outline" style="margin-left:8px;">Batal</a>
        </form>
        <?php include 'includes/footer.php'; ?>