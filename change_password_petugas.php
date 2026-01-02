<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'petugas') {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'] ?? 'Petugas';
$error = "";
$success = "";

if (isset($_POST['update_password'])) {
    $user_id = $_SESSION['user_id'];
    $old_pass = $_POST['old_password'];
    $new_pass = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];

    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($old_pass !== $user['password']) {
        $error = "Password lama salah!";
    } elseif ($new_pass !== $confirm_pass) {
        $error = "Konfirmasi password baru tidak cocok!";
    } elseif (strlen($new_pass) < 5) {
        $error = "Password baru minimal 5 karakter!";
    } else {
        $update = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $update->bind_param("si", $new_pass, $user_id);
        if ($update->execute()) {
            $success = "Password berhasil diperbarui!";
        } else {
            $error = "Gagal mengupdate database.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keamanan - RM Padang</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet"/>
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #fffaf0; }
        .sidebar-orange { background: linear-gradient(180deg, #ea580c 0%, #9a3412 100%); }
        .orange-gradient { background: linear-gradient(135deg, #ea580c 0%, #9a3412 100%); }
    </style>
</head>
<body class="flex min-h-screen">

    <aside class="w-64 sidebar-orange text-white flex flex-col shadow-2xl sticky top-0 h-screen">
        <div class="p-8 text-center border-b border-orange-400/30">
            <div class="w-12 h-12 bg-white rounded-xl flex items-center justify-center mx-auto mb-3 shadow-lg rotate-3">
                <i class="fas fa-utensils text-orange-600 text-xl"></i>
            </div>
            <h2 class="font-black tracking-tighter text-lg uppercase">RM PADANG</h2>
        </div>
        
        <nav class="flex-1 px-4 py-6 space-y-2">
            <a href="dashboard_petugas.php" class="flex items-center hover:bg-white/10 px-4 py-3 rounded-xl transition group">
                <i class="fas fa-cash-register mr-3 text-orange-200 group-hover:text-white"></i> 
                <span>Penjualan</span>
            </a>
            
            <a href="change_password_petugas.php" class="flex items-center bg-white/20 px-4 py-3 rounded-xl font-bold shadow-inner">
                <i class="fas fa-key mr-3"></i> Keamanan
            </a>

            <div class="pt-4 border-t border-orange-400/30">
                <a href="logout.php" class="flex items-center text-orange-200 hover:text-white px-4 py-3 transition">
                    <i class="fas fa-power-off mr-3"></i> Keluar
                </a>
            </div>
        </nav>

        <div class="p-6 bg-orange-900/30 text-center text-[10px] font-bold tracking-widest uppercase">
            User: <?php echo htmlspecialchars($username); ?>
        </div>
    </aside>

    <main class="flex-1 p-12 flex justify-center items-start">
        
        <div class="w-full max-w-md mt-10">
            <div class="bg-white rounded-[40px] shadow-2xl overflow-hidden border border-orange-100">
                <div class="orange-gradient p-8 text-center">
                    <div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center mx-auto mb-4 backdrop-blur-md">
                        <i class="fas fa-shield-alt text-white text-3xl"></i>
                    </div>
                    <h2 class="text-white text-xl font-bold uppercase tracking-widest">Ganti Password</h2>
                    <p class="text-orange-200 text-xs mt-1">Jaga keamanan akun petugas Anda</p>
                </div>

                <div class="p-8">
                    <?php if($error): ?>
                        <div class="bg-red-50 text-red-600 p-4 rounded-2xl mb-6 text-xs font-bold border border-red-100 flex items-center">
                            <i class="fas fa-exclamation-circle mr-2"></i> <?php echo $error; ?>
                        </div>
                    <?php endif; ?>

                    <?php if($success): ?>
                        <div class="bg-green-50 text-green-600 p-4 rounded-2xl mb-6 text-xs font-bold border border-green-100 flex items-center">
                            <i class="fas fa-check-circle mr-2"></i> <?php echo $success; ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" class="space-y-5">
                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase ml-2 tracking-widest">Password Saat Ini</label>
                            <div class="relative mt-1">
                                <i class="fas fa-lock absolute left-4 top-4 text-orange-300"></i>
                                <input type="password" name="old_password" required
                                    class="w-full pl-12 pr-4 py-4 bg-orange-50 border-none rounded-2xl focus:ring-2 focus:ring-orange-500 font-bold text-slate-700 outline-none">
                            </div>
                        </div>

                        <hr class="border-orange-100">

                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase ml-2 tracking-widest">Password Baru</label>
                            <div class="relative mt-1">
                                <i class="fas fa-key absolute left-4 top-4 text-orange-300"></i>
                                <input type="password" name="new_password" required
                                    class="w-full pl-12 pr-4 py-4 bg-orange-50 border-none rounded-2xl focus:ring-2 focus:ring-orange-500 font-bold text-slate-700 outline-none">
                            </div>
                        </div>

                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase ml-2 tracking-widest">Konfirmasi Password Baru</label>
                            <div class="relative mt-1">
                                <i class="fas fa-check-double absolute left-4 top-4 text-orange-300"></i>
                                <input type="password" name="confirm_password" required
                                    class="w-full pl-12 pr-4 py-4 bg-orange-50 border-none rounded-2xl focus:ring-2 focus:ring-orange-500 font-bold text-slate-700 outline-none">
                            </div>
                        </div>

                        <button type="submit" name="update_password" 
                            class="w-full orange-gradient text-white py-5 rounded-2xl font-black text-xs uppercase tracking-[0.2em] shadow-lg shadow-orange-200 hover:scale-[1.02] active:scale-95 transition-all">
                            Simpan Perubahan
                        </button>
                    </form>
                </div>
            </div>
            <p class="text-center text-slate-400 text-[10px] mt-8 uppercase font-bold tracking-widest">
                RM PADANG &copy; 2025 - Security System
            </p>
        </div>
    </main>

</body>
</html>