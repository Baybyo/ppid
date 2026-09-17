# DOKUMEN 01: PROJECT BRIEF & RUANG LINGKUP (SCOPE)
## SISTEM LAYANAN PERMOHONAN INFORMASI PUBLIK (PPID)
**Dinas Tenaga Kerja dan Transmigrasi (Disnakertrans)**

---

### 1. INFORMASI PROYEK
- **Nama Sistem**: Sistem Informasi Layanan PPID Online
- **Instansi Pemilik**: Dinas Tenaga Kerja dan Transmigrasi
- **Framework & Teknologi**: CodeIgniter 4.7 (PHP 8.2), MySQL / MariaDB, Dompdf 3.1, Bootstrap 5
- **Status Pengembangan**: Tahap Implementasi / Production-Ready Core Features
- **Dokumen Versi**: 1.0.0
- **Tanggal Penyusunan**: 17 September 2026

---

### 2. LATAR BELAKANG & DASAR HUKUM
Berdasarkan **Undang-Undang Republik Indonesia Nomor 14 Tahun 2008 tentang Keterbukaan Informasi Publik (KIP)**, setiap badan publik berkewajiban membuka akses atas informasi publik yang berkaitan dengan kebijakan, program kerja, serta anggaran kepada masyarakat luas, kecuali informasi yang dikecualikan secara ketat oleh hukum.

Sebelum adanya sistem ini, proses pengajuan permohonan informasi publik pada Disnakertrans menghadapi kendala operasional:
1. Pemohon harus datang langsung atau mengirimkan surat secara manual.
2. Arsip berkas permohonan tersebar dan berisiko tercecer tanpa nomor pelacakan terpadu.
3. Kepatuhan terhadap batas waktu layanan (SLA) sulit dipantau secara akurat, meningkatkan risiko terjadinya sengketa informasi publik.

Sistem PPID Online dibangun sebagai portal satu pintu (*single window service*) untuk menjamin akuntabilitas, kecepatan, serta transparansi pelayanan publik.

---

### 3. IDENTIFIKASI MASALAH
| No | Masalah Utama | Dampak pada Pemohon | Dampak pada Admin / Instansi |
|:---|:---|:---|:---|
| 1 | Pengajuan manual berbasis fisik | Biaya transportasi, waktu terbuang, antrean loket | Beban arsip kertas, pencatatan ganda manual |
| 2 | Ketiadaan pelacakan real-time | Pemohon cemas dan berulang kali bertanya status | Petugas berulang kali melayani pertanyaan repetitif |
| 3 | SLA 10 + 7 hari kerja rawan terlewati | Pemohon tidak mendapat kepastian hukum | Risiko pelanggaran kepatuhan KIP & sengketa informasi |
| 4 | Dokumen jawaban tidak tersentralisasi | Salinan jawaban rentan hilang atau rusak | Tidak ada audit trail riwayat pemberian berkas informasi |

---

### 4. TUJUAN SISTEM
1. **Bagi Masyarakat (Pemohon Informasi)**:
   - Memberikan saluran resmi online yang dapat diakses 24/7 untuk mengajukan permohonan.
   - Menyediakan fitur *drafting* agar pemohon dapat mencicil pengisian sebelum resmi dikirim.
   - Menerbitkan tanda bukti registrasi sah berformat PDF secara otomatis.
   - Menyediakan sarana pelacakan status (*public tracking*) transparan cukup menggunakan nomor registrasi.
2. **Bagi Petugas PPID (Admin / Verifikator)**:
   - Menyediakan dasbor terpadu untuk memverifikasi keabsahan data pemohon dan identitas (KTP/SIM/Paspor).
   - Menghitung secara otomatis batas waktu penanganan (SLA 10 hari kerja + perpanjangan 7 hari kerja).
   - Mengelola riwayat status secara berjenjang (`Draft` -> `Menunggu` -> `Diproses` -> `Selesai` / `Ditolak`).
   - Mengunggah salinan dokumen jawaban informasi resmi langsung ke akun pemohon.
   - Menyimpan jejak audit (*audit trail*) setiap perubahan status dan aksi admin.

