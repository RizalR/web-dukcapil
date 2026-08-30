# Spesifikasi Hosting Website Dukcapil Kabupaten Gorontalo

## 1. Identitas Aplikasi

| Komponen | Keterangan |
|---|---|
| Nama aplikasi | Website Informasi Dinas Dukcapil Kabupaten Gorontalo |
| Domain produksi | `https://dukcapil.gorontalokab.go.id` |
| Framework | Laravel 13 |
| Bahasa server | PHP 8.3 atau versi 8.x yang kompatibel dengan `^8.3` |
| Frontend | Vite 8 dan Tailwind CSS 4 |
| Database produksi | MySQL 8.0+ atau MariaDB 10.6+ |
| Repository | `https://github.com/RizalR/web-dukcapil.git` |
| Zona waktu | `Asia/Makassar` |

Website ini merupakan portal informasi publik. Aplikasi tidak digunakan untuk mengunggah berkas permohonan warga atau menyimpan data kependudukan individual seperti NIK.

## 2. Kebutuhan Hosting

### Minimum

- Hosting Linux dengan Apache 2.4 atau Nginx.
- PHP 8.3.
- RAM yang tersedia untuk proses PHP minimal 512 MB.
- Ruang penyimpanan minimal 2 GB.
- Satu database MySQL/MariaDB.
- Akses SSL/TLS untuk domain produksi.
- Dukungan `.htaccess` dan `mod_rewrite` jika menggunakan Apache.
- Akses Composer 2 melalui Terminal/SSH atau kemampuan mengunggah folder `vendor` hasil build.
- Kemampuan mengubah document root domain ke folder `public` milik Laravel.

### Rekomendasi Produksi

- PHP 8.3 atau 8.4 dengan patch keamanan terbaru.
- RAM akun minimal 1 GB dan batas memori PHP minimal 256 MB.
- Penyimpanan SSD minimal 5 GB agar tersedia ruang untuk unggahan, log, backup sementara, dan pembaruan aplikasi.
- MySQL 8.0+ atau MariaDB 10.6+ dengan dukungan `utf8mb4`.
- Terminal/SSH dan menu **Git Version Control** di cPanel.
- Cron Job cPanel.
- Backup harian database dan file unggahan, dengan retensi minimal 7 hari.
- Cloudflare atau WAF sejenis di depan server, dengan SSL mode **Full (strict)**.
- Hosting yang menyediakan pemantauan penggunaan CPU, RAM, disk, dan error log.

## 3. Ekstensi PHP

Ekstensi berikut harus aktif:

- `ctype`
- `curl`
- `dom`
- `fileinfo`
- `filter`
- `hash`
- `iconv`
- `intl`
- `json`
- `libxml`
- `mbstring`
- `openssl`
- `pcre`
- `pdo`
- `pdo_mysql`
- `session`
- `tokenizer`
- `xml`
- `xmlreader`
- `xmlwriter`
- `zip`

Ekstensi `zip`, `xmlreader`, dan `xmlwriter` diperlukan untuk pemrosesan workbook Excel statistik kependudukan. Ekstensi `fileinfo` diperlukan untuk validasi berkas unggahan.

Ekstensi berikut direkomendasikan:

- `gd` untuk dukungan pengolahan dan validasi gambar.
- `opcache` untuk meningkatkan performa PHP.
- `redis` hanya jika cache, session, atau queue nantinya dipindahkan ke Redis.

## 4. Konfigurasi PHP

Nilai produksi yang disarankan:

```ini
memory_limit = 256M
max_execution_time = 120
max_input_time = 120
upload_max_filesize = 8M
post_max_size = 10M
max_file_uploads = 20
date.timezone = Asia/Makassar
display_errors = Off
log_errors = On
```

Aplikasi saat ini membatasi:

- Gambar slider, pegawai, dan latar beranda: maksimal 1 MB per berkas.
- Workbook Excel statistik: maksimal 2 MB per berkas.
- Format gambar: JPG, JPEG, PNG, atau WebP.
- Format impor statistik: XLSX.

Batas PHP harus lebih besar daripada batas aplikasi agar Laravel dapat menerima dan memvalidasi berkas tersebut.

## 5. Struktur Direktori Produksi

Repository disarankan berada di luar `public_html`:

```text
/home/USERNAME/repositories/web-dukcapil/
├── app/
├── bootstrap/
├── config/
├── database/
├── public/                 <- document root domain
├── resources/
├── routes/
├── storage/
├── vendor/
└── .env
```

