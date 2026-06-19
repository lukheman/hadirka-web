<?php
// Matikan output selain JSON untuk mencegah error "Unexpected Token"
error_reporting(0);
ini_set('display_errors', 0);
header("Content-Type: application/json");

require_once __DIR__ . '/../../config/koneksi.php'; 
require '../../../vendor/autoload.php'; 

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

// --- FUNGSI PEMBERSIH FORMAT MATA UANG ---
function cleanRupiah($val) {
    if (empty($val)) return 0;
    
    // Jika dari Excel sudah terbaca sebagai angka murni, langsung return
    if (is_numeric($val)) return floatval($val);
    
    $val = (string)$val;
    // Hapus karakter huruf (Rp, rp) dan spasi
    $val = preg_replace('/[a-zA-Z\s]/', '', $val);
    
    // Deteksi pemisah ribuan dan desimal (Contoh: 36.881,30)
    if (strpos($val, ',') !== false && strpos($val, '.') !== false) {
        // Jika koma berada setelah titik, berarti koma adalah desimal (Format Indo)
        if (strrpos($val, ',') > strrpos($val, '.')) {
            $val = str_replace('.', '', $val); // hapus titik ribuan
            $val = str_replace(',', '.', $val); // jadikan koma sebagai desimal
        } else {
            // Jika format English (Contoh: 36,881.30)
            $val = str_replace(',', '', $val); // hapus koma ribuan
        }
    } elseif (strpos($val, ',') !== false) {
        // Jika cuma ada koma (Contoh: 36881,30), asumsikan format Indo (koma = desimal)
        $val = str_replace(',', '.', $val);
    }
    
    // Konversi akhir ke tipe Float
    return floatval($val);
}

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

    for ($i = $startRow; $i <= count($data); $i++) {
        $row = $data[$i];
        
        $nik = trim($row['B'] ?? '');
        if (empty($nik)) continue;

        // 1. Cari ID Karyawan
        $stmtK = $koneksi->prepare("SELECT id_karyawan FROM tb_karyawan WHERE nik_karyawan = ?");
        $stmtK->bind_param("s", $nik);
        $stmtK->execute();
        $resK = $stmtK->get_result()->fetch_assoc();
        $stmtK->close();

        if (!$resK) {
            $errors[] = "Baris $i: NIK $nik tidak ada di database.";
            $errorCount++;
            continue;
        }

        $id_karyawan = $resK['id_karyawan'];

        // 2. Bersihkan Nominal Menggunakan Pembersih `cleanRupiah`
        $nominal = $row['E'] ?? 0;
        $nominal_potongan = cleanRupiah($nominal);

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
        $stmtCheck = $koneksi->prepare("SELECT id_potongan_tidak_tetap FROM tb_potongan_tidak_tetap WHERE id_karyawan = ? AND tgl_potongan = ? AND jenis_potongan = ? AND ket_potongan = ?");
        $stmtCheck->bind_param("isss", $id_karyawan, $tgl_potongan, $jenis_potongan, $ket_potongan);
        $stmtCheck->execute();
        $resCheck = $stmtCheck->get_result();
        $isDuplicate = $resCheck->num_rows > 0;
        $stmtCheck->close();

        if ($isDuplicate) {
            // Jika data sudah ada, Update HANYA nominalnya
            $stmtUpd = $koneksi->prepare("UPDATE tb_potongan_tidak_tetap SET nominal_potongan=? WHERE id_karyawan=? AND tgl_potongan=? AND jenis_potongan=? AND ket_potongan=?");
            $stmtUpd->bind_param("disss", $nominal_potongan, $id_karyawan, $tgl_potongan, $jenis_potongan, $ket_potongan);
            $stmtUpd->execute();
            $stmtUpd->close();
        } else {
            // Jika data belum ada, Insert sebagai data baru
            $stmtIns = $koneksi->prepare("INSERT INTO tb_potongan_tidak_tetap (id_karyawan, tgl_potongan, nominal_potongan, jenis_potongan, ket_potongan) VALUES (?,?,?,?,?)");
            $stmtIns->bind_param("isdss", $id_karyawan, $tgl_potongan, $nominal_potongan, $jenis_potongan, $ket_potongan);
            $stmtIns->execute();
            $stmtIns->close();
        }
        $successCount++;
    }

    echo json_encode([
        'status' => ($errorCount > 0 && $successCount == 0) ? 'error' : (($errorCount > 0) ? 'warning' : 'success'),
        'message' => "Selesai. Berhasil: $successCount, Gagal: $errorCount. " . implode(" ", array_slice($errors, 0, 2))
    ]);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'System Error: ' . $e->getMessage()]);
}
?>