---

### 5. RUANG LINGKUP (SCOPE)

#### A. In-Scope (Fitur yang Masuk dalam Sistem)
1. **Modul Autentikasi Pengguna & Petugas**:
   - Registrasi masyarakat dengan validasi Nomor HP unik, Email, dan Password terenkripsi (`bcrypt`).
   - Login masyarakat dan proteksi sesi aktif (`is_active`).
   - Login admin terpisah dengan filter keamanan sesi (`adminAuth`).
2. **Modul Pengajuan Permohonan**:
   - Formulir pengajuan informasi publik lengkap (data pemohon, rincian informasi, tujuan, cara memperoleh, cara mendapatkan salinan, dan upload identitas).
   - Penyimpanan sementara berkas (*save draft*) status `0`.
   - Pengiriman resmi (*submit*) status `1` dan auto-locking (data terkunci setelah dikirim).
   - Penomoran registrasi otomatis dengan format: `PPID-SEQ,DD,MM,YYYY`.
3. **Modul Bukti & Pelacakan**:
   - Generasi dokumen PDF Tanda Bukti Permohonan Informasi Publik via library Dompdf.
   - Fitur pelacakan publik (*public tracking*) tanpa wajib login.
4. **Modul Dashboard & Pengelolaan Admin**:
   - Metrik jumlah permohonan: Menunggu, Diproses, Selesai, Ditolak, dan Total.
   - Filter daftar permohonan berdasarkan status, rentang tanggal pengajuan, dan pencarian kata kunci.
   - Perubahan status permohonan berjenjang disertai pencatatan tahapan (`permohonan_tahapan`) dan audit log (`permohonan_log`).
   - Form penolakan permohonan dengan kewajiban mencantumkan alasan penolakan.
   - Upload dokumen jawaban (PDF/Gambar) ke pemohon.
   - Manajemen akun masyarakat (melihat detail data & toggle aktif/nonaktif).
   - Fitur ekspor data rekapitulasi permohonan.

#### B. Out-of-Scope (Bukan Bagian dari Proyek Ini)
1. Penanganan Pengaduan Masyarakat / Whistleblowing System (WBS) terpisah di luar alur PPID.
2. Pembayaran biaya penggandaan/legallisasi secara online (payment gateway).
3. Integrasi Tanda Tangan Elektronik tersertifikasi BSRE / Kominfo (menggunakan tanda bukti digital sistem internal).
4. Pengiriman notifikasi SMS atau WhatsApp otomatis ke nomor pemohon (notifikasi saat ini melalui portal akun dan email manual).
5. Modul pengajuan Uji Konsekuensi Informasi Publik internal antar OPD.

---

### 6. ATURAN BISNIS UTAMA (BUSINESS RULES)
- **BR-01**: Nomor identitas (KTP/SIM/Paspor) wajib berukuran minimal 8 karakter dan terverifikasi jenisnya.
- **BR-02**: File identitas pemohon wajib berformat gambar (JPG/PNG) atau PDF dengan batas ukuran aman.
- **BR-03**: Permohonan yang berstatus Draft (`0`) dapat diubah dan dihapus oleh pemilik akun.
- **BR-04**: Permohonan yang telah berstatus Dikirim (`1` atau lebih) terkunci secara permanen dan tidak dapat diedit/dihapus oleh pemohon.
- **BR-05**: Setiap perubahan status oleh Admin wajib mencatat nama admin, IP address, waktu kejadian, dan status awal ke tabel log.
- **BR-06**: SLA dihitung secara eksklusif menggunakan hari kerja (Senin s.d. Jumat), tidak menghitung hari Sabtu dan Minggu.
- **BR-07**: Apabila permohonan Ditolak (`status = 4`), admin wajib menginput alasan penolakan yang dapat dibaca oleh pemohon.
