# Dokumentasi Struktur dan Teknologi Aplikasi

## 1. Ringkasan

Project ini adalah aplikasi HRIS untuk perusahaan outsourcing. Fitur utama:

- Manajemen data karyawan dan kandidat.
- Manajemen karier, site, departemen, divisi, jabatan, dan kontrak.
- Absensi, izin, cuti, sakit, libur, dan bukti SKS.
- Payroll, slip gaji, THR, rapel, tunjangan, dan potongan.
- Pinjaman karyawan.
- Finance dan akuntansi sederhana.
- Manajemen APD/K3.
- Pengumuman dan surat peringatan.
- Login admin dan API untuk aplikasi mobile.

Aplikasi menggunakan PHP procedural dengan pola front controller sederhana. Sebagian besar halaman diakses melalui `index.php`, sedangkan API dan controller dipanggil langsung melalui request AJAX atau aplikasi mobile.

## 2. Teknologi

### Backend

- PHP 8.x.
- MySQL/MariaDB.
- MySQLi untuk koneksi dan query database.
- Session PHP untuk login web.
- JSON untuk response API.
- Composer untuk dependency management.

### Frontend

- HTML5.
- CSS3.
- JavaScript.
- jQuery.
- Bootstrap 5.
- Sneat-style dashboard template.
- DataTables untuk tabel interaktif.
- Select2 untuk dropdown.
- Flatpickr untuk input tanggal.
- Toastr/IziToast/SweetAlert untuk notifikasi.
- ApexCharts dan Chart.js untuk grafik.

### Dependency PHP

Dependency utama berada di `composer.json`:

- `phpoffice/phpspreadsheet`: import dan export Excel.

Dependency transitive yang dipasang Composer:

- `composer/pcre`.
- `maennchen/zipstream-php`.
- `markbaker/complex`.
- `markbaker/matrix`.
- `psr/simple-cache`.

PHPMailer berada pada `app/api/vendor/` dan digunakan untuk kebutuhan email/OAuth tertentu.

## 3. Struktur Folder

```text
project-root/
├── index.php
├── login.php
├── slipgajinew.php
├── slip-thr.php
├── composer.json
├── composer.lock
├── .env.example
├── .htaccess
├── app/
│   ├── api/
│   ├── config/
│   └── controller/
├── assets/
│   ├── css/
│   ├── img/
│   ├── template-csv/
│   └── vendor/
├── layout/
├── view/
├── database/
├── docs/
└── vendor/
```

### Root Files

#### `index.php`

Front controller aplikasi web. Tugas utama:

- Memuat autentikasi.
- Memuat koneksi database.
- Membaca parameter `page`.
- Menentukan route ke file view.
- Memeriksa akses berdasarkan level user.
- Memuat layout utama.

Contoh URL routing:

```text
/?page=home
/?page=data-karyawan
/?page=payroll
```

#### `login.php`

Halaman login user admin/web.

#### `slipgajinew.php` dan `slip-thr.php`

Halaman khusus slip gaji dan slip THR yang sebagian berjalan di luar routing utama `index.php`.

#### `.htaccess`

Mengatur clean URL pada Apache. Contoh:

```text
/login             -> login.php
/data-karyawan     -> index.php?page=data-karyawan
/data-tunjangan/X  -> index.php?page=data-tunjangan&id=X
```

Clean URL membutuhkan Apache `mod_rewrite`. `.htaccess` tidak diproses oleh Nginx.

### `app/config/`

Konfigurasi dan helper aplikasi.

#### `env.php`

Loader sederhana untuk membaca file `.env` tanpa dependency tambahan.

Variable utama:

```env
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nama_database
DB_USERNAME=user_database
DB_PASSWORD=password_database
APP_URL=https://domain.example/
```

#### `koneksi.php`

Koneksi database utama untuk aplikasi web. File ini:

- Memuat `env.php`.
- Membaca konfigurasi database dari environment.
- Membuat koneksi MySQLi.
- Mengatur charset `utf8mb4`.
- Menyediakan fungsi `url()`.

#### `auth.php`

Middleware login web. Jika session `id_user` tidak tersedia, user diarahkan ke `login.php`.

#### `function.php`

Helper umum, antara lain:

- Format tanggal Indonesia.
- Format Rupiah.
- Konversi nomor telepon.
- Perhitungan umur.
- Perhitungan jumlah hari.
- Helper active menu.

### `app/controller/`

Berisi proses bisnis dan endpoint aplikasi web.

Kelompok controller:

