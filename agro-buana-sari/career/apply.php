<?php
require_once '../config/koneksi.php';
require_once '../inc/user_auth.php';
require_once '../inc/user_profile.php';
require_user();

$id=(int)($_GET['id']??0);
$s=$conn->prepare("SELECT * FROM career_positions WHERE id=? AND status='Aktif'");$s->bind_param('i',$id);$s->execute();$career=$s->get_result()->fetch_assoc();
if(!$career) die('Lowongan tidak ditemukan atau sudah ditutup.');
$user=current_user($conn); if(!$user){session_destroy();header('Location: ../login.php');exit;}
$userId=(int)$user['id'];
$profile=get_user_profile($conn,$userId);
$success='';$error='';
$cvDoc=null;
$d=$conn->prepare("SELECT * FROM user_documents WHERE user_id=? AND jenis_dokumen='CV' LIMIT 1");$d->bind_param('i',$userId);$d->execute();$cvDoc=$d->get_result()->fetch_assoc();

if($_SERVER['REQUEST_METHOD']==='POST'){
 $nama=trim($_POST['nama']??$user['nama']);$email=trim($_POST['email']??$user['email']);$no_hp=trim($_POST['no_hp']??$user['no_hp']);$pesan=trim($_POST['pesan']??'');$cv=null;
 if($nama===''||$email===''||$no_hp==='')$error='Nama, email, dan nomor WhatsApp wajib diisi.';
 elseif(!filter_var($email,FILTER_VALIDATE_EMAIL))$error='Email tidak valid.';
 if(!$error && !empty($_FILES['cv']['name'])){ $ext=strtolower(pathinfo($_FILES['cv']['name'],PATHINFO_EXTENSION)); if($ext!=='pdf'||$_FILES['cv']['size']>3*1024*1024)$error='CV baru harus PDF dan maksimal 3 MB.'; else {$safe='cv_'.$userId.'_'.time().'.pdf';$dest='../uploads/cv/'.$safe;if(!is_dir('../uploads/cv'))mkdir('../uploads/cv',0755,true);if(move_uploaded_file($_FILES['cv']['tmp_name'],$dest))$cv=$safe;else $error='CV gagal diunggah.';} }
 if(!$error && !$cv && $cvDoc) $cv=$cvDoc['nama_file'];
 if(!$error){
   $q=$conn->prepare('INSERT INTO career_applications(career_id,user_id,nama,email,no_hp,cv,pesan) VALUES(?,?,?,?,?,?,?)');$q->bind_param('iisssss',$id,$userId,$nama,$email,$no_hp,$cv,$pesan);
   if($q->execute()){
      if($cv && (!$cvDoc || $cv !== $cvDoc['nama_file'])){
        $chk=$conn->prepare("SELECT id FROM user_documents WHERE user_id=? AND jenis_dokumen='CV' LIMIT 1");$chk->bind_param('i',$userId);$chk->execute();
        if($chk->get_result()->fetch_assoc()){$up=$conn->prepare("UPDATE user_documents SET nama_file=?,file_asli=? WHERE user_id=? AND jenis_dokumen='CV'");$orig=$_FILES['cv']['name']??$cv;$up->bind_param('ssi',$cv,$orig,$userId);$up->execute();}
        else {$ins=$conn->prepare("INSERT INTO user_documents(user_id,jenis_dokumen,nama_file,file_asli) VALUES(?,?,?,?)");$type='CV';$orig=$_FILES['cv']['name']??$cv;$ins->bind_param('isss',$userId,$type,$cv,$orig);$ins->execute();}
      }
      $success='Lamaran berhasil dikirim menggunakan data profil Anda. Tim PT Agro Buana Sari akan meninjau data tersebut.';
   }else $error='Lamaran gagal disimpan.';
 }
}
?>
<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Lamar <?=htmlspecialchars($career['posisi'])?></title><link rel="stylesheet" href="../assets/css/style.css"></head><body>
<header class="navbar"><div class="container nav-wrap"><a href="../index.php" class="brand"><img src="../assets/img/logo.png" alt="Logo PT Agro Buana Sari"><div><strong>PT AGRO</strong><span>BUANA SARI</span></div></a><nav><a href="../index.php#home">Home</a><a href="../index.php#tentang">Tentang Kami</a><a href="../index.php#produk">Produk</a><a href="../index.php#artikel">Artikel</a><a href="index.php">Career</a></nav><div class="nav-actions"><a class="nav-btn nav-user" href="../user/profile.php">👤 <?=htmlspecialchars($user['nama'])?></a><a class="nav-btn nav-career" href="../user/career.php">💼 My Career</a><a class="nav-btn nav-login" href="../logout.php">Keluar</a></div></div></header>
<main class="section"><div class="container apply-layout"><article class="apply-info"><p class="label">LAMARAN KARIER</p><h1><?=htmlspecialchars($career['posisi'])?></h1><div class="career-meta"><span><?=htmlspecialchars($career['tipe'])?></span><span>📍 <?=htmlspecialchars($career['lokasi'])?></span></div><h3>Deskripsi</h3><p><?=nl2br(htmlspecialchars($career['deskripsi']))?></p><h3>Kualifikasi</h3><p><?=nl2br(htmlspecialchars($career['kualifikasi']))?></p><div class="profile-apply-tip"><strong>💡 Data profil Anda digunakan otomatis</strong><p>Lengkapi <a href="../user/profile.php">My Profile</a> untuk mempermudah proses lamaran berikutnya.</p></div></article><div class="form-card apply-form"><?php if($error): ?><div class="alert"><?=htmlspecialchars($error)?></div><?php endif;?><?php if($success): ?><div class="alert success"><?=htmlspecialchars($success)?></div><a class="btn" href="../user/career.php">Lihat My Career</a> <a class="btn btn-outline" href="index.php">Lihat Lowongan</a><?php else: ?><h2>Kirim Lamaran</h2><p class="apply-muted">Data berikut diambil dari akun Anda dan dapat disesuaikan sebelum dikirim.</p><form method="post" enctype="multipart/form-data"><div class="form-group"><label>Nama Lengkap</label><input name="nama" value="<?=htmlspecialchars($profile['nama_lengkap']??$user['nama'])?>" required></div><div class="form-group"><label>Email</label><input type="email" name="email" value="<?=htmlspecialchars($user['email'])?>" required></div><div class="form-group"><label>No. WhatsApp</label><input name="no_hp" value="<?=htmlspecialchars($user['no_hp']??'')?>" required></div><div class="form-group"><label>CV</label><?php if($cvDoc): ?><div class="saved-file">✓ CV tersimpan: <?=htmlspecialchars($cvDoc['file_asli']?:$cvDoc['nama_file'])?></div><small class="help-text">CV tersimpan akan digunakan otomatis. Upload baru hanya jika ingin menggantinya.</small><?php endif; ?><input type="file" name="cv" accept="application/pdf"><small class="help-text">PDF, maksimal 3 MB.</small></div><div class="form-group"><label>Pesan / Pengalaman</label><textarea name="pesan" placeholder="Ceritakan pengalaman atau alasan Anda melamar..."><?=htmlspecialchars($profile['motivasi_kerja']??'')?></textarea></div><button class="btn" type="submit">Kirim Lamaran</button> <a class="btn btn-outline" href="index.php">Batal</a></form><?php endif;?></div></div></main></body></html>
