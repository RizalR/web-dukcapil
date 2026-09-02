# Ringkasan Fitur Aduan

Status dokumen: rancangan awal untuk dibahas sebelum implementasi.

## 1. Ringkasan

Fitur aduan menyediakan alur bagi pemohon untuk menyampaikan keluhan, admin untuk memeriksa dan menindaklanjuti keluhan, serta masyarakat untuk melihat hasil tindak lanjut yang telah dinyatakan aman dan layak dipublikasikan.

Data aduan dan identitas pemohon tetap berada pada sistem privat, yaitu SI PRIMA atau modul internal yang berwenang. Website informasi Dukcapil hanya menyimpan atau menerima salinan publik yang sudah disanitasi dan disetujui.

Prinsip utama fitur:

- Aduan mentah tidak dipublikasikan secara otomatis.
- Identitas pemohon, data kependudukan, dokumen, dan catatan internal tidak dikirim ke website publik.
- Informasi yang dipublikasikan adalah ringkasan aduan, tanggapan resmi, hasil penyelesaian, dan status tindak lanjut.
- Aduan yang mengandung dugaan korupsi, pelanggaran pegawai, atau penyalahgunaan wewenang dipisahkan ke alur Whistleblowing System (WBS) yang bersifat rahasia.

## 2. Tujuan

- Menyediakan kanal aduan yang jelas dan mudah digunakan pemohon.
- Memastikan setiap aduan memiliki penanggung jawab dan rekam tindak lanjut.
- Memberikan transparansi mengenai respons dan penyelesaian aduan pelayanan.
- Melindungi data pribadi pemohon, pegawai, dan pihak lain yang disebutkan dalam aduan.
- Menyediakan data agregat untuk evaluasi kualitas pelayanan.

## 3. Batas Sistem

### Sistem privat: SI PRIMA atau modul internal

Sistem privat menangani:

- Form aduan pemohon.
- Identitas dan akun pemohon.
- Referensi permohonan atau layanan.
- Isi aduan lengkap.
- Lampiran atau bukti.
- Komunikasi antara pemohon dan admin.
- Catatan pemeriksaan dan tindak lanjut internal.
- Penetapan hasil akhir aduan.
- Penyusunan dan persetujuan ringkasan publik.

### Website informasi Dukcapil

Website informasi hanya menangani:

- Halaman Transparansi Aduan.
- Statistik aduan yang telah diagregasi.
- Daftar ringkasan hasil tindak lanjut yang disetujui.
- Detail publik yang tidak mengandung data pribadi.
- Waktu terakhir pembaruan data dan penjelasan metodologi publikasi.

Website informasi tidak menyimpan NIK, nomor KK, berkas permohonan, lampiran aduan, atau percakapan privat pemohon.

## 4. Peran Pengguna

### Pemohon

- Mengirim aduan melalui form privat.
- Melihat nomor tiket dan perkembangan aduannya sendiri.
- Memberikan informasi tambahan bila diminta.
- Menerima tanggapan dan hasil penyelesaian.

### Admin Aduan

- Memverifikasi kelengkapan dan kategori aduan.
- Menetapkan penanggung jawab.
- Memperbarui status dan mencatat tindak lanjut.
- Berkomunikasi dengan pemohon melalui kanal privat.
- Menyusun tanggapan resmi dan ringkasan publik.
- Menandai aduan yang harus dialihkan ke WBS atau kanal khusus lainnya.

### Reviewer/PPID

- Memeriksa ringkasan publik dan risiko pengungkapan identitas.
- Memastikan data pribadi dan catatan internal telah dihapus.
- Menyetujui, menolak, atau meminta perbaikan materi publikasi.
- Menarik publikasi bila ditemukan kesalahan atau risiko privasi.

### Masyarakat

- Melihat statistik pengaduan.
- Melihat ringkasan aduan yang telah selesai ditindaklanjuti.
- Membaca tanggapan resmi dan hasil penyelesaian.

Peran Admin Aduan dan Reviewer/PPID tidak boleh digantikan oleh peran Editor Berita. Hak akses diberikan berdasarkan tugas dan menggunakan prinsip hak akses minimum.

## 5. Alur Utama

```text
Pemohon mengirim aduan
        ↓
Admin memverifikasi dan mengklasifikasikan
        ↓
Admin melakukan tindak lanjut
        ↓
Admin menyampaikan hasil kepada pemohon
        ↓
Admin membuat ringkasan publik terpisah
        ↓
Reviewer/PPID memeriksa dan menyetujui
        ↓
Salinan publik dikirim ke website informasi
        ↓
Hasil tindak lanjut tampil pada halaman Transparansi Aduan
```

