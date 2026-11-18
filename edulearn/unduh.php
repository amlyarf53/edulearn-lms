<?php
// Mengunduh berkas materi (FR-06).
// Berkas disimpan di folder uploads/ yang tidak bisa dibuka langsung,
// jadi semua unduhan harus lewat halaman ini (wajib login).
require __DIR__ . '/config.php';
wajib_login();

$stmt = $db->prepare('SELECT file_path FROM materi WHERE id = ?');
$stmt->execute([(int)($_GET['id'] ?? 0)]);
$m = $stmt->fetch();

$lokasi = $m && $m['file_path'] ? FOLDER_UPLOAD . basename($m['file_path']) : '';

if (!$lokasi || !is_file($lokasi)) {
    http_response_code(404);
    exit('Berkas tidak ditemukan.');
}

header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . nama_berkas($m['file_path']) . '"');
header('Content-Length: ' . filesize($lokasi));
readfile($lokasi);
