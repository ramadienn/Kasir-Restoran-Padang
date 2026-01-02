<?php
session_start();
include 'koneksi.php';

// 1. Proteksi Halaman
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$message = '';
$message_type = ''; 

// 2. LOGIKA TAMBAH MENU
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_menu'])) {
    $nama_menu = trim($_POST['nama_menu']);
    $deskripsi = trim($_POST['deskripsi']);
    $harga = $_POST['harga'];
    $gambar_url = ''; // Default jika tidak upload

    if (empty($nama_menu) || empty($harga)) {
        $message = "Nama Menu dan Harga harus diisi.";
        $message_type = "error";
    } elseif (!is_numeric($harga) || $harga < 0) {
        $message = "Harga harus berupa angka positif.";
        $message_type = "error";
    } else {
        // Proses Upload Gambar
        if (isset($_FILES['gambar_file']) && $_FILES['gambar_file']['error'] === 0) {
            $target_dir = "assets/img/";
            if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

            $file_extension = strtolower(pathinfo($_FILES["gambar_file"]["name"], PATHINFO_EXTENSION));
            $new_file_name = time() . "_" . bin2hex(random_bytes(4)) . "." . $file_extension;
            $target_file = $target_dir . $new_file_name;
            $valid_extensions = array("jpg", "jpeg", "png", "gif");

            if(in_array($file_extension, $valid_extensions)) {
                if (move_uploaded_file($_FILES["gambar_file"]["tmp_name"], $target_file)) {
                    $gambar_url = $target_file;
                }
            }
        }

        // Cek Duplikasi & Simpan ke DB
        $check_stmt = $conn->prepare("SELECT id FROM menus WHERE nama_menu = ?");
        $check_stmt->bind_param("s", $nama_menu);
        $check_stmt->execute();
        if ($check_stmt->get_result()->num_rows > 0) {
            $message = "Nama Menu sudah ada.";
            $message_type = "error";
        } else {
            $insert_stmt = $conn->prepare("INSERT INTO menus (nama_menu, deskripsi, harga, gambar_url) VALUES (?, ?, ?, ?)");
            $insert_stmt->bind_param("ssds", $nama_menu, $deskripsi, $harga, $gambar_url);
            if ($insert_stmt->execute()) {
                $message = "Menu '$nama_menu' berhasil ditambahkan!";
                $message_type = "success";
            }
            $insert_stmt->close();
        }
        $check_stmt->close();
    }
}

// 3. LOGIKA HAPUS MENU
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id_del = (int)$_GET['id'];
    
    // Ambil info gambar untuk dihapus dari server
    $get_img = $conn->prepare("SELECT gambar_url FROM menus WHERE id = ?");
    $get_img->bind_param("i", $id_del);
    $get_img->execute();
    $res_img = $get_img->get_result()->fetch_assoc();
    
    if($res_img && !empty($res_img['gambar_url']) && file_exists($res_img['gambar_url'])) {
        unlink($res_img['gambar_url']);
    }

    $del_stmt = $conn->prepare("DELETE FROM menus WHERE id = ?");
    $del_stmt->bind_param("i", $id_del);
    if ($del_stmt->execute()) {
        $_SESSION['success_message'] = "Menu berhasil dihapus.";
    }
    header("Location: manage_menus.php");
    exit();
}

// 4. AMBIL DATA UNTUK TABEL
if (isset($_SESSION['success_message'])) { 
    $message = $_SESSION['success_message']; 
    $message_type = 'success'; 
    unset($_SESSION['success_message']); 
}

$result_menus = $conn->query("SELECT * FROM menus ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Manajemen Menu - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet"/>
</head>
<body class="bg-gray-100 flex">

    <aside class="w-64 bg-yellow-700 text-white min-h-screen p-6">
        <div class="text-2xl font-bold mb-8">Admin Padang</div>
        <nav class="space-y-4">
            <a href="dashboard_admin.php" class="block p-3 hover:bg-yellow-800 rounded"><i class="fas fa-home mr-2"></i> Dashboard</a>
            <a href="manage_menus.php" class="block p-3 bg-yellow-800 rounded"><i class="fas fa-utensils mr-2"></i> Menu</a>
            <a href="logout.php" class="block p-3 hover:bg-red-600 rounded mt-10"><i class="fas fa-sign-out-alt mr-2"></i> Logout</a>
        </nav>
    </aside>

    <main class="flex-1 p-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">Manajemen Menu</h1>

        <?php if ($message): ?>
        <div class="mb-4 p-4 rounded-lg <?php echo $message_type === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
            <?php echo $message; ?>
        </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-1">
                <div class="bg-white p-6 rounded-xl shadow-md">
                    <h2 class="text-xl font-bold mb-4">Tambah Menu</h2>
                    <form action="" method="POST" enctype="multipart/form-data" class="space-y-4">
                        <input type="hidden" name="add_menu" value="1">
                        <div>
                            <label class="block text-sm font-medium mb-1">Nama Menu</label>
                            <input type="text" name="nama_menu" class="w-full border p-2 rounded" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Harga (Rp)</label>
                            <input type="number" name="harga" class="w-full border p-2 rounded" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Deskripsi</label>
                            <textarea name="deskripsi" class="w-full border p-2 rounded"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Gambar</label>
                            <input type="file" name="gambar_file" class="w-full text-sm">
                        </div>
                        <button type="submit" class="w-full bg-yellow-600 text-white py-2 rounded hover:bg-yellow-700 font-bold">Simpan</button>
                    </form>
                </div>
            </div>

            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-md overflow-hidden">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="p-4 border-b">Menu</th>
                                <th class="p-4 border-b">Harga</th>
                                <th class="p-4 border-b">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $result_menus->fetch_assoc()): ?>
                            <tr class="hover:bg-gray-50 border-b">
                                <td class="p-4">
                                    <div class="flex items-center">
                                        <img src="<?php echo $row['gambar_url'] ?: 'assets/img/no-image.png'; ?>" class="w-12 h-12 object-cover rounded mr-3 bg-gray-200">
                                        <div>
                                            <div class="font-bold"><?php echo htmlspecialchars($row['nama_menu']); ?></div>
                                            <div class="text-xs text-gray-500"><?php echo htmlspecialchars($row['deskripsi']); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-4 font-bold text-yellow-700">Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?></td>
                                <td class="p-4">
                                    <a href="edit_menu.php?id=<?php echo $row['id']; ?>" class="text-blue-500 hover:text-blue-700 mr-2"><i class="fas fa-edit"></i></a>
                                    <a href="javascript:void(0)" onclick="confirmDelete(<?php echo $row['id']; ?>, '<?php echo addslashes($row['nama_menu']); ?>')" class="text-red-500 hover:text-red-700"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <script>
        function confirmDelete(id, name) {
            if (confirm("Hapus menu '" + name + "'?")) {
                window.location.href = "manage_menus.php?action=delete&id=" + id;
            }
        }
    </script>
</body>
</html>