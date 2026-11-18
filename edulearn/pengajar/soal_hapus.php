<?php
// Menghapus satu soal (FR-08)
require __DIR__ . '/../config.php';
wajib_login('pengajar');

$kuis_id = (int)($_POST['kuis'] ?? 0);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('pengajar/kuis.php');
}
csrf_cek();

$db->prepare('DELETE FROM soal WHERE id = ? AND kuis_id = ?')->execute([(int)($_POST['id'] ?? 0), $kuis_id]);
set_pesan('Soal berhasil dihapus.');
redirect('pengajar/kuis_soal.php?id=' . $kuis_id);
