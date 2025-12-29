<?php
$servername = "localhost"; // Biasanya localhost untuk XAMPP
$username_db = "root";     // Username default XAMPP
$password_db = "";         // Password default XAMPP (biasanya kosong)
$dbname = "rm_padang_db";  // Nama database yang sudah kita buat

// Membuat koneksi
$conn = new mysqli($servername, $username_db, $password_db, $dbname);

// Mengecek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
// echo "Koneksi berhasil"; // Baris ini bisa dihapus atau dikomentari setelah dipastikan berhasil
?>