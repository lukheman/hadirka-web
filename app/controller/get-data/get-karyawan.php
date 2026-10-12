<?php

/**
 * Get Karyawan Data for DataTables
 * =================================
 * FIXED VERSION - Tampilkan data karyawan dengan detail
 */

header("Content-Type: application/json");
error_reporting(E_ALL);
ini_set('display_errors', 0); // Matikan error display agar tidak corrupt JSON

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../../config/function.php';

// Finance (level 6) hanya boleh melihat: sembunyikan tombol ubah di kolom aksi.
$is_finance_readonly = (($_SESSION['level'] ?? 99) == 6);

try {
    // Ambil filter dari POST
    $filter_site = isset($_POST['site']) ? $_POST['site'] : '';
    $filter_jabatan = isset($_POST['jabatan']) ? $_POST['jabatan'] : '';
    $filter_status = isset($_POST['status']) ? $_POST['status'] : '';
    $filter_bulan = isset($_POST['bulan']) ? $_POST['bulan'] : '';


    // Build WHERE clause
    $where = ["1=1"];
    $params = [];

    if (!empty($filter_status)) {
        if ($filter_status !== 'Semua') {
            // Jika memilih status spesifik (Aktif, Resign, dll)
            $where[] = "kar.status_karyawan = ?";
            $params[] = $filter_status;
        }
    } else {
        // Jika filter status kosong, tampilkan semua karyawan aktif
        $where[] = "kar.status_karyawan = 'Aktif'";
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
        // Asumsi tgl_selesai ada di tb_karier yang di-alias sebagai 'k'
        $where[] = "MONTH(k.tgl_selesai) = ?";
        $params[] = $filter_bulan;

        // Opsional: Jika Anda ingin memfilter spesifik hanya di tahun ini juga,
        // Anda bisa meng-uncomment baris di bawah ini:
        $where[] = "YEAR(k.tgl_selesai) = YEAR(CURDATE())";
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
            te.tgl_keluar, te.alasan_keluar,

            kon1.nama_kontak AS nama_kontak1,
            kon1.hubungan AS hubungan1,
            kon1.no_telp_darurat AS no_telp_darurat1,

            kon2.nama_kontak AS nama_kontak2,
            kon2.hubungan AS hubungan2,
            kon2.no_telp_darurat AS no_telp_darurat2
        FROM tb_karyawan kar
        LEFT JOIN tb_identitas i ON kar.id_karyawan = i.id_karyawan
        LEFT JOIN tb_karier k ON kar.id_karyawan = k.id_karyawan
            AND k.id_karier = (SELECT MAX(k2.id_karier) FROM tb_karier k2 WHERE k2.id_karyawan = kar.id_karyawan)
        LEFT JOIN tb_site s ON k.id_site = s.id_site
        LEFT JOIN tb_departemen d ON k.id_departemen = d.id_departemen
        LEFT JOIN tb_jabatan j ON k.id_jabatan = j.id_jabatan
        LEFT JOIN tb_tereliminasi te ON kar.id_karyawan = te.id_karyawan
        LEFT JOIN tb_kontak_darurat kon1 
            ON kar.id_karyawan = kon1.id_karyawan AND kon1.nomor_urut = 1
        LEFT JOIN tb_kontak_darurat kon2 
            ON kar.id_karyawan = kon2.id_karyawan AND kon2.nomor_urut = 2
        WHERE $where_sql
        ORDER BY kar.nik_karyawan ASC
    ";

    $stmt = $koneksi->prepare($query);
    $stmt->execute($params);
    $result = $stmt;

    $data = [];
    $no = 1;

    while ($row = $result->fetch()) {
        // Hitung usia
        $usia = '';
        if (!empty($row['tgl_lahir'])) {
            $lahir = new DateTime($row['tgl_lahir']);
            $sekarang = new DateTime();
            $usia = $sekarang->diff($lahir)->y . ' tahun';
        }

        // Cek kontrak expired (merah jika kurang dari 30 hari)
        $rowClass = '';
        if (!empty($row['tgl_selesai'])) {
            $selesai = new DateTime($row['tgl_selesai']);
            $sekarang = new DateTime();
            $diff = $sekarang->diff($selesai)->days;

            if ($sekarang > $selesai) {
                $rowClass = 'table-danger'; // Sudah expired
            } elseif ($diff <= 30) {
                $rowClass = 'table-warning'; // Kurang dari 30 hari
            }
        }

        // Format tanggal Indonesia
        $tgl_lahir_format = !empty($row['tgl_lahir']) ? date('d-m-Y', strtotime($row['tgl_lahir'])) : '-';
        $tgl_mulai_format = !empty($row['tgl_mulai']) ? date('d-m-Y', strtotime($row['tgl_mulai'])) : '-';
        $tgl_selesai_format = !empty($row['tgl_selesai']) ? date('d-m-Y', strtotime($row['tgl_selesai'])) : '-';


        // --- LOGIKA FOTO ---
        $foto_karyawan = $row['foto'] ?? '';
        if (!empty($foto_karyawan)) {
            $url_foto = 'app/uploads/profil/' . htmlspecialchars($foto_karyawan); 
        } else {
            $url_foto = 'assets/img/avatars/person.png';
        }
        
        $nama_esc = htmlspecialchars($row['nama_lengkap']);
        
        // Tambahkan class 'zoom-foto', atribut 'data-src', dan 'data-nama' agar bisa ditangkap JavaScript
        $foto_html = "<img src='{$url_foto}' onerror=\"this.onerror=null;this.src='assets/img/avatars/person.png';\" class='rounded-circle shadow-sm zoom-foto' width='45' height='45' style='object-fit: cover; cursor: pointer;' data-src='{$url_foto}' data-nama='{$nama_esc}'>";


        // Cek status apakah karyawan aktif atau bukan, klo bukan tampilkan alasan keluar dan tanggal keluar
        $isNonAktif = ($row['status_karyawan'] !== 'Aktif');

        $eliminasiHtml = '';

        if ($isNonAktif) {
            $tgl_keluar_format = !empty($row['tgl_keluar'])
                ? date('d-m-Y', strtotime($row['tgl_keluar']))
                : '-';

            $eliminasiHtml = "   
                <h6 class='text-danger'><i class='fa fa-user-slash'></i> Data Eliminasi</h6>
                <table class='table table-sm table-borderless info-table' style='font-size: 13px;'>
                    <tr>
                        <td><strong>Status Karyawan</strong></td>
                        <td>: <span class='badge bg-danger'>{$row['status_karyawan']}</span></td>
                    </tr>
                    <tr>
                        <td><strong>Tanggal Keluar</strong></td>
                        <td>: {$tgl_keluar_format}</td>
                    </tr>
                    <tr>
                        <td><strong>Alasan Keluar</strong></td>
                        <td>: " . htmlspecialchars($row['alasan_keluar'] ?? '-') . "</td>
                    </tr>
                </table>
            ";
        }


        // Build detail HTML (tab: Informasi Pribadi & Riwayat Karier)
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
                            <tr><td><strong>Status PTKP</strong></td><td>: " . htmlspecialchars($row['status_ptkp'] ?? '-') . "</td></tr>
                            <tr><td><strong>Nationality</strong></td><td>: " . htmlspecialchars($row['nationality'] ?? '-') . "</td></tr>
                        </table>
                    </div>
                    
                    <div class='col-md-4 mb-4'>
                        <h6 class='text-primary'><i class='fa fa-id-card'></i> Identitas & Kontak</h6>
                        <table class='table table-sm table-borderless info-table'>
                            <tr><td><strong>NIK KTP</strong></td><td>: " . htmlspecialchars($row['nik_ktp'] ?? '-') . "</td></tr>
                            <tr><td><strong>No. KK</strong></td><td>: " . htmlspecialchars($row['kk_number'] ?? '-') . "</td></tr>
                            <tr><td><strong>NPWP</strong></td><td>: " . htmlspecialchars($row['npwp'] ?? '-') . "</td></tr>
                            <tr><td><strong>BPJS TK</strong></td><td>: " . htmlspecialchars($row['bpjs_tk'] ?? '-') . "</td></tr>
                            <tr><td><strong>BPJS KES</strong></td><td>: " . htmlspecialchars($row['bpjs_kes'] ?? '-') . "</td></tr>
                            <tr><td><strong>Email</strong></td><td>: " . htmlspecialchars($row['email_pribadi'] ?? '-') . "</td></tr>
                            <tr><td><strong>No. Telp</strong></td><td>: " . htmlspecialchars($row['no_telp'] ?? '-') . "</td></tr>
                        </table>
                    </div>
                    
                    <div class='col-md-4 mb-4'>
                        <h6 class='text-primary'><i class='fa fa-graduation-cap'></i> Pendidikan</h6>
                        <table class='table table-sm table-borderless info-table'>
                            <tr><td><strong>Pendidikan</strong></td><td>: " . htmlspecialchars($row['last_education'] ?? '-') . "</td></tr>
                            <tr><td><strong>Jurusan</strong></td><td>: " . htmlspecialchars($row['major'] ?? '-') . "</td></tr>
                        </table>
                    </div>
                </div>   

                <div class='row'>
                    <div class='col-md-4 mb-4'>
                        <h6 class='text-primary'><i class='fa fa-map-marker'></i> Alamat KTP</h6>
                        <p>" . htmlspecialchars($row['alamat_ktp'] ?? '-') . "<br>
                        Desa " . htmlspecialchars($row['desa'] ?? '') . ", Kec. " . htmlspecialchars($row['kecamatan'] ?? '') . ", " . htmlspecialchars($row['kabupaten'] ?? '') . ", " . htmlspecialchars($row['provinsi'] ?? '') . " 
                        (RT/RW: " . htmlspecialchars($row['rt_rw'] ?? '-') . ")</p>
                    </div>
                    <div class='col-md-4 mb-4'>
                        <h6 class='text-primary'><i class='fa fa-phone'></i> Kontak Darurat 1</h6>
                        <table class='table table-sm table-borderless info-table'>
                            <tr><td><strong>Nama Kontak</strong></td><td>: " . htmlspecialchars($row['nama_kontak1'] ?? '-') . "</td></tr>
                            <tr><td><strong>Hubungan</strong></td><td>: " . htmlspecialchars($row['hubungan1'] ?? '-') . "</td></tr>
                            <tr><td><strong>No. Telepon</strong></td><td>: " . htmlspecialchars($row['no_telp_darurat1'] ?? '-') . "</td></tr>
                        </table>
                    </div>
                    <div class='col-md-4 mb-4'>
                        <h6 class='text-primary'><i class='fa fa-phone'></i> Kontak Darurat 2</h6>
                        <table class='table table-sm table-borderless info-table'>
                            <tr><td><strong>Nama Kontak</strong></td><td>: " . htmlspecialchars($row['nama_kontak2'] ?? '-') . "</td></tr>
                            <tr><td><strong>Hubungan</strong></td><td>: " . htmlspecialchars($row['hubungan2'] ?? '-') . "</td></tr>
                            <tr><td><strong>No. Telepon</strong></td><td>: " . htmlspecialchars($row['no_telp_darurat2'] ?? '-') . "</td></tr>
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
                                        <th class='text-center'>Gaji Pokok</th>
                                        <th class='text-center'>Tgl Mulai</th>
                                        <th class='text-center'>Tgl Selesai</th>
                                        <th class='text-center'>Status</th>
                                    </tr>
                                </thead>
                                <tbody id='riwayatKarierBody'>
                                    <tr><td colspan='8' class='text-center'>Memuat riwayat karier...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        ";

        // Di bagian build action buttons
        $eliminatedStatuses = ['Habis Kontrak', 'Pensiun', 'PHK', 'Resign', 'Black List'];

        if ($is_finance_readonly) {
            // Finance read-only: hanya boleh lihat detail.
            $action = "
                <button class='btn btn-sm btn-info btnDetail' title='Lihat Detail'>
                    <i class='fa fa-eye'></i>
                </button>
            ";
        } elseif (in_array($row['status_karyawan'], $eliminatedStatuses)) {
            $action = "
                <button class='btn btn-sm btn-info btnDetail' title='Lihat Detail'>
                    <i class='fa fa-eye'></i>
                </button><br>
                <span class='badge bg-danger'>TERELIMINASI</span><br>
                <small class='text-muted'>Tidak dapat diedit</small>
            ";
        } else {
            $action = "
                <button class='btn btn-sm btn-info btnDetail' title='Lihat Detail'>
                    <i class='fa fa-eye'></i>
                </button>
                <a href='?page=karier&id=" . urlencode(encrypt($row['kar_id_karyawan'])) . "' class='btn btn-sm btn-info' title='Lihat Karier'>
                    <i class='fa fa-briefcase'></i>
                </a>
                <button class='btn btn-sm btn-warning btnEdit' data-id='" . $row['kar_id_karyawan'] . "' title='Edit'>
                    <i class='fa fa-edit'></i>
                </button>
                <button class='btn btn-sm btn-danger btnDelete' data-id='" . $row['kar_id_karyawan'] . "' data-nama='" . htmlspecialchars($row['nama_lengkap']) . "' title='Hapus'>
                    <i class='fa fa-trash'></i>
                </button>
                <button class='btn btn-sm btn-dark btnElimination' data-id='" . $row['kar_id_karyawan'] . "' title='Eliminasi'>
                    <i class='fa fa-user-slash'></i>
                </button>
            ";
        }

        $data[] = [
            'no' => $no++,
            'id_karyawan' => (int)($row['kar_id_karyawan'] ?? 0),
            'foto' => $foto_html,
            'nama' => htmlspecialchars($row['nama_lengkap']),
            'nik' => htmlspecialchars($row['nik_karyawan'] ?? '-'),
            'jabatan' => htmlspecialchars($row['nama_jabatan'] ?? '-'),
            'status_karyawan' => htmlspecialchars($row['status_karyawan'] ?? '-'),
            'action' => $action,
            'detail' => $detail,
            'rowClass' => $rowClass
        ];
    }

    // Response JSON untuk DataTables
    echo json_encode([
        'data' => $data,
        'recordsTotal' => count($data),
        'recordsFiltered' => count($data)
    ]);
} catch (Exception $e) {
    // Log error
    error_log("Get Karyawan Error: " . $e->getMessage());

    // Return empty data dengan error message
    echo json_encode([
        'data' => [],
        'error' => 'Terjadi kesalahan saat mengambil data',
        'debug' => $e->getMessage() // Hapus di production
    ]);
}
