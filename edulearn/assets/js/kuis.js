// kuis.js - mengatur halaman pengerjaan kuis:
// satu soal tampil sekaligus, navigasi nomor soal, dan timer hitung mundur.

var form    = document.getElementById('formKuis');
var daftar  = form.querySelectorAll('.soal');       // semua soal
var nomor   = form.querySelectorAll('.nomor');      // tombol navigasi 1..N
var segmen  = form.querySelectorAll('.segmen span'); // garis kemajuan di atas
var posisi  = document.getElementById('posisi');    // teks "3 / 10"
var tombolSebelum = document.getElementById('sebelum');
var tombolLanjut  = document.getElementById('lanjut');
var aktif = 0;                                      // nomor soal yang sedang tampil (mulai dari 0)

// Menampilkan soal ke-i, menyembunyikan yang lain
function tampilkan(i) {
    aktif = i;
    for (var n = 0; n < daftar.length; n++) {
        daftar[n].hidden = (n !== i);
    }
    perbarui();
}

// Memperbarui penanda: nomor yang dijawab, nomor aktif, garis kemajuan
function perbarui() {
    for (var n = 0; n < daftar.length; n++) {
        var sudahDijawab = daftar[n].querySelector('input:checked') !== null;
        nomor[n].classList.toggle('dijawab', sudahDijawab);
        nomor[n].classList.toggle('aktif', n === aktif);
        segmen[n].classList.toggle('terisi', n <= aktif);
    }
    posisi.textContent = (aktif + 1) + ' / ' + daftar.length;
    tombolSebelum.disabled = (aktif === 0);
    tombolLanjut.disabled  = (aktif === daftar.length - 1);
}

tombolSebelum.addEventListener('click', function () { tampilkan(aktif - 1); });
tombolLanjut.addEventListener('click',  function () { tampilkan(aktif + 1); });
for (var n = 0; n < nomor.length; n++) {
    (function (i) { nomor[i].addEventListener('click', function () { tampilkan(i); }); })(n);
}
form.addEventListener('change', perbarui);          // saat memilih jawaban

// ---------- Timer hitung mundur ----------
var sisa = parseInt(form.dataset.sisa, 10);         // sisa detik dari server
var layarWaktu = document.getElementById('sisaWaktu');
var otomatis = false;                               // true = dikirim karena waktu habis

function dua(angka) { return (angka < 10 ? '0' : '') + angka; }

function detak() {
    layarWaktu.textContent = dua(Math.floor(sisa / 60)) + ':' + dua(sisa % 60);
    if (sisa <= 0) {
        otomatis = true;
        form.submit();                              // waktu habis: kirim otomatis
        return;
    }
    sisa--;
}

// ---------- Konfirmasi sebelum mengirim ----------
form.addEventListener('submit', function (e) {
    if (otomatis) return;
    var belum = form.querySelectorAll('.nomor:not(.dijawab)').length;
    var pesan = belum > 0
        ? 'Masih ada ' + belum + ' soal yang belum dijawab. Kirim sekarang?'
        : 'Kirim jawaban sekarang?';
    if (!confirm(pesan)) e.preventDefault();
});

tampilkan(0);
detak();
setInterval(detak, 1000);
