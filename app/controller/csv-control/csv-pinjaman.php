<?php
// Matikan output selain JSON untuk mencegah error "Unexpected Token"
error_reporting(0);
ini_set('display_errors', 0);
header("Content-Type: application/json");

require_once __DIR__ . '/../../config/koneksi.php'; 
require_once __DIR__ . '/../../config/Excel.php'; // Excel::cleanRupiah() terpusat
require '../../../vendor/autoload.php'; 

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Metode request tidak valid.");
    }

    if (!isset($_FILES['file_csv_pinjaman'])) {
        throw new Exception("File tidak ditemukan atau belum dipilih.");
    }

    $fileTmpPath = $_FILES['file_csv_pinjaman']['tmp_name'];
    $spreadsheet = IOFactory::load($fileTmpPath);
    
    // PERBAIKAN PENTING 1:
    // Parameter ke-3 (formatData) diubah jadi FALSE agar Excel tidak 
    // memaksa mengambil string berformat "Rp", melainkan angka aslinya.
    $data = $spreadsheet->getActiveSheet()->toArray(null, true, false, true);
    
    $successCount = 0;
    $errorCount = 0;
    $errors = [];
    $startRow = 2; // Lewati header

    $koneksi->beginTransaction(); // Gunakan transaksi agar aman

    // Prepare sekali di luar loop (dipakai ulang per baris/tenor).
    $stmtK = $koneksi->prepare("SELECT id_karyawan FROM tb_karyawan WHERE nik_karyawan = ?");
    $stmtCheck = $koneksi->prepare("SELECT id_pinjaman FROM tb_pinjaman WHERE id_karyawan = ? AND tgl_bayar_pinjaman = ? AND ket_pinjaman = ? AND tgl_pinjaman = ?");
    $stmtUpd = $koneksi->prepare("UPDATE tb_pinjaman SET nominal_pinjaman=?, tgl_pinjaman=? WHERE id_karyawan=? AND tgl_bayar_pinjaman=? AND ket_pinjaman=?");
    $stmtIns = $koneksi->prepare("INSERT INTO tb_pinjaman (id_karyawan, nominal_pinjaman, tgl_pinjaman, tgl_bayar_pinjaman, ket_pinjaman) VALUES (?,?,?,?,?)");

    for ($i = $startRow; $i <= count($data); $i++) {
        $row = $data[$i];
        
        // PERBAIKAN PENTING 2 (PENGAMAN LOOP):
        // Reset variabel penampung di awal loop agar data tidak bocor ke baris berikutnya
        $id_karyawan = null;

        $nik = trim($row['B'] ?? '');
        if (empty($nik)) continue;

        // 1. Cari ID Karyawan
        $stmtK->execute([$nik]);
        $resK = $stmtK->fetch();

        if (!$resK) {
            $errors[] = "Baris $i: NIK $nik tidak ada di database.";
            $errorCount++;
            continue;
        }

        $id_karyawan = $resK['id_karyawan'];

        // 2. Bersihkan Nominal Total menggunakan cleanRupiah
        $nominal = $row['E'] ?? '0';
        $nominal_total = Excel::cleanRupiah($nominal);

        // 3. Konversi Tanggal Pinjaman (Kolom F)
        $tglPinjamRaw = $row['F'] ?? '';
        $tgl_pinjaman = null;
        if (!empty($tglPinjamRaw)) {
            if (is_numeric($tglPinjamRaw)) {
                $tgl_pinjaman = Date::excelToDateTimeObject($tglPinjamRaw)->format('Y-m-d');
            } else {
                $tgl_pinjaman = date('Y-m-d', strtotime($tglPinjamRaw));
            }
        }

        // 4. Konversi Tanggal Bayar Awal (Kolom G)
        $tglBayarRaw = $row['G'] ?? '';
        $tgl_bayar_awal = null;
        if (!empty($tglBayarRaw)) {
            if (is_numeric($tglBayarRaw)) {
                $tgl_bayar_awal = Date::excelToDateTimeObject($tglBayarRaw)->format('Y-m-d');
            } else {
                $tgl_bayar_awal = date('Y-m-d', strtotime($tglBayarRaw));
            }
        }

        $ket = trim($row['H'] ?? '');

        // 5. Tangkap Tenor dari Kolom I (Default 1)
        $tenorRaw = trim($row['I'] ?? '1');
        $tenor = (int) $tenorRaw;
        if ($tenor < 1) $tenor = 1;

        if (empty($tgl_pinjaman) || empty($tgl_bayar_awal) || $nominal_total <= 0) {
            $errors[] = "Baris $i: Data tidak lengkap (Tanggal/Nominal kosong).";
            $errorCount++;
            continue;
        }

        // Hitung nominal cicilan dasar
        $cicilan_dasar = floor($nominal_total / $tenor);
        $sisa_bagi = $nominal_total - ($cicilan_dasar * $tenor);

        // 6. Looping Insert/Update sebanyak Tenor
        for ($j = 0; $j < $tenor; $j++) {
            $tgl_jatuh_tempo = date('Y-m-d', strtotime("+$j month", strtotime($tgl_bayar_awal)));
            
            // Cicilan bulan terakhir menanggung sisa pembagian agar totalnya genap
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
            $stmtCheck->execute([$id_karyawan, $tgl_jatuh_tempo, $ket_cicilan, $tgl_pinjaman]);
            $isDuplicate = (bool)$stmtCheck->fetch();

            if ($isDuplicate) {
                $stmtUpd->execute([$nominal_cicilan_ini, $tgl_pinjaman, $id_karyawan, $tgl_jatuh_tempo, $ket_cicilan]);
            } else {
                $stmtIns->execute([$id_karyawan, $nominal_cicilan_ini, $tgl_pinjaman, $tgl_jatuh_tempo, $ket_cicilan]);
            }
        }
        $successCount++;
    }

    $koneksi->commit(); // Simpan semua perubahan

    echo json_encode([
        'status' => ($errorCount > 0 && $successCount == 0) ? 'error' : (($errorCount > 0) ? 'warning' : 'success'),
        'message' => "Selesai. Berhasil: $successCount baris Excel, Gagal: $errorCount. " . implode(" ", array_slice($errors, 0, 2))
    ]);

} catch (Exception $e) {
    error_log('csv-pinjaman: ' . $e->getMessage());
    if (isset($koneksi) && $koneksi instanceof PDO && $koneksi->inTransaction()) $koneksi->rollBack();
    echo json_encode(['status' => 'error', 'message' => 'System Error: gagal memproses file.']);
}
?>