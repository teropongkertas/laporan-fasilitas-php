<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/** Cek apakah pengguna sudah login */
function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']);
}

/** Cek apakah pengguna adalah admin/petugas */
function isAdmin(): bool
{
    return isLoggedIn() && $_SESSION['role'] === 'admin';
}

/** Wajib login, redirect ke login.php jika belum */
function requireLogin(): void
{
    if (!isLoggedIn()) {
        header('Location: ' . basePath() . 'login.php');
        exit;
    }
}

/** Wajib admin, redirect jika bukan admin */
function requireAdmin(): void
{
    requireLogin();
    if (!isAdmin()) {
        header('Location: ' . basePath() . 'dashboard.php');
        exit;
    }
}

/** Path relatif dasar (agar link tetap benar dari dalam folder admin/) */
function basePath(): string
{
    return (strpos($_SERVER['SCRIPT_NAME'], '/admin/') !== false) ? '../' : '';
}

/** Bersihkan input string */
function bersih(string $data): string
{
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/** Buat kode tiket unik, mis: WO-20260703-0472 */
function buatKodeTiket(): string
{
    return 'WO-' . date('Ymd') . '-' . str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
}

/** Kelas badge CSS berdasarkan status */
function statusClass(string $status): string
{
    return match ($status) {
        'Menunggu' => 'badge badge-menunggu',
        'Diproses' => 'badge badge-diproses',
        'Selesai'  => 'badge badge-selesai',
        'Ditolak'  => 'badge badge-ditolak',
        default    => 'badge',
    };
}
