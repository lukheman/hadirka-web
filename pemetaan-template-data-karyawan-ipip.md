# Pemetaan Template Data Karyawan IPIP → Database

Template: `assets/template-csv/Format_Impor_Database_Karyawan_IPIP.xlsx`
(judul baris 2–4, header baris 5, sub-header baris 6, nomor kolom baris 7,
data mulai **baris 8**, kolom **A–CQ**).
Service: `app/controller/csv-control/csv-karyawan-ipip.php`
(UI: tombol **Import IPIP** di `view/master-data/data-karyawan.php`).
Template lama (44 kolom, header baris 1) tetap via `csv-karyawan.php`.

## Pilihan sheet

Template IPIP terdiri dari beberapa sheet/halaman. Alurnya:

1. User pilih file di modal **Import IPIP** → frontend memanggil
   `app/controller/csv-control/sheets-karyawan-ipip.php`
   (POST `file_excel`) yang mengembalikan `{sheets, active}`.
2. Dropdown **Sheet / Halaman** terisi; default = sheet aktif.
3. Submit mengirim `sheet` (nama; boleh juga index angka, kosong = aktif)
   ke service import. Sheet yang tidak ditemukan → error yang
   mencantumkan daftar sheet yang tersedia.
4. Judul hasil import mencantumkan nama sheet (`Import IPIP [X] ...`).

## Alur dua tahap (pratinjau → konfirmasi → terapkan)

Database **hanya berubah** bila request `mode=apply & confirm=1`;
selain itu service berjalan dry-run (pratinjau, tanpa tulis).

1. User pilih file + sheet → **Cek Pratinjau** (dry-run).
2. Pratinjau menampilkan: karyawan baru, data berubah (rincian
   `Field: 'lama' → 'baru'`), jabatan baru (checkbox persetujuan),
   dilewati, gagal.
3. User menekan **Konfirmasi & Terapkan** → apply dengan
   `jabatan_disetujui` (yang dicentang). Jabatan tak dicentang →
   barisnya gagal dengan pesan yang jelas.

## Jabatan baru otomatis (dengan persetujuan)

- Kolom H8 dibersihkan dari tulisan Cina (`Op.Grader/平地机` →
  `OPERATOR GRADER`, huruf besar mengikuti konvensi master).
- Bila hasil bersih belum ada di `tb_jabatan`: masuk daftar
  `jabatan_baru` di pratinjau (dikelompokkan per nama + contoh
  tulisan asli + jumlah baris), barisnya `tertunda`.
- Setelah disetujui: dimasukkan ke `tb_jabatan` saat apply
  (`jabatan_ditambah`), baris diproses normal.
- Site/departemen yang tidak dikenal tetap error (tidak dibuat otomatis).

Template IPIP terdiri dari beberapa sheet/halaman. Alurnya:

1. User pilih file di modal **Import IPIP** → frontend memanggil
   `app/controller/csv-control/sheets-karyawan-ipip.php`
   (POST `file_excel`) yang mengembalikan `{sheets, active}`.
2. Dropdown **Sheet / Halaman** terisi; default = sheet aktif.
3. Submit mengirim `sheet` (nama; boleh juga index angka, kosong = aktif)
   ke service import. Sheet yang tidak ditemukan → error yang
   mencantumkan daftar sheet yang tersedia.
4. Judul hasil import mencantumkan nama sheet (`Import IPIP [X] ...`).

Prinsip: **hanya nilai yang ada di database yang diambil, database tidak dirubah**
(tanpa migrasi/ALTER; kolom tanpa padanan di tabel existing diabaikan).

## Tabel pemetaan (notasi baris 8 = baris data pertama)

