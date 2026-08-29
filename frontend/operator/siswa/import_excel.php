<?php
session_start();
include '../../../backend/koneksi.php';
require '../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

if (isset($_POST['import'])) {
    $kelas_id_input = $_POST['kelas_id'] ?? '';
    $kelas_id = !empty($kelas_id_input) ? "'".mysqli_real_escape_string($conn, $kelas_id_input)."'" : "NULL";

    $file_mimes = [
        'application/octet-stream', 
        'application/vnd.ms-excel', 
        'application/x-csv', 
        'text/x-csv', 
        'text/csv', 
        'application/csv', 
        'application/excel', 
        'application/vnd.msexcel', 
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
    ];

    if (isset($_FILES['file_excel']['name']) && in_array($_FILES['file_excel']['type'], $file_mimes)) {
        $arr_file = explode('.', $_FILES['file_excel']['name']);
        $extension = end($arr_file);

        $reader = ($extension == 'csv') ? IOFactory::createReader('Csv') : IOFactory::createReader('Xlsx');
        $spreadsheet = $reader->load($_FILES['file_excel']['tmp_name']);
        $sheetData = $spreadsheet->getActiveSheet()->toArray();

        $success_count = 0;
        $updated_count = 0;

        for ($i = 1; $i < count($sheetData); $i++) {
            $nisn          = trim($sheetData[$i][0] ?? '');
            $nama_lengkap  = trim($sheetData[$i][1] ?? '');
            $jenis_kelamin = trim($sheetData[$i][2] ?? '');
            $tempat_lahir  = trim($sheetData[$i][3] ?? '');
            $raw_tgl       = trim($sheetData[$i][4] ?? '');

            if (!empty($nisn) && !empty($nama_lengkap)) {
                $nisn_clean = mysqli_real_escape_string($conn, $nisn);
                $nama_clean = mysqli_real_escape_string($conn, $nama_lengkap);
                $jk_clean   = mysqli_real_escape_string($conn, $jenis_kelamin);
                $tl_clean   = mysqli_real_escape_string($conn, $tempat_lahir);

                // Handling Kolom Tanggal Lahir Kosong atau Validasi Format YYYY-MM-DD
                if (!empty($raw_tgl)) {
                    $time = strtotime($raw_tgl);
                    if ($time) {
                        $tgl_clean = "'".date('Y-m-d', $time)."'";
                    } else {
                        $tgl_clean = "NULL";
                    }
                } else {
                    $tgl_clean = "NULL";
                }

                // Cek apakah NISN sudah terdaftar
                $cek_user = mysqli_query($conn, "SELECT id FROM users WHERE username = '$nisn_clean'");

                if (mysqli_num_rows($cek_user) > 0) {
                    // Update jika NISN sudah ada
                    $row_user = mysqli_fetch_assoc($cek_user);
                    $user_id  = $row_user['id'];

                    $q_update_siswa = "UPDATE siswa SET 
                                        nama_lengkap = '$nama_clean',
                                        jenis_kelamin = '$jk_clean',
                                        tempat_lahir = '$tl_clean',
                                        tanggal_lahir = $tgl_clean,
                                        kelas_id = $kelas_id 
                                       WHERE user_id = '$user_id' OR nisn = '$nisn_clean'";
                    
                    if (mysqli_query($conn, $q_update_siswa)) {
                        $updated_count++;
                    }
                } else {
                    // Insert baru jika NISN belum ada
                    $password = password_hash($nisn, PASSWORD_DEFAULT);
                    $q_user   = "INSERT INTO users (username, password, role) VALUES ('$nisn_clean', '$password', 'siswa')";

                    if (mysqli_query($conn, $q_user)) {
                        $user_id = mysqli_insert_id($conn);

                        $q_siswa = "INSERT INTO siswa (user_id, nama_lengkap, jenis_kelamin, tempat_lahir, tanggal_lahir, nisn, kelas_id) 
                                    VALUES ('$user_id', '$nama_clean', '$jk_clean', '$tl_clean', $tgl_clean, '$nisn_clean', $kelas_id)";
                        
                        if (mysqli_query($conn, $q_siswa)) {
                            $success_count++;
                        }
                    }
                }
            }
        }

        $_SESSION['swal'] = [
            'title' => 'Selesai', 
            'text'  => "$success_count Data baru ditambahkan, $updated_count Data diperbarui.", 
            'type'  => 'success'
        ];
    } else {
        $_SESSION['swal'] = [
            'title' => 'Gagal', 
            'text'  => 'Format file tidak valid, harap upload file Excel (.xlsx / .xls)', 
            'type'  => 'error'
        ];
    }
}

header("Location: index.php");
exit;
?>