<?php
// Dashboard nilai dan progres mahasiswa (FR-11)
require __DIR__ . '/config.php';
wajib_login('mahasiswa');
$id_user = user()['id'];

// Progres materi: berapa materi yang sudah ditandai selesai
$total_materi = $db->query('SELECT COUNT(*) FROM materi')->fetchColumn();
$stmt = $db->prepare('SELECT COUNT(*) FROM progres_materi WHERE user_id = ? AND selesai = 1');
$stmt->execute([$id_user]);
$materi_selesai = $stmt->fetchColumn();

// Riwayat kuis yang sudah dikerjakan
$stmt = $db->prepare('SELECT p.id, p.nilai, p.waktu_selesai, k.id AS kuis_id, k.judul
                      FROM percobaan_kuis p JOIN kuis k ON k.id = p.kuis_id
                      WHERE p.user_id = ? AND p.waktu_selesai IS NOT NULL
                      ORDER BY p.waktu_selesai');
$stmt->execute([$id_user]);
$riwayat = $stmt->fetchAll();

$jumlah_selesai = count($riwayat);
$rata_rata = 0;
foreach ($riwayat as $r) {
    $rata_rata += $r['nilai'];
}
if ($jumlah_selesai > 0) {
    $rata_rata = $rata_rata / $jumlah_selesai;
}

// Kuis yang belum dikerjakan
$stmt = $db->prepare('SELECT k.id, k.judul FROM kuis k
                      LEFT JOIN percobaan_kuis p ON p.kuis_id = k.id AND p.user_id = ? AND p.waktu_selesai IS NOT NULL
                      WHERE p.id IS NULL ORDER BY k.id');
$stmt->execute([$id_user]);
$kuis_belum = $stmt->fetchAll();
$total_kuis = $jumlah_selesai + count($kuis_belum);

// Materi berikutnya yang belum dipelajari
$stmt = $db->prepare('SELECT m.id, m.judul FROM materi m
                      LEFT JOIN progres_materi p ON p.materi_id = m.id AND p.user_id = ?
                      WHERE p.selesai IS NULL OR p.selesai = 0
                      ORDER BY m.id LIMIT 1');
$stmt->execute([$id_user]);
$materi_next = $stmt->fetch();

$judul = 'Dashboard';
$menu  = 'dashboard';
$crumb = ['dashboard'];
require __DIR__ . '/includes/header.php';
?>
<h1>Dashboard Nilai</h1>
<p class="sub">Ringkasan belajar Anda di mata kuliah <?= e(NAMA_MATKUL) ?>.</p>

<!-- Empat kartu ringkasan -->
<div class="grid-4">
    <div class="kartu stat">
        <span class="ikon-kotak"><?= ikon('buku') ?></span>
        <div class="label-kecil">PROGRES MATERI</div>
        <div class="angka"><?= $materi_selesai ?>/<?= $total_materi ?></div>
        <div class="ket"><?= $materi_selesai ?> dari <?= $total_materi ?> materi dipelajari</div>
    </div>
    <div class="kartu stat">
        <span class="ikon-kotak teal"><?= ikon('nilai') ?></span>
        <div class="label-kecil">RATA-RATA NILAI</div>
        <div class="angka"><?= $jumlah_selesai ? angka($rata_rata, 0) : '-' ?></div>
        <div class="ket">dari <?= $jumlah_selesai ?> kuis yang selesai</div>
    </div>
    <div class="kartu stat">
        <span class="ikon-kotak hijau"><?= ikon('cek') ?></span>
        <div class="label-kecil">KUIS SELESAI</div>
        <div class="angka"><?= $jumlah_selesai ?></div>
        <div class="ket">dari <?= $total_kuis ?> kuis tersedia</div>
    </div>
    <div class="kartu stat">
        <span class="ikon-kotak kuning"><?= ikon('kuis') ?></span>
        <div class="label-kecil">KUIS BERIKUTNYA</div>
        <div class="angka"><?= count($kuis_belum) ?></div>
        <div class="ket">
            <?= $kuis_belum ? 'Kuis ' . $kuis_belum[0]['id'] . ': ' . e($kuis_belum[0]['judul']) : 'Semua kuis sudah dikerjakan' ?>
        </div>
    </div>
</div>

<div class="grid-2">
    <!-- Grafik batang nilai per kuis (dibuat dengan CSS biasa) -->
    <div class="kartu isi-kartu">
        <h3>Nilai per kuis</h3>
        <p class="ket">Skala 0 sampai 100</p>
        <?php if ($riwayat): ?>
            <div class="grafik">
                <div class="grafik-y"><span>100</span><span>50</span><span>0</span></div>
                <div class="grafik-isi">
                    <div class="grafik-batang" style="--n: <?= count($riwayat) ?>">
                        <?php foreach ($riwayat as $r): ?>
                            <div class="batang" style="height: <?= (float)$r['nilai'] ?>%"><b><?= nilai($r['nilai']) ?></b></div>
                        <?php endforeach; ?>
                    </div>
                    <div class="grafik-label" style="--n: <?= count($riwayat) ?>">
                        <?php foreach ($riwayat as $r): ?><span>Kuis <?= $r['kuis_id'] ?></span><?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <p class="kosong">Belum ada kuis yang dikerjakan.</p>
        <?php endif; ?>
    </div>

    <!-- Tabel riwayat kuis -->
    <div class="kartu" id="riwayat">
        <h3 class="judul-tabel">Riwayat kuis</h3>
        <div class="tabel-bungkus">
            <table class="tabel">
                <thead><tr><th>KUIS</th><th>NILAI</th><th>TANGGAL</th></tr></thead>
                <tbody>
                <?php foreach ($riwayat as $r): ?>
                    <tr>
                        <td>
                            <a class="tebal" href="<?= url('kuis_hasil.php?id=' . $r['id']) ?>">Kuis <?= $r['kuis_id'] ?></a>
                            <div class="ket"><?= e($r['judul']) ?></div>
                        </td>
                        <td><span class="pil pil-primer"><?= nilai($r['nilai']) ?></span></td>
                        <td class="mono"><?= tanggal($r['waktu_selesai']) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$riwayat): ?><tr><td colspan="3" class="kosong">Belum ada riwayat.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Ajakan melanjutkan belajar -->
<div class="kartu lanjut">
    <span class="ikon-kotak"><?= ikon('buku') ?></span>
    <div>
        <h3>Lanjutkan belajar</h3>
        <p class="ket">
            <?= $materi_next ? 'Materi berikutnya: ' . e($materi_next['judul']) . '.' : 'Semua materi sudah Anda pelajari. Kerja bagus!' ?>
        </p>
    </div>
    <a class="btn btn-primer" href="<?= url($materi_next ? 'materi_baca.php?id=' . $materi_next['id'] : 'materi.php') ?>">Buka materi</a>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
