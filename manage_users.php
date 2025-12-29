<?php
session_start();
include 'koneksi.php';

// Cek apakah user sudah login dan perannya adalah admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
$message = '';
$message_type = '';

// --- Logika Tambah Pengguna Baru ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_user'])) {
    $new_username = trim($_POST['new_username']);
    $new_password = $_POST['new_password'];
    $new_role = $_POST['new_role'];

<<<<<<< HEAD
=======
    // Validasi input
>>>>>>> 3d33e59de29485dbc5c0a3fa748595769751f536
    if (empty($new_username) || empty($new_password) || empty($new_role)) {
        $message = "Semua kolom harus diisi.";
        $message_type = "error";
    } else {
<<<<<<< HEAD
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
        $check_stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
=======
        // Hash password sebelum disimpan
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

        // Cek apakah username sudah ada
        $check_stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        if ($check_stmt === false) {
            die("Error preparing check user statement: " . $conn->error);
        }
>>>>>>> 3d33e59de29485dbc5c0a3fa748595769751f536
        $check_stmt->bind_param("s", $new_username);
        $check_stmt->execute();
        $check_stmt->store_result();

        if ($check_stmt->num_rows > 0) {
<<<<<<< HEAD
            $message = "Username sudah terdaftar!";
            $message_type = "error";
        } else {
            $insert_stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
            $insert_stmt->bind_param("sss", $new_username, $hashed_password, $new_role);
            if ($insert_stmt->execute()) {
                $message = "Pengguna berhasil ditambahkan!";
                $message_type = "success";
            } else {
                $message = "Gagal menambahkan: " . $insert_stmt->error;
=======
            $message = "Username sudah ada. Pilih username lain.";
            $message_type = "error";
        } else {
            // Masukkan data pengguna baru
            $insert_stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
            if ($insert_stmt === false) {
                die("Error preparing insert user statement: " . $conn->error);
            }
            $insert_stmt->bind_param("sss", $new_username, $hashed_password, $new_role);

            if ($insert_stmt->execute()) {
                $message = "Pengguna baru berhasil ditambahkan!";
                $message_type = "success";
            } else {
                $message = "Gagal menambahkan pengguna: " . $insert_stmt->error;
>>>>>>> 3d33e59de29485dbc5c0a3fa748595769751f536
                $message_type = "error";
            }
            $insert_stmt->close();
        }
        $check_stmt->close();
    }
}

// --- Logika Hapus Pengguna ---
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
<<<<<<< HEAD
    $id_to_delete = (int)$_GET['id'];
    if ($id_to_delete == $_SESSION['user_id']) {
        $message = "Anda tidak bisa menghapus diri sendiri!";
        $message_type = "error";
    } else {
        $del = $conn->prepare("DELETE FROM users WHERE id = ?");
        $del->bind_param("i", $id_to_delete);
        if ($del->execute()) {
            $message = "Pengguna berhasil dihapus!";
            $message_type = "success";
        }
        $del->close();
    }
}

// Ambil Data Pengguna
$result_users = $conn->query("SELECT id, username, role, reg_date FROM users ORDER BY role ASC, username ASC");
=======
    $user_id_to_delete = (int)$_GET['id'];

    // Pastikan admin tidak bisa menghapus akunnya sendiri
    if ($user_id_to_delete == $_SESSION['user_id']) {
        $message = "Anda tidak bisa menghapus akun Anda sendiri!";
        $message_type = "error";
    } else {
        $delete_stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        if ($delete_stmt === false) {
            die("Error preparing delete user statement: " . $conn->error);
        }
        $delete_stmt->bind_param("i", $user_id_to_delete);

        if ($delete_stmt->execute()) {
            $message = "Pengguna berhasil dihapus!";
            $message_type = "success";
        } else {
            $message = "Gagal menghapus pengguna: " . $delete_stmt->error;
            $message_type = "error";
        }
        $delete_stmt->close();
    }
    // Refresh halaman untuk membersihkan parameter GET setelah aksi
    header("Location: manage_users.php?message=" . urlencode($message) . "&type=" . urlencode($message_type));
    exit();
}

// Ambil pesan dari redirect setelah delete/edit
if (isset($_GET['message']) && isset($_GET['type'])) {
    $message = htmlspecialchars($_GET['message']);
    $message_type = htmlspecialchars($_GET['type']);
}

// --- Ambil Data Pengguna untuk Ditampilkan ---
$sql_users = "SELECT id, username, role, reg_date FROM users ORDER BY username ASC";
$result_users = $conn->query($sql_users);

