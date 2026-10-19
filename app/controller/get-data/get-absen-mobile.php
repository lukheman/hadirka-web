<?php
require_once __DIR__ . '/../../config/koneksi.php';

/**
 * Tabel baris HTML log absensi aplikasi mobile (GPS + selfie).
 *
 * Sejak data absen disatukan, log aplikasi tersimpan di tabel utama tb_absen
 * dengan sumber='mobile' (lihat docs/INTEGRASI-ABSEN-MOBILE.md).
 * Filter: start_date, end_date, site (id_lokasi), status
 * (tepat | terlambat | selesai).
 */

$start  = $_GET['start_date'] ?? date('Y-m-01');
$end    = $_GET['end_date'] ?? date('Y-m-d');
$site   = $_GET['site'] ?? '';
$status = $_GET['status'] ?? '';

$where = ["a.sumber = 'mobile'", 'a.tgl_absen BETWEEN ? AND ?'];
$params = [$start, $end];

if ($site !== '') {
    $where[] = 'a.id_lokasi = ?';
    $params[] = (int)$site;
}

if ($status === 'tepat') {
    $where[] = "a.status_kehadiran LIKE 'Tepat%'";
} elseif ($status === 'terlambat') {
    $where[] = "a.status_kehadiran LIKE 'Terlambat%'";
} elseif ($status === 'selesai') {
    $where[] = "a.status_kehadiran LIKE 'Sudah Selesai%'";
}

$where_sql = implode(' AND ', $where);

$sql = "SELECT a.*, k.nama_lengkap, k.nik_karyawan
        FROM tb_absen a
        JOIN tb_karyawan k ON k.id_karyawan = a.id_karyawan
        WHERE $where_sql
        ORDER BY a.tgl_absen DESC, a.dibuat_pada DESC";

$stmt = $koneksi->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

if (count($rows) === 0) {
    echo '<tr><td colspan="9" class="text-center py-5 text-muted">Belum ada log absensi aplikasi pada periode/filter ini.</td></tr>';
    exit;
}

$no = 1;

foreach ($rows as $row) {
    $nama = htmlspecialchars((string)$row['nama_lengkap'], ENT_QUOTES);
    $nik = htmlspecialchars((string)$row['nik_karyawan'], ENT_QUOTES);
    $inisial = strtoupper(substr((string)$row['nama_lengkap'], 0, 1));
    $lokasi = htmlspecialchars((string)$row['nama_lokasi'], ENT_QUOTES);

    // --- Badge status ---
    $status_kehadiran = (string)$row['status_kehadiran'];
    if (stripos($status_kehadiran, 'Terlambat') === 0) {
        $conf = ['color' => 'warning', 'icon' => 'bx-time-five'];
    } elseif (stripos($status_kehadiran, 'Sudah Selesai') === 0) {
        $conf = ['color' => 'primary', 'icon' => 'bx-check-double'];
    } else {
        $conf = ['color' => 'success', 'icon' => 'bx-check-circle'];
    }

    // --- Jam ---
    $masuk = htmlspecialchars((string)$row['waktu_masuk'], ENT_QUOTES);
    $keluar = !empty($row['waktu_keluar'])
        ? htmlspecialchars((string)$row['waktu_keluar'], ENT_QUOTES)
        : '<span class="badge bg-label-secondary"><i class="bx bx-minus"></i> Belum</span>';

    $metodeCheckout = strtolower(trim((string)($row['metode_checkout'] ?? '')));
    if ($metodeCheckout === 'manual') {
        $metodeCheckoutCell = '<span class="badge bg-label-info"><i class="bx bx-camera me-1"></i> Manual</span>';
    } elseif ($metodeCheckout === 'otomatis') {
        $metodeCheckoutCell = '<span class="badge bg-label-warning"><i class="bx bx-map-pin me-1"></i> Otomatis</span>';
    } else {
        $metodeCheckoutCell = '<span class="badge bg-label-secondary"><i class="bx bx-minus"></i> -</span>';
    }

    // --- Lokasi + peta ---
    $lat = (float)$row['latitude'];
    $lng = (float)$row['longitude'];
    $jarak = number_format((float)$row['jarak_meter'], 0, ',', '.') . ' m';
    $mapLink = ($lat != 0.0 || $lng != 0.0)
        ? ' <a href="https://maps.google.com/?q=' . $lat . ',' . $lng . '" target="_blank" title="Lihat di peta"><i class="bx bx-map"></i></a>'
        : '';

    // --- Foto selfie (path relatif dari root: uploads/absensi/...) ---
    $fotoCell = '<span class="badge bg-label-secondary"><i class="bx bx-minus"></i></span>';
    if (!empty($row['foto_selfie_path'])) {
        $pathRel = (string)$row['foto_selfie_path'];
        // Path DB relatif dari root publik: uploads/absensi/...
        // File fisik tersimpan di app/uploads/absensi/...
        $pathFs = __DIR__ . '/../../../app/' . ltrim($pathRel, '/');

        if (is_file($pathFs)) {
            $src = 'app/' . htmlspecialchars($pathRel, ENT_QUOTES);
            $fotoCell = '<div class="preview-thumbnail shadow-sm border rounded p-1 d-inline-block" onclick="previewSelfie(\'' . $src . '\')">
                    <img src="' . $src . '" class="rounded" style="width:38px; height:38px; object-fit:cover; cursor:zoom-in;">
                </div>';
        } else {
            $fotoCell = '<span class="badge bg-label-warning" title="' . htmlspecialchars($pathRel, ENT_QUOTES) . '"><i class="bx bx-image"></i> File tidak ada</span>';
        }
    }

    echo '<tr>
        <td class="text-center">' . $no . '</td>
        <td>
            <div class="d-flex align-items-center">
                <div class="avatar avatar-sm me-3">
                    <span class="avatar-initial rounded-circle bg-label-primary">' . $inisial . '</span>
                </div>
                <div class="d-flex flex-column">
                    <span class="fw-bold text-dark">' . $nama . '</span>
                    <small class="text-muted">' . $nik . '</small>
                </div>
            </div>
        </td>
        <td>' . date('d M Y', strtotime((string)$row['tgl_absen'])) . '</td>
        <td><span class="fw-semibold text-dark">' . $masuk . '</span></td>
        <td>' . $keluar . '</td>
        <td>' . $metodeCheckoutCell . '</td>
        <td>
            <div class="d-flex flex-column">
                <span class="text-dark">' . $lokasi . $mapLink . '</span>
                <small class="text-muted">&plusmn; ' . $jarak . ' dari titik site</small>
            </div>
        </td>
        <td>
            <span class="badge bg-label-' . $conf['color'] . ' d-inline-flex align-items-center justify-content-center rounded-pill">
                <i class="bx ' . $conf['icon'] . ' me-1"></i> ' . htmlspecialchars($status_kehadiran, ENT_QUOTES) . '
            </span>
        </td>
        <td class="text-center">' . $fotoCell . '</td>
    </tr>';

    $no++;
}
