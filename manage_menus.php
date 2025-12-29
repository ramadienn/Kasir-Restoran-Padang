<?php
session_start();
include 'koneksi.php';

// Cek apakah user sudah login dan memiliki role admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$message = '';
$message_type = ''; 

// --- Logika Tambah Menu Baru ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_menu'])) {
    $nama_menu = trim($_POST['nama_menu']);
    $deskripsi = trim($_POST['deskripsi']);
    $harga = $_POST['harga'];
    
    // Inisialisasi variabel gambar
    $gambar_url = '';

    // Validasi input dasar
    if (empty($nama_menu) || empty($harga)) {
        $message = "Nama Menu dan Harga harus diisi.";
        $message_type = "error";
    } elseif (!is_numeric($harga) || $harga < 0) {
        $message = "Harga harus berupa angka positif.";
        $message_type = "error";
    } else {
        // --- LOGIKA UPLOAD GAMBAR ---
        if (isset($_FILES['gambar_file']) && $_FILES['gambar_file']['error'] === 0) {
            $target_dir = "assets/img/";
            
            // Buat folder jika belum ada
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }

            $file_name = time() . "_" . basename($_FILES["gambar_file"]["name"]); // Beri prefix waktu agar unik
            $target_file = $target_dir . $file_name;
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            $valid_extensions = array("jpg", "jpeg", "png", "gif");

            // Cek apakah itu gambar asli
            $check = getimagesize($_FILES["gambar_file"]["tmp_name"]);
            
            if($check !== false && in_array($imageFileType, $valid_extensions)) {
                if (move_uploaded_file($_FILES["gambar_file"]["tmp_name"], $target_file)) {
                    $gambar_url = $target_file;
                } else {
                    $message = "Gagal mengunggah file gambar.";
                    $message_type = "error";
                }
            } else {
                $message = "Format file tidak didukung (Gunakan JPG, PNG, atau GIF).";
                $message_type = "error";
            }
        }

        // Simpan ke database jika tidak ada error sebelumnya
        if ($message_type !== 'error') {
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
                    $message = "Menu berhasil ditambahkan!";
                    $message_type = "success";
                } else {
                    $message = "Gagal menyimpan ke database.";
                    $message_type = "error";
                }
                $insert_stmt->close();
            }
            $check_stmt->close();
        }
    }
}

// --- Logika Hapus Menu ---
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id_del = (int)$_GET['id'];
    
    // Opsional: Hapus file fisik gambar sebelum hapus data di DB
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
    } else {
        $_SESSION['error_message'] = "Gagal menghapus menu.";
    }
    header("Location: manage_menus.php");
    exit();
}

// Ambil Pesan Session
if (isset($_SESSION['success_message'])) { $message = $_SESSION['success_message']; $message_type = 'success'; unset($_SESSION['success_message']); }
if (isset($_SESSION['error_message'])) { $message = $_SESSION['error_message']; $message_type = 'error'; unset($_SESSION['error_message']); }

$result_menus = $conn->query("SELECT * FROM menus ORDER BY nama_menu ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Menu - RM Padang</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet"/>
</head>
<body class="bg-gray-100 flex">

    <?php include 'sidebar.php'; ?>

    <main class="flex-1 p-8">
        <header class="mb-8 bg-white p-6 rounded-2xl shadow-sm flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-800">Manajemen Menu</h1>
            <div class="bg-yellow-100 text-yellow-700 px-4 py-1 rounded-full text-sm font-bold">
                <?php echo $result_menus->num_rows; ?> Produk
            </div>
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
                    <h2 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">Tambah Menu Baru</h2>
                    <form action="" method="POST" enctype="multipart/form-data" class="space-y-4">
                        <input type="hidden" name="add_menu" value="1">
                        <div>
                            <label class="block text-sm font-semibold text-gray-600 mb-1">Nama Menu</label>
                            <input type="text" name="nama_menu" class="w-full p-2.5 border rounded-lg focus:ring-2 focus:ring-yellow-500 outline-none" required>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-600 mb-1">Harga (Rp)</label>
                            <input type="number" name="harga" class="w-full p-2.5 border rounded-lg focus:ring-2 focus:ring-yellow-500 outline-none" required>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-600 mb-1">Deskripsi</label>
                            <textarea name="deskripsi" rows="3" class="w-full p-2.5 border rounded-lg focus:ring-2 focus:ring-yellow-500 outline-none"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-600 mb-1 text-red-600">Upload Gambar File</label>
                            <input type="file" name="gambar_file" class="w-full p-2 border border-dashed border-gray-300 rounded-lg bg-gray-50 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-yellow-50 file:text-yellow-700 hover:file:bg-yellow-100">
                        </div>
                        <button type="submit" class="w-full bg-yellow-600 hover:bg-yellow-700 text-white font-bold py-3 rounded-lg transition shadow-lg shadow-yellow-200">
                            <i class="fas fa-plus-circle mr-2"></i> Simpan Menu
                        </button>
                    </form>
                </section>
            </div>

            <div class="lg:col-span-2">
                <section class="bg-white rounded-2xl shadow-sm overflow-hidden text-sm md:text-base">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 border-b">
                                <th class="p-4 text-xs uppercase text-gray-400 font-bold">Menu</th>
                                <th class="p-4 text-xs uppercase text-gray-400 font-bold">Harga</th>
                                <th class="p-4 text-xs uppercase text-gray-400 font-bold text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php if ($result_menus->num_rows > 0): ?>
                                <?php while($row = $result_menus->fetch_assoc()): ?>
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="p-4">
                                        <div class="flex items-center">
                                            <div class="h-12 w-12 rounded-lg bg-gray-200 overflow-hidden mr-3 border">
                                                <?php if (!empty($row['gambar_url'])): ?>
                                                    <img src="<?php echo htmlspecialchars($row['gambar_url']); ?>" class="w-full h-full object-cover">
                                                <?php else: ?>
                                                    <div class="flex items-center justify-center h-full text-gray-400 bg-gray-100">
                                                        <i class="fas fa-image"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div>
                                                <div class="font-bold text-gray-800"><?php echo htmlspecialchars($row['nama_menu']); ?></div>
                                                <div class="text-xs text-gray-500 truncate w-32 md:w-48"><?php echo htmlspecialchars($row['deskripsi']); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-4 font-bold text-yellow-700">
                                        Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?>
                                    </td>
                                    <td class="p-4 text-center">
                                        <div class="flex justify-center space-x-2">
                                            <a href="edit_menu.php?id=<?php echo $row['id']; ?>" class="text-blue-500 hover:text-blue-700 p-2">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button onclick="confirmDelete(<?php echo $row['id']; ?>, '<?php echo addslashes($row['nama_menu']); ?>')" 
                                               class="text-red-400 hover:text-red-600 p-2">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="p-8 text-center text-gray-400 italic">Belum ada menu yang terdaftar.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </section>
            </div>
        </div>
    </main>

    <script>
        function confirmDelete(id, name) {
            if (confirm("Hapus menu '" + name + "'? Tindakan ini tidak dapat dibatalkan.")) {
                window.location.href = "manage_menus.php?action=delete&id=" + id;
            }
        }
    </script>
</body>
</html>