# Integrasi Aplikasi Absen Mobile ke Website IPM HRIS

Dokumen ini menjelaskan integrasi **aplikasi absen mobile (Flutter, repo `ipm-hris-karyawan`)**
ke dalam **website IPM HRIS** menjadi satu sistem penuh: satu database, satu server (PHP),
aplikasi hanya sebagai klien.

## 1. Ringkasan & Arsitektur

**Sebelum**

```
Aplikasi Flutter ──► Node.js backend (port 3000) ──► MySQL db_absen_tjs (terpisah)
Website HRIS      ──► PHP + MySQL                    (database website)
```

**Sesudah**

```
Aplikasi Flutter ──► Website IPM HRIS ──► MySQL (database website, satu sumber data)
                     /api/*  →  app/api/index.php  (satu route / satu front controller)
```

* Backend Node.js (`ipm-hris-karyawan/backend_server`) dan database `db_absen_tjs` **tidak
  dipakai lagi** (simpan sebagai cadangan sampai aplikasi versi baru terbukti stabil).
* Semua data (karyawan, absensi, pengajuan, slip, notifikasi, geofence) tersimpan di
  database website sehingga rekap HRD dan aplikasi selalu sinkron.

## 2. Perubahan Database

Migration:

* `db/migrations/20261017100000_integrasi_absen_mobile.php`
* `db/migrations/20261017200000_satukan_tb_absen.php` — data absen disatukan.

| Objek | Isi |
|---|---|
| `tb_absen` (diperluas) | **SATU tabel absen** untuk HRD, rekap, payroll, dan aplikasi. Kolom baru: `sumber` (`web`/`mobile`), `id_lokasi` (FK `tb_site`), `nama_lokasi`, `latitude`, `longitude`, `jarak_meter`, `foto_selfie_path`, `waktu_masuk`, `waktu_keluar`, `metode_checkout` (`manual`/`otomatis`), `status_kehadiran`, `keterlambatan_menit`, `is_tepat_waktu`, `dibuat_pada` |
| `tb_notifikasi` (baru) | Notifikasi aplikasi: judul, pesan, kategori, `is_dibaca` |
| `tb_slip_gaji` | Dihapus; slip mobile dihitung langsung dari periode payroll dan data payroll utama |
| `tb_site` (+kolom) | Geofence: `kode_lokasi`, `latitude`, `longitude`, `radius_meter` (default 80), `deskripsi_lokasi`, `is_aktif` |
| `tb_pengajuan_pinjaman` (ubah) | `foto_wajah` & `foto_id_card` dibuat nullable (pengajuan dari aplikasi tanpa foto) |

> Riwayat: versi pertama integrasi membuat tabel terpisah `tb_absen_mobile`
> (agar tabel rekap web lama tidak tersentuh). Karena sistem ini akan dipakai
> sebagai instalasi baru, data absen kini **disatukan** — `tb_absen_mobile`
> dihapus oleh migration satukan. Baris absen aplikasi memakai `sumber='mobile'`,
> `ket_absen='HADIR'`, `jam_kerja='1'` sehingga rekap harian dan payroll
> (rumus `SUM(tb_absen.jam_kerja)` hari HADIR/SPL) otomatis ikut menghitungnya.
> HRD tetap bisa mengoreksi lewat menu Daily Absen (baris yang sama).

Fitur yang memakai tabel website yang sudah ada:

* Aktivasi/login & profil → `tb_karyawan` (NIK harus sudah ada; password di-set saat aktivasi).
* Pengajuan cuti → `tb_pengajuan_cuti`; izin (termasuk SKS) → `tb_pengajuan_izin`.
* Pengajuan pinjaman → `tb_pengajuan_pinjaman` (gerbang `tb_pengaturan.buka_pinjaman`).

## 3. API Mobile — Satu Route (`app/api/`)

Seluruh endpoint disajikan oleh **satu front controller**: `app/api/index.php`.
Daftar rute hanya ada di file itu; helper & handler dipisah per domain agar rapi:

* `index.php` — SATU pintu masuk + tabel rute (satu-satunya routing).
* `helpers.php` — input JSON, envelope, Haversine, status mapping, dsb.
* `handlers_auth.php`, `handlers_attendance.php`, `handlers_services.php` — handler.

Dokumentasi detail API pengajuan izin, cuti, dan SKS: `docs/API-PENGAJUAN-IZIN-CUTI-SKS.md`.
Dokumentasi API slip gaji: `docs/API-SLIP-GAJI.md`.

> **Konsolidasi:** sistem API lama sudah dihapus — seluruh file v1
> (`app/api/*.php`: login OTP, aktivasi, APD, SKS, kontrak, dsb.) beserta
> `composer.json`/`vendor` PHPMailer-nya, dan folder `app/api/v2/`. Tinggal
> API ini; jangan menambah file endpoint baru di `app/api/` — tambahkan rute
> di tabel `index.php` + handler-nya.

**Pemasangan URL**

* Produksi (Apache/XAMPP): aturan di `.htaccess` root repo
  (`RewriteRule ^api(/.*)?$ app/api/index.php [L,QSA]`) — pastikan `mod_rewrite` aktif.
