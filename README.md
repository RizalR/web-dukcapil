# Portal Informasi Dukcapil Kabupaten Gorontalo

Website informasi publik berbasis Laravel 13 untuk Dinas Kependudukan dan Pencatatan Sipil Kabupaten Gorontalo. Aplikasi ini menampilkan berita, profil dinas, agregat kependudukan, status bahan pencetakan, dokumen publik, profil pegawai, kontak, serta tautan aplikasi pegawai.

Website ini **bukan aplikasi pengajuan dokumen kependudukan**. Pengguna diarahkan ke SI PRIMA untuk pelayanan dokumen.

## Fitur

- Halaman publik responsif dan pencarian lintas berita, dokumen, serta pegawai.
- Dashboard agregat kependudukan dan ekspor CSV.
- Status bahan pencetakan per lokasi dengan waktu pemeriksaan.
- Panel admin dengan peran `super_admin` dan `news_editor`.
- Workflow berita draf → verifikasi super admin → terbit.
- Audit log untuk login dan perubahan konten penting.
- Pemeriksaan tautan eksternal terjadwal setiap hari pukul 06.00 WITA.
- Header keamanan, CSRF, rate limiting login, session rotation, policy/gate, dan output escaping bawaan Blade.

## Menjalankan Lokal

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install
npm run build
php artisan serve
```

### Windows dengan Laragon

Jika `php -v` masih menampilkan PHP 8.2 dari XAMPP, jalankan aplikasi secara eksplisit dengan PHP 8.3 milik Laragon:

```powershell
D:\laragon\laragon\bin\php\php-8.3.26-Win32-vs16-x64\php.exe artisan serve
```

Untuk memperbaiki terminal secara permanen, buka **Laragon → Menu → Tools → Path → Add Laragon to Path**, kemudian tutup dan buka kembali terminal. Pastikan `where.exe php` menunjuk ke Laragon dan `php -v` menampilkan versi minimal 8.3 sebelum menjalankan Artisan atau Composer.

Isi variabel `DUKCAPIL_SUPER_ADMIN_*` dan `DUKCAPIL_NEWS_EDITOR_*` di `.env` sebelum menjalankan seeder. Kredensial tidak disimpan di source code.

## Verifikasi

```bash
php artisan test --compact
vendor/bin/pint
composer audit
npm audit
npm run build
php artisan schedule:list
```

## Operasional Produksi

- Arahkan document root web server ke folder `public/`.
- Gunakan HTTPS, set `APP_DEBUG=false`, `APP_ENV=production`, dan `SESSION_SECURE_COOKIE=true`.
- Jalankan `php artisan schedule:run` setiap menit melalui scheduler server.
- Gunakan database produksi dengan akun berhak minimum; jangan memakai SQLite untuk trafik produksi multi-instance.
- Terapkan backup database, uji pemulihan, retensi log, monitoring, dan prosedur respons insiden.
- Ganti kata sandi bootstrap, gunakan email kedinasan, dan aktifkan MFA untuk super admin sebelum peluncuran.

Lihat [PROJECT_CHECKLIST.md](PROJECT_CHECKLIST.md) untuk status implementasi dan kesiapan peluncuran.
