<?php
// Menghapus materi (FR-05). Hanya menerima kiriman POST.
require __DIR__ . '/../config.php';
wajib_login('pengajar');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('pengajar/materi.php');
}
csrf_cek();

$id = (int)($_POST['id'] ?? 0);
$stmt = $db->prepare('SELECT file_path FROM materi WHERE id = ?');
$stmt->execute([$id]);
$m = $stmt->fetch();

if ($m) {
    // Hapus berkas di folder uploads, lalu hapus barisnya (progres ikut terhapus otomatis)
    if ($m['file_path'] && is_file(FOLDER_UPLOAD . basename($m['file_path']))) {
        unlink(FOLDER_UPLOAD . basename($m['file_path']));
    }
    $db->prepare('DELETE FROM materi WHERE id = ?')->execute([$id]);
    set_pesan('Materi berhasil dihapus.');
}
redirect('pengajar/materi.php');
