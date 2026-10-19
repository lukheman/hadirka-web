# API Pengajuan Izin, Cuti, dan SKS

Semua pengajuan memakai endpoint `/leave`. Server PHP menerima JSON melalui
`app/api/index.php` dan menyimpan data ke tabel website.

## Base URL dan format

```text
http://HOST:8000/api
```

Header:

```http
Content-Type: application/json
Authorization: Bearer <token>
```

Respons sukses:

```json
{"success":true,"data":[]}
```

Respons gagal:

```json
{"success":false,"message":"Pesan kesalahan"}
```

Token dibuat saat login. Saat ini token Bearer belum divalidasi, mengikuti
kompatibilitas API lama.

## Jenis pengajuan

| `jenis` | Tabel | Keterangan |
|---|---|---|
| `Izin` | `tb_pengajuan_izin` | Izin biasa |
| `Cuti` | `tb_pengajuan_cuti` | Cuti dengan tanggal awal dan akhir |
| `SKS` atau `Sakit` | `tb_pengajuan_izin` | Surat Keterangan Sakit; disimpan dengan prefix internal `[SKS]` |

## GET `/leave`

Mengambil daftar pengajuan izin, cuti, dan SKS.

```http
GET /leave?username=IPM-00002
```

Parameter `username` opsional. Nilai dapat berupa NIK karyawan atau
`id_karyawan` numerik. Tanpa parameter, API mengembalikan seluruh data.

Contoh:

```bash
curl 'http://192.168.1.10:8000/api/leave?username=IPM-00002' \
  -H 'Authorization: Bearer <token>'
```

Response:

```json
{
  "success": true,
  "data": [
    {
      "id": 12,
      "sumber": "izin",
      "nik": "IPM-00002",
      "username": "IPM-00002",
      "nama": "ZAENAL",
      "jenis": "Izin",
      "tanggal": "18/10/2026",
      "alasan": "Keperluan keluarga",
      "status": "MENUNGGU",
      "lampiranPath": "uploads/izin/izin_12.jpg",
      "createdAt": "2026-10-18T11:40:00"
    }
  ]
}
```

Field response:

| Field | Tipe | Keterangan |
|---|---|---|
| `id` | integer | ID pengajuan. ID izin dan cuti berasal dari tabel berbeda. |
| `sumber` | string | `izin` atau `cuti`. |
| `nik`, `username` | string | Identitas karyawan. |
| `nama` | string | Nama karyawan. |
| `jenis` | string | `Izin`, `Cuti`, atau `SKS (Surat Keterangan Sakit)`. |
| `tanggal` | string | `dd/mm/yyyy` atau `dd/mm/yyyy - dd/mm/yyyy`. |
| `alasan` | string | Alasan tanpa prefix `[SKS]`. |
| `status` | string | `MENUNGGU`, `DISETUJUI`, atau `DITOLAK`. |
| `lampiranPath` | string/null | Path lampiran. |
| `createdAt` | string/null | Format `Y-m-dTH:i:s`. |

## POST `/leave`

Membuat pengajuan baru.

Request body:

| Field | Wajib | Tipe | Keterangan |
|---|---:|---|---|
| `username` | Ya | string | NIK atau ID karyawan. |
| `jenis` | Ya | string | `Izin`, `Cuti`, `SKS`, atau `Sakit`. Default `Izin`. |
| `tanggal` | Ya | string | `dd/mm/yyyy` atau `dd/mm/yyyy - dd/mm/yyyy`. |
| `alasan` | Tidak | string | Alasan. Kosong disimpan sebagai `-`. |
| `lampiranPath` | Tidak | string/null | Path file yang sudah tersedia di server. |

Tanggal tunggal:

```text
18/10/2026
```

Tanggal rentang:

```text
20/10/2026 - 22/10/2026
```

Untuk `Cuti`, `lama_cuti` dihitung otomatis dan mencakup tanggal awal serta
tanggal akhir. Untuk `Izin` dan `SKS`, tanggal akhir ditambahkan ke alasan.

### Contoh Izin

```bash
curl -X POST 'http://192.168.1.10:8000/api/leave' \
  -H 'Content-Type: application/json' \
  -H 'Authorization: Bearer <token>' \
  -d '{
    "username":"IPM-00002",
    "jenis":"Izin",
    "tanggal":"18/10/2026",
    "alasan":"Keperluan keluarga",
    "lampiranPath":"uploads/izin/izin_20261018.jpg"
  }'
```

### Contoh Cuti

```json
{
  "username": "IPM-00002",
  "jenis": "Cuti",
  "tanggal": "20/10/2026 - 22/10/2026",
  "alasan": "Keperluan pribadi",
  "lampiranPath": null
}
```

### Contoh SKS

```json
{
  "username": "IPM-00002",
  "jenis": "SKS",
  "tanggal": "19/10/2026",
  "alasan": "Demam",
  "lampiranPath": "uploads/izin/surat_sakit_20261019.jpg"
}
```

Response create:

```json
{
  "success": true,
  "message": "Pengajuan cuti/izin/sakit berhasil disimpan."
}
```

`lampiranPath` bukan upload multipart. API hanya menerima path string. Path
lokal Android seperti `/data/user/0/.../foto.jpg` diabaikan dan disimpan sebagai
`null`. Upload file dari perangkat perlu endpoint upload terpisah.

## PUT `/leave/{id}/status`

Mengubah status pengajuan. Persetujuan normal dilakukan HRD melalui website.

```http
PUT /leave/13/status
```

Body:

```json
{
  "status": "DISETUJUI",
  "sumber": "cuti"
}
```

| Field | Wajib | Nilai |
|---|---:|---|
| `status` | Ya | `MENUNGGU`, `PENDING`, `DISETUJUI`, `DITERIMA`, `DITOLAK`, `DIBATALKAN`, `DICAIRKAN`. |
| `sumber` | Disarankan | `izin` atau `cuti`. |

`sumber` perlu dikirim karena ID pengajuan izin dan cuti dapat sama.

Pemetaan status:

| Aplikasi | Database |
|---|---|
| `MENUNGGU`, `PENDING` | `pending` |
| `DISETUJUI`, `DITERIMA` | `disetujui` |
| `DITOLAK`, `DIBATALKAN` | `ditolak` |
| `DICAIRKAN` | `dicairkan` |

Contoh:

```bash
curl -X PUT 'http://192.168.1.10:8000/api/leave/13/status' \
  -H 'Content-Type: application/json' \
  -H 'Authorization: Bearer <token>' \
  -d '{"status":"DISETUJUI","sumber":"cuti"}'
```

Response:

```json
{
  "success": true,
  "message": "Status pengajuan cuti (ID: 13) berhasil diubah menjadi DISETUJUI."
}
```

## Error HTTP

| HTTP | Kondisi |
|---:|---|
| `400` | Data kurang atau format tanggal salah. |
| `404` | Karyawan atau pengajuan tidak ditemukan. |
| `200` | Berhasil. |
| `500` | Kesalahan server/database. |

Contoh:

```json
{
  "success": false,
  "message": "Format tanggal tidak dikenali. Gunakan format dd/mm/yyyy."
}
```

## File server

| Komponen | File |
|---|---|
| Routing | `app/api/index.php` |
| Handler | `app/api/handlers_services.php` |
| Helper | `app/api/helpers.php` |
| Izin/SKS | `tb_pengajuan_izin` |
| Cuti | `tb_pengajuan_cuti` |
