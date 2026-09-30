<?php
// Endpoint pendamping import IPIP: membaca daftar sheet dalam file Excel
// yang dipilih user, agar user bisa memilih halaman yang diproses.
// Dipakai oleh dropdown sheet di ModalImportIPIP (view/master-data/data-karyawan.php).
// Request : POST multipart, field `file_excel` (xls/xlsx).
// Response: {success, sheets: [nama...], active: nama sheet aktif} atau {success:false, error}.

header("Content-Type: application/json");

require __DIR__ . '/../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$response = ["success" => false, "sheets" => [], "active" => "", "error" => ""];

if (!isset($_FILES['file_excel']) || $_FILES['file_excel']['error'] !== UPLOAD_ERR_OK) {
    $response["error"] = "File Excel belum dipilih atau terjadi error saat upload.";
    echo json_encode($response);
    exit;
}

$file = $_FILES['file_excel']['tmp_name'];
$file_ext = strtolower(pathinfo($_FILES['file_excel']['name'], PATHINFO_EXTENSION));

if (!in_array($file_ext, ['xls', 'xlsx'])) {
    $response["error"] = "Format file tidak didukung. Harus XLS atau XLSX.";
    echo json_encode($response);
    exit;
}

try {
    // Cukup baca daftar nama sheet tanpa memuat seluruh isi (ringan).
    $reader = IOFactory::createReaderForFile($file);
    $names = $reader->listWorksheetNames($file);

    // Nama sheet aktif perlu load ringan; bila gagal, pakai sheet pertama.
    $active = $names[0] ?? '';
    try {
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($file);
        $active = $spreadsheet->getActiveSheet()->getTitle();
        $spreadsheet->disconnectWorksheets();
    } catch (Exception $e) {
        // abaikan, $active sudah diisi sheet pertama
    }

    $response["success"] = true;
    $response["sheets"] = array_values($names);
    $response["active"] = $active;
} catch (Exception $e) {
    $response["error"] = "Gagal membaca file Excel: " . $e->getMessage();
}

echo json_encode($response);
exit;
