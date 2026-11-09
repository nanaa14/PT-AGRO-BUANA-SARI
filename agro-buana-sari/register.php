<?php
session_start();
if (!empty($_SESSION['user_id'])) { header('Location: index.php'); exit; }
require_once 'config/koneksi.php';
$error=''; $success='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $nama=trim($_POST['nama']??''); $email=trim($_POST['email']??''); $no_hp=trim($_POST['no_hp']??''); $password=$_POST['password']??''; $confirm=$_POST['confirm_password']??'';
    if($nama===''||$email===''||$password==='') $error='Nama, email, dan password wajib diisi.';
    elseif(!filter_var($email,FILTER_VALIDATE_EMAIL)) $error='Format email tidak valid.';
    elseif(strlen($password)<6) $error='Password minimal 6 karakter.';
    elseif($password!==$confirm) $error='Konfirmasi password tidak sama.';
    else { $check=$conn->prepare('SELECT id FROM users WHERE email=? LIMIT 1');$check->bind_param('s',$email);$check->execute(); if($check->get_result()->fetch_assoc()) $error='Email sudah terdaftar.'; }
    if(!$error){$hash=password_hash($password,PASSWORD_DEFAULT);$s=$conn->prepare('INSERT INTO users(nama,email,password,no_hp) VALUES(?,?,?,?)');$s->bind_param('ssss',$nama,$email,$hash,$no_hp);if($s->execute()){$success='Akun berhasil dibuat. Silakan login.';}else{$error='Akun gagal dibuat.';}}
}
?>
<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Buat Akun | PT Agro Buana Sari</title><link rel="stylesheet" href="assets/css/style.css"></head><body class="auth-page"><div class="auth-card"><a class="auth-back" href="index.php">← Kembali ke website</a><div class="auth-logo"><img src="assets/img/logo.png" alt="Logo PT Agro Buana Sari"></div><p class="label">AREA PELANGGAN</p><h1>Buat Akun</h1><p class="auth-subtitle">Buat akun untuk melakukan pemesanan dan melamar karier.</p><?php if($error): ?><div class="alert"><?=htmlspecialchars($error)?></div><?php endif; ?><?php if($success): ?><div class="alert success"><?=htmlspecialchars($success)?> <a href="login.php">Login sekarang</a></div><?php endif; ?><form method="post"><div class="form-group"><label>Nama Lengkap</label><input name="nama" required></div><div class="form-group"><label>Email</label><input type="email" name="email" required></div><div class="form-group"><label>No. WhatsApp</label><input name="no_hp"></div><div class="form-group"><label>Password</label><input type="password" name="password" required minlength="6"></div><div class="form-group"><label>Konfirmasi Password</label><input type="password" name="confirm_password" required minlength="6"></div><button class="btn" type="submit">Buat Akun</button></form><p class="auth-switch">Sudah punya akun? <a href="login.php">Login Pengguna</a></p></div></body></html>
