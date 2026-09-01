# SQL MySQL/MariaDB

File `web_dukcapil_mysql.sql` berisi skema dasar database yang setara dengan migration Laravel aplikasi ini. File `update_2026_08_31.sql` melengkapi skema dengan seluruh pembaruan berikutnya. Keduanya menggunakan database `dukc_kabGTLO` yang sudah tersedia dan telah divalidasi pada MySQL 8.4.3 Laragon.

## Impor melalui phpMyAdmin

1. Buka phpMyAdmin dari Laragon.
2. Pastikan database kosong `dukc_kabGTLO` sudah tersedia.
3. Pilih database `dukc_kabGTLO`, lalu buka menu **Import**.
4. Impor file `web_dukcapil_mysql.sql`.
5. Setelah berhasil, impor file `update_2026_08_31.sql`. Skrip update juga membangun ulang foreign key agar tidak membawa referensi database lama `web_dukcapil`.

Kedua file hanya mengisi database yang sudah tersedia dan tidak membuat database baru.

## Konfigurasi Laravel

Ubah bagian database pada `.env`:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=dukc_kabGTLO
DB_USERNAME=root
DB_PASSWORD=
```

Kemudian bersihkan cache konfigurasi dan isi akun/konten awal dari seeder:

```powershell
D:\laragon\laragon\bin\php\php-8.3.26-Win32-vs16-x64\php.exe artisan config:clear
D:\laragon\laragon\bin\php\php-8.3.26-Win32-vs16-x64\php.exe artisan db:seed
```

Isi variabel akun admin di `.env` sebelum menjalankan seeder. Jangan menyimpan kata sandi produksi di file SQL atau repository.
