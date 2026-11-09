<?php
require_once 'config/koneksi.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


/* =========================================================
   DATA ARTIKEL
   ========================================================= */

$artikel = $conn->query("
    SELECT *
    FROM artikel
    ORDER BY id DESC
    LIMIT 3
");


/* =========================================================
   DATA GALERI
   ========================================================= */

$galeri = $conn->query("
    SELECT *
    FROM galeri
    ORDER BY id DESC
    LIMIT 6
");


/* =========================================================
   DATA USER
   ========================================================= */

$userLogged = !empty($_SESSION['user_id']);

$userName = $_SESSION['user_nama']
    ?? $_SESSION['user_name']
    ?? $_SESSION['nama']
    ?? 'Akun Saya';


/* =========================================================
   DATA PRODUK DARI DATABASE
   ========================================================= */

$products = $conn->query("
    SELECT
        id,
        nama_produk,
        deskripsi,
        foto,
        stok
    FROM produk
    ORDER BY id ASC
");

if (!$products) {
    die("Gagal mengambil data produk: " . $conn->error);
}

?>
<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>PT Agro Buana Sari</title>

    <link rel="stylesheet"
          href="assets/css/style.css">


    <style>

        /* =====================================================
           PRODUK
           ===================================================== */

        .product-img {
            width: 100%;
            height: 220px;
            object-fit: cover;
            display: block;
            border-radius: 10px 10px 0 0;
        }

        .product-card {
            overflow: hidden;
            padding: 0 !important;
        }

        .product-card .card-body {
            padding: 22px;
        }

        .product-card h3 {
            margin-top: 0;
        }

        .product-card p {
            margin-bottom: 0;
        }

        .cards {
            align-items: stretch;
        }

        .card {
            height: 100%;
        }

        .product-actions {
            margin-top: 18px;
        }

        .buy-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            box-sizing: border-box;
            padding: 11px 14px;
            background: #ee7d12;
            color: #fff;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 800;
            transition: .2s;
            border: none;
        }

        .buy-btn:hover {
            transform: translateY(-2px);
            filter: brightness(.96);
        }

        .buy-btn.disabled {
            background: #aaa;
            cursor: not-allowed;
            opacity: .65;
        }

        .stock-note {
            margin-top: 10px !important;
            font-size: 12px;
            color: #718078;
        }

        .stock-available {
            color: #178044;
            font-weight: 700;
        }

        .stock-empty {
            color: #d9534f;
            font-weight: 700;
        }


        /* =====================================================
           ARTIKEL
           ===================================================== */

        .article-img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 15px;
        }


        /* =====================================================
           GALERI
           ===================================================== */

        .gallery img {
            width: 100%;
            height: 220px;
            object-fit: cover;
        }


        /* =====================================================
           UMUM
           ===================================================== */

        html {
            scroll-behavior: smooth;
        }

        body {
            overflow-x: hidden;
        }


        /* =====================================================
           NAVBAR
           ===================================================== */

        .navbar {
            position: sticky;
            top: 0;
            z-index: 1000;
            background: rgba(255,255,255,.97);
            backdrop-filter: blur(10px);
        }

        .nav-wrap {
            gap: 22px;
        }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-left: auto;
            flex-wrap: wrap;
        }

        .nav-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 9px 13px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 13px;
            text-decoration: none !important;
        }

        .nav-login {
            color: #145c35 !important;
            border: 1px solid #dbe8df;
            background: #fff;
        }

        .nav-register {
            color: #fff !important;
            background: #ee7d12;
        }

        .nav-company {
            color: #145c35 !important;
            background: #eef7f1;
            border: 1px solid #d6eadc;
        }

        .nav-user {
            color: #145c35 !important;
            background: #f5f8f6;
        }

        .nav-career {
            color: #145c35 !important;
            background: #eaf7ee;
            border: 1px solid #d1e7d8;
        }

        .nav-user {
            max-width: 170px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }


        /* =====================================================
           HERO
           ===================================================== */

        .hero {
            min-height: min(720px, calc(100vh - 72px));
        }

        .hero img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .hero-content {
            width: min(1180px, calc(100% - 40px));
        }

        .quick-links {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 18px;
        }

        .quick-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            padding: 11px 16px;
            border-radius: 9px;
            font-weight: 700;
            font-size: 14px;
        }

        .quick-link.primary {
            background: #ee7d12;
            color: #fff;
        }

        .quick-link.secondary {
            background: rgba(255,255,255,.12);
            color: #fff;
            border: 1px solid rgba(255,255,255,.35);
        }


        /* =====================================================
           CAREER
           ===================================================== */

        .career-banner {
            background: linear-gradient(135deg,#0d4f2d,#16663b);
            color: #fff;
        }

        .career-banner .container {
            display: grid;
            grid-template-columns: 1.5fr 1fr;
            gap: 40px;
            align-items: center;
        }

        .career-banner h2 {
            color: #fff;
            margin: 8px 0 12px;
        }

        .career-banner p {
            color: rgba(255,255,255,.82);
        }

        .career-box {
            background: #fff;
            padding: 26px;
            border-radius: 16px;
            box-shadow: 0 14px 35px rgba(0,0,0,.12);
            color: #173525;
        }

        .career-box strong {
            font-size: 20px;
            display: block;
            margin-bottom: 7px;
        }


        /* =====================================================
           ARTIKEL EXTERNAL
           ===================================================== */

        .article-external {
            display: flex;
            flex-direction: column;
        }

        .article-external .btn {
            margin-top: auto;
            align-self: flex-start;
        }


        /* =====================================================
           KONTAK
           ===================================================== */

        .contact-info a:not(.btn) {
            color: inherit;
            font-weight: 700;
        }


        /* =====================================================
           RESPONSIVE
           ===================================================== */

        @media (max-width: 1000px) {

            .nav-wrap {
                flex-wrap: wrap;
            }

            .nav-wrap nav {
                order: 3;
                width: 100%;
                justify-content: center;
                flex-wrap: wrap;
                padding-bottom: 10px;
            }

            .nav-actions {
                margin-left: auto;
            }

            .hero {
                min-height: 560px;
            }

            .career-banner .container {
                grid-template-columns: 1fr;
            }

        }


        @media (max-width: 680px) {

            .nav-wrap {
                gap: 12px;
                padding-top: 10px;
            }

            .nav-wrap nav {
                gap: 12px;
                font-size: 12px;
                overflow-x: auto;
                justify-content: flex-start;
            }

            .nav-actions {
                width: 100%;
                margin-left: 0;
                justify-content: flex-end;
            }

            .nav-btn {
                font-size: 11px;
                padding: 8px 10px;
            }

            .hero {
                min-height: 540px;
            }

            .hero-content {
                width: calc(100% - 28px);
            }

            .quick-link {
                font-size: 12px;
                padding: 9px 12px;
            }

            .product-img {
                height: 200px;
            }

        }

    </style>

</head>


<body>


<!-- =========================================================
     NAVBAR
     ========================================================= -->

<header class="navbar">

    <div class="container nav-wrap">


        <!-- LOGO -->

        <a href="index.php"
           class="brand">

            <img
                src="assets/img/logo.png"
                alt="Logo PT Agro Buana Sari"
            >

            <div>

                <strong>PT AGRO</strong>

                <span>BUANA SARI</span>

            </div>

        </a>


        <!-- MENU -->

        <nav>

            <a href="#home">
                Home
            </a>

            <a href="#tentang">
                Tentang Kami
            </a>

            <a href="#produk">
                Produk
            </a>

            <a href="#artikel">
                Artikel
            </a>

            <a href="career/">
                Career
            </a>

            <a href="#galeri">
                Galeri
            </a>

            <a href="#kontak">
                Kontak
            </a>

        </nav>


        <!-- USER MENU -->

        <div class="nav-actions">

            <?php if ($userLogged): ?>

                <a
                    href="user/profile.php"
                    class="nav-btn nav-user"
                >
                    👤 <?= htmlspecialchars($userName) ?>
                </a>


                <a
                    href="user/career.php"
                    class="nav-btn nav-career"
                >
                    💼 My Career
                </a>


                <a
                    href="logout.php"
                    class="nav-btn nav-login"
                >
                    Keluar
                </a>

            <?php else: ?>

                <a
                    href="login.php"
                    class="nav-btn nav-login"
                >
                    👤 Akun Saya
                </a>


                <a
                    href="register.php"
                    class="nav-btn nav-register"
                >
                    Buat Akun
                </a>

            <?php endif; ?>


            <a
                href="company/login.php"
                class="nav-btn nav-company"
            >
                🏢 Perusahaan
            </a>

        </div>

    </div>

</header>



<!-- =========================================================
     HERO
     ========================================================= -->

<section id="home"
         class="hero">


    <img
        src="assets/img/hero.jpg"
        alt="Kegiatan PT Agro Buana Sari"
    >


    <div class="hero-overlay"></div>


    <div class="hero-content container">

        <p class="eyebrow">
            PT AGRO BUANA SARI
        </p>


        <h1>

            Tumbuh Bersama<br>

            <span>
                Memberi Manfaat
            </span>

        </h1>


        <p>

            Membangun pertanian yang produktif melalui
            kemitraan, inovasi, dan pengembangan
            agribisnis berkelanjutan.

        </p>


        <a
            href="#tentang"
            class="btn"
        >
            Tentang Kami
        </a>


        <div class="quick-links">

            <a
                href="#produk"
                class="quick-link primary"
            >
                🛒 Lihat Produk
            </a>


            <a
                href="career/"
                class="quick-link secondary"
            >
                💼 Lihat Career
            </a>

        </div>

    </div>

</section>



<!-- =========================================================
     TENTANG KAMI
     ========================================================= -->

<section id="tentang"
         class="section">

    <div class="container two-col">


        <div>

            <p class="label">
                TENTANG KAMI
            </p>


            <h2>

                Bersama Membangun
                Pertanian Indonesia

            </h2>

        </div>


        <div>

            <p>

                PT Agro Buana Sari merupakan perusahaan
                yang bergerak dalam bidang agribisnis dan
                pengembangan sektor pertanian.

            </p>


            <p>

                Kami berkomitmen membangun kemitraan
                bersama petani untuk menciptakan kegiatan
                pertanian yang produktif, berkelanjutan,
                dan memberikan manfaat bagi masyarakat.

            </p>

        </div>

    </div>

</section>



<!-- =========================================================
     STATISTIK
     ========================================================= -->

<section class="stats">

    <div class="container stat-grid">


        <div>

            <strong>
                1.000
            </strong>

            <span>
                Target Lahan (Ha)
            </span>

        </div>


        <div>

            <strong>
                5.000
            </strong>

            <span>
                Target Produksi (Ton)
            </span>

        </div>


        <div>

            <strong>
                100
            </strong>

            <span>
                Program Lahan (Ha)
            </span>

        </div>


        <div>

            <strong>
                +
            </strong>

            <span>
                Kemitraan Petani
            </span>

        </div>

    </div>

</section>



<!-- =========================================================
     PRODUK
     ========================================================= -->

<section id="produk"
         class="section light">

    <div class="container">


        <div class="section-head">

            <div>

                <p class="label">
                    PRODUK
                </p>

                <h2>
                    Produk Pertanian
                </h2>

            </div>

        </div>



        <div class="cards">


            <?php if ($products->num_rows > 0): ?>


                <?php while ($product = $products->fetch_assoc()): ?>


                    <?php

                    $productId = (int)$product['id'];

                    $productName =
                        $product['nama_produk'] ?? '';

                    $productDescription =
                        $product['deskripsi'] ?? '';

                    $productStock =
                        (int)($product['stok'] ?? 0);


                    /*
                     * FOTO PRODUK
                     *
                     * Database:
                     * uploads/produk/nama-file.jpg
                     */

                    if (!empty($product['foto'])) {

                        $productImage =
                            'uploads/produk/' .
                            $product['foto'];

                    } else {

                        $productImage =
                            'assets/img/hero.jpg';

                    }

                    ?>


                    <article class="card product-card">


                        <!-- FOTO -->

                        <img
                            src="<?= htmlspecialchars($productImage) ?>"
                            alt="<?= htmlspecialchars($productName) ?>"
                            class="product-img"
                            onerror="this.src='assets/img/hero.jpg';"
                        >


                        <div class="card-body">


                            <!-- NAMA PRODUK -->

                            <h3>

                                <?= htmlspecialchars($productName) ?>

                            </h3>


                            <!-- DESKRIPSI -->

                            <p>

                                <?= htmlspecialchars($productDescription) ?>

                            </p>


                            <!-- STOK -->

                            <?php if ($productStock > 0): ?>

                                <p class="stock-note">

                                    Stok tersedia:

                                    <span class="stock-available">

                                        <?= number_format($productStock) ?>

                                    </span>

                                </p>

                            <?php else: ?>

                                <p class="stock-note">

                                    <span class="stock-empty">

                                        Stok habis

                                    </span>

                                </p>

                            <?php endif; ?>


                            <!-- TOMBOL BELI -->

                            <div class="product-actions">


                                <?php if ($productStock > 0): ?>


                                    <a
                                        class="buy-btn"
                                        href="beli.php?id=<?= $productId ?>"
                                    >

                                        🛒 Beli Sekarang

                                    </a>


                                <?php else: ?>


                                    <button
                                        type="button"
                                        class="buy-btn disabled"
                                        disabled
                                    >

                                        Stok Habis

                                    </button>


                                <?php endif; ?>


                            </div>


                        </div>

                    </article>


                <?php endwhile; ?>


            <?php else: ?>


                <div class="card">

                    <div class="card-body">

                        <h3>
                            Belum Ada Produk
                        </h3>

                        <p>
                            Produk pertanian belum tersedia.
                        </p>

                    </div>

                </div>


            <?php endif; ?>


        </div>

    </div>

</section>



<!-- =========================================================
     PROGRAM KEMITRAAN
     ========================================================= -->

<section class="section">

    <div class="container program">


        <div class="program-image">

            <img
                src="assets/img/hero.jpg"
                alt="Program Kemitraan PT Agro Buana Sari"
            >

        </div>


        <div class="program-content">


            <p class="label">
                PROGRAM KEMITRAAN
            </p>


            <h2>

                Bersama Petani
                Membangun Pertanian

            </h2>


            <p>

                Program kemitraan menjadi bagian dari
                upaya PT Agro Buana Sari dalam
                membangun hubungan yang saling
                menguntungkan dengan petani.

            </p>


            <ul>

                <li>
                    Pendampingan petani
                </li>

                <li>
                    Pengelolaan lahan
                </li>

                <li>
                    Peningkatan produktivitas
                </li>

                <li>
                    Kemitraan berkelanjutan
                </li>

            </ul>


        </div>

    </div>

</section>



<!-- =========================================================
     ARTIKEL
     ========================================================= -->

<section id="artikel"
         class="section light">

    <div class="container">


        <div class="section-head">

            <div>

                <p class="label">
                    ARTIKEL
                </p>

                <h2>
                    Informasi Pertanian
                </h2>

            </div>

        </div>



        <div class="cards">


            <!-- ARTIKEL DATABASE -->

            <?php if ($artikel && $artikel->num_rows > 0): ?>


                <?php while ($row = $artikel->fetch_assoc()): ?>


                    <article class="card article-external">


                        <?php if (!empty($row['foto'])): ?>

                            <img
                                src="uploads/artikel/<?= htmlspecialchars($row['foto']) ?>"
                                alt="<?= htmlspecialchars($row['judul']) ?>"
                                class="article-img"
                            >

                        <?php else: ?>

                            <div class="card-icon">📰</div>

                        <?php endif; ?>


                        <h3>

                            <?= htmlspecialchars($row['judul']) ?>

                        </h3>


                        <p>

                            <?= htmlspecialchars(
                                substr(
                                    strip_tags($row['isi']),
                                    0,
                                    150
                                )
                            ) ?>...

                        </p>


                        <?php
                        // Jika link_artikel diisi, buka tautan eksternal.
                        // Jika kosong, buka halaman detail artikel internal.
                        $linkArtikel = trim($row['link_artikel'] ?? '');
                        if ($linkArtikel === '') {
                            $linkArtikel = 'artikel/detail.php?id=' . (int)$row['id'];
                            $targetArtikel = '';
                            $relArtikel = '';
                        } else {
                            $targetArtikel = ' target="_blank"';
                            $relArtikel = ' rel="noopener noreferrer"';
                        }
                        ?>

                        <a
                            href="<?= htmlspecialchars($linkArtikel) ?>"
                            class="btn"
                            <?= $targetArtikel ?>
                            <?= $relArtikel ?>
                        >
                            Baca Artikel
                        </a>


                    </article>


                <?php endwhile; ?>


            <?php endif; ?>


        </div>

    </div>

</section>



<!-- =========================================================
     CAREER
     ========================================================= -->

<section class="section career-banner">


    <div class="container">


        <div>


            <p
                class="label"
                style="color:#f59b4a;"
            >
                CAREER
            </p>


            <h2>

                Bergabung dan Tumbuh Bersama
                PT Agro Buana Sari

            </h2>


            <p>

                Temukan kesempatan untuk berkembang
                bersama perusahaan dan ikut berkontribusi
                dalam sektor pertanian Indonesia.

            </p>


            <a
                href="career/"
                class="btn"
            >

                Lihat Lowongan

            </a>


        </div>


        <div class="career-box">


            <strong>

                Siap membangun masa depan bersama?

            </strong>


            <span>

                Login sebagai pengguna untuk mengirim
                lamaran secara online.

            </span>


        </div>


    </div>

</section>



<!-- =========================================================
     GALERI
     ========================================================= -->

<section id="galeri"
         class="section">


    <div class="container">


        <div class="section-head">


            <div>

                <p class="label">
                    GALERI
                </p>

                <h2>
                    Kegiatan Kami
                </h2>

            </div>


        </div>



        <div class="gallery">


            <?php if ($galeri && $galeri->num_rows > 0): ?>


                <?php while ($row = $galeri->fetch_assoc()): ?>


                    <?php if (!empty($row['foto'])): ?>


                        <img
                            src="uploads/galeri/<?= htmlspecialchars($row['foto']) ?>"
                            alt="<?= htmlspecialchars($row['judul'] ?? 'Kegiatan PT Agro Buana Sari') ?>"
                            onerror="this.src='assets/img/hero.jpg';"
                        >


                    <?php endif; ?>


                <?php endwhile; ?>


            <?php else: ?>


                <img
                    src="assets/img/hero.jpg"
                    alt="Kegiatan PT Agro Buana Sari"
                >


                <img
                    src="assets/img/hero.jpg"
                    alt="Kegiatan pertanian"
                >


                <img
                    src="assets/img/hero.jpg"
                    alt="Kemitraan petani"
                >


            <?php endif; ?>


        </div>


    </div>

</section>



<!-- =========================================================
     KONTAK
     ========================================================= -->

<section id="kontak"
         class="contact">


    <div class="container contact-box">


        <div>


            <p class="label">
                HUBUNGI KAMI
            </p>


            <h2>

                Mari Tumbuh Bersama
                Sektor Pertanian

            </h2>


        </div>


        <div class="contact-info">


            <p>

                <strong>
                    PT Agro Buana Sari
                </strong>

            </p>


            <p>

                Desa Singasari,
                Kecamatan Jonggol,
                Kabupaten Bogor,
                Jawa Barat

            </p>


            <p>

                <strong>
                    Telepon / WhatsApp:
                </strong>

                <br>

                <a
                    href="https://wa.me/6285710234948"
                    target="_blank"
                    rel="noopener noreferrer"
                >

                    0857-1023-4948

                </a>

            </p>


            <p>

                <strong>
                    Instagram:
                </strong>

                <br>

                <a
                    href="https://www.instagram.com/ptagrobuanasari/"
                    target="_blank"
                    rel="noopener noreferrer"
                >

                    @ptagrobuanasari

                </a>

            </p>


            <a
                href="https://wa.me/6285710234948"
                target="_blank"
                rel="noopener noreferrer"
                class="btn"
            >

                Hubungi Kami

            </a>


        </div>


    </div>

</section>



<!-- =========================================================
     FOOTER
     ========================================================= -->

<footer>


    <div class="container footer-wrap">


        <p>

            © <?= date('Y') ?>
            PT Agro Buana Sari

        </p>


        <p>

            Bersama Petani Sukses

        </p>


    </div>


</footer>


</body>

</html>