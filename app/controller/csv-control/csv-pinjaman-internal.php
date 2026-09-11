<?php
error_reporting(0);
ini_set('display_errors', 0);
header("Content-Type: application/json; charset=utf-8");

require __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../../config/Excel.php'; // Excel::cleanRupiah() terpusat
require '../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

try {
    if (!isset($_FILES['file_excel']) || $_FILES['file_excel']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("File Excel kosong atau terjadi error saat upload.");
    }

    $file = $_FILES['file_excel']['tmp_name'];
    $spreadsheet = IOFactory::load($file);
    
    // Set formatData ke false agar nominal terbaca bulat
    $sheet = $spreadsheet->getActiveSheet()->toArray(null, true, false, true);

    $successCount = 0;
    $errorCount = 0;
    $startRow = 2; 

    mysqli_begin_transaction($koneksi); 

    foreach ($sheet as $i => $row) {
        if ($i == 1) continue; 

        // Mapping Kolom Import
        $nik = strtoupper(trim($row['B'] ?? ''));
        $nominal_total = Excel::cleanRupiah($row['D'] ?? '0');
        $tglPinjamRaw = trim($row['E'] ?? '');
        $tglBayarRaw = trim($row['F'] ?? '');
        $ket = trim($row['G'] ?? '');
        $tenorRaw = trim($row['H'] ?? '1');

        if (empty($nik)) continue;

        $tenor = (int) $tenorRaw;
        if ($tenor < 1) $tenor = 1;

        if (empty($tglPinjamRaw) || empty($tglBayarRaw) || $nominal_total <= 0) {
            $errorCount++;
            continue;
        }

        $tgl_pinjaman = null;
        if (is_numeric($tglPinjamRaw)) {
            $tgl_pinjaman = Date::excelToDateTimeObject($tglPinjamRaw)->format('Y-m-d');
        } else {
            $tgl_pinjaman = date('Y-m-d', strtotime(str_replace(['/', '.'], '-', $tglPinjamRaw)));
        }

        $tgl_bayar_awal = null;
        if (is_numeric($tglBayarRaw)) {
            $tgl_bayar_awal = Date::excelToDateTimeObject($tglBayarRaw)->format('Y-m-d');
        } else {
            $tgl_bayar_awal = date('Y-m-d', strtotime(str_replace(['/', '.'], '-', $tglBayarRaw)));
        }

        $cek = $koneksi->query("SELECT id_karyawan_internal FROM tb_karyawan_internal WHERE nik_karyawan_internal = '$nik' LIMIT 1");
        if ($cek->num_rows == 0) {
            $errorCount++;
            continue;
        }
        $id_karyawan = $cek->fetch_assoc()['id_karyawan_internal'];

        $cicilan_dasar = floor($nominal_total / $tenor);
        $sisa_bagi = $nominal_total - ($cicilan_dasar * $tenor);

        for ($j = 0; $j < $tenor; $j++) {
            $tgl_jatuh_tempo = date('Y-m-d', strtotime("+$j month", strtotime($tgl_bayar_awal)));
            
            $nominal_cicilan_ini = $cicilan_dasar;
            if ($j === ($tenor - 1)) {
                $nominal_cicilan_ini += $sisa_bagi; 
            }

            $ket_cicilan = $ket;
            if ($tenor > 1) {
                $cicilan_ke = $j + 1;
                $ket_cicilan = $ket . " (Cicilan $cicilan_ke/$tenor)";
            }

            // Cek duplikat
            $stmtCheck = $koneksi->prepare("SELECT id_pinjaman FROM tb_pinjaman_internal WHERE id_karyawan_internal = ? AND tgl_bayar_pinjaman = ? AND ket_pinjaman = ?");
            $stmtCheck->bind_param("iss", $id_karyawan, $tgl_jatuh_tempo, $ket_cicilan);
            $stmtCheck->execute();
            $resCheck = $stmtCheck->get_result();
            $isDuplicate = $resCheck->num_rows > 0;
            $stmtCheck->close();

            if ($isDuplicate) {
                $stmtUpd = $koneksi->prepare("UPDATE tb_pinjaman_internal SET nominal_pinjaman=?, tgl_pinjaman=? WHERE id_karyawan_internal=? AND tgl_bayar_pinjaman=? AND ket_pinjaman=?");
                $stmtUpd->bind_param("dsiss", $nominal_cicilan_ini, $tgl_pinjaman, $id_karyawan, $tgl_jatuh_tempo, $ket_cicilan);
                $stmtUpd->execute();
                $stmtUpd->close();
            } else {
                $stmtIns = $koneksi->prepare("INSERT INTO tb_pinjaman_internal (id_karyawan_internal, tgl_pinjaman, tgl_bayar_pinjaman, nominal_pinjaman, ket_pinjaman) VALUES (?,?,?,?,?)");
                $stmtIns->bind_param("issds", $id_karyawan, $tgl_pinjaman, $tgl_jatuh_tempo, $nominal_cicilan_ini, $ket_cicilan);
                $stmtIns->execute();
                $stmtIns->close();
            }
        }
        $successCount++;
    }

    mysqli_commit($koneksi); 
    echo json_encode([
        'status' => 'success',
        'message' => "Proses import selesai. Berhasil: $successCount baris Excel."
    ]);

} catch (Exception $e) {
    if(isset($koneksi)) mysqli_rollback($koneksi);
    echo json_encode(['status' => 'error', 'message' => 'System Error: ' . $e->getMessage()]);
}
?>