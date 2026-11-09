<?php
require_once '../inc/auth.php';
require_once '../../config/koneksi.php';

$title = 'Produk & Stok';

/* Ambil data produk */
$result = $conn->query("
    SELECT *
    FROM produk
    ORDER BY id DESC
");

$produk = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $produk[] = $row;
    }
}

/* Statistik */
$totalProduk = count($produk);
$totalStok   = 0;
$stokHabis   = 0;
$stokTersedia = 0;

foreach ($produk as $item) {
    $stok = (int) $item['stok'];

    $totalStok += $stok;

    if ($stok > 0) {
        $stokTersedia++;
    } else {
        $stokHabis++;
    }
}

include '../inc/header.php';
?>

<div class="produk-page">

    <!-- PAGE HEADER -->
    <div class="produk-header">
        <div>
            <div class="eyebrow-admin">
                INVENTORI
            </div>

            <h1>Produk &amp; Stok</h1>

            <p class="produk-subtitle">
                Kelola produk, foto, deskripsi, dan ketersediaan stok untuk pelanggan.
            </p>
        </div>

        <a href="tambah.php" class="produk-add-btn">
            <span>＋</span>
            Tambah Produk
        </a>
    </div>


    <!-- STATISTICS -->
    <div class="produk-stats">

        <div class="produk-stat-card">
            <div class="produk-stat-icon icon-product">
                📦
            </div>

            <div>
                <span>Total Produk</span>
                <strong><?= number_format($totalProduk) ?></strong>
            </div>
        </div>


        <div class="produk-stat-card">
            <div class="produk-stat-icon icon-stock">
                📊
            </div>

            <div>
                <span>Total Stok</span>
                <strong><?= number_format($totalStok) ?></strong>
            </div>
        </div>


        <div class="produk-stat-card">
            <div class="produk-stat-icon icon-available">
                ✓
            </div>

            <div>
                <span>Produk Tersedia</span>
                <strong><?= number_format($stokTersedia) ?></strong>
            </div>
        </div>


        <div class="produk-stat-card">
            <div class="produk-stat-icon icon-empty">
                !
            </div>

            <div>
                <span>Stok Habis</span>
                <strong><?= number_format($stokHabis) ?></strong>
            </div>
        </div>

    </div>


    <!-- TABLE CARD -->
    <div class="produk-card">

        <div class="produk-card-header">

            <div>
                <h2>Daftar Produk</h2>
                <p>
                    Daftar produk yang tersedia di sistem.
                </p>
            </div>

            <div class="produk-search">
                <span>⌕</span>
                <input
                    type="text"
                    id="produkSearch"
                    placeholder="Cari produk..."
                    autocomplete="off"
                >
            </div>

        </div>


        <div class="produk-table-wrapper">

            <table class="produk-table" id="produkTable">

                <thead>
                    <tr>
                        <th width="90">FOTO</th>
                        <th>PRODUK</th>
                        <th width="130">STOK</th>
                        <th>DESKRIPSI</th>
                        <th width="190">AKSI</th>
                    </tr>
                </thead>

                <tbody>

                <?php if (empty($produk)): ?>

                    <tr>
                        <td colspan="5">
                            <div class="produk-empty">
                                <div class="produk-empty-icon">
                                    📦
                                </div>

                                <h3>Belum ada produk</h3>

                                <p>
                                    Tambahkan produk pertama untuk mulai mengelola inventori.
                                </p>

                                <a href="tambah.php" class="produk-add-btn small">
                                    ＋ Tambah Produk
                                </a>
                            </div>
                        </td>
                    </tr>

                <?php else: ?>

                    <?php foreach ($produk as $r): ?>

                        <?php
                        $stok = (int) $r['stok'];

                        if ($stok > 0) {
                            $stockClass = 'available';
                            $stockText  = 'Tersedia';
                        } else {
                            $stockClass = 'empty';
                            $stockText  = 'Stok Habis';
                        }
                        ?>

                        <tr class="produk-row">

                            <!-- FOTO -->
                            <td>

                                <?php if (!empty($r['foto'])): ?>

                                    <div class="produk-image">
                                        <img
                                            src="../../uploads/produk/<?= htmlspecialchars($r['foto']) ?>"
                                            alt="<?= htmlspecialchars($r['nama_produk']) ?>"
                                            loading="lazy"
                                        >
                                    </div>

                                <?php else: ?>

                                    <div class="produk-image no-image">
                                        📦
                                    </div>

                                <?php endif; ?>

                            </td>


                            <!-- PRODUK -->
                            <td>

                                <div class="produk-name">
                                    <?= htmlspecialchars($r['nama_produk']) ?>
                                </div>

                                <div class="produk-id">
                                    ID Produk #<?= (int) $r['id'] ?>
                                </div>

                            </td>


                            <!-- STOK -->
                            <td>

                                <div class="stock-wrapper">

                                    <strong class="stock-number">
                                        <?= number_format($stok) ?>
                                    </strong>

                                    <span class="stock-badge <?= $stockClass ?>">
                                        <span></span>
                                        <?= $stockText ?>
                                    </span>

                                </div>

                            </td>


                            <!-- DESKRIPSI -->
                            <td>

                                <div class="produk-description">

                                    <?php
                                    $deskripsi = trim($r['deskripsi'] ?? '');

                                    if ($deskripsi === '') {
                                        echo '<span class="description-empty">Tidak ada deskripsi.</span>';
                                    } else {
                                        echo htmlspecialchars(
                                            mb_strimwidth(
                                                $deskripsi,
                                                0,
                                                120,
                                                '...'
                                            )
                                        );
                                    }
                                    ?>

                                </div>

                            </td>


                            <!-- AKSI -->
                            <td>

                                <div class="produk-actions">

                                    <a
                                        href="edit.php?id=<?= (int) $r['id'] ?>"
                                        class="action-edit"
                                    >
                                        ✎ Edit
                                    </a>

                                    <a
                                        href="hapus.php?id=<?= (int) $r['id'] ?>"
                                        class="action-delete"
                                        onclick="return confirm('Yakin ingin menghapus produk ini? Data produk yang dihapus tidak dapat dikembalikan.')"
                                    >
                                        🗑 Hapus
                                    </a>

                                </div>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                <?php endif; ?>

                </tbody>

            </table>

        </div>

        <!-- NO SEARCH RESULT -->
        <div id="noSearchResult" class="no-search-result">
            Produk yang Anda cari tidak ditemukan.
        </div>

        <!-- FOOTER -->
        <?php if (!empty($produk)): ?>

            <div class="produk-card-footer">
                <span>
                    Menampilkan
                    <strong id="visibleCount"><?= $totalProduk ?></strong>
                    produk
                </span>
            </div>

        <?php endif; ?>

    </div>

