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

// --- Logika Tambah Menu Baru ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_menu'])) {
    $nama_menu = $_POST['nama_menu'];
    $deskripsi = $_POST['deskripsi'];
    $harga = $_POST['harga'];
    $gambar_url = $_POST['gambar_url']; // Untuk sementara, ini input teks

    // Validasi input
    if (empty($nama_menu) || empty($harga)) {
        $message = "Nama Menu dan Harga harus diisi.";
        $message_type = "error";
    } elseif (!is_numeric($harga) || $harga < 0) {
        $message = "Harga harus berupa angka positif.";
        $message_type = "error";
    } else {
        // Cek apakah nama menu sudah ada
        $check_stmt = $conn->prepare("SELECT id FROM menus WHERE nama_menu = ?");
        if ($check_stmt === false) {
            die("Error preparing check menu statement: " . $conn->error);
        }
        $check_stmt->bind_param("s", $nama_menu);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        if ($check_result->num_rows > 0) {
            $message = "Nama Menu sudah ada. Gunakan nama yang berbeda.";
            $message_type = "error";
        } else {
            // Masukkan menu baru ke database
            $insert_stmt = $conn->prepare("INSERT INTO menus (nama_menu, deskripsi, harga, gambar_url) VALUES (?, ?, ?, ?)");
            if ($insert_stmt === false) {
                die("Error preparing insert statement: " . $conn->error);
            }
            $insert_stmt->bind_param("ssds", $nama_menu, $deskripsi, $harga, $gambar_url); // 'd' untuk double/float (harga)

            if ($insert_stmt->execute()) {
                $message = "Menu '" . htmlspecialchars($nama_menu) . "' berhasil ditambahkan!";
                $message_type = "success";
            } else {
                $message = "Gagal menambahkan menu: " . $insert_stmt->error;
                $message_type = "error";
            }
            $insert_stmt->close();
        }
        $check_stmt->close();
    }
}

// --- Logika Hapus Menu ---
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $menu_id_to_delete = $_GET['id'];

    $delete_stmt = $conn->prepare("DELETE FROM menus WHERE id = ?");
    if ($delete_stmt === false) {
        die("Error preparing delete statement: " . $conn->error);
    }
    $delete_stmt->bind_param("i", $menu_id_to_delete);

    if ($delete_stmt->execute()) {
        $_SESSION['success_message'] = "Menu berhasil dihapus.";
    } else {
        $_SESSION['error_message'] = "Gagal menghapus menu: " . $delete_stmt->error;
    }
    $delete_stmt->close();
    header("Location: manage_menus.php"); // Redirect kembali untuk membersihkan URL
    exit();
}


// --- Ambil Data Menu untuk Ditampilkan ---
$sql_menus = "SELECT id, nama_menu, deskripsi, harga, gambar_url FROM menus ORDER BY id ASC";
$result_menus = $conn->query($sql_menus);

// Mengambil pesan dari session setelah redirect (misalnya dari edit_menu.php atau delete)
if (isset($_SESSION['success_message'])) {
    $message = $_SESSION['success_message'];
    $message_type = 'success';
    unset($_SESSION['success_message']);
}
if (isset($_SESSION['error_message'])) {
    $message = $_SESSION['error_message'];
    $message_type = 'error';
    unset($_SESSION['error_message']);
}

