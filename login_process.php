<?php
session_start(); // Mulai sesi

include 'koneksi.php'; // Hubungkan ke database

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Siapkan statement SQL: sekarang hanya berdasarkan username
    $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        // Verifikasi password yang di-hash
        if (password_verify($password, $row['password'])) {
            // Password benar, buat sesi
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role']; // Ambil peran langsung dari database

            // Arahkan ke dashboard yang sesuai berdasarkan peran yang diambil dari DB
            if ($row['role'] == 'admin') {
                header("Location: dashboard_admin.php");
            } else if ($row['role'] == 'petugas') {
                header("Location: dashboard_petugas.php");
            }
            exit();
        } else {
            // Password salah
            $_SESSION['login_error'] = "Username atau password salah.";
            header("Location: login.php");
            exit();
        }
    } else {
        // Username tidak ditemukan
        $_SESSION['login_error'] = "Username atau password salah.";
        header("Location: login.php");
        exit();
    }
} else {
    // Jika diakses langsung tanpa POST, arahkan kembali ke login
    header("Location: login.php");
    exit();
}
$stmt->close();
$conn->close();
?>