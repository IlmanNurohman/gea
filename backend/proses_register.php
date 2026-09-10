<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

include __DIR__ . '/koneksi.php';

if (isset($_POST['register'])) {

    $username  = trim($_POST['username'] ?? '');
    $password  = $_POST['password'] ?? '';
    $nisn_anak = trim($_POST['nisn_anak'] ?? '');

    // Role otomatis
    $role = 'orang_tua';

    // ==========================================
    // VALIDASI FIELD
    // ==========================================

    if ($username === '' || $password === '' || $nisn_anak === '') {

        header("Location: ../login.php?register=empty");
        exit;
    }

    // Minimal 6 karakter
    if (strlen($password) < 6) {

        header("Location: ../login.php?register=password_short");
        exit;
    }

    // ==========================================
    // CEK USERNAME
    // ==========================================

    $stmt = mysqli_prepare(
        $conn,
        "SELECT id 
         FROM users 
         WHERE username = ? 
         LIMIT 1"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "s",
        $username
    );

    mysqli_stmt_execute($stmt);

    mysqli_stmt_store_result($stmt);

    if (mysqli_stmt_num_rows($stmt) > 0) {

        mysqli_stmt_close($stmt);

        header("Location: ../login.php?register=username_exists");
        exit;
    }

    mysqli_stmt_close($stmt);

    // ==========================================
    // CARI SISWA BERDASARKAN NISN
    // ==========================================

    $stmt = mysqli_prepare(
        $conn,
        "SELECT id 
         FROM siswa 
         WHERE nisn = ? 
         LIMIT 1"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "s",
        $nisn_anak
    );

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    $siswa = mysqli_fetch_assoc($result);

    mysqli_stmt_close($stmt);

    // NISN tidak ditemukan
    if (!$siswa) {

        header("Location: ../login.php?register=nisn_not_found");
        exit;
    }

    $siswa_id = (int) $siswa['id'];

    // ==========================================
    // HASH PASSWORD
    // ==========================================

    $password_hash = password_hash(
        $password,
        PASSWORD_DEFAULT
    );

    // ==========================================
    // TRANSACTION
    // ==========================================

    mysqli_begin_transaction($conn);

    try {

        // ======================================
        // INSERT USER ORANG TUA
        // ======================================

        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO users
                (username, password, role)
             VALUES
                (?, ?, ?)"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "sss",
            $username,
            $password_hash,
            $role
        );

        mysqli_stmt_execute($stmt);

        // Ambil ID user yang baru dibuat
        $orang_tua_id = mysqli_insert_id($conn);

        mysqli_stmt_close($stmt);

        // ======================================
        // INSERT RELASI ORANG TUA - SISWA
        // ======================================

        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO orang_tua_siswa
                (orang_tua_id, siswa_id)
             VALUES
                (?, ?)"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "ii",
            $orang_tua_id,
            $siswa_id
        );

        mysqli_stmt_execute($stmt);

        mysqli_stmt_close($stmt);

        // ======================================
        // COMMIT
        // ======================================

        mysqli_commit($conn);

        // Redirect ke login
        header("Location: ../login.php?register=success");
        exit;

    } catch (Throwable $e) {

        // Batalkan semua query jika ada error
        mysqli_rollback($conn);

        // Untuk sementara error disimpan di URL
        header(
            "Location: ../login.php?register=failed"
        );
        exit;
    }
}