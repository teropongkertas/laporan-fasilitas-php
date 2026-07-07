<?php
/**
 * Header halaman (dalam .main), dipakai di semua halaman ber-layout "shell".
 * Variabel yang bisa di-set sebelum include file ini:
 *   $pageEyebrow   (string, opsional) teks kecil di atas judul
 *   $pageTitle     (string) judul halaman
 *   $headerAction  (string HTML, opsional) tombol di kanan topbar
 */
$flash = getFlash();
?>
<div class="topbar">
    <div>
        <?php if (!empty($pageEyebrow)): ?>
            <span class="eyebrow"><?= $pageEyebrow ?></span>
        <?php endif; ?>
        <h1><?= $pageTitle ?? '' ?></h1>
    </div>
    <?php if (!empty($headerAction)): ?>
        <div><?= $headerAction ?></div>
    <?php endif; ?>
</div>

<?php if ($flash): ?>
    <div class="alert alert-<?= $flash['tipe'] === 'error' ? 'error' : 'success' ?>" data-alert>
        <span><?= bersih($flash['pesan']) ?></span>
        <button type="button" class="alert-close" onclick="this.parentElement.remove()" aria-label="Tutup notifikasi">&times;</button>
    </div>
<?php endif; ?>