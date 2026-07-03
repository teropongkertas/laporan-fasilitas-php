# Lapor Fasilitas — Sistem Pelaporan Fasilitas Rusak Kampus

Aplikasi web PHP + MySQL untuk melaporkan dan memantau perbaikan fasilitas kampus yang rusak (AC, proyektor, kursi, sanitasi, jaringan, dll), bertema "surat perintah kerja" (work order) teknisi.

## Fitur

- **Pelapor (mahasiswa/dosen)**: daftar/masuk, buat laporan lengkap dengan foto, pantau status laporan sendiri.
- **Petugas (admin)**: lihat semua laporan, cari & filter berdasarkan status, ubah status (Menunggu → Diproses → Selesai/Ditolak), beri catatan untuk pelapor.
- Kode tiket unik otomatis per laporan (format `WO-YYYYMMDD-XXXX`).
- Upload foto kerusakan (validasi tipe & ukuran file).
- Password di-hash dengan `password_hash()`, query memakai prepared statement (PDO) — aman dari SQL Injection.

## Struktur folder

```
lapor-fasilitas/
├── admin/
│   ├── dashboard.php       # daftar semua laporan (petugas)
│   └── update_status.php   # proses ubah status
├── assets/
│   ├── css/style.css
│   └── uploads/            # foto laporan yang diunggah
├── config/database.php     # koneksi PDO
├── includes/
│   ├── auth.php            # sesi, otorisasi, fungsi bantu
│   └── sidebar.php         # navigasi
├── database.sql            # skema + akun admin default
├── index.php
├── login.php / register.php / logout.php
├── dashboard.php           # daftar laporan milik pelapor
├── lapor.php               # form buat laporan
└── detail.php              # detail laporan + form ubah status (admin)
```

## Cara instalasi (XAMPP/Laragon/local server)

1. Salin folder `lapor-fasilitas` ke direktori web server, contoh: `htdocs/lapor-fasilitas`.
2. Buat database dengan mengimpor `database.sql` lewat phpMyAdmin atau:
   ```bash
   mysql -u root -p < database.sql
   ```
3. Sesuaikan kredensial database di `config/database.php` (`DB_HOST`, `DB_USER`, `DB_PASS`) jika berbeda dari default XAMPP (`root` tanpa password).
4. Pastikan folder `assets/uploads/` bisa ditulis oleh web server:
   ```bash
   chmod 755 assets/uploads
   ```
5. Buka `http://localhost/lapor-fasilitas/` di browser.

## Akun demo

| Peran   | Email                 | Password  |
|---------|-----------------------|-----------|
| Petugas | admin@kampus.ac.id    | admin123  |
| Pelapor | *(daftar sendiri lewat halaman Register)* | — |

Segera ganti password admin default setelah instalasi.

## Pengembangan lanjutan (opsional)

- Notifikasi email saat status laporan berubah.
- Multi-foto per laporan.
- Statistik/grafik laporan per kategori & lokasi.
- Role tambahan "teknisi" yang berbeda dari admin.