- `act-*.php`: proses tambah, ubah, hapus, dan approval.
- `get-data/`: mengambil data untuk DataTables/AJAX.
- `get-slip/`: mengambil data slip gaji.
- `export/`: export data ke file.
- `csv-control/`: import data dari template Excel/CSV.
- `surat/`: proses surat peringatan dan pelanggaran.

Controller umumnya dipanggil dengan AJAX, contohnya:

```text
app/controller/act-karyawan.php
app/controller/get-data/get-karyawan.php
```

### `app/api/`

Endpoint JSON untuk aplikasi mobile atau integrasi eksternal.

Fitur API meliputi:

- Login karyawan.
- Aktivasi akun.
- Reset password dan OTP.
- Riwayat absensi.
- Pengajuan izin, cuti, pinjaman, dan APD.
- Data slip gaji.
- Upload dan download kontrak.
- Pengumuman.
- Update profil.

Koneksi API berada di:

```text
app/api/koneksi-api.php
```

### `view/`

Berisi tampilan modul aplikasi.

Modul utama:

- `view/master-data/`: data karyawan, site, jabatan, departemen, karier, kontrak, BPJS.
- `view/master-akses/`: import atau pengaturan akses tertentu.
- `view/kehadiran/`: absensi, izin, cuti, sakit, dan SKS.
- `view/penggajian/`: payroll, THR, rapel, potongan, dan pinjaman.
- `view/finance-payrol/`: COA, jurnal, buku besar, neraca, laporan keuangan.
- `view/k3/`: APD dan transaksi K3.
- `view/surat/`: pelanggaran dan surat peringatan.
- `view/broadcast/`: pengumuman.

### `layout/`

Komponen layout bersama:

- `menu.php`: sidebar menu berdasarkan level user.
- Header, footer, dan struktur dashboard dimuat dari `index.php`.

### `assets/`

Berisi asset frontend dan file template import.

- `assets/css/`: CSS custom.
- `assets/img/`: logo, avatar, tanda tangan, dan gambar aplikasi.
- `assets/template-csv/`: template Excel import data.
- `assets/vendor/`: JavaScript/CSS vendor lokal.

### `database/`

```text
database/schema.sql
```

Berisi schema database hasil rekonstruksi dari query aplikasi. Schema mencakup tabel HRD, payroll, finance, K3, user, pengumuman, OTP, dan modul lainnya.

### `docs/`

Dokumentasi teknis project.

## 4. Alur Request Web

Alur request halaman utama:

```text
Browser
  -> index.php
  -> app/config/auth.php
  -> app/config/koneksi.php
  -> baca $_GET['page']
  -> cek level user
  -> pilih file pada $routes
  -> render view
  -> render layout/menu.php
```

Alur login:

```text
Browser
  -> login.php
  -> app/controller/login.php
  -> SELECT tb_user
  -> password_verify()
  -> simpan $_SESSION
  -> redirect ke dashboard
```

Alur AJAX CRUD:

```text
View JavaScript
  -> fetch/AJAX
  -> app/controller/act-*.php
  -> validasi input
  -> query MySQLi
  -> response JSON
  -> update tabel/notifikasi
```

## 5. Level User

Kolom role disimpan pada:

```text
tb_user.level
```

Mapping role:

| Level | Role | Akses utama |
|---:|---|---|
| 0 | Master | Semua modul dan user management |
| 1 | Direktur | HRD, finance, K3 |
| 2 | HRD | HRD dan finance/payroll |
| 3 | Supervisor | HRD dan K3 |
| 4 | Safety | K3 |
| 5 | Admin | HRD umum |
| 6 | Finance | Finance dan payroll |
| 99 | Default/tidak dikenal | Tidak punya akses khusus |

Kontrol akses utama berada di `index.php` dan `layout/menu.php`.

## 6. Database

Database yang dipakai adalah MySQL/MariaDB dengan engine InnoDB dan charset `utf8mb4`.

Kelompok tabel:

### Master HRD

- `tb_karyawan`
- `tb_identitas`
- `tb_karier`
- `tb_kontak_darurat`
- `tb_site`
- `tb_departemen`
- `tb_devisi`
- `tb_jabatan`
- `tb_tunjangan`
- `tb_potongan`

### Kehadiran dan pengajuan

- `tb_absen`
- `tb_izin`
- `tb_sakit`
- `tb_cuti`
- `tb_libur`
- `tb_jatah_cuti`
- `tb_pengajuan_cuti`
- `tb_pengajuan_izin`
- `tb_bukti_sks`

### Payroll

