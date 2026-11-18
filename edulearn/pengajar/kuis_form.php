<?php
// Form tambah / ubah kuis (FR-07)
require __DIR__ . '/../config.php';
wajib_login('pengajar');

$id = (int)($_GET['id'] ?? 0);
$kuis = ['judul' => '', 'deskripsi' => '', 'durasi_menit' => 15];

if ($id) {
    $stmt = $db->prepare('SELECT * FROM kuis WHERE id = ?');
    $stmt->execute([$id]);
    $kuis = $stmt->fetch();
    if (!$kuis) {
        set_pesan('Kuis tidak ditemukan.', 'galat');
        redirect('pengajar/kuis.php');
    }
}

$galat = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_cek();
    $kuis['judul']        = trim($_POST['judul'] ?? '');
    $kuis['deskripsi']    = trim($_POST['deskripsi'] ?? '');
    $kuis['durasi_menit'] = (int)($_POST['durasi_menit'] ?? 0);

    if ($kuis['judul'] === '' || mb_strlen($kuis['judul']) > 150) {
        $galat[] = 'Judul kuis wajib diisi (maksimal 150 huruf).';
    }
    if ($kuis['durasi_menit'] < 1 || $kuis['durasi_menit'] > 300) {
        $galat[] = 'Durasi harus antara 1 dan 300 menit.';
    }

    if (!$galat) {
        if ($id) {
            $stmt = $db->prepare('UPDATE kuis SET judul = ?, deskripsi = ?, durasi_menit = ? WHERE id = ?');
            $stmt->execute([$kuis['judul'], $kuis['deskripsi'], $kuis['durasi_menit'], $id]);
            set_pesan('Kuis berhasil disimpan.');
            redirect('pengajar/kuis.php');
        }
        $stmt = $db->prepare('INSERT INTO kuis (judul, deskripsi, durasi_menit, dibuat_oleh) VALUES (?, ?, ?, ?)');
        $stmt->execute([$kuis['judul'], $kuis['deskripsi'], $kuis['durasi_menit'], user()['id']]);
        set_pesan('Kuis dibuat. Sekarang tambahkan soalnya.');
        redirect('pengajar/kuis_soal.php?id=' . $db->lastInsertId());   // langsung ke halaman soal
    }
}

$judul = $id ? 'Ubah Kuis' : 'Tambah Kuis';
$menu  = 'kuis';
$crumb = ['kuis', $id ? 'ubah' : 'tambah'];
require __DIR__ . '/../includes/header.php';
?>
<h1><?= e($judul) ?></h1>
<p class="sub">Isi judul dan durasi kuis. Soal ditambahkan setelah kuis disimpan.</p>

<?php foreach ($galat as $g): ?><div class="pesan pesan-galat"><?= e($g) ?></div><?php endforeach; ?>

<div class="grid-form">
    <form class="kartu isi-kartu" method="post">
        <?= csrf_field() ?>
        <div class="kelompok">
            <label for="judul">Judul kuis *</label>
            <input class="input" type="text" id="judul" name="judul" placeholder="judul kuis"
                   value="<?= e($kuis['judul']) ?>" maxlength="150" required>
        </div>
        <div class="kelompok">
            <label for="deskripsi">Deskripsi</label>
            <textarea class="input" id="deskripsi" name="deskripsi" rows="4" placeholder="Penjelasan singkat kuis"><?= e($kuis['deskripsi']) ?></textarea>
        </div>
        <div class="kelompok">
            <label for="durasi_menit">Durasi (menit) *</label>
            <input class="input input-pendek" type="number" id="durasi_menit" name="durasi_menit"
                   min="1" max="300" value="<?= e($kuis['durasi_menit']) ?>" required>
        </div>
        <div class="aksi">
            <button class="btn btn-primer" type="submit">Simpan</button>
            <a class="btn btn-garis" href="<?= url('pengajar/kuis.php') ?>">Batal</a>
        </div>
    </form>

    <div class="kartu isi-kartu">
        <h3>Informasi</h3>
        <p class="ket">Waktu pengerjaan dihitung sejak mahasiswa menekan Mulai kuis.</p>
        <p class="ket">Kolom bertanda * wajib diisi.</p>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
