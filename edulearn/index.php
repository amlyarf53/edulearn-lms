<?php
// Pintu masuk: arahkan ke halaman sesuai status login
require __DIR__ . '/config.php';

if (user()) {
    redirect(beranda(user()['role']));
}
redirect('login.php');
