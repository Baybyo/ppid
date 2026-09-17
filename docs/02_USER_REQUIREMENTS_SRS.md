# DOKUMEN 02: SPESIFIKASI KEBUTUHAN PENGGUNA & PERANGKAT LUNAK (SRS)
## SOFTWARE REQUIREMENTS SPECIFICATION — SISTEM PPID DISNAKERTRANS

---

### 1. PENDAHULUAN
Dokumen ini mendefinisikan seluruh kebutuhan fungsional (*Functional Requirements*), kebutuhan non-fungsional (*Non-Functional Requirements*), batasan desain, serta pemodelan Use Case untuk Sistem Layanan Permohonan Informasi Publik (PPID) pada Dinas Tenaga Kerja dan Transmigrasi.

---

### 2. ROLE & PROFIL PENGGUNA (USER PERSONA)

#### 2.1. Aktor 1: Masyarakat / Pemohon Informasi Publik
- **Karakteristik**: Warga negara Indonesia, perwakilan LSM, peneliti, mahasiswa, atau jurnalis yang membutuhkan data/informasi resmi dari Disnakertrans.
- **Hak Akses**:
  1. Melakukan pendaftaran akun dengan Nomor HP & Email.
  2. Mengisi formulir permohonan informasi publik.
  3. Menyimpan permohonan ke dalam status Draft (`status = 0`) untuk diedit kemudian hari.
  4. Mengirimkan permohonan secara resmi (`status = 1`).
  5. Mengunduh Tanda Bukti Permohonan dalam format PDF resmi.
  6. Memantau riwayat dan progres tahapan verifikasi permohonan miliknya.
  7. Mengunduh berkas jawaban/salinan informasi publik yang diunggah oleh admin.
  8. Menghapus data permohonan selama masih berstatus Draft.
- **Batasan Akses**:
  - Dilarang melihat permohonan milik pengguna lain.
  - Dilarang mengedit atau membatalkan permohonan yang sudah dikirim (`status >= 1`).
  - Dilarang mengakses endpoint manapun di bawah rute `/admin/*`.

#### 2.2. Aktor 2: Admin / Petugas Pengelola PPID
- **Karakteristik**: Pegawai/petugas fungsional pada Disnakertrans yang ditugaskan memverifikasi dan menindaklanjuti permohonan informasi publik.
- **Hak Akses**:
  1. Login ke panel administrasi `/admin` dengan username dan password terotentikasi.
  2. Melihat ringkasan metrik statistik (Menunggu, Diproses, Selesai, Ditolak).
  3. Mengakses daftar seluruh permohonan masuk dengan filter status dan rentang tanggal.
  4. Memverifikasi keabsahan data pemohon dan file identitas (KTP/SIM/Paspor).
  5. Mengubah status permohonan (`Menunggu` -> `Diproses` -> `Selesai` / `Ditolak`).
  6. Menolak permohonan wajib menyertakan alasan penolakan tertulis.
  7. Mengunggah dokumen file jawaban (format PDF/Gambar, multi-file).
  8. Memantau penghitungan batas waktu SLA kerja.
  9. Mengelola data pengguna masyarakat (mengaktifkan/menonaktifkan akun).
  10. Melakukan ekspor data rekapitulasi permohonan ke format Excel/CSV.
- **Batasan Akses**:
  - Wajib memiliki session `isAdminLoggedIn`.
  - Setiap perubahan status diverifikasi dan dicatat ke log sistem tanpa bisa diubah kembali (*immutable log*).

#### 2.3. Aktor 3: Publik (Guest / Anonim)
- **Hak Akses**:
  1. Mengakses halaman form publik (diarahkan login bila hendak menyimpan/mengirim).
  2. Mengakses fitur **Lacak Permohonan** (`/permohonan/tracking`) hanya bermodalkan Nomor Registrasi sah. Data sensitif NIK dan No HP disensor otomatis (*masked*).

---

### 3. KEBUTUHAN FUNGSIONAL (FUNCTIONAL REQUIREMENTS)