Document root untuk `dukcapil.gorontalokab.go.id` harus diarahkan ke:

```text
/home/USERNAME/repositories/web-dukcapil/public
```

Folder repository, `.env`, `vendor`, `storage`, database backup, dan `.git` tidak boleh menjadi bagian dari document root publik.

## 6. Permission File dan Folder

Gunakan kepemilikan file sesuai pengguna akun cPanel. Rekomendasi permission:

```bash
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;
chmod -R 775 storage bootstrap/cache
```

Jangan menggunakan `chmod -R 777`. Web server harus dapat menulis hanya ke `storage` dan `bootstrap/cache`.

Untuk menampilkan unggahan dari disk publik, jalankan satu kali:

```bash
php artisan storage:link
```

## 7. Database

Konfigurasi produksi menggunakan database MySQL/MariaDB khusus untuk aplikasi ini:

```env
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=nama_database
DB_USERNAME=nama_pengguna_database
DB_PASSWORD=kata_sandi_kuat
```

Pengguna database hanya perlu hak akses terhadap database aplikasi. Jangan menggunakan akun root database.

Aplikasi memakai database untuk session, cache, queue, konten berita, pengguna, statistik agregat, status pencetakan, dan data administrasi lainnya. Setelah deployment, jalankan:

```bash
php artisan migrate --force
```

## 8. Konfigurasi Environment Produksi

Buat `.env` langsung di server dan jangan commit ke Git:

```env
APP_NAME="Dukcapil Kabupaten Gorontalo"
APP_ENV=production
APP_KEY=base64:HASIL_DARI_ARTISAN_KEY_GENERATE
APP_DEBUG=false
APP_URL=https://dukcapil.gorontalokab.go.id
APP_TIMEZONE=Asia/Makassar

APP_LOCALE=id
APP_FALLBACK_LOCALE=id

DUKCAPIL_PUBLIC_URL=https://dukcapil.gorontalokab.go.id
DUKCAPIL_SOCIAL_IMAGE=https://dukcapil.gorontalokab.go.id/images/dukcapil_.jpeg
DUKCAPIL_SERVICE_URL=https://siprima.gorontalokab.go.id/
DUKCAPIL_CONTACT_EMAIL=dukcapil@gorontalokab.go.id

SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax

CACHE_STORE=database
QUEUE_CONNECTION=database

LOG_CHANNEL=stack
LOG_LEVEL=warning
```

Hasil `APP_KEY`, kredensial database, password admin, token, dan private key SSL tidak boleh dibagikan atau disimpan di repository.

## 9. SSL dan Domain

- DNS `dukcapil.gorontalokab.go.id` harus mengarah ke server produksi.
- Sertifikat harus mencakup `dukcapil.gorontalokab.go.id` dan masih berlaku.
- Gunakan AutoSSL/Let's Encrypt pada server, atau Cloudflare Origin CA jika server hanya diakses melalui Cloudflare.
- Jika menggunakan Cloudflare, pilih SSL/TLS mode **Full (strict)**.
- Aktifkan pengalihan HTTP ke HTTPS.
- Port 443 harus dapat diakses.
- Cookie session produksi harus menggunakan `SESSION_SECURE_COOKIE=true`.

## 10. Git dan Deployment

Clone repository melalui **cPanel → Git Version Control** ke:

```text
repositories/web-dukcapil
```

Untuk repository publik gunakan:

```text
https://github.com/RizalR/web-dukcapil.git
```

Untuk repository private gunakan SSH deploy key dengan akses baca saja. Jangan menyimpan GitHub Personal Access Token di repository atau file yang dapat diakses publik.

Urutan deployment setelah melakukan pull:

