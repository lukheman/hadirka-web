<?php
header("Content-Type: application/json; charset=utf-8");
require __DIR__ . '/../../config/koneksi.php';
require '../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

$response = ["success" => false, "berhasil" => 0, "gagal" => 0, "error" => ""];

// Validasi jika file tidak ada
if (!isset($_FILES['file_excel']) || $_FILES['file_excel']['error'] !== UPLOAD_ERR_OK) {
    $response["error"] = "File Excel kosong atau terjadi error saat upload.";
    echo json_encode($response);
    exit;
}

try {
    $file = $_FILES['file_excel']['tmp_name'];
    $spreadsheet = IOFactory::load($file);

    // Format: A=No, B=NIK, C=Nama, D=Tgl, E=Nominal, F=Ket
    $sheet = $spreadsheet->getActiveSheet()->toArray(null, true, false, true);

    // Kunci proses import: Gunakan begin_transaction
    $koneksi->begin_transaction();

    foreach ($sheet as $i => $row) {
        if ($i == 1) continue; // Skip header

        // Tangkap data dan antisipasi null value dari Excel
        $nik = strtoupper(trim($row['B'] ?? ''));
        $tgl_raw = trim($row['D'] ?? '');
        $nominal = preg_replace('/\D/', '', $row['E'] ?? '0');
        $ket = trim($row['F'] ?? '');

        // Jika NIK atau Tanggal kosong, lewati baris ini secara normal (bukan error gagal)
        if (empty($nik) && empty($tgl_raw)) continue;

        // Jika salah satu dari wajib isi ini kosong tapi barisnya ada data lain, berarti format salah
        if (empty($nik) || empty($tgl_raw) || $nominal <= 0) {
            $response["gagal"]++;
            continue;
        }

        // Format tanggal Excel
        $tgl = null;
        if (is_numeric($tgl_raw)) {
            $tgl = Date::excelToDateTimeObject($tgl_raw)->format('Y-m-d H:i:s');
        } else {
            $tgl = date('Y-m-d H:i:s', strtotime(str_replace(['/', '.'], '-', $tgl_raw)));
        }

        if (!$tgl) {
            $response["gagal"]++;
            continue;
        }

        // Cari Karyawan Internal
        $cek = $koneksi->query("SELECT id_karyawan_internal FROM tb_karyawan_internal WHERE nik_karyawan_internal = '$nik' LIMIT 1");
        if ($cek->num_rows == 0) {
            $response["gagal"]++;
            continue;
        }
        $id_karyawan = $cek->fetch_assoc()['id_karyawan_internal'];

        // Masukkan Data
        $stmt = $koneksi->prepare("INSERT INTO tb_bonus_internal (id_karyawan_internal, tgl_bonus, nominal, ket_bonus) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isds", $id_karyawan, $tgl, $nominal, $ket);

        if ($stmt->execute()) {
            $response["berhasil"]++;
        } else {
            $response["gagal"]++;
        }
        $stmt->close();
    }

    $koneksi->commit();
    $response["success"] = true;
} catch (Exception $e) {
    if (isset($koneksi)) $koneksi->rollback();
    $response["error"] = "Gagal memproses file: " . $e->getMessage();
}

echo json_encode($response);
exit;