</div>


<style>

/* =========================================
   PRODUK PAGE
========================================= */

.produk-page {
    width: 100%;
}


/* HEADER */

.produk-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    gap: 30px;
    margin-bottom: 28px;
}

.produk-header h1 {
    margin: 4px 0 6px;
    font-size: 32px;
    line-height: 1.2;
    color: #10291f;
}

.produk-subtitle {
    margin: 0;
    color: #64736d;
    font-size: 14px;
}

.eyebrow-admin {
    color: #7b8c85;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 1.5px;
}


/* ADD BUTTON */

.produk-add-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    padding: 11px 18px;
    border-radius: 9px;
    background: #0b4b32;
    color: #fff !important;
    text-decoration: none !important;
    font-size: 13px;
    font-weight: 700;
    border: 1px solid #0b4b32;
    box-shadow: 0 4px 12px rgba(11, 75, 50, .15);
    transition: all .2s ease;
    white-space: nowrap;
}

.produk-add-btn:hover {
    background: #073b28;
    transform: translateY(-1px);
    box-shadow: 0 6px 16px rgba(11, 75, 50, .2);
}

.produk-add-btn span {
    font-size: 17px;
}

.produk-add-btn.small {
    font-size: 12px;
    padding: 9px 14px;
}


/* =========================================
   STATISTICS
========================================= */

.produk-stats {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 24px;
}

.produk-stat-card {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 18px;
    background: #fff;
    border: 1px solid #e3e9e5;
    border-radius: 12px;
    box-shadow: 0 4px 14px rgba(24, 47, 37, .04);
}

.produk-stat-card span {
    display: block;
    color: #7b8983;
    font-size: 12px;
    margin-bottom: 4px;
}

.produk-stat-card strong {
    display: block;
    color: #172d24;
    font-size: 22px;
}

.produk-stat-icon {
    width: 42px;
    height: 42px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
    font-size: 19px;
}

.icon-product {
    background: #edf5f0;
}

.icon-stock {
    background: #eef3f8;
}

.icon-available {
    background: #edf7ef;
}

.icon-empty {
    background: #fff4e9;
}


/* =========================================
   CARD
========================================= */

.produk-card {
    background: #fff;
    border: 1px solid #e2e8e4;
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 5px 20px rgba(24, 47, 37, .05);
}

.produk-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    padding: 20px 22px;
    border-bottom: 1px solid #edf1ef;
}

.produk-card-header h2 {
    margin: 0 0 4px;
    font-size: 16px;
    color: #193329;
}

.produk-card-header p {
    margin: 0;
    color: #829089;
    font-size: 12px;
}


/* SEARCH */

.produk-search {
    width: 240px;
    height: 38px;
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 0 12px;
    border: 1px solid #dfe6e2;
    border-radius: 8px;
    background: #fafcfb;
}

.produk-search span {
    color: #8a9992;
    font-size: 18px;
}

.produk-search input {
    width: 100%;
    border: none;
    outline: none;
    background: transparent;
    color: #263a32;
    font-size: 12px;
}

.produk-search input::placeholder {
    color: #9aa7a1;
}


/* =========================================
   TABLE
========================================= */

.produk-table-wrapper {
    width: 100%;
    overflow-x: auto;
}

.produk-table {
    width: 100%;
    border-collapse: collapse;
}

.produk-table th {
    padding: 13px 18px;
    background: #f8faf9;
    border-bottom: 1px solid #e7ece9;
    color: #66766e;
    text-align: left;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: .8px;
    white-space: nowrap;
}

