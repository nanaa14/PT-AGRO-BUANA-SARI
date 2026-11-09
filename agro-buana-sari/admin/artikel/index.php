<?php

require_once '../inc/auth.php';
require_once '../../config/koneksi.php';

$title = 'Kelola Artikel';

$rows = $conn->query("
    SELECT *
    FROM artikel
    ORDER BY id DESC
");

include '../inc/header.php';

?>

<div class="page-title">

    <h1>Artikel</h1>

    <a class="btn" href="tambah.php">
        + Tambah Artikel
    </a>

</div>


<div class="table-wrap">

    <table class="table">

        <thead>
            <tr>
                <th>Foto</th>
                <th>Judul</th>
                <th>Tanggal</th>
                <th>Link Artikel</th>
                <th>Aksi</th>
            </tr>
        </thead>

        <tbody>

        <?php if ($rows && $rows->num_rows > 0): ?>

            <?php while ($r = $rows->fetch_assoc()): ?>

                <tr>

                    <!-- FOTO -->
                    <td>

                        <?php if (!empty($r['foto'])): ?>

                            <img
                                class="thumb"
                                src="../../uploads/artikel/<?= htmlspecialchars($r['foto']) ?>"
                                alt="<?= htmlspecialchars($r['judul']) ?>"
                            >

                        <?php else: ?>

                            -

                        <?php endif; ?>

                    </td>


                    <!-- JUDUL -->
                    <td>
                        <?= htmlspecialchars($r['judul']) ?>
                    </td>


                    <!-- TANGGAL -->
                    <td>
                        <?= htmlspecialchars($r['tanggal']) ?>
                    </td>


                    <!-- LINK ARTIKEL -->
                    <td>

                        <?php if (!empty($r['link_artikel'])): ?>

                            <a
                                href="<?= htmlspecialchars($r['link_artikel']) ?>"
                                class="btn"
                                target="_blank"
                                rel="noopener noreferrer"
                            >
                                🔗 Link Artikel
                            </a>

                        <?php else: ?>

                            <span style="color:#999;">
                                Belum ada link
                            </span>

                        <?php endif; ?>

                    </td>


                    <!-- AKSI -->
                    <td>

                        <div class="actions">

                            <!-- EDIT -->
                            <a
                                class="btn green"
                                href="edit.php?id=<?= (int)$r['id'] ?>"
                            >
                                Edit
                            </a>


                            <!-- HAPUS -->
                            <a
                                class="btn danger"
                                href="hapus.php?id=<?= (int)$r['id'] ?>"
                                onclick="return confirm('Hapus artikel ini?')"
                            >
                                Hapus
                            </a>

                        </div>

                    </td>

                </tr>

            <?php endwhile; ?>

        <?php else: ?>

            <tr>

                <td
                    colspan="5"
                    style="text-align:center;"
                >
                    Belum ada artikel.
                </td>

            </tr>

        <?php endif; ?>

        </tbody>

    </table>

</div>


<?php

include '../inc/footer.php';

?>