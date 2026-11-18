<?php
// =====================================================
// config.php - pengaturan dasar aplikasi EduLearn
// Semua halaman memanggil file ini paling awal.
// =====================================================

session_start();

// --- Pengaturan database (sesuaikan dengan komputer Anda) ---
const DB_HOST = 'localhost';
const DB_NAME = 'edulearn';
const DB_USER = 'root';
const DB_PASS = '';

// --- Pengaturan aplikasi ---
const BASE_URL      = '/edulearn';                // nama folder aplikasi di htdocs
const NAMA_MATKUL   = 'Algoritma Pemrograman';
const FOLDER_UPLOAD = __DIR__ . '/uploads/';
const MAKS_BERKAS   = 5 * 1024 * 1024;            // batas unggah: 5 MB

// --- Koneksi ke MySQL memakai PDO ---
try {
    $db = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
    $db->exec("SET time_zone = '+07:00'");        // waktu Indonesia Barat
} catch (PDOException $e) {
    exit('Koneksi database gagal. Periksa pengaturan di config.php.');
}

require __DIR__ . '/includes/fungsi.php';
