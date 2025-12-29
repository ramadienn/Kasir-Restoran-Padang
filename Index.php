<?php
<<<<<<< HEAD
session_start();
include 'koneksi.php';

if (isset($_SESSION['user_id'])) {
=======
session_start(); // Selalu mulai session di awal setiap file PHP yang menggunakannya
include 'koneksi.php'; // Sertakan file koneksi database Anda

// Tambahkan logika redirect di sini
if (isset($_SESSION['user_id'])) {
    // Jika sudah login, cek role-nya dan arahkan ke dashboard yang sesuai
>>>>>>> 3d33e59de29485dbc5c0a3fa748595769751f536
    if ($_SESSION['role'] === 'admin') {
        header("Location: dashboard_admin.php");
        exit();
    } elseif ($_SESSION['role'] === 'petugas') {
        header("Location: dashboard_petugas.php");
        exit();
    }
}
<<<<<<< HEAD
=======
// Jika belum login, atau jika sudah login tapi role tidak dikenali,
// biarkan halaman index.php ini tetap tampil (tidak ada redirect ke login.php di sini)
// Pengguna bisa mengklik link "Login" secara manual di navbar.

>>>>>>> 3d33e59de29485dbc5c0a3fa748595769751f536
?>
<!DOCTYPE html>
<html class="scroll-smooth" lang="id">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1" name="viewport"/>
<<<<<<< HEAD
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
            background-color: #0f172a; /* Warna dasar gelap jika gambar gagal muat */
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

        /* --- NAVBAR --- */
        .navbar-custom {
            background: rgba(15, 23, 42, 0.8);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* --- CARDS & BUTTONS --- */
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

        /* Hero Image Animation */
        .hero-img {
            animation: float 6s ease-in-out infinite;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
=======
    <title>
        Rumah Makan Padang - Beranda
    </title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Roboto&amp;display=swap" rel="stylesheet"/>
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            /* PROPERTI UNTUK WALLPAPER KESELURUHAN HALAMAN */
            background-image: url('assets/images/restoran_wallpaper.jpg'); /* GANTI PATH INI dengan lokasi gambar Anda */
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed; /* Membuat efek paralaks */
            color: #333; /* Default warna teks gelap */
        }

        /* Overlay gelap untuk meningkatkan keterbacaan teks di atas wallpaper */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(247, 152, 0, 0.45); /* Hitam dengan opacity 45%. Coba sesuaikan nilai ini! */
            z-index: -1;
        }

        /* Navbar: Sedikit transparan agar wallpaper sedikit terlihat */
        .navbar {
            background-color: rgba(180, 83, 9, 0.85); /* Kuning tua original dengan opacity 85% */
        }
        .navbar a {
            color: white; /* Pastikan link di navbar tetap putih */
        }

        /* Hero Section (Selamat Datang): Latar belakang putih semi-transparan dengan blur */
        .hero-section {
            background-color: rgba(255, 255, 255, 0.8); /* Putih dengan opacity 80% */
            backdrop-filter: blur(5px); /* Efek blur pada background di belakang elemen ini */
            -webkit-backdrop-filter: blur(5px); /* Untuk kompatibilitas browser lama */
        }

        /* Section lain (Menu, Tentang, Kontak): Latar belakang putih semi-transparan */
        .content-section {
            background-color: rgba(255, 255, 255, 0.9); /* Putih dengan opacity 90% */
            border-radius: 0.5rem; /* Tambahkan sedikit border-radius */
            padding: 2.5rem; /* Tambahkan padding agar tidak terlalu mepet */
            margin-top: 2.5rem; /* Beri jarak antar section */
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); /* Tambahkan bayangan */
        }

        /* Card Menu di Section Menu Andalan: Latar belakang putih semi-transparan */
        .menu-card {
            background-color: rgba(255, 255, 255, 0.95); /* Putih dengan opacity 95% */
            border-radius: 0.5rem;
            overflow: hidden; /* Penting untuk gambar di dalam card */
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        /* Warna Teks: Pastikan kontras dengan background */
        h1, h2, h3 {
            color: #d97706; /* Kuning tua original */
        }
        p, span.font-bold {
            color: #000000ff; /* Abu-abu gelap */
        }
        a.bg-yellow-600 {
            background-color: #d97706; /* Kuning tua */
            color: white;
        }
        a.bg-yellow-600:hover {
            background-color: #b45309; /* Lebih gelap saat hover */
        }

        /* Footer: Warna yang sama dengan navbar atau menyesuaikan */
        .footer {
            background-color: rgba(180, 83, 9, 0.85); /* Sama dengan navbar */
            color: white;
>>>>>>> 3d33e59de29485dbc5c0a3fa748595769751f536
        }
    </style>
</head>
<body class="min-h-screen flex flex-col">
<<<<<<< HEAD

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
=======
    <nav class="navbar text-white shadow-md">
        <div class="container mx-auto px-4 py-4 flex justify-between items-center">
            <div class="text-2xl font-bold flex items-center space-x-2">
                <img alt="Logo Rumah Makan Padang, a traditional Indonesian restaurant logo with rice and chili pepper" class="rounded-full" height="40" src="assets/img/navbar.jpg" width="40"/>
                <span>
                    Rumah Makan Padang
                </span>
            </div>
            <div>
                <a class="hover:underline px-3 font-semibold" href="#menu">
                    Menu
                </a>
                <a class="hover:underline px-3 font-semibold" href="#about">
                    Tentang
                </a>
                <a class="hover:underline px-3 font-semibold" href="#contact">
                    Kontak
                </a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <a class="hover:underline px-3 font-semibold" href="dashboard_admin.php">
                            Dashboard Admin
                        </a>
                    <?php elseif ($_SESSION['role'] === 'petugas'): ?>
                        <a class="hover:underline px-3 font-semibold" href="dashboard_petugas.php">
                            Dashboard Petugas
                        </a>
                    <?php endif; ?>
                    <a class="hover:underline px-3 font-semibold" href="logout.php">
                        Logout
                    </a>
                <?php else: ?>
                    <a class="hover:underline px-3 font-semibold" href="login.php">
                        Login
                    </a>
                <?php endif; ?>
>>>>>>> 3d33e59de29485dbc5c0a3fa748595769751f536
            </div>
        </div>
    </nav>

<<<<<<< HEAD
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
=======
    <section class="hero-section flex flex-col md:flex-row items-center justify-center gap-8 py-12 px-6 md:px-20">
        <div class="md:w-1/2 text-center md:text-left">
            <h1 class="text-4xl md:text-5xl font-extrabold text-yellow-800 mb-4">
                Selamat Datang di Rumah Makan Padang
            </h1>
            <p class="text-yellow-700 text-lg mb-2">
                Nikmati cita rasa asli masakan Padang dengan bahan segar dan resep turun-temurun.
            </p>
            <p class="text-yellow-700 text-base mb-6">
                Untuk pemesanan, silakan datang langsung ke kasir kami!
            </p>
            <a class="inline-block bg-yellow-600 text-white px-6 py-3 rounded-lg font-semibold shadow-md hover:bg-yellow-700 transition" href="#menu">
                Lihat Menu
            </a>
        </div>
        <div class="md:w-1/2">
            <img alt="Piring berisi masakan Padang lengkap dengan rendang, gulai, dan sayur daun singkong, disajikan di atas meja kayu" class="rounded-lg shadow-lg mx-auto md:mx-0" height="400" src="assets/img/Halaman.jpg" width="600"/>
        </div>
    </section>

    <section class="container mx-auto px-6 py-12 content-section" id="menu">
        <h2 class="text-3xl font-bold text-yellow-800 mb-8 text-center">
            Menu Andalan
        </h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-8">
            <?php
            // Ambil data menu dari database
            $sql_menus = "SELECT nama_menu, deskripsi, harga, gambar_url FROM menus ORDER BY id ASC";
            $result_menus = $conn->query($sql_menus);

            if ($result_menus->num_rows > 0) {
                // Tampilkan setiap menu
                while($row = $result_menus->fetch_assoc()) {
                    echo '<div class="menu-card rounded-lg shadow-md overflow-hidden">';
                    echo '  <img alt="' . htmlspecialchars($row["nama_menu"]) . '" class="w-full h-48 object-cover" src="' . htmlspecialchars($row["gambar_url"]) . '" />';
                    echo '  <div class="p-4">';
                    echo '    <h3 class="text-xl font-semibold text-yellow-900 mb-2">' . htmlspecialchars($row["nama_menu"]) . '</h3>';
                    echo '    <p class="text-yellow-700 mb-3">' . htmlspecialchars($row["deskripsi"]) . '</p>';
                    echo '    <span class="font-bold text-yellow-800">Rp ' . number_format($row["harga"], 0, ',', '.') . '</span>';
                    echo '  </div>';
                    echo '</div>';
                }
            } else {
                echo "<p class='col-span-full text-center text-yellow-700'>Belum ada menu yang tersedia.</p>";
>>>>>>> 3d33e59de29485dbc5c0a3fa748595769751f536
            }
            ?>
        </div>
    </section>

<<<<<<< HEAD
    <footer class="navbar-custom py-12 text-center text-white mt-auto border-t border-white/5">
        <div class="container mx-auto px-6">
            <h2 class="text-2xl font-brand font-bold text-yellow-500 mb-4">RM PADANG</h2>
            <p class="text-gray-500 text-xs md:text-sm uppercase tracking-[0.3em] mb-8"> &copy; 2026</p>
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
            }, 3500);
        });

        const btn = document.getElementById('menu-btn');
        btn.addEventListener('click', () => {
            alert('Menu navigasi dapat Anda tambahkan di sini untuk versi mobile.');
        });
    </script>