- `tb_priode`
- `tb_pinjaman`
- `tb_pengajuan_pinjaman`
- `tb_rapel`
- `tb_thr`
- `tb_potongan_tidak_tetap`

### User dan sistem

- `tb_user`
- `tb_user_token`
- `tb_pengaturan`
- `tb_pengumuman`
- `tb_reset_otp`
- `footer_shortcuts`

### Finance

- `tb_kategori_keuangan`
- `tb_transaksi_keuangan`

### K3/APD

- `master_apd`
- `tb_pengajuan_apd`
- `pengambilan_apd`

### Surat

- `m_jenis_pelanggaran`
- `m_jenis_keluar`
- `tb_tereliminasi`
- `tb_surat_peringatan`
- `tb_calon_karyawan`
- `tb_loker`

## 7. Password dan Login

### User web

User web pada `tb_user` dibuat memakai:

```php
password_hash($password, PASSWORD_DEFAULT)
```

Validasi login web memakai:

```php
password_verify($password, $data['password'])
```

### Karyawan mobile

Login mobile lama menggunakan MD5. Password yang diubah melalui Master saat ini disimpan dengan:

```php
md5($new_password)
```

API mobile menerima password bcrypt dan MD5, untuk menjaga kompatibilitas selama masa migrasi.

MD5 tidak direkomendasikan untuk produksi jangka panjang karena tidak aman untuk password. Migrasi ke `password_hash()` dan `password_verify()` sebaiknya dilakukan setelah aplikasi mobile diperbarui.

## 8. Konfigurasi Environment

Credential database disimpan di `.env`, bukan di source code.

Contoh:

```env
APP_ENV=production
APP_URL=https://domain.example/
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=database_name
DB_USERNAME=database_user
DB_PASSWORD=database_password
```

`.env` tidak boleh di-commit. File template yang boleh disimpan di repository:

```text
.env.example
```

## 9. Instalasi Lokal

1. Pastikan PHP, MySQL/MariaDB, dan Composer tersedia.
2. Buat file `.env` dari `.env.example`.
3. Isi credential database.
4. Import schema:

```bash
mysql -u root -p < database/schema.sql
```

5. Install dependency:

```bash
composer install
```

6. Jalankan server development:

```bash
php -S localhost:8000
```

7. Buka:

```text
http://localhost:8000/login.php
```

## 10. Deployment Hosting

Untuk Apache:

- Upload project ke document root.
- Pastikan `.htaccess` ikut ter-upload.
- Aktifkan `mod_rewrite`.
- Pastikan PHP memiliki extension MySQLi, ZIP, GD, XML, mbstring, dan fileinfo.
- Buat `.env` di root project.
- Import database hosting.
- Jalankan `composer install --no-dev` jika Composer tersedia.

Untuk Nginx:

- `.htaccess` tidak berlaku.
- Buat aturan rewrite menuju `index.php`.
- Atau gunakan URL query langsung seperti `/?page=data-karyawan`.

## 11. Verifikasi Setelah Deployment

Tes syntax:

```bash
php -l index.php
php -l login.php
php -l app/config/koneksi.php
```

Tes dependency:

```bash
php -r "require 'vendor/autoload.php'; echo class_exists('PhpOffice\\PhpSpreadsheet\\Spreadsheet') ? 'OK' : 'FAIL';"
```

Tes halaman:

```text
/login.php
/?page=home
/?page=data-karyawan
```

Tes fungsi:

- Login user web.
- Logout.
- Tambah dan edit karyawan.
- Ubah password karyawan sebagai Master.
- Login mobile memakai password baru.
- Import/export Excel.
- Generate slip gaji.
- Pengajuan cuti dan pinjaman.

## 12. Catatan Keamanan

Temuan yang perlu ditangani sebelum production:

- Password mobile masih memakai MD5.
- API sensitif belum seluruhnya memiliki autentikasi dan authorization yang kuat.
- Beberapa endpoint memakai CORS wildcard.
- Upload file masih perlu validasi MIME dan penyimpanan di luar web root.
- CSRF protection belum terpusat.
- File debugging dan file eksperimen tidak boleh tersedia di web root.
- Credential database harus berada di `.env` dan password database perlu dirotasi jika pernah terekspos.
- Dependency PHPSpreadsheet perlu diperbarui berdasarkan hasil `composer audit`.

## 13. Status Pengujian

Pemeriksaan syntax PHP telah dilakukan pada file-file utama. Project belum memiliki automated test suite. Pengujian database, login, API, dan upload membutuhkan environment MySQL/MariaDB yang aktif.
