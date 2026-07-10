<?php
header("Content-Type: application/json; charset=utf-8");
require __DIR__ . '/../../config/koneksi.php';
require '../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

$response = ["success" => false, "berhasil" => 0, "gagal" => 0, "error" => ""];
if (!isset($_FILES['file_excel'])) {
    echo json_encode(["success" => false, "error" => "File kosong"]);
    exit;
}

function formatTgl($val)
{
    if (empty($val)) return null;
    if (is_numeric($val)) return Date::excelToDateTimeObject($val)->format('Y-m-d');
    return date('Y-m-d', strtotime(str_replace(['/', '.'], '-', $val)));
}

try {
    $spreadsheet = IOFactory::load($_FILES['file_excel']['tmp_name']);
    // Urutan: A=No, B=NIK, C=Nama, D=L/P, E=Site, F=Jabatan, G=Gaji, H=Bank, I=No Rek, J=Atas Nama, K=Status, L=Tgl Masuk, M=Tgl Keluar, N=Ket Keluar
    $sheet = $spreadsheet->getActiveSheet()->toArray(null, true, false, true);

    foreach ($sheet as $i => $row) {
        if ($i == 1) continue;
        $nik = strtoupper(trim($row['B'] ?? ''));
        $nama = trim($row['C'] ?? '');
        $jk = trim($row['D'] ?? '');
        $nama_site = trim($row['E'] ?? '');

        if (empty($nik) || empty($nama)) continue;

        $id_site = null;
        if (!empty($nama_site)) {
            $cekSite = $koneksi->query("SELECT id_site FROM tb_site WHERE LOWER(TRIM(nama_site)) = '" . strtolower($nama_site) . "' LIMIT 1");
            if ($cekSite->num_rows > 0) $id_site = $cekSite->fetch_assoc()['id_site'];
        }

        $jabatan = trim($row['F'] ?? '');
        $gaji = preg_replace('/\D/', '', $row['G'] ?? '0');
        $bank = strtoupper(trim($row['H'] ?? ''));
        $rek = trim($row['I'] ?? '');
        $an = strtoupper(trim($row['J'] ?? ''));
        $status = trim($row['K'] ?? '') ?: 'Aktif';
        $tgl_masuk = formatTgl($row['L'] ?? '');
        $tgl_keluar = formatTgl($row['M'] ?? '');
        $ket_keluar = trim($row['N'] ?? '');

        $cek = $koneksi->query("SELECT id_karyawan_internal FROM tb_karyawan_internal WHERE nik_karyawan_internal='$nik'");
        if ($cek->num_rows > 0) {
            $stmt = $koneksi->prepare("UPDATE tb_karyawan_internal SET id_site=?, nama_karyawan=?, jenis_kelamin=?, jabatan=?, gaji_pokok=?, nama_bank=?, no_rek=?, atas_nama_rek=?, status_karyawan=?, tgl_masuk=?, tgl_keluar=?, ket_keluar=? WHERE nik_karyawan_internal=?");
            // 1 int + 12 strings = issssssssssss (13 vars)
            $stmt->bind_param("issssssssssss", $id_site, $nama, $jk, $jabatan, $gaji, $bank, $rek, $an, $status, $tgl_masuk, $tgl_keluar, $ket_keluar, $nik);
        } else {
            $stmt = $koneksi->prepare("INSERT INTO tb_karyawan_internal (nik_karyawan_internal, id_site, nama_karyawan, jenis_kelamin, jabatan, gaji_pokok, nama_bank, no_rek, atas_nama_rek, status_karyawan, tgl_masuk, tgl_keluar, ket_keluar) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)");
            // 1 string + 1 int + 11 strings = sisssssssssss (13 vars)
            $stmt->bind_param("sisssssssssss", $nik, $id_site, $nama, $jk, $jabatan, $gaji, $bank, $rek, $an, $status, $tgl_masuk, $tgl_keluar, $ket_keluar);
        }
        if ($stmt->execute()) $response["berhasil"]++;
        else $response["gagal"]++;
    }
    $response["success"] = true;
} catch (Exception $e) {
    $response["error"] = $e->getMessage();
}

echo json_encode($response);
