<?php
session_start();
include 'koneksi.php';

// Logika Redirect: Jika sudah login, langsung lempar ke dashboard masing-masing
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: dashboard_admin.php");
        exit();
    } elseif ($_SESSION['role'] === 'petugas') {
        header("Location: dashboard_petugas.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html class="scroll-smooth" lang="id">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1" name="viewport"/>
    <title>RM Padang - Tambua Ciek!</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800&family=Playfair+Display:wght@700&display=swap" rel="stylesheet"/>
    <style>
        /* --- PRELOADER --- */
        #preloader {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background-color: #000;
            z-index: 9999;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            transition: opacity 0.8s ease-in-out, visibility 0.8s;
        }
        .welcome-text {
            color: white;
            text-align: center;
            font-family: 'Poppins', sans-serif;
            opacity: 0;
            transform: translateY(30px);
            animation: textFadeIn 3.5s ease-in-out forwards;
        }
        @keyframes textFadeIn {
            0% { opacity: 0; transform: translateY(30px); filter: blur(10px); }
            25% { opacity: 1; transform: translateY(0); filter: blur(0px); }
            75% { opacity: 1; transform: translateY(0); filter: blur(0px); }
            100% { opacity: 0; transform: translateY(-30px); filter: blur(10px); }
        }
        .preloader-hidden { opacity: 0; visibility: hidden; }

        /* --- GLOBAL STYLE --- */
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #0f172a;
            color: #f8fafc;
        }
        
        .bg-overlay {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: linear-gradient(rgba(0,0,0,0.6), rgba(15, 23, 42, 0.9)), 
                        url('assets/images/restoran_wallpaper.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            z-index: -1;
        }

        .font-brand { font-family: 'Playfair Display', serif; }

        .navbar-custom {
            background: rgba(15, 23, 42, 0.8);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        .glass-card:hover {
            background: rgba(255, 255, 255, 0.07);
            transform: translateY(-12px);
            border-color: rgba(234, 179, 8, 0.5);
            box-shadow: 0 20px 40px rgba(0,0,0,0.4);
        }

        .btn-premium {
            background: linear-gradient(45deg, #b45309, #f59e0b);
            box-shadow: 0 4px 15px rgba(180, 83, 9, 0.4);
            transition: all 0.3s ease;
        }
        .btn-premium:hover {
            box-shadow: 0 8px 25px rgba(180, 83, 9, 0.6);
            transform: scale(1.05);
        }

        .hero-img {
            animation: float 6s ease-in-out infinite;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }
    </style>
</head>
<body class="min-h-screen flex flex-col">

    <div id="preloader">
        <div class="welcome-text">
            <h2 class="text-5xl md:text-7xl font-extrabold text-yellow-500 tracking-tighter mb-2 font-brand">TAMBUA CIEK!</h2>
            <p class="text-white text-sm md:text-lg tracking-[0.5em] font-light uppercase opacity-60">Kemewahan Rasa Minang</p>
        </div>
    </div>

    <div class="bg-overlay"></div>

    <nav class="navbar-custom text-white shadow-2xl sticky top-0 z-50">
        <div class="container mx-auto px-6 py-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-4 group cursor-pointer">
                    <div class="overflow-hidden rounded-full border-2 border-yellow-500 w-10 h-10">
                        <img alt="Logo" class="group-hover:scale-110 transition duration-300" src="assets/img/navbar.jpg"/>
                    </div>
                    <span class="text-2xl font-black tracking-tighter text-yellow-500 font-brand">RM PADANG</span>
                </div>
                
                <div class="hidden md:flex items-center space-x-10 text-sm font-bold tracking-widest uppercase">
                    <a class="hover:text-yellow-500 transition-colors" href="#menu">Menu</a>
                    <a class="hover:text-yellow-500 transition-colors" href="#about">Tentang</a>
                    <a class="hover:text-yellow-500 transition-colors" href="#contact">Kontak</a>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a class="bg-red-500/20 border border-red-500 text-red-500 px-6 py-2 rounded-full hover:bg-red-500 hover:text-white transition" href="logout.php">Logout</a>
                    <?php else: ?>
                        <a class="btn-premium text-white px-8 py-2.5 rounded-full" href="login.php">Login</a>
                    <?php endif; ?>
                </div>

                <div class="md:hidden">
                    <button id="menu-btn" class="text-yellow-500"><i class="fas fa-bars text-2xl"></i></button>
                </div>
            </div>
        </div>
    </nav>

    <section class="relative container mx-auto px-6 flex flex-col md:flex-row items-center justify-between min-h-[90vh] py-12">
        <div class="md:w-3/5 text-center md:text-left z-10">
            <div class="inline-block px-4 py-1.5 mb-6 rounded-full bg-yellow-500/10 border border-yellow-500/20 text-yellow-500 text-xs font-bold tracking-[0.3em] uppercase">
                Tradisi Bertemu Modernitas
            </div>
            <h1 class="text-5xl md:text-8xl font-black text-white mb-6 leading-none font-brand">
                Nikmati <br> <span class="text-transparent bg-clip-text bg-gradient-to-r from-yellow-400 to-yellow-600">Keajaiban</span> <br> Rasa Minang.
            </h1>
            <p class="text-gray-300 text-lg md:text-xl mb-10 max-w-xl leading-relaxed">
                Kami menyajikan lebih dari sekadar makanan. Kami menyajikan warisan rempah terpilih yang dimasak perlahan untuk kesempurnaan rasa.
            </p>
            <div class="flex flex-col sm:flex-row gap-5 justify-center md:justify-start">
                <a class="btn-premium text-white px-10 py-5 rounded-2xl font-bold text-center" href="#menu">
                    <i class="fas fa-utensils mr-2"></i> LIHAT MENU SEKARANG
                </a>
                <a class="bg-white/5 backdrop-blur-md border border-white/10 text-white px-10 py-5 rounded-2xl font-bold hover:bg-white/10 transition text-center" href="#about">
                    Kisah Kami
                </a>
            </div>
        </div>
        
        <div class="md:w-2/5 mt-16 md:mt-0 relative group">
            <div class="absolute -inset-10 bg-yellow-500/20 rounded-full blur-[100px] animate-pulse"></div>
            <img alt="Masakan Padang" class="hero-img relative rounded-[40px] shadow-2xl border-2 border-white/10 mx-auto w-full max-w-md object-cover" src="assets/img/Halaman.jpg"/>
            <div class="absolute -bottom-6 -right-6 bg-white p-6 rounded-3xl shadow-2xl hidden md:block animate-bounce">
                <p class="text-black font-black text-xl">4.9/5 ★</p>
                <p class="text-gray-500 text-xs uppercase tracking-tighter">Rating Google</p>
            </div>
        </div>
    </section>

    <section class="container mx-auto px-6 py-32" id="menu">
        <div class="flex flex-col md:flex-row justify-between items-end mb-20 gap-6">
            <div class="max-w-2xl text-center md:text-left">
                <h2 class="text-4xl md:text-6xl font-black text-white mb-4 font-brand">Menu Andalan</h2>
                <p class="text-gray-400 uppercase tracking-[0.5em] text-sm">Pilihan Terbaik Dari Dapur Kami</p>
            </div>
            <div class="h-px flex-grow bg-white/10 mx-10 hidden md:block mb-4"></div>
            <a href="#" class="text-yellow-500 font-bold hover:underline">Lihat Semua Menu &rarr;</a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-12">
            <?php
            $sql_menus = "SELECT nama_menu, deskripsi, harga, gambar_url FROM menus ORDER BY id ASC LIMIT 6";
            $result_menus = $conn->query($sql_menus);
            if ($result_menus && $result_menus->num_rows > 0) {
                while($row = $result_menus->fetch_assoc()) {
                    echo '<div class="glass-card rounded-[35px] overflow-hidden group p-4">';
                    echo '  <div class="relative overflow-hidden rounded-[25px] h-64">';
                    echo '      <img class="w-full h-full object-cover group-hover:scale-110 transition duration-700" src="'.htmlspecialchars($row["gambar_url"]).'">';
                    echo '      <div class="absolute bottom-4 left-4 bg-black/60 backdrop-blur-md text-white px-4 py-1 rounded-full text-xs font-bold">Populer</div>';
                    echo '  </div>';
                    echo '  <div class="p-6">';
                    echo '    <h3 class="text-2xl font-bold text-white group-hover:text-yellow-500 transition font-brand">'.htmlspecialchars($row["nama_menu"]).'</h3>';
                    echo '    <p class="text-gray-400 my-4 text-sm line-clamp-2">'.htmlspecialchars($row["deskripsi"]).'</p>';
                    echo '    <div class="flex justify-between items-center border-t border-white/5 pt-4">';
                    echo '        <span class="text-xl font-black text-yellow-500">Rp '.number_format($row["harga"],0,',','.').'</span>';
                    echo '        <button class="bg-white/10 p-3 rounded-xl hover:bg-yellow-500 hover:text-black transition"><i class="fas fa-plus"></i></button>';
                    echo '    </div>';
                    echo '  </div>';
                    echo '</div>';
                }
            } else {
                echo "<p class='col-span-full text-center text-gray-400'>Belum ada menu yang tersedia.</p>";
            }
            ?>
        </div>
    </section>

    <footer class="navbar-custom py-12 text-center text-white mt-auto border-t border-white/5">
        <div class="container mx-auto px-6">
            <h2 class="text-2xl font-brand font-bold text-yellow-500 mb-4">RM PADANG</h2>
            <p class="text-gray-500 text-xs md:text-sm uppercase tracking-[0.3em] mb-8"> &copy; 2026 Muhammad Ramadien Rizky Darmawan</p>
            <div class="flex justify-center space-x-6 text-gray-400">
                <a href="#" class="hover:text-white transition"><i class="fab fa-instagram text-xl"></i></a>
                <a href="#" class="hover:text-white transition"><i class="fab fa-facebook text-xl"></i></a>
                <a href="#" class="hover:text-white transition"><i class="fab fa-whatsapp text-xl"></i></a>
            </div>
        </div>
    </footer>

    <script>
        window.addEventListener('load', () => {
            const preloader = document.getElementById('preloader');
            setTimeout(() => {
                preloader.classList.add('preloader-hidden');
            }, 3000);
        });

        const btn = document.getElementById('menu-btn');
        btn.addEventListener('click', () => {
            alert('Menu mobile sedang dalam pengembangan.');
        });
    </script>
</body>
</html>
<?php if(isset($conn)) $conn->close(); ?>