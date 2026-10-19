# IPM HRIS — Color Palette

Dokumen ini mendefinisikan warna resmi UI IPM HRIS. Sumber implementasi utama:

```text
assets/css/ipm-theme.css
```

File tema dimuat setelah stylesheet vendor Sneat/Bootstrap sehingga token IPM
menimpa warna bawaan template tanpa mengubah file vendor.

## Prinsip warna

- **Oranye** menjadi warna primary: aksi utama, link, state aktif, fokus, dan
  highlight.
- **Biru** menjadi warna sekunder: warning, info, navigasi pendukung, dan
  elemen yang perlu dibedakan dari primary.
- **Abu-abu** menjadi warna background dan teks sekunder.
- Warna status dipakai konsisten untuk kondisi data, bukan untuk dekorasi.

## Token warna

### IPM Blue

| Token CSS | Hex | RGB | Penggunaan |
|---|---|---|---|
| `--ipm-blue` | `#1D5C96` | `29, 92, 150` | Sekunder, warning, tombol biru |
| `--ipm-blue-light` | `#2E7BB8` | `46, 123, 184` | Info, border, aksen terang |
| `--ipm-blue-dark` | `#123F68` | `18, 63, 104` | Hover biru, teks kontras |
| `--ipm-blue-soft` | `#CFE3F2` | `207, 227, 242` | Background label dan alert |

### IPM Orange

| Token CSS | Hex | RGB | Penggunaan |
|---|---|---|---|
| `--ipm-orange` | `#F2921D` | `242, 146, 29` | Primary, aksi utama, link |
| `--ipm-orange-light` | `#F7A94A` | `247, 169, 74` | Border dan highlight |
| `--ipm-orange-dark` | `#D97706` | `217, 119, 6` | Hover, teks primary, kontras |
| `--ipm-orange-soft` | `#FDE6C6` | `253, 230, 198` | Background label dan alert |

### Netral

| Token CSS | Hex | RGB | Penggunaan |
|---|---|---|---|
| `--ipm-bg` | `#F5F5F5` | `245, 245, 245` | Background halaman |
| `--ipm-muted` | `#8C8C8C` | `140, 140, 140` | Teks sekunder |
| `--ipm-ink` | `#1A1A1A` | `26, 26, 26` | Teks utama dan heading |

## Token Bootstrap yang dioverride

```css
:root {
  --bs-primary: #F2921D;
  --bs-primary-rgb: 242, 146, 29;
  --bs-link-color: #F2921D;
  --bs-link-hover-color: #D97706;
  --bs-warning: #1D5C96;
  --bs-warning-rgb: 29, 92, 150;
}
```

Konsekuensi komponen:

| Komponen | Warna normal | Warna hover/fokus |
|---|---|---|
| `.btn-primary` | `#F2921D` | `#D97706` |
| `.btn-warning` | `#1D5C96` | `#123F68` |
| `.bg-primary` | `#F2921D` | `#D97706` untuk link |
| `.bg-warning` | `#1D5C96` | — |
| Link | `#F2921D` | `#D97706` |
| Menu aktif | Oranye transparan | Oranye lebih kuat |
| Progress bar | `#F2921D` | — |
| Form focus | Border `#F2921D` | Shadow oranye |

## Warna status data

| Status | Hex | Contoh penggunaan |
|---|---|---|
| Success / Masuk | `#71DD37` | Berhasil, hadir, aktif |
| Danger / Mangkir | `#FF3E1D` | Error, ditolak, mangkir |
| Warning / SPL | `#FFAB00` | Perlu perhatian, SPL |
| Info / Sakit | `#03C3EC` | Informasi, sakit |
| Secondary / OFF | `#8592A3` | Tidak aktif, OFF, status netral |
| Izin | `#696CFF` | Status izin pada kalender lama |
| Cuti | `#6F42C1` | Status cuti |

Background status kalender:

| Status | Background | Border/status |
|---|---|---|
| Masuk | `#E8FADF` | `#71DD37` |
| SPL | `#FFF2D6` | `#FFAB00` |
| OFF | `#F1F2F4` | `#D9DEE3` |
| Izin | `#E7E7FF` | `#696CFF` |
| Sakit | `#D7F5FC` | `#03C3EC` |
| Cuti | `#EDE4F7` | `#6F42C1` |
| Mangkir | `#FFE0DB` | `#FF3E1D` |
| Resign/New | `#E9EAEC` | `#8592A3` |

## Contoh penggunaan CSS

Gunakan token, bukan hex berulang:

```css
.custom-action {
  background: var(--ipm-orange);
  border-color: var(--ipm-orange);
  color: #fff;
}

.custom-action:hover {
  background: var(--ipm-orange-dark);
  border-color: var(--ipm-orange-dark);
}

.custom-info {
  background: var(--ipm-blue-soft);
  border-color: var(--ipm-blue-light);
  color: var(--ipm-blue-dark);
}
```

Untuk transparansi, gunakan RGB token:

```css
.custom-active {
  background: rgba(242, 146, 29, 0.16);
}
```

## Aturan penggunaan

1. Pakai `--ipm-orange` untuk aksi utama dan state aktif.
2. Pakai `--ipm-blue` untuk warning/info yang harus berbeda dari primary.
3. Pakai warna soft untuk background, bukan warna solid gelap.
4. Pakai warna dark untuk teks di atas background soft.
5. Jangan mengubah file di `assets/vendor/` untuk mengganti palette.
6. Tambahkan token baru ke `assets/css/ipm-theme.css` bila warna dipakai lebih
   dari satu komponen.
7. Jangan memakai warna status untuk tombol aksi jika makna status tidak sesuai.

## Aksesibilitas

- Teks utama memakai `--ipm-ink` di atas `--ipm-bg`.
- Teks di atas primary orange harus putih atau warna gelap yang sudah diuji.
- Jangan menyampaikan status hanya dengan warna; sertakan teks, ikon, atau
  label.
- Uji kontras untuk kombinasi baru sebelum dipakai di produksi.

## Palette vendor

`assets/vendor/css/theme-default.css` masih berisi palette bawaan Sneat,
termasuk primary `#696CFF`. Palette tersebut bukan palette resmi IPM HRIS.
`assets/css/ipm-theme.css` harus tetap dimuat setelah stylesheet vendor agar
override tetap berlaku.

## File terkait

| File | Fungsi |
|---|---|
| `assets/css/ipm-theme.css` | Implementasi palette resmi IPM |
| `assets/vendor/css/theme-default.css` | Palette bawaan vendor, jangan edit |
| `index.php` | Urutan pemuatan stylesheet dan warna JavaScript global |
| `slipgajinew.php` | Palette khusus halaman slip/cetak |
