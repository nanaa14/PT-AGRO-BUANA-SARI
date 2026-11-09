<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/*
|--------------------------------------------------------------------------
| KONFIGURASI PATH ADMIN
|--------------------------------------------------------------------------
*/

$adminBase = '/agro-buana-sari/admin';

$title = $title ?? 'Panel Perusahaan';

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        <?= htmlspecialchars($title) ?> -
        PT Agro Buana Sari
    </title>

    <!-- CSS ADMIN -->
    <link
        rel="stylesheet"
        href="<?= $adminBase ?>/assets/admin.css"
    >

    <!-- FAVICON -->
    <link
        rel="icon"
        type="image/png"
        href="<?= $adminBase ?>/assets/img/logo.png"
    >

</head>


<body>

<div class="admin-layout">


    <!-- =====================================================
         SIDEBAR
    ====================================================== -->

    <aside class="sidebar">

        <div class="admin-brand">

            <img
                src="<?= $adminBase ?>/assets/img/logo.png"
                alt="PT Agro Buana Sari"
            >

            <div>

                <b>PT AGRO</b>

                <span>BUANA SARI</span>

                <small>COMPANY PANEL</small>

            </div>

        </div>


        <nav>

            <a href="<?= $adminBase ?>/index.php">
                🏠 Dashboard
            </a>

            <a href="<?= $adminBase ?>/produk/index.php">
                🌽 Produk &amp; Stok
            </a>

            <a href="<?= $adminBase ?>/career.php">
                💼 Kelola Karier
            </a>

            <a href="<?= $adminBase ?>/pesanan.php">
                🛒 Pesanan
            </a>

            <a href="<?= $adminBase ?>/career_applications.php">
                📄 Lamaran
            </a>

            <a href="<?= $adminBase ?>/artikel/index.php">
                📰 Artikel
            </a>

            <a href="<?= $adminBase ?>/galeri/index.php">
                📷 Galeri / Foto
            </a>

            <a
                href="/agro-buana-sari/index.php"
                target="_blank"
            >
                🌐 Lihat Website
            </a>

            <a
                href="<?= $adminBase ?>/logout.php"
                class="logout"
                onclick="return confirm('Yakin ingin keluar?')"
            >
                ↪ Keluar
            </a>

        </nav>

    </aside>


    <!-- =====================================================
         MAIN CONTENT
    ====================================================== -->

    <main class="main-content">


        <!-- TOPBAR -->

        <div class="topbar">

            <div class="topbar-left">

                <img
                    class="topbar-logo"
                    src="<?= $adminBase ?>/assets/img/logo.png"
                    alt="Logo PT Agro Buana Sari"
                >

                <strong>
                    <?= htmlspecialchars($title) ?>
                </strong>

            </div>


            <div class="topbar-user">

                <span>Perusahaan</span>

                <span>·</span>

                <strong>
                    <?= htmlspecialchars(
                        $_SESSION['admin_nama'] ?? 'Administrator'
                    ) ?>
                </strong>

            </div>

        </div>


        <!-- ISI HALAMAN -->

        <div class="content">