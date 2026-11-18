<?php
// Form tambah / ubah materi + unggah berkas (FR-05)
// Tanpa ?id= berarti tambah materi baru; dengan ?id= berarti ubah materi.
require __DIR__ . '/../config.php';
wajib_login('pengajar');

const BERKAS_BOLEH = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'txt', 'zip'];

$id = (int)($_GET['id'] ?? 0);
$materi = ['judul' => '', 'isi_teks' => '', 'file_path' => null];

if ($id) {
    $stmt = $db->prepare('SELECT * FROM materi WHERE id = ?');
    $stmt->execute([$id]);
    $materi = $stmt->fetch();
    if (!$materi) {
        set_pesan('Materi tidak ditemukan.', 'galat');
        redirect('pengajar/materi.php');
    }
}

$galat = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_cek();
    $materi['judul']    = trim($_POST['judul'] ?? '');
    $materi['isi_teks'] = trim($_POST['isi_teks'] ?? '');

    // Validasi input
    if ($materi['judul'] === '' || mb_strlen($materi['judul']) > 150) {
        $galat[] = 'Judul materi wajib diisi (maksimal 150 huruf).';
    }
    if ($materi['isi_teks'] === '') {
        $galat[] = 'Isi materi wajib diisi.';
    }

    // Validasi berkas (opsional)
    $berkas = $_FILES['berkas'] ?? null;
    $ada_berkas = $berkas && $berkas['error'] !== UPLOAD_ERR_NO_FILE;
    $nama_baru = null;

    if ($ada_berkas) {
        $ekstensi = strtolower(pathinfo($berkas['name'], PATHINFO_EXTENSION));
        if ($berkas['error'] !== UPLOAD_ERR_OK) {
            $galat[] = 'Berkas gagal diunggah. Coba lagi.';
        } elseif (!in_array($ekstensi, BERKAS_BOLEH, true)) {
            $galat[] = 'Jenis berkas tidak diizinkan. Boleh: ' . implode(', ', BERKAS_BOLEH) . '.';
        } elseif ($berkas['size'] > MAKS_BERKAS) {
            $galat[] = 'Ukuran berkas maksimal 5 MB.';
        } else {
            // Nama aman: awalan acak + nama asli yang sudah dibersihkan
            $bersih = preg_replace('/[^A-Za-z0-9._-]/', '_', basename($berkas['name']));
            $nama_baru = uniqid() . '_' . $bersih;
        }
    }

    if (!$galat) {
        if ($nama_baru) {
            move_uploaded_file($berkas['tmp_name'], FOLDER_UPLOAD . $nama_baru);
            // Saat ubah materi: hapus berkas lama
            if ($materi['file_path'] && is_file(FOLDER_UPLOAD . basename($materi['file_path']))) {
                unlink(FOLDER_UPLOAD . basename($materi['file_path']));
            }
            $materi['file_path'] = $nama_baru;
        }

        if ($id) {
            $stmt = $db->prepare('UPDATE materi SET judul = ?, isi_teks = ?, file_path = ? WHERE id = ?');
            $stmt->execute([$materi['judul'], $materi['isi_teks'], $materi['file_path'], $id]);
        } else {
            $stmt = $db->prepare('INSERT INTO materi (judul, isi_teks, file_path, dibuat_oleh) VALUES (?, ?, ?, ?)');
            $stmt->execute([$materi['judul'], $materi['isi_teks'], $materi['file_path'], user()['id']]);
        }
        set_pesan('Materi berhasil disimpan.');
        redirect('pengajar/materi.php');
    }
}

$judul = $id ? 'Ubah Materi' : 'Tambah Materi';
$menu  = 'materi';
$crumb = ['materi', $id ? 'ubah' : 'tambah'];
require __DIR__ . '/../includes/header.php';
?>
<h1><?= e($judul) ?></h1>
<p class="sub">Isi judul dan isi materi, lalu unggah berkas bila perlu.</p>

<?php foreach ($galat as $g): ?><div class="pesan pesan-galat"><?= e($g) ?></div><?php endforeach; ?>

<div class="grid-form">
    <form class="kartu isi-kartu" method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <div class="kelompok">
            <label for="judul">Judul materi *</label>
            <input class="input" type="text" id="judul" name="judul" placeholder="judul materi"
                   value="<?= e($materi['judul']) ?>" maxlength="150" required>
        </div>
        <div class="kelompok">
            <label for="isi_teks">Isi materi *</label>
            <textarea class="input" id="isi_teks" name="isi_teks" rows="9" placeholder="Tulis isi materi di sini..." required><?= e($materi['isi_teks']) ?></textarea>
        </div>
        <div class="kelompok">
            <label for="berkas">Berkas materi (opsional)</label>
            <div class="unggah">
                <span class="ikon-kotak"><?= ikon('berkas') ?></span>
                <input type="file" id="berkas" name="berkas">
            </div>
            <?php if ($materi['file_path']): ?>
                <p class="ket">Berkas saat ini: <b><?= e(nama_berkas($materi['file_path'])) ?></b>. Pilih berkas baru untuk menggantinya.</p>
            <?php endif; ?>
        </div>
        <div class="aksi">
            <button class="btn btn-primer" type="submit">Simpan</button>
            <a class="btn btn-garis" href="<?= url('pengajar/materi.php') ?>">Batal</a>
        </div>
    </form>

    <div class="kartu isi-kartu">
        <h3>Informasi</h3>
        <p class="ket">Materi yang disimpan akan tampil pada halaman Materi mahasiswa.</p>
        <p class="ket">Kolom bertanda * wajib diisi.</p>
        <p class="ket">Berkas: <?= e(implode(', ', BERKAS_BOLEH)) ?> (maksimal 5 MB).</p>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
