<?php
echo password_hash('admin123', PASSWORD_DEFAULT); // Ini akan menghasilkan hash untuk 'admin123'
echo "<br>";
echo password_hash('petugas123', PASSWORD_DEFAULT); // Ini akan menghasilkan hash untuk 'petugas123'
?>