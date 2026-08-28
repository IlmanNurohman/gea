<?php
include 'koneksi.php';

if (isset($_POST['register'])) {

    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $role     = $_POST['role'];

    // Validasi role
    $allowed_roles = ['siswa', 'guru', 'operator', 'kepsek', 'orang_tua'];

    if (!in_array($role, $allowed_roles)) {
        echo "<script>swal('Error','Role tidak valid!','error');</script>";
        exit;
    }

    // Cek username
    $cek = mysqli_query(
        $conn,
        "SELECT id FROM users WHERE username='$username'"
    );

    if (mysqli_num_rows($cek) > 0) {
        echo "<script>swal('Gagal','Username sudah digunakan!','error');</script>";
        exit;
    }

    // Hash password
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Insert database
    $insert = mysqli_query(
        $conn,
        "
        INSERT INTO users (username, email, password, role)
        VALUES ('$username', '$email', '$password_hash', '$role')
        "
    );

    if ($insert) {

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

    } else {

        echo "
        <script>
            swal('Error', 'Registrasi gagal!', 'error');
        </script>
        ";

    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Register</title>

    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
</head>

<body class="bg-light">

    <div class="container mt-5">

        <div class="row justify-content-center">

            <div class="col-md-5">

                <div class="card shadow">

                    <div class="card-header text-center">
                        <h4>Register Akun</h4>
                    </div>

                    <div class="card-body">

                        <form method="POST">

                            <div class="mb-3">
                                <label>Username</label>

                                <input type="text" name="username" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label>Email</label>

                                <input type="email" name="email" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label>Password</label>

                                <input type="password" name="password" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label>Role</label>

                                <select name="role" class="form-select" required>
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

</body>

</html>