Aduan tidak boleh masuk ke halaman publik hanya karena status penanganannya sudah selesai. Publikasi memerlukan status persetujuan terpisah.

## 6. Status Aduan Internal

Status penanganan yang disarankan:

- `Dikirim`: aduan telah diterima sistem.
- `Diverifikasi`: isi dan kewenangan penanganan telah diperiksa.
- `Perlu Informasi Tambahan`: pemohon diminta melengkapi informasi.
- `Diproses`: unit atau petugas sedang menindaklanjuti.
- `Selesai`: tanggapan atau penyelesaian telah diberikan kepada pemohon.
- `Ditolak`: aduan tidak dapat diproses dengan alasan yang tercatat.
- `Dialihkan`: aduan diteruskan ke instansi, unit, atau kanal berwenang.

Status publikasi dikelola secara terpisah:

- `Tidak Dipublikasikan`.
- `Draf Ringkasan`.
- `Menunggu Tinjauan`.
- `Perlu Perbaikan`.
- `Disetujui`.
- `Dipublikasikan`.
- `Ditarik`.

## 7. Data Form Aduan Privat

Data minimum yang dapat disiapkan pada form pemohon:

- Kategori aduan.
- Judul atau topik aduan.
- Uraian kejadian.
- Layanan yang berkaitan.
- Waktu kejadian.
- Harapan penyelesaian.
- Referensi permohonan bila diperlukan.
- Lampiran pendukung bila benar-benar diperlukan.
- Persetujuan terhadap pemberitahuan privasi.

Identitas akun pemohon tidak perlu diminta ulang jika sudah tersedia secara sah di SI PRIMA. Data yang dikumpulkan harus terbatas pada kebutuhan penanganan aduan.

Form harus menjelaskan bahwa aduan mentah tidak dipublikasikan. Bila suatu ringkasan hasil akan dipublikasikan, identitas dan informasi yang dapat mengidentifikasi pemohon akan dihapus terlebih dahulu.

## 8. Data Tindak Lanjut Admin

Admin membutuhkan field internal berikut:

- Nomor tiket internal.
- Kategori dan tingkat prioritas.
- Unit atau petugas penanggung jawab.
- Status penanganan.
- Catatan tindak lanjut internal.
- Tanggapan kepada pemohon.
- Hasil penyelesaian.
- Tanggal tanggapan pertama dan tanggal selesai.
- Alasan penolakan atau pengalihan, bila ada.
- Penanda WBS atau data sensitif.
- Ringkasan aduan untuk publik.
- Ringkasan tanggapan atau penyelesaian untuk publik.
- Status dan catatan pemeriksaan publikasi.
- Petugas yang membuat, meninjau, menyetujui, menerbitkan, atau menarik publikasi.

Catatan internal dan ringkasan publik harus menggunakan field yang berbeda. Ringkasan publik tidak dibuat dengan langsung memotong atau menutup sebagian teks aduan mentah.

## 9. Data yang Boleh Dipublikasikan

Data publik menggunakan daftar field yang dibatasi:

- ID publik yang tidak berkaitan langsung dengan nomor permohonan atau nomor tiket internal.
- Kategori aduan yang cukup umum.
- Bulan atau periode penerimaan.
- Status publik, misalnya `Selesai Ditindaklanjuti`.
- Ringkasan masalah yang telah disanitasi.
- Tanggapan resmi yang telah disanitasi.
- Ringkasan hasil penyelesaian.
- Lama penyelesaian dalam jumlah hari, bila aman.
- Tanggal publikasi dan waktu terakhir diperbarui.

Data berikut tidak boleh tampil pada publikasi:

- Nama atau inisial pemohon.
- NIK, nomor KK, dan data kependudukan lainnya.
- Nomor telepon, email, alamat, alamat IP, dan identitas perangkat.
- Nomor permohonan SI PRIMA atau nomor tiket internal.
- Dokumen, foto, dan lampiran aduan.
- Teks aduan mentah dan percakapan privat.
- Catatan pemeriksaan internal.
- Identitas pihak yang dilaporkan sebelum ada dasar publikasi yang sah.
- Kombinasi informasi yang dapat mengidentifikasi seseorang secara tidak langsung.

Kategori dengan jumlah kasus sangat sedikit perlu digabung, ditunda, atau hanya ditampilkan sebagai statistik yang lebih umum sesuai keputusan PPID.

## 10. Tampilan Halaman Publik

Halaman `Transparansi Aduan` direncanakan memuat:

