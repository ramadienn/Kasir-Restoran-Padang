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

$menu_to_edit = null;

// Ambil ID menu yang akan diedit dari URL
if (isset($_GET['id'])) {
    $edit_menu_id = $_GET['id'];

    // Ambil data menu yang akan diedit
    $stmt = $conn->prepare("SELECT id, nama_menu, deskripsi, harga, gambar_url FROM menus WHERE id = ?");
    if ($stmt === false) {
        die("Error preparing select statement: " . $conn->error);
    }
    $stmt->bind_param("i", $edit_menu_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $menu_to_edit = $result->fetch_assoc();
    $stmt->close();

    if (!$menu_to_edit) {
        // Jika menu tidak ditemukan, redirect kembali
        $_SESSION['error_message'] = "Menu tidak ditemukan.";
        header("Location: manage_menus.php");
        exit();
    }
} else {
    // Jika tidak ada ID di URL, redirect kembali
    header("Location: manage_menus.php");
    exit();
}

// Proses update data jika form disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama_menu = $_POST['nama_menu'];
    $deskripsi = $_POST['deskripsi'];
    $harga = $_POST['harga'];
    $current_gambar_url = $_POST['current_gambar_url'] ?? ''; // Gambar saat ini

    // Validasi input
    if (empty($nama_menu) || empty($harga)) {
        $message = "Nama Menu dan Harga harus diisi.";
        $message_type = "error";
    } elseif (!is_numeric($harga) || $harga <= 0) {
        $message = "Harga harus berupa angka positif.";
        $message_type = "error";
    } else {
        $new_gambar_url = $current_gambar_url; // Default: gunakan gambar yang sudah ada

        // Proses upload gambar baru jika ada
        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
            $target_dir = "uploads/";
            // Buat folder uploads jika belum ada
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            $target_file = $target_dir . basename($_FILES["gambar"]["name"]);
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            $uploadOk = 1;

            // Cek apakah file gambar asli atau palsu
            $check = getimagesize($_FILES["gambar"]["tmp_name"]);
            if ($check !== false) {
                $uploadOk = 1;
            } else {
                $message = "File bukan gambar.";
                $message_type = "error";
                $uploadOk = 0;
            }

            // Cek ukuran file (maks 5MB)
            if ($_FILES["gambar"]["size"] > 5000000) {
                $message = "Ukuran gambar terlalu besar (maks 5MB).";
                $message_type = "error";
                $uploadOk = 0;
            }

            // Izinkan format file tertentu
            if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" ) {
                $message = "Hanya file JPG, JPEG, PNG & GIF yang diizinkan.";
                $message_type = "error";
                $uploadOk = 0;
            }

            // Jika semua cek lolos, coba upload
            if ($uploadOk == 1 && $message_type !== "error") {
                // Hapus gambar lama jika ada dan bukan gambar default (hindari hapus default)
                if (!empty($current_gambar_url) && file_exists($current_gambar_url) && basename($current_gambar_url) !== 'default_menu.jpg' && basename($current_gambar_url) !== 'no_image.png') {
                    unlink($current_gambar_url);
                }
                
                // Buat nama file unik untuk gambar baru
                $new_file_name = uniqid('menu_', true) . '.' . $imageFileType;
                $new_gambar_url = $target_dir . $new_file_name;
                if (!move_uploaded_file($_FILES["gambar"]["tmp_name"], $new_gambar_url)) {
                    $message = "Maaf, ada error saat mengupload gambar baru.";
                    $message_type = "error";
                }
            }
        }

        if ($message_type !== "error") { // Lanjutkan jika tidak ada error dari upload
            $stmt = $conn->prepare("UPDATE menus SET nama_menu = ?, deskripsi = ?, harga = ?, gambar_url = ? WHERE id = ?");
            if ($stmt === false) {
                die("Error preparing update statement: " . $conn->error);
            }
            $stmt->bind_param("ssdsi", $nama_menu, $deskripsi, $harga, $new_gambar_url, $edit_menu_id); // s=string, d=decimal, i=integer
            
            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Menu '$nama_menu' berhasil diperbarui!";
            } else {
                $_SESSION['error_message'] = "Gagal memperbarui menu: " . $stmt->error;
            }
            $stmt->close();
            header("Location: manage_menus.php"); // Redirect ke manage_menus.php setelah berhasil
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Menu - Admin</title>
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
                    <a href="manage_users.php" class="flex items-center text-white hover:bg-yellow-800 p-3 rounded-lg transition duration-200">
                        <i class="fas fa-users mr-3"></i> Manajemen Pengguna
                    </a>
                </li>
                <li class="mb-4">
                    <a href="manage_menus.php" class="flex items-center text-white bg-yellow-800 p-3 rounded-lg transition duration-200">
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

    <main class="flex-1 p-8">
        <header class="flex justify-between items-center mb-8">
            <h1 class="text-4xl font-bold text-yellow-800">Edit Menu</h1>
            <div class="bg-yellow-600 text-white px-4 py-2 rounded-lg text-lg">
                <i class="fas fa-user-circle mr-2"></i> Admin
            </div>
        </header>

        <section class="bg-white p-6 rounded-lg shadow-md max-w-lg mx-auto">
            <h2 class="text-2xl font-bold text-yellow-800 mb-6 text-center">Form Edit Menu</h2>

            <?php if ($message): ?>
                <div class="mb-6 p-4 rounded-lg <?php echo $message_type === 'success' ? 'bg-green-100 text-green-800 border border-green-400' : 'bg-red-100 text-red-800 border border-red-400'; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <form action="edit_menu.php?id=<?php echo $menu_to_edit['id']; ?>" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="current_gambar_url" value="<?php echo htmlspecialchars($menu_to_edit['gambar_url']); ?>">

                <div class="mb-4">
                    <label for="nama_menu" class="block text-gray-700 text-sm font-semibold mb-2">Nama Menu:</label>
                    <input type="text" id="nama_menu" name="nama_menu" value="<?php echo htmlspecialchars($menu_to_edit['nama_menu']); ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500" required>
                </div>
                <div class="mb-4">
                    <label for="harga" class="block text-gray-700 text-sm font-semibold mb-2">Harga (Rp):</label>
                    <input type="number" id="harga" name="harga" step="0.01" value="<?php echo htmlspecialchars($menu_to_edit['harga']); ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500" required>
                </div>
                <div class="mb-4">
                    <label for="deskripsi" class="block text-gray-700 text-sm font-semibold mb-2">Deskripsi:</label>
                    <textarea id="deskripsi" name="deskripsi" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500"><?php echo htmlspecialchars($menu_to_edit['deskripsi']); ?></textarea>
                </div>
                <div class="mb-6">
                    <label for="gambar" class="block text-gray-700 text-sm font-semibold mb-2">Ganti Gambar Menu:</label>
                    <?php if (!empty($menu_to_edit['gambar_url']) && file_exists($menu_to_edit['gambar_url'])): ?>
                        <img src="<?php echo htmlspecialchars($menu_to_edit['gambar_url']); ?>" alt="Current Image" class="w-32 h-32 object-cover rounded-md mb-2">
                        <p class="text-sm text-gray-500 mb-2">Gambar saat ini.</p>
                    <?php else: ?>
                        <p class="text-sm text-gray-500 mb-2">Tidak ada gambar saat ini.</p>
                    <?php endif; ?>
                    <input type="file" id="gambar" name="gambar" accept="image/*" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500">
                    <p class="text-sm text-gray-500 mt-1">Biarkan kosong jika tidak ingin mengubah gambar. Ukuran maks 5MB. Format: JPG, JPEG, PNG, GIF.</p>
                </div>
                <div class="flex justify-between">
                    <button type="submit" class="bg-yellow-600 text-white px-6 py-2 rounded-lg font-semibold shadow-md hover:bg-yellow-700 transition">
                        <i class="fas fa-save mr-2"></i> Simpan Perubahan
                    </button>
                    <a href="manage_menus.php" class="bg-gray-400 text-white px-6 py-2 rounded-lg font-semibold shadow-md hover:bg-gray-500 transition">
                        <i class="fas fa-arrow-left mr-2"></i> Kembali
                    </a>
                </div>
            </form>
        </section>
    </main>
</body>
</html>
<?php
$conn->close();
?>