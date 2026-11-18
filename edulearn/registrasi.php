<?php
// Registrasi akun mahasiswa (FR-02)
require __DIR__ . '/config.php';

if (user()) {
    redirect(beranda(user()['role']));
}

$galat = [];
$nama = $username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_cek();
    $nama     = trim($_POST['nama'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $sandi    = $_POST['sandi'] ?? '';
    $ulang    = $_POST['ulang'] ?? '';

    // Validasi input
    if ($nama === '' || mb_strlen($nama) > 100) {
        $galat[] = 'Nama lengkap wajib diisi (maksimal 100 huruf).';
    }
    if (!preg_match('/^[A-Za-z0-9_]{3,50}$/', $username)) {
        $galat[] = 'Username 3-50 karakter, hanya huruf, angka, dan garis bawah.';
    }
    if (strlen($sandi) < 6) {
        $galat[] = 'Kata sandi minimal 6 karakter.';
    }
    if ($sandi !== $ulang) {
        $galat[] = 'Ulangi kata sandi tidak sama.';
    }

    // Cek username belum dipakai
    if (!$galat) {
        $stmt = $db->prepare('SELECT id FROM users WHERE username = ?');
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $galat[] = 'Username sudah dipakai, pilih yang lain.';
        }
    }

    // Simpan akun baru (kata sandi disimpan dalam bentuk hash)
    if (!$galat) {
        $stmt = $db->prepare("INSERT INTO users (nama, username, password_hash, role) VALUES (?, ?, ?, 'mahasiswa')");
        $stmt->execute([$nama, $username, password_hash($sandi, PASSWORD_DEFAULT)]);
        set_pesan('Pendaftaran berhasil. Silakan masuk.');
        redirect('login.php');
    }
}

$judul     = 'Daftar';
$headline  = 'Mulai belajar<br>dengan akun Anda.';
$deskripsi = 'Buat akun mahasiswa untuk membuka materi ' . NAMA_MATKUL . ', mengerjakan kuis, dan melihat nilai.';
require __DIR__ . '/includes/auth_atas.php';
?>
<h1>Daftar akun</h1>
<p class="sub">Isi data berikut untuk membuat akun mahasiswa.</p>

<?php foreach ($galat as $g): ?><div class="pesan pesan-galat"><?= e($g) ?></div><?php endforeach; ?>

<form method="post">
    <?= csrf_field() ?>
    <div class="kelompok">
        <label for="nama">Nama lengkap</label>
        <input class="input" type="text" id="nama" name="nama" placeholder="nama lengkap" value="<?= e($nama) ?>" required>
    </div>
    <div class="kelompok">
        <label for="username">Username</label>
        <input class="input" type="text" id="username" name="username" placeholder="nama pengguna" value="<?= e($username) ?>" required>
    </div>
    <div class="kelompok">
        <label for="sandi">Kata sandi</label>
        <input class="input" type="password" id="sandi" name="sandi" placeholder="••••••••" required>
    </div>
    <div class="kelompok">
        <label for="ulang">Ulangi kata sandi</label>
        <input class="input" type="password" id="ulang" name="ulang" placeholder="••••••••" required>
    </div>
    <button class="btn btn-primer btn-penuh" type="submit">Daftar</button>
</form>

<p class="tautan-bawah"><a href="<?= url('login.php') ?>">Sudah punya akun? Masuk</a></p>
<?php require __DIR__ . '/includes/auth_bawah.php'; ?>