| ID Kebutuhan | Nama Kebutuhan | Deskripsi Rinci & Aturan |
|:---|:---|:---|
| **FR-AUTH-01** | Registrasi Akun Masyarakat | Sistem menerima input Nama, No HP, Email (opsional/unik), dan Password. Sistem mewajibkan No HP unik berawalan `08`, panjang 10-15 digit, serta melakukan hashing password (`PASSWORD_BCRYPT`). |
| **FR-AUTH-02** | Login & Proteksi Sesi User | Sistem memvalidasi kredensial login (No HP/Email + Password). Akun yang non-aktif (`is_active = 0`) diblokir dari login. |
| **FR-AUTH-03** | Login & Guard Admin | Admin login via `/admin/login`. Sistem menerapkan filter `adminAuth` di level route untuk memproteksi seluruh rute `/admin/*`. |
| **FR-PERM-01** | Form Input Permohonan | Sistem menyediakan form isian: Nama, No Identitas, Jenis Identitas (KTP, SIM, Paspor), Pekerjaan, Alamat, No Telepon, Email, Rincian Informasi yang Dibutuhkan, Tujuan Penggunaan Informasi, Cara Memperoleh Informasi (multi-pilihan JSON), Cara Mendapatkan Salinan (multi-pilihan JSON + input teks Lainnya), dan File Identitas. |
| **FR-PERM-02** | Simpan Draft Permohonan | Sistem menyediakan endpoint AJAX `POST /permohonan/store-draft` untuk menyimpan data sementara dengan status `0` tanpa validasi ketat, sehingga pemohon dapat melanjutkan di lain waktu. |
| **FR-PERM-03** | Validasi & Pengiriman Permohonan | Saat tombol submit ditekan (`POST /permohonan/submit`), sistem menerapkan validasi ketat di sisi server (Server-side validation):<br>• Nama: 3-100 karakter alfabet/tanda baca nama.<br>• No Identitas: 8-20 karakter alfanumerik.<br>• Pekerjaan: 2-50 karakter.<br>• Alamat: 10-500 karakter.<br>• No Telp: 10-15 digit berformat `08...`.<br>• Email: format email valid max 100 karakter.<br>• Rincian Informasi: min 20 s.d. 2000 karakter.<br>• Tujuan: min 10 s.d. 1000 karakter.<br>• Upload Identitas: Wajib JPG/JPEG/PNG/PDF, ukuran maksimal 10 MB.<br>• Checkbox Persetujuan: Wajib dicentang. |
| **FR-PERM-04** | Penomoran Registrasi Otomatis | Sistem meng-generate nomor registrasi unik dengan format terstruktur: `PPID-{SEQ_3_DIGIT},{TANGGAL},{BULAN},{TAHUN}` (contoh: `PPID-009,11,09,2026`). Sequence dihitung per tahun berjalan secara transaksional. |
| **FR-PERM-05** | Perhitungan Otomatis SLA | Saat permohonan disubmit, sistem secara otomatis menghitung tenggat waktu respon (`sla_deadline`) selama 10 hari kerja (tidak menghitung hari Sabtu dan Minggu), dengan opsi perpanjangan 7 hari kerja. |
| **FR-PERM-06** | Inisialisasi Tahapan & Log | Sistem secara otomatis menginisialisasi 4 tahapan ke tabel `permohonan_tahapan`: `Diterima`, `Verifikasi`, `Diproses`, `Selesai`, serta mencatat transaksi pengiriman ke tabel `permohonan_log`. |
| **FR-PERM-07** | Generate Tanda Bukti PDF | Sistem menyediakan endpoint unduh bukti PDF (`/permohonan/download-bukti/{id}`) yang merender layout resmi A4 potret via Dompdf. NIK pada bukti PDF disensor pada digit tengah demi keamanan privasi. |
| **FR-PERM-08** | Pelacakan Status Publik | Halaman `/permohonan/tracking` menyediakan pengecekan real-time via AJAX berdasarkan nomor registrasi. Sistem mengembalikan status permohonan, riwayat log tahapan, ringkasan rincian informasi, dan sensor NIK/No Telp (`*****1234`). |
| **FR-PERM-09** | Riwayat Permohonan Pemohon | Pemohon dapat melihat daftar seluruh permohonan yang pernah diajukan, status terkini, tombol cetak bukti, serta tombol hapus draft (khusus status `0`). |
| **FR-ADM-01** | Dasbor Statistik Admin | Dasbor menyajikan total permohonan masuk, permohonan menunggu verifikasi (`status = 1`), permohonan diproses (`status = 2`), permohonan selesai (`status = 3`), dan permohonan ditolak (`status = 4`). |
| **FR-ADM-02** | Pengelolaan Status Permohonan | Admin dapat mengubah status permohonan secara berjenjang dari detail permohonan. Perubahan status dari `Diproses` ke `Selesai` otomatis menutup tahapan proses. Jika status diubah ke `Ditolak`, input alasan penolakan wajib diisi. |
| **FR-ADM-03** | Upload Dokumen Jawaban | Admin dapat mengunggah berkas salinan jawaban informasi resmi (PDF, DOCX, ZIP, Gambar) yang otomatis tersimpan ke tabel `permohonan_lampiran` dengan tipe `jawaban` dan dapat diunduh pemohon. |
| **FR-ADM-04** | Audit Trail Log Perubahan | Setiap aksi admin (ubah status, upload jawaban, verifikasi) otomatis tercatat ke tabel `permohonan_log` lengkap dengan ID admin, alamat IP, deskripsi aktivitas, status awal, dan status akhir. |
| **FR-ADM-05** | Ekspor Rekapitulasi Data | Admin dapat mengunduh rekapan data permohonan sesuai filter rentang tanggal dan status ke format file ekspor untuk laporan dinas. |
| **FR-ADM-06** | Manajemen Akun Pemohon | Admin dapat memantau daftar pemohon terdaftar dan melakukan switch status akun (`is_active` 1/0) bila ditemukan indikasi data palsu/spam. |

