<?php
session_start();
include '../../../backend/koneksi.php';
require '../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

if (isset($_POST['import'])) {
    $kelas_id_input = $_POST['kelas_id'] ?? '';
    
    // Validasi kelas_id dari dropdown modal
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

        // Loop data Excel (Dimulai dari baris ke-2 / indeks 1 untuk melewati header)
        for ($i = 1; $i < count($sheetData); $i++) {
            $nisn          = trim($sheetData[$i][0] ?? '');
            $nama_lengkap  = trim($sheetData[$i][1] ?? '');
            $jenis_kelamin = trim($sheetData[$i][2] ?? '');
            $tempat_lahir  = trim($sheetData[$i][3] ?? '');
            $tanggal_lahir = trim($sheetData[$i][4] ?? '');

            if (!empty($nisn) && !empty($nama_lengkap)) {
                $password = password_hash($nisn, PASSWORD_DEFAULT);

                // 1. Insert ke tabel Users
                $q_user = "INSERT INTO users (username, password, role) VALUES ('$nisn', '$password', 'siswa')";
                if (mysqli_query($conn, $q_user)) {
                    $user_id = mysqli_insert_id($conn);

                    // 2. Insert ke tabel Siswa menggunakan kelas_id dari pilihan Modal
                    $q_siswa = "INSERT INTO siswa (user_id, nama_lengkap, jenis_kelamin, tempat_lahir, tanggal_lahir, nisn, kelas_id) 
                                VALUES ('$user_id', '$nama_lengkap', '$jenis_kelamin', '$tempat_lahir', '$tanggal_lahir', '$nisn', $kelas_id)";
                    
                    if (mysqli_query($conn, $q_siswa)) {
                        $success_count++;
                    }
                }
            }
        }

        $_SESSION['swal'] = [
            'title' => 'Berhasil', 
            'text' => "$success_count data siswa berhasil diimport ke kelas yang dipilih", 
            'type' => 'success'
        ];
    } else {
        $_SESSION['swal'] = [
            'title' => 'Gagal', 
            'text' => 'Format file tidak valid, harap upload file .xlsx / .xls', 
            'type' => 'error'
        ];
    }
}

header("Location: index.php");
exit;
?>