$role = $_SESSION['role']; // Ambil role untuk sidebar
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Menu - Rumah Makan Padang</title>
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
        <div class="text-3xl font-bold mb-8 text-center"><?php echo ucfirst($role); ?> Padang</div>
        <nav>
            <ul>
                <!-- Menu untuk Admin -->
                <?php if ($role == 'admin'): ?>
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
                        <a href="manage_menus.php" class="flex items-center text-white bg-yellow-800 p-3 rounded-lg transition duration-200">
                            <i class="fas fa-utensils mr-3"></i> Manajemen Menu
                        </a>
                    </li>
                <!-- Menu untuk Petugas -->
                <?php elseif ($role == 'petugas'): ?>
                    <li class="mb-4">
                        <a href="dashboard_petugas.php" class="flex items-center text-white hover:bg-yellow-800 p-3 rounded-lg transition duration-200">
                            <i class="fas fa-cash-register mr-3"></i> Penjualan
                        </a>
                    </li>
                <?php endif; ?>
                <!-- Menu yang tersedia untuk semua role yang login -->
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
            <h1 class="text-4xl font-bold text-yellow-800">Manajemen Menu</h1>
            <div class="bg-yellow-600 text-white px-4 py-2 rounded-lg text-lg">
                <i class="fas fa-user-circle mr-2"></i> <?php echo ucfirst($role); ?>
            </div>
        </header>

        <?php if ($message): ?>
            <div class="mb-6 p-4 rounded-lg <?php echo $message_type === 'success' ? 'bg-green-100 text-green-800 border border-green-400' : 'bg-red-100 text-red-800 border border-red-400'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <!-- Form Tambah Menu Baru -->
        <section class="bg-white p-6 rounded-lg shadow-md mb-8">
            <h2 class="text-2xl font-bold text-yellow-800 mb-6 text-center">Tambah Menu Baru</h2>
            <form action="manage_menus.php" method="POST">
                <input type="hidden" name="add_menu" value="1">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="mb-4">
                        <label for="nama_menu" class="block text-gray-700 text-sm font-semibold mb-2">Nama Menu:</label>
                        <input type="text" id="nama_menu" name="nama_menu" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500" required>
                    </div>
                    <div class="mb-4">
                        <label for="harga" class="block text-gray-700 text-sm font-semibold mb-2">Harga (Rp):</label>
                        <input type="number" id="harga" name="harga" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500" step="0.01" min="0" required>
                    </div>
                </div>
                <div class="mb-4">
                    <label for="deskripsi" class="block text-gray-700 text-sm font-semibold mb-2">Deskripsi:</label>
                    <textarea id="deskripsi" name="deskripsi" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500"></textarea>
                </div>
                <div class="mb-6">
                    <label for="gambar_url" class="block text-gray-700 text-sm font-semibold mb-2">URL Gambar Menu:</label>
                    <input type="text" id="gambar_url" name="gambar_url" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500">
                    <p class="text-sm text-gray-500 mt-1">Misal: assets/img/rendang.jpg</p>
                </div>
                <button type="submit" class="w-full bg-yellow-600 text-white px-4 py-2 rounded-lg font-semibold shadow-md hover:bg-yellow-700 transition">
                    <i class="fas fa-plus-circle mr-2"></i> Tambah Menu
                </button>
            </form>
        </section>

        <!-- Daftar Menu -->
        <section class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-2xl font-bold text-yellow-800 mb-6 text-center">Daftar Menu</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white border border-gray-200">
                    <thead>
                        <tr class="bg-yellow-100 text-yellow-800">
                            <th class="py-3 px-4 border-b text-left">ID</th>
                            <th class="py-3 px-4 border-b text-left">Gambar</th>
                            <th class="py-3 px-4 border-b text-left">Nama Menu</th>
                            <th class="py-3 px-4 border-b text-left">Deskripsi</th>
                            <th class="py-3 px-4 border-b text-left">Harga</th>
                            <th class="py-3 px-4 border-b text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result_menus->num_rows > 0) {
                            while($row = $result_menus->fetch_assoc()) {
                                ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="py-3 px-4 border-b"><?php echo $row['id']; ?></td>
                                    <td class="py-3 px-4 border-b">
                                        <?php if (!empty($row['gambar_url'])): ?>
                                            <img src="<?php echo htmlspecialchars($row['gambar_url']); ?>" alt="<?php echo htmlspecialchars($row['nama_menu']); ?>" class="w-16 h-16 object-cover rounded">
                                        <?php else: ?>
                                            Tidak ada gambar
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3 px-4 border-b font-semibold"><?php echo htmlspecialchars($row['nama_menu']); ?></td>
                                    <td class="py-3 px-4 border-b text-gray-700"><?php echo htmlspecialchars($row['deskripsi']); ?></td>
                                    <td class="py-3 px-4 border-b text-yellow-800 font-bold">Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?></td>
                                    <td class="py-3 px-4 border-b text-center space-x-2">
                                        <a href="edit_menu.php?id=<?php echo $row['id']; ?>" class="bg-blue-500 text-white px-3 py-1 rounded-lg hover:bg-blue-600 transition">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <a href="javascript:void(0);" onclick="confirmDelete(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['nama_menu']); ?>')" class="bg-red-500 text-white px-3 py-1 rounded-lg hover:bg-red-600 transition">
                                            <i class="fas fa-trash"></i> Hapus
                                        </a>
                                    </td>
                                </tr>
                                <?php
                            }
                        } else {
                            ?>
                            <tr>
                                <td colspan="6" class="py-4 text-center text-gray-500">Belum ada menu yang terdaftar.</td>
                            </tr>
                            <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <script>
        function confirmDelete(id, name) {
            if (confirm("Apakah Anda yakin ingin menghapus menu '" + name + "' (ID: " + id + ")?")) {
                window.location.href = "manage_menus.php?action=delete&id=" + id;
            }
        }
    </script>
</body>
</html>
<?php
// Tutup koneksi database di paling akhir file setelah semua HTML selesai
if (isset($conn)) {
    $conn->close();
}
?>