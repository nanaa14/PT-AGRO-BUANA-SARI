PT AGRO BUANA SARI - UPDATE FITUR

Fitur tambahan:
- Login Pengguna/Pelanggan: /login.php
- Buat akun: /register.php
- Login Perusahaan/Admin terpisah: /company/login.php
- Panel perusahaan: /admin/index.php
- Kelola stok produk dari panel perusahaan
- Pembelian produk + pengurangan stok + pengiriman detail pesanan ke WhatsApp
- Karier publik: /career/index.php
- Pengguna login dapat melamar lowongan + upload CV PDF opsional
- Perusahaan dapat membuat/menutup lowongan dan melihat lamaran
- Tampilan responsive desktop/tablet/HP

INSTALASI
1. Backup folder project lama.
2. Copy seluruh isi paket ini ke C:\xampp\htdocs\agro-buana-sari (timpa file yang sama, jangan hapus assets/uploads/config/database lama).
3. Jalankan Apache dan MySQL di XAMPP.
4. Buka phpMyAdmin, pilih database agro_buana_sari.
5. Jalankan/import database/ecommerce_career_update.sql.
6. Buka http://localhost/agro-buana-sari/

LOGIN PERUSAHAAN
URL: http://localhost/agro-buana-sari/company/login.php
Username: admin
Password: admin123

Catatan: login perusahaan memakai tabel admin yang sudah ada. Setelah masuk, gunakan menu Produk & Stok untuk mengubah stok. Ganti password admin untuk penggunaan produksi.

LOGIN PENGGUNA
Buat akun melalui tombol Login Pengguna -> Buat Akun.

KARIER
Publik: http://localhost/agro-buana-sari/career/index.php
Admin: masuk melalui Login Perusahaan -> Kelola Karier.


FITUR USER PROFILE & MY CAREER
- My Profile sekarang memiliki Data Diri, Data Keluarga, Data Pendidikan, Pengalaman Organisasi, Pengalaman Kerja, Pernyataan Pribadi, Riwayat Kesehatan, dan Upload Dokumen.
- Jalankan database/user_profile.sql pada database agro_buana_sari.
- Dokumen user disimpan di uploads/documents.
- CV tersimpan dapat dipakai otomatis saat apply lowongan.