* Dev: `php -S localhost:8000 router.php` (router khusus menangani `/api`).
* Debug manual: `app/api/index.php?__route=auth/login`.

**Format respons** (sama dengan backend lama):

* Sukses: `{"success": true, "data": ...}` — login mengembalikan `{"success":true,"user":{...}}`.
* Gagal: `{"success": false, "message": "..."}` + HTTP status yang sesuai.
* Autentikasi: aplikasi mengirim `Authorization: Bearer <token>`. Token dibuat saat login
  ** tetapi belum divalidasi** (parity dengan backend lama). Validasi token = rencana penguatan
  berikutnya.

**Daftar endpoint**

| Method | Path | Keterangan |
|---|---|---|
| POST | `/auth/login` | `{identifier, password}` → user + token |
| POST | `/auth/register` | Aktivasi akun: `{nik, nama, password, noHp}` — NIK harus ada di `tb_karyawan` |
| PUT | `/user/profile` | `{username, nama, noHp, fotoProfil(base64)}` |
| PUT | `/user/password` | `{username, oldPassword, newPassword}` |
| GET | `/attendance` | `?username=` — riwayat absen; response menyertakan `metodeCheckout` (`manual`/`otomatis`/`null`) |
| POST | `/attendance/submit` | `{username, latitude, longitude, accuracy, tipe: check_in/check_out, fotoPath, fotoBase64}` — `accuracy` wajib maksimal 100 meter |
| GET / POST | `/leave` | daftar & buat pengajuan (cuti/izin/SKS) |
| PUT | `/leave/:id/status` | opsional `sumber=izin|cuti` (id izin & cuti bisa sama) |
| GET / POST | `/loan` | daftar & buat pengajuan pinjaman (dibatasi `buka_pinjaman`) |
| PUT | `/loan/:id/status` | parity: tidak mengubah apa pun |
| GET | `/geofence` | site penempatan karyawan aktif (`?username=IPM-00001`), sudah punya koordinat |
| PUT | `/geofence/:id` | update titik dari aplikasi |
| GET | `/payslip` | daftar slip gaji milik karyawan; read-only |
| GET / POST / DELETE | `/users` | POST/DELETE membalas "dikelola dari website" |
| GET | `/notifications` | `?username=` — notifikasi + tandai dibaca |

**Catatan kontrak penting**

* Zona waktu `Asia/Makassar`; batas terlambat **08:00 WITA**; `jarak_meter` = Haversine ke
  site aktif terdekat; status kehadiran: `Tepat Waktu`, `Terlambat {n}m`, `Sudah Selesai`.
* Foto selfie (base64) disimpan ke `app/uploads/absensi/`; foto profil ke
  `app/uploads/profil/`. Yang dikembalikan ke aplikasi berupa base64.
* Pemetaan status pengajuan: web `pending` → app `MENUNGGU`, `disetujui/dicairkan/lunas` →
  `DISETUJUI`, `ditolak` → `DITOLAK` (dan sebaliknya).
* Email user di aplikasi: `{nik}@ipm.local`.

## 4. Halaman Web Baru / Berubah

| Halaman | Route `?page=` | Akses | Fungsi |
|---|---|---|---|
| Master Data → Data Site | `data-site` | Master/HRD | Isi geofence (lat, lng, radius, kode, deskripsi, aktif). Site tanpa koordinat tidak dikirim ke aplikasi |
| Kehadiran → Absensi Mobile (GPS) | `absen-mobile` | HRD | Monitoring log absen aplikasi: filter tanggal/site/status, lihat selfie |
| Informasi → Notifikasi Aplikasi | `notifikasi-app` | HRD | Kirim notifikasi ke semua aktif / per site / per karyawan; daftar & hapus |
| Payroll → Pengajuan Pinjaman | `pengajuan-pinjaman` | Finance/Master | Saklar buka/tutup akses pinjaman, ACC/tolak/cair (satuan & massal) |

Controller: `app/controller/act-site.php`, `act-notifikasi.php`,
dan `app/controller/get-data/get-{site,absen-mobile,notifikasi}.php`.
Pengajuan pinjaman memakai controller yang sudah ada
(`get-data/get-pengajuan-pinjaman.php`, `act-pengajuan-pinjaman.php`).

> Catatan: input keterangan pinjaman memakai kolom `keterangan_admin` yang sudah ada;
> saat ditolak, isi kolom ini yang tampil ke karyawan di aplikasi.

## 5. Perubahan Aplikasi Flutter (repo `ipm-hris-karyawan`)

| File | Perubahan |
|---|---|
| `lib/core/constants/api_constants.dart` | `setCustomUrl()` tidak lagi memaksa port `:3000`; alamat otomatis dirapikan + ditambah `/api`. Default dev pindah ke port 8000 |
| `lib/core/widgets/dialog_pengaturan_server.dart` | Teks bantuan & contoh alamat diubah ke alamat website |
| `lib/features/attendance/...` (service, repository, provider) | Submit absen wajib mengirim `fotoBase64` (selfie JPEG dibaca dari file lokal, maksimal 7MB) — server menyimpan ke `app/uploads/absensi/` |
| `.env` / `.env.example` | Contoh URL website + kandidat fallback baru |

