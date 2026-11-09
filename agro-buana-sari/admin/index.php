<?php

require_once 'inc/auth.php';
require_once '../config/koneksi.php';

$title = 'Dashboard Perusahaan';


// ============================================================
// FUNGSI AMAN UNTUK MENGAMBIL JUMLAH DATA
// ============================================================

function getCount($conn, $table)
{
    $allowedTables = [
        'produk',
        'artikel',
        'galeri',
        'pesanan',
        'career_applications'
    ];

    if (!in_array($table, $allowedTables, true)) {
        return 0;
    }

    $result = $conn->query("SELECT COUNT(*) AS n FROM `$table`");

    if (!$result) {
        return 0;
    }

    $row = $result->fetch_assoc();

    return (int)($row['n'] ?? 0);
}


// ============================================================
// FUNGSI UNTUK TOTAL STOK
// ============================================================

function getTotalStok($conn)
{
    $result = $conn->query("
        SELECT COALESCE(SUM(stok), 0) AS n
        FROM produk
    ");

    if (!$result) {
        return 0;
    }

    $row = $result->fetch_assoc();

    return (int)($row['n'] ?? 0);
}


// ============================================================
// AMBIL DATA DASHBOARD
// ============================================================

$produk = getCount($conn, 'produk');

$artikel = getCount($conn, 'artikel');

$galeri = getCount($conn, 'galeri');

$pesanan = getCount($conn, 'pesanan');

$lamaran = getCount($conn, 'career_applications');

$stok = getTotalStok($conn);


// ============================================================
// HEADER
// ============================================================

include 'inc/header.php';

?>


<!-- ============================================================
     JUDUL DASHBOARD
     ============================================================ -->

<div class="page-title">

    <div>

        <p class="eyebrow-admin">
            PANEL PERUSAHAAN
        </p>

        <h1>
            Dashboard
        </h1>

        <p>
            Kelola operasional website, stok produk,
            pesanan, dan karier PT Agro Buana Sari.
        </p>

    </div>

</div>


<!-- ============================================================
     STATISTIK
     ============================================================ -->

<div class="cards six">


    <!-- TOTAL PRODUK -->

    <div class="stat">

        <b>
            <?= $produk ?>
        </b>

        <span>
            Total Produk
        </span>

    </div>


    <!-- TOTAL STOK -->

    <div class="stat">

        <b>
            <?= $stok ?>
        </b>

        <span>
            Total Stok
        </span>

    </div>


    <!-- TOTAL PESANAN -->

    <div class="stat">

        <b>
            <?= $pesanan ?>
        </b>

        <span>
            Total Pesanan
        </span>

    </div>


    <!-- TOTAL LAMARAN -->

    <div class="stat">

        <b>
            <?= $lamaran ?>
        </b>

        <span>
            Total Lamaran
        </span>

    </div>


    <!-- TOTAL ARTIKEL -->

    <div class="stat">

        <b>
            <?= $artikel ?>
        </b>

        <span>
            Total Artikel
        </span>

    </div>


    <!-- TOTAL FOTO -->

    <div class="stat">

        <b>
            <?= $galeri ?>
        </b>

        <span>
            Total Foto
        </span>

    </div>

</div>


<!-- ============================================================
     MENU DASHBOARD
     ============================================================ -->

<div class="dashboard-grid">


    <!-- KONTROL STOK -->

    <div class="form-card">

        <h2>
            Kontrol Stok
        </h2>

        <p>
            Masuk ke menu
            <b>Produk &amp; Stok</b>
            untuk memperbarui jumlah stok
            yang tampil di website pelanggan.
        </p>

        <a
            class="btn green"
            href="produk/index.php"
        >
            Kelola Stok
        </a>

    </div>


    <!-- ARTIKEL -->

    <div class="form-card">

        <h2>
            Artikel
        </h2>

        <p>
            Tambahkan informasi pertanian,
            berita perusahaan, dan artikel
            yang akan tampil di website.
        </p>

        <a
            class="btn"
            href="artikel/index.php"
        >
            Kelola Artikel
        </a>

    </div>


    <!-- KARIER -->

    <div class="form-card">

        <h2>
            Karier
        </h2>

        <p>
            Publikasikan lowongan dan lihat
            lamaran dari kandidat melalui
            panel perusahaan.
        </p>

        <a
            class="btn"
            href="../career/admin.php"
        >
            Kelola Karier
        </a>

    </div>


</div>


<?php

include 'inc/footer.php';

?>