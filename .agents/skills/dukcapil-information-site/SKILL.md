---
name: dukcapil-information-site
description: Rencanakan, bangun, ubah, dan verifikasi website informasi publik Dinas Dukcapil Kabupaten Gorontalo dalam repository ini. Gunakan untuk pekerjaan produk, Laravel, antarmuka, konten, admin, statistik, keamanan, dan pengujian situs ini; jangan mengubahnya menjadi sistem pelayanan dokumen atau mengerjakan Tahap 3 tanpa permintaan eksplisit.
metadata:
  short-description: Pedoman kerja website informasi Dukcapil
---

# Website Informasi Dinas Dukcapil

## Tujuan Produk

Bangun website sebagai media informasi resmi Dinas Dukcapil Kabupaten Gorontalo. Website ini bukan tempat mengajukan atau memproses dokumen kependudukan. Arahkan kebutuhan pelayanan dokumen ke website pelayanan resmi melalui tautan eksternal yang jelas dan terverifikasi.

Jangan menyimpan atau memproses NIK, berkas permohonan, atau data penduduk individual di website ini.

## Keputusan Lingkup

Pertahankan ruang lingkup berikut sampai pengguna mengubahnya secara eksplisit.

### Tahap 1 — MVP

- Beranda.
- Informasi pelayanan berupa ringkasan dan tautan menuju website pelayanan terpisah.
- Profil dinas.
- Berita.
- Dokumen publik.
- Profil pegawai.
- Kontak dan peta.
- Tautan aplikasi pegawai.
- Panel administrasi dan pencarian publik.

### Tahap 2

- Dashboard agregat kependudukan.
- Status ketersediaan bahan pencetakan per lokasi.
- Ekspor data agregat ke CSV.
- Alur verifikasi konten.
- Audit log.
- Pemeriksaan tautan secara berkala.

### Di Luar Lingkup Saat Ini

- Pengajuan atau pemrosesan dokumen kependudukan.
- Unggah berkas warga.
- Antrean daring dan pelacakan permohonan.
- Integrasi transaksi kependudukan, SSO lintas aplikasi, dan fitur Tahap 3 lainnya.

Jangan memperluas aplikasi ke area tersebut hanya karena implementasinya memungkinkan.

## Peran Administrasi

Terapkan hak akses berdasarkan peran, bukan berdasarkan alamat email atau pemeriksaan kondisional tersebar.

- **Super Admin** memiliki akses penuh ke panel, pengelolaan pengguna dan hak akses, konten umum, dokumen publik, profil pegawai, tautan, pengaturan, serta input dan pengelolaan statistik kependudukan.
- **Editor Berita** hanya mengelola konten berita sesuai izin yang diberikan. Editor tidak boleh mengubah statistik, pengguna, konfigurasi, atau audit log.

Sebelum membuat alur publikasi, tanyakan apakah Editor Berita boleh menerbitkan langsung atau hanya membuat draf untuk disetujui Super Admin. Rekomendasikan alur draf, tinjau, lalu terbit jika pemisahan tugas diperlukan.

Gunakan prinsip hak akses minimum dan tambahkan pengujian yang membuktikan pengguna tidak dapat mengakses fungsi di luar perannya.

## Protokol Kerja

1. Baca `AGENTS.md`, skill yang relevan, dan aturan repository sebelum merencanakan atau mengubah file.
2. Periksa struktur, konvensi, dependensi, serta perubahan yang sudah ada. Pertahankan pekerjaan pengguna yang tidak terkait.
3. Nyatakan lingkup dan kriteria penerimaan untuk tugas yang sedang dikerjakan.
4. Buat checklist pekerjaan sebelum implementasi untuk tugas bertahap.
5. Implementasikan perubahan terkecil yang memenuhi kebutuhan dan mengikuti pola Laravel yang sudah digunakan aplikasi.
6. Periksa kembali perubahan, jalankan verifikasi yang sebanding dengan risikonya, lalu laporkan hasilnya dengan checklist.

## Kapan Harus Bertanya

Tanyakan kepada pengguna apabila ketidakjelasan dapat mengubah ruang lingkup, struktur data, desain utama, hak akses, sumber data, keamanan, atau perilaku yang terlihat oleh publik.

Saat membutuhkan keputusan:

- Jelaskan bagian yang belum pasti secara singkat.
- Berikan dua atau tiga pilihan yang benar-benar berbeda.
- Tandai satu pilihan yang direkomendasikan beserta dampaknya.
- Jangan menebak pada keputusan berisiko tinggi atau yang sulit dibatalkan.

Untuk detail kecil yang berisiko rendah, gunakan asumsi wajar agar pekerjaan tetap maju dan sebutkan asumsi tersebut dalam laporan akhir.

## Data dan Konten

