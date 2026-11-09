<?php
require_once '../config/koneksi.php';
require_once '../inc/user_auth.php';
require_once '../inc/user_profile.php';
require_user();

$user = current_user($conn);
if (!$user) { session_destroy(); header('Location: ../login.php'); exit; }
$userId = (int)$user['id'];
$profile = get_user_profile($conn, $userId);
$success = '';
$error = '';
$active = $_GET['tab'] ?? 'diri';
$allowedTabs = ['diri','keluarga','pendidikan','organisasi','kerja','pernyataan','kesehatan','dokumen'];
if (!in_array($active, $allowedTabs, true)) $active = 'diri';

function postv(string $key, array $source): string { return trim((string)($source[$key] ?? '')); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $section = $_POST['section'] ?? 'diri';
    $active = in_array($section, $allowedTabs, true) ? $section : 'diri';

    if ($section === 'dokumen') {
        $type = $_POST['jenis_dokumen'] ?? '';
        $allowedTypes = ['CV','Ijazah','Sertifikat','Portofolio','Surat Lamaran'];
        if (!in_array($type, $allowedTypes, true)) {
            $error = 'Jenis dokumen tidak valid.';
        } elseif (empty($_FILES['dokumen']['name'])) {
            $error = 'Pilih dokumen yang akan diunggah.';
        } else {
            $file = $_FILES['dokumen'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowedExt = ['pdf','jpg','jpeg','png'];
            if ($file['error'] !== UPLOAD_ERR_OK) $error = 'Dokumen gagal diunggah.';
            elseif (!in_array($ext, $allowedExt, true)) $error = 'Format dokumen harus PDF, JPG, JPEG, atau PNG.';
            elseif ($file['size'] > 5 * 1024 * 1024) $error = 'Ukuran dokumen maksimal 5 MB.';
            else {
                $dir = '../uploads/documents';
                if (!is_dir($dir)) mkdir($dir, 0755, true);
                $safe = 'user_' . $userId . '_' . preg_replace('/[^a-z0-9]+/i','_', strtolower($type)) . '_' . time() . '.' . $ext;
                $dest = $dir . '/' . $safe;
                if (!move_uploaded_file($file['tmp_name'], $dest)) {
                    $error = 'Dokumen gagal disimpan.';
                } else {
                    $old = $conn->prepare('SELECT nama_file FROM user_documents WHERE user_id=? AND jenis_dokumen=? LIMIT 1');
                    $old->bind_param('is', $userId, $type); $old->execute(); $oldRow = $old->get_result()->fetch_assoc();
                    $stmt = $conn->prepare("INSERT INTO user_documents(user_id,jenis_dokumen,nama_file,file_asli) VALUES(?,?,?,?) ON DUPLICATE KEY UPDATE nama_file=VALUES(nama_file), file_asli=VALUES(file_asli)");
                    $original = basename($file['name']);
                    $stmt->bind_param('isss', $userId, $type, $safe, $original);
                    if ($stmt->execute()) {
                        if ($oldRow && !empty($oldRow['nama_file']) && is_file($dir.'/'.$oldRow['nama_file'])) @unlink($dir.'/'.$oldRow['nama_file']);
                        $success = $type . ' berhasil diunggah.';
                    } else { @unlink($dest); $error = 'Data dokumen gagal disimpan.'; }
                }
            }
        }
    } else {
        $data = [];
        if ($section === 'diri') {
            // no_hp disimpan di tabel users, bukan user_profiles
            $noHp = postv('no_hp', $_POST);
            $data = [
                'nama_lengkap'=>postv('nama_lengkap',$_POST),'nama_panggilan'=>postv('nama_panggilan',$_POST),'jenis_kelamin'=>postv('jenis_kelamin',$_POST),
                'tempat_lahir'=>postv('tempat_lahir',$_POST),'tanggal_lahir'=>postv('tanggal_lahir',$_POST),'alamat'=>postv('alamat',$_POST),
                'kota'=>postv('kota',$_POST),'kode_pos'=>postv('kode_pos',$_POST)
            ];
        } elseif ($section === 'keluarga') {
            $data = ['status_pernikahan'=>postv('status_pernikahan',$_POST),'nama_ayah'=>postv('nama_ayah',$_POST),'nama_ibu'=>postv('nama_ibu',$_POST),'nama_pasangan'=>postv('nama_pasangan',$_POST),'jumlah_saudara'=>postv('jumlah_saudara',$_POST)];
        } elseif ($section === 'pendidikan') {
            $data = ['pendidikan_terakhir'=>postv('pendidikan_terakhir',$_POST),'institusi_pendidikan'=>postv('institusi_pendidikan',$_POST),'jurusan'=>postv('jurusan',$_POST),'tahun_lulus'=>postv('tahun_lulus',$_POST)];
        } elseif ($section === 'organisasi') {
            $data = ['organisasi_nama'=>postv('organisasi_nama',$_POST),'organisasi_jabatan'=>postv('organisasi_jabatan',$_POST),'organisasi_periode'=>postv('organisasi_periode',$_POST),'organisasi_deskripsi'=>postv('organisasi_deskripsi',$_POST)];
        } elseif ($section === 'kerja') {
            $data = ['pengalaman_perusahaan'=>postv('pengalaman_perusahaan',$_POST),'pengalaman_jabatan'=>postv('pengalaman_jabatan',$_POST),'pengalaman_periode'=>postv('pengalaman_periode',$_POST),'pengalaman_deskripsi'=>postv('pengalaman_deskripsi',$_POST)];
        } elseif ($section === 'pernyataan') {
            $data = ['kelebihan'=>postv('kelebihan',$_POST),'kekurangan'=>postv('kekurangan',$_POST),'motivasi_kerja'=>postv('motivasi_kerja',$_POST),'tujuan_karier'=>postv('tujuan_karier',$_POST)];
        } elseif ($section === 'kesehatan') {
            $data = ['riwayat_kesehatan'=>postv('riwayat_kesehatan',$_POST),'alergi'=>postv('alergi',$_POST),'kontak_darurat_nama'=>postv('kontak_darurat_nama',$_POST),'kontak_darurat_hubungan'=>postv('kontak_darurat_hubungan',$_POST),'kontak_darurat_no_hp'=>postv('kontak_darurat_no_hp',$_POST)];
        }
        if (!$error && $data) {
            $sets = [];
            $types = '';
            $values = [];

            foreach ($data as $key => $value) {
                $sets[] = "$key=?";
                $types .= 's';
                $values[] = $value;
            }
            $types .= 'i';
            $values[] = $userId;

            $stmt = $conn->prepare('UPDATE user_profiles SET ' . implode(',', $sets) . ' WHERE user_id=?');
            if (!$stmt) {
                $error = 'Data profil gagal disimpan: ' . $conn->error;
            } else {
                $stmt->bind_param($types, ...$values);
                if ($stmt->execute()) {
                    // Nomor HP memang berada di tabel users.
                    if ($section === 'diri' && !empty($data['nama_lengkap'])) {
                        $u = $conn->prepare('UPDATE users SET nama=?, no_hp=? WHERE id=?');
                        if ($u) {
                            $u->bind_param('ssi', $data['nama_lengkap'], $noHp, $userId);
                            $u->execute();
                        }
                        $_SESSION['user_nama'] = $data['nama_lengkap'];
                    }

                    $namaSection = [
                        'diri'=>'Diri', 'keluarga'=>'Keluarga', 'pendidikan'=>'Pendidikan',
                        'organisasi'=>'Organisasi', 'kerja'=>'Kerja',
                        'pernyataan'=>'Pernyataan', 'kesehatan'=>'Kesehatan'
                    ];
                    $success = 'Data ' . ($namaSection[$section] ?? ucfirst($section)) . ' berhasil disimpan.';
                    $profile = get_user_profile($conn, $userId);
                    $user = current_user($conn);
                } else {
                    $error = 'Data gagal disimpan: ' . $stmt->error;
                }
            }
        }
    }
}

$documents=[];
$dq=$conn->prepare('SELECT * FROM user_documents WHERE user_id=? ORDER BY id ASC');
$dq->bind_param('i',$userId); $dq->execute(); $dr=$dq->get_result();
while($d=$dr->fetch_assoc()) $documents[$d['jenis_dokumen']]=$d;
$profile['no_hp'] = $user['no_hp'] ?? ($profile['no_hp'] ?? '');
$completion = profile_completion($profile);

function val(array $p,string $key): string { return htmlspecialchars((string)($p[$key] ?? ''), ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>My Profile | PT Agro Buana Sari</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="user-page">
<header class="navbar"><div class="container nav-wrap">
<a href="../index.php" class="brand"><img src="../assets/img/logo.png" alt="Logo PT Agro Buana Sari"><div><strong>PT AGRO</strong><span>BUANA SARI</span></div></a>
<nav><a href="../index.php#home">Home</a><a href="../index.php#tentang">Tentang Kami</a><a href="../index.php#produk">Produk</a><a href="../index.php#artikel">Artikel</a><a href="../career/">Career</a><a href="../index.php#galeri">Galeri</a><a href="../index.php#kontak">Kontak</a></nav>
<div class="nav-actions"><a class="nav-btn nav-user" href="profile.php">👤 <?=htmlspecialchars($user['nama'])?></a><a class="nav-btn nav-career" href="career.php">💼 My Career</a><a class="nav-btn nav-login" href="../logout.php">Keluar</a></div>
</div></header>
<main class="profile-page"><div class="container profile-container">
<div class="profile-page-head"><div><p class="label">MY ACCOUNT</p><h1>My Profile</h1><p>Lengkapi profil Anda untuk mempercepat proses pemesanan dan lamaran kerja.</p></div><a class="btn" href="career.php">💼 My Career</a></div>
<?php if($success): ?><div class="alert success"><?=htmlspecialchars($success)?></div><?php endif; ?><?php if($error): ?><div class="alert"><?=htmlspecialchars($error)?></div><?php endif; ?>
<div class="profile-progress"><div><strong>Profil <?= $completion ?>% lengkap</strong><span>Lengkapi data agar dapat digunakan otomatis saat melamar.</span></div><div class="progress-bar"><i style="width:<?=$completion?>%"></i></div></div>
<section class="profile-layout">
<aside class="profile-sidebar">
<div class="profile-sidebar-top"><div class="profile-avatar large"><?=htmlspecialchars(strtoupper(substr($user['nama'] ?: 'U',0,1)))?></div><div><b><?=htmlspecialchars($user['nama'])?></b><small><?=htmlspecialchars($user['email'])?></small></div></div>
<nav class="profile-tabs">
<a class="<?= $active==='diri'?'active':'' ?>" href="?tab=diri"><span>👤</span>Data Diri</a>
<a class="<?= $active==='keluarga'?'active':'' ?>" href="?tab=keluarga"><span>👪</span>Data Keluarga</a>
<a class="<?= $active==='pendidikan'?'active':'' ?>" href="?tab=pendidikan"><span>🎓</span>Data Pendidikan</a>
<a class="<?= $active==='organisasi'?'active':'' ?>" href="?tab=organisasi"><span>🤝</span>Pengalaman Organisasi</a>
<a class="<?= $active==='kerja'?'active':'' ?>" href="?tab=kerja"><span>💼</span>Pengalaman Kerja</a>
<a class="<?= $active==='pernyataan'?'active':'' ?>" href="?tab=pernyataan"><span>📝</span>Pernyataan Pribadi</a>
<a class="<?= $active==='kesehatan'?'active':'' ?>" href="?tab=kesehatan"><span>♡</span>Riwayat Kesehatan</a>
<a class="<?= $active==='dokumen'?'active':'' ?>" href="?tab=dokumen"><span>📎</span>Upload Dokumen</a>
</nav>
</aside>
<div class="profile-content">
<?php if($active==='diri'): ?>
<div class="profile-section-head"><div><p class="label">PERSONAL INFORMATION</p><h2>Data Diri</h2><p>Informasi dasar yang akan digunakan dalam proses lamaran.</p></div></div>
<form method="post" class="profile-form-grid"><input type="hidden" name="section" value="diri">
<div class="form-group full"><label>Nama Lengkap</label><input name="nama_lengkap" value="<?=val($profile,'nama_lengkap')?>" required></div>
<div class="form-group"><label>Nama Panggilan</label><input name="nama_panggilan" value="<?=val($profile,'nama_panggilan')?>"></div>
<div class="form-group"><label>Jenis Kelamin</label><select name="jenis_kelamin"><option value="">Pilih</option><option <?=($profile['jenis_kelamin']??'')==='Laki-laki'?'selected':''?>>Laki-laki</option><option <?=($profile['jenis_kelamin']??'')==='Perempuan'?'selected':''?>>Perempuan</option></select></div>
<div class="form-group"><label>Tempat Lahir</label><input name="tempat_lahir" value="<?=val($profile,'tempat_lahir')?>"></div>
<div class="form-group"><label>Tanggal Lahir</label><input type="date" name="tanggal_lahir" value="<?=val($profile,'tanggal_lahir')?>"></div>
<div class="form-group"><label>No. WhatsApp</label><input name="no_hp" value="<?=val($user,'no_hp')?>" placeholder="08xxxxxxxxxx"></div>
<div class="form-group full"><label>Alamat</label><textarea name="alamat"><?=val($profile,'alamat')?></textarea></div>
<div class="form-group"><label>Kota / Kabupaten</label><input name="kota" value="<?=val($profile,'kota')?>"></div>
<div class="form-group"><label>Kode Pos</label><input name="kode_pos" value="<?=val($profile,'kode_pos')?>"></div>
<div class="profile-save"><button class="btn" type="submit">Simpan Data Diri</button></div>
</form>
<?php elseif($active==='keluarga'): ?>
<div class="profile-section-head"><div><p class="label">FAMILY INFORMATION</p><h2>Data Keluarga</h2><p>Informasi keluarga untuk melengkapi data kandidat.</p></div></div>
<form method="post" class="profile-form-grid"><input type="hidden" name="section" value="keluarga">
<div class="form-group"><label>Status Pernikahan</label><select name="status_pernikahan"><option value="">Pilih</option><?php foreach(['Belum Menikah','Menikah','Lainnya'] as $o): ?><option <?=($profile['status_pernikahan']??'')===$o?'selected':''?>><?=$o?></option><?php endforeach; ?></select></div>
<div class="form-group"><label>Jumlah Saudara</label><input type="number" min="0" name="jumlah_saudara" value="<?=val($profile,'jumlah_saudara')?>"></div>
<div class="form-group"><label>Nama Ayah</label><input name="nama_ayah" value="<?=val($profile,'nama_ayah')?>"></div>
<div class="form-group"><label>Nama Ibu</label><input name="nama_ibu" value="<?=val($profile,'nama_ibu')?>"></div>
<div class="form-group full"><label>Nama Pasangan <small class="muted-note">(jika ada)</small></label><input name="nama_pasangan" value="<?=val($profile,'nama_pasangan')?>"></div>
<div class="profile-save"><button class="btn" type="submit">Simpan Data Keluarga</button></div></form>
<?php elseif($active==='pendidikan'): ?>
<div class="profile-section-head"><div><p class="label">EDUCATION</p><h2>Data Pendidikan</h2><p>Riwayat pendidikan terakhir Anda.</p></div></div>
<form method="post" class="profile-form-grid"><input type="hidden" name="section" value="pendidikan">
<div class="form-group"><label>Pendidikan Terakhir</label><select name="pendidikan_terakhir"><option value="">Pilih</option><?php foreach(['SMA / SMK','D1','D2','D3','D4','S1','S2','S3'] as $o): ?><option <?=($profile['pendidikan_terakhir']??'')===$o?'selected':''?>><?=$o?></option><?php endforeach; ?></select></div>
<div class="form-group"><label>Tahun Lulus</label><input name="tahun_lulus" maxlength="4" value="<?=val($profile,'tahun_lulus')?>"></div>
<div class="form-group full"><label>Nama Institusi / Sekolah / Universitas</label><input name="institusi_pendidikan" value="<?=val($profile,'institusi_pendidikan')?>"></div>
<div class="form-group full"><label>Jurusan / Program Studi</label><input name="jurusan" value="<?=val($profile,'jurusan')?>"></div>
<div class="profile-save"><button class="btn" type="submit">Simpan Data Pendidikan</button></div></form>
<?php elseif($active==='organisasi'): ?>
<div class="profile-section-head"><div><p class="label">ORGANIZATION</p><h2>Pengalaman Organisasi</h2><p>Tambahkan pengalaman organisasi yang relevan.</p></div></div>
<form method="post" class="profile-form-grid"><input type="hidden" name="section" value="organisasi">
<div class="form-group full"><label>Nama Organisasi</label><input name="organisasi_nama" value="<?=val($profile,'organisasi_nama')?>"></div>
<div class="form-group"><label>Jabatan / Peran</label><input name="organisasi_jabatan" value="<?=val($profile,'organisasi_jabatan')?>"></div>
<div class="form-group"><label>Periode</label><input name="organisasi_periode" placeholder="Contoh: 2022 - 2024" value="<?=val($profile,'organisasi_periode')?>"></div>
<div class="form-group full"><label>Deskripsi Kegiatan</label><textarea name="organisasi_deskripsi"><?=val($profile,'organisasi_deskripsi')?></textarea></div>
<div class="profile-save"><button class="btn" type="submit">Simpan Pengalaman Organisasi</button></div></form>
<?php elseif($active==='kerja'): ?>
<div class="profile-section-head"><div><p class="label">WORK EXPERIENCE</p><h2>Pengalaman Kerja</h2><p>Riwayat pekerjaan yang pernah Anda jalani.</p></div></div>
<form method="post" class="profile-form-grid"><input type="hidden" name="section" value="kerja">
<div class="form-group full"><label>Nama Perusahaan</label><input name="pengalaman_perusahaan" value="<?=val($profile,'pengalaman_perusahaan')?>"></div>
<div class="form-group"><label>Jabatan</label><input name="pengalaman_jabatan" value="<?=val($profile,'pengalaman_jabatan')?>"></div>
<div class="form-group"><label>Periode</label><input name="pengalaman_periode" placeholder="Contoh: 2021 - 2025" value="<?=val($profile,'pengalaman_periode')?>"></div>
<div class="form-group full"><label>Deskripsi Pekerjaan</label><textarea name="pengalaman_deskripsi"><?=val($profile,'pengalaman_deskripsi')?></textarea></div>
<div class="profile-save"><button class="btn" type="submit">Simpan Pengalaman Kerja</button></div></form>
<?php elseif($active==='pernyataan'): ?>
<div class="profile-section-head"><div><p class="label">PERSONAL STATEMENT</p><h2>Pernyataan Pribadi</h2><p>Informasi yang membantu perusahaan mengenal motivasi dan tujuan Anda.</p></div></div>
<form method="post" class="profile-form-grid"><input type="hidden" name="section" value="pernyataan">
<div class="form-group full"><label>Kelebihan</label><textarea name="kelebihan" placeholder="Contoh: teliti, komunikatif, cepat belajar..." ><?=val($profile,'kelebihan')?></textarea></div>
<div class="form-group full"><label>Kekurangan / Hal yang Sedang Dikembangkan</label><textarea name="kekurangan"><?=val($profile,'kekurangan')?></textarea></div>
<div class="form-group full"><label>Motivasi Bekerja</label><textarea name="motivasi_kerja"><?=val($profile,'motivasi_kerja')?></textarea></div>
<div class="form-group full"><label>Tujuan Karier</label><textarea name="tujuan_karier"><?=val($profile,'tujuan_karier')?></textarea></div>
<div class="profile-save"><button class="btn" type="submit">Simpan Pernyataan</button></div></form>
<?php elseif($active==='kesehatan'): ?>
<div class="profile-section-head"><div><p class="label">HEALTH INFORMATION</p><h2>Riwayat Kesehatan</h2><p>Isi seperlunya untuk kebutuhan administrasi rekrutmen.</p></div></div>
<div class="profile-notice">Informasi kesehatan bersifat pribadi dan sebaiknya diisi hanya jika diperlukan untuk proses rekrutmen atau kebutuhan kerja.</div>
<form method="post" class="profile-form-grid"><input type="hidden" name="section" value="kesehatan">
<div class="form-group full"><label>Riwayat Kesehatan</label><textarea name="riwayat_kesehatan" placeholder="Tuliskan informasi yang relevan untuk kebutuhan pekerjaan."><?=val($profile,'riwayat_kesehatan')?></textarea></div>
<div class="form-group full"><label>Alergi</label><textarea name="alergi" placeholder="Jika ada, jelaskan secara singkat."><?=val($profile,'alergi')?></textarea></div>
<div class="form-group"><label>Kontak Darurat</label><input name="kontak_darurat_nama" value="<?=val($profile,'kontak_darurat_nama')?>"></div>
<div class="form-group"><label>Hubungan</label><input name="kontak_darurat_hubungan" value="<?=val($profile,'kontak_darurat_hubungan')?>"></div>
<div class="form-group full"><label>No. Kontak Darurat</label><input name="kontak_darurat_no_hp" value="<?=val($profile,'kontak_darurat_no_hp')?>"></div>
<div class="profile-save"><button class="btn" type="submit">Simpan Data Kesehatan</button></div></form>
<?php elseif($active==='dokumen'): ?>
<div class="profile-section-head"><div><p class="label">DOCUMENT CENTER</p><h2>Upload Dokumen</h2><p>Simpan dokumen yang dapat digunakan saat melamar pekerjaan.</p></div></div>
<div class="document-note">Format yang diterima: PDF, JPG, JPEG, PNG. Maksimal 5 MB per dokumen.</div>
<div class="document-grid">
<?php foreach(['CV','Ijazah','Sertifikat','Portofolio','Surat Lamaran'] as $type): $d=$documents[$type]??null; ?>
<div class="document-card"><div class="document-icon">📄</div><div class="document-body"><h3><?=htmlspecialchars($type)?></h3><?php if($d): ?><p class="document-file">✓ <?=htmlspecialchars($d['file_asli'] ?: $d['nama_file'])?></p><a target="_blank" href="../uploads/documents/<?=htmlspecialchars($d['nama_file'])?>" class="document-link">Lihat dokumen</a><?php else: ?><p class="document-file muted-note">Belum ada dokumen.</p><?php endif; ?></div><form method="post" enctype="multipart/form-data" class="document-upload"><input type="hidden" name="section" value="dokumen"><input type="hidden" name="jenis_dokumen" value="<?=htmlspecialchars($type)?>"><label class="upload-label">Pilih File<input type="file" name="dokumen" accept=".pdf,.jpg,.jpeg,.png" required></label><button type="submit" class="btn btn-small"><?= $d ? 'Ganti' : 'Upload' ?></button></form></div>
<?php endforeach; ?>
</div>
<?php endif; ?>
</div></section>
</div></main>
<footer><div class="container footer-wrap"><p>© <?=date('Y')?> PT Agro Buana Sari</p><p>Bersama Petani Sukses</p></div></footer>
</body></html>