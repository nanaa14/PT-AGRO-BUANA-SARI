<?php

require_once 'inc/auth.php';
require_once '../config/koneksi.php';

$title = 'Kelola Karier';
$error = '';
$success = '';

/* =========================
   PROSES SIMPAN / UPDATE / HAPUS
   ========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);

    /* =========================
       SIMPAN / UPDATE
       ========================= */
    if ($action === 'save') {

        $posisi = trim($_POST['posisi'] ?? '');
        $lokasi = trim($_POST['lokasi'] ?? '');
        $tipe = trim($_POST['tipe'] ?? '');
        $deskripsi = trim($_POST['deskripsi'] ?? '');
        $kualifikasi = trim($_POST['kualifikasi'] ?? '');
        $status = trim($_POST['status'] ?? 'Aktif');

        if ($posisi === '' || $deskripsi === '' || $kualifikasi === '') {

            $error = 'Posisi, deskripsi, dan kualifikasi wajib diisi.';

        } else {

            /* UPDATE */
            if ($id > 0) {

                $sql = "
                    UPDATE career_positions
                    SET
                        posisi = ?,
                        lokasi = ?,
                        tipe = ?,
                        deskripsi = ?,
                        kualifikasi = ?,
                        status = ?
                    WHERE id = ?
                ";

                $stmt = $conn->prepare($sql);

                if (!$stmt) {

                    $error = 'Query update gagal: ' . $conn->error;

                } else {

                    $stmt->bind_param(
                        'ssssssi',
                        $posisi,
                        $lokasi,
                        $tipe,
                        $deskripsi,
                        $kualifikasi,
                        $status,
                        $id
                    );

                    if ($stmt->execute()) {

                        $stmt->close();

                        header('Location: career.php?success=updated');
                        exit;

                    } else {

                        $error = 'Data gagal diperbarui: ' . $stmt->error;
                        $stmt->close();
                    }
                }

            /* INSERT */
            } else {

                $sql = "
                    INSERT INTO career_positions
                    (
                        posisi,
                        lokasi,
                        tipe,
                        deskripsi,
                        kualifikasi,
                        status
                    )
                    VALUES (?, ?, ?, ?, ?, ?)
                ";

                $stmt = $conn->prepare($sql);

                if (!$stmt) {

                    $error = 'Query simpan gagal: ' . $conn->error;

                } else {

                    $stmt->bind_param(
                        'ssssss',
                        $posisi,
                        $lokasi,
                        $tipe,
                        $deskripsi,
                        $kualifikasi,
                        $status
                    );

                    if ($stmt->execute()) {

                        $stmt->close();

                        header('Location: career.php?success=added');
                        exit;

                    } else {

                        $error = 'Lowongan gagal disimpan: ' . $stmt->error;
                        $stmt->close();
                    }
                }
            }
        }
    }

    /* =========================
       HAPUS
       ========================= */
    elseif ($action === 'delete') {

        if ($id <= 0) {

            $error = 'ID lowongan tidak valid.';

        } else {

            $stmt = $conn->prepare(
                'DELETE FROM career_positions WHERE id = ?'
            );

            if (!$stmt) {

                $error = 'Query hapus gagal: ' . $conn->error;

            } else {

                $stmt->bind_param('i', $id);

                if ($stmt->execute()) {

                    $stmt->close();

                    header('Location: career.php?success=deleted');
                    exit;

                } else {

                    $error = 'Lowongan gagal dihapus: ' . $stmt->error;
                    $stmt->close();
                }
            }
        }
    }
}


/* =========================
   PESAN BERHASIL
   ========================= */
if (isset($_GET['success'])) {

    if ($_GET['success'] === 'added') {
        $success = 'Lowongan berhasil disimpan.';
    }

    if ($_GET['success'] === 'updated') {
        $success = 'Lowongan berhasil diperbarui.';
    }

    if ($_GET['success'] === 'deleted') {
        $success = 'Lowongan berhasil dihapus.';
    }
}


/* =========================
   DATA EDIT
   ========================= */
$edit = null;

if (isset($_GET['edit'])) {

    $editId = (int)$_GET['edit'];

    if ($editId > 0) {

        $stmt = $conn->prepare(
            'SELECT * FROM career_positions WHERE id = ?'
        );

        if ($stmt) {

            $stmt->bind_param('i', $editId);
            $stmt->execute();

            $result = $stmt->get_result();

            $edit = $result->fetch_assoc();

            $stmt->close();
        }
    }
}


/* =========================
   AMBIL DATA LOWONGAN
   ========================= */
$rows = $conn->query(
    'SELECT * FROM career_positions ORDER BY id DESC'
);

if (!$rows) {
    $error = 'Gagal mengambil data lowongan: ' . $conn->error;
}


/* =========================
   HEADER ADMIN
   ========================= */
include 'inc/header.php';

?>

<div class="page-title">

    <div>

        <p class="eyebrow-admin">
            CAREER MANAGEMENT
        </p>

        <h1>
            Kelola Karier
        </h1>

        <p>
            Publikasikan lowongan dan atur status rekrutmen perusahaan.
        </p>

    </div>

