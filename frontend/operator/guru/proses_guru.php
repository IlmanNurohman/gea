<?php
session_start();
include '../../../backend/koneksi.php';

$aksi = $_GET['aksi'] ?? '';

if ($aksi === 'tambah') {

    $nip           = mysqli_real_escape_string($conn, $_POST['nip']);
    $password      = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $nama_guru     = mysqli_real_escape_string($conn, $_POST['nama_guru']);
    $jenis_kelamin = mysqli_real_escape_string($conn, $_POST['jenis_kelamin']);

    $mapel_input = trim($_POST['mapel_id'] ?? '');

    $mapel_id = null;

    /*
     * Jika user memilih mapel yang sudah ada,
     * value-nya berupa ID.
     *
     * Jika user mengetik mapel baru,
     * value-nya berupa nama mapel.
     */
    if ($mapel_input !== '') {

        if (ctype_digit($mapel_input)) {

            // Mapel sudah ada
            $mapel_id = (int) $mapel_input;

        } else {

            // Mapel baru
            $nama_mapel = mysqli_real_escape_string($conn, $mapel_input);

            // Cek apakah sebenarnya nama tersebut sudah ada
            $cek_mapel = mysqli_query(
                $conn,
                "SELECT id FROM mapel WHERE nama_mapel = '$nama_mapel' LIMIT 1"
            );

            if ($cek_mapel && mysqli_num_rows($cek_mapel) > 0) {

                $mapel = mysqli_fetch_assoc($cek_mapel);
                $mapel_id = (int) $mapel['id'];

            } else {

                // Buat mapel baru
                $insert_mapel = mysqli_query(
                    $conn,
                    "INSERT INTO mapel (nama_mapel)
                     VALUES ('$nama_mapel')"
                );

                if (!$insert_mapel) {
                    $_SESSION['swal'] = [
                        'title' => 'Gagal',
                        'text' => 'Gagal menambahkan mata pelajaran baru',
                        'type' => 'error'
                    ];

                    header("Location: index.php");
                    exit;
                }

                $mapel_id = mysqli_insert_id($conn);
            }
        }
    }

    // =========================
    // 1. Tambah user
    // =========================

    $q_user = "INSERT INTO users
               (username, password, role)
               VALUES
               ('$nip', '$password', 'guru')";

    if (mysqli_query($conn, $q_user)) {

        $user_id = mysqli_insert_id($conn);

        // =========================
        // 2. Tambah guru
        // =========================

        $mapel_sql = $mapel_id !== null
            ? (int) $mapel_id
            : "NULL";

        $q_guru = "INSERT INTO guru
                   (user_id, nip, nama_guru, jenis_kelamin, mapel_id)
                   VALUES
                   ('$user_id',
                    '$nip',
                    '$nama_guru',
                    '$jenis_kelamin',
                    $mapel_sql)";

        if (mysqli_query($conn, $q_guru)) {

            $_SESSION['swal'] = [
                'title' => 'Berhasil',
                'text' => 'Data guru berhasil ditambahkan',
                'type' => 'success'
            ];

        } else {

            $_SESSION['swal'] = [
                'title' => 'Gagal',
                'text' => 'Gagal menyimpan data guru: ' . mysqli_error($conn),
                'type' => 'error'
            ];
        }

    } else {

        $_SESSION['swal'] = [
            'title' => 'Gagal',
            'text' => 'NIP / Username sudah ada',
            'type' => 'error'
        ];
    }

    header("Location: index.php");
    exit;
}

if ($aksi === 'edit') {

    $id            = (int) $_POST['id'];
    $nip           = mysqli_real_escape_string($conn, $_POST['nip']);
    $nama_guru     = mysqli_real_escape_string($conn, $_POST['nama_guru']);
    $jenis_kelamin = mysqli_real_escape_string($conn, $_POST['jenis_kelamin']);

    $mapel_input = trim($_POST['mapel_id'] ?? '');

    $mapel_id = null;

    if ($mapel_input !== '') {

        if (ctype_digit($mapel_input)) {

            $mapel_id = (int) $mapel_input;

        } else {

            $nama_mapel = mysqli_real_escape_string($conn, $mapel_input);

            $cek_mapel = mysqli_query(
                $conn,
                "SELECT id
                 FROM mapel
                 WHERE nama_mapel = '$nama_mapel'
                 LIMIT 1"
            );

            if ($cek_mapel && mysqli_num_rows($cek_mapel) > 0) {

                $mapel = mysqli_fetch_assoc($cek_mapel);
                $mapel_id = (int) $mapel['id'];

            } else {

                mysqli_query(
                    $conn,
                    "INSERT INTO mapel (nama_mapel)
                     VALUES ('$nama_mapel')"
                );

                $mapel_id = mysqli_insert_id($conn);
            }
        }
    }

    $mapel_sql = $mapel_id !== null
        ? (int) $mapel_id
        : "NULL";

    $q_update = "UPDATE guru SET
                    nip = '$nip',
                    nama_guru = '$nama_guru',
                    jenis_kelamin = '$jenis_kelamin',
                    mapel_id = $mapel_sql
                 WHERE id = '$id'";

    if (mysqli_query($conn, $q_update)) {

        $_SESSION['swal'] = [
            'title' => 'Berhasil',
            'text' => 'Data guru berhasil diubah',
            'type' => 'success'
        ];

    } else {

        $_SESSION['swal'] = [
            'title' => 'Gagal',
            'text' => 'Gagal mengupdate data guru',
            'type' => 'error'
        ];
    }

    header("Location: index.php");
    exit;
}
?>