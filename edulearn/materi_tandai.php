<?php
// Menyimpan tanda "sudah dipelajari" (FR-13). Hanya menerima kiriman POST.
require __DIR__ . '/config.php';
wajib_login('mahasiswa');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('materi.php');
}
csrf_cek();

$materi_id = (int)($_POST['materi_id'] ?? 0);
$selesai   = isset($_POST['selesai']) ? 1 : 0;      // kotak dicentang = 1

// Pastikan materinya ada
$stmt = $db->prepare('SELECT id FROM materi WHERE id = ?');
$stmt->execute([$materi_id]);

if ($stmt->fetch()) {
    // Belum ada catatan -> tambah. Sudah ada -> perbarui (karena UNIQUE user_id + materi_id)
    $stmt = $db->prepare('INSERT INTO progres_materi (user_id, materi_id, selesai, tanggal)
                          VALUES (?, ?, ?, NOW())
                          ON DUPLICATE KEY UPDATE selesai = VALUES(selesai), tanggal = NOW()');
    $stmt->execute([user()['id'], $materi_id, $selesai]);
}

// Kembali ke halaman asal (hanya dua tujuan yang diizinkan)
$balik = $_POST['balik'] ?? 'materi.php';
if (strpos($balik, 'materi_baca.php?id=') !== 0) {
    $balik = 'materi.php';
}
redirect($balik);
