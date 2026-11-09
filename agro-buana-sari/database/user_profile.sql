USE agro_buana_sari;

CREATE TABLE IF NOT EXISTS user_profiles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL UNIQUE,
  nama_lengkap VARCHAR(150) DEFAULT NULL,
  nama_panggilan VARCHAR(80) DEFAULT NULL,
  jenis_kelamin VARCHAR(30) DEFAULT NULL,
  tempat_lahir VARCHAR(100) DEFAULT NULL,
  tanggal_lahir DATE DEFAULT NULL,
  alamat TEXT DEFAULT NULL,
  kota VARCHAR(100) DEFAULT NULL,
  kode_pos VARCHAR(20) DEFAULT NULL,
  status_pernikahan VARCHAR(40) DEFAULT NULL,
  nama_ayah VARCHAR(150) DEFAULT NULL,
  nama_ibu VARCHAR(150) DEFAULT NULL,
  nama_pasangan VARCHAR(150) DEFAULT NULL,
  jumlah_saudara INT DEFAULT NULL,
  pendidikan_terakhir VARCHAR(100) DEFAULT NULL,
  institusi_pendidikan VARCHAR(180) DEFAULT NULL,
  jurusan VARCHAR(150) DEFAULT NULL,
  tahun_lulus VARCHAR(10) DEFAULT NULL,
  organisasi_nama VARCHAR(180) DEFAULT NULL,
  organisasi_jabatan VARCHAR(120) DEFAULT NULL,
  organisasi_periode VARCHAR(100) DEFAULT NULL,
  organisasi_deskripsi TEXT DEFAULT NULL,
  pengalaman_perusahaan VARCHAR(180) DEFAULT NULL,
  pengalaman_jabatan VARCHAR(150) DEFAULT NULL,
  pengalaman_periode VARCHAR(100) DEFAULT NULL,
  pengalaman_deskripsi TEXT DEFAULT NULL,
  kelebihan TEXT DEFAULT NULL,
  kekurangan TEXT DEFAULT NULL,
  motivasi_kerja TEXT DEFAULT NULL,
  tujuan_karier TEXT DEFAULT NULL,
  riwayat_kesehatan TEXT DEFAULT NULL,
  alergi TEXT DEFAULT NULL,
  kontak_darurat_nama VARCHAR(150) DEFAULT NULL,
  kontak_darurat_hubungan VARCHAR(80) DEFAULT NULL,
  kontak_darurat_no_hp VARCHAR(40) DEFAULT NULL,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_profile_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS user_documents (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  jenis_dokumen VARCHAR(50) NOT NULL,
  nama_file VARCHAR(255) NOT NULL,
  file_asli VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_user_document (user_id, jenis_dokumen),
  CONSTRAINT fk_document_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO user_profiles (user_id, nama_lengkap)
SELECT u.id, u.nama FROM users u
LEFT JOIN user_profiles p ON p.user_id=u.id
WHERE p.id IS NULL;
