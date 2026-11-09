<?php
session_start();
if (!empty($_SESSION['user_id'])) { header('Location: index.php'); exit; }
require_once 'config/koneksi.php';
$error='';
$redirect=$_GET['redirect']??$_POST['redirect']??'index.php';
if (!preg_match('/^(?:[a-zA-Z0-9_\-\/\?=&.#]+)$/', $redirect) || str_starts_with($redirect,'http')) $redirect='index.php';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $email=trim($_POST['email']??''); $password=$_POST['password']??'';
    $stmt=$conn->prepare('SELECT id,nama,email,password FROM users WHERE email=? LIMIT 1');
    $stmt->bind_param('s',$email); $stmt->execute(); $user=$stmt->get_result()->fetch_assoc();
    if ($user && password_verify($password,$user['password'])) {
        session_regenerate_id(true); $_SESSION['user_id']=$user['id']; $_SESSION['user_nama']=$user['nama']; $_SESSION['user_email']=$user['email'];
        header('Location: '.($redirect ?: 'index.php')); exit;
    }
    $error='Email atau password pengguna salah.';
}
?>
<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Login Pengguna | PT Agro Buana Sari</title><link rel="stylesheet" href="assets/css/style.css"></head>
<body class="auth-page"><div class="auth-card"><a class="auth-back" href="index.php">← Kembali ke website</a><div class="auth-logo"><img src="assets/img/logo.png" alt="Logo PT Agro Buana Sari"></div><p class="label">AREA PELANGGAN</p><h1>Login Pengguna</h1><p class="auth-subtitle">Masuk untuk membeli produk dan melamar lowongan karier.</p><?php if($error): ?><div class="alert"><?=htmlspecialchars($error)?></div><?php endif; ?><form method="post"><input type="hidden" name="redirect" value="<?=htmlspecialchars($redirect)?>"><div class="form-group"><label>Email</label><input type="email" name="email" required autocomplete="email"></div><div class="form-group"><label>Password</label><input type="password" name="password" required autocomplete="current-password"></div><button class="btn" type="submit">Masuk sebagai Pengguna</button></form><p class="auth-switch">Belum punya akun? <a href="register.php">Buat akun</a></p><div class="auth-company">Login khusus perusahaan → <a href="company/login.php">Login Perusahaan</a></div></div></body></html>
