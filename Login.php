<?php
session_start();
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
<html lang="id">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1" name="viewport"/>
    <title>Login Cinematic - RM Padang</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet"/>
    <style>
        body, html { margin: 0; padding: 0; height: 100%; width: 100%; font-family: 'Poppins', sans-serif; overflow: hidden; background-color: #000; }
        
        .bg-cinema {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background-image: url('assets/img/Kota Padang.jpg');
            background-size: cover; background-position: center; z-index: -2;
            animation: zoomEffect 25s infinite alternate ease-in-out;
        }
        @keyframes zoomEffect { 0% { transform: scale(1); } 100% { transform: scale(1.2); } }

        .overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(to bottom, rgba(0,0,0,0.2), rgba(0,0,0,0.8)); z-index: -1; }

        .login-box { opacity: 0; transform: translateY(30px); animation: fadeInUp 1.2s ease-out forwards; animation-delay: 0.5s; }
        @keyframes fadeInUp { to { opacity: 1; transform: translateY(0); } }

        .glass { background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(15px); -webkit-backdrop-filter: blur(15px); border: 1px solid rgba(255, 255, 255, 0.2); box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.7); }

        .input-cinema { background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.2); color: white; transition: all 0.4s ease; }
        .input-cinema:focus { background: rgba(255, 255, 255, 0.15); border-color: #fbbf24; box-shadow: 0 0 20px rgba(251, 191, 36, 0.4); outline: none; }

        /* MODAL STYLE */
        #staffModal {
            display: none; position: fixed; inset: 0; z-index: 10000;
            background: rgba(0, 0, 0, 0.85); backdrop-filter: blur(10px);
            align-items: center; justify-content: center;
        }
        .modal-content {
            transform: scale(0.9); opacity: 0; transition: all 0.3s ease-in-out;
        }
        .modal-active .modal-content { transform: scale(1); opacity: 1; }
    </style>
</head>
<body class="flex items-center justify-center p-4">

    <div id="staffModal" class="flex p-4">
        <div class="modal-content glass max-w-sm w-full p-8 rounded-[30px] text-center border-red-500/50">
            <div class="w-20 h-20 bg-red-500/20 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-user-shield text-red-500 text-3xl"></i>
            </div>
            <h2 class="text-white text-2xl font-bold mb-3">AREA TERBATAS!</h2>
            <p class="text-gray-300 text-sm leading-relaxed mb-8">
                Halaman ini hanya diperuntukkan bagi <span class="text-yellow-500 font-bold">Pegawai & Admin</span>. Pelanggan dilarang keras mencoba login tanpa izin otoritas.
            </p>
            <button onclick="closeModal()" class="w-full bg-white text-black font-bold py-3 rounded-xl hover:bg-gray-200 transition uppercase text-xs tracking-widest">
                Saya Mengerti
            </button>
        </div>
    </div>

    <div class="bg-cinema"></div>
    <div class="overlay"></div>

    <div class="login-box w-full max-w-md">
        <div class="glass p-10 rounded-[40px] relative overflow-hidden">
            <div class="absolute -top-10 -right-10 w-32 h-32 bg-yellow-500/20 rounded-full blur-3xl"></div>

            <div class="text-center mb-10">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-yellow-500 rounded-3xl rotate-12 mb-6 shadow-2xl">
                    <i class="fas fa-utensils text-black text-3xl -rotate-12"></i>
                </div>
                <h1 class="text-4xl font-black text-white tracking-tighter uppercase">RM PADANG</h1>
                <p class="text-yellow-500/70 text-[10px] tracking-[0.4em] font-bold">STAFF ONLY</p>
            </div>

            <?php if (isset($_SESSION['login_error'])): ?>
                <div class="bg-red-500/20 border border-red-500/50 text-red-200 p-4 mb-6 rounded-2xl text-center text-sm backdrop-blur-md">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <?php echo $_SESSION['login_error']; unset($_SESSION['login_error']); ?>
                </div>
            <?php endif; ?>

            <form action="login_process.php" method="POST" class="space-y-6">
                <div class="relative group">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400 group-focus-within:text-yellow-500 transition-colors">
                        <i class="fas fa-user"></i>
                    </span>
                    <input type="text" name="username" class="w-full pl-12 pr-4 py-4 rounded-2xl input-cinema placeholder-gray-500 font-medium" placeholder="USERNAME PEGAWAI" required>
                </div>

                <div class="relative group">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400 group-focus-within:text-yellow-500 transition-colors">
                        <i class="fas fa-lock"></i>
                    </span>
                    <input type="password" name="password" class="w-full pl-12 pr-4 py-4 rounded-2xl input-cinema placeholder-gray-500 font-medium" placeholder="PASSWORD" required>
                </div>

                <button type="submit" class="w-full bg-yellow-500 hover:bg-yellow-400 text-black font-black py-5 rounded-2xl shadow-2xl shadow-yellow-500/20 transition-all duration-500 transform hover:scale-[1.03] active:scale-[0.97] uppercase tracking-widest text-sm">
                    MASUK KE SISTEM
                </button>
            </form>

            <div class="mt-10 text-center">
                <a href="index.php" class="text-gray-500 hover:text-white text-[10px] font-bold transition-all tracking-[0.3em] uppercase">
                    <i class="fas fa-long-arrow-alt-left mr-2"></i> KEMBALI KE BERANDA
                </a>
            </div>
        </div>
        <p class="text-center text-gray-600 text-[9px] mt-8 tracking-[0.6em] uppercase opacity-50">Citarasa Minangkabau Digital System</p>
    </div>

    <script>
        // Memunculkan Modal secara otomatis saat halaman selesai dimuat
        window.onload = function() {
            const modal = document.getElementById('staffModal');
            setTimeout(() => {
                modal.style.display = 'flex';
                setTimeout(() => {
                    modal.classList.add('modal-active');
                }, 10);
            }, 1800); // Muncul setelah animasi kartu login selesai
        };

        function closeModal() {
            const modal = document.getElementById('staffModal');
            modal.classList.remove('modal-active');
            setTimeout(() => {
                modal.style.display = 'none';
            }, 300);
        }
    </script>
</body>
</html>