- Statistik kependudukan harus agregat, memiliki periode, wilayah, kategori, satuan, sumber, waktu pembaruan, dan pihak yang memperbarui.
- Jangan menampilkan kelompok data yang cukup kecil untuk mengidentifikasi seseorang. Tanyakan ambang penyamaran atau penggabungan kategori kepada pemilik data sebelum implementasi.
- Ekspor CSV hanya boleh memuat data agregat yang sama atau lebih aman daripada data yang tampil di publik.
- Status tinta dan film harus memuat lokasi, status tekstual, catatan, dan waktu pembaruan. Jangan hanya mengandalkan warna dan jangan menampilkan jumlah stok internal tanpa persetujuan eksplisit.
- Berita dan dokumen publik harus memiliki status publikasi, slug unik, metadata, dan waktu pembaruan.
- Tautan pelayanan dan aplikasi pegawai harus menuju domain resmi, diberi penanda tautan eksternal, dan dibuka dengan perlindungan `noopener` serta `noreferrer` bila menggunakan tab baru.
- Profil pegawai hanya memuat data kedinasan yang disetujui untuk publik. Jangan memublikasikan NIP lengkap, alamat, nomor pribadi, tanggal lahir, atau data sensitif lainnya.

## Keamanan

Sebelum keputusan keamanan atau peluncuran, periksa ketentuan resmi terbaru dari Komdigi, BSSN, SPBE, Keterbukaan Informasi Publik, dan Pelindungan Data Pribadi. Jangan mengandalkan ingatan terhadap versi regulasi atau standar.

Minimal terapkan:

- Validasi server-side, encoding keluaran, perlindungan CSRF, dan query terparameterisasi melalui API Laravel.
- Autentikasi yang aman, hashing kata sandi bawaan Laravel, rate limiting, rotasi sesi setelah login, dan logout yang benar.
- Otorisasi melalui policy, gate, atau mekanisme terpusat yang sesuai; default-kan akses menjadi ditolak.
- MFA untuk Super Admin bila mekanisme autentikasi yang dipilih mendukungnya. Jika belum masuk lingkup, sampaikan sebagai risiko sebelum peluncuran.
- Pembatasan jenis MIME, ukuran, nama, dan lokasi penyimpanan unggahan. Simpan berkas di lokasi non-eksekusi dan jangan mempercayai ekstensi dari pengguna.
- Audit log untuk login penting serta perubahan pengguna, peran, statistik, berita, dokumen, dan konfigurasi tanpa merekam kata sandi, token, atau data sensitif.
- Rahasia hanya di environment/configuration; jangan menulis kredensial ke source code, log, contoh data, atau respons error.
- HTTPS, cookie aman, header keamanan, backup teruji, pembaruan dependensi, dan pemantauan log sebagai syarat kesiapan produksi.

Gunakan rekomendasi OWASP yang relevan sebagai baseline teknis tambahan, tanpa menggantikan ketentuan resmi pemerintah Indonesia.

## Verifikasi Wajib

Sesuaikan pemeriksaan dengan perubahan, tetapi jangan menyatakan selesai sebelum melakukan hal-hal yang relevan berikut:

- Tinjau diff atau seluruh file yang dibuat untuk memastikan tidak ada perubahan di luar lingkup.
- Jalankan formatter yang diwajibkan repository untuk file yang berubah.
- Jalankan pengujian paling sempit yang membuktikan perubahan, lalu perluas pengujian jika risikonya tinggi.
- Uji otorisasi untuk setiap peran dan setidaknya satu kasus akses yang harus ditolak.
- Uji validasi input, unggahan, pencarian, ekspor, dan kondisi kosong bila fitur tersebut berubah.
- Periksa tampilan desktop dan seluler, navigasi keyboard, fokus, label formulir, kontras, dan teks alternatif yang relevan.
- Periksa tautan internal dan eksternal, status kosong, status gagal, serta informasi waktu pembaruan.
- Pastikan tidak ada rahasia, NIK, data individu, atau informasi pegawai sensitif dalam source code, fixture, log, maupun output publik.

Jika pemeriksaan tidak dapat dijalankan, jelaskan pemeriksaan yang terlewat, penyebabnya, dan risikonya. Jangan menandainya selesai.

## Checklist Laporan

Pada akhir setiap tugas implementasi, berikan checklist ringkas yang dapat diverifikasi:

```markdown
## Checklist

- [x] Perubahan yang benar-benar selesai
- [x] Pengujian atau pemeriksaan yang berhasil
- [ ] Pekerjaan yang belum selesai — alasan atau keputusan yang masih dibutuhkan
```

Pisahkan dengan jelas hasil yang sudah dibuat, hasil yang sudah diuji, dan pekerjaan yang masih tertunda. Jangan mencentang pekerjaan hanya karena kodenya sudah ditulis.