// --- DEBUGGING: Pengecekan Error Query ---
if ($result_users === false) {
    die("Error mengambil data pengguna: " . $conn->error);
}
// --- AKHIR DEBUGGING ---

// Tutup koneksi database di akhir file setelah semua query selesai
// CATATAN: $conn->close() TIDAK ditempatkan di sini agar $result_users->num_rows bisa diakses
>>>>>>> 3d33e59de29485dbc5c0a3fa748595769751f536
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<<<<<<< HEAD
    <title>Manajemen User - RM Padang</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet"/>
</head>
<body class="bg-gray-100 flex">

    <?php include 'sidebar.php'; ?>

    <main class="flex-1 p-8">
        <header class="mb-8 bg-white p-6 rounded-2xl shadow-sm flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-800">Manajemen Pengguna</h1>
            <span class="text-gray-500 font-medium">Total: <?php echo $result_users->num_rows; ?> User</span>
        </header>

        <?php if ($message): ?>
        <div class="mb-6 flex items-center p-4 rounded-xl <?php echo $message_type === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
            <i class="fas <?php echo $message_type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?> mr-3 text-xl"></i>
            <span class="font-medium"><?php echo $message; ?></span>
        </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-1">
                <section class="bg-white p-6 rounded-2xl shadow-sm sticky top-8">
                    <h2 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">Tambah User Baru</h2>
                    <form action="" method="POST" class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-600 mb-1">Username</label>
                            <input type="text" name="new_username" class="w-full p-2.5 border rounded-lg focus:ring-2 focus:ring-yellow-500 outline-none" placeholder="Masukkan username" required>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-600 mb-1">Password</label>
                            <input type="password" name="new_password" class="w-full p-2.5 border rounded-lg focus:ring-2 focus:ring-yellow-500 outline-none" placeholder="••••••••" required>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-600 mb-1">Role</label>
                            <select name="new_role" class="w-full p-2.5 border rounded-lg focus:ring-2 focus:ring-yellow-500 outline-none" required>
                                <option value="petugas">Petugas (Kasir)</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        <button type="submit" name="add_user" class="w-full bg-yellow-600 hover:bg-yellow-700 text-white font-bold py-3 rounded-lg transition shadow-lg shadow-yellow-200">
                            Simpan User
                        </button>
                    </form>
                </section>
            </div>

            <div class="lg:col-span-2">
                <section class="bg-white rounded-2xl shadow-sm overflow-hidden">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 border-b">
                                <th class="p-4 text-xs uppercase text-gray-400 font-bold">Username</th>
                                <th class="p-4 text-xs uppercase text-gray-400 font-bold">Role</th>
                                <th class="p-4 text-xs uppercase text-gray-400 font-bold">Terdaftar</th>
                                <th class="p-4 text-xs uppercase text-gray-400 font-bold text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php while($user = $result_users->fetch_assoc()): ?>
                            <tr class="hover:bg-gray-50 transition">
                                <td class="p-4 font-semibold text-gray-800">
                                    <div class="flex items-center">
                                        <div class="h-8 w-8 rounded-full bg-yellow-100 text-yellow-700 flex items-center justify-center mr-3">
                                            <i class="fas fa-user text-xs"></i>
                                        </div>
                                        <?php echo htmlspecialchars($user['username']); ?>
                                    </div>
                                </td>
                                <td class="p-4">
                                    <span class="px-3 py-1 rounded-full text-xs font-bold <?php echo $user['role'] == 'admin' ? 'bg-purple-100 text-purple-600' : 'bg-blue-100 text-blue-600'; ?>">
                                        <?php echo strtoupper($user['role']); ?>
                                    </span>
                                </td>
                                <td class="p-4 text-sm text-gray-500">
                                    <?php echo date('d/m/y', strtotime($user['reg_date'] ?? 'now')); ?>
                                </td>
                                <td class="p-4 text-center">
                                    <div class="flex justify-center space-x-2">
                                        <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="text-blue-500 hover:text-blue-700 p-2">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php if($user['id'] != $_SESSION['user_id']): ?>
                                        <a href="manage_users.php?action=delete&id=<?php echo $user['id']; ?>" 
                                           onclick="return confirm('Hapus user ini?')" 
                                           class="text-red-400 hover:text-red-600 p-2">
                                            <i class="fas fa-trash-alt"></i>
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </section>
            </div>
        </div>
    </main>
</body>
</html>
=======
    <title>Manajemen Pengguna - Admin Padang</title>
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
        <div class="text-3xl font-bold mb-8 text-center">Admin Padang</div>
        <nav>
            <ul>
                <li class="mb-4">
                    <a href="dashboard_admin.php" class="flex items-center text-white hover:bg-yellow-800 p-3 rounded-lg transition duration-200">
                        <i class="fas fa-chart-line mr-3"></i> Laporan
                    </a>
                </li>
                <li class="mb-4">
                    <a href="manage_users.php" class="flex items-center text-white bg-yellow-800 p-3 rounded-lg transition duration-200">
                        <i class="fas fa-users mr-3"></i> Manajemen Pengguna
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

    <main class="flex-1 p-8">
        <header class="flex justify-between items-center mb-8">
            <h1 class="text-4xl font-bold text-yellow-800">Manajemen Pengguna</h1>
            <div class="bg-yellow-600 text-white px-4 py-2 rounded-lg text-lg">
                <i class="fas fa-user-shield mr-2"></i> Admin
            </div>
        </header>

        <?php if ($message): ?>
            <div class="mb-6 p-4 rounded-lg <?php echo $message_type === 'success' ? 'bg-green-100 text-green-800 border border-green-400' : 'bg-red-100 text-red-800 border border-red-400'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <section class="bg-white p-6 rounded-lg shadow-md mb-8">
            <h2 class="text-2xl font-bold text-yellow-800 mb-6">Tambah Pengguna Baru</h2>
            <form action="manage_users.php" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="new_username" class="block text-gray-700 text-sm font-semibold mb-2">Username:</label>
                    <input type="text" id="new_username" name="new_username" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500" required>
                </div>
                <div>
                    <label for="new_password" class="block text-gray-700 text-sm font-semibold mb-2">Password:</label>
                    <input type="password" id="new_password" name="new_password" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500" required>
                </div>
                <div>
                    <label for="new_role" class="block text-gray-700 text-sm font-semibold mb-2">Role:</label>
                    <select id="new_role" name="new_role" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500" required>
                        <option value="">Pilih Role</option>
                        <option value="admin">Admin</option>
                        <option value="petugas">Petugas</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <button type="submit" name="add_user" class="w-full bg-yellow-600 text-white px-4 py-2 rounded-lg font-semibold shadow-md hover:bg-yellow-700 transition">
                        <i class="fas fa-user-plus mr-2"></i> Tambah Pengguna
                    </button>
                </div>
            </form>
        </section>

        <section class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-2xl font-bold text-yellow-800 mb-6">Daftar Pengguna</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white border border-gray-200">
                    <thead>
                        <tr>
                            <th class="py-3 px-4 border-b text-left text-gray-600 font-semibold">ID</th>
                            <th class="py-3 px-4 border-b text-left text-gray-600 font-semibold">Username</th>
                            <th class="py-3 px-4 border-b text-left text-gray-600 font-semibold">Role</th>
                            <th class="py-3 px-4 border-b text-left text-gray-600 font-semibold">Tanggal Daftar</th>
                            <th class="py-3 px-4 border-b text-left text-gray-600 font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result_users && $result_users->num_rows > 0): // Cek $result_users sebelum num_rows ?>
                            <?php while($user = $result_users->fetch_assoc()): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="py-3 px-4 border-b text-gray-800"><?php echo htmlspecialchars($user['id']); ?></td>
                                    <td class="py-3 px-4 border-b text-gray-800"><?php echo htmlspecialchars($user['username']); ?></td>
                                    <td class="py-3 px-4 border-b text-gray-800"><?php echo htmlspecialchars(ucfirst($user['role'])); ?></td>
                                    <td class="py-3 px-4 border-b text-gray-800"><?php echo date('d M Y H:i', strtotime($user['reg_date'])); ?></td>
                                    <td class="py-3 px-4 border-b text-gray-800">
                                        <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="text-blue-600 hover:underline mr-2">Edit</a>
                                        <a href="manage_users.php?action=delete&id=<?php echo $user['id']; ?>" class="text-red-600 hover:underline" onclick="return confirm('Apakah Anda yakin ingin menghapus pengguna ini? Ini akan menghapus semua transaksi yang terkait!');">Hapus</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="py-4 text-center text-gray-600">Tidak ada pengguna ditemukan.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
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
>>>>>>> 3d33e59de29485dbc5c0a3fa748595769751f536
