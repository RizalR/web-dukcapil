# Checklist Website Informasi Dukcapil

Pembaruan terakhir: 28 Agustus 2026 (WITA).

## Tahap 1 — MVP

- [x] Beranda dengan berita, jam pelayanan, agregat, dan status bahan.
- [x] Informasi pelayanan dengan pengalihan jelas ke SI PRIMA.
- [x] Profil, visi dan misi, serta gambaran struktur organisasi.
- [x] Daftar dan detail berita.
- [x] Dokumen publik per kategori.
- [x] Profil pegawai tanpa data pribadi sensitif.
- [x] Kontak dan tautan peta.
- [x] Tautan aplikasi pegawai.
- [x] Pencarian publik.
- [x] Login dan panel administrasi.

## Tahap 2

- [x] Dashboard agregat kependudukan dengan periode, wilayah, satuan, dan sumber.
- [x] Status bahan pencetakan per lokasi, status tekstual, catatan, dan waktu pembaruan.
- [x] Ekspor CSV data terbit dengan mitigasi formula injection.
- [x] Workflow berita draf, verifikasi, terbit, dan tarik publikasi.
- [x] Audit log login, berita, statistik, dan status bahan.
- [x] Pemeriksa tautan eksternal terjadwal.

## Keamanan dan Kualitas

- [x] Otorisasi berbasis policy/gate untuk super admin dan editor berita.
- [x] Editor tidak dapat menerbitkan berita atau mengelola statistik.
- [x] Validasi server-side, CSRF, hashing kata sandi, rotasi sesi, dan rate limiting login.
- [x] Header CSP, anti-framing, MIME sniffing protection, referrer policy, dan permissions policy.
- [x] Eksternal link memakai `noopener noreferrer`.
- [x] Rahasia akun berada di `.env`, bukan source code.
- [x] 14 test lulus dengan 57 assertion.
- [x] Laravel Pint lulus.
- [x] Blade template berhasil dikompilasi.
- [x] Build Vite produksi berhasil.
- [x] `npm audit`: 0 kerentanan.
- [x] `composer audit`: tidak ada advisori kerentanan.
- [x] HTTP smoke test halaman utama, profil, informasi, berita, login, CSS, dan JS: status 200.
- [ ] QA visual desktop dan seluler di browser — browser tidak tersedia pada sesi build ini.

## Wajib Sebelum Produksi

- [ ] Verifikasi dan setujui nama pegawai, struktur organisasi, seluruh dokumen publik, kontak, dan tautan aplikasi oleh pejabat pemilik informasi.
- [ ] Ganti akun `.local` dan kata sandi bootstrap dengan akun kedinasan; aktifkan MFA super admin.
- [ ] Set HTTPS, `APP_DEBUG=false`, `SESSION_SECURE_COOKIE=true`, dan konfigurasi proxy tepercaya.
- [ ] Daftarkan/selaraskan PSE Lingkup Publik dan klasifikasi data sesuai Permenkomdigi Nomor 5 Tahun 2025.
- [ ] Tetapkan SOP backup, uji pemulihan, retensi audit log, monitoring, dan respons insiden bersama Diskominfo/CSIRT.
- [ ] Jalankan scheduler server untuk `php artisan schedule:run` setiap menit.
- [ ] Lakukan vulnerability assessment dan penetration test sebelum go-live.
- [ ] Lakukan uji aksesibilitas keyboard, pembaca layar, kontras, desktop, dan seluler pada browser nyata.

## Acuan Operasional

- Permenkomdigi Nomor 5 Tahun 2025 tentang PSE Lingkup Publik: https://jdih.komdigi.go.id/produk_hukum/view/id/962/
- Perpres Nomor 95 Tahun 2018 tentang SPBE: https://peraturan.bpk.go.id/Details/96913/
- Perpres Nomor 82 Tahun 2023 tentang Percepatan Transformasi Digital: https://peraturan.bpk.go.id/Details/273981/
- UU Nomor 27 Tahun 2022 tentang Pelindungan Data Pribadi: https://peraturan.bpk.go.id/Details/229798/
