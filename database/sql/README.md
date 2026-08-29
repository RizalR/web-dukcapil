# SQL MySQL/MariaDB

File `web_dukcapil_mysql.sql` berisi skema database yang setara dengan migration Laravel aplikasi ini. Skema telah divalidasi pada MySQL 8.4.3 Laragon.

## Impor melalui phpMyAdmin

1. Buka phpMyAdmin dari Laragon.
2. Pilih menu **Import**.
3. Pilih file `web_dukcapil_mysql.sql`.
4. Jalankan impor. Database `web_dukcapil` akan dibuat otomatis.

## Konfigurasi Laravel

Ubah bagian database pada `.env`:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=web_dukcapil
DB_USERNAME=root
DB_PASSWORD=
```

Kemudian bersihkan cache konfigurasi dan isi akun/konten awal dari seeder:

```powershell
D:\laragon\laragon\bin\php\php-8.3.26-Win32-vs16-x64\php.exe artisan config:clear
D:\laragon\laragon\bin\php\php-8.3.26-Win32-vs16-x64\php.exe artisan db:seed
```

Isi variabel akun admin di `.env` sebelum menjalankan seeder. Jangan menyimpan kata sandi produksi di file SQL atau repository.