- Penjelasan tujuan dan batas data yang dipublikasikan.
- Tombol menuju form aduan resmi di SI PRIMA.
- Jumlah aduan diterima, diproses, selesai, ditolak, dan dialihkan.
- Persentase penyelesaian sesuai standar waktu layanan.
- Rata-rata atau median waktu penyelesaian.
- Grafik jumlah aduan per bulan dan kategori umum.
- Daftar ringkasan hasil tindak lanjut yang sudah disetujui.
- Filter periode, kategori, dan status publik.
- Informasi waktu terakhir sinkronisasi.
- Kanal koreksi apabila masyarakat menemukan informasi yang keliru atau berisiko membuka data pribadi.

Daftar publik tidak menyediakan pencarian berdasarkan nama, NIK, nomor permohonan, nomor tiket internal, atau identitas individu lainnya.

## 11. Pemisahan Aduan Layanan dan WBS

Aduan layanan berkaitan dengan mutu, waktu, prosedur, akses, atau hasil pelayanan. Ringkasan hasil tindak lanjutnya dapat dipertimbangkan untuk publikasi setelah ditinjau.

Aduan yang memuat dugaan korupsi, pungutan liar, konflik kepentingan, manipulasi data, pelanggaran disiplin berat, atau penyalahgunaan wewenang harus ditandai sebagai calon WBS. Data tersebut:

- Tidak otomatis dipublikasikan.
- Hanya dapat diakses petugas yang berwenang.
- Tidak menampilkan identitas pelapor atau terlapor kepada publik.
- Diteruskan ke Inspektorat atau kanal WBS resmi sesuai SOP.
- Hanya dapat menghasilkan statistik atau ringkasan hasil akhir anonim setelah ada persetujuan pejabat berwenang.

## 12. Integrasi dengan SI PRIMA

Rekomendasi integrasi:

1. SI PRIMA tetap menjadi sumber data privat dan sumber kebenaran penanganan aduan.
2. Admin menyelesaikan tindak lanjut serta membuat ringkasan publik di SI PRIMA atau panel privat yang berwenang.
3. Hanya data berstatus `Disetujui` yang tersedia melalui API internal hanya-baca.
4. Website informasi menarik data secara terjadwal dan menyimpan salinan publik lokal.
5. Halaman publik membaca salinan lokal agar tidak bergantung langsung pada ketersediaan SI PRIMA.
6. Sinkronisasi mencatat waktu, hasil, jumlah data, dan kegagalan tanpa merekam data pribadi ke log.

Website publik tidak boleh memakai akun database SI PRIMA yang dapat membaca seluruh tabel. Bila API belum tersedia, tahap awal menggunakan ekspor data publik yang sudah diperiksa dan disetujui.

## 13. Keamanan Minimum

- Validasi server-side untuk seluruh input.
- Perlindungan CSRF, rate limiting, dan pembatasan panjang teks.
- Encoding keluaran untuk mencegah XSS dari isi aduan.
- Lampiran disimpan di lokasi privat non-eksekusi dan diperiksa jenis MIME serta ukurannya.
- Otorisasi terpusat untuk Pemohon, Admin Aduan, Reviewer/PPID, dan Super Admin.
- MFA diwajibkan bagi pengguna yang dapat membaca aduan atau menyetujui publikasi.
- Seluruh aktivitas penting dicatat dalam audit log.
- Audit log tidak menyimpan isi aduan lengkap, token, kata sandi, NIK, atau data sensitif lainnya.
- API menggunakan HTTPS, kredensial khusus hanya-baca, rotasi rahasia, timeout, dan pembatasan jaringan bila tersedia.
- Data API menggunakan field allowlist; field yang tidak dikenal ditolak.
- Tersedia mekanisme koreksi, penarikan publikasi, retensi, penghapusan, backup, pemulihan, dan respons insiden.

## 14. Audit Log

Aktivitas yang perlu dicatat:

- Aduan diterima dan diverifikasi.
- Perubahan kategori, prioritas, penanggung jawab, dan status.
- Permintaan informasi tambahan.
- Tanggapan dan penetapan hasil penyelesaian.
- Perubahan status WBS atau pengalihan kanal.
- Pembuatan dan perubahan ringkasan publik.
- Persetujuan, penolakan, penerbitan, pembaruan, dan penarikan publikasi.
- Proses sinkronisasi dengan website informasi.

Audit log mencatat pelaku, aksi, waktu, objek, dan hasil. Isi sensitif tidak disalin ke deskripsi log.

## 15. Pengujian Wajib

