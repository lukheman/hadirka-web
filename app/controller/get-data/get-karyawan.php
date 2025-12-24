<?php
/**
 * Get Karyawan Data for DataTables
 * =================================
 * FIXED VERSION - Tampilkan data karyawan dengan detail
 */

header("Content-Type: application/json");
error_reporting(E_ALL);
ini_set('display_errors', 0); // Matikan error display agar tidak corrupt JSON

require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../../config/function.php';

try {
    // Ambil filter dari POST
    $filter_site = isset($_POST['site']) ? $koneksi->real_escape_string($_POST['site']) : '';
    $filter_jabatan = isset($_POST['jabatan']) ? $koneksi->real_escape_string($_POST['jabatan']) : '';
    
    // Build WHERE clause
    $where = ["1=1"];
    
    if (!empty($filter_site)) {
        $where[] = "k.id_site = '$filter_site'";
    }
    
    if (!empty($filter_jabatan)) {
        $where[] = "k.id_jabatan = '$filter_jabatan'";
    }
    
    $where_sql = implode(" AND ", $where);
    
    // Query utama dengan JOIN
    $query = "SELECT *  FROM tb_karyawan kar
        LEFT JOIN tb_identitas i ON kar.id_karyawan = i.id_karyawan
        LEFT JOIN tb_karier k ON kar.id_karyawan = k.id_karyawan
        LEFT JOIN tb_site s ON k.id_site = s.id_site
        LEFT JOIN tb_departemen d ON k.id_departemen = d.id_departemen
        LEFT JOIN tb_jabatan j ON k.id_jabatan = j.id_jabatan
        WHERE $where_sql
        ORDER BY kar.nik_karyawan ASC
    ";
    
    $result = $koneksi->query($query);
    
    if (!$result) {
        throw new Exception("Query Error: " . $koneksi->error);
    }
    
    $data = [];
    $no = 1;
    
    while ($row = $result->fetch_assoc()) {
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
        
        // Build detail HTML (child row)
        $detail = "
        <div class='card'>
            <div class='card-body'>
                <div class='row'>
                    <!-- Kolom 1 -->
                    <div class='col-md-4'>
                        <h6 class='text-primary'><i class='fa fa-user'></i> Data Pribadi</h6>
                        <table class='table table-sm table-borderless'>
                            <tr><td><strong>Tempat Lahir</strong></td><td>: " . htmlspecialchars($row['tempat_lahir'] ?? '-') . "</td></tr>
                            <tr><td><strong>Tanggal Lahir</strong></td><td>: $tgl_lahir_format ($usia)</td></tr>
                            <tr><td><strong>Jenis Kelamin</strong></td><td>: " . ($row['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan') . "</td></tr>
                            <tr><td><strong>Agama</strong></td><td>: " . htmlspecialchars($row['agama'] ?? '-') . "</td></tr>
                            <tr><td><strong>Status</strong></td><td>: " . htmlspecialchars($row['status_pernikahan'] ?? '-') . "</td></tr>
                            <tr><td><strong>Nationality</strong></td><td>: " . htmlspecialchars($row['nationality'] ?? '-') . "</td></tr>
                        </table>
                    </div>
                    
                    <!-- Kolom 2 -->
                    <div class='col-md-4'>
                        <h6 class='text-primary'><i class='fa fa-id-card'></i> Identitas & Kontak</h6>
                        <table class='table table-sm table-borderless'>
                            <tr><td><strong>NIK KTP</strong></td><td>: " . htmlspecialchars($row['nik_ktp'] ?? '-') . "</td></tr>
                            <tr><td><strong>No. KK</strong></td><td>: " . htmlspecialchars($row['kk_number'] ?? '-') . "</td></tr>
                            <tr><td><strong>NPWP</strong></td><td>: " . htmlspecialchars($row['npwp'] ?? '-') . "</td></tr>
                            <tr><td><strong>BPJS TK</strong></td><td>: " . htmlspecialchars($row['bpjs_tk'] ?? '-') . "</td></tr>
                            <tr><td><strong>BPJS KES</strong></td><td>: " . htmlspecialchars($row['bpjs_kes'] ?? '-') . "</td></tr>
                            <tr><td><strong>Email</strong></td><td>: " . htmlspecialchars($row['email_pribadi'] ?? '-') . "</td></tr>
                            <tr><td><strong>No. Telp</strong></td><td>: " . htmlspecialchars($row['no_telp'] ?? '-') . "</td></tr>
                        </table>
                    </div>
                    
                    <!-- Kolom 3 -->
                    <div class='col-md-4'>
                        <h6 class='text-primary'><i class='fa fa-briefcase'></i> Karier & Pendidikan</h6>
                        <table class='table table-sm table-borderless'>
                            <tr><td><strong>Site</strong></td><td>: " . htmlspecialchars($row['nama_site'] ?? '-') . "</td></tr>
                            <tr><td><strong>Departemen</strong></td><td>: " . htmlspecialchars($row['nama_departemen'] ?? '-') . "</td></tr>
                            <tr><td><strong>Jabatan</strong></td><td>: " . htmlspecialchars($row['nama_jabatan'] ?? '-') . "</td></tr>
                            <tr><td><strong>Tgl Mulai</strong></td><td>: $tgl_mulai_format</td></tr>
                            <tr><td><strong>Tgl Selesai</strong></td><td>: <span class='badge bg-danger'>$tgl_selesai_format</span></td></tr>
                            <tr><td><strong>Pendidikan</strong></td><td>: " . htmlspecialchars($row['last_education'] ?? '-') . "</td></tr>
                            <tr><td><strong>Jurusan</strong></td><td>: " . htmlspecialchars($row['major'] ?? '-') . "</td></tr>
                        </table>
                    </div>
                </div>
                
                <div class='row mt-2'>
                    <div class='col-12'>
                        <h6 class='text-primary'><i class='fa fa-map-marker'></i> Alamat KTP</h6>
                        <p>" . htmlspecialchars($row['alamat_ktp'] ?? '-') . "<br>
                        Desa " . htmlspecialchars($row['desa'] ?? '') . ", Kec. " . htmlspecialchars($row['kecamatan'] ?? '') . ", " . htmlspecialchars($row['kabupaten'] ?? '') . ", " . htmlspecialchars($row['provinsi'] ?? '') . " 
                        (RT/RW: " . htmlspecialchars($row['rt_rw'] ?? '-') . ")</p>
                    </div>
                </div>
            </div>
        </div>
        ";
        
        // Action buttons
        $action = "
            <a href='karier/".encrypt($row['id_karyawan'])."' class='btn btn-sm btn-info'>Karir</a>
            <button class='btn btn-sm btn-warning btnEdit' data-id='" . $row['id_karyawan'] . "' title='Edit'>
                <i class='fa fa-edit'></i>
            </button>
            <button class='btn btn-sm btn-danger btnDelete' data-id='" . $row['id_karyawan'] . "' title='Hapus'>
                <i class='fa fa-trash'></i>
            </button>
        ";
        
        $data[] = [
            'no' => $no++,
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
?>