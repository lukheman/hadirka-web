<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors in output
ini_set('log_errors', 1);

// Bypass batasan waktu eksekusi agar tidak timeout saat melooping ratusan karyawan
header('X-LiteSpeed-No-Timeout: 1');
set_time_limit(0);

// Set header first
header("Content-Type: application/json");

try {
    include __DIR__ . '/../../config/koneksi.php';
    include __DIR__ . '/../../config/function.php';
    include __DIR__ . '/../../config/function-slip.php';

    // Check database connection
    if (!$koneksi) {
        throw new Exception("Database connection failed");
    }

    // --- Periode Dinamis Dari AJAX ---
    $startDate = $_POST['startDate'] ?? date("Y-m-01");
    $endDate   = $_POST['endDate']   ?? date("Y-m-t");

    // Ambil semua karyawan dengan data identitas (rekening dan site)
    // HANYA yang kariernya aktif pada periode yang dipilih
    // FIX 2: Hapus GROUP BY dan gunakan DISTINCT untuk mencegah ONLY_FULL_GROUP_BY error MySQL
    $sql = "SELECT DISTINCT
                k.id_karyawan, 
                k.nik_karyawan, 
                k.nama_lengkap,
                s.nama_site,
                i.atas_nama_rek,
                i.no_rek,
                i.nama_bank
            FROM tb_karyawan k
            LEFT JOIN tb_identitas i ON k.id_karyawan = i.id_karyawan
            LEFT JOIN tb_karier kr ON kr.id_karyawan = k.id_karyawan
            LEFT JOIN tb_site s ON kr.id_site = s.id_site
            WHERE kr.tgl_mulai <= ?
              AND (kr.tgl_selesai IS NULL OR kr.tgl_selesai >= ?)
            ORDER BY k.nama_lengkap ASC";

    $stPayrol = $koneksi->prepare($sql);
    $stPayrol->execute([$endDate, $startDate]);
    $q = $stPayrol;

    // JSON response array
    $data = [];
    $no = 1;

    while ($r = $q->fetch()) {

        // Check if generateSlipGaji function exists
        if (function_exists('generateSlipGaji')) {
            $slip = generateSlipGaji($r['nik_karyawan'], $startDate, $endDate);

            if (isset($slip['error'])) {
                continue;
            } else {
                $jabatan = $slip['jabatan'] ?? "-";
                // UBAH DARI (int) MENJADI round()
                $gaji_bersih = (int)round($slip['gaji_bersih'] ?? 0);
            }
        } else {
            // If function doesn't exist, use default values
            $jabatan = "-";
            $gaji_bersih = 0;
        }

        // Jika gaji bersih minus, lewati baris ini agar tidak ditambahkan ke tabel
        if ($gaji_bersih < 0) {
            continue;
        }

        $data[] = [
            "no" => $no++,
            "id_karyawan" => $r['id_karyawan'],
            "nik_karyawan" => $r['nik_karyawan'] ?? "-",
            "nama" => $r['nama_lengkap'] ?? "-",
            "jabatan" => $jabatan,
            "site" => $r['nama_site'] ?? null,
            "rekening_nama" => $r['atas_nama_rek'] ?? null,
            "rekening_no" => $r['no_rek'] ?? null,
            "bank" => $r['nama_bank'] ?? null,
            "gaji_bersih_raw" => $gaji_bersih, // Raw number for Excel
            "gaji_bersih_formatted" => number_format($gaji_bersih, 0, ',', '.') // Formatted for display
        ];
    }

    // FIX 3: Bersihkan buffer sebelum output agar JSON tidak rusak
    ob_clean(); 
    echo json_encode([
        "status" => "success",
        "data" => $data,
        "count" => count($data)
    ]);
} catch (Throwable $e) { 
    // FIX 4: Gunakan Throwable (bukan Exception) untuk menangkap PHP Fatal Error
    ob_clean();
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage(),
        "file" => $e->getFile(),
        "line" => $e->getLine()
    ]);
}
exit;
