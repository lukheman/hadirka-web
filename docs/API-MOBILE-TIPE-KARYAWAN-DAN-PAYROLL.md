# Dokumentasi API Mobile: Tipe Karyawan dan Dua Perhitungan Gaji

Dokumen ini menjadi acuan implementasi API pada aplikasi mobile setelah project
mendukung dua tipe karyawan dan dua metode perhitungan gaji.

## 1. Base URL

Development:

```text
http://HOST:8000/api
```

Production:

```text
https://domain.com/ipm-hris/api
```

Header umum:

```http
Content-Type: application/json
Accept: application/json
Authorization: Bearer <token>
```

Format sukses:

```json
{
  "success": true,
  "data": {}
}
```

Format gagal:

```json
{
  "success": false,
  "message": "Pesan kesalahan"
}
```

## 2. Tipe Karyawan

Master tipe karyawan berada pada tabel:

```text
tb_tipe_karyawan
```

Relasi karyawan:

```text
tb_karyawan.id_tipe_karyawan
    -> tb_tipe_karyawan.id_tipe_karyawan
```

Tipe yang tersedia:

| Tipe | Metode | Dasar perhitungan |
|---|---|---|
| `Kontrak` | `bulanan` | Gaji pokok bulanan |
| `Harian` | `harian` | Upah harian dikali jumlah hari kerja |

Data jam kerja berasal dari `tb_tipe_karyawan`:

```text
jam_kerja_mulai
jam_kerja_selesai
```

Contoh master:

| Tipe | Mulai | Selesai |
|---|---:|---:|
| Kontrak | `08:00:00` | `17:00:00` |
| Harian | `08:00:00` | `16:00:00` |

`id_tipe_karyawan` menjadi sumber relasi utama. Field lama
`tb_karyawan.tipe_karyawan` dipertahankan untuk kompatibilitas data lama.

## 3. Login

### Endpoint

```http
POST /auth/login
```

### Request

```json
{
  "identifier": "IPM-00001",
  "password": "password123"
}
```

### Response yang disarankan

```json
{
  "success": true,
  "user": {
    "id": 1,
    "nik": "IPM-00001",
    "username": "IPM-00001",
    "nama": "Budi Santoso",
    "email": "IPM-00001@ipm.local",
    "noHp": "08123456789",
    "role": "karyawan",
    "jabatan": "Staff",
    "departemen": "Operasional",
    "tipeKaryawan": "Harian",
    "idTipeKaryawan": 2,
    "metodePerhitunganGaji": "harian",
    "jamKerjaMulai": "08:00:00",
    "jamKerjaSelesai": "16:00:00",
    "fotoProfil": null,
    "token": "token_karyawan_xxx"
  }
}
```

Mobile memakai field berikut untuk profil dan tampilan payroll:

```text
tipeKaryawan
idTipeKaryawan
metodePerhitunganGaji
jamKerjaMulai
jamKerjaSelesai
```

Mobile tidak boleh mengubah field tersebut melalui endpoint profil. Field hanya
dikelola HRD melalui website.

## 4. Endpoint Slip Gaji

### Request

```http
GET /payslip?username=IPM-00001
```

Filter periode:

```http
GET /payslip?username=IPM-00001&periode=2026-10-01%20-%202026-10-31
```

Untuk implementasi aman, identitas karyawan sebaiknya diambil dari token. User
biasa tidak boleh membaca slip karyawan lain dengan mengganti `username`.

### Response kompatibilitas lama

Field lama tetap dipertahankan:

```json
{
  "success": true,
  "data": [
    {
      "id": 12,
      "periode": "2026-10-01 - 2026-10-31",
      "gajiPokok": 4400000,
      "tunjangan": 300000,
      "potongan": 100000,
      "totalDiterima": 4600000,
      "status": "TERSEDIA",
      "createdAt": null,
      "detail": {}
    }
  ]
}
```

Jangan menghapus field lama agar aplikasi mobile versi lama tetap kompatibel.

### Response baru yang disarankan

```json
{
  "success": true,
  "data": [
    {
      "id": 12,
      "periode": "2026-10-01 - 2026-10-31",
      "tipeKaryawan": "Harian",
      "idTipeKaryawan": 2,
      "metodePerhitungan": "harian",
      "gajiPokok": 4400000,
      "gajiPokokBulanan": null,
      "upahHarian": 200000,
      "hariKerja": 22,
      "pendapatanDasar": 4400000,
      "tunjangan": 300000,
      "lembur": 0,
      "overtime": 0,
      "rapel": 0,
      "totalPendapatan": 4700000,
      "potongan": 100000,
      "totalDiterima": 4600000,
      "status": "TERSEDIA",
      "createdAt": null,
      "detail": {
        "nik": "IPM-00001",
        "nama": "Budi Santoso",
        "basic": 4400000,
        "hari_kerja": 22,
        "total_pendapatan": 4700000,
        "total_potongan": 100000,
        "gaji_bersih": 4600000
      }
    }
  ]
}
```

