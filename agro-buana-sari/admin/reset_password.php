<?php
// Jalankan sekali melalui browser: http://localhost/agro-buana-sari/admin/reset_password.php
// Setelah berhasil, HAPUS file ini dari folder admin.
require_once '../config/koneksi.php';
$hash=password_hash('admin123',PASSWORD_DEFAULT);
$s=$conn->prepare("UPDATE admin SET password=? WHERE username='admin'");$s->bind_param('s',$hash);$s->execute();echo 'Password admin sudah di-reset menjadi admin123. HAPUS FILE reset_password.php setelah ini.';
?>