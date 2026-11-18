<?php
// Daftar materi untuk mahasiswa (FR-06) + tanda sudah dipelajari (FR-13)
require __DIR__ . '/config.php';
wajib_login('mahasiswa');

// Ambil semua materi beserta status "selesai" milik mahasiswa ini
$stmt = $db->prepare('SELECT m.*, COALESCE(p.selesai, 0) AS selesai
                      FROM materi m
                      LEFT JOIN progres_materi p ON p.materi_id = m.id AND p.user_id = ?
                      ORDER BY m.id');
$stmt->execute([user()['id']]);
$daftar = $stmt->fetchAll();

$total   = count($daftar);
$selesai = 0;
foreach ($daftar as $m) {
    $selesai += $m['selesai'];
}
$persen = $total ? round($selesai / $total * 100) : 0;

$judul = 'Materi';
$menu  = 'materi';
$crumb = ['materi'];
require __DIR__ . '/includes/header.php';
?>
<h1>Materi Pembelajaran</h1>
<p class="sub">Buka materi, unduh berkasnya, lalu tandai jika sudah dipelajari.</p>

<div class="kartu progres">
    <div>
        <strong>Progres belajar</strong>
        <div class="mono ket"><?= $selesai ?> dari <?= $total ?> materi selesai</div>
    </div>
    <div class="bar"><span style="width: <?= $persen ?>%"></span></div>
    <strong><?= $persen ?>%</strong>
</div>

<div class="grid-3">
    <?php foreach ($daftar as $no => $m): ?>
        <div class="kartu kartu-materi">
            <div class="baris-antar">
                <span class="ikon-kotak"><?= ikon('berkas') ?></span>
                <span class="pil pil-abu mono"><?= e(jenis_berkas($m['file_path'])) ?></span>
            </div>
            <div class="label-kecil biru">MATERI <?= sprintf('%02d', $no + 1) ?></div>
            <h3><?= e($m['judul']) ?></h3>
            <p class="ket ringkas"><?= e(strtok($m['isi_teks'], "\n")) ?></p>

            <!-- Kotak centang: otomatis terkirim saat diklik -->
            <form method="post" action="<?= url('materi_tandai.php') ?>" class="centang">
                <?= csrf_field() ?>
                <input type="hidden" name="materi_id" value="<?= $m['id'] ?>">
                <input type="hidden" name="balik" value="materi.php">
                <label>
                    <input type="checkbox" name="selesai" value="1" <?= $m['selesai'] ? 'checked' : '' ?> onchange="this.form.submit()">
                    <span class="kotak"><?= ikon('cek') ?></span> Tandai sudah dipelajari
                </label>
            </form>

            <div class="aksi">
                <a class="btn btn-primer" href="<?= url('materi_baca.php?id=' . $m['id']) ?>">Buka materi</a>
                <?php if ($m['file_path']): ?>
                    <a class="btn btn-garis" href="<?= url('unduh.php?id=' . $m['id']) ?>">Unduh</a>
                <?php else: ?>
                    <span class="btn btn-garis mati">Unduh</span>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php if (!$daftar): ?><p class="kosong">Belum ada materi.</p><?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