</body>
</html>
<?php if(isset($conn)) $conn->close(); ?>
=======
    <section class="py-12 px-6 md:px-20 content-section" id="about">
        <div class="container mx-auto max-w-5xl text-center">
            <h2 class="text-3xl font-bold text-yellow-800 mb-6">
                Tentang Kami
            </h2>
            <p class="text-yellow-700 text-lg leading-relaxed">
                Rumah Makan Padang kami berdiri sejak tahun 2025, menyajikan masakan Padang asli dengan resep turun-temurun dari keluarga. Kami berkomitmen menggunakan bahan segar dan rempah pilihan untuk memberikan cita rasa terbaik bagi pelanggan.
            </p>
        </div>
    </section>

    <section class="container mx-auto px-6 py-12 content-section" id="contact">
        <h2 class="text-3xl font-bold text-yellow-800 mb-8 text-center">
            Kontak Kami
        </h2>
        <div class="max-w-3xl mx-auto bg-white rounded-lg shadow-md p-8">
            <div class="flex items-center mb-6">
                <i class="fas fa-map-marker-alt text-yellow-600 text-2xl mr-4">
                </i>
                <p class="text-yellow-800 text-lg">
                    Jl. TANGERANG SELATAN
                </p>
            </div>
            <div class="flex items-center mb-6">
                <i class="fas fa-phone-alt text-yellow-600 text-2xl mr-4">
                </i>
                <p class="text-yellow-800 text-lg">
                    +62 812 3456 7890
                </p>
            </div>
            <div class="flex items-center">
                <i class="fas fa-envelope text-yellow-600 text-2xl mr-4">
                </i>
                <p class="text-yellow-800 text-lg">
                    info@rumahmakanpadang.com
                </p>
            </div>
        </div>
    </section>

    <footer class="footer py-4 text-center">
        © 2025 Muhammad Ramadien Rizky Darmawan. All rights reserved.
    </footer>
</body>
</html>
<?php
$conn->close(); // Tutup koneksi database di akhir file setelah semua query selesai
?>
>>>>>>> 3d33e59de29485dbc5c0a3fa748595769751f536
