<?php
// Halaman isi satu materi
require __DIR__ . '/config.php';
wajib_login('mahasiswa');

$stmt = $db->prepare('SELECT m.*, COALESCE(p.selesai, 0) AS selesai
                      FROM materi m
                      LEFT JOIN progres_materi p ON p.materi_id = m.id AND p.user_id = ?
                      WHERE m.id = ?');
$stmt->execute([user()['id'], (int)($_GET['id'] ?? 0)]);
$m = $stmt->fetch();

if (!$m) {
    set_pesan('Materi tidak ditemukan.', 'galat');
    redirect('materi.php');
}

$judul = $m['judul'];
$menu  = 'materi';
$crumb = ['materi', 'baca'];
require __DIR__ . '/includes/header.php';
?>
<h1><?= e($m['judul']) ?></h1>
<p class="sub">Dibuat pada <?= tanggal($m['created_at']) ?></p>

<div class="kartu isi-kartu">
    <div class="teks-materi"><?= nl2br(e($m['isi_teks'])) ?></div>

    <form method="post" action="<?= url('materi_tandai.php') ?>" class="centang">
        <?= csrf_field() ?>
        <input type="hidden" name="materi_id" value="<?= $m['id'] ?>">
        <input type="hidden" name="balik" value="materi_baca.php?id=<?= $m['id'] ?>">
        <label>
            <input type="checkbox" name="selesai" value="1" <?= $m['selesai'] ? 'checked' : '' ?> onchange="this.form.submit()">
            <span class="kotak"><?= ikon('cek') ?></span> Tandai sudah dipelajari
        </label>
    </form>

    <div class="aksi">
        <?php if ($m['file_path']): ?>
            <a class="btn btn-primer" href="<?= url('unduh.php?id=' . $m['id']) ?>">Unduh <?= e(nama_berkas($m['file_path'])) ?></a>
        <?php endif; ?>
        <a class="btn btn-garis" href="<?= url('materi.php') ?>">Kembali</a>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