- Pemohon hanya dapat melihat aduannya sendiri.
- Admin tanpa hak tidak dapat membaca atau mengubah aduan.
- Editor Berita tidak dapat mengakses modul aduan.
- Hanya Reviewer/PPID yang dapat menyetujui publikasi.
- Aduan selesai tidak otomatis menjadi publik.
- Record tanpa persetujuan publikasi tidak pernah muncul pada API atau halaman publik.
- Payload API yang mengandung field terlarang ditolak.
- Teks berbahaya seperti HTML atau JavaScript tidak dieksekusi.
- NIK, nomor KK, email, telepon, nomor permohonan, dan lampiran tidak muncul pada respons publik.
- Status gagal dan data sinkronisasi lama ditampilkan secara aman tanpa membuka detail sistem.
- Proses sinkronisasi bersifat idempoten dan tidak membuat publikasi ganda.
- Penarikan publikasi menghapus record dari tampilan dan cache publik.
- Tampilan form, panel admin, dan halaman publik konsisten pada desktop dan perangkat seluler.

## 16. Tahapan Implementasi

### Tahap A — Form dan penanganan privat

- Menetapkan kategori, status, SLA, dan SOP pengaduan.
- Membuat form aduan pemohon.
- Membuat panel tindak lanjut Admin Aduan.
- Membuat komunikasi privat dan riwayat status.
- Menambahkan otorisasi, MFA, audit log, dan pengamanan lampiran.

### Tahap B — Persiapan publikasi

- Menambahkan field ringkasan publik yang terpisah dari aduan mentah.
- Menambahkan alur draf, tinjau, setujui, dan tarik publikasi.
- Menetapkan Reviewer/PPID serta aturan anonimisasi.
- Melakukan pengujian konsekuensi dan penilaian risiko pelindungan data.

### Tahap C — Transparansi publik

- Membuat API atau ekspor khusus data yang sudah disetujui.
- Membuat sinkronisasi terjadwal ke website informasi.
- Membuat halaman statistik dan daftar hasil tindak lanjut.
- Menampilkan sumber data, metodologi, dan waktu pembaruan.

## 17. Kriteria Penerimaan

- Form dan proses tindak lanjut hanya tersedia pada sistem privat.
- Admin dapat mencatat proses sampai aduan selesai.
- Ringkasan publik dibuat pada field terpisah.
- Publikasi membutuhkan pemeriksaan dan persetujuan khusus.
- Website informasi tidak menerima data pribadi atau isi aduan mentah.
- Hanya hasil tindak lanjut yang telah disanitasi dan disetujui yang tampil ke publik.
- Aduan WBS tidak masuk daftar publik aduan layanan.
- Semua perubahan penting memiliki audit log.
- Hak akses, privasi, kondisi gagal, dan tampilan responsif telah diuji.

## 18. Keputusan yang Masih Diperlukan

- Lokasi form aduan: SI PRIMA atau modul privat terpisah.
- Kategori aduan dan standar waktu penyelesaian setiap kategori.
- Apakah pemohon dapat membalas setelah admin memberikan tanggapan.
- Jenis dan batas lampiran yang diperbolehkan.
- Pejabat yang menjadi Admin Aduan, Reviewer/PPID, dan penyetuju akhir.
- Ambang penggabungan kategori dengan jumlah kasus kecil.
- Lama retensi aduan, komunikasi, lampiran, dan salinan publik.
- Dasar pemrosesan dan isi pemberitahuan privasi.
- Kanal resmi untuk pengalihan laporan WBS.
- Jadwal sinkronisasi dan penanggung jawab koreksi publikasi.

## 19. Rujukan

- [UU Nomor 27 Tahun 2022 tentang Pelindungan Data Pribadi](https://jdih.komdigi.go.id/produk_hukum/view/id/832/t/undangundang%2Bnomor%2B27%2Btahun%2B2022)
- [UU Nomor 14 Tahun 2008 tentang Keterbukaan Informasi Publik](https://jdih.komdigi.go.id/produk_hukum/view/id/172/t/Undang-Undang%2BNomor%2B14%2BTahun%2B%2B2008%2Btanggal%2B30%2BApril%2B2008)
- [Peraturan BSSN Nomor 4 Tahun 2021 tentang Keamanan SPBE](https://peraturan.bpk.go.id/Details/174275/)
- [Pedoman Pengaduan Masyarakat dan KPK Whistleblower's System](https://aclc.kpk.go.id/materi-pembelajaran/tata-kelola-pemerintahan/website/pengaduan-masyarakat)

