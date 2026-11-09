<?php
session_start();

require_once 'config/koneksi.php';

/* ==============================
   CEK LOGIN
   ============================== */
if (empty($_SESSION['user_id'])) {
    header(
        'Location: login.php?redirect=' .
        urlencode($_SERVER['REQUEST_URI'])
    );
    exit;
}


/* ==============================
   AMBIL ID / NAMA PRODUK
   ============================== */

$id   = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$nama = trim($_GET['nama'] ?? '');

$produk = null;


/* ==============================
   CARI BERDASARKAN ID
   ============================== */

if ($id > 0) {

    $stmt = $conn->prepare(
        "SELECT *
         FROM produk
         WHERE id = ?
         LIMIT 1"
    );

    if (!$stmt) {
        die("Error database: " . $conn->error);
    }

    $stmt->bind_param("i", $id);
    $stmt->execute();

    $result = $stmt->get_result();
    $produk = $result->fetch_assoc();

    $stmt->close();
}


/* ==============================
   CARI BERDASARKAN NAMA
   ============================== */

if (!$produk && $nama !== '') {

    $stmt = $conn->prepare(
        "SELECT *
         FROM produk
         WHERE TRIM(nama_produk) = TRIM(?)
         LIMIT 1"
    );

    if (!$stmt) {
        die("Error database: " . $conn->error);
    }

    $stmt->bind_param("s", $nama);
    $stmt->execute();

    $result = $stmt->get_result();
    $produk = $result->fetch_assoc();

    $stmt->close();
}


/* ==============================
   PRODUK TIDAK DITEMUKAN
   ============================== */

if (!$produk) {

    die("
        <div style='
            font-family:Arial;
            padding:40px;
            text-align:center;
        '>

            <h2>Produk tidak ditemukan.</h2>

            <p>
                Produk yang dipilih belum tersedia
                di database produk.
            </p>

            <a href='index.php'>
                Kembali ke Produk
            </a>

        </div>
    ");
}


/* ==============================
   DATA PRODUK
   ============================== */

$produkId = (int)$produk['id'];
$stok     = (int)$produk['stok'];

$namaProduk = $produk['nama_produk'];

$error = '';



/* ==============================
   DATA USER
   ============================== */

$userId = (int)$_SESSION['user_id'];

$stmt = $conn->prepare(
    "SELECT *
     FROM users
     WHERE id = ?
     LIMIT 1"
);

$stmt->bind_param("i", $userId);
$stmt->execute();

$user = $stmt->get_result()->fetch_assoc();

$stmt->close();


if (!$user) {
    session_destroy();

    header("Location: login.php");

    exit;
}


/* ==============================
   PROSES PESANAN
   ============================== */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $namaPelanggan = trim(
        $_POST['nama'] ?? ''
    );

    $email = trim(
        $_POST['email'] ?? ''
    );

    $noHp = trim(
        $_POST['no_hp'] ?? ''
    );

    $jumlah = (int)(
        $_POST['jumlah'] ?? 1
    );

    $alamat = trim(
        $_POST['alamat'] ?? ''
    );


    /* ==========================
       VALIDASI
       ========================== */

    if ($stok <= 0) {

        $error =
            "Stok produk sedang habis.";

    } elseif ($jumlah < 1) {

        $error =
            "Jumlah pesanan minimal 1.";

    } elseif ($jumlah > $stok) {

        $error =
            "Jumlah pesanan melebihi stok yang tersedia.";

    } elseif (
        $namaPelanggan === '' ||
        $email === '' ||
        $noHp === '' ||
        $alamat === ''
    ) {

        $error =
            "Semua data pemesanan wajib diisi.";

    } elseif (
        !filter_var($email, FILTER_VALIDATE_EMAIL)
    ) {

        $error =
            "Email tidak valid.";
    }


    /* ==========================
       SIMPAN PESANAN
       ========================== */

    if ($error === '') {

        $conn->begin_transaction();

        try {

            /* Kunci stok */
            $lock = $conn->prepare(
                "SELECT stok, nama_produk
                 FROM produk
                 WHERE id = ?
                 FOR UPDATE"
            );

            if (!$lock) {
                throw new Exception(
                    "Gagal mengecek stok: " .
                    $conn->error
                );
            }

            $lock->bind_param(
                "i",
                $produkId
            );

            $lock->execute();

            $dataProduk =
                $lock
                ->get_result()
                ->fetch_assoc();

            $lock->close();


            if (!$dataProduk) {

                throw new Exception(
                    "Produk tidak ditemukan."
                );
            }


            $stokSekarang =
                (int)$dataProduk['stok'];


            if ($stokSekarang < $jumlah) {

                throw new Exception(
                    "Stok tidak mencukupi."
                );
            }


            /* ======================
               INSERT PESANAN
               ====================== */

            $q = $conn->prepare(
                "INSERT INTO pesanan
                (
                    user_id,
                    produk_id,
                    nama_pelanggan,
                    email,
                    no_hp,
                    jumlah,
                    alamat
                )
                VALUES (?, ?, ?, ?, ?, ?, ?)"
            );


            if (!$q) {

                throw new Exception(
                    "Gagal membuat pesanan: " .
                    $conn->error
                );
            }


            $q->bind_param(
                "iisssis",
                $userId,
                $produkId,
                $namaPelanggan,
                $email,
                $noHp,
                $jumlah,
                $alamat
            );


            if (!$q->execute()) {

                throw new Exception(
                    "Pesanan gagal disimpan: " .
                    $q->error
                );
            }


            $q->close();


            /* ======================
               UPDATE STOK
               ====================== */

            $stokBaru =
                $stokSekarang - $jumlah;


            $up = $conn->prepare(
                "UPDATE produk
                 SET stok = ?
                 WHERE id = ?"
            );


            if (!$up) {

                throw new Exception(
                    "Gagal update stok: " .
                    $conn->error
                );
            }


            $up->bind_param(
                "ii",
                $stokBaru,
                $produkId
            );


            if (!$up->execute()) {

                throw new Exception(
                    "Stok gagal diperbarui: " .
                    $up->error
                );
            }


            $up->close();


            /* ======================
               SELESAI
               ====================== */

            $conn->commit();


            /* ======================
               WHATSAPP
               ====================== */

            $pesan =
                "Halo PT Agro Buana Sari, saya ingin memesan "
                . $dataProduk['nama_produk']
                . " sebanyak "
                . $jumlah
                . " unit.\n\n"
                . "Nama: "
                . $namaPelanggan
                . "\n"
                . "Email: "
                . $email
                . "\n"
                . "No. WhatsApp: "
                . $noHp
                . "\n"
                . "Alamat: "
                . $alamat
                . "\n\n"
                . "Mohon konfirmasi pesanan saya.";


            $wa =
                "https://wa.me/6285710234948?text="
                . rawurlencode($pesan);


            header(
                "Location: " . $wa
            );

            exit;


        } catch (Exception $e) {

            $conn->rollback();

            $error =
                $e->getMessage();
        }
    }
}


