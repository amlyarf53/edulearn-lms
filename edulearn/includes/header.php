<?php
// Kerangka halaman setelah login: sidebar + topbar.
// Variabel yang diisi oleh halaman pemanggil:
//   $judul = judul tab, $menu = menu yang aktif, $crumb = jejak halaman (array)
$u = user();

if ($u['role'] === 'pengajar') {
    $daftar_menu = [
        ['materi', 'Kelola Materi', 'pengajar/materi.php', 'buku'],
        ['kuis',   'Kelola Kuis',   'pengajar/kuis.php',   'kuis'],
        ['rekap',  'Rekap Nilai',   'pengajar/rekap.php',  'nilai'],
    ];
} else {
    $daftar_menu = [
        ['dashboard', 'Dashboard', 'dashboard.php',          'dashboard'],
        ['materi',    'Materi',    'materi.php',             'buku'],
        ['kuis',      'Kuis',      'kuis.php',               'kuis'],
        ['nilai',     'Nilai',     'dashboard.php#riwayat',  'nilai'],
    ];
}
$pesan = ambil_pesan();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($judul) ?> - EduLearn</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">
</head>
<body>
<div class="app">

    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="merek">
            <div class="logo">E</div>
            <div><strong>EduLearn</strong><small>lms v1.0</small></div>
        </div>

        <div class="label-menu">MENU</div>
        <nav>
            <?php foreach ($daftar_menu as [$kode, $nama, $tujuan, $ikon]): ?>
                <a href="<?= url($tujuan) ?>" class="menu <?= $kode === $menu ? 'aktif' : '' ?>">
                    <?= ikon($ikon) ?> <?= e($nama) ?>
                </a>
            <?php endforeach; ?>
        </nav>

        <div class="sidebar-bawah">
            <a href="<?= url('logout.php') ?>" class="menu"><?= ikon('keluar') ?> Keluar</a>
            <div class="profil">
                <div class="avatar avatar-besar"><?= e(inisial($u['nama'])) ?></div>
                <div><strong><?= e($u['nama']) ?></strong><small>role: <?= e($u['role']) ?></small></div>
            </div>
        </div>
    </aside>
    <div class="tirai" id="tirai"></div>

    <!-- Bagian kanan: topbar + isi halaman -->
    <div class="utama">
        <header class="topbar">
            <button class="tombol-menu" id="tombolMenu" aria-label="Buka menu"><?= ikon('menu') ?></button>
            <div class="crumb">
                edulearn
                <?php foreach ($crumb as $i => $c): ?>
                    / <?= $i === count($crumb) - 1 ? '<b>' . e($c) . '</b>' : e($c) ?>
                <?php endforeach; ?>
            </div>
            <span class="pil pil-abu sembunyi-kecil"><?= e(NAMA_MATKUL) ?></span>
            <span class="pil pil-primer"><?= e($u['role']) ?></span>
            <div class="avatar"><?= e(inisial($u['nama'])) ?></div>
        </header>

        <main class="isi">
            <?php if ($pesan): ?>
                <div class="pesan pesan-<?= e($pesan[0]) ?>"><?= e($pesan[1]) ?></div>
            <?php endif; ?>
