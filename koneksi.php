<?php
$host = "localhost"; // Nama host (biasanya 'localhost' untuk server lokal)
$username = "root"; // Username MySQL (default di localhost biasanya 'root')
$password = ""; // Password MySQL (default kosong di localhost)
$database = "tugas_proyek"; // Nama database yang digunakan

// Membuat koneksi ke MySQL
$conn = new mysqli($host, $username, $password, $database);

// Cek apakah koneksi berhasil
if ($conn->connect_error) {
    // Jika terjadi error, tampilkan pesan error
    die("Koneksi gagal: " . $conn->connect_error);
}

// Jika koneksi berhasil, tidak ada pesan error dan koneksi tetap terbuka
?>
