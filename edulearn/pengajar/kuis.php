<?php
// Kelola kuis: daftar kuis untuk pengajar (FR-07)
require __DIR__ . '/../config.php';
wajib_login('pengajar');

$daftar = $db->query('SELECT k.*, (SELECT COUNT(*) FROM soal s WHERE s.kuis_id = k.id) AS jumlah_soal
                      FROM kuis k ORDER BY k.id')->fetchAll();

$judul = 'Kelola Kuis';
$menu  = 'kuis';
$crumb = ['kuis'];
require __DIR__ . '/../includes/header.php';
?>
<div class="baris-antar kepala-halaman">
    <div>
        <h1>Kelola Kuis</h1>
        <p class="sub">Buat kuis, atur durasinya, lalu tambahkan soal.</p>
    </div>
    <a class="btn btn-primer" href="<?= url('pengajar/kuis_form.php') ?>">+ Tambah kuis</a>
</div>

<div class="kartu">
    <h3 class="judul-tabel">Daftar kuis <span class="pil pil-abu mono"><?= count($daftar) ?> kuis</span></h3>
    <div class="tabel-bungkus">
        <table class="tabel">
            <thead><tr><th>NO</th><th>KUIS</th><th>JUMLAH SOAL</th><th>DURASI</th><th>AKSI</th></tr></thead>
            <tbody>
            <?php foreach ($daftar as $no => $k): ?>
                <tr>
                    <td class="mono"><?= sprintf('%02d', $no + 1) ?></td>
                    <td><span class="tebal">Kuis <?= $k['id'] ?></span><div class="ket"><?= e($k['judul']) ?></div></td>
                    <td class="mono"><?= $k['jumlah_soal'] ?> soal</td>
                    <td class="mono"><?= $k['durasi_menit'] ?> menit</td>
                    <td>
                        <div class="aksi">
                            <a class="btn btn-lembut btn-kecil" href="<?= url('pengajar/kuis_soal.php?id=' . $k['id']) ?>">Kelola soal</a>
                            <a class="btn btn-garis btn-kecil" href="<?= url('pengajar/kuis_form.php?id=' . $k['id']) ?>">Edit</a>
                            <form method="post" action="<?= url('pengajar/kuis_hapus.php') ?>"
                                  onsubmit="return confirm('Hapus kuis ini beserta soal dan nilai mahasiswa?')">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= $k['id'] ?>">
                                <button class="btn btn-bahaya btn-kecil" type="submit">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$daftar): ?><tr><td colspan="5" class="kosong">Belum ada kuis.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
