<?php
// Form tambah / ubah soal beserta kunci jawaban (FR-08)
require __DIR__ . '/../config.php';
wajib_login('pengajar');

$kuis_id = (int)($_GET['kuis'] ?? 0);
$id      = (int)($_GET['id'] ?? 0);

$stmt = $db->prepare('SELECT k.*, (SELECT COUNT(*) FROM soal s WHERE s.kuis_id = k.id) AS jumlah_soal FROM kuis k WHERE k.id = ?');
$stmt->execute([$kuis_id]);
$kuis = $stmt->fetch();
if (!$kuis) {
    set_pesan('Kuis tidak ditemukan.', 'galat');
    redirect('pengajar/kuis.php');
}

$soal = ['pertanyaan' => '', 'opsi_a' => '', 'opsi_b' => '', 'opsi_c' => '', 'opsi_d' => '', 'kunci_jawaban' => ''];

if ($id) {
    // "kuis_id = ?" memastikan soal memang milik kuis ini
    $stmt = $db->prepare('SELECT * FROM soal WHERE id = ? AND kuis_id = ?');
    $stmt->execute([$id, $kuis_id]);
    $soal = $stmt->fetch();
    if (!$soal) {
        set_pesan('Soal tidak ditemukan.', 'galat');
        redirect('pengajar/kuis_soal.php?id=' . $kuis_id);
    }
}

$galat = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_cek();
    foreach (['pertanyaan', 'opsi_a', 'opsi_b', 'opsi_c', 'opsi_d', 'kunci_jawaban'] as $kolom) {
        $soal[$kolom] = trim($_POST[$kolom] ?? '');
    }

    // Validasi: semua kolom wajib, kunci harus A/B/C/D
    foreach (['opsi_a', 'opsi_b', 'opsi_c', 'opsi_d'] as $kolom) {
        if ($soal[$kolom] === '' || mb_strlen($soal[$kolom]) > 255) {
            $galat[] = 'Semua opsi wajib diisi (maksimal 255 huruf).';
            break;
        }
    }
    if ($soal['pertanyaan'] === '') {
        $galat[] = 'Pertanyaan wajib diisi.';
    }
    if (!in_array($soal['kunci_jawaban'], ['A', 'B', 'C', 'D'], true)) {
        $galat[] = 'Pilih satu opsi sebagai kunci jawaban.';
    }

    if (!$galat) {
        $nilai_kolom = [$soal['pertanyaan'], $soal['opsi_a'], $soal['opsi_b'], $soal['opsi_c'], $soal['opsi_d'], $soal['kunci_jawaban']];
        if ($id) {
            $stmt = $db->prepare('UPDATE soal SET pertanyaan = ?, opsi_a = ?, opsi_b = ?, opsi_c = ?, opsi_d = ?, kunci_jawaban = ? WHERE id = ?');
            $stmt->execute([...$nilai_kolom, $id]);
        } else {
            $stmt = $db->prepare('INSERT INTO soal (pertanyaan, opsi_a, opsi_b, opsi_c, opsi_d, kunci_jawaban, kuis_id) VALUES (?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([...$nilai_kolom, $kuis_id]);
        }
        set_pesan('Soal berhasil disimpan.');
        redirect('pengajar/kuis_soal.php?id=' . $kuis_id);
    }
}

$judul = $id ? 'Ubah Soal' : 'Tambah Soal';
$menu  = 'kuis';
$crumb = ['kuis', 'kuis-' . $kuis['id'], $id ? 'ubah-soal' : 'soal-baru'];
require __DIR__ . '/../includes/header.php';
?>
<h1><?= e($judul) ?></h1>
<p class="sub">Kuis <?= $kuis['id'] ?>: <?= e($kuis['judul']) ?></p>

<?php foreach (array_unique($galat) as $g): ?><div class="pesan pesan-galat"><?= e($g) ?></div><?php endforeach; ?>

<div class="grid-form">
    <form class="kartu isi-kartu" method="post">
        <?= csrf_field() ?>
        <div class="kelompok">
            <label for="pertanyaan">Pertanyaan *</label>
            <textarea class="input" id="pertanyaan" name="pertanyaan" rows="4" placeholder="Tulis teks pertanyaan di sini..." required><?= e($soal['pertanyaan']) ?></textarea>
        </div>

        <div class="baris-antar">
            <label>Pilihan jawaban dan kunci *</label>
            <span class="ket">Pilih satu sebagai kunci</span>
        </div>

        <?php foreach (['A', 'B', 'C', 'D'] as $huruf): $kolom = 'opsi_' . strtolower($huruf); ?>
            <div class="opsi opsi-form">
                <span class="huruf"><?= $huruf ?></span>
                <input class="teks-opsi" type="text" name="<?= $kolom ?>" placeholder="Teks opsi <?= $huruf ?>"
                       value="<?= e($soal[$kolom]) ?>" maxlength="255" required>
                <span class="kunci">kunci</span>
                <label class="pilih-kunci" title="Jadikan opsi <?= $huruf ?> sebagai kunci">
                    <input type="radio" name="kunci_jawaban" value="<?= $huruf ?>" <?= $soal['kunci_jawaban'] === $huruf ? 'checked' : '' ?> required>
                    <span class="bulat"></span>
                </label>
            </div>
        <?php endforeach; ?>

        <div class="aksi">
            <button class="btn btn-primer" type="submit">Simpan soal</button>
            <a class="btn btn-garis" href="<?= url('pengajar/kuis_soal.php?id=' . $kuis_id) ?>">Batal</a>
        </div>
    </form>

    <div class="kartu isi-kartu">
        <h3>Ringkasan kuis</h3>
        <div class="ringkas-baris"><span class="mono ket">kuis</span><b>Kuis <?= $kuis['id'] ?></b></div>
        <div class="ringkas-baris"><span class="mono ket">jumlah soal</span><b><?= $kuis['jumlah_soal'] ?> soal</b></div>
        <div class="ringkas-baris"><span class="mono ket">durasi</span><b><?= $kuis['durasi_menit'] ?> menit</b></div>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