---

### 4. KEBUTUHAN NON-FUNGSIONAL (NON-FUNCTIONAL REQUIREMENTS)

#### 4.1. Keamanan Data & Sistem (Security)
- **NFR-SEC-01**: Password akun masyarakat dan admin wajib di-hash menggunakan algoritma `PASSWORD_BCRYPT` dengan cost minimum 10.
- **NFR-SEC-02**: Proteksi Cross-Site Scripting (XSS) wajib diimplementasikan pada seluruh output tampilan menggunakan helper `esc()`.
- **NFR-SEC-03**: Seluruh formulir POST wajib diamankan dari serangan Cross-Site Request Forgery (CSRF).
- **NFR-SEC-04**: Validasi berkas unggahan wajib memverifikasi ekstensi file, validasi MIME Type nyata di level server, serta mengganti nama berkas dengan format acak unik guna mencegah eksekusi skrip berbahaya (*Arbitrary File Upload*).
- **NFR-SEC-05**: Akses unduh berkas sensitif identitas dan jawaban dibatasi hanya untuk pemilik sah permohonan atau admin yang login.

#### 4.2. Keandalan & Integritas Data (Reliability & Integrity)
- **NFR-REL-01**: Operasi kritis seperti submit permohonan, penomoran sequence, inisialisasi tahapan, dan pencatatan audit log wajib berjalan di dalam satu blok Database Transaction (`$db->transBegin()`).
- **NFR-REL-02**: Relasi data antar tabel `permohonan` ke `permohonan_lampiran`, `permohonan_tahapan`, dan `permohonan_log` menggunakan constraint Foreign Key dengan integritas `ON DELETE CASCADE ON UPDATE CASCADE`.

#### 4.3. Kinerja & Efisiensi (Performance)
- **NFR-PERF-01**: Halaman utama dan pencarian permohonan harus dimuat dalam waktu kurang dari 2 detik pada koneksi internet standar.
- **NFR-PERF-02**: Pembuatan dokumen PDF Bukti Permohonan via Dompdf harus diselesaikan dalam waktu kurang dari 3 detik.
- **NFR-PERF-03**: Kolom yang sering dijadikan parameter filter dan pencarian (`no_registrasi`, `no_identitas`, `status`, `masyarakat_id`) wajib memiliki struktur Database Index.

#### 4.4. Keterpakaian & Tampilan (Usability)
- **NFR-USE-01**: Antarmuka responsif penuh (*mobile-friendly*) menggunakan framework Bootstrap 5 untuk berbagai ukuran layar (smartphone, tablet, laptop).
- **NFR-USE-02**: Seluruh label formulir, keterangan error, dan notifikasi disajikan menggunakan Bahasa Indonesia baku yang jelas dan mudah dipahami masyarakat umum.

---

### 5. USE CASE DIAGRAM (DESKRIPTIF TEKSTUAL)

