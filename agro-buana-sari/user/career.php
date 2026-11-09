<?php
require_once '../config/koneksi.php';
require_once '../inc/user_auth.php';
require_user();

$user = current_user($conn);
if (!$user) { session_destroy(); header('Location: ../login.php'); exit; }

$stmt = $conn->prepare("SELECT a.*, c.posisi, c.lokasi, c.tipe FROM career_applications a LEFT JOIN career_positions c ON c.id=a.career_id WHERE a.user_id=? ORDER BY a.id DESC");
$stmt->bind_param('i', $user['id']);
$stmt->execute();
$applications = $stmt->get_result();
$positions = $conn->query("SELECT id,posisi,lokasi,tipe FROM career_positions WHERE status='Aktif' ORDER BY id DESC LIMIT 6");
?>
<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>My Career | PT Agro Buana Sari</title><link rel="stylesheet" href="../assets/css/style.css"></head><body class="user-page">
<header class="navbar"><div class="container nav-wrap"><a href="../index.php" class="brand"><img src="../assets/img/logo.png" alt="Logo PT Agro Buana Sari"><div><strong>PT AGRO</strong><span>BUANA SARI</span></div></a><nav><a href="../index.php#home">Home</a><a href="../index.php#tentang">Tentang Kami</a><a href="../index.php#produk">Produk</a><a href="../index.php#artikel">Artikel</a><a href="../career/">Career</a><a href="../index.php#galeri">Galeri</a><a href="../index.php#kontak">Kontak</a></nav><div class="nav-actions"><a class="nav-btn nav-user" href="profile.php">👤 <?=htmlspecialchars($user['nama'])?></a><a class="nav-btn nav-career" href="career.php">💼 My Career</a><a class="nav-btn nav-login" href="../logout.php">Keluar</a></div></div></header>
<main class="user-page-main"><div class="container user-container"><div class="user-heading"><div><p class="label">CAREER CENTER</p><h1>My Career</h1><p>Halo, <?=htmlspecialchars($user['nama'])?>. Pantau semua lamaran pekerjaan Anda di sini.</p></div><a class="btn" href="../career/">+ Cari Lowongan</a></div>
<section class="career-account-card"><div class="account-card-title"><div><h2>Lamaran Saya</h2><p>Riwayat dan status proses rekrutmen Anda.</p></div><span class="application-count"><?= (int)$applications->num_rows ?> Lamaran</span></div>
<?php if($applications->num_rows): ?><div class="application-list"><?php while($a=$applications->fetch_assoc()): ?><?php $status=$a['status']??'Baru'; $statusClass=['Baru'=>'new','Diproses'=>'process','Diterima'=>'accepted','Ditolak'=>'rejected'][$status]??'new'; ?><article class="application-item"><div class="application-icon">💼</div><div class="application-main"><h3><?=htmlspecialchars($a['posisi']??'Posisi tidak tersedia')?></h3><p><?=htmlspecialchars($a['tipe']??'') ?> · 📍 <?=htmlspecialchars($a['lokasi']??'-')?></p><small>Dikirim <?=date('d M Y, H:i', strtotime($a['created_at']))?></small></div><span class="application-status <?=$statusClass?>"><?=htmlspecialchars($status)?></span></article><?php endwhile; ?></div><?php else: ?><div class="empty-user"><div>💼</div><h3>Belum ada lamaran</h3><p>Temukan lowongan yang sesuai dan kirim lamaran Anda.</p><a class="btn" href="../career/">Lihat Lowongan</a></div><?php endif; ?></section>
<section class="recommended-career"><div class="account-card-title"><div><p class="label">OPEN POSITION</p><h2>Lowongan Aktif</h2></div><a href="../career/" class="btn btn-outline btn-small">Lihat Semua</a></div><div class="mini-career-grid"><?php if($positions && $positions->num_rows): while($c=$positions->fetch_assoc()): ?><a class="mini-career" href="../career/apply.php?id=<?=$c['id']?>"><strong><?=htmlspecialchars($c['posisi'])?></strong><span><?=htmlspecialchars($c['tipe'])?> · <?=htmlspecialchars($c['lokasi'])?></span><b>Lihat posisi →</b></a><?php endwhile; else: ?><p class="empty">Belum ada lowongan aktif.</p><?php endif; ?></div></section>
</div></main><footer><div class="container footer-wrap"><p>© <?=date('Y')?> PT Agro Buana Sari</p><p>Bersama Petani Sukses</p></div></footer></body></html>