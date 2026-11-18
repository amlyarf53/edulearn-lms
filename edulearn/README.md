# EduLearn (LMS Mini)

Aplikasi web pembelajaran sederhana dengan **PHP + MySQL + HTML/CSS**, dibuat berdasarkan
desain di file drawio (ERD, Use Case, Activity, Sequence, Arsitektur) dan mockup Figma.

## Cara menjalankan (XAMPP / Laragon)

1. Salin folder `edulearn` ke `htdocs` (XAMPP) atau `www` (Laragon).
2. Buka **phpMyAdmin** -> tab **Impor** -> pilih `database.sql` -> **Kirim**.
   (Database `edulearn` dan data contoh dibuat otomatis.)
3. Buka `config.php`, sesuaikan `DB_USER`, `DB_PASS`, dan `BASE_URL`
   (isi dengan nama folder, misalnya `/edulearn`).
4. Buka `http://localhost/edulearn` di peramban.

Butuh PHP 7.4 atau lebih baru.

## Akun contoh

| Peran     | Username    | Kata sandi     |
|-----------|-------------|----------------|
| Pengajar  | `pengajar`  | `pengajar123`  |
| Mahasiswa | `mahasiswa` | `mahasiswa123` |

Mahasiswa baru dapat mendaftar sendiri lewat halaman Registrasi.
Akun pengajar hanya dibuat lewat database. Ganti kata sandi contoh sebelum dipasang di hosting.

## Struktur folder

```
edulearn/
├── config.php            pengaturan database dan aplikasi
├── database.sql          tabel (sesuai ERD) + data contoh
├── index.php             pintu masuk
├── login.php, registrasi.php, logout.php
├── dashboard.php         dashboard nilai mahasiswa
├── materi.php            daftar materi + tanda "sudah dipelajari"
├── materi_baca.php, materi_tandai.php, unduh.php
├── kuis.php              daftar kuis
├── kuis_kerjakan.php     mengerjakan kuis + nilai otomatis
├── kuis_hasil.php        hasil kuis
├── pengajar/             halaman khusus pengajar
│   ├── materi.php, materi_form.php, materi_hapus.php
│   ├── kuis.php, kuis_form.php, kuis_hapus.php
│   ├── kuis_soal.php, soal_form.php, soal_hapus.php
│   └── rekap.php
├── includes/             fungsi bantu dan kerangka halaman (sidebar, topbar)
├── assets/css/style.css  seluruh tampilan (responsif)
├── assets/js/            app.js (menu ponsel), kuis.js (timer dan navigasi soal)
└── uploads/              berkas materi (dikunci, hanya bisa lewat unduh.php)
```

## Kesesuaian dengan Use Case

| Kode   | Fitur                          | File                                   |
|--------|--------------------------------|----------------------------------------|
| FR-01  | Login                          | `login.php`                            |
| FR-02  | Registrasi mahasiswa           | `registrasi.php`                       |
| FR-03  | Logout                         | `logout.php`                           |
| FR-04  | Pembatasan akses per peran     | `wajib_login()` di `includes/fungsi.php` |
| FR-05  | Kelola materi                  | `pengajar/materi*.php`                 |
| FR-06  | Lihat dan unduh materi         | `materi.php`, `materi_baca.php`, `unduh.php` |
| FR-07  | Kelola kuis                    | `pengajar/kuis*.php`                   |
| FR-08  | Kelola soal dan kunci jawaban  | `pengajar/kuis_soal.php`, `soal_form.php` |
| FR-09  | Kerjakan kuis                  | `kuis.php`, `kuis_kerjakan.php`        |
| FR-10  | Hitung nilai otomatis          | `kuis_kerjakan.php` (bagian POST)      |
| FR-11  | Dashboard nilai dan progres    | `dashboard.php`                        |
| FR-12  | Rekap nilai mahasiswa          | `pengajar/rekap.php`                   |
| FR-13  | Tandai materi sudah dipelajari | `materi_tandai.php`                    |

## Keamanan dasar (NFR-02)

- Kata sandi disimpan dengan `password_hash()` dan dicek dengan `password_verify()`.
- Semua query memakai prepared statement (PDO).
- Setiap formulir POST memakai token CSRF.
- Input divalidasi di server, keluaran diamankan dengan `e()` (htmlspecialchars).
- Halaman dibatasi per peran; unggahan dibatasi jenis dan ukurannya (maks 5 MB).

## Catatan perilaku

- Satu mahasiswa hanya bisa mengerjakan satu kuis **satu kali** (ditambah `UNIQUE` pada
  `percobaan_kuis`). Jika halaman ditutup di tengah jalan, timer tetap berjalan dari
  `waktu_mulai` saat kuis dibuka lagi.
- Jawaban yang masuk lebih dari 60 detik setelah waktu habis tidak dihitung.
- Data contoh: Kuis 1 berisi 10 soal, Kuis 2-4 berisi 3 soal. Tambahkan soal lewat akun pengajar.
- Tabel `progres_materi` diberi `UNIQUE (user_id, materi_id)` agar satu mahasiswa hanya punya
  satu catatan per materi.
- Jika memakai Nginx (bukan Apache), `uploads/.htaccess` tidak berlaku. Tolak akses langsung
  ke folder `uploads/` lewat konfigurasi Nginx.
