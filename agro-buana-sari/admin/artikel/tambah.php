<?php

require_once '../inc/auth.php';
require_once '../../config/koneksi.php';

$title = 'Tambah Artikel';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $judul = trim($_POST['judul'] ?? '');
    $link_artikel = trim($_POST['link_artikel'] ?? '');
    $isi = trim($_POST['isi'] ?? '');
    $foto = '';

    /*
    |--------------------------------------------------------------------------
    | Validasi
    |--------------------------------------------------------------------------
    */

    if ($judul === '' || $isi === '') {

        $error = 'Judul dan isi wajib diisi.';

    } elseif (
        $link_artikel !== '' &&
        !filter_var($link_artikel, FILTER_VALIDATE_URL)
    ) {

        $error = 'Link artikel tidak valid. Contoh: https://contoh.com/artikel';

    }


    /*
    |--------------------------------------------------------------------------
    | Upload Foto
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

            $foto = $up['name'];

        }
    }


    /*
    |--------------------------------------------------------------------------
    | Simpan Artikel
    |--------------------------------------------------------------------------
    */

    if (!$error) {

        $s = $conn->prepare("
            INSERT INTO artikel
            (
                judul,
                link_artikel,
                isi,
                foto,
                tanggal
            )
            VALUES
            (
                ?,
                ?,
                ?,
                ?,
                CURDATE()
            )
        ");

        if (!$s) {

            $error = 'Query database gagal: ' . $conn->error;

        } else {

            $s->bind_param(
                'ssss',
                $judul,
                $link_artikel,
                $isi,
                $foto
            );

            if ($s->execute()) {

                $s->close();

                header('Location: index.php');
                exit;

            } else {

                $error = 'Artikel gagal disimpan: ' . $s->error;

                $s->close();
            }
        }
    }
}

include '../inc/header.php';

?>


<div class="page-title">

    <h1>Tambah Artikel</h1>

</div>


<div class="form-card">


    <?php if ($error): ?>

        <div class="alert">
            <?= htmlspecialchars($error) ?>
        </div>

    <?php endif; ?>


    <form
        method="post"
        enctype="multipart/form-data"
    >


        <!-- =====================================================
             JUDUL
        ====================================================== -->

        <div class="form-group">

            <label>
                Judul
            </label>

            <input
                type="text"
                name="judul"
                value="<?= htmlspecialchars($_POST['judul'] ?? '') ?>"
                required
            >

        </div>


        <!-- =====================================================
             LINK ARTIKEL
        ====================================================== -->

        <div class="form-group">

            <label>
                Link Artikel
            </label>

            <input
                type="url"
                name="link_artikel"
                value="<?= htmlspecialchars($_POST['link_artikel'] ?? '') ?>"
                placeholder="https://contoh.com/artikel"
            >

            <p class="help">
                Masukkan link artikel lengkap, contoh:
                https://www.contoh.com/artikel
            </p>

        </div>


        <!-- =====================================================
             ISI ARTIKEL
        ====================================================== -->

        <div class="form-group">

            <label>
                Isi Artikel
            </label>

            <textarea
                name="isi"
                required
            ><?= htmlspecialchars($_POST['isi'] ?? '') ?></textarea>

        </div>


        <!-- =====================================================
             FOTO
        ====================================================== -->

        <div class="form-group">

            <label>
                Foto Artikel
            </label>

            <input
                type="file"
                name="foto"
                accept="image/jpeg,image/png,image/webp"
            >

            <p class="help">
                JPG/PNG/WEBP, maksimal 2 MB.
            </p>

        </div>


        <!-- =====================================================
             TOMBOL
        ====================================================== -->

        <button
            type="submit"
            class="btn green"
        >
            Simpan
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