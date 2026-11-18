<?php
// Kerangka halaman login dan registrasi (panel gelap di kiri, formulir di kanan).
// Variabel dari halaman: $judul, $headline, $deskripsi
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
<body class="auth">

    <section class="auth-kiri">
        <div class="merek">
            <div class="logo">E</div>
            <div><strong>EduLearn</strong><small>learning management system</small></div>
        </div>

        <div class="auth-tengah">
            <h2><?= $headline ?></h2>
            <p><?= e($deskripsi) ?></p>

            <div class="terminal">
                <div class="terminal-bar"><i class="m"></i><i class="k"></i><i class="h"></i><span>bash</span></div>
                <pre><span class="biru">$ edulearn --cek</span>
[ ok ] materi  : tersedia
[ ok ] kuis    : tersedia
<span class="hijau">[ ok ] nilai   : dihitung otomatis</span>
<span class="biru">$ _</span></pre>
            </div>
        </div>

        <small class="hak-cipta">&copy; <?= date('Y') ?> EduLearn</small>
    </section>

    <section class="auth-kanan">
        <div class="auth-kotak">
