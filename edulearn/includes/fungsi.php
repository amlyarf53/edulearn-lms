<?php
// =====================================================
// fungsi.php - fungsi-fungsi bantu yang dipakai di banyak halaman
// =====================================================

// ---------- Umum ----------

// Mengamankan teks sebelum ditampilkan di HTML (mencegah XSS)
function e($teks) {
    return htmlspecialchars((string)$teks, ENT_QUOTES, 'UTF-8');
}

// Membuat alamat lengkap, contoh: url('login.php') -> /edulearn/login.php
function url($path = '') {
    return BASE_URL . '/' . ltrim($path, '/');
}

// Pindah halaman
function redirect($path) {
    header('Location: ' . url($path));
    exit;
}

// ---------- Pesan sekali tampil (flash message) ----------
function set_pesan($teks, $jenis = 'sukses') {      // jenis: sukses / galat
    $_SESSION['pesan'] = [$jenis, $teks];
}
function ambil_pesan() {
    $pesan = $_SESSION['pesan'] ?? null;
    unset($_SESSION['pesan']);
    return $pesan;
}

// ---------- Token CSRF (melindungi formulir dari kiriman palsu) ----------
function csrf_token() {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}
function csrf_field() {
    return '<input type="hidden" name="csrf" value="' . csrf_token() . '">';
}
function csrf_cek() {
    $kiriman = $_POST['csrf'] ?? '';
    if (!hash_equals(csrf_token(), $kiriman)) {
        http_response_code(400);
        exit('Token keamanan tidak valid. Silakan muat ulang halaman.');
    }
}

// ---------- Login dan pembatasan akses per peran ----------
function user() {
    return $_SESSION['user'] ?? null;
}

// Halaman awal sesuai peran
function beranda($role) {
    return $role === 'pengajar' ? 'pengajar/materi.php' : 'dashboard.php';
}

// Dipanggil di awal setiap halaman yang butuh login.
// $role = 'pengajar' atau 'mahasiswa' (kosong = semua peran boleh)
function wajib_login($role = null) {
    $u = user();
    if (!$u) {
        redirect('login.php');
    }
    if ($role && $u['role'] !== $role) {
        set_pesan('Anda tidak punya akses ke halaman itu.', 'galat');
        redirect(beranda($u['role']));
    }
}

// ---------- Format tampilan ----------
const BULAN = [1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

// 2026-11-10 10:12:00 -> "10 Nov 2026"
function tanggal($waktu) {
    $t = strtotime($waktu);
    return date('d', $t) . ' ' . BULAN[(int)date('n', $t)] . ' ' . date('Y', $t);
}

// -> "10 Nov 2026, 10:12"
function tanggal_jam($waktu) {
    return tanggal($waktu) . ', ' . date('H:i', strtotime($waktu));
}

// Angka gaya Indonesia: angka(81.25, 1) -> "81,3"
function angka($n, $desimal = 0) {
    return number_format($n, $desimal, ',', '.');
}

// Nilai tanpa nol di belakang koma: 80.00 -> "80", 85.50 -> "85,5"
function nilai($n) {
    return rtrim(rtrim(number_format($n, 2, ',', ''), '0'), ',');
}

// Huruf pertama nama untuk avatar
function inisial($nama) {
    return strtoupper(mb_substr($nama, 0, 1));
}

// Nama berkas asli (di server berkas disimpan dengan awalan acak "xxxx_")
function nama_berkas($file_path) {
    return substr($file_path, strpos($file_path, '_') + 1);
}

// Jenis berkas untuk lencana kartu materi: "pdf" -> "PDF", kosong -> "TEKS"
function jenis_berkas($file_path) {
    return $file_path ? strtoupper(pathinfo($file_path, PATHINFO_EXTENSION)) : 'TEKS';
}

// ---------- Ikon (SVG kecil) ----------
function ikon($nama) {
    $bentuk = [
        'dashboard' => '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/>',
        'buku'      => '<path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>',
        'kuis'      => '<path d="m9 11 3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>',
        'nilai'     => '<path d="M3 3v18h18"/><path d="M18 17V9"/><path d="M13 17V5"/><path d="M8 17v-3"/>',
        'keluar'    => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="m16 17 5-5-5-5"/><path d="M21 12H9"/>',
        'berkas'    => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/>',
        'cek'       => '<path d="M20 6 9 17l-5-5"/>',
        'x'         => '<path d="M18 6 6 18"/><path d="m6 6 12 12"/>',
        'jam'       => '<circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>',
        'user'      => '<path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
        'menu'      => '<path d="M3 12h18M3 6h18M3 18h18"/>',
    ];
    return '<svg class="ikon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'
        . ($bentuk[$nama] ?? '') . '</svg>';
}
