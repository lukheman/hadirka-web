<?php
header("Content-Type: application/json; charset=utf-8");
require __DIR__ . '/../../config/koneksi.php';
require '../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$response = ["success" => false, "berhasil" => 0, "gagal" => 0, "errors" => []];

if (!isset($_FILES['file_excel'])) {
    echo json_encode(["success" => false, "error" => "File Excel kosong!"]);
    exit;
}

try {
    $file = $_FILES['file_excel']['tmp_name'];
    $spreadsheet = IOFactory::load($file);
    // Kolom Excel: A=NIK Karyawan, B=Tanggal(YYYY-MM-DD), C=Nominal, D=Keterangan
    $sheet = $spreadsheet->getActiveSheet()->toArray(null, true, false, true);

    foreach ($sheet as $i => $row) {
        if ($i == 1) continue; // Skip header

        $nik_excel   = strtoupper(trim($row['A'] ?? ''));
        $tgl_bonus   = trim($row['B'] ?? '');
        $nominal     = preg_replace('/\D/', '', $row['C'] ?? '0');
        $ket_bonus   = trim($row['D'] ?? '');

        if(empty($nik_excel)) continue;

        // Cari ID Karyawan menggunakan NIK (Aman & Akurat)
        $cek = $koneksi->query("SELECT id_karyawan_internal FROM tb_karyawan_internal WHERE nik_karyawan_internal = '$nik_excel' LIMIT 1");
        if($cek->num_rows == 0) {
            $response["gagal"]++;
            $response["errors"][] = "Baris $i: NIK '$nik_excel' tidak ditemukan.";
            continue;
        }
        $id_karyawan = $cek->fetch_assoc()['id_karyawan_internal'];

        if($nominal <= 0 || empty($tgl_bonus)){
            $response["gagal"]++;
            $response["errors"][] = "Baris $i: Tanggal/Nominal tidak valid.";
            continue;
        }

        $stmt = $koneksi->prepare("INSERT INTO tb_bonus_internal (id_karyawan_internal, tgl_bonus, nominal, ket_bonus) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isds", $id_karyawan, $tgl_bonus, $nominal, $ket_bonus);
        if($stmt->execute()) $response["berhasil"]++;
        else $response["gagal"]++;
        $stmt->close();
    }
    $response["success"] = true;
} catch (Exception $e) {
    $response["error"] = $e->getMessage();
}

echo json_encode($response);
exit;