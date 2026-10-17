<?php
require_once __DIR__ . '/../../config/koneksi.php';


$q = $koneksi->query("SELECT * FROM tb_site ORDER BY id_site DESC");
$no = 1;

while ($r = $q->fetch()) {
    $nama = htmlspecialchars((string)$r['nama_site'], ENT_QUOTES);
    $lokasi = htmlspecialchars((string)$r['lokasi'], ENT_QUOTES);
    $kode = htmlspecialchars((string)($r['kode_lokasi'] ?? ''), ENT_QUOTES);
    $deskripsi = htmlspecialchars((string)($r['deskripsi_lokasi'] ?? ''), ENT_QUOTES);
    $radius = $r['radius_meter'] !== null ? (float)$r['radius_meter'] : 80;
    $aktif = (int)($r['is_aktif'] ?? 1);

    $latAttr = $r['latitude'] !== null ? (string)$r['latitude'] : '';
    $lngAttr = $r['longitude'] !== null ? (string)$r['longitude'] : '';
    $geoAttr = $r['latitude'] !== null && $r['longitude'] !== null ? '1' : '0';

    // Titik geofence (dipakai aplikasi absen mobile)
    if ($r['latitude'] !== null && $r['longitude'] !== null) {
        $geo = '<span class="badge bg-label-success"><i class="bx bx-current-location me-1"></i>'
            . number_format((float)$r['latitude'], 6, '.', '') . ', '
            . number_format((float)$r['longitude'], 6, '.', '') . '</span>'
            . '<br><small class="text-muted">Radius ' . number_format($radius, 0, ',', '.') . ' m</small>';
    } else {
        $geo = '<span class="badge bg-label-warning"><i class="bx bx-error-circle me-1"></i> Belum dipetakan</span>'
            . '<br><small class="text-muted">Tidak dikirim ke aplikasi</small>';
    }

    $badgeAktif = $aktif === 1
        ? '<span class="badge bg-label-primary">Aktif</span>'
        : '<span class="badge bg-label-secondary">Nonaktif</span>';

    echo "
    <tr>
        <td class='text-center'>{$no}</td>
        <td>{$nama}</td>
        <td>" . ($kode !== '' ? $kode : '<span class="text-muted">-</span>') . "</td>
        <td>{$lokasi}</td>
        <td>{$geo}</td>
        <td class='text-center'>{$badgeAktif}</td>
        <td class='text-center'>
            <button class='btn btn-sm btn-warning btnEdit'
                data-id='{$r['id_site']}'
                data-nama='{$nama}'
                data-lokasi='{$lokasi}'
                data-kode='{$kode}'
                data-lat='{$latAttr}'
                data-lng='{$lngAttr}'
                data-geo='{$geoAttr}'
                data-radius='{$radius}'
                data-deskripsi='{$deskripsi}'
                data-aktif='{$aktif}'>Edit</button>

            <button class='btn btn-sm btn-danger btnDelete'
                data-id='{$r['id_site']}'>Delete</button>
        </td>
    </tr>";
    $no++;
}