```
================================================================================
                    SISTEM LAYANAN PPID ONLINE
================================================================================

 [ Publik / Tamu ]
        │
        ├─── (UC-01: Lacak Status Permohonan Publik)
        ├─── (UC-02: Registrasi Akun Pemohon)
        └─── (UC-03: Login ke Sistem)

 [ Pemohon / Masyarakat ]
        │
        ├─── (UC-03: Login ke Sistem)
        ├─── (UC-04: Kelola Profil & Ganti Password)
        ├─── (UC-05: Isi Formulir Permohonan Informasi)
        │         ├── <<include>> (UC-06: Upload Dokumen Identitas)
        │         └── <<extend>>  (UC-07: Simpan Sebagai Draft)
        ├─── (UC-08: Kirim Permohonan Resmi / Submit)
        │         ├── <<include>> (UC-09: Generate Nomor Registrasi)
        │         └── <<include>> (UC-10: Hitung Deadline SLA)
        ├─── (UC-11: Lihat Riwayat Permohonan)
        ├─── (UC-12: Cetak / Unduh Tanda Bukti PDF)
        └─── (UC-13: Hapus Draft Permohonan)

 [ Petugas / Admin PPID ]
        │
        ├─── (UC-14: Login Admin)
        ├─── (UC-15: Lihat Dashboard Statistik & SLA)
        ├─── (UC-16: Kelola & Filter Daftar Permohonan)
        ├─── (UC-17: Verifikasi Kelengkapan Permohonan)
        ├─── (UC-18: Perbarui Status Permohonan)
        │         ├── <<extend>> (UC-19: Input Alasan Penolakan)
        │         └── <<include>> (UC-20: Catat Audit Trail Log)
        ├─── (UC-21: Unggah Dokumen Jawaban Informasi)
        ├─── (UC-22: Ekspor Data Laporan Permohonan)
        └─── (UC-23: Kelola Status Akun Pemohon)

================================================================================
```

---

### 6. USE CASE DESCRIPTION PILIHAN (SKENARIO UTAMA)

#### Skenario 1: Pengiriman Permohonan Informasi Publik (UC-08)
- **Aktor Utama**: Pemohon (Masyarakat yang login)
- **Kondisi Awal (*Pre-condition*)**: Pemohon sudah login dan membuka formulir permohonan.
- **Kondisi Akhir (*Post-condition*)**: Data permohonan tersimpan dengan status `1` (Menunggu Verifikasi), nomor registrasi terbit, deadline SLA terhitung, berkas terkunci dari pengeditan.
- **Alur Normal**:
  1. Pemohon mengisi seluruh rincian formulir wajib dan mengunggah berkas identitas (KTP/SIM/Paspor).
  2. Pemohon mencentang pernyataan persetujuan kebenaran data.
  3. Pemohon menekan tombol "Kirim Permohonan".
  4. Sistem memvalidasi seluruh input di server (format nama, no telp 08, email, panjang rincian informasi).
  5. Sistem memverifikasi file identitas (ekstensi, tipe MIME, dan ukuran <= 10MB).
  6. Sistem menghasilkan nomor registrasi unik baru (contoh: `PPID-009,11,09,2026`).
  7. Sistem menghitung deadline SLA kerja 10 hari kerja.
  8. Sistem menyimpan record permohonan ke tabel `permohonan` dengan status `1`.
  9. Sistem menginisialisasi tahapan di `permohonan_tahapan` dan mencatat log di `permohonan_log`.
  10. Sistem menampilkan halaman sukses beserta nomor registrasi dan tombol unduh bukti PDF.
- **Alur Alternatif (Validasi Gagal)**:
  - 4a. Sistem mendeteksi field tidak lengkap atau format tidak valid.
  - 4b. Sistem membatalkan penyimpanan dan mengembalikan respon error JSON / flash data berisi daftar pesan kesalahan spesifik pada field terkait.
  - 4c. Pemohon memperbaiki isian dan mengulangi langkah 3.

#### Skenario 2: Admin Memperbarui Status & Mengunggah Jawaban (UC-18 & UC-21)
- **Aktor Utama**: Admin PPID
- **Kondisi Awal**: Admin sudah login dan membuka halaman detail permohonan status `1` atau `2`.
- **Kondisi Akhir**: Status permohonan terupdate, file jawaban terlampir, log riwayat audit tercatat.
- **Alur Normal**:
  1. Admin meninjau data pemohon dan keabsahan file identitas.
  2. Admin memilih opsi status baru: "Diproses" (`2`) atau "Selesai" (`3`).
  3. Jika memilih "Selesai", Admin mengunggah dokumen jawaban informasi resmi yang telah ditandatangani instansi.
  4. Sistem memvalidasi file jawaban dan menyimpannya ke `uploads/permohonan/`.
  5. Sistem mencatat metadata file ke `permohonan_lampiran` dengan tipe `jawaban`.
  6. Sistem memperbarui kolom `status` di tabel `permohonan`.
  7. Sistem memperbarui status di tabel `permohonan_tahapan` ke status `Selesai`.
  8. Sistem menambahkan baris baru pada `permohonan_log` mencatat ID Admin, IP, aktivitas, dan waktu kejadian.
  9. Sistem memperbarui tampilan detail permohonan dengan pesan konfirmasi sukses.