</div>


<?php if ($success): ?>

    <div class="alert success">
        <?= htmlspecialchars($success) ?>
    </div>

<?php endif; ?>


<?php if ($error): ?>

    <div class="alert">
        <?= htmlspecialchars($error) ?>
    </div>

<?php endif; ?>


<div class="career-admin-grid">

    <!-- FORM -->
    <div class="form-card">

        <h2>
            <?= $edit ? 'Edit Lowongan' : 'Tambah Lowongan' ?>
        </h2>

        <form method="post">

            <input
                type="hidden"
                name="action"
                value="save"
            >

            <input
                type="hidden"
                name="id"
                value="<?= (int)($edit['id'] ?? 0) ?>"
            >


            <div class="form-group">

                <label>
                    Posisi
                </label>

                <input
                    type="text"
                    name="posisi"
                    value="<?= htmlspecialchars($edit['posisi'] ?? '') ?>"
                    required
                >

            </div>


            <div class="form-group">

                <label>
                    Lokasi
                </label>

                <input
                    type="text"
                    name="lokasi"
                    value="<?= htmlspecialchars($edit['lokasi'] ?? 'Jonggol, Bogor') ?>"
                    required
                >

            </div>


            <div class="form-group">

                <label>
                    Tipe
                </label>

                <input
                    type="text"
                    name="tipe"
                    value="<?= htmlspecialchars($edit['tipe'] ?? 'Full Time') ?>"
                    required
                >

            </div>


            <div class="form-group">

                <label>
                    Deskripsi
                </label>

                <textarea
                    name="deskripsi"
                    required
                ><?= htmlspecialchars($edit['deskripsi'] ?? '') ?></textarea>

            </div>


            <div class="form-group">

                <label>
                    Kualifikasi
                </label>

                <textarea
                    name="kualifikasi"
                    required
                ><?= htmlspecialchars($edit['kualifikasi'] ?? '') ?></textarea>

            </div>


            <div class="form-group">

                <label>
                    Status
                </label>

                <select name="status">

                    <option
                        value="Aktif"
                        <?= ($edit['status'] ?? 'Aktif') === 'Aktif'
                            ? 'selected'
                            : '' ?>
                    >
                        Aktif
                    </option>

                    <option
                        value="Ditutup"
                        <?= ($edit['status'] ?? '') === 'Ditutup'
                            ? 'selected'
                            : '' ?>
                    >
                        Ditutup
                    </option>

                </select>

            </div>


            <button
                type="submit"
                class="btn green"
            >
                <?= $edit ? 'Update Lowongan' : 'Simpan Lowongan' ?>
            </button>


            <?php if ($edit): ?>

                <a
                    href="career.php"
                    class="btn gray"
                >
                    Batal
                </a>

            <?php endif; ?>

        </form>

    </div>


    <!-- DAFTAR LOWONGAN -->
    <div>

        <div class="table-wrap">

            <table class="table">

                <tr>

                    <th>
                        Posisi
                    </th>

                    <th>
                        Lokasi
                    </th>

                    <th>
                        Status
                    </th>

                    <th>
                        Aksi
                    </th>

                </tr>


                <?php if ($rows && $rows->num_rows > 0): ?>

                    <?php while ($r = $rows->fetch_assoc()): ?>

                        <tr>

                            <td>

                                <b>
                                    <?= htmlspecialchars($r['posisi']) ?>
                                </b>

                                <br>

                                <small>
                                    <?= htmlspecialchars($r['tipe']) ?>
                                </small>

                            </td>


                            <td>
                                <?= htmlspecialchars($r['lokasi']) ?>
                            </td>


                            <td>
                                <?= htmlspecialchars($r['status']) ?>
                            </td>


                            <td>

                                <div class="actions">

                                    <!-- EDIT -->
                                    <a
                                        class="btn green"
                                        href="career.php?edit=<?= (int)$r['id'] ?>"
                                    >
                                        Edit
                                    </a>


                                    <!-- HAPUS -->
                                    <form
                                        method="post"
                                        onsubmit="return confirm('Hapus lowongan ini?')"
                                    >

                                        <input
                                            type="hidden"
                                            name="action"
                                            value="delete"
                                        >

                                        <input
                                            type="hidden"
                                            name="id"
                                            value="<?= (int)$r['id'] ?>"
                                        >

                                        <button
                                            type="submit"
                                            class="btn danger"
                                        >
                                            Hapus
                                        </button>

                                    </form>

                                </div>

                            </td>

                        </tr>

                    <?php endwhile; ?>

                <?php else: ?>

                    <tr>

                        <td
                            colspan="4"
                            style="text-align:center;"
                        >
                            Belum ada lowongan pekerjaan.
                        </td>

                    </tr>

                <?php endif; ?>

            </table>

        </div>

    </div>

</div>


<?php

include 'inc/footer.php';

?>