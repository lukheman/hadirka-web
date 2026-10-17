# IPM HRIS & Payroll

Sistem informasi outsourcing **PT Indoteknik Prima Mekongga**: database karyawan & kandidat,
kehadiran (absensi, izin, cuti, SKS), penggajian (payroll, pinjaman, rapel, THR, potongan),
keuangan (kas, jurnal, buku besar, laporan), K3/APD, surat peringatan, plus REST API
untuk aplikasi mobile karyawan.

## Tech Stack

| Lapisan | Teknologi |
|---|---|
| Backend | PHP (PDO MySQL + prepared statements), MySQL/MariaDB |
| Frontend | Bootstrap 5 (template Sneat), jQuery, DataTables, Select2, SweetAlert2, Toastr, Flatpickr |
| Cetak/Unduh | html2pdf.js, JSZip + FileSaver, SheetJS (XLSX) |
| Import/Export server | PhpSpreadsheet (`composer.json` root) |
| Mobile API | `app/api/index.php` — satu route `app/api/` (aplikasi absen mobile, dipasang di `/api/*`) |

## Syarat

* PHP >= 7.4 (disarankan 8.x) + ekstensi `pdo_mysql`, `gd`, `mbstring`, `zip`, `xml`
* MySQL/MariaDB, Composer 2
* Apache/Nginx dengan `mod_rewrite` (atau PHP built-in untuk dev)

## Instalasi

```bash
git clone <repo> && cd ipm-hris

# 1. Dependensi PHP (folder vendor/ tidak di-commit)
composer install

# 2. Konfigurasi environment
cp .env.example .env
# isi: APP_URL, DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD

# 3. Import database (47 tabel, lihat ipm_hris_structure.sql bila ada)
mysql -u user -p nama_db < struktur.sql

# 4. Folder upload harus writable oleh web server
mkdir -p app/uploads/{profil,absensi,template-kontrak,kontrak,cuti,izin,pinjaman,sks,apd,bukti_transaksi} \
         assets/img/user-pic
chmod -R 775 app/uploads assets/img/user-pic

# 5. Jalankan (dev) — router.php wajib agar /api/* dan URL cantik aktif
php -S localhost:8000 router.php
# buka http://localhost:8000/login.php
```

> Dump `*.sql` dan `vendor/` sengaja tidak di-commit (lihat `.gitignore`).

## Struktur Direktori

```
index.php                  # entry point + routing ?page= + otorisasi level
login.php                  # login web
slipgajinew.php            # cetak slip gaji (standalone, dibuka tab baru)
slip-thr.php               # cetak slip THR (standalone)
app/
  config/                  # koneksi.php, env.php, auth.php, function.php,
                           # function-slip.php, Excel.php (helper import terpusat)
  controller/              # act-*.php (CRUD), posting-*.php,
    csv-control/           # import Excel/CSV (pakai Excel::*)
    export/                # export Excel (PhpSpreadsheet)
    get-data/              # JSON untuk tabel (56 endpoint)
    get-slip/              # JSON untuk slip gaji
    surat/                 # surat peringatan & pelanggaran
  api/                     # SATU API aplikasi absen mobile (index.php +
                           # helpers.php + handlers_*.php; terpasang di /api/*)
view/                      # halaman per modul (di-include index.php)
  master-data/ master-akses/ kehadiran/ penggajian/
  finance-payrol/ k3/ surat/ broadcast/
layout/menu.php            # sidebar + daftar halaman per level
assets/js/periode.js       # getPeriodeBulan()/setDefaultBulan() terpusat
docs/                      # arsitektur, rencana refactor, spec lama
```

## Routing & Hak Akses

Navigasi memakai `?page=<key>` sesuai `$routes` di `index.php`. Level user
(`tb_user.level`): `0` Master, `1` Direktur, `2` HRD, `3` Supervisor,
`4` Safety/K3, `5` Admin, `6` Finance. Modul sensitif dicek di `index.php`
(HRD, Finance, K3, Sistem) — user tak berhak mendapat dialog
penolakan + redirect ke home.

Modul utama: Data Karyawan/Site/Jabatan/Karier/Kontrak/BPJS/Loker, Rekapan
Absensi, Pengajuan Izin/Cuti/SKS, Payroll + Pinjaman + Rapel + THR + Potongan,
Periode + COA + Kelola Transaksi + Jurnal/Buku Besar/Neraca/Laporan Keuangan,
Master & Transaksi APD, Surat Peringatan, Pengumuman, Users.

## Konvensi Kode (wajib diikuti)

* **Notifikasi:** `showToast(pesan, 'success'|'error'|'warning'|'info')` — satu
  definisi global di `index.php`. Jangan pakai `alert()` / definisi lokal.
* **Konfirmasi:** `await confirmDialog('Yakin hapus?')` → `true/false`
  (SweetAlert2, selalu di atas modal via CSS `.swal2-container`).
  Jangan pakai `confirm()` native.
* **Periode:** `getPeriodeBulan()` / `setDefaultBulan()` dari
  `assets/js/periode.js`. Jangan definisikan ulang di view.
* **Import CSV:** pakai `Excel::cleanRupiah()`, `Excel::parseDate()`,
  `Excel::esc()`, `Excel::json()` dari `app/config/Excel.php`.
* **Response JSON controller:** `{status: 'success'|'error', msg|message}`.
  Frontend menyesuaikan key per endpoint (`msg` untuk `act-*`, `message`
  untuk posting/import).
* **Require path:** selalu `__DIR__`-based
  (`require_once __DIR__.'/../config/koneksi.php'`), jangan path relatif
  (`../../...`) yang tergantung CWD.
* **Hapus data:** konfirmasi via `confirmDialog`, hapus fisik file terkait
  (`@unlink`) seperti pola `act-kelola-transaksi.php`.

## API Mobile — Satu Route (`app/api/`)

Seluruh API disajikan oleh **satu front controller**: `app/api/index.php`
(tabel rute ada di dalamnya; helper & handler terpisah per domain:
`helpers.php`, `handlers_auth.php`, `handlers_attendance.php`,
`handlers_services.php`). Dipasang di `/api/*` via `.htaccess` (produksi)
atau `router.php` (dev `php -S`), dengan kontrak respons JSON
`{success:true,data}` / `{success:false,message}`.

Sistem API lama sudah dihapus: file v1 (`app/api/*.php` — login OTP,
aktivasi, APD, SKS, kontrak, dsb.) beserta `composer.json` PHPMailer-nya,
dan folder `app/api/v2/`. Aplikasi absen mobile (Flutter, GPS + selfie)
memakai API ini — satu database dengan website, backend Node.js lama tidak
dipakai lagi. Modul pendukung di web: geofence di Data Site, Absensi Mobile
(GPS), Notifikasi Aplikasi, Pengajuan Pinjaman, Slip Gaji Aplikasi.

Panduan lengkap (endpoint, deploy, checklist go-live): `docs/INTEGRASI-ABSEN-MOBILE.md`.

## Dokumen Terkait

* `docs/INTEGRASI-ABSEN-MOBILE.md` — integrasi aplikasi absen mobile (satu API `app/api/`, geofence, deploy)
* `docs/ARSITEKTUR-APLIKASI.md` — arsitektur & alur data
* `docs/RENCANA-REFACTOR-DRY.md` — status refactor DRY (toast, confirm,
  Excel.php, periode.js, hapus file mati)
* Backup file yang pernah dihapus: `/tmp/opencode/backup-hapus-20260909.zip`
  (di luar repo; minta ke maintainer bila perlu restore)
