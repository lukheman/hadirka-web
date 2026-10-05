<?php
// Mengaktifkan pelaporan error untuk debugging internal, matikan display langsung ke output
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Set header response ke JSON
header("Content-Type: application/json");

try {
    require_once __DIR__ . '/../../config/koneksi.php';
    // Sertakan file fungsi payroll jika diperlukan untuk mengambil nama jabatan secara dinamis
    if (file_exists('../../config/function.php')) {
        include '../../config/function.php';
    }
    if (file_exists('../../config/function-slip.php')) {
        include '../../config/function-slip.php';
    }

    if (!$koneksi) {
        throw new Exception("Koneksi database gagal terhubung.");
    }

    // Tangkap rentang tanggal pencarian dari request POST AJAX
    $startDate = $_POST['startDate'] ?? date("Y-m-01");
    $endDate   = $_POST['endDate']   ?? date("Y-m-t");

    // Ambil data pengajuan pinjaman dan join dengan detail profil serta data karier aktif karyawan
    $query = "SELECT p.*, k.nama_lengkap, k.nik_karyawan,
                    i.nama_bank, i.no_rek, i.atas_nama_rek,
                    COALESCE(s.nama_site, '-') AS nama_site
            FROM tb_pengajuan_pinjaman p
            JOIN tb_karyawan k ON p.id_karyawan = k.id_karyawan
            LEFT JOIN tb_identitas i ON k.id_karyawan = i.id_karyawan
            LEFT JOIN tb_karier kr ON kr.id_karyawan = k.id_karyawan 
            LEFT JOIN tb_site s ON kr.id_site = s.id_site
            WHERE DATE(p.tgl_pengajuan) BETWEEN ? AND ?
            GROUP BY p.id_pengajuan_pinjaman
            ORDER BY p.tgl_pengajuan DESC";

    $stPinjaman = $koneksi->prepare($query);
    $stPinjaman->execute([$startDate, $endDate]);
    $result = $stPinjaman;

    $data = [];
    $no = 1;

    while ($row = $result->fetch()) {
        // Ambil nama jabatan menggunakan fungsi generateSlipGaji bawaan agar nama jabatan seragam dengan payroll
        $jabatan = "-";
        if (function_exists('generateSlipGaji')) {
            $slip = generateSlipGaji($row['nik_karyawan'], $startDate, $endDate);
            if (!isset($slip['error'])) {
                $jabatan = $slip['jabatan'] ?? "-";
            }
        }

        $data[] = [
            "no" => $no++,
            "id_pengajuan_pinjaman" => $row['id_pengajuan_pinjaman'],
            "id_karyawan" => $row['id_karyawan'],
            "nik_karyawan" => $row['nik_karyawan'] ?? "-",
            "nama_lengkap" => $row['nama_lengkap'] ?? "-",
            "jabatan" => $jabatan,
            "site" => $row['nama_site'] ?? null,
            "tgl_pengajuan" => $row['tgl_pengajuan'],
            "nominal_pinjaman" => (int)$row['nominal_pinjaman'],
            "alasan_pinjaman" => $row['alasan_pinjaman'] ?? "-",
            "foto_wajah" => $row['foto_wajah'] ?? null,
            "foto_id_card" => $row['foto_id_card'] ?? null,
            "nama_bank" => $row['nama_bank'] ?? null,
            "no_rek" => $row['no_rek'] ?? null,
            "atas_nama_rek" => $row['atas_nama_rek'] ?? null,
            "status_pinjaman" => $row['status_pinjaman'] ?? "pending",
            "keterangan_admin" => $row['keterangan_admin'] ?? ""
        ];
    }

    echo json_encode([
        "status" => "success",
        "data" => $data,
        "count" => count($data)
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}
exit;