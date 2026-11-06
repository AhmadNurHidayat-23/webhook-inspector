# Webhook Inspector

Pemeriksaan dan pengujian webhook dengan endpoint privat pengguna dan penerima pengujian lokal.

## Fitur

- Endpoint UUID + token acak X-Webhook-Token, HTTP method, header/query/body JSON atau form yang telah diredaksi, timestamp, dan riwayat.
- Batas payload 64 KB, header 16 KB, frekuensi 60 request/menit per endpoint/IP, serta 1.000 request per endpoint.
- Salin payload, status respons, pengiriman ulang, dan hapus riwayat dengan konfirmasi HAPUS.
- Replay hanya ke penerima lokal bawaan dengan challenge HMAC; tidak menerima URL bebas, redirect, atau meneruskan kredensial asli.
- Payload non-JSON/form disembunyikan; redaksi berdasarkan nama key dan pola credential, bukan jaminan klasifikasi seluruh isi bebas.

## Teknologi

Laravel 13, React, Inertia.js, Tailwind CSS, SQLite, PHPUnit.

## Cara menjalankan

Memerlukan PHP 8.4+, Composer, dan Node.js 22.12+. Arahkan document root Laragon ke folder `public`.

```sh
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm ci
npm run build
php artisan serve
```

Buka http://127.0.0.1:8000. Jalankan `php artisan test` untuk pengujian backend.

Buat akun melalui halaman pendaftaran. Di terminal kedua, jalankan `php artisan inspector:receiver` untuk mengaktifkan target replay lokal pada 127.0.0.1:48112. Kirim webhook ke URL endpoint menggunakan header `X-Webhook-Token` dari akun Anda. Tidak ada tunnel publik otomatis.
