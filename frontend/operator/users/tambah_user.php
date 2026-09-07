<?php

session_start();
include '../../../backend/koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'operator') {
    die('Akses ditolak');
}

if (isset($_POST['simpan'])) {

    $username  = trim($_POST['username']);
    $password  = $_POST['password'];
    $role      = $_POST['role'];
    $nisn_anak = trim($_POST['nisn_anak'] ?? '');

    // =========================
    // VALIDASI
    // =========================

    if ($username === '' || $password === '' || $role === '') {
        $_SESSION['swal'] = [
            'title' => 'Gagal',
            'text'  => 'Semua data wajib diisi.',
            'type'  => 'error'
        ];

        header("Location: index.php");
        exit;
    }

    // =========================
    // CEK USERNAME
    // =========================

    $stmt = mysqli_prepare(
        $conn,
        "SELECT id FROM users WHERE username = ?"
    );

    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    if (mysqli_stmt_num_rows($stmt) > 0) {

        $_SESSION['swal'] = [
            'title' => 'Gagal',
            'text'  => 'Username sudah digunakan.',
            'type'  => 'error'
        ];

        header("Location: index.php");
        exit;
    }

    mysqli_stmt_close($stmt);

    // =========================
    // KHUSUS ORANG TUA
    // =========================

    $siswa_id = null;

    if ($role === 'orang_tua') {

        if ($nisn_anak === '') {

            $_SESSION['swal'] = [
                'title' => 'Gagal',
                'text'  => 'NISN anak wajib diisi.',
                'type'  => 'error'
            ];

            header("Location: index.php");
            exit;
        }

        // Cari siswa berdasarkan NISN
        $stmt = mysqli_prepare(
            $conn,
            "SELECT id, nama_lengkap 
             FROM siswa 
             WHERE nisn = ?"
        );

        mysqli_stmt_bind_param($stmt, "s", $nisn_anak);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);
        $siswa = mysqli_fetch_assoc($result);

        mysqli_stmt_close($stmt);

        if (!$siswa) {

            $_SESSION['swal'] = [
                'title' => 'Gagal',
                'text'  => 'NISN anak tidak ditemukan.',
                'type'  => 'error'
            ];

            header("Location: index.php");
            exit;
        }

        $siswa_id = $siswa['id'];
    }

    // =========================
    // BUAT USER
    // =========================

    $password_hash = password_hash(
        $password,
        PASSWORD_DEFAULT
    );

    $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO users (username, password, role)
         VALUES (?, ?, ?)"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "sss",
        $username,
        $password_hash,
        $role
    );

    if (!mysqli_stmt_execute($stmt)) {

        $_SESSION['swal'] = [
            'title' => 'Gagal',
            'text'  => 'Gagal membuat akun.',
            'type'  => 'error'
        ];

        header("Location: index.php");
        exit;
    }

    $orang_tua_id = mysqli_insert_id($conn);

    mysqli_stmt_close($stmt);

    // =========================
    // HUBUNGKAN ORANG TUA DENGAN ANAK
    // =========================

    if ($role === 'orang_tua') {

        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO orang_tua_siswa
             (orang_tua_id, siswa_id)
             VALUES (?, ?)"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "ii",
            $orang_tua_id,
            $siswa_id
        );

        mysqli_stmt_execute($stmt);

        mysqli_stmt_close($stmt);
    }

    // =========================
    // BERHASIL
    // =========================

    $_SESSION['swal'] = [
        'title' => 'Berhasil',
        'text'  => 'User berhasil ditambahkan.',
        'type'  => 'success'
    ];

    header("Location: index.php");
    exit;
}