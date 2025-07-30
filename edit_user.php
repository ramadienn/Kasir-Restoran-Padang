<?php
session_start();
include 'koneksi.php';

// Cek apakah user sudah login dan memiliki role admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$message = '';
$message_type = ''; // success or error

// Ambil ID pengguna yang akan diedit dari URL
if (isset($_GET['id'])) {
    $edit_user_id = $_GET['id'];

    // Ambil data pengguna yang akan diedit
    $stmt = $conn->prepare("SELECT id, username, role FROM users WHERE id = ?");
    if ($stmt === false) {
        die("Error preparing select statement: " . $conn->error);
    }
    $stmt->bind_param("i", $edit_user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user_to_edit = $result->fetch_assoc();
    $stmt->close();

    if (!$user_to_edit) {
        // Jika user tidak ditemukan, redirect kembali
        header("Location: manage_users.php");
        exit();
    }
} else {
    // Jika tidak ada ID di URL, redirect kembali
    header("Location: manage_users.php");
    exit();
}

// Proses update data jika form disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_username = $_POST['username'];
    $new_role = $_POST['role'];
    $new_password = $_POST['password']; // Password bisa kosong jika tidak ingin diubah

    // Validasi input
    if (empty($new_username) || empty($new_role)) {
        $message = "Username dan Role harus diisi.";
        $message_type = "error";
    } elseif (!in_array($new_role, ['admin', 'petugas'])) { // Pastikan role valid
        $message = "Role tidak valid.";
        $message_type = "error";
    } else {
        // Cek apakah username sudah ada (kecuali untuk user yang sedang diedit)
        $check_stmt = $conn->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
        if ($check_stmt === false) {
            die("Error preparing check username statement: " . $conn->error);
        }
        $check_stmt->bind_param("si", $new_username, $edit_user_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        if ($check_result->num_rows > 0) {
            $message = "Username sudah digunakan oleh pengguna lain.";
            $message_type = "error";
            $check_stmt->close();
        } else {
            $check_stmt->close();

            // Bangun query update
            $query_parts = [];
            $bind_types = "";
            $bind_params = [];

            $query_parts[] = "username = ?";
            $bind_types .= "s";
            $bind_params[] = $new_username;

            $query_parts[] = "role = ?";
            $bind_types .= "s";
            $bind_params[] = $new_role;

            if (!empty($new_password)) {
                if (strlen($new_password) < 6) {
                    $message = "Password baru minimal 6 karakter.";
                    $message_type = "error";
                } else {
                    $hashed_new_password = password_hash($new_password, PASSWORD_BCRYPT);
                    $query_parts[] = "password = ?";
                    $bind_types .= "s";
                    $bind_params[] = $hashed_new_password;
                }
            }

            if ($message_type !== "error") { // Lanjutkan jika tidak ada error validasi password
                $sql = "UPDATE users SET " . implode(", ", $query_parts) . " WHERE id = ?";
                $bind_types .= "i";
                $bind_params[] = $edit_user_id;

                $update_stmt = $conn->prepare($sql);
                if ($update_stmt === false) {
                    die("Error preparing update statement: " . $conn->error);
                }

                // Menggunakan call_user_func_array untuk bind_param karena jumlah parameter dinamis
                // Membuat array references untuk bind_param
                $refs = [];
                foreach ($bind_params as $key => $value) {
                    $refs[$key] = &$bind_params[$key];
                }
                // Menggabungkan tipe dan parameter
                array_unshift($refs, $bind_types); // Masukkan $bind_types sebagai elemen pertama

                // Menggunakan call_user_func_array dengan array references
                call_user_func_array([$update_stmt, 'bind_param'], $refs);

                if ($update_stmt->execute()) {
                    $message = "Data pengguna berhasil diperbarui!";
                    $message_type = "success";
                    // Redirect ke manage_users.php setelah berhasil diupdate
                    $_SESSION['success_message'] = $message; // Simpan pesan di session
                    header("Location: manage_users.php");
                    exit();
                } else {
                    $message = "Gagal memperbarui data pengguna: " . $update_stmt->error;
                    $message_type = "error";
                }
                $update_stmt->close();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Pengguna - Rumah Makan Padang</title>
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
    <!-- Sidebar -->
    <aside class="w-64 bg-yellow-700 text-white min-h-screen p-6">
        <div class="text-3xl font-bold mb-8 text-center">Admin Padang</div>
        <nav>
            <ul>
                <li class="mb-4">
                    <a href="dashboard_admin.php" class="flex items-center text-white hover:bg-yellow-800 p-3 rounded-lg transition duration-200">
                        <i class="fas fa-chart-line mr-3"></i> Laporan
                    </a>
                </li>
                <li class="mb-4">
                    <a href="manage_users.php" class="flex items-center text-white hover:bg-yellow-800 p-3 rounded-lg transition duration-200">
                        <i class="fas fa-users mr-3"></i> Manajemen Pengguna
                    </a>
                </li>
                <li class="mb-4">
                    <a href="manage_menus.php" class="flex items-center text-white hover:bg-yellow-800 p-3 rounded-lg transition duration-200">
                        <i class="fas fa-utensils mr-3"></i> Manajemen Menu
                    </a>
                </li>
                <li class="mb-4">
                    <a href="change_password.php" class="flex items-center text-white hover:bg-yellow-800 p-3 rounded-lg transition duration-200">
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

    <!-- Main Content -->
    <main class="flex-1 p-8">
        <header class="flex justify-between items-center mb-8">
            <h1 class="text-4xl font-bold text-yellow-800">Edit Pengguna</h1>
            <div class="bg-yellow-600 text-white px-4 py-2 rounded-lg text-lg">
                <i class="fas fa-user-circle mr-2"></i> Admin
            </div>
        </header>

        <section class="bg-white p-6 rounded-lg shadow-md max-w-lg mx-auto">
            <h2 class="text-2xl font-bold text-yellow-800 mb-6 text-center">Form Edit Pengguna</h2>

            <?php if ($message): ?>
                <div class="mb-6 p-4 rounded-lg <?php echo $message_type === 'success' ? 'bg-green-100 text-green-800 border border-green-400' : 'bg-red-100 text-red-800 border border-red-400'; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <form action="edit_user.php?id=<?php echo $user_to_edit['id']; ?>" method="POST">
                <div class="mb-4">
                    <label for="username" class="block text-gray-700 text-sm font-semibold mb-2">Username:</label>
                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user_to_edit['username']); ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500" required>
                </div>
                <div class="mb-4">
                    <label for="role" class="block text-gray-700 text-sm font-semibold mb-2">Role:</label>
                    <select id="role" name="role" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500" required>
                        <option value="admin" <?php echo ($user_to_edit['role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                        <option value="petugas" <?php echo ($user_to_edit['role'] == 'petugas') ? 'selected' : ''; ?>>Petugas</option>
                    </select>
                </div>
                <div class="mb-6">
                    <label for="password" class="block text-gray-700 text-sm font-semibold mb-2">Password (kosongkan jika tidak ingin diubah):</label>
                    <input type="password" id="password" name="password" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500">
                    <p class="text-sm text-gray-500 mt-1">Isi hanya jika Anda ingin mengganti password pengguna ini.</p>
                </div>
                <div class="flex justify-between">
                    <button type="submit" class="bg-yellow-600 text-white px-6 py-2 rounded-lg font-semibold shadow-md hover:bg-yellow-700 transition">
                        <i class="fas fa-save mr-2"></i> Simpan Perubahan
                    </button>
                    <a href="manage_users.php" class="bg-gray-400 text-white px-6 py-2 rounded-lg font-semibold shadow-md hover:bg-gray-500 transition">
                        <i class="fas fa-arrow-left mr-2"></i> Kembali
                    </a>
                </div>
            </form>
        </section>
    </main>
</body>
</html>
<?php
// Tutup koneksi database di paling akhir file setelah semua HTML selesai
if (isset($conn)) {
    $conn->close();
}
?>