<?php
// Mengerjakan kuis dan penilaian otomatis (FR-09, FR-10)
// Alur mengikuti diagram Activity dan Sequence di file drawio:
//   GET  -> catat waktu_mulai, tampilkan soal
//   POST -> simpan jawaban, bandingkan dengan kunci, hitung nilai, simpan hasil
require __DIR__ . '/config.php';
wajib_login('mahasiswa');

$id_user = user()['id'];
$kuis_id = (int)($_GET['id'] ?? 0);

// 1. Ambil data kuis
$stmt = $db->prepare('SELECT * FROM kuis WHERE id = ?');
$stmt->execute([$kuis_id]);
$kuis = $stmt->fetch();
if (!$kuis) {
    set_pesan('Kuis tidak ditemukan.', 'galat');
    redirect('kuis.php');
}

// 2. Ambil soal-soalnya
$stmt = $db->prepare('SELECT * FROM soal WHERE kuis_id = ? ORDER BY id');
$stmt->execute([$kuis_id]);
$daftar_soal = $stmt->fetchAll();
if (!$daftar_soal) {
    set_pesan('Kuis ini belum punya soal.', 'galat');
    redirect('kuis.php');
}

// 3. Cari percobaan milik mahasiswa ini. Belum ada -> catat waktu_mulai sekarang.
//    (SELECT lagi setelah INSERT supaya selisih waktu dihitung oleh MySQL sendiri)
$stmt = $db->prepare('SELECT * FROM percobaan_kuis WHERE kuis_id = ? AND user_id = ?');
$stmt->execute([$kuis_id, $id_user]);
$percobaan = $stmt->fetch();

if (!$percobaan) {
    $db->prepare('INSERT INTO percobaan_kuis (kuis_id, user_id, waktu_mulai) VALUES (?, ?, NOW())')
       ->execute([$kuis_id, $id_user]);
    $stmt->execute([$kuis_id, $id_user]);
    $percobaan = $stmt->fetch();
}

// Sudah selesai? Langsung tampilkan hasilnya (kuis hanya boleh dikerjakan sekali)
if ($percobaan['waktu_selesai'] !== null) {
    redirect('kuis_hasil.php?id=' . $percobaan['id']);
}

// 4. Hitung sisa waktu (detik) = durasi - waktu yang sudah berjalan
$stmt = $db->prepare('SELECT TIMESTAMPDIFF(SECOND, waktu_mulai, NOW()) FROM percobaan_kuis WHERE id = ?');
$stmt->execute([$percobaan['id']]);
$sisa = $kuis['durasi_menit'] * 60 - (int)$stmt->fetchColumn();

// ---------- Mahasiswa menekan "Kirim jawaban" (atau waktu habis) ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_cek();

    // Terlambat lebih dari 60 detik dari batas waktu -> jawaban tidak dihitung
    $terlambat = $sisa < -60;
    $kiriman   = $_POST['jawaban'] ?? [];
    $jumlah_benar = 0;

    $db->beginTransaction();
    $simpan = $db->prepare('INSERT INTO jawaban (percobaan_id, soal_id, jawaban_dipilih, benar) VALUES (?, ?, ?, ?)');

    foreach ($daftar_soal as $s) {
        $pilihan = $terlambat ? '' : ($kiriman[$s['id']] ?? '');
        if (!in_array($pilihan, ['A', 'B', 'C', 'D'], true)) {
            $pilihan = null;                               // tidak dijawab
        }
        $benar = ($pilihan !== null && $pilihan === $s['kunci_jawaban']) ? 1 : 0;   // bandingkan dengan kunci
        $jumlah_benar += $benar;
        $simpan->execute([$percobaan['id'], $s['id'], $pilihan, $benar]);
    }

    // Nilai = (jumlah benar / jumlah soal) x 100
    $nilai_akhir = $jumlah_benar / count($daftar_soal) * 100;

    $db->prepare('UPDATE percobaan_kuis SET nilai = ?, waktu_selesai = NOW() WHERE id = ?')
       ->execute([$nilai_akhir, $percobaan['id']]);
    $db->commit();

    redirect('kuis_hasil.php?id=' . $percobaan['id']);
}

// ---------- Tampilan halaman soal ----------
$sisa  = max(0, $sisa);
$judul = 'Kuis ' . $kuis['id'];
$menu  = 'kuis';
$crumb = ['kuis', 'kuis-' . $kuis['id'], 'kerjakan'];
$skrip = 'kuis.js';
require __DIR__ . '/includes/header.php';
?>
<form method="post" id="formKuis" data-sisa="<?= $sisa ?>">
    <?= csrf_field() ?>

    <!-- Kepala: judul, timer, dan penanda kemajuan -->
    <div class="kartu kuis-kepala">
        <div>
            <span class="pil pil-primer mono">KUIS <?= $kuis['id'] ?></span>
            <h1><?= e($kuis['judul']) ?></h1>
            <p class="ket"><?= count($daftar_soal) ?> soal | durasi <?= $kuis['durasi_menit'] ?> menit</p>
        </div>
        <div class="timer"><small>SISA WAKTU</small><strong id="sisaWaktu">--:--</strong></div>
        <div class="segmen">
            <?php foreach ($daftar_soal as $s): ?><span></span><?php endforeach; ?>
        </div>
        <div class="posisi mono" id="posisi"></div>
    </div>

    <div class="kuis-grid">
        <!-- Soal: satu soal tampil sekaligus -->
        <div class="kartu isi-kartu">
            <?php foreach ($daftar_soal as $i => $s): ?>
                <section class="soal" <?= $i > 0 ? 'hidden' : '' ?>>
                    <div class="label-kecil biru">SOAL <?= sprintf('%02d', $i + 1) ?></div>
                    <h2><?= nl2br(e($s['pertanyaan'])) ?></h2>
                    <?php foreach (['A', 'B', 'C', 'D'] as $huruf): ?>
                        <label class="opsi">
                            <input type="radio" name="jawaban[<?= $s['id'] ?>]" value="<?= $huruf ?>">
                            <span class="huruf"><?= $huruf ?></span>
                            <span class="teks"><?= e($s['opsi_' . strtolower($huruf)]) ?></span>
                            <span class="bulat"></span>
                        </label>
                    <?php endforeach; ?>
                </section>
            <?php endforeach; ?>

            <div class="baris-antar kuis-tombol">
                <button type="button" class="btn btn-garis" id="sebelum">Sebelumnya</button>
                <button type="button" class="btn btn-primer" id="lanjut">Selanjutnya</button>
            </div>
        </div>

        <!-- Samping: navigasi soal dan tombol kirim -->
        <div>
            <div class="kartu isi-kartu">
                <h3>Navigasi soal</h3>
                <div class="nomor-grid">
                    <?php foreach ($daftar_soal as $i => $s): ?>
                        <button type="button" class="nomor"><?= $i + 1 ?></button>
                    <?php endforeach; ?>
                </div>
                <div class="legenda ket">
                    <span><i class="l-jawab"></i>Dijawab</span>
                    <span><i class="l-aktif"></i>Soal ini</span>
                    <span><i class="l-belum"></i>Belum</span>
                </div>
                <hr>
                <p class="ket">Jawaban disimpan saat Anda menekan Kirim.</p>
            </div>
            <button type="submit" class="btn btn-teal btn-penuh btn-besar" id="kirim">Kirim jawaban</button>
        </div>
    </div>
</form>
<?php require __DIR__ . '/includes/footer.php'; ?>