/* ==============================
   FOTO
   ============================== */

if (!empty($produk['foto'])) {

    $photo =
        "uploads/produk/" .
        $produk['foto'];

} else {

    $photo =
        "assets/img/hero.jpg";
}

?>
<!doctype html>

<html lang="id">

<head>

<meta charset="utf-8">

<meta
    name="viewport"
    content="width=device-width,initial-scale=1"
>

<title>
Beli <?= htmlspecialchars($namaProduk) ?>
</title>

<link
    rel="stylesheet"
    href="assets/css/style.css"
>

</head>

<body>


<header class="navbar">

<div class="container nav-wrap">

<a
    href="index.php"
    class="brand"
>

<img
    src="assets/img/logo.png"
    alt="Logo"
>

<div>

<strong>PT AGRO</strong>

<span>BUANA SARI</span>

</div>

</a>


<div class="nav-actions">

<span class="nav-user">

Halo,
<?= htmlspecialchars(
    $_SESSION['user_nama'] ??
    $user['nama']
) ?>

</span>


<a
    class="btn btn-outline btn-small"
    href="logout.php"
>
Keluar
</a>

</div>

</div>

</header>


<main class="section">

<div class="container buy-layout">


<div class="buy-product">

<img
    src="<?= htmlspecialchars($photo) ?>"
    alt="<?= htmlspecialchars($namaProduk) ?>"
>


<div class="buy-product-body">

<p class="label">
PEMESANAN PRODUK
</p>


<h1>
<?= htmlspecialchars($namaProduk) ?>
</h1>


<p>
<?= htmlspecialchars(
    $produk['deskripsi'] ?? ''
) ?>
</p>


<div class="stock-large">

Stok tersedia:

<strong>
<?= number_format($stok) ?>
</strong>

</div>

</div>

</div>



<div class="form-card">

<h2>
Data Pemesanan
</h2>


<?php if ($error): ?>

<div class="alert">

<?= htmlspecialchars($error) ?>

</div>

<?php endif; ?>


<form method="post">


<div class="form-group">

<label>Nama</label>

<input
    name="nama"
    value="<?= htmlspecialchars(
        $user['nama'] ?? ''
    ) ?>"
    required
>

</div>


<div class="form-group">

<label>Email</label>

<input
    type="email"
    name="email"
    value="<?= htmlspecialchars(
        $user['email'] ?? ''
    ) ?>"
    required
>

</div>


<div class="form-group">

<label>No. WhatsApp</label>

<input
    name="no_hp"
    value="<?= htmlspecialchars(
        $user['no_hp'] ?? ''
    ) ?>"
    required
>

</div>


<div class="form-group">

<label>Jumlah</label>

<input
    type="number"
    name="jumlah"
    min="1"
    max="<?= $stok ?>"
    value="1"
    required
>

</div>


<div class="form-group">

<label>Alamat Pengiriman</label>

<textarea
    name="alamat"
    required
    placeholder="Alamat lengkap pengiriman"
></textarea>

</div>


<button
    class="btn"
    type="submit"
>
🛒 Pesan &amp; Kirim ke WhatsApp
</button>


<a
    class="btn btn-outline"
    href="index.php#produk"
>
Batal
</a>


</form>

</div>

</div>

</main>

</body>

</html>