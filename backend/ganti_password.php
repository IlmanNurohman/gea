<?php

session_start();
include '../../../backend/koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'operator') {
    die('Akses ditolak');
}

if (isset($_POST['ganti_password'])) {

    $user_id             = $_SESSION['user_id'];
    $password_baru       = $_POST['password_baru'] ?? '';
    $konfirmasi_password = $_POST['konfirmasi_password'] ?? '';

    // =========================
    // VALIDASI
    // =========================

    if ($password_baru === '' || $konfirmasi_password === '') {

        $_SESSION['swal'] = [
            'title' => 'Gagal',
            'text'  => 'Password wajib diisi.',
            'type'  => 'error'
        ];

        header("Location: index.php");
        exit;
    }

    // =========================
    // CEK KONFIRMASI PASSWORD
    // =========================

    if ($password_baru !== $konfirmasi_password) {

        $_SESSION['swal'] = [
            'title' => 'Gagal',
            'text'  => 'Konfirmasi password tidak cocok.',
            'type'  => 'error'
        ];

        header("Location: index.php");
        exit;
    }

    // =========================
    // HASH PASSWORD
    // =========================

    $password_hash = password_hash(
        $password_baru,
        PASSWORD_DEFAULT
    );

    // =========================
    // UPDATE PASSWORD
    // =========================

    $stmt = mysqli_prepare(
        $conn,
        "UPDATE users
         SET password = ?
         WHERE id = ?"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "si",
        $password_hash,
        $user_id
    );

    if (mysqli_stmt_execute($stmt)) {

        $_SESSION['swal'] = [
            'title' => 'Berhasil',
            'text'  => 'Password berhasil diubah.',
            'type'  => 'success'
        ];

    } else {

        $_SESSION['swal'] = [
            'title' => 'Gagal',
            'text'  => 'Password gagal diubah.',
            'type'  => 'error'
        ];
    }

    mysqli_stmt_close($stmt);

    header("Location: index.php");
    exit;
}