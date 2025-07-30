<?php
session_start();
include 'koneksi.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$role = $_SESSION['role'];

$message = '';
$message_type = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_new_password = $_POST['confirm_new_password'];

    // Ambil password lama dari database
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        // Verifikasi password lama
        if (password_verify($current_password, $user['password'])) {
            // Pastikan password baru dan konfirmasi cocok
            if ($new_password === $confirm_new_password) {
                // Hash password baru
                $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);

                // Update password di database
                $stmt_update = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt_update->bind_param("si", $hashed_new_password, $user_id);

                if ($stmt_update->execute()) {
                    $message = "Password berhasil diubah!";
                    $message_type = "success";
                } else {
                    $message = "Gagal mengubah password. Silakan coba lagi.";
                    $message_type = "error";
                }
                $stmt_update->close();
            } else {
                $message = "Password baru dan konfirmasi password tidak cocok.";
                $message_type = "error";
            }
        } else {
            $message = "Password lama salah.";
            $message_type = "error";
        }
    } else {
        $message = "User tidak ditemukan."; // Seharusnya tidak terjadi jika sesi valid
        $message_type = "error";
    }
    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ganti Password - <?php echo ucfirst($role); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Roboto&amp;display=swap" rel="stylesheet"/>
    <style>
        body {
            font-family: 'Roboto', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-100 flex">
    <aside class="w-64 bg-yellow-700 text-white min-h-screen p-6">
        <div class="text-3xl font-bold mb-8 text-center"><?php echo ucfirst($role); ?> Padang</div>
        <nav>
            <ul>
                <li class="mb-4">
                    <?php if ($role == 'admin'): ?>
                        <a href="dashboard_admin.php" class="flex items-center text-white hover:bg-yellow-800 p-3 rounded-lg transition duration-200">
                            <i class="fas fa-chart-line mr-3"></i> Laporan
                        </a>
                    <?php elseif ($role == 'petugas'): ?>
                        <a href="dashboard_petugas.php" class="flex items-center text-white hover:bg-yellow-800 p-3 rounded-lg transition duration-200">
                            <i class="fas fa-cash-register mr-3"></i> Penjualan
                        </a>
                    <?php endif; ?>
                </li>
                <li class="mb-4">
                    <a href="change_password.php" class="flex items-center text-white bg-yellow-800 p-3 rounded-lg transition duration-200">
                        <i class="fas fa-key mr-3"></i> Ganti Password
                    </a>
                </li>
                <li class="mb-4">
                    <a href="logout.php" class="flex items-center text-white hover:bg-yellow-800 p-3 rounded-lg transition duration-200">
                        <i class="fas fa-sign-out-alt mr-3"></i> Logout
                    </a>
                </li>
            </ul>
        </nav>
    </aside>

    <main class="flex-1 p-8">
        <header class="flex justify-between items-center mb-8">
            <h1 class="text-4xl font-bold text-yellow-800">Ganti Password</h1>
            <div class="bg-yellow-600 text-white px-4 py-2 rounded-lg text-lg">
                <i class="fas fa-user-circle mr-2"></i> <?php echo ucfirst($role); ?>
            </div>
        </header>

        <section class="bg-white p-6 rounded-lg shadow-md max-w-md mx-auto">
            <?php if ($message): ?>
                <div class="mb-4 p-3 rounded-lg <?php echo $message_type === 'success' ? 'bg-green-100 text-green-800 border border-green-400' : 'bg-red-100 text-red-800 border border-red-400'; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <form action="change_password.php" method="POST">
                <div class="mb-4">
                    <label for="current_password" class="block text-gray-700 text-sm font-semibold mb-2">Password Lama:</label>
                    <input type="password" id="current_password" name="current_password" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500" required>
                </div>
                <div class="mb-4">
                    <label for="new_password" class="block text-gray-700 text-sm font-semibold mb-2">Password Baru:</label>
                    <input type="password" id="new_password" name="new_password" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500" required>
                </div>
                <div class="mb-6">
                    <label for="confirm_new_password" class="block text-gray-700 text-sm font-semibold mb-2">Konfirmasi Password Baru:</label>
                    <input type="password" id="confirm_new_password" name="confirm_new_password" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500" required>
                </div>
                <button type="submit" class="w-full bg-yellow-600 text-white px-4 py-2 rounded-lg font-semibold shadow-md hover:bg-yellow-700 transition">
                    Ganti Password
                </button>
            </form>
        </section>
    </main>
</body>
</html>