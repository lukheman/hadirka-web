# API Slip Gaji Mobile

Endpoint ini dipakai aplikasi mobile untuk membaca hasil perhitungan slip gaji
karyawan. Perhitungan memakai helper payroll yang sama dengan slip web.
Endpoint bersifat **read-only**.

## Base URL

```text
https://alamat-server/ipm-hris/api
```

Contoh lokal:

```text
http://localhost:8000/api
```

## Endpoint

```http
GET /payslip
```

URL lengkap:

```http
GET https://alamat-server/ipm-hris/api/payslip
```

## Header

```http
Accept: application/json
Authorization: Bearer <token>
```

Token didapat dari endpoint `POST /auth/login`.

> Implementasi API saat ini membuat token saat login, tetapi validasi token masih
> mengikuti sistem lama dan belum memeriksa token pada setiap request.

## Parameter Query

| Parameter | Wajib | Keterangan |
|---|---:|---|
| `username` | Ya | NIK karyawan, contoh `IPM-00001`. ID karyawan numerik juga diterima. |
| `periode` | Tidak | ID periode atau rentang `YYYY-MM-DD - YYYY-MM-DD`. Jika kosong, semua periode payroll. |

## Contoh Request

### Semua slip karyawan

```bash
curl -X GET \
  'https://alamat-server/ipm-hris/api/payslip?username=IPM-00001' \
  -H 'Accept: application/json' \
  -H 'Authorization: Bearer token_karyawan_xxxxxxxxxxxxxxxx'
```

### Slip berdasarkan periode

```bash
curl -X GET \
  'https://alamat-server/ipm-hris/api/payslip?username=IPM-00001&periode=2026-10-01%20-%202026-10-31' \
  -H 'Accept: application/json' \
  -H 'Authorization: Bearer token_karyawan_xxxxxxxxxxxxxxxx'
```

## Response Berhasil

HTTP status:

```http
200 OK
```

Response:

```json
{
  "success": true,
  "data": [
    {
      "id": 12,
      "periode": "2026-10-01 - 2026-10-31",
      "gajiPokok": 5000000,
      "tunjangan": 500000,
      "potongan": 100000,
      "totalDiterima": 5400000,
      "status": "TERSEDIA",
      "createdAt": null,
      "detail": {
        "nik": "IPM-00001",
        "nama": "Budi Santoso",
        "basic": 5000000,
        "total_nilai_kehadiran": 22,
        "total_pendapatan": 110000000,
        "total_potongan": 100000,
        "gaji_bersih": 109900000
      }
    }
  ]
}
```

## Response Tanpa Data

Jika karyawan belum memiliki slip gaji atau filter periode tidak menemukan data,
API tetap mengembalikan HTTP `200` dengan array kosong.

```json
{
  "success": true,
  "data": []
}
```

## Response Error

### Username tidak dikirim

HTTP status:

```http
400 Bad Request
```

```json
{
  "success": false,
  "message": "Username / NIK wajib diisi."
}
```

### Karyawan tidak ditemukan

HTTP status:

```http
404 Not Found
```

```json
{
  "success": false,
  "message": "Karyawan tidak ditemukan."
}
```

### Endpoint atau method tidak sesuai

Request `POST`, `PUT`, atau `DELETE` ke endpoint ini tidak didukung.

HTTP status:

```http
404 Not Found
```

```json
{
  "success": false,
  "message": "Endpoint tidak ditemukan: POST /payslip"
}
```

## Struktur Data

| Field | Tipe | Keterangan |
|---|---|---|
| `id` | integer | ID periode payroll pada `tb_priode`. |
| `periode` | string | Periode slip, contoh `Oktober 2026`. |
| `gajiPokok` | number | Nilai gaji pokok. |
| `tunjangan` | number | Total tunjangan. |
| `potongan` | number | Total potongan. |
| `totalDiterima` | number | Gaji bersih yang diterima. |
| `status` | string | Status rilis slip, saat ini biasanya `DITERBITKAN`. |
| `createdAt` | string/null | Waktu pembuatan dalam format ISO 8601. |

## Sumber Data dan Batasan

- Data dihitung dari `tb_priode`, `tb_karyawan`, `tb_karier`, `tb_absen`, dan data potongan.
- Data payroll utama tidak dihapus atau diubah endpoint ini.
- Endpoint tidak menyimpan hasil perhitungan sebagai slip baru.
- Endpoint tidak mengubah nominal gaji.
- Hasil hanya berisi slip untuk karyawan pada parameter `username`.
- Urutan data terbaru berdasarkan tanggal mulai periode.
