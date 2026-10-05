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
    // Pastikan request adalah POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Metode request tidak valid.");
    }

    if (!isset($_FILES['file_csv_potongan'])) {
        throw new Exception("File tidak ditemukan atau belum dipilih.");
    }

    $fileTmpPath = $_FILES['file_csv_potongan']['tmp_name'];
    $spreadsheet = IOFactory::load($fileTmpPath);
    
    // PERBAIKAN PENTING:
    // Parameter ke-3 (formatData) diubah jadi FALSE agar Excel tidak 
    // memaksa mengambil string berformat "Rp", melainkan angka aslinya.
    $data = $spreadsheet->getActiveSheet()->toArray(null, true, false, true);
    
    $successCount = 0;
    $errorCount = 0;
    $errors = [];
    $startRow = 2; // Lewati header

    $stmtK = $koneksi->prepare("SELECT id_karyawan FROM tb_karyawan WHERE nik_karyawan = ?");
    $stmtCheck = $koneksi->prepare("SELECT id_potongan_tidak_tetap FROM tb_potongan_tidak_tetap WHERE id_karyawan = ? AND tgl_potongan = ? AND jenis_potongan = ? AND ket_potongan = ?");
    $stmtUpd = $koneksi->prepare("UPDATE tb_potongan_tidak_tetap SET nominal_potongan=? WHERE id_karyawan=? AND tgl_potongan=? AND jenis_potongan=? AND ket_potongan=?");
    $stmtIns = $koneksi->prepare("INSERT INTO tb_potongan_tidak_tetap (id_karyawan, tgl_potongan, nominal_potongan, jenis_potongan, ket_potongan) VALUES (?,?,?,?,?)");

    for ($i = $startRow; $i <= count($data); $i++) {
        $row = $data[$i];
        
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

        // 2. Bersihkan Nominal Menggunakan Pembersih `cleanRupiah`
        $nominal = $row['E'] ?? 0;
        $nominal_potongan = Excel::cleanRupiah($nominal);

        // 3. Konversi Tanggal Potongan (Kolom F)
        $tglPotonganRaw = $row['F'] ?? '';
        $tgl_potongan = null;
        if (!empty($tglPotonganRaw)) {
            if (is_numeric($tglPotonganRaw)) {
                $tgl_potongan = Date::excelToDateTimeObject($tglPotonganRaw)->format('Y-m-d');
            } else {
                $tgl_potongan = date('Y-m-d', strtotime($tglPotonganRaw));
            }
        }

        // 4. Ambil Jenis Potongan & Keterangan (Kolom G & H)
        $jenis_potongan = trim($row['G'] ?? '');
        $ket_potongan = trim($row['H'] ?? '');

        // Abaikan jika tanggal potongan kosong (wajib diisi)
        if(empty($tgl_potongan)) {
             $errors[] = "Baris $i: Tanggal potongan kosong.";
             $errorCount++;
             continue;
        }

        // 5. Cek Duplikat atau Update
        $stmtCheck->execute([$id_karyawan, $tgl_potongan, $jenis_potongan, $ket_potongan]);
        $isDuplicate = (bool)$stmtCheck->fetch();

        if ($isDuplicate) {
            // Jika data sudah ada, Update HANYA nominalnya
            $stmtUpd->execute([$nominal_potongan, $id_karyawan, $tgl_potongan, $jenis_potongan, $ket_potongan]);
        } else {
            // Jika data belum ada, Insert sebagai data baru
            $stmtIns->execute([$id_karyawan, $tgl_potongan, $nominal_potongan, $jenis_potongan, $ket_potongan]);
        }
        $successCount++;
    }

    echo json_encode([
        'status' => ($errorCount > 0 && $successCount == 0) ? 'error' : (($errorCount > 0) ? 'warning' : 'success'),
        'message' => "Selesai. Berhasil: $successCount, Gagal: $errorCount. " . implode(" ", array_slice($errors, 0, 2))
    ]);

} catch (Exception $e) {
    error_log('csv-potongan-tidak-tetap: ' . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'System Error: gagal memproses file.']);
}
?>