| Kolom | Template IPIP | Tujuan DB / keterangan |
|---|---|---|
| A8 | No | diabaikan |
| B8 | NIK Karyawan | `tb_karyawan.nik_karyawan` (duplikat → dilewati) |
| C8 | Nama Lengkap Karyawan | `tb_karyawan.nama_lengkap` |
| D8 | Jenis Kelamin (L/P) | `tb_karyawan.jenis_kelamin` (`Laki-Laki`→L, `Perempuan`→P) |
| E8 | Perusahaan | diabaikan (badan usaha, bukan site penempatan) |
| F8 | Department | `tb_karier.id_departemen` (Cina dibuang: `LOGISTIK …`→LOGISTIK) |
| G8 | DIVISI | diabaikan (tidak ada kolom DB) |
| H8 | Jabatan Karyawan | `tb_karier.id_jabatan` (`Op.`→OPERATOR) |
| I8 | Level - Gol | diabaikan |
| J8 | Level - Gol (kolom kedua) | diabaikan |
| K8 | Level - Level | diabaikan |
| L8 | POH | diabaikan |
| M8 | Roster - Roster | diabaikan |
| N8 | Roster - Travel | diabaikan |
| O8 | Working Location | `tb_karier.id_site` (kosong = default PT. IPIP, sesuai judul file SITE IPIP) |
| P8 | Tgl Masuk Site | `tb_karier.tgl_mulai` (utama) |
| Q8 | Join Date (in IPIP) | `tb_karier.tgl_mulai` (fallback bila P8 kosong) |
| R8 | Length of Service (yy.mm) | diabaikan |
| S8 | Length of Service (yy) | diabaikan |
| T8 | LOS Tag | diabaikan |
| U8 | Payroll Base | diabaikan |
| V8 | Tempat Tanggal Lahir | `tb_karyawan.tempat_lahir` |
| W8 | Tanggal Lahir | `tb_karyawan.tgl_lahir` |
| X8 | Age (yy:mm) | diabaikan |
| Y8 | Age Tag | diabaikan |
| Z8 | Emp. Typ (TKI/TKA) | diabaikan |
| AA8 | Nationality | `tb_karyawan.nationality` |
| AB8 | Religion | `tb_karyawan.agama` (`Islam/…`→Islam) |
| AC8 | Last Education - Education Level | `tb_identitas.education_level` |
| AD8 | Last Education - Education Tag | `tb_identitas.last_education` |
| AE8 | Last Education - Major | `tb_identitas.major` |
| AF8 | Rekening Bank - Bank Account Name | `tb_identitas.atas_nama_rek` |
| AG8 | Rekening Bank - Bank Account Number | `tb_identitas.no_rek` |
| AH8 | Rekening Bank - Bank Name & Branch | `tb_identitas.nama_bank` |
| AI8 | Nama Ibu Kandung | `tb_identitas.nama_ibu` |
| AJ8 | NIK KTP | `tb_identitas.nik_ktp` |
| AK8 | DIGIT NIK KTP | diabaikan (info validasi di Excel saja) |
| AL8 | Alamat Sesuai KTP | `tb_identitas.alamat_ktp` |
| AM8 | RT/RW | `tb_identitas.rt_rw` |
| AN8 | Desa / Kelurahan | `tb_identitas.desa` |
| AO8 | Kecamatan | `tb_identitas.kecamatan` |
| AP8 | Kota / Kabupaten | `tb_identitas.kabupaten` |
| AQ8 | Provinsi | `tb_identitas.provinsi` |
| AR8 | Ring | diabaikan |
| AS8 | Personal Email | `tb_karyawan.email_pribadi` |
| AT8 | No Telp / HP Karyawan | `tb_karyawan.no_telp` (typo `O822…`→`0822…`) |
| AU8 | Emergency Number 1 - Nama | `tb_kontak_darurat` nomor_urut 1 |
| AV8 | Emergency Number 1 - Hubungan | `tb_kontak_darurat` nomor_urut 1 |
| AW8 | Emergency Number 1 - No Telp / HP | `tb_kontak_darurat` nomor_urut 1 |
| AX8 | Emergency Number 2 - Nama | `tb_kontak_darurat` nomor_urut 2 |
| AY8 | Emergency Number 2 - Hubungan | `tb_kontak_darurat` nomor_urut 2 |
| AZ8 | Emergency Number 2 - No Telp / HP | `tb_kontak_darurat` nomor_urut 2 |
| BA8 | Jenis Hubungan Kerja | diabaikan (mis. CONTRACT, tanpa kolom DB) |
| BB8 | Status Karyawan | diabaikan untuk status (berisi jenis kontrak mis. CONTRACT, bukan enum status); status diambil dari CJ8/CF8/CI8 |
| BC8 | Nomor NPWP | `tb_identitas.npwp` |
| BD8 | Nomor BPJS TK | `tb_identitas.bpjs_tk` |
| BE8 | Nomor BPJS KES | `tb_identitas.bpjs_kes` |
| BF8 | Status Pernikahan | `tb_karyawan.status_pernikahan` |
| BG8 | No Kartu Keluarga | `tb_identitas.kk_number` |
| BH8 | Keluarga - Nama Suami / Istri | diabaikan (tanpa kolom DB) |
| BI8 | Keluarga - L/P (Suami / Istri) | diabaikan |
| BJ8 | Keluarga - Tempat Lahir Suami / Istri | diabaikan |
| BK8 | Keluarga - Tanggal Lahir Suami / Istri | diabaikan |
| BL8–BO8 | Keluarga - Anak 1 (nama, L/P, tempat & tgl lahir) | diabaikan |
| BP8–BS8 | Keluarga - Anak 2 | diabaikan |
| BT8–BW8 | Keluarga - Anak 3 | diabaikan |
| BX8–CA8 | Keluarga - Anak 4 | diabaikan |
| CB8–CE8 | Keluarga - Anak 5 | diabaikan |
| CF8 | Non Aktif - Exit Date | `tb_tereliminasi.tgl_keluar` |
| CG8 | Non Aktif - Bulan Keluar | fallback tgl_keluar (`YYYY-MM-01` bersama CH8) bila CF8 kosong |
| CH8 | Non Aktif - Tahun Keluar | lihat CG8 |
| CI8 | Non Aktif - Exit Reason | jenis keluar (`RESIGN`→Resign) + bagian `alasan_keluar` |
| CJ8 | Remark Aktif / Non Aktif | penentu `status_karyawan` Aktif vs Non Aktif |
| CK8 | Personal File | diabaikan (tanpa kolom DB) |
| CL8 | Keterangan | tambahan `alasan_keluar` (`CI8 \| CL8`) |
| CM8 | (tanpa header) | diabaikan |
| CN8 | (tanpa header) | diabaikan |
| CO8 | Ring - Ring 1 | diabaikan |
| CP8 | Ring - Ring 2 | diabaikan |
| CQ8 | Ring - Ring 3 | diabaikan |
| — | Status PTKP | `''` (tidak ada di template IPIP) |
| — | Tanggal selesai kontrak | `NULL` (tidak ada di template IPIP) |

