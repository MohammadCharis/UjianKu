# UjianKu - Sistem Ujian Online SMA/SMK

Aplikasi ujian online berbasis web untuk SMA/SMK dengan fitur pemantauan pelanggaran siswa secara real-time. Dirancang mobile-first untuk siswa dan guru, serta nyaman di desktop untuk admin.

## Stack

- Laravel 13, PHP 8.3, MySQL
- Blade Template + Tailwind CSS v4
- Laravel Breeze (Blade) untuk autentikasi
- JavaScript vanilla + Fetch API
- Laravel Excel untuk import/export
- DomPDF untuk ekspor PDF
- PWA (manifest + service worker)

## Role Pengguna

- `admin`: data master, pengaturan, laporan
- `guru`: bank soal, ujian, pengawasan, koreksi esai, laporan
- `siswa`: daftar ujian, kerjakan ujian, lihat hasil

## Fitur Utama

- Login berbasis role dengan middleware `role:admin,guru,siswa`
- Bank soal PG, isian, dan esai, bisa bergambar
- Pembuatan ujian: jadwal, durasi, token, batas pelanggaran, mode kunci, acak soal
- Ujian siswa mobile-first: timer server, navigasi soal, tandai ragu, autosave tahan offline
- Deteksi pelanggaran desktop/mobile dengan toleransi 3 detik dan durasi keluar
- Dashboard guru realtime (polling 5 detik, badge, bunyi, getar)
- Koreksi esai manual dan laporan nilai/pelanggaran
- Ekspor Excel/PDF
- PWA dan header keamanan

## Instalasi

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Atur database pada `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ujianKu
DB_USERNAME=root
DB_PASSWORD=
```

Lanjutkan:

```bash
php artisan migrate --seed
php artisan storage:link
npm install
npm run build
php artisan serve
```

Akses aplikasi di `http://127.0.0.1:8000`.

## Akun Demo

Password semua akun: `password`

- Admin: `admin@ujianku.test`
- Guru: `guru1@ujianku.test`, `guru2@ujianku.test`
- Siswa: `siswa01@ujianku.test` sampai `siswa30@ujianku.test`

Token ujian demo (jika dibuat): `DEMO123`

## Pengujian

```bash
php artisan test --compact
vendor/bin/pint --dirty --format agent
npm run build
```

## PWA

- Manifest: `/manifest.json`
- Service worker: `/sw.js`
- Aset ikon: `/icons/icon-192.png`, `/icons/icon-512.png`

## Catatan Keamanan

- Timer dan validasi waktu dilakukan di server
- Endpoint jawaban/pelanggaran dibatasi pemilik sesi aktif
- Kunci jawaban tidak dikirim ke browser siswa
- Form Request, Policy/Gate, dan rate limiting digunakan pada endpoint sensitif
- Header keamanan aktif melalui middleware `SecurityHeaders`
