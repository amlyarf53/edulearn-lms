<?php
// Daftar kuis untuk mahasiswa (FR-09)
require __DIR__ . '/config.php';
wajib_login('mahasiswa');

// Setiap kuis + jumlah soalnya + hasil pengerjaan mahasiswa ini (kalau sudah)
$stmt = $db->prepare('SELECT k.*,
                             (SELECT COUNT(*) FROM soal s WHERE s.kuis_id = k.id) AS jumlah_soal,
                             p.id AS percobaan_id, p.nilai
                      FROM kuis k
                      LEFT JOIN percobaan_kuis p
                             ON p.kuis_id = k.id AND p.user_id = ? AND p.waktu_selesai IS NOT NULL
                      ORDER BY k.id');
$stmt->execute([user()['id']]);
$daftar = $stmt->fetchAll();

$judul = 'Kuis';
$menu  = 'kuis';
$crumb = ['kuis'];
require __DIR__ . '/includes/header.php';
?>
<h1>Daftar Kuis</h1>
<p class="sub">Pilih kuis, lalu kerjakan sebelum waktu habis.</p>

<div class="kartu">
    <h3 class="judul-tabel">Kuis tersedia <span class="pil pil-abu mono"><?= count($daftar) ?> kuis</span></h3>
    <div class="tabel-bungkus">
        <table class="tabel">
            <thead><tr><th>KUIS</th><th>SOAL</th><th>DURASI</th><th>STATUS</th><th>NILAI</th><th>AKSI</th></tr></thead>
            <tbody>
            <?php foreach ($daftar as $k): ?>
                <tr>
                    <td><span class="tebal">Kuis <?= $k['id'] ?></span><div class="ket"><?= e($k['judul']) ?></div></td>
                    <td class="mono"><?= $k['jumlah_soal'] ?></td>
                    <td class="mono"><?= $k['durasi_menit'] ?> menit</td>
                    <td>
                        <?php if ($k['percobaan_id']): ?>
                            <span class="pil pil-hijau">Sudah dikerjakan</span>
                        <?php else: ?>
                            <span class="pil pil-kuning">Belum dikerjakan</span>
                        <?php endif; ?>
                    </td>
                    <td class="tebal"><?= $k['percobaan_id'] ? nilai($k['nilai']) : '-' ?></td>
                    <td>
                        <?php if ($k['percobaan_id']): ?>
                            <a class="btn btn-garis btn-kecil" href="<?= url('kuis_hasil.php?id=' . $k['percobaan_id']) ?>">Lihat hasil</a>
                        <?php elseif ($k['jumlah_soal'] > 0): ?>
                            <a class="btn btn-primer btn-kecil" href="<?= url('kuis_kerjakan.php?id=' . $k['id']) ?>"
                               onclick="return confirm('Waktu mulai dihitung sekarang. Mulai kuis?')">Mulai kuis</a>
                        <?php else: ?>
                            <span class="ket">Belum ada soal</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$daftar): ?><tr><td colspan="6" class="kosong">Belum ada kuis.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
