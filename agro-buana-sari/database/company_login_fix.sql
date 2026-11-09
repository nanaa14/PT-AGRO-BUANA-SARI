USE agro_buana_sari;

CREATE TABLE IF NOT EXISTS admin (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(100) NOT NULL,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Akun perusahaan awal:
-- Username: admin
-- Password: admin123
INSERT INTO admin (nama, username, password)
VALUES ('Administrator', 'admin', '$2y$12$3wsqYZSUJTv.J4oqgx84z.cpFXx1zkwkxRutPgOB2cmj.NmBHzJZ6')
ON DUPLICATE KEY UPDATE
  nama = VALUES(nama),
  password = VALUES(password);