```bash
cd ~/repositories/web-dukcapil
composer install --no-dev --prefer-dist --optimize-autoloader
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Jika Node.js tersedia pada server:

```bash
npm ci
npm run build
```

Jika Node.js tidak tersedia, jalankan `npm ci` dan `npm run build` di komputer lokal atau pipeline deployment, kemudian pastikan hasil `public/build` ikut dikirim ke produksi.

Sebelum deployment yang mengubah database, aktifkan mode pemeliharaan:

```bash
php artisan down --retry=60
```

Setelah deployment selesai:

```bash
php artisan up
```

Jika deployment gagal, aplikasi jangan dibiarkan dalam keadaan setengah terbarui. Simpan backup database dan commit terakhir yang stabil untuk proses rollback.

## 11. Cron dan Queue

Tambahkan Cron Job cPanel berikut agar Laravel Scheduler siap digunakan:

```cron
* * * * * cd /home/USERNAME/repositories/web-dukcapil && /usr/local/bin/php artisan schedule:run >> /dev/null 2>&1
```

Sesuaikan lokasi executable PHP dengan server cPanel. Queue worker belum wajib dijalankan terus-menerus apabila belum ada proses asynchronous aktif. Jika kelak digunakan, jalankan worker melalui Supervisor atau fasilitas process manager dari penyedia hosting, bukan melalui proses Terminal yang dibiarkan terbuka.

## 12. Email

Jika aplikasi akan mengirim notifikasi atau reset password melalui email, sediakan akun SMTP resmi dan ubah konfigurasi mail dari `log` menjadi `smtp`:

```env
MAIL_MAILER=smtp
MAIL_HOST=host_smtp
MAIL_PORT=587
MAIL_USERNAME=akun_smtp
MAIL_PASSWORD=kata_sandi_smtp
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=dukcapil@gorontalokab.go.id
MAIL_FROM_NAME="Dukcapil Kabupaten Gorontalo"
```

Kredensial SMTP hanya disimpan dalam `.env` produksi.

## 13. Backup dan Pemulihan

Backup minimal harus mencakup:

- Database MySQL/MariaDB.
- Folder `storage/app/public` yang berisi unggahan.
- File `.env` melalui penyimpanan rahasia yang terenkripsi.
- Konfigurasi domain, SSL, Cron Job, dan versi PHP cPanel.

Rekomendasi jadwal:

- Database: setiap hari.
- File unggahan: setiap hari.
- Backup penuh akun: setiap minggu.
- Retensi: minimal 7 backup harian dan 4 backup mingguan.
- Uji pemulihan backup: minimal setiap tiga bulan.

Backup tidak boleh disimpan hanya pada server produksi yang sama.

## 14. Keamanan Produksi

- Wajib menggunakan HTTPS.
- `APP_DEBUG` wajib `false`.
- Gunakan kata sandi admin dan database yang unik serta kuat.
- Aktifkan MFA pada akun cPanel, GitHub, Cloudflare, dan Super Admin jika tersedia.
- Batasi akses SSH berdasarkan key.
- Jangan mengedit file produksi langsung kecuali untuk pemulihan darurat.
- Terapkan pembaruan melalui Git agar perubahan dapat diaudit dan dipulihkan.
- Pantau `storage/logs/laravel.log`, error log PHP, access log, dan penggunaan disk.
- Lindungi halaman admin menggunakan rate limiting dan mekanisme autentikasi aplikasi.
- Jangan menyimpan NIK, berkas permohonan, atau data penduduk individual pada website informasi ini.

## 15. Pemeriksaan Setelah Deployment

Pastikan semua pemeriksaan berikut berhasil:

- `https://dukcapil.gorontalokab.go.id` dapat dibuka tanpa peringatan SSL.
- HTTP otomatis dialihkan ke HTTPS.
- Halaman publik, login, dan panel admin dapat diakses sesuai izin.
- Upload gambar dan workbook XLSX berfungsi.
- Gambar dari `storage` dapat ditampilkan.
- Migrasi database berhasil.
- Cache konfigurasi dan route berhasil dibuat.
- `/robots.txt` dapat dibuka.
- `/sitemap.xml` dapat dibuka.
- `APP_DEBUG=false` dan halaman error tidak menampilkan stack trace.
- Backup pertama telah dibuat dan dapat ditemukan.
- Tidak ada `.env`, `.git`, log, atau backup database yang dapat diakses dari web.

## 16. Ringkasan Kelayakan Hosting

Hosting dinyatakan layak jika memenuhi seluruh persyaratan berikut:

- PHP 8.3+ beserta ekstensi wajib tersedia.
- Composer 2 dan Git tersedia, atau penyedia hosting mendukung deployment hasil build.
- Document root dapat diarahkan ke folder Laravel `public`.
- MySQL 8.0+ atau MariaDB 10.6+ tersedia.
- SSL aktif dan mendukung HTTPS penuh hingga origin server.
- Folder `storage` dan `bootstrap/cache` dapat ditulis dengan aman.
- Tersedia Cron Job, backup, error log, dan kapasitas minimal yang dipersyaratkan.
- Penyedia hosting mengizinkan symlink untuk `php artisan storage:link`, atau menyediakan alternatif mapping folder storage yang aman.
