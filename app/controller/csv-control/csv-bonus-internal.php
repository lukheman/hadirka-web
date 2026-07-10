<?php
header("Content-Type: application/json; charset=utf-8");
require __DIR__ . '/../../config/koneksi.php';
require '../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

$response = ["success" => false, "berhasil" => 0, "gagal" => 0, "error" => ""];
if (!isset($_FILES['file_excel'])) {
    echo json_encode($response);
    exit;
}

try {
    $spreadsheet = IOFactory::load($_FILES['file_excel']['tmp_name']);
    // Format: A=No, B=NIK, C=Nama, D=Tgl, E=Nominal, F=Ket
    $sheet = $spreadsheet->getActiveSheet()->toArray(null, true, false, true);

    foreach ($sheet as $i => $row) {
        if ($i == 1) continue;
        $nik = strtoupper(trim($row['B'] ?? ''));
        $tgl = trim($row['D'] ?? '');
        $nominal = preg_replace('/\D/', '', $row['E'] ?? '0');
        $ket = trim($row['F'] ?? '');

        if (empty($nik) || empty($tgl) || $nominal <= 0) continue;

        if (is_numeric($tgl)) $tgl = Date::excelToDateTimeObject($tgl)->format('Y-m-d');
        else $tgl = date('Y-m-d', strtotime(str_replace(['/', '.'], '-', $tgl)));

        $cek = $koneksi->query("SELECT id_karyawan_internal FROM tb_karyawan_internal WHERE nik_karyawan_internal = '$nik' LIMIT 1");
        if ($cek->num_rows == 0) {
            $response["gagal"]++;
            continue;
        }
        $id_karyawan = $cek->fetch_assoc()['id_karyawan_internal'];

        $stmt = $koneksi->prepare("INSERT INTO tb_bonus_internal (id_karyawan_internal, tgl_bonus, nominal, ket_bonus) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isds", $id_karyawan, $tgl, $nominal, $ket);
        if ($stmt->execute()) $response["berhasil"]++;
        else $response["gagal"]++;
    }
    $response["success"] = true;
} catch (Exception $e) {
    $response["error"] = $e->getMessage();
}
echo json_encode($response);