.produk-table td {
    padding: 15px 18px;
    border-bottom: 1px solid #edf1ef;
    vertical-align: middle;
}

.produk-row {
    transition: background .15s ease;
}

.produk-row:hover {
    background: #fafcfb;
}

.produk-row:last-child td {
    border-bottom: none;
}


/* IMAGE */

.produk-image {
    width: 56px;
    height: 56px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    border-radius: 9px;
    background: #f1f5f3;
    border: 1px solid #e4ebe7;
}

.produk-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.no-image {
    color: #899790;
    font-size: 20px;
}


/* PRODUCT NAME */

.produk-name {
    max-width: 230px;
    color: #18352a;
    font-size: 13px;
    font-weight: 700;
}

.produk-id {
    margin-top: 4px;
    color: #9aa69f;
    font-size: 10px;
}


/* STOCK */

.stock-wrapper {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.stock-number {
    color: #1c342a;
    font-size: 15px;
}

.stock-badge {
    display: inline-flex;
    align-items: center;
    width: fit-content;
    gap: 5px;
    padding: 4px 7px;
    border-radius: 20px;
    font-size: 9px;
    font-weight: 700;
}

.stock-badge span {
    width: 5px;
    height: 5px;
    border-radius: 50%;
}

.stock-badge.available {
    color: #277345;
    background: #edf8f0;
}

.stock-badge.available span {
    background: #38a05c;
}

.stock-badge.empty {
    color: #a65a38;
    background: #fff1eb;
}

.stock-badge.empty span {
    background: #d96b43;
}


/* DESCRIPTION */

.produk-description {
    max-width: 400px;
    color: #64736c;
    font-size: 12px;
    line-height: 1.6;
}

.description-empty {
    color: #a4aea9;
    font-style: italic;
}


/* ACTION */

.produk-actions {
    display: flex;
    align-items: center;
    gap: 7px;
}

.produk-actions a {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 7px 10px;
    border-radius: 7px;
    text-decoration: none !important;
    font-size: 11px;
    font-weight: 700;
    transition: all .15s ease;
    white-space: nowrap;
}

.action-edit {
    color: #fff !important;
    background: #0b4b32;
}

.action-edit:hover {
    background: #073b28;
}

.action-delete {
    color: #a94f4f !important;
    background: #fff1f1;
    border: 1px solid #f4dede;
}

.action-delete:hover {
    background: #fde5e5;
}


/* =========================================
   EMPTY
========================================= */

.produk-empty {
    text-align: center;
    padding: 60px 20px;
}

.produk-empty-icon {
    width: 60px;
    height: 60px;
    margin: 0 auto 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 14px;
    background: #f0f5f2;
    font-size: 25px;
}

.produk-empty h3 {
    margin: 0 0 6px;
    color: #233b31;
    font-size: 16px;
}

.produk-empty p {
    margin: 0 auto 18px;
    max-width: 350px;
    color: #89968f;
    font-size: 12px;
}


/* =========================================
   SEARCH EMPTY
========================================= */

.no-search-result {
    display: none;
    padding: 30px;
    text-align: center;
    color: #87948e;
    font-size: 12px;
}


/* =========================================
   FOOTER
========================================= */

.produk-card-footer {
    display: flex;
    align-items: center;
    padding: 13px 20px;
    border-top: 1px solid #edf1ef;
    color: #8b9791;
    font-size: 11px;
}

.produk-card-footer strong {
    color: #344b40;
}


/* =========================================
   RESPONSIVE
========================================= */

@media (max-width: 1100px) {

    .produk-stats {
        grid-template-columns: repeat(2, 1fr);
    }

}

@media (max-width: 768px) {

    .produk-header {
        align-items: flex-start;
        flex-direction: column;
    }

    .produk-header h1 {
        font-size: 27px;
    }

    .produk-add-btn {
        width: 100%;
    }

    .produk-card-header {
        align-items: flex-start;
        flex-direction: column;
    }

    .produk-search {
        width: 100%;
    }

}

@media (max-width: 520px) {

    .produk-stats {
        grid-template-columns: 1fr;
    }

    .produk-table th,
    .produk-table td {
        padding: 12px;
    }

}

</style>


<script>

document.addEventListener('DOMContentLoaded', function () {

    const searchInput = document.getElementById('produkSearch');
    const rows = document.querySelectorAll('.produk-row');
    const noResult = document.getElementById('noSearchResult');
    const visibleCount = document.getElementById('visibleCount');

    if (!searchInput) return;

    searchInput.addEventListener('input', function () {

        const keyword = this.value.toLowerCase().trim();

        let count = 0;

        rows.forEach(function (row) {

            const text = row.innerText.toLowerCase();

            if (text.includes(keyword)) {
                row.style.display = '';
                count++;
            } else {
                row.style.display = 'none';
            }

        });

        if (noResult) {
            noResult.style.display =
                count === 0 ? 'block' : 'none';
        }

        if (visibleCount) {
            visibleCount.textContent = count;
        }

    });

});

</script>

<?php include '../inc/footer.php'; ?>