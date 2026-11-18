<?php
// Rekap nilai mahasiswa per kuis (FR-12)
require __DIR__ . '/../config.php';
wajib_login('pengajar');

$semua_kuis = $db->query('SELECT id, judul FROM kuis ORDER BY id')->fetchAll();

// Kuis yang dipilih (dari ?kuis=); kalau kosong, pakai kuis pertama
$kuis_id = (int)($_GET['kuis'] ?? 0);
if (!$kuis_id && $semua_kuis) {
    $kuis_id = $semua_kuis[0]['id'];
}

// Nilai semua mahasiswa untuk kuis tersebut
$stmt = $db->prepare('SELECT u.nama, p.nilai, p.waktu_selesai
                      FROM percobaan_kuis p JOIN users u ON u.id = p.user_id
                      WHERE p.kuis_id = ? AND p.waktu_selesai IS NOT NULL
                      ORDER BY p.waktu_selesai');
$stmt->execute([$kuis_id]);
$hasil = $stmt->fetchAll();

// Statistik kelas
$jumlah = count($hasil);
$rata = $tertinggi = $terendah = 0;
if ($jumlah > 0) {
    $nilai_semua = array_column($hasil, 'nilai');
    $rata      = array_sum($nilai_semua) / $jumlah;
    $tertinggi = max($nilai_semua);
    $terendah  = min($nilai_semua);
}

$judul = 'Rekap Nilai';
$menu  = 'rekap';
$crumb = ['rekap'];
require __DIR__ . '/../includes/header.php';
?>
<h1>Rekap Nilai Mahasiswa</h1>
<p class="sub">Lihat hasil tiap mahasiswa untuk kuis yang dipilih.</p>

<?php if ($semua_kuis): ?>
<form method="get" class="kelompok pilih-kuis">
    <label for="kuis">Kuis</label>
    <select class="input" id="kuis" name="kuis" onchange="this.form.submit()">
        <?php foreach ($semua_kuis as $k): ?>
            <option value="<?= $k['id'] ?>" <?= $k['id'] == $kuis_id ? 'selected' : '' ?>>Kuis <?= $k['id'] ?>: <?= e($k['judul']) ?></option>
        <?php endforeach; ?>
    </select>
    <noscript><button class="btn btn-garis" type="submit">Tampilkan</button></noscript>
</form>
<?php endif; ?>

<div class="grid-4">
    <div class="kartu stat">
        <span class="ikon-kotak"><?= ikon('nilai') ?></span>
        <div class="label-kecil">RATA-RATA KELAS</div>
        <div class="angka"><?= $jumlah ? angka($rata, 1) : '-' ?></div>
    </div>
    <div class="kartu stat">
        <span class="ikon-kotak"><?= ikon('cek') ?></span>
        <div class="label-kecil">TERTINGGI</div>
        <div class="angka"><?= $jumlah ? nilai($tertinggi) : '-' ?></div>
    </div>
    <div class="kartu stat">
        <span class="ikon-kotak"><?= ikon('nilai') ?></span>
        <div class="label-kecil">TERENDAH</div>
        <div class="angka"><?= $jumlah ? nilai($terendah) : '-' ?></div>
    </div>
    <div class="kartu stat">
        <span class="ikon-kotak kuning"><?= ikon('user') ?></span>
        <div class="label-kecil">MAHASISWA</div>
        <div class="angka"><?= $jumlah ?></div>
    </div>
</div>

<div class="kartu">
    <h3 class="judul-tabel">Hasil mahasiswa</h3>
    <div class="tabel-bungkus">
        <table class="tabel">
            <thead><tr><th>NO</th><th>NAMA MAHASISWA</th><th>NILAI</th><th>WAKTU SELESAI</th></tr></thead>
            <tbody>
            <?php foreach ($hasil as $no => $h): ?>
                <tr>
                    <td class="mono"><?= sprintf('%02d', $no + 1) ?></td>
                    <td><span class="avatar avatar-kecil"><?= e(inisial($h['nama'])) ?></span> <span class="tebal"><?= e($h['nama']) ?></span></td>
                    <td><span class="pil pil-primer"><?= nilai($h['nilai']) ?></span></td>
                    <td class="mono"><?= tanggal_jam($h['waktu_selesai']) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$hasil): ?><tr><td colspan="4" class="kosong">Belum ada mahasiswa yang mengerjakan kuis ini.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
