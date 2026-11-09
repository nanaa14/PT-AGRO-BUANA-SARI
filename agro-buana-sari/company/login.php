<?php
session_start();

require_once '../config/koneksi.php';

$error = '';

// Jika sudah login sebagai admin
if (!empty($_SESSION['admin_id'])) {
    header('Location: ../admin/index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Username dan password wajib diisi.';
    } else {

        $stmt = $conn->prepare(
            "SELECT id, nama, username, password 
             FROM admin 
             WHERE username = ? 
             LIMIT 1"
        );

        if (!$stmt) {
            $error = 'Terjadi kesalahan pada database: ' . $conn->error;
        } else {

            $stmt->bind_param("s", $username);
            $stmt->execute();

            $stmt->store_result();

            if ($stmt->num_rows === 1) {

                $stmt->bind_result(
                    $admin_id,
                    $admin_nama,
                    $admin_username,
                    $admin_password
                );

                $stmt->fetch();

                /*
                 * Bisa menerima:
                 * 1. Password yang sudah di-hash
                 * 2. Password lama yang masih berupa teks biasa
                 */

                $login_valid = false;

                // Password sudah menggunakan password_hash()
                if (password_verify($password, $admin_password)) {
                    $login_valid = true;
                }

                // Jika password database masih berupa teks biasa
                if (!$login_valid && hash_equals($admin_password, $password)) {
                    $login_valid = true;

                    // Langsung ubah menjadi password hash
                    $password_baru = password_hash(
                        $password,
                        PASSWORD_DEFAULT
                    );

                    $update = $conn->prepare(
                        "UPDATE admin SET password = ? WHERE id = ?"
                    );

                    if ($update) {
                        $update->bind_param(
                            "si",
                            $password_baru,
                            $admin_id
                        );
                        $update->execute();
                        $update->close();
                    }
                }

                if ($login_valid) {

                    session_regenerate_id(true);

                    $_SESSION['admin_id']   = $admin_id;
                    $_SESSION['admin_nama'] = $admin_nama;
                    $_SESSION['admin_role'] = 'company';
                    $_SESSION['username']   = $admin_username;

                    header('Location: ../admin/index.php');
                    exit;

                } else {
                    $error = 'Username atau password salah.';
                }

            } else {
                $error = 'Username atau password salah.';
            }

            $stmt->close();
        }
    }
}
?>

<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Login Perusahaan | PT Agro Buana Sari</title>

    <link rel="stylesheet" href="../admin/assets/admin.css">
</head>

<body class="login-page company-login">

<div class="login-box">

    <a class="login-back" href="../index.php">
        ← Kembali ke website
    </a>

    <div class="login-logo">
        <img src="../assets/img/logo.png" alt="Logo PT Agro Buana Sari">
    </div>

    <div class="login-badge">
        AREA INTERNAL PERUSAHAAN
    </div>

    <h1>Login Perusahaan</h1>

    <p class="login-desc">
        Khusus admin/staf PT Agro Buana Sari untuk mengelola
        stok, produk, artikel, karier, dan pesanan.
    </p>

    <?php if ($error): ?>
        <div class="alert">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="post" autocomplete="off">

        <div class="form-group">
            <label for="username">
                Username Perusahaan
            </label>

            <input
                type="text"
                id="username"
                name="username"
                value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                required
                autocomplete="username"
            >
        </div>

        <div class="form-group">
            <label for="password">
                Password
            </label>

            <input
                type="password"
                id="password"
                name="password"
                required
                autocomplete="current-password"
            >
        </div>

        <button
            type="submit"
            class="btn green"
            style="width:100%;"
        >
            Masuk ke Panel Perusahaan
        </button>

    </form>



    <div class="login-separator"></div>

    <a class="public-link" href="../login.php">
        Login Pengguna / Pelanggan
    </a>

</div>

</body>
</html>