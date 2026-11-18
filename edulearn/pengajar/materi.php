<?php
// Kelola materi: daftar materi untuk pengajar (FR-05)
require __DIR__ . '/../config.php';
wajib_login('pengajar');

$daftar = $db->query('SELECT * FROM materi ORDER BY id')->fetchAll();

$judul = 'Kelola Materi';
$menu  = 'materi';
$crumb = ['materi'];
require __DIR__ . '/../includes/header.php';
?>
<div class="baris-antar kepala-halaman">
    <div>
        <h1>Kelola Materi</h1>
        <p class="sub">Tambah, ubah, atau hapus materi untuk mahasiswa.</p>
    </div>
    <a class="btn btn-primer" href="<?= url('pengajar/materi_form.php') ?>">+ Tambah materi</a>
</div>

<div class="kartu">
    <h3 class="judul-tabel">Daftar materi <span class="pil pil-abu mono"><?= count($daftar) ?> materi</span></h3>
    <div class="tabel-bungkus">
        <table class="tabel">
            <thead><tr><th>NO</th><th>JUDUL MATERI</th><th>BERKAS</th><th>DIBUAT</th><th>AKSI</th></tr></thead>
            <tbody>
            <?php foreach ($daftar as $no => $m): ?>
                <tr>
                    <td class="mono"><?= sprintf('%02d', $no + 1) ?></td>
                    <td class="tebal"><?= e($m['judul']) ?></td>
                    <td class="mono">
                        <?php if ($m['file_path']): ?>
                            <span class="berkas"><?= ikon('berkas') ?> <?= e(nama_berkas($m['file_path'])) ?></span>
                        <?php else: ?>
                            <span class="ket">tanpa berkas</span>
                        <?php endif; ?>
                    </td>
                    <td class="mono"><?= tanggal($m['created_at']) ?></td>
                    <td>
                        <div class="aksi">
                            <a class="btn btn-garis btn-kecil" href="<?= url('pengajar/materi_form.php?id=' . $m['id']) ?>">Edit</a>
                            <form method="post" action="<?= url('pengajar/materi_hapus.php') ?>"
                                  onsubmit="return confirm('Hapus materi ini beserta berkasnya?')">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= $m['id'] ?>">
                                <button class="btn btn-bahaya btn-kecil" type="submit">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$daftar): ?><tr><td colspan="5" class="kosong">Belum ada materi.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