Untuk karyawan `Kontrak`:

```json
{
  "tipeKaryawan": "Kontrak",
  "idTipeKaryawan": 1,
  "metodePerhitungan": "bulanan",
  "gajiPokok": 5000000,
  "gajiPokokBulanan": 5000000,
  "upahHarian": null,
  "hariKerja": 22,
  "pendapatanDasar": 5000000,
  "tunjangan": 500000,
  "lembur": 0,
  "overtime": 0,
  "rapel": 0,
  "totalPendapatan": 5500000,
  "potongan": 100000,
  "totalDiterima": 5400000
}
```

## 5. Aturan Perhitungan Gaji

### Kontrak

```text
pendapatanDasar = gaji pokok bulanan
```

```text
totalPendapatan =
    gaji pokok bulanan
  + tunjangan
  + lembur
  + overtime
  + SPL
  + rapel
```

```text
gajiBersih = totalPendapatan - totalPotongan
```

Potongan izin dan mangkir memakai rate karier atau fallback:

```text
gaji pokok bulanan / 25
```

### Harian

```text
pendapatanDasar = hari kerja × upah harian
```

```text
totalPendapatan =
    pendapatanDasar
  + tunjangan
  + lembur
  + overtime
  + SPL
  + rapel
```

```text
gajiBersih = totalPendapatan - totalPotongan
```

Potongan izin dan mangkir memakai rate karier atau fallback:

```text
upah harian
```

## 6. Perubahan pada Absensi

### Endpoint

```http
POST /attendance/submit
```

Request tetap:

```json
{
  "username": "IPM-00001",
  "latitude": -4.0123,
  "longitude": 122.5123,
  "accuracy": 12.5,
  "tipe": "check_in",
  "fotoBase64": "data:image/jpeg;base64,..."
}
```

Perubahan yang harus diterapkan server:

1. Ambil `jam_kerja_mulai` dari tipe karyawan.
2. Hitung keterlambatan berdasarkan jam mulai tipe tersebut.
3. Jangan memakai jam `08:00` secara hard-code jika master tipe sudah memiliki
   jam kerja berbeda.
4. Kembalikan jam kerja pada response absensi.

Response tambahan:

```json
{
  "success": true,
  "data": {
    "id": 10,
    "tipe": "check_in",
    "status": "Terlambat 10m",
    "jamKerjaMulai": "08:00:00",
    "jamKerjaSelesai": "16:00:00",
    "keterlambatanMenit": 10
  }
}
```

## 7. Perubahan Endpoint Profil

Endpoint:

```http
PUT /user/profile
```

Field yang boleh diubah aplikasi:

```text
nama
noHp
fotoProfil
```

Field yang tidak boleh diubah aplikasi:

```text
tipeKaryawan
idTipeKaryawan
metodePerhitunganGaji
jamKerjaMulai
jamKerjaSelesai
```

## 8. Kontrak Data yang Harus Dipakai Mobile

Model user:

```text
id: int
nik: string
username: string
nama: string
tipeKaryawan: string
idTipeKaryawan: int?
metodePerhitunganGaji: string
jamKerjaMulai: string?
jamKerjaSelesai: string?
token: string
```

Model slip:

```text
id: int
periode: string
tipeKaryawan: string
idTipeKaryawan: int?
metodePerhitungan: string
gajiPokok: number
gajiPokokBulanan: number?
upahHarian: number?
hariKerja: number
pendapatanDasar: number
tunjangan: number
lembur: number
overtime: number
rapel: number
totalPendapatan: number
potongan: number
totalDiterima: number
status: string
```

Format uang memakai angka, bukan string berformat Rupiah:

```json
{
  "totalDiterima": 4600000
}
```

Format tampilan Rupiah dilakukan di aplikasi mobile.

## 9. Perubahan Backend yang Diperlukan

### Resolver tipe karyawan

File:

```text
app/helpers/slip_helper.php
```

Resolver sebaiknya membaca:

