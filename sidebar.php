<aside class="w-64 bg-yellow-800 text-white min-h-screen shadow-xl hidden md:block">
    <div class="p-6 text-center border-b border-yellow-700">
        <h2 class="text-2xl font-bold tracking-wider">ADMIN RM PADANG</h2>
        <p class="text-xs text-yellow-200 opacity-70 mt-1">Sistem Manajemen Transaksi</p>
    </div>
    
    <nav class="mt-6 px-4">
        <ul class="space-y-2">
            <li>
                <a href="dashboard_admin.php" class="flex items-center p-3 rounded-lg hover:bg-yellow-700 transition duration-300 <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard_admin.php' ? 'bg-yellow-900' : ''; ?>">
                    <i class="fas fa-chart-line w-6"></i>
                    <span class="ml-3">Laporan & Statistik</span>
                </a>
            </li>
            <li>
                <a href="manage_users.php" class="flex items-center p-3 rounded-lg hover:bg-yellow-700 transition duration-300 <?php echo basename($_SERVER['PHP_SELF']) == 'manage_users.php' ? 'bg-yellow-900' : ''; ?>">
                    <i class="fas fa-users w-6"></i>
                    <span class="ml-3">Manajemen User</span>
                </a>
            </li>
            <li>
                <a href="manage_menus.php" class="flex items-center p-3 rounded-lg hover:bg-yellow-700 transition duration-300 <?php echo basename($_SERVER['PHP_SELF']) == 'manage_menus.php' ? 'bg-yellow-900' : ''; ?>">
                    <i class="fas fa-utensils w-6"></i>
                    <span class="ml-3">Manajemen Menu</span>
                </a>
            </li>
            <li>
                <a href="change_password.php" class="flex items-center p-3 rounded-lg hover:bg-yellow-700 transition duration-300 <?php echo basename($_SERVER['PHP_SELF']) == 'change_password.php' ? 'bg-yellow-900' : ''; ?>">
                    <i class="fas fa-user-shield w-6"></i>
                    <span class="ml-3">Ganti Password</span>
                </a>
            </li>
            <li class="pt-4 mt-4 border-t border-yellow-700">
                <a href="logout.php" class="flex items-center p-3 rounded-lg hover:bg-red-600 transition duration-300 text-yellow-200 hover:text-white">
                    <i class="fas fa-sign-out-alt w-6"></i>
                    <span class="ml-3">Logout</span>
                </a>
            </li>
        </ul>
    </nav>
</aside>