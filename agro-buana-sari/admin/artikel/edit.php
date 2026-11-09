<?php
require_once '../inc/auth.php';require_once '../../config/koneksi.php';$title='Edit Artikel';$id=(int)($_GET['id']??0);$s=$conn->prepare("SELECT * FROM artikel WHERE id=?");$s->bind_param('i',$id);$s->execute();$data=$s->get_result()->fetch_assoc();if(!$data)die('Artikel tidak ditemukan.');$error='';if($_SERVER['REQUEST_METHOD']==='POST'){$judul=trim($_POST['judul']??'');$isi=trim($_POST['isi']??'');$foto=$data['foto'];if($judul===''||$isi==='')$error='Judul dan isi wajib diisi.';if(!$error&&!empty($_FILES['foto']['name'])){require_once '../../inc/upload.php';$up=upload_image($_FILES['foto'],'../../uploads/artikel/');if(!$up['ok'])$error=$up['error'];else{$foto=$up['name'];if($data['foto']&&file_exists('../../uploads/artikel/'.$data['foto']))@unlink('../../uploads/artikel/'.$data['foto']);}}if(!$error){$s=$conn->prepare("UPDATE artikel SET judul=?,isi=?,foto=? WHERE id=?");$s->bind_param('sssi',$judul,$isi,$foto,$id);$s->execute();header('Location:index.php');exit;}}include '../inc/header.php';?><div class="page-title"><h1>Edit Artikel</h1></div><div class="form-card"><?php if($error):?><div class="alert"><?=htmlspecialchars($error)?></div><?php endif;?><form method="post" enctype="multipart/form-data"><div class="form-group"><label>Judul</label><input name="judul" value="<?=htmlspecialchars($_POST['judul']??$data['judul'])?>" required></div><div class="form-group"><label>Isi Artikel</label><textarea name="isi" required><?=htmlspecialchars($_POST['isi']??$data['isi'])?></textarea></div><div class="form-group"><label>Ganti Foto</label><input type="file" name="foto" accept="image/jpeg,image/png,image/webp"><?php if($data['foto']):?><br><img class="preview" src="../../uploads/artikel/<?=htmlspecialchars($data['foto'])?>"><?php endif;?></div><button class="btn green">Update</button> <a class="btn gray" href="index.php">Batal</a></form></div><?php include '../inc/footer.php';?><?php

require_once '../inc/auth.php';
require_once '../../config/koneksi.php';

$title = 'Edit Artikel';

$id = (int)($_GET['id'] ?? 0);

/*
|--------------------------------------------------------------------------
| Ambil data artikel
|--------------------------------------------------------------------------
*/
$s = $conn->prepare("
    SELECT *
    FROM artikel
    WHERE id = ?
");

$s->bind_param('i', $id);
$s->execute();

$result = $s->get_result();
$data = $result->fetch_assoc();

if (!$data) {
    die('Artikel tidak ditemukan.');
}

$error = '';

/*
|--------------------------------------------------------------------------
| Proses update
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $judul = trim($_POST['judul'] ?? '');
    $link_artikel = trim($_POST['link_artikel'] ?? '');
    $isi = trim($_POST['isi'] ?? '');

    $foto = $data['foto'];

    /*
    |--------------------------------------------------------------------------
    | Validasi
    |--------------------------------------------------------------------------
    */
    if ($judul === '') {

        $error = 'Judul artikel wajib diisi.';

    } elseif ($isi === '') {

        $error = 'Isi artikel wajib diisi.';

    } elseif ($link_artikel !== '' && !filter_var($link_artikel, FILTER_VALIDATE_URL)) {

        $error = 'Link artikel tidak valid. Contoh: https://www.contoh.com/artikel';

    }


    /*
    |--------------------------------------------------------------------------
    | Upload foto baru
    |--------------------------------------------------------------------------
    */
    if (!$error && !empty($_FILES['foto']['name'])) {

        require_once '../../inc/upload.php';

        $up = upload_image(
            $_FILES['foto'],
            '../../uploads/artikel/'
        );

        if (!$up['ok']) {

            $error = $up['error'];

        } else {

            $foto_baru = $up['name'];

            /*
            | Hapus foto lama
            */
            if (
                !empty($data['foto']) &&
                file_exists('../../uploads/artikel/' . $data['foto'])
            ) {

                @unlink(
                    '../../uploads/artikel/' . $data['foto']
                );
            }

            $foto = $foto_baru;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Simpan perubahan
    |--------------------------------------------------------------------------
    */
    if (!$error) {

        $s = $conn->prepare("
            UPDATE artikel
            SET
                judul = ?,
                link_artikel = ?,
                isi = ?,
                foto = ?
            WHERE id = ?
        ");

        $s->bind_param(
            'ssssi',
            $judul,
            $link_artikel,
            $isi,
            $foto,
            $id
        );

        if ($s->execute()) {

            header('Location: index.php');
            exit;

        } else {

            $error = 'Gagal memperbarui artikel: ' . $conn->error;
        }
    }
}


/*
|--------------------------------------------------------------------------
| Header
|--------------------------------------------------------------------------
*/
include '../inc/header.php';

?>

<div class="page-title">

    <h1>Edit Artikel</h1>

</div>


<div class="form-card">

    <?php if ($error): ?>

        <div class="alert">
            <?= htmlspecialchars($error) ?>
        </div>

    <?php endif; ?>


    <form method="post" enctype="multipart/form-data">


        <!-- ==========================================================
             JUDUL
             ========================================================== -->

        <div class="form-group">

            <label>Judul</label>

            <input
                type="text"
                name="judul"
                value="<?= htmlspecialchars(
                    $_POST['judul'] ?? $data['judul']
                ) ?>"
                required
            >

        </div>


        <!-- ==========================================================
             LINK ARTIKEL
             ========================================================== -->

        <div class="form-group">

            <label>Link Artikel</label>

            <input
                type="url"
                name="link_artikel"
                value="<?= htmlspecialchars(
                    $_POST['link_artikel'] ?? ($data['link_artikel'] ?? '')
                ) ?>"
                placeholder="https://www.contoh.com/artikel"
            >

            <p class="help">
                Masukkan link artikel lengkap, contoh:
                https://www.contoh.com/artikel
            </p>

        </div>


        <!-- ==========================================================
             ISI ARTIKEL
             ========================================================== -->

        <div class="form-group">

            <label>Isi Artikel</label>

            <textarea
                name="isi"
                required
            ><?= htmlspecialchars(
                $_POST['isi'] ?? $data['isi']
            ) ?></textarea>

        </div>


        <!-- ==========================================================
             FOTO
             ========================================================== -->

        <div class="form-group">

            <label>Ganti Foto</label>

            <input
                type="file"
                name="foto"
                accept="image/jpeg,image/png,image/webp"
            >

            <p class="help">
                JPG/PNG/WEBP, maksimal 2 MB.
            </p>


            <?php if (!empty($data['foto'])): ?>

                <div style="margin-top:10px;">

                    <p class="help">
                        Foto saat ini:
                    </p>

                    <img
                        class="preview"
                        src="../../uploads/artikel/<?= htmlspecialchars(
                            $data['foto']
                        ) ?>"
                        alt="<?= htmlspecialchars(
                            $data['judul']
                        ) ?>"
                    >

                </div>

            <?php endif; ?>

        </div>


        <!-- ==========================================================
             BUTTON
             ========================================================== -->

        <button
            type="submit"
            class="btn green"
        >
            Update
        </button>


        <a
            class="btn gray"
            href="index.php"
        >
            Batal
        </a>


    </form>

</div>


<?php

include '../inc/footer.php';

?>