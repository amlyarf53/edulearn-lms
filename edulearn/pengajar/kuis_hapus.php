<?php
// Menghapus kuis (FR-07). Soal, percobaan, dan jawaban ikut terhapus otomatis (ON DELETE CASCADE).
require __DIR__ . '/../config.php';
wajib_login('pengajar');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('pengajar/kuis.php');
}
csrf_cek();

$db->prepare('DELETE FROM kuis WHERE id = ?')->execute([(int)($_POST['id'] ?? 0)]);
set_pesan('Kuis berhasil dihapus.');
redirect('pengajar/kuis.php');