```sql
SELECT
    k.id_karyawan,
    k.tipe_karyawan,
    k.id_tipe_karyawan,
    t.nama_tipe,
    t.jam_kerja_mulai,
    t.jam_kerja_selesai
FROM tb_karyawan k
LEFT JOIN tb_tipe_karyawan t
    ON t.id_tipe_karyawan = k.id_tipe_karyawan
WHERE k.id_karyawan = ?
LIMIT 1
```

Gunakan `tb_tipe_karyawan.nama_tipe` sebagai sumber utama. Gunakan
`tb_karyawan.tipe_karyawan` sebagai fallback data lama.

### Payload user

File:

```text
app/api/helpers.php
```

Fungsi yang perlu diperbarui:

```text
api_user_payload()
```

Tambahkan tipe, ID tipe, metode perhitungan, dan jam kerja.

### Payload slip

File:

```text
app/api/handlers_services.php
```

Fungsi yang perlu diperbarui:

```text
api_payslip_list()
```

Tambahkan metadata tipe karyawan dan field payroll terstruktur. Field lama tetap
dipertahankan untuk kompatibilitas.

### Perhitungan absensi

File:

```text
app/api/helpers.php
app/api/handlers_attendance.php
```

Fungsi yang perlu diperbarui:

```text
api_late_minutes()
```

Fungsi harus menerima jam mulai kerja dari tipe karyawan.

## 10. Prioritas Implementasi Mobile

### Tahap 1: Model data

Tambahkan field:

```text
tipeKaryawan
idTipeKaryawan
metodePerhitungan
upahHarian
hariKerja
pendapatanDasar
totalPendapatan
```

Field tambahan harus nullable agar aplikasi tetap kompatibel dengan response
API lama.

### Tahap 2: Tampilan profil

Tampilkan:

```text
Tipe Karyawan: Harian
Jam Kerja: 08:00 - 16:00
```

### Tahap 3: Tampilan slip

Jika `metodePerhitungan` adalah `bulanan`:

```text
Gaji Pokok Bulanan
```

Jika `metodePerhitungan` adalah `harian`:

```text
Upah Harian × Hari Kerja
```

Contoh:

```text
Rp200.000 × 22 hari = Rp4.400.000
```

### Tahap 4: Absensi

Gunakan `jamKerjaMulai` untuk informasi keterlambatan dan tampilan jadwal.
Keputusan final keterlambatan tetap berasal dari server.

## 11. Keamanan API

Token saat ini dibuat ketika login, tetapi validasi token pada setiap request
belum diterapkan penuh. Sebelum production, implementasikan:

1. Penyimpanan hash token di database.
2. Middleware `Authorization: Bearer <token>`.
3. Masa berlaku token.
4. Logout dan revoke token.
5. Pembatasan data berdasarkan user dari token.
6. Larangan user biasa membaca `username` milik karyawan lain.
7. Proteksi endpoint admin.

Endpoint payroll harus mengambil identitas karyawan dari token, bukan hanya dari
parameter query `username`.

## 12. Checklist Pengujian

### Kontrak

- Login karyawan Kontrak.
- Login karyawan Harian.
- Payload user berisi tipe dan jam kerja.
- Data tipe tetap benar setelah perubahan master.

### Payroll Kontrak

- `pendapatanDasar` sama dengan gaji pokok bulanan.
- Tunjangan dan potongan benar.
- `totalDiterima` sama dengan website.

### Payroll Harian

- `hariKerja` benar.
- `upahHarian` benar.
- `pendapatanDasar = hariKerja × upahHarian`.
- `totalDiterima` sama dengan website.

### Absensi

- Jam kerja memakai tipe karyawan.
- Keterlambatan benar untuk tiap tipe.
- Geofence tetap berjalan.
- Check-in dan check-out tetap tersimpan ke `tb_absen`.

### Kompatibilitas

- Aplikasi mobile versi lama tetap membaca `gajiPokok`.
- Field baru tidak membuat parsing response gagal.
- Nilai kosong ditangani sebagai `null`.

## 13. File Backend Terkait

```text
app/api/index.php
app/api/helpers.php
app/api/handlers_auth.php
app/api/handlers_attendance.php
app/api/handlers_services.php
app/helpers/slip_helper.php
app/helpers/slip_helper_kontrak.php
app/helpers/slip_helper_harian.php
db/migrations/20261021100000_add_tipe_karyawan_to_tb_karyawan.php
db/migrations/20261021120000_create_tb_tipe_karyawan.php
db/migrations/20261021130000_add_id_tipe_karyawan_to_tb_karyawan.php
```
