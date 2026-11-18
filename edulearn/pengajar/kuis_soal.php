<?php
// Daftar soal dalam satu kuis (FR-08)
require __DIR__ . '/../config.php';
wajib_login('pengajar');

$kuis_id = (int)($_GET['id'] ?? 0);
$stmt = $db->prepare('SELECT * FROM kuis WHERE id = ?');
$stmt->execute([$kuis_id]);
$kuis = $stmt->fetch();
if (!$kuis) {
    set_pesan('Kuis tidak ditemukan.', 'galat');
    redirect('pengajar/kuis.php');
}

$stmt = $db->prepare('SELECT * FROM soal WHERE kuis_id = ? ORDER BY id');
$stmt->execute([$kuis_id]);
$daftar = $stmt->fetchAll();

$judul = 'Soal Kuis ' . $kuis['id'];
$menu  = 'kuis';
$crumb = ['kuis', 'kuis-' . $kuis['id'], 'soal'];
require __DIR__ . '/../includes/header.php';
?>
<div class="baris-antar kepala-halaman">
    <div>
        <h1>Kelola Soal</h1>
        <p class="sub">Kuis <?= $kuis['id'] ?>: <?= e($kuis['judul']) ?></p>
    </div>
    <a class="btn btn-primer" href="<?= url('pengajar/soal_form.php?kuis=' . $kuis['id']) ?>">+ Tambah soal</a>
</div>

<div class="kartu">
    <h3 class="judul-tabel">Daftar soal <span class="pil pil-abu mono"><?= count($daftar) ?> soal</span></h3>
    <div class="tabel-bungkus">
        <table class="tabel">
            <thead><tr><th>NO</th><th>PERTANYAAN</th><th>KUNCI</th><th>AKSI</th></tr></thead>
            <tbody>
            <?php foreach ($daftar as $no => $s): ?>
                <tr>
                    <td class="mono"><?= sprintf('%02d', $no + 1) ?></td>
                    <td><?= e(mb_strimwidth($s['pertanyaan'], 0, 90, '...')) ?></td>
                    <td><span class="pil pil-primer mono"><?= e($s['kunci_jawaban']) ?></span></td>
                    <td>
                        <div class="aksi">
                            <a class="btn btn-garis btn-kecil" href="<?= url('pengajar/soal_form.php?kuis=' . $kuis['id'] . '&id=' . $s['id']) ?>">Edit</a>
                            <form method="post" action="<?= url('pengajar/soal_hapus.php') ?>" onsubmit="return confirm('Hapus soal ini?')">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= $s['id'] ?>">
                                <input type="hidden" name="kuis" value="<?= $kuis['id'] ?>">
                                <button class="btn btn-bahaya btn-kecil" type="submit">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$daftar): ?><tr><td colspan="4" class="kosong">Belum ada soal. Klik "+ Tambah soal".</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<p><a class="tautan" href="<?= url('pengajar/kuis.php') ?>">&larr; Kembali ke daftar kuis</a></p>
<?php require __DIR__ . '/../includes/footer.php'; ?>
