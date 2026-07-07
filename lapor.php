<?php
require 'includes/auth.php';
require 'config/database.php';
requireLogin();

if (isAdmin()) {
    header('Location: admin/dashboard.php');
    exit;
}

$active = 'lapor';
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
        $fotoNama = null;

        // Unggah foto (opsional)
        if (!empty($_FILES['foto']['name'])) {
            $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];

            if (!in_array($ext, $allowedExt, true)) {
                $error = 'Foto harus berformat JPG, PNG, atau WEBP.';
            } elseif ($_FILES['foto']['size'] > 5 * 1024 * 1024) {
                $error = 'Ukuran foto maksimal 5MB.';
            } else {
                $fotoNama = uniqid('foto_', true) . '.' . $ext;
                $tujuan = __DIR__ . '/assets/uploads/' . $fotoNama;
                if (!move_uploaded_file($_FILES['foto']['tmp_name'], $tujuan)) {
                    $error = 'Gagal mengunggah foto. Silakan coba lagi.';
                    $fotoNama = null;
                }
            }
        }

        if ($error === '') {
            $kode = buatKodeTiket();
            $stmt = $pdo->prepare(
                'INSERT INTO reports (kode_tiket, user_id, nama_fasilitas, kategori, lokasi, tingkat_kerusakan, deskripsi, foto)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([$kode, $_SESSION['user_id'], $nama_fasilitas, $kategori, $lokasi, $tingkat, $deskripsi, $fotoNama]);

            setFlash('success', 'Laporan berhasil dikirim. Petugas akan segera meninjau.');
            header('Location: dashboard.php');
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
<title>Buat Laporan — Lapor Fasilitas</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="shell">
    <?php include 'includes/sidebar.php'; ?>

    <main class="main">
        <?php
        $pageEyebrow = 'Surat perintah kerja baru';
        $pageTitle   = 'Laporkan fasilitas rusak';
        include 'includes/header.php';
        ?>

        <?php if ($error): ?>
            <div class="alert alert-error"><span><?= $error ?></span></div>
        <?php endif; ?>

        <form class="form-card" method="post" enctype="multipart/form-data">
            <div class="field">
                <label for="nama_fasilitas">Nama fasilitas</label>
                <input type="text" id="nama_fasilitas" name="nama_fasilitas"
                       placeholder="Contoh: AC ruang kelas, Kran wastafel, Proyektor"
                       value="<?= bersih($_POST['nama_fasilitas'] ?? '') ?>" required>
            </div>

            <div class="grid-2">
                <div class="field">
                    <label for="kategori">Kategori</label>
                    <select id="kategori" name="kategori" required>
                        <option value="">Pilih kategori</option>
                        <?php foreach ($kategoriList as $k): ?>
                            <option value="<?= $k ?>" <?= (($_POST['kategori'] ?? '') === $k) ? 'selected' : '' ?>><?= $k ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field">
                    <label for="tingkat_kerusakan">Tingkat kerusakan</label>
                    <select id="tingkat_kerusakan" name="tingkat_kerusakan" required>
                        <option value="Ringan">Ringan — masih bisa dipakai</option>
                        <option value="Sedang" selected>Sedang — mengganggu aktivitas</option>
                        <option value="Berat">Berat — tidak bisa dipakai</option>
                    </select>
                </div>
            </div>

            <div class="field">
                <label for="lokasi">Lokasi</label>
                <input type="text" id="lokasi" name="lokasi"
                       placeholder="Contoh: Gedung B, Lantai 2, Ruang 204"
                       value="<?= bersih($_POST['lokasi'] ?? '') ?>" required>
            </div>

            <div class="field">
                <label for="deskripsi">Deskripsi kerusakan</label>
                <textarea id="deskripsi" name="deskripsi" placeholder="Jelaskan kondisi kerusakan yang Anda temukan..." required><?= bersih($_POST['deskripsi'] ?? '') ?></textarea>
            </div>

            <div class="field">
                <label for="foto">Foto kerusakan (opsional)</label>
                <input type="file" id="foto" name="foto" accept=".jpg,.jpeg,.png,.webp">
                <div class="hint">Format JPG/PNG/WEBP, maksimal 5MB. Foto membantu petugas menilai kerusakan lebih cepat.</div>
            </div>

            <button type="submit" class="btn btn-primary">Kirim laporan</button>
        </form>
        <?php include 'includes/footer.php'; ?>