<?php
// Variabel $active dipakai untuk menandai menu aktif, di-set di masing-masing halaman
$bp = basePath();
?>
<aside class="sidebar">
    <div>
        <div class="brand"><span class="tag"></span> Lapor Fasilitas</div>
        <div class="sub">Sarana &amp; Prasarana Kampus</div>
    </div>
    <nav>
        <?php if (isAdmin()): ?>
            <a href="<?= $bp ?>admin/dashboard.php" class="<?= ($active ?? '') === 'admin_dashboard' ? 'active' : '' ?>">Semua Laporan</a>
        <?php else: ?>
            <a href="<?= $bp ?>dashboard.php" class="<?= ($active ?? '') === 'dashboard' ? 'active' : '' ?>">Laporan Saya</a>
            <a href="<?= $bp ?>lapor.php" class="<?= ($active ?? '') === 'lapor' ? 'active' : '' ?>">Buat Laporan</a>
        <?php endif; ?>
    </nav>
    <div class="user-box">
        <div class="name"><?= bersih($_SESSION['nama']) ?></div>
        <div class="role"><?= isAdmin() ? 'Petugas' : 'Pelapor' ?></div>
        <a class="logout" href="<?= $bp ?>logout.php">Keluar &rarr;</a>
    </div>
</aside>
