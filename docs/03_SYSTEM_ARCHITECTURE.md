# DOKUMEN 03: ARSITEKTUR SISTEM & DESAIN TEKNIS
## LAYANAN PPID ONLINE DISNAKERTRANS

---

### 1. GAMBARAN UMUM ARSITEKTUR
Sistem dibangun menggunakan pola arsitektur **Model-View-Controller (MVC)** standar berbasis kerangka kerja modern **CodeIgniter 4 (v4.7)** dengan pendekatan pemrograman berorientasi objek (OOP) dan struktur berbasis layanan modular.

```
┌────────────────────────────────────────────────────────────────────────┐
│                          WEB BROWSER / CLIENT                          │
└───────────────────────────────────┬────────────────────────────────────┘
                                    │ HTTP / HTTPS (GET, POST, AJAX)
                                    ▼
┌────────────────────────────────────────────────────────────────────────┐
│                        APACHE HTTP SERVER / PHP 8.2                    │
└───────────────────────────────────┬────────────────────────────────────┘
                                    │ Entry Point: public/index.php
                                    ▼
┌────────────────────────────────────────────────────────────────────────┐
│                     CODEIGNITER 4 ROUTING SYSTEM                       │
│                        (app/Config/Routes.php)                         │
└──────────────────┬─────────────────────────────────┬───────────────────┘
                   │                                 │
                   ▼ (rute publik / pemohon)         ▼ (rute grup /admin)
        ┌─────────────────────┐           ┌──────────────────────┐
        │ App\Controllers\*   │           │ App\Controllers\Admin│
        │ - Permohonan        │           │ - Auth, Dashboard    │
        │ - Auth, Profil      │           │ - Permohonan, Profil │
        └──────────┬──────────┘           └──────────┬───────────┘
                   │                                 │
                   └─────────────────┬───────────────┘
                                     ▼
        ┌────────────────────────────────────────────────────────┐
        │                     MODELS & DATABASE                  │
        │ - PermohonanModel, PermohonanLampiranModel             │
        │ - PermohonanTahapanModel, PermohonanLogModel           │
        └────────────┬───────────────────────────────┬───────────┘
                     │                               │
                     ▼                               ▼
        ┌─────────────────────────┐       ┌──────────────────────────┐
        │  MySQL / MariaDB (DB)   │       │  Dompdf / File System    │
        │  - Tabel Relasional     │       │  - fcpth/uploads/        │
        └─────────────────────────┘       └──────────────────────────┘
```

---

### 2. STRUKTUR DIREKTORI UTAMA

```text
/opt/lampp/htdocs/ppid/
├── app/
│   ├── Config/
│   │   ├── Database.php
│   │   └── Routes.php
│   ├── Controllers/
│   │   ├── Admin/
│   │   │   ├── Auth.php
│   │   │   ├── Dashboard.php
│   │   │   ├── Masyarakat.php
│   │   │   ├── Permohonan.php
│   │   │   └── Profil.php
│   │   ├── Auth.php
│   │   ├── BaseController.php
│   │   ├── FileServe.php
│   │   ├── Home.php
│   │   ├── Permohonan.php
│   │   └── Profil.php
│   ├── Filters/
│   │   └── AdminAuthFilter.php
│   └── Models/
│       ├── AdminModel.php
│       ├── MasyarakatModel.php
│       ├── PermohonanLampiranModel.php
│       ├── PermohonanLogModel.php
│       ├── PermohonanModel.php
│       └── PermohonanTahapanModel.php
├── public/
│   ├── uploads/
│   │   └── permohonan/
│   └── index.php
└── composer.json
```

---

### 3. DESAIN SKEMA BASIS DATA (DATABASE SCHEMA)

Sistem menggunakan database relasional MySQL/MariaDB dengan aturan *Foreign Key Constraints* dan *Cascade Delete*.

#### 3.1. Tabel `masyarakat`
Menyimpan akun pengguna publik pemohon informasi.
- `id` (INT, PK, AI)
- `nama` (VARCHAR(100))
- `no_telp` (VARCHAR(15), UNIQUE)
- `email` (VARCHAR(100), NULLABLE)
- `password` (VARCHAR(255)) — Hash BCRYPT
- `is_active` (TINYINT, Default 1)
- `created_at`, `updated_at` (DATETIME)

#### 3.2. Tabel `admin`
Menyimpan data petugas pengelola sistem PPID.
- `id` (INT, PK, AI)
- `username` (VARCHAR(50), UNIQUE)
- `password` (VARCHAR(255))
- `nama_lengkap` (VARCHAR(100))
- `role` (ENUM: `superadmin`, `operator`)
- `created_at`, `updated_at` (DATETIME)

#### 3.3. Tabel `permohonan`
Tabel inti permohonan informasi publik.
- `id` (INT, PK, AI)
- `masyarakat_id` (INT, FK ke `masyarakat.id`)
- `no_registrasi` (VARCHAR(50), UNIQUE, NULLABLE saat draft)
- `seq_tahunan` (INT)
- `nama_pemohon`, `no_identitas`, `jenis_identitas`, `pekerjaan`
- `alamat`, `no_telp`, `email`
- `rincian_informasi` (TEXT), `tujuan_penggunaan` (TEXT)
- `cara_memperoleh` (JSON / TEXT), `cara_salinan` (JSON / TEXT)
- `cara_salinan_lainnya` (VARCHAR(100), NULLABLE)
- `file_identitas` (VARCHAR(255))
- `status` (TINYINT: `0` = Draft, `1` = Menunggu, `2` = Diproses, `3` = Selesai, `4` = Ditolak)
- `alasan_penolakan` (TEXT, NULLABLE)
- `sla_deadline` (DATE)
- `submitted_at`, `created_at`, `updated_at` (DATETIME)

#### 3.4. Tabel `permohonan_lampiran`
Menyimpan berkas pendukung atau dokumen jawaban dari admin.
- `id` (INT, PK, AI)
- `permohonan_id` (INT, FK ke `permohonan.id`, CASCADE)
- `tipe` (ENUM: `pendukung`, `jawaban`)
- `nama_file`, `path_file`, `mime_type`
- `ukuran_kb` (INT)
- `created_at` (DATETIME)

#### 3.5. Tabel `permohonan_tahapan`
Pelacakan progres tahapan pengerjaan.
- `id` (INT, PK, AI)
- `permohonan_id` (INT, FK ke `permohonan.id`, CASCADE)
- `tahap` (VARCHAR(50)) — contoh: `Diterima`, `Verifikasi`, `Diproses`, `Selesai`
- `status` (VARCHAR(30)) — contoh: `Menunggu`, `Dalam Proses`, `Selesai`
- `tanggal_mulai`, `tanggal_selesai` (DATETIME, NULLABLE)

#### 3.6. Tabel `permohonan_log`
Jejak audit (*audit trail*) setiap perubahan status dan aksi di dalam sistem.
- `id` (INT, PK, AI)
- `permohonan_id` (INT, FK ke `permohonan.id`, CASCADE)
- `admin_id` (INT, NULLABLE)
- `aktivitas` (TEXT)
- `status_awal`, `status_akhir` (VARCHAR(10), NULLABLE)
- `created_at` (DATETIME)

---

### 4. MEKANISME KEAMANAN & ALUR TRANSAKSI

1. **Proteksi Sesi & Otorisasi**:
   - Filter `adminAuth` memeriksa apakah key session `isAdminLoggedIn` bernilai `true`. Jika tidak, dialihkan ke halaman login admin.
   - Guard di controller permohonan publik memeriksa kepemilikan data (`masyarakat_id`) sebelum mengizinkan pemohon melihat detail atau mengunduh dokumen.
2. **Sanitasi Input & XSS Defense**:
   - CodeIgniter 4 Request Library otomatis melakukan pembersihan input dasar.
   - Seluruh output teks yang dirender ke view dienkode menggunakan fungsi `esc()`.
3. **Penyimpanan Berkas Aman**:
   - Validasi ekstensi ketat (`jpg`, `jpeg`, `png`, `pdf`) dan pengecekan MIME type asli.
   - Nama file diubah secara acak (`permohonan_TIMESTAMP_RANDOM.ext`) untuk mencegah tabrakan nama dan potensi eksekusi skrip berbahaya.
