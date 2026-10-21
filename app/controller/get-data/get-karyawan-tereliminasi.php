<?php

/**
 * Get Karyawan Tereliminasi untuk DataTables
 * ==========================================
 * Menampilkan HANYA karyawan dengan status != 'Aktif'
 * (Habis Kontrak, Pensiun, PHK, Resign, Black List)
 * beserta data eliminasi dari tb_tereliminasi + m_jenis_keluar.
 */

header("Content-Type: application/json");
error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../../config/function.php';

try {
    $filter_site   = isset($_POST['site']) ? $_POST['site'] : '';
    $filter_jabatan = isset($_POST['jabatan']) ? $_POST['jabatan'] : '';
    $filter_jenis  = isset($_POST['jenis']) ? $_POST['jenis'] : '';
    $filter_bulan  = isset($_POST['bulan']) ? $_POST['bulan'] : '';

    $where = [];
    $params = [];

    // Wajib: hanya yang sudah dieliminasi
    $allowed_status = ['Habis Kontrak', 'Pensiun', 'PHK', 'Resign', 'Black List'];
    if (!empty($filter_jenis) && in_array($filter_jenis, $allowed_status, true)) {
        $where[] = "kar.status_karyawan = ?";
        $params[] = $filter_jenis;
    } else {
        $where[] = "kar.status_karyawan != 'Aktif'";
    }

    if (!empty($filter_site)) {
        $where[] = "k.id_site = ?";
        $params[] = $filter_site;
    }

    if (!empty($filter_jabatan)) {
        $where[] = "k.id_jabatan = ?";
        $params[] = $filter_jabatan;
    }

    if (!empty($filter_bulan)) {
        $where[] = "MONTH(te.tgl_keluar) = ?";
        $params[] = $filter_bulan;
        $where[] = "YEAR(te.tgl_keluar) = YEAR(CURDATE())";
    }

    $where_sql = implode(" AND ", $where);

    $query = "SELECT
            kar.id_karyawan AS kar_id_karyawan,
            kar.*,
            i.*,
            k.*,
            s.nama_site,
            d.nama_departemen,
            j.nama_jabatan,
            te.tgl_keluar, te.alasan_keluar, te.id_tereliminasi,
            jk.nama_kategori AS jenis_keluar,
            kon1.nama_kontak AS nama_kontak1,
            kon1.hubungan AS hubungan1,
            kon1.no_telp_darurat AS no_telp_darurat1,
            kon2.nama_kontak AS nama_kontak2,
            kon2.hubungan AS hubungan2,
            kon2.no_telp_darurat AS no_telp_darurat2,
            tipe.nama_tipe,
            tipe.jam_kerja_mulai,
            tipe.jam_kerja_selesai
        FROM tb_karyawan kar
        LEFT JOIN tb_tipe_karyawan tipe ON kar.id_tipe_karyawan = tipe.id_tipe_karyawan
        INNER JOIN tb_tereliminasi te ON kar.id_karyawan = te.id_karyawan
        LEFT JOIN m_jenis_keluar jk ON te.id_jenis_keluar = jk.id_jenis_keluar
        LEFT JOIN tb_identitas i ON kar.id_karyawan = i.id_karyawan
        LEFT JOIN tb_karier k ON kar.id_karyawan = k.id_karyawan
            AND k.id_karier = (SELECT MAX(k2.id_karier) FROM tb_karier k2 WHERE k2.id_karyawan = kar.id_karyawan)
        LEFT JOIN tb_site s ON k.id_site = s.id_site
        LEFT JOIN tb_departemen d ON k.id_departemen = d.id_departemen
        LEFT JOIN tb_jabatan j ON k.id_jabatan = j.id_jabatan
        LEFT JOIN tb_kontak_darurat kon1
            ON kar.id_karyawan = kon1.id_karyawan AND kon1.nomor_urut = 1
        LEFT JOIN tb_kontak_darurat kon2
            ON kar.id_karyawan = kon2.id_karyawan AND kon2.nomor_urut = 2
        WHERE $where_sql
        ORDER BY te.tgl_keluar DESC, kar.nama_lengkap ASC
    ";

    $stmt = $koneksi->prepare($query);
    $stmt->execute($params);
    $result = $stmt;

    $data = [];
    $no = 1;

    while ($row = $result->fetch()) {
        $usia = '';
        if (!empty($row['tgl_lahir'])) {
            try {
                $lahir = new DateTime($row['tgl_lahir']);
                $sekarang = new DateTime();
                $usia = $sekarang->diff($lahir)->y . ' tahun';
            } catch (Exception $e) {
                $usia = '';
            }
        }

        $tgl_lahir_format = !empty($row['tgl_lahir']) ? date('d-m-Y', strtotime($row['tgl_lahir'])) : '-';
        $tgl_mulai_format = !empty($row['tgl_mulai']) ? date('d-m-Y', strtotime($row['tgl_mulai'])) : '-';
        $tgl_selesai_format = !empty($row['tgl_selesai']) ? date('d-m-Y', strtotime($row['tgl_selesai'])) : '-';
        $tgl_keluar_format = !empty($row['tgl_keluar']) ? date('d-m-Y', strtotime($row['tgl_keluar'])) : '-';

        $foto_karyawan = $row['foto'] ?? '';
        if (!empty($foto_karyawan)) {
            $url_foto = 'app/uploads/profil/' . htmlspecialchars($foto_karyawan);
        } else {
            $url_foto = 'assets/img/avatars/person.png';
        }

        $nama_esc = htmlspecialchars($row['nama_lengkap']);
        $foto_html = "<img src='{$url_foto}' onerror=\"this.onerror=null;this.src='assets/img/avatars/person.png';\" class='rounded-circle shadow-sm zoom-foto' width='45' height='45' style='object-fit: cover; cursor: pointer;' data-src='{$url_foto}' data-nama='{$nama_esc}'>";

        $jenis_keluar = $row['jenis_keluar'] ?? $row['status_karyawan'];

        // Tipe karyawan: Kontrak / Harian (data lama default Kontrak).
        $tipe_karyawan = $row['nama_tipe'] ?? $row['tipe_karyawan'] ?? 'Kontrak';
        $tipe_badge = ($tipe_karyawan === 'Harian') ? 'bg-warning text-dark' : 'bg-primary';

        $eliminasiHtml = "
            <h6 class='text-danger'><i class='fa fa-user-slash'></i> Data Eliminasi</h6>
            <table class='table table-sm table-borderless info-table' style='font-size: 13px;'>
                <tr>
                    <td><strong>Status Karyawan</strong></td>
                    <td>: <span class='badge bg-danger'>" . htmlspecialchars($row['status_karyawan']) . "</span></td>
                </tr>
                <tr>
                    <td><strong>Jenis Keluar</strong></td>
                    <td>: " . htmlspecialchars($jenis_keluar) . "</td>
                </tr>
                <tr>
                    <td><strong>Tanggal Keluar</strong></td>
                    <td>: {$tgl_keluar_format}</td>
                </tr>
                <tr>
                    <td><strong>Alasan Keluar</strong></td>
                    <td>: " . htmlspecialchars($row['alasan_keluar'] ?? '-') . "</td>
                </tr>
                <tr>
                    <td><strong>Tipe Karyawan</strong></td>
                    <td>: <span class='badge $tipe_badge'>" . htmlspecialchars($tipe_karyawan) . "</span></td>
                </tr>
                <tr>
                    <td><strong>Jam Kerja</strong></td>
                    <td>: " . (!empty($row['jam_kerja_mulai']) && !empty($row['jam_kerja_selesai'])
                        ? date('H:i', strtotime($row['jam_kerja_mulai'])) . ' – ' . date('H:i', strtotime($row['jam_kerja_selesai']))
                        : 'Ikuti aturan tipe (' . htmlspecialchars($tipe_karyawan) . ')') . "</td>
                </tr>
            </table>
        ";

        $detail = "
        <div class='card' style='font-size: 13px;'>
            <div class='card-body'>
                <ul class='nav nav-tabs' role='tablist'>
                    <li class='nav-item' role='presentation'>
                        <button class='nav-link active' data-bs-toggle='tab' data-bs-target='#tab-info-pribadi' type='button' role='tab'>Informasi Pribadi</button>
                    </li>
                    <li class='nav-item' role='presentation'>
                        <button class='nav-link' data-bs-toggle='tab' data-bs-target='#tab-riwayat-karier' type='button' role='tab'>Riwayat Karier</button>
                    </li>
                </ul>
                <div class='tab-content pt-3'>
                    <div class='tab-pane fade show active' id='tab-info-pribadi' role='tabpanel'>
                <div class='row '>
                    <div class='col-md-4 mb-4'>
                        <h6 class='text-primary'><i class='fa fa-user'></i> Data Pribadi</h6>
                        <table class='table table-sm table-borderless info-table'>
                            <tr><td><strong>Tempat Lahir</strong></td><td>: " . htmlspecialchars($row['tempat_lahir'] ?? '-') . "</td></tr>
                            <tr><td><strong>Tanggal Lahir</strong></td><td>: $tgl_lahir_format ($usia)</td></tr>
                            <tr><td><strong>Jenis Kelamin</strong></td><td>: " . ($row['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan') . "</td></tr>
                            <tr><td><strong>Agama</strong></td><td>: " . htmlspecialchars($row['agama'] ?? '-') . "</td></tr>
                            <tr><td><strong>Status Pernikahan</strong></td><td>: " . htmlspecialchars($row['status_pernikahan'] ?? '-') . "</td></tr>
                            <tr><td><strong>Nationality</strong></td><td>: " . htmlspecialchars($row['nationality'] ?? '-') . "</td></tr>
                        </table>
                    </div>
                    <div class='col-md-4 mb-4'>
                        <h6 class='text-primary'><i class='fa fa-id-card'></i> Identitas & Kontak</h6>
                        <table class='table table-sm table-borderless info-table'>
                            <tr><td><strong>NIK KTP</strong></td><td>: " . htmlspecialchars($row['nik_ktp'] ?? '-') . "</td></tr>
                            <tr><td><strong>Email</strong></td><td>: " . htmlspecialchars($row['email_pribadi'] ?? '-') . "</td></tr>
                            <tr><td><strong>No. Telp</strong></td><td>: " . htmlspecialchars($row['no_telp'] ?? '-') . "</td></tr>
                            <tr><td><strong>Site Terakhir</strong></td><td>: " . htmlspecialchars($row['nama_site'] ?? '-') . "</td></tr>
                            <tr><td><strong>Departemen</strong></td><td>: " . htmlspecialchars($row['nama_departemen'] ?? '-') . "</td></tr>
                            <tr><td><strong>Jabatan</strong></td><td>: " . htmlspecialchars($row['nama_jabatan'] ?? '-') . "</td></tr>
                        </table>
                    </div>
                    <div class='col-md-4 mb-4'>
                        <h6 class='text-primary'><i class='fa fa-briefcase'></i> Penempatan Terakhir</h6>
                        <table class='table table-sm table-borderless info-table'>
                            <tr><td><strong>Tipe Karyawan</strong></td><td>: <span class='badge $tipe_badge'>" . htmlspecialchars($tipe_karyawan) . "</span></td></tr>
                            <tr><td><strong>Tgl Mulai</strong></td><td>: $tgl_mulai_format</td></tr>
                            <tr><td><strong>Tgl Selesai</strong></td><td>: $tgl_selesai_format</td></tr>
                        </table>
                    </div>
                </div>
                <div class='row'>
                    <div class='col-md-12'>
                        $eliminasiHtml
                    </div>
                </div>
                    </div>
                    <div class='tab-pane fade' id='tab-riwayat-karier' role='tabpanel'>
                        <div class='table-responsive'>
                            <table class='table table-sm table-bordered'>
                                <thead class='table-info'>
                                    <tr>
                                        <th class='text-center'>No</th>
                                        <th class='text-center'>Site</th>
                                        <th class='text-center'>Departemen</th>
                                        <th class='text-center'>Jabatan</th>
                                        <th class='text-center'>Tgl Mulai</th>
                                        <th class='text-center'>Tgl Selesai</th>
                                    </tr>
                                </thead>
                                <tbody id='riwayatKarierBody'>
                                    <tr><td colspan='6' class='text-center'>Memuat riwayat karier...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        ";

        $action = "
            <div class='d-flex justify-content-center gap-1'>
                <button class='btn btn-sm btn-info btnDetailTer' title='Lihat Detail'>
                    <i class='fa fa-eye me-1'></i>Detail
                </button>
                <button class='btn btn-sm btn-warning btnKoreksiTer' data-id='" . $row['kar_id_karyawan'] . "' title='Koreksi Data Eliminasi'>
                    <i class='fa fa-edit me-1'></i>Koreksi
                </button>
                <button class='btn btn-sm btn-success btnReaktivasi' data-id='" . $row['kar_id_karyawan'] . "' data-nama='" . htmlspecialchars($row['nama_lengkap']) . "' title='Kembalikan menjadi Aktif'>
                    <i class='fa fa-undo me-1'></i>Reaktivasi
                </button>
            </div>
        ";

        $badge_class = 'bg-dark';
        switch ($row['status_karyawan']) {
            case 'Resign': $badge_class = 'bg-warning text-dark'; break;
            case 'PHK': $badge_class = 'bg-danger'; break;
            case 'Habis Kontrak': $badge_class = 'bg-secondary'; break;
            case 'Pensiun': $badge_class = 'bg-info text-dark'; break;
            case 'Black List': $badge_class = 'bg-dark'; break;
        }

        $data[] = [
            'no' => $no++,
            'id_karyawan' => (int)($row['kar_id_karyawan'] ?? 0),
            'foto' => $foto_html,
            'nama' => htmlspecialchars($row['nama_lengkap']),
            'nik' => htmlspecialchars($row['nik_karyawan'] ?? '-'),
            'jabatan' => htmlspecialchars($row['nama_jabatan'] ?? '-'),
            'site' => htmlspecialchars($row['nama_site'] ?? '-'),
            'status_karyawan' => "<span class='badge $badge_class'>" . htmlspecialchars($row['status_karyawan'] ?? '-') . "</span>",
            'status_raw' => $row['status_karyawan'] ?? '',
            'tgl_keluar' => $tgl_keluar_format,
            'tgl_keluar_raw' => $row['tgl_keluar'] ?? '',
            'jenis_keluar' => htmlspecialchars($jenis_keluar),
            'alasan_keluar' => htmlspecialchars($row['alasan_keluar'] ?? '-'),
            'action' => $action,
            'detail' => $detail,
        ];
    }

    echo json_encode([
        'data' => $data,
        'recordsTotal' => count($data),
        'recordsFiltered' => count($data)
    ]);
} catch (Exception $e) {
    error_log("Get Karyawan Tereliminasi Error: " . $e->getMessage());
    echo json_encode([
        'data' => [],
        'error' => 'Terjadi kesalahan saat mengambil data',
        'debug' => $e->getMessage()
    ]);
}
