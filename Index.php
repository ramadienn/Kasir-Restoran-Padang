<?php
session_start(); // Selalu mulai session di awal setiap file PHP yang menggunakannya
include 'koneksi.php'; // Sertakan file koneksi database Anda

// Tambahkan logika redirect di sini
if (isset($_SESSION['user_id'])) {
    // Jika sudah login, cek role-nya dan arahkan ke dashboard yang sesuai
    if ($_SESSION['role'] === 'admin') {
        header("Location: dashboard_admin.php");
        exit();
    } elseif ($_SESSION['role'] === 'petugas') {
        header("Location: dashboard_petugas.php");
        exit();
    }
}
// Jika belum login, atau jika sudah login tapi role tidak dikenali,
// biarkan halaman index.php ini tetap tampil (tidak ada redirect ke login.php di sini)
// Pengguna bisa mengklik link "Login" secara manual di navbar.

?>
<!DOCTYPE html>
<html class="scroll-smooth" lang="id">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1" name="viewport"/>
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
        }
    </style>
</head>
<body class="min-h-screen flex flex-col">
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
            </div>
        </div>
    </nav>

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
            }
            ?>
        </div>
    </section>

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