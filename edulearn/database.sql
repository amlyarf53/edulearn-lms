-- =====================================================
-- database.sql - basis data EduLearn (sesuai ERD di drawio)
-- Cara pakai: impor file ini lewat phpMyAdmin (tab Impor)
-- =====================================================
CREATE DATABASE IF NOT EXISTS edulearn CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE edulearn;

DROP TABLE IF EXISTS jawaban, percobaan_kuis, soal, kuis, progres_materi, materi, users;

-- Pengguna: pengajar atau mahasiswa
CREATE TABLE users (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  nama          VARCHAR(100) NOT NULL,
  username      VARCHAR(50)  NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role          ENUM('pengajar','mahasiswa') NOT NULL,
  created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Materi yang dibuat pengajar
CREATE TABLE materi (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  judul       VARCHAR(150) NOT NULL,
  isi_teks    TEXT NOT NULL,
  file_path   VARCHAR(255) NULL,
  dibuat_oleh INT NOT NULL,
  created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (dibuat_oleh) REFERENCES users(id)
);

-- Catatan mahasiswa: materi mana yang sudah dipelajari
-- (UNIQUE: satu mahasiswa hanya punya satu catatan per materi)
CREATE TABLE progres_materi (
  id        INT AUTO_INCREMENT PRIMARY KEY,
  user_id   INT NOT NULL,
  materi_id INT NOT NULL,
  selesai   BOOLEAN NOT NULL DEFAULT 0,
  tanggal   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY (user_id, materi_id),
  FOREIGN KEY (user_id)   REFERENCES users(id)  ON DELETE CASCADE,
  FOREIGN KEY (materi_id) REFERENCES materi(id) ON DELETE CASCADE
);

CREATE TABLE kuis (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  judul        VARCHAR(150) NOT NULL,
  deskripsi    TEXT NULL,
  durasi_menit INT NOT NULL DEFAULT 15,
  dibuat_oleh  INT NOT NULL,
  created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (dibuat_oleh) REFERENCES users(id)
);

CREATE TABLE soal (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  kuis_id       INT NOT NULL,
  pertanyaan    TEXT NOT NULL,
  opsi_a        VARCHAR(255) NOT NULL,
  opsi_b        VARCHAR(255) NOT NULL,
  opsi_c        VARCHAR(255) NOT NULL,
  opsi_d        VARCHAR(255) NOT NULL,
  kunci_jawaban CHAR(1) NOT NULL,
  FOREIGN KEY (kuis_id) REFERENCES kuis(id) ON DELETE CASCADE
);

-- Satu pengerjaan kuis oleh satu mahasiswa
-- (UNIQUE: setiap mahasiswa hanya boleh mengerjakan satu kuis satu kali)
CREATE TABLE percobaan_kuis (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  kuis_id       INT NOT NULL,
  user_id       INT NOT NULL,
  waktu_mulai   DATETIME NOT NULL,
  waktu_selesai DATETIME NULL,
  nilai         DECIMAL(5,2) NULL,
  UNIQUE KEY (kuis_id, user_id),
  FOREIGN KEY (kuis_id) REFERENCES kuis(id)  ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Jawaban mahasiswa untuk tiap soal
CREATE TABLE jawaban (
  id              INT AUTO_INCREMENT PRIMARY KEY,
  percobaan_id    INT NOT NULL,
  soal_id         INT NOT NULL,
  jawaban_dipilih CHAR(1) NULL,
  benar           BOOLEAN NOT NULL DEFAULT 0,
  FOREIGN KEY (percobaan_id) REFERENCES percobaan_kuis(id) ON DELETE CASCADE,
  FOREIGN KEY (soal_id)      REFERENCES soal(id)           ON DELETE CASCADE
);

-- ---------------- DATA AWAL (contoh) ----------------
-- Akun: pengajar / pengajar123  dan  mahasiswa / mahasiswa123
INSERT INTO users (nama, username, password_hash, role) VALUES
('Pengajar',     'pengajar',  '$2y$12$Ka8tclaIM3.hagGz3vFyO.A8VuAR17cMZAz4F4B.6jeAYKAbWSr0a', 'pengajar'),
('Mahasiswa A',  'mahasiswa', '$2y$12$wlF5w9fHwTeQyrPSwwEabet1azRldA8SFWy6SdlLVPghEMBo9MVh6', 'mahasiswa');

INSERT INTO materi (judul, isi_teks, dibuat_oleh) VALUES
('Pengantar Algoritma dan Pemrograman', 'Konsep algoritma, flowchart, dan pseudocode.\n\nAlgoritma adalah urutan langkah logis untuk menyelesaikan suatu masalah. Flowchart menggambarkan alur algoritma dengan simbol, sedangkan pseudocode menuliskannya dengan bahasa yang mirip pemrograman.', 1),
('Percabangan dan Perulangan', 'Struktur if-else, for, dan while.\n\nPercabangan memilih aksi berdasarkan kondisi. Perulangan menjalankan blok kode berkali-kali selama kondisi terpenuhi.', 1),
('Array dan Fungsi', 'Penyimpanan data dan modularisasi program.\n\nArray menyimpan banyak nilai dalam satu variabel. Fungsi adalah blok kode bernama yang dapat dipanggil berulang kali.', 1),
('Pengurutan dan Pencarian', 'Bubble sort, linear search, dan binary search.\n\nPengurutan menyusun data secara berurutan. Pencarian menemukan data tertentu di dalam kumpulan data.', 1);

INSERT INTO kuis (judul, deskripsi, durasi_menit, dibuat_oleh) VALUES
('Pengantar Algoritma dan Pemrograman', 'Konsep dasar algoritma.', 15, 1),
('Percabangan dan Perulangan', 'Struktur kontrol program.', 15, 1),
('Array dan Fungsi', 'Array dan fungsi.', 20, 1),
('Pengurutan dan Pencarian', 'Sorting dan searching.', 20, 1);

INSERT INTO soal (kuis_id, pertanyaan, opsi_a, opsi_b, opsi_c, opsi_d, kunci_jawaban) VALUES
(1, 'Apa yang dimaksud dengan algoritma?', 'Bahasa pemrograman tingkat tinggi', 'Urutan langkah logis untuk menyelesaikan masalah', 'Perangkat keras komputer', 'Program yang sudah dikompilasi', 'B'),
(1, 'Diagram yang menggambarkan alur algoritma disebut?', 'Flowchart', 'Histogram', 'Spreadsheet', 'Kompilator', 'A'),
(1, 'Apa yang dimaksud dengan pseudocode?', 'Kode mesin hasil kompilasi', 'Format penyimpanan basis data', 'Penulisan algoritma dengan bahasa mirip pemrograman tanpa sintaks ketat', 'Jenis virus komputer', 'C'),
(1, 'Simbol belah ketupat pada flowchart digunakan untuk?', 'Awal dan akhir program', 'Input data', 'Percabangan atau keputusan', 'Output data', 'C'),
(1, 'Manakah yang termasuk tipe data bilangan bulat?', 'string', 'float', 'boolean', 'integer', 'D'),
(1, 'Sifat algoritma yang baik adalah?', 'Langkahnya boleh tidak terbatas', 'Berhenti setelah sejumlah langkah terbatas', 'Boleh bermakna ganda', 'Tidak perlu menghasilkan keluaran', 'B'),
(1, 'Berapa hasil dari 7 mod 3?', '1', '2', '3', '0', 'A'),
(1, 'Proses menerjemahkan kode sumber menjadi kode mesin disebut?', 'Debugging', 'Formatting', 'Backup', 'Kompilasi', 'D'),
(1, 'Kesalahan yang membuat program berjalan tetapi hasilnya keliru disebut?', 'Syntax error', 'Logic error', 'Network error', 'Disk error', 'B'),
(1, 'Tahap pertama dalam memecahkan masalah dengan pemrograman adalah?', 'Menulis kode', 'Menguji program', 'Memahami masalah', 'Mendokumentasikan program', 'C'),
(2, 'Struktur yang memilih aksi berdasarkan kondisi disebut?', 'Perulangan', 'Percabangan', 'Fungsi', 'Array', 'B'),
(2, 'Perulangan dengan jumlah iterasi yang sudah diketahui umumnya memakai?', 'while', 'if', 'for', 'switch', 'C'),
(2, 'Perulangan while berhenti ketika?', 'Kondisi bernilai false', 'Kondisi bernilai true', 'Program dimatikan', 'Variabel dideklarasikan', 'A'),
(3, 'Indeks pertama array pada kebanyakan bahasa pemrograman adalah?', '1', '0', '-1', '2', 'B'),
(3, 'Blok kode bernama yang dapat dipanggil berulang disebut?', 'Variabel', 'Komentar', 'Fungsi', 'Konstanta', 'C'),
(3, 'Nilai yang dikirim ke fungsi saat dipanggil disebut?', 'Argumen', 'Return', 'Header', 'Modul', 'A'),
(4, 'Algoritma yang menukar elemen bersebelahan berulang kali adalah?', 'Binary search', 'Bubble sort', 'Linear search', 'Hashing', 'B'),
(4, 'Binary search mensyaratkan data yang?', 'Acak', 'Bertipe string saja', 'Sudah terurut', 'Berjumlah genap', 'C'),
(4, 'Pencarian yang memeriksa elemen satu per satu dari awal disebut?', 'Linear search', 'Binary search', 'Merge sort', 'Quick sort', 'A');
