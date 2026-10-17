<?php
require_once __DIR__ . '/../../config/koneksi.php';

/**
 * Baris tabel HTML notifikasi aplikasi (join nama karyawan penerima).
 * Filter: kategori (presensi|penggajian|pengajuan|pengumuman).
 */

$kategori = $_GET['kategori'] ?? '';

$where = "";
$params = [];

if ($kategori !== '') {
    $where = "WHERE n.kategori = ?";
    $params[] = $kategori;
}

$sql = "SELECT n.*, k.nama_lengkap, k.nik_karyawan
        FROM tb_notifikasi n
        JOIN tb_karyawan k ON k.id_karyawan = n.id_karyawan
        $where
        ORDER BY n.dibuat_pada DESC, n.id_notifikasi DESC
        LIMIT 300";

$stmt = $koneksi->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

if (count($rows) === 0) {
    echo '<tr><td colspan="6" class="text-center py-5 text-muted">Belum ada notifikasi terkirim.</td></tr>';
    exit;
}

foreach ($rows as $row) {
    $judul = htmlspecialchars((string)$row['judul'], ENT_QUOTES);
    $pesan = htmlspecialchars((string)$row['pesan'], ENT_QUOTES);
    $nama = htmlspecialchars((string)$row['nama_lengkap'], ENT_QUOTES);
    $nik = htmlspecialchars((string)$row['nik_karyawan'], ENT_QUOTES);
    $inisial = strtoupper(substr((string)$row['nama_lengkap'], 0, 1));
    $kategoriRow = (string)$row['kategori'];

    $kategori_conf = [
        'presensi'   => ['color' => 'primary', 'label' => 'Presensi'],
        'penggajian' => ['color' => 'success', 'label' => 'Penggajian'],
        'pengajuan'  => ['color' => 'warning', 'label' => 'Pengajuan'],
        'pengumuman' => ['color' => 'info', 'label' => 'Pengumuman'],
    ];
    $conf = $kategori_conf[$kategoriRow] ?? ['color' => 'secondary', 'label' => ucfirst($kategoriRow)];

    $badgeBaca = ((int)$row['is_dibaca'] === 1)
        ? '<span class="badge bg-label-success">Dibaca</span>'
        : '<span class="badge bg-label-secondary">Belum dibaca</span>';

    echo '<tr>
        <td class="text-nowrap">' . date('d/m/Y H:i', strtotime((string)$row['dibuat_pada'])) . '</td>
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
        <td>
            <div class="d-flex flex-column" style="max-width: 320px;">
                <span class="fw-semibold text-dark">' . $judul . '</span>
                <small class="text-muted text-wrap" style="white-space: normal;">' . $pesan . '</small>
            </div>
        </td>
        <td><span class="badge bg-label-' . $conf['color'] . '">' . $conf['label'] . '</span></td>
        <td>' . $badgeBaca . '</td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-danger" onclick="hapusNotifikasi(' . (int)$row['id_notifikasi'] . ')" title="Hapus">
                <i class="bx bx-trash"></i>
            </button>
        </td>
    </tr>';
}
