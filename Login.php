<?php
session_start();
// Jika user sudah login, arahkan ke dashboard yang sesuai
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'admin') {
        header("Location: dashboard_admin.php");
        exit();
    } elseif ($_SESSION['role'] == 'petugas') {
        header("Location: dashboard_petugas.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html class="scroll-smooth" lang="id">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1" name="viewport"/>
    <title>
        Rumah Makan Padang - Login
    </title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Roboto&amp;display=swap" rel="stylesheet"/>
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            /* Hapus atau ganti bg-orange-500 dari class body di HTML */
            /* Tambahkan properti background untuk wallpaper */
            background-image: url('assets/img/Kota Padang.jpg'); /* Ganti dengan path ke gambar Anda */
            background-size: cover; /* Membuat gambar menutupi seluruh area body */
            background-position: center; /* Posisikan gambar di tengah */
            background-repeat: no-repeat; /* Mencegah gambar berulang */
            background-attachment: fixed; /* Membuat gambar tetap di tempat saat discroll (opsional) */
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center"> <div class="bg-white p-8 rounded-lg shadow-lg max-w-md w-full">
        <h2 class="text-3xl font-bold text-yellow-800 mb-6 text-center">Login</h2>
        <?php
        // Tampilkan pesan error jika ada
        if (isset($_SESSION['login_error'])) {
            echo '<p class="text-red-600 text-center mb-4">' . $_SESSION['login_error'] . '</p>';
            unset($_SESSION['login_error']); // Hapus pesan error setelah ditampilkan
        }
        ?>
        <form action="login_process.php" method="POST">
            <div class="mb-4">
                <label for="username" class="block text-yellow-700 text-sm font-semibold mb-2">Username:</label>
                <input type="text" id="username" name="username" class="w-full px-4 py-2 border border-yellow-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500" required>
            </div>
            <div class="mb-6">
                <label for="password" class="block text-yellow-700 text-sm font-semibold mb-2">Password:</label>
                <input type="password" id="password" name="password" class="w-full px-4 py-2 border border-yellow-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500" required>
            </div>
            <button type="submit" class="w-full bg-yellow-600 text-white px-4 py-2 rounded-lg font-semibold shadow-md hover:bg-yellow-700 transition">Login</button>
        </form>

        <div class="mt-6 text-center"> <p class="text-gray-600">
                <a href="index.php" class="text-yellow-700 hover:underline">Kembali ke Beranda</a>
            </p>
        </div>
    </div>
</body>
</html>