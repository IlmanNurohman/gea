<?php
include 'koneksi.php';

if (isset($_POST['register'])) {

    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role     = $_POST['role'] ?? '';
    $nisn_anak = trim($_POST['nisn_anak'] ?? '');

    // Validasi role
    $allowed_roles = [
        'siswa',
        'guru',
        'operator',
        'kepsek',
        'orang_tua'
    ];

    if (!in_array($role, $allowed_roles, true)) {
        echo "<script>
            swal('Error', 'Role tidak valid!', 'error');
        </script>";
        exit;
    }

    // Validasi password
    if (strlen($password) < 6) {
        echo "<script>
            swal('Gagal', 'Password minimal 6 karakter!', 'error');
        </script>";
        exit;
    }

    // Cek username
    $stmt = mysqli_prepare(
        $conn,
        "SELECT id FROM users WHERE username = ?"
    );

    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    if (mysqli_stmt_num_rows($stmt) > 0) {
        mysqli_stmt_close($stmt);

        echo "<script>
            swal('Gagal', 'Username sudah digunakan!', 'error');
        </script>";
        exit;
    }

    mysqli_stmt_close($stmt);

    /*
    |--------------------------------------------------------------------------
    | Kalau role orang_tua
    |--------------------------------------------------------------------------
    | Cari siswa berdasarkan NISN terlebih dahulu
    */

    $siswa_id = null;

    if ($role === 'orang_tua') {

        if ($nisn_anak === '') {
            echo "<script>
                swal('Gagal', 'NISN anak wajib diisi!', 'error');
            </script>";
            exit;
        }

        $stmt = mysqli_prepare(
            $conn,
            "SELECT id FROM siswa WHERE nisn = ? LIMIT 1"
        );

        mysqli_stmt_bind_param($stmt, "s", $nisn_anak);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);
        $siswa = mysqli_fetch_assoc($result);

        mysqli_stmt_close($stmt);

        if (!$siswa) {
            echo "<script>
                swal(
                    'Gagal',
                    'NISN anak tidak ditemukan!',
                    'error'
                );
            </script>";
            exit;
        }

        $siswa_id = (int) $siswa['id'];
    }

    // Hash password
    $password_hash = password_hash(
        $password,
        PASSWORD_DEFAULT
    );

    /*
    |--------------------------------------------------------------------------
    | Mulai transaction
    |--------------------------------------------------------------------------
    */

    mysqli_begin_transaction($conn);

    try {

        // Insert users
        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO users
                (username, email, password, role)
             VALUES
                (?, ?, ?, ?)"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "ssss",
            $username,
            $email,
            $password_hash,
            $role
        );

        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception(
                mysqli_stmt_error($stmt)
            );
        }

        $orang_tua_id = mysqli_insert_id($conn);

        mysqli_stmt_close($stmt);

        /*
        |--------------------------------------------------------------------------
        | Jika orang_tua, hubungkan dengan siswa
        |--------------------------------------------------------------------------
        */

        if ($role === 'orang_tua') {

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

            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception(
                    mysqli_stmt_error($stmt)
                );
            }

            mysqli_stmt_close($stmt);
        }

        // Commit
        mysqli_commit($conn);

        echo "
        <script>
            swal({
                title: 'Berhasil!',
                text: 'Registrasi berhasil.',
                icon: 'success'
            }).then(() => {
                window.location = 'register.php';
            });
        </script>
        ";

    } catch (Exception $e) {

        // Batalkan semua perubahan
        mysqli_rollback($conn);

        echo "
        <script>
            swal({
                title: 'Error!',
                text: 'Registrasi gagal: " . addslashes($e->getMessage()) . "',
                icon: 'error'
            });
        </script>
        ";
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Register</title>

    <link rel="stylesheet" href="assets/css/bootstrap.min.css">

</head>

<body class="bg-light">

    <div class="container mt-5">

        <div class="row justify-content-center">

            <div class="col-md-5">

                <div class="card shadow">

                    <div class="card-header text-center">

                        <h4 class="mb-0">
                            Register Akun
                        </h4>

                    </div>

                    <div class="card-body">

                        <form method="POST">

                            <!-- Username -->
                            <div class="mb-3">

                                <label class="form-label">
                                    Username
                                </label>

                                <input type="text" name="username" class="form-control" required>

                            </div>

                            <!-- Email -->
                            <div class="mb-3">

                                <label class="form-label">
                                    Email
                                </label>

                                <input type="email" name="email" class="form-control" required>

                            </div>

                            <!-- Password -->
                            <div class="mb-3">

                                <label class="form-label">
                                    Password
                                </label>

                                <input type="password" name="password" class="form-control" minlength="6" required>

                                <small class="text-muted">
                                    Minimal 6 karakter.
                                </small>

                            </div>

                            <!-- Role -->
                            <div class="mb-3">

                                <label class="form-label">
                                    Role
                                </label>

                                <select name="role" id="role" class="form-select" required>

                                    <option value="">
                                        -- Pilih Role --
                                    </option>

                                    <option value="siswa">
                                        Siswa
                                    </option>

                                    <option value="operator">
                                        Operator
                                    </option>

                                    <option value="guru">
                                        Guru
                                    </option>

                                    <option value="kepsek">
                                        Kepsek
                                    </option>

                                    <option value="orang_tua">
                                        Orang Tua
                                    </option>

                                </select>

                            </div>

                            <!-- NISN Anak -->
                            <div class="mb-3" id="nisnContainer" style="display: none;">

                                <label class="form-label">
                                    NISN Anak
                                </label>

                                <input type="text" name="nisn_anak" id="nisn_anak" class="form-control"
                                    placeholder="Masukkan NISN anak">

                                <small class="text-muted">
                                    Masukkan NISN anak yang akan
                                    dihubungkan dengan akun orang tua.
                                </small>

                            </div>

                            <button type="submit" name="register" class="btn btn-primary w-100">
                                Daftar
                            </button>

                        </form>

                    </div>

                </div>

            </div>

        </div>

    </div>

    <script src="assets/js/plugin/sweetalert/sweetalert.min.js"></script>

    <script>
    const role = document.getElementById('role');
    const nisnContainer =
        document.getElementById('nisnContainer');
    const nisnInput =
        document.getElementById('nisn_anak');

    role.addEventListener('change', function() {

        if (this.value === 'orang_tua') {

            // Tampilkan NISN
            nisnContainer.style.display = 'block';

            // Wajib diisi
            nisnInput.required = true;

        } else {

            // Sembunyikan NISN
            nisnContainer.style.display = 'none';

            // Tidak wajib
            nisnInput.required = false;

            // Kosongkan
            nisnInput.value = '';
        }

    });
    </script>

</body>

</html>