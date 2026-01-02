<?php
session_start();
include 'koneksi.php';

// 1. Proteksi Halaman: Cek apakah user sudah login dan perannya adalah admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$username_logged_in = $_SESSION['username'];
$message = '';
$message_type = '';

// --- LOGIKA TAMBAH PENGGUNA BARU ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_user'])) {
    $new_username = trim($_POST['new_username']);
    $new_password = $_POST['new_password'];
    $new_role = $_POST['new_role'];

    if (empty($new_username) || empty($new_password) || empty($new_role)) {
        $message = "Semua kolom harus diisi.";
        $message_type = "error";
    } else {
        // Hash password sebelum disimpan
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

        // Cek apakah username sudah ada
        $check_stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $check_stmt->bind_param("s", $new_username);
        $check_stmt->execute();
        $check_stmt->store_result();

        if ($check_stmt->num_rows > 0) {
            $message = "Username sudah terdaftar! Pilih username lain.";
            $message_type = "error";
        } else {
            // Masukkan data pengguna baru
            $insert_stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
            $insert_stmt->bind_param("sss", $new_username, $hashed_password, $new_role);
            
            if ($insert_stmt->execute()) {
                $message = "Pengguna berhasil ditambahkan!";
                $message_type = "success";
            } else {
                $message = "Gagal menambahkan: " . $insert_stmt->error;
                $message_type = "error";
            }
            $insert_stmt->close();
        }
        $check_stmt->close();
    }
}

// --- LOGIKA HAPUS PENGGUNA ---
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
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
        } else {
            $message = "Gagal menghapus: " . $conn->error;
            $message_type = "error";
        }
        $del->close();
    }
    // Opsional: Redirect untuk membersihkan URL
    // header("Location: manage_users.php?msg=".urlencode($message)."&type=".$message_type);
}

// --- AMBIL DATA PENGGUNA UNTUK TABEL ---
$result_users = $conn->query("SELECT id, username, role, reg_date FROM users ORDER BY role ASC, username ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen User - RM Padang</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet"/>
</head>
<body class="bg-gray-100 flex">

    <?php include 'sidebar.php'; // Pastikan file ini ada ?>

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
                                <td class="p-4 text-center">
                                    <div class="flex justify-center space-x-2">
                                        <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="text-blue-500 hover:text-blue-700 p-2" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php if($user['id'] != $_SESSION['user_id']): ?>
                                        <a href="manage_users.php?action=delete&id=<?php echo $user['id']; ?>" 
                                           onclick="return confirm('Hapus user ini?')" 
                                           class="text-red-400 hover:text-red-600 p-2" title="Hapus">
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
<?php $conn->close(); ?>