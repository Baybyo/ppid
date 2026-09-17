# Layanan PPID Online — Dinas Tenaga Kerja dan Transmigrasi (Disnakertrans)

Sistem Informasi Pelayanan Permohonan Informasi Publik (PPID) berbasis web yang dikembangkan untuk mematuhi **Undang-Undang Nomor 14 Tahun 2008 tentang Keterbukaan Informasi Publik (KIP)**. Sistem ini berfungsi sebagai portal satu pintu (*single window service*) untuk pengajuan, pelacakan, dan pengelolaan dokumen permohonan informasi secara transparan dan akuntabel.

---

## 🛠️ Stack Teknologi

| Komponen | Teknologi | Versi |
| :--- | :--- | :--- |
| **Framework** | CodeIgniter | 4.7+ |
| **Bahasa** | PHP | 8.2+ |
| **Database** | MySQL / MariaDB | 10.4+ |
| **PDF Generator** | Dompdf | 3.1+ |
| **Antarmuka** | Bootstrap 5 | 5.3+ |

---

## 📋 Fitur Utama

### 1. Sisi Pemohon (Masyarakat)
- **Drafting Mandiri**: Pengguna dapat menyimpan draf permohonan sebelum dikirim secara resmi.
- **Tanda Bukti Digital**: Penerbitan bukti registrasi berformat PDF otomatis lengkap dengan nomor unik.
- **Pelacakan Status (*Public Tracking*)**: Pantau tahapan permohonan secara real-time tanpa harus login.
- **Riwayat & Unduh Jawaban**: Akses riwayat pengajuan dan unduh dokumen tanggapan resmi dari instansi.

### 2. Sisi Administrator (Verifikator)
- **Dashboard Terpadu**: Monitoring jumlah permohonan masuk, diproses, selesai, dan ditolak.
- **Manajemen SLA**: Perhitungan otomatis batas waktu layanan (SLA standar 10 hari kerja + perpanjangan 7 hari kerja).
- **Verifikasi & Tanggapan**: Validasi identitas pemohon, pembaruan status berjenjang, dan unggah berkas jawaban.
- **Jejak Audit (*Audit Trail*)**: Pencatatan log aktivitas dan alamat IP setiap perubahan status demi akuntabilitas hukum.

---

## ⚙️ Persyaratan Sistem

Sebelum melakukan instalasi, pastikan server atau lingkungan lokal Anda memenuhi spesifikasi berikut:
- **PHP** >= 8.2 dengan ekstensi aktif:
  - `intl`
  - `mbstring`
  - `mysqli`
  - `gd`
  - `curl`
  - `json`
- **Composer** (Package Manager untuk PHP)
- **Web Server** (Apache / Nginx / PHP Built-in Server)
- **MySQL** atau **MariaDB**

---

## 🚀 Panduan Instalasi (Development Setup)

Ikuti langkah-langkah berikut untuk menjalankan proyek di perangkat lokal:

### 1. Kloning Repositori
```bash
git clone https://github.com/Baybyo/ppid.git
cd ppid
```

### 2. Install Dependensi PHP
```bash
composer install
```

### 3. Konfigurasi Environment (`.env`)
Salin file bawaan `env` menjadi `.env`:
```bash
cp env .env
```
Buka file `.env` dan sesuaikan konfigurasi dasar berikut:
```ini
CI_ENVIRONMENT = development

app.baseURL = 'http://localhost/ppid/public/'

database.default.hostname = localhost
database.default.database = ppid_db
database.default.username = root
database.default.password = 
database.default.DBDriver = MySQLi
database.default.port = 3306
```

### 4. Konfigurasi Database
Buat database baru di MySQL dengan nama `ppid_db`, lalu jalankan migrasi dan seeder untuk tabel serta akun admin default:
```bash
php spark migrate --all
php spark db:seed DatabaseSeeder
```

### 5. Pengaturan Hak Akses Folder (Linux/macOS)
Pastikan folder `writable` memiliki izin tulis:
```bash
chmod -R 777 writable/
```

### 6. Jalankan Aplikasi
Anda dapat menggunakan server bawaan PHP Spark:
```bash
php spark serve
```
Akses aplikasi melalui browser di `http://localhost:8080`.

*(Alternatif: Arahkan Document Root web server lokal/XAMPP Anda langsung ke folder `/public` proyek ini).*

---

## 🔑 Kredensial Akses Default

Setelah menjalankan `DatabaseSeeder`, akun administrator bawaan akan tersedia:
- **URL Login Admin**: `http://localhost:8080/admin/login` (atau sesuai `baseURL`)
- **Username**: `admin`
- **Password**: `admin123`

*(Harap segera mengganti kredensial default ini apabila diaplikasikan pada lingkungan produksi).*

---

## 🔍 Alur Status Permohonan

```text
[ Draft ] ──> [ Menunggu Verifikasi ] ──> [ Diproses ] ──> [ Selesai / Ditolak ]
```

---

## 🧩 Penanganan Masalah Umum (Troubleshooting)

| Kendala | Penyebab Umum | Solusi |
| :--- | :--- | :--- |
| **Class "Dompdf\Dompdf" not found** | Dependensi belum terunduh | Jalankan `composer install` di root proyek. |
| **Database Connection Error** | Koneksi `.env` salah atau DB belum ada | Buat database dan periksa baris `database.default.*` di `.env`. |
| **Error 404 pada Halaman Tertentu** | Modul rewrite Apache belum aktif | Pastikan `mod_rewrite` aktif atau gunakan `php spark serve`. |
| **Gagal Mengunggah Berkas / Log Error** | Folder `writable/` tidak writable | Jalankan perintah `chmod -R 777 writable/`. |

---

## 📄 Lisensi
Proyek ini dilisensikan di bawah [MIT License](LICENSE).