**Cara menyetel server di aplikasi:** buka dialog *Pengaturan Server* di aplikasi, isi:

* Dev (`php -S`): `http://192.168.x.x:8000` → otomatis menjadi `.../api`
* XAMPP/Laragon: `http://192.168.x.x/ipm-hris` → otomatis menjadi `.../ipm-hris/api`
* Bisa juga lewat `.env`: `API_BASE_URL=http://alamat/ipm-hris/api`

## 6. Deploy & Go-Live Checklist

1. Deploy kode website (repo ini), `composer install`, isi `.env` (koneksi DB).
2. Pastikan folder upload writable:
   `app/uploads/{absensi,profil}` (sisanya sudah ada dari instalasi awal).
3. Apache: aktifkan `mod_rewrite` (aturan `/api` sudah di `.htaccess`).
   Dev: `php -S localhost:8000 router.php`.
4. Jalankan migration bila server baru (lihat `db/migrations/`).
5. **Isi koordinat semua site aktif di Master Data → Data Site** — wajib agar aplikasi
   bisa check-in (site tanpa titik tidak akan muncul di aplikasi).
6. Tentukan kebijakan pinjaman di halaman *Payroll → Pengajuan Pinjaman* (saklar
   buka/tutup).
7. Di aplikasi: set alamat server, lalu uji login, geofence, check-in/out, pengajuan,
   notifikasi, slip.
8. Nonaktifkan service Node.js lama (dan simpan backup `db_absen_tjs`).

## 7. Keputusan Desain & Catatan

* **Satu tabel absen.** Absen aplikasi menulis langsung ke `tb_absen`
  (`sumber='mobile'`, `ket_absen='HADIR'`, `jam_kerja='1'` = hari penuh berbayar)
  sehingga rekap & payroll otomatis menghitungnya. Aturan tabrakan: kalau HRD
  sudah mengisi hari itu selain HADIR (IZIN/SAKIT/CUTI/MANGKIR/OFF), absen dari
  aplikasi ditolak (HTTP 409) dan karyawan diminta hubungi HRD; kalau HRD
  mengisi HADIR, barisnya diperkaya data GPS/jam/selfie dari aplikasi.
* Register = **aktivasi** akun karyawan yang sudah dibuat HRD (bukan daftar karyawan baru).
* `PUT /leave/:id/status` menerima `sumber` opsional karena id `tb_pengajuan_izin`
  (varchar) dan `tb_pengajuan_cuti` (auto increment) bisa bertabrakan; tanpa `sumber`
  dicoba izin lebih dulu lalu cuti.
* Login/perubahan data karyawan dari aplikasi memakai tabel website — `tb_karyawan.created_at`
  ikut berubah saat UPDATE (perilaku lama website, tidak diubah).
* Token sesi dibuat tapi belum divalidasi; amankan endpoint publik lebih lanjut bila
  aplikasi dipakai di luar jaringan kantor.
* Kolom `tb_pinjam`/`tb_notifikasi` memakai charset `utf8mb4_general_ci`
  seperti tabel aplikasi; join dengan tabel website (sebagian `utf8mb4_unicode_ci`)
  memakai `COLLATE` eksplisit bila diperlukan.

## 8. Rollback

* Aplikasi: ubah alamat server kembali ke `http://IP:3000/api` (dialog pengaturan).
* Website: halaman baru tidak mengganggu halaman lama; migration bersifat *additive*
  (kolom `tb_site` & `tb_pengajuan_pinjaman` hanya ditambah/dilonggarkan).
* Data yang dibuat aplikasi tersimpan di tabel website; backup rutin tetapkan seperti biasa.

## 9. File Terkait

* API: `app/api/` — satu route di `index.php` + `helpers.php` + `handlers_*.php`
* Migration: `db/migrations/20261017100000_integrasi_absen_mobile.php`,
  `db/migrations/20261017200000_satukan_tb_absen.php`,
  `db/migrations/20261020000000_drop_tb_slip_gaji.php`,
  `db/migrations/20261020100000_add_metode_checkout_to_tb_absen.php`
* View: `view/master-data/data-site.php`, `view/kehadiran/absen-mobile.php`,
  `view/broadcast/notifikasi-app.php`, `view/penggajian/pengajuan-pinjaman.php`,
* Controller: `app/controller/{act-site,act-notifikasi}.php`,
  `app/controller/get-data/get-{site,absen-mobile,notifikasi}.php`,
  `app/controller/{act-pengajuan-pinjaman,get-data/get-pengajuan-pinjaman}.php`
* Routing & menu: `index.php`, `layout/menu.php`, `.htaccess`, `router.php`
* Aplikasi: `lib/core/constants/api_constants.dart`,
  `lib/features/attendance/{providers,data}` dan `lib/core/widgets/dialog_pengaturan_server.dart`
