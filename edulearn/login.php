<?php
// Login (FR-01) - pengajar dan mahasiswa memakai halaman yang sama
require __DIR__ . '/config.php';

if (user()) {
    redirect(beranda(user()['role']));
}

$galat = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_cek();
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // Cari pengguna (prepared statement: aman dari SQL injection)
    $stmt = $db->prepare('SELECT * FROM users WHERE username = ?');
    $stmt->execute([$username]);
    $akun = $stmt->fetch();

    // Cocokkan kata sandi dengan hash yang tersimpan
    if ($akun && password_verify($password, $akun['password_hash'])) {
        session_regenerate_id(true);   // ganti ID sesi setelah login
        $_SESSION['user'] = [
            'id'       => $akun['id'],
            'nama'     => $akun['nama'],
            'username' => $akun['username'],
            'role'     => $akun['role'],
        ];
        redirect(beranda($akun['role']));
    }
    $galat = 'Username atau kata sandi salah.';
}

$pesan     = ambil_pesan();
$judul     = 'Masuk';
$headline  = 'Satu platform untuk<br>materi, kuis, dan nilai.';
$deskripsi = 'Akses materi ' . NAMA_MATKUL . ', kerjakan kuis, dan pantau nilai Anda dalam satu sistem terpadu.';
require __DIR__ . '/includes/auth_atas.php';
?>
<h1>Masuk</h1>
<p class="sub">Gunakan akun Anda untuk melanjutkan.</p>

<?php if ($galat): ?><div class="pesan pesan-galat"><?= e($galat) ?></div><?php endif; ?>
<?php if ($pesan): ?><div class="pesan pesan-<?= e($pesan[0]) ?>"><?= e($pesan[1]) ?></div><?php endif; ?>

<form method="post">
    <?= csrf_field() ?>
    <div class="kelompok">
        <label for="username">Username</label>
        <input class="input" type="text" id="username" name="username" placeholder="nama pengguna"
               value="<?= e($_POST['username'] ?? '') ?>" required autofocus>
    </div>
    <div class="kelompok">
        <label for="password">Kata sandi</label>
        <input class="input" type="password" id="password" name="password" placeholder="••••••••" required>
    </div>
    <button class="btn btn-primer btn-penuh" type="submit">Masuk</button>
</form>

<p class="tautan-bawah"><a href="<?= url('registrasi.php') ?>">Belum punya akun? Daftar sebagai mahasiswa</a></p>
<?php require __DIR__ . '/includes/auth_bawah.php'; ?>