## Aturan status

- `CJ8 = Aktif` + tanpa data exit → `status_karyawan = Aktif`.
- `CJ8 = Non Aktif` **atau** CF8/CI8 terisi → Non Aktif:
  `CI8` dipetakan ke `Resign / Habis Kontrak / PHK / Pensiun / Black List`
  (tak dikenal → default `Resign`), `tgl_keluar` wajib ada,
  baris ditulis ke `tb_tereliminasi`.

## Normalisasi sebelum import (spasi & simbol dibuang)

- Teks bebas: trim + spasi ganda/tab/newline → satu spasi.
- NIK Karyawan, RT/RW: seluruh whitespace dibuang (strip NIK dipertahankan).
- NIK KTP, No KK, NPWP, BPJS TK/KES, No Rekening: digit murni.
- Telepon: buang spasi/koma/titik/strip/kurung, `+62` → `0`,
  `O822…` → `0822…`, `812…` → `0812…`.
- Email: buang spasi + huruf kecil.
- Baca sel Excel mentah (tanpa format tampilan) agar format ribuan
  (`81,234…`) tidak merusak nomor; kolom ID/telepon di file template
  diformat Teks.

## Aturan update (NIK sudah terdaftar)

- Baris dibandingkan field per field dengan data existing.
- Sama semua → `Dilewati` ("tidak ada perubahan").
- Ada beda → `UPDATE` kolom yang berubah + entri `updated`
  (`NIK — Nama (N perubahan)` + rincian `Field: 'lama' → 'baru'`),
  tampil di modal Hasil Import IPIP (kartu **Diupdate**).
- Sel template yang **kosong = pertahankan data lama**
  (tidak menghapus; mis. bank kosong tidak menghapus rekening existing).
- Master (site/dept/jabatan) dibandingkan berdasarkan **nama**,
  bukan id (master mengandung duplikat, mis. LOGISTIK id 2 & 7);
  lookup memakai id terkecil agar konsisten.
- `status_ptkp`, `password`, `foto`, `tgl_selesai` kontrak tidak
  disentuh (tidak ada di template / bukan wewenang import).

## Normalisasi khusus IPIP (tanpa rubah DB master)

- Karakter Cina dibuang sebelum pencocokan master;
  segmen latin sebelum `/` dipakai (`Op.Excavator/…`→`Op.Excavator`).
  Termasuk tanda baca CJK (、。，〈〉 dsb.), fullwidth (：，．),
  dan simbol sisa di ujung nama.
- Singkatan jabatan `Op./Opr.` → `OPERATOR`.
- Site tidak dibuat otomatis: tidak cocok → baris gagal dengan pesan
  yang menyebut nilai E8/O8, F8, H8 yang bermasalah.
- `Excel::parseDateFlexible()` mendukung `15-Feb-25`, `19-Apr-99`
  (tgl–bulan teks–tahun 2 digit).
