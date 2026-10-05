<?php
header("Content-Type: application/json");

// Sesuaikan struktur direktori koneksi dan vendor Anda
require __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../../config/Excel.php'; // Excel::esc()/parseDate() terpusat
require '../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$response = [
    "success" => false,
    "berhasil" => 0,
    "gagal" => 0,
    "errors" => [],
    "error" => ""
];

if (!isset($_FILES['file_excel']) || $_FILES['file_excel']['error'] !== UPLOAD_ERR_OK) {
    $response["error"] = "File Excel belum dipilih atau terjadi error upload.";
    echo json_encode($response);
    exit;
}

$file = $_FILES['file_excel']['tmp_name'];
$file_ext = strtolower(pathinfo($_FILES['file_excel']['name'], PATHINFO_EXTENSION));

if (!in_array($file_ext, ['xls', 'xlsx'])) {
    $response["error"] = "Format file tidak didukung. Harus berformat XLS atau XLSX.";
    echo json_encode($response);
    exit;
}

try {
    $spreadsheet = IOFactory::load($file);
    $sheet = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

    $koneksi->beginTransaction();

    $stmtKaryawan = $koneksi->prepare("SELECT id_karyawan FROM tb_karyawan WHERE nik_karyawan = ?");
    $stmtApd = $koneksi->prepare("SELECT id_apd FROM master_apd WHERE LOWER(nama_apd) = LOWER(?) AND status_aktif = 'aktif'");
    $stmtInsert = $koneksi->prepare("INSERT INTO pengambilan_apd (id_karyawan, id_apd, tanggal_pengambilan, jumlah) VALUES (?, ?, ?, ?)");
    
    foreach ($sheet as $rowNum => $row) {
        if ($rowNum == 1) continue; // Skip baris pertama (Header)

        // === Mapping data kolom Excel sesuai format baru Anda ===
        // A: No (Abaikan)
        // B: ID/NIK Karyawan
        // C: Site (Abaikan saja saat import, hanya untuk visual admin)
        // D: Jabatan (Abaikan saja saat import, hanya untuk visual admin)
        // E: Nama APD
        // F: Tanggal Pengambilan
        // G: QTY

        $nik_karyawan = Excel::esc($koneksi, $row['B'] ?? '');
        $nama_apd     = Excel::esc($koneksi, $row['F'] ?? '');
        $tanggal      = Excel::parseDate($row['G'] ?? '');
        $jumlah       = (int)($row['H'] ?? 0);

        // Abaikan jika baris benar-benar kosong
        if (empty($nik_karyawan) && empty($nama_apd)) {
            continue;
        }

        // Validasi kelengkapan data per baris
        if (empty($nik_karyawan) || empty($nama_apd) || empty($tanggal) || $jumlah <= 0) {
            $response["gagal"]++;
            $response["errors"][] = "Baris $rowNum: NIK, Nama APD, Tanggal, atau Jumlah tidak lengkap/valid.";
            continue;
        }

        try {
            // 1. Cari ID Karyawan berdasarkan NIK
            $stmtKaryawan->execute([$nik_karyawan]);
            $rowKaryawan = $stmtKaryawan->fetch();
            if (!$rowKaryawan) {
                throw new Exception("NIK Karyawan '$nik_karyawan' tidak ditemukan.");
            }
            $id_karyawan = $rowKaryawan['id_karyawan'];

            // 2. Cari ID APD berdasarkan Nama APD (Case Insensitive)
            $stmtApd->execute([$nama_apd]);
            $rowApd = $stmtApd->fetch();
            if (!$rowApd) {
                throw new Exception("APD '$nama_apd' tidak ditemukan atau tidak aktif.");
            }
            $id_apd = $rowApd['id_apd'];

            // 3. Simpan ke Database
            try {
                $stmtInsert->execute([$id_karyawan, $id_apd, $tanggal, $jumlah]);
            } catch (PDOException $e) {
                error_log('csv-transaksi-apd insert: ' . $e->getMessage());
                throw new Exception("Gagal menyimpan data pengambilan APD.");
            }

            $response["berhasil"]++;
        } catch (Exception $e) {
            $response["gagal"]++;
            $response["errors"][] = "Baris $rowNum: " . $e->getMessage();
        }
    }

    $koneksi->commit();
    $response["success"] = true;

} catch (Exception $e) {
    error_log('csv-transaksi-apd: ' . $e->getMessage());
    if ($koneksi->inTransaction()) $koneksi->rollBack();
    $response["error"] = "Sistem gagal memproses file Excel.";
}

echo json_encode($response);
exit;