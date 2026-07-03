-- =========================================================
-- Database: lapor_fasilitas
-- Sistem Pelaporan Fasilitas Rusak Kampus
-- =========================================================

CREATE DATABASE IF NOT EXISTS lapor_fasilitas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE lapor_fasilitas;

-- Tabel pengguna (mahasiswa/dosen & admin/petugas)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('pelapor', 'admin') NOT NULL DEFAULT 'pelapor',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Tabel laporan kerusakan fasilitas
CREATE TABLE IF NOT EXISTS reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode_tiket VARCHAR(20) NOT NULL UNIQUE,
    user_id INT NOT NULL,
    nama_fasilitas VARCHAR(150) NOT NULL,
    kategori ENUM('Elektronik','Furnitur','Sanitasi','Bangunan','Pendingin Ruangan','Jaringan/Internet','Lainnya') NOT NULL,
    lokasi VARCHAR(150) NOT NULL,
    tingkat_kerusakan ENUM('Ringan','Sedang','Berat') NOT NULL DEFAULT 'Sedang',
    deskripsi TEXT NOT NULL,
    foto VARCHAR(255) DEFAULT NULL,
    status ENUM('Menunggu','Diproses','Selesai','Ditolak') NOT NULL DEFAULT 'Menunggu',
    catatan_admin TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Akun admin default
-- Email   : pratiwiiutami@gmail.com
-- Password: admin123
INSERT INTO users (nama, email, password, role) VALUES
('Petugas Sarana Prasarana', 'pratiwiiutami@gmail.com', '$2b$10$ssrZopTEl.NjuPvfGX04dehlQQDUKNq9FhAd.SZInUw9FR62g06C.', 'admin');

-- Catatan: hash di atas adalah hasil password_hash('admin123', PASSWORD_DEFAULT)
-- Silakan login dengan email pratiwiiutami@gmail.com dan password admin123, lalu segera ganti password.
