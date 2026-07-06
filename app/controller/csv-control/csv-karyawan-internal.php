<?php
header("Content-Type: application/json; charset=utf-8");
require __DIR__ . '/../../config/koneksi.php';
require '../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$response = ["success" => false, "berhasil" => 0, "gagal" => 0, "error" => ""];

if (!isset($_FILES['file_excel'])) {
    echo json_encode(["success" => false, "error" => "File dokumen Excel belum dipilih"]);
    exit;
}

try {
    $file = $_FILES['file_excel']['tmp_name'];
    $spreadsheet = IOFactory::load($file);
    // Kolom Excel: A=NIK, B=Nama, C=Jabatan, D=Gaji Pokok, E=Bank, F=No Rek, G=Atas Nama, H=Nama Site
    $sheet = $spreadsheet->getActiveSheet()->toArray(null, true, false, true);

    foreach ($sheet as $i => $row) {
        if ($i == 1) continue;

        $nik       = strtoupper(trim($row['A'] ?? ''));
        $nama      = trim($row['B'] ?? '');
        $jabatan   = trim($row['C'] ?? '');
        $gaji      = preg_replace('/\D/', '', $row['D'] ?? '0');
        $bank      = !empty($row['E']) ? strtoupper(trim($row['E'])) : null;
        $rek       = !empty($row['F']) ? trim($row['F']) : null;
        $an        = !empty($row['G']) ? strtoupper(trim($row['G'])) : null;
        $nama_site = trim($row['H'] ?? '');

        if (empty($nik) || empty($nama) || empty($nama_site)) continue;

        // Cari ID Site berdasarkan Nama Site yang diinput di Excel
        $id_site = null;
        $site_clean = mysqli_real_escape_string($koneksi, strtolower($nama_site));
        $cekSite = $koneksi->query("SELECT id_site FROM tb_site WHERE LOWER(TRIM(nama_site)) = '$site_clean' LIMIT 1");
        if ($cekSite->num_rows > 0) {
            $id_site = $cekSite->fetch_assoc()['id_site'];
        } else {
            $response["gagal"]++;
            continue; // Skip baris jika nama site tidak ada di database
        }

        // Upsert Logic
        $cek = $koneksi->query("SELECT id_karyawan_internal FROM tb_karyawan_internal WHERE nik_karyawan_internal = '$nik' LIMIT 1");
        if ($cek->num_rows > 0) {
            $id_exist = $cek->fetch_assoc()['id_karyawan_internal'];
            $stmt = $koneksi->prepare("UPDATE tb_karyawan_internal SET id_site=?, nama_karyawan=?, jabatan=?, gaji_pokok=?, nama_bank=?, no_rek=?, atas_nama_rek=? WHERE id_karyawan_internal=?");
            $stmt->bind_param("issssssi", $id_site, $nama, $jabatan, $gaji, $bank, $rek, $an, $id_exist);
        } else {
            $stmt = $koneksi->prepare("INSERT INTO tb_karyawan_internal (nik_karyawan_internal, id_site, nama_karyawan, jabatan, gaji_pokok, nama_bank, no_rek, atas_nama_rek) VALUES (?,?,?,?,?,?,?,?)");
            $stmt->bind_param("sissssss", $nik, $id_site, $nama, $jabatan, $gaji, $bank, $rek, $an);
        }

        if ($stmt->execute()) $response["berhasil"]++;
        else $response["gagal"]++;
        $stmt->close();
    }
    $response["success"] = true;
} catch (Exception $e) {
    $response["error"] = "Gagal membaca berkas: " . $e->getMessage();
}

echo json_encode($response);
exit;
