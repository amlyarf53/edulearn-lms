// app.js - tombol menu untuk tampilan ponsel (membuka dan menutup sidebar)
var tombolMenu = document.getElementById('tombolMenu');
var tirai = document.getElementById('tirai');

if (tombolMenu) {
    tombolMenu.addEventListener('click', function () {
        document.body.classList.toggle('menu-buka');
    });
    tirai.addEventListener('click', function () {
        document.body.classList.remove('menu-buka');
    });
}
