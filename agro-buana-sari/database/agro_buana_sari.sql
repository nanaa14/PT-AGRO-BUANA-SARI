-- ============================================================
-- DATABASE PT AGRO BUANA SARI
-- ============================================================

CREATE DATABASE IF NOT EXISTS agro_buana_sari
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE agro_buana_sari;


-- ============================================================
-- TABEL ADMIN
-- ============================================================

CREATE TABLE IF NOT EXISTS admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- TABEL PRODUK
-- ============================================================

CREATE TABLE IF NOT EXISTS produk (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_produk VARCHAR(150) NOT NULL,
    deskripsi TEXT NOT NULL,
    stok INT NOT NULL DEFAULT 0,
    foto VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- TABEL ARTIKEL
-- ============================================================

CREATE TABLE IF NOT EXISTS artikel (
    id INT AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(200) NOT NULL,
    isi LONGTEXT NOT NULL,
    foto VARCHAR(255) DEFAULT NULL,
    link VARCHAR(500) DEFAULT NULL,
    tanggal DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- TABEL GALERI
-- ============================================================

CREATE TABLE IF NOT EXISTS galeri (
    id INT AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(150) DEFAULT NULL,
    deskripsi TEXT DEFAULT NULL,
    foto VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- TABEL PESANAN
-- ============================================================

CREATE TABLE IF NOT EXISTS pesanan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(150) NOT NULL,
    email VARCHAR(150) DEFAULT NULL,
    telepon VARCHAR(30) DEFAULT NULL,
    produk VARCHAR(150) DEFAULT NULL,
    jumlah INT DEFAULT 1,
    pesan TEXT DEFAULT NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'baru',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- TABEL CAREER POSITIONS
-- ============================================================

CREATE TABLE IF NOT EXISTS career_positions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    posisi VARCHAR(150) NOT NULL,
    deskripsi TEXT DEFAULT NULL,
    lokasi VARCHAR(150) DEFAULT NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- TABEL CAREER APPLICATIONS
-- ============================================================

CREATE TABLE IF NOT EXISTS career_applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    position_id INT DEFAULT NULL,
    nama VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL,
    telepon VARCHAR(30) DEFAULT NULL,
    cv VARCHAR(255) DEFAULT NULL,
    pesan TEXT DEFAULT NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'baru',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_career_application_position
        FOREIGN KEY (position_id)
        REFERENCES career_positions(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- TABEL USERS
-- ============================================================

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- TABEL USER PROFILES
-- ============================================================

CREATE TABLE IF NOT EXISTS user_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    nama_lengkap VARCHAR(150) DEFAULT NULL,
    telepon VARCHAR(30) DEFAULT NULL,
    alamat TEXT DEFAULT NULL,
    foto VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY unique_user_profile (user_id),

    CONSTRAINT fk_user_profile_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- TABEL USER DOCUMENTS
-- ============================================================

CREATE TABLE IF NOT EXISTS user_documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    nama_dokumen VARCHAR(150) NOT NULL,
    file VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_user_document_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- ADMIN DEFAULT
-- Username : admin
-- Password : admin123
-- ============================================================

INSERT INTO admin (
    nama,
    username,
    password
)
SELECT
    'Administrator',
    'admin',
    '$2y$12$3wsqYZSUJTv.J4oqgx84z.cpFXx1zkwkxRutPgOB2cmj.NmBHzJZ6'
WHERE NOT EXISTS (
    SELECT 1
    FROM admin
    WHERE username = 'admin'
);


-- ============================================================
-- PRODUK DEFAULT
-- ============================================================

INSERT INTO produk (
    nama_produk,
    deskripsi,
    stok
)
SELECT
    'Jagung Hibrida',
    'Pengembangan budidaya jagung hibrida melalui program kemitraan bersama petani.',
    0
WHERE NOT EXISTS (
    SELECT 1
    FROM produk
    WHERE nama_produk = 'Jagung Hibrida'
);


INSERT INTO produk (
    nama_produk,
    deskripsi,
    stok
)
SELECT
    'Cabai',
    'Pengembangan komoditas hortikultura untuk mendukung produktivitas petani.',
    0
WHERE NOT EXISTS (
    SELECT 1
    FROM produk
    WHERE nama_produk = 'Cabai'
);


INSERT INTO produk (
    nama_produk,
    deskripsi,
    stok
)
SELECT
    'Komoditas Pertanian',
    'Pengembangan berbagai komoditas sesuai potensi lahan dan kebutuhan pasar.',
    0
WHERE NOT EXISTS (
    SELECT 1
    FROM produk
    WHERE nama_produk = 'Komoditas Pertanian'
);


-- ============================================================
-- SELESAI
-- ============================================================

SELECT
    'Database agro_buana_sari berhasil disiapkan.' AS status;