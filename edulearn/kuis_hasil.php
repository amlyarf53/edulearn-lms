<?php
// Hasil kuis: nilai, jumlah benar/salah, dan ringkasan jawaban
require __DIR__ . '/config.php';
wajib_login('mahasiswa');

// Ambil percobaan. "user_id = ?" memastikan mahasiswa hanya bisa melihat hasil miliknya.
$stmt = $db->prepare('SELECT p.*, k.judul, k.durasi_menit,
                             TIMESTAMPDIFF(SECOND, p.waktu_mulai, p.waktu_selesai) AS detik
                      FROM percobaan_kuis p JOIN kuis k ON k.id = p.kuis_id
                      WHERE p.id = ? AND p.user_id = ? AND p.waktu_selesai IS NOT NULL');
$stmt->execute([(int)($_GET['id'] ?? 0), user()['id']]);
$p = $stmt->fetch();

if (!$p) {
    set_pesan('Hasil kuis tidak ditemukan.', 'galat');
    redirect('kuis.php');
}

// Jawaban per soal, urut sesuai soal
$stmt = $db->prepare('SELECT j.jawaban_dipilih, j.benar, s.kunci_jawaban
                      FROM jawaban j JOIN soal s ON s.id = j.soal_id
                      WHERE j.percobaan_id = ? ORDER BY s.id');
$stmt->execute([$p['id']]);
$daftar = $stmt->fetchAll();

$jumlah = count($daftar);
$benar  = 0;
foreach ($daftar as $j) {
    $benar += $j['benar'];
}
$salah = $jumlah - $benar;
$menit = max(1, (int)ceil($p['detik'] / 60));

$judul = 'Hasil Kuis';
$menu  = 'kuis';
$crumb = ['kuis', 'kuis-' . $p['kuis_id'], 'hasil'];
require __DIR__ . '/includes/header.php';
?>
<h1>Hasil Kuis</h1>
<p class="sub">Kuis <?= $p['kuis_id'] ?>: <?= e($p['judul']) ?></p>

<div class="grid-hasil">
    <div class="kartu stat-nilai">
        <!-- Lingkaran nilai: --p adalah persen yang diwarnai -->
        <div class="donat" style="--p: <?= (float)$p['nilai'] ?>"><span><?= nilai($p['nilai']) ?></span></div>
        <div>
            <div class="label-kecil">NILAI AKHIR</div>
            <div class="angka"><?= nilai($p['nilai']) ?> / 100</div>
            <div class="ket"><?= $benar ?> dari <?= $jumlah ?> soal benar</div>
        </div>
    </div>
    <div class="kartu stat">
        <span class="ikon-kotak hijau"><?= ikon('cek') ?></span>
        <div class="label-kecil">BENAR</div>
        <div class="angka kecil"><?= $benar ?> soal</div>
        <div class="ket">jawaban sesuai kunci</div>
    </div>
    <div class="kartu stat">
        <span class="ikon-kotak merah"><?= ikon('x') ?></span>
        <div class="label-kecil">SALAH</div>
        <div class="angka kecil"><?= $salah ?> soal</div>
        <div class="ket">jawaban tidak sesuai</div>
    </div>
    <div class="kartu stat">
        <span class="ikon-kotak"><?= ikon('jam') ?></span>
        <div class="label-kecil">WAKTU</div>
        <div class="angka kecil"><?= $menit ?> menit</div>
        <div class="ket">dari <?= $p['durasi_menit'] ?> menit</div>
    </div>
</div>

<div class="kartu">
    <div class="baris-antar judul-tabel">
        <h3>Ringkasan jawaban</h3>
        <div class="aksi">
            <a class="btn btn-garis btn-kecil" href="<?= url('dashboard.php') ?>">Lihat dashboard</a>
            <a class="btn btn-primer btn-kecil" href="<?= url('kuis.php') ?>">Daftar kuis</a>
        </div>
    </div>
    <div class="tabel-bungkus">
        <table class="tabel">
            <thead><tr><th>NO</th><th>JAWABAN ANDA</th><th>KUNCI JAWABAN</th><th>STATUS</th></tr></thead>
            <tbody>
            <?php foreach ($daftar as $no => $j): ?>
                <tr>
                    <td class="mono"><?= sprintf('%02d', $no + 1) ?></td>
                    <td class="mono tebal"><?= e($j['jawaban_dipilih'] ?? '-') ?></td>
                    <td class="mono tebal"><?= e($j['kunci_jawaban']) ?></td>
                    <td><span class="pil <?= $j['benar'] ? 'pil-hijau' : 'pil-merah' ?>"><?= $j['benar'] ? 'Benar' : 'Salah' ?></span></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
