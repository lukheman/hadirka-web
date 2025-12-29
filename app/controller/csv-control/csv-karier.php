<?php
error_reporting(0);
ob_clean();

header("Content-Type: application/json");
require '../../config/koneksi.php';
require '../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$response = ["success" => false, "berhasil" => 0, "gagal" => 0, "errors" => []];

if (!isset($_FILES['file_excel'])) {
    $response["error"] = "File belum dipilih";
    echo json_encode($response); exit;
}

$file = $_FILES['file_excel']['tmp_name'];
$ext = strtolower(pathinfo($_FILES['file_excel']['name'], PATHINFO_EXTENSION));

if (!in_array($ext, ['xls', 'xlsx'])) {
    $response["error"] = "Format tidak didukung. Hanya XLS/XLSX";
    echo json_encode($response); exit;
}

try {
    $spreadsheet = IOFactory::load($file);
    $sheet = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

    foreach ($sheet as $i => $row) {
        if ($i == 1) continue; 

        $nik = trim($row['B'] ?? '');
        if (empty($nik)) continue;

        // 1. Ambil Data Karyawan (Cek Departemen/Jabatan untuk backup insert karier baru)
        $cekKaryawan = $koneksi->query("SELECT id_karyawan FROM tb_karyawan WHERE nik_karyawan='$nik'");
        if ($cekKaryawan->num_rows == 0) {
            $response["gagal"]++;
            $response["errors"][] = "Row $i : NIK '$nik' tidak terdaftar";
            continue;
        }
        $dataKaryawan = $cekKaryawan->fetch_assoc();
        $id_karyawan = $dataKaryawan['id_karyawan'];

        // Ambil Nilai dari Excel
        $basic      = intval($row['E'] ?? 0);
        $t_daerah   = intval($row['F'] ?? 0);
        $t_hidup    = intval($row['G'] ?? 0);
        $t_makan    = intval($row['H'] ?? 0);
        $t_skill    = intval($row['I'] ?? 0);
        $p_bonus    = intval($row['J'] ?? 0);
        $ot_pertama = intval($row['K'] ?? 0);
        $ot_kedua   = intval($row['L'] ?? 0);
        $spl        = intval($row['M'] ?? 0);
        $ot1_spl    = intval($row['N'] ?? 0);
        $ot2_spl    = intval($row['O'] ?? 0);
        $pot_bpjs   = intval($row['P'] ?? 0);
        $pot_jht    = intval($row['Q'] ?? 0);
        $pph21      = intval($row['R'] ?? 0);
        $pot_izin   = intval($row['S'] ?? 0);
        $pot_mangkir= intval($row['T'] ?? 0);

        // 2. Kelola tb_karier
        $cekKarier = $koneksi->query("SELECT id_karier FROM tb_karier WHERE id_karyawan='$id_karyawan' ORDER BY tgl_mulai DESC, id_karier DESC LIMIT 1");

        if ($cekKarier->num_rows > 0) {
            $id_karier = $cekKarier->fetch_assoc()['id_karier'];
            // Update Karier Terakhir
            $koneksi->query("UPDATE tb_karier SET 
                gaji_pokok=$basic, ot_pertama=$ot_pertama, ot_kedua=$ot_kedua, 
                spl=$spl, ot1_spl=$ot1_spl, ot2_spl=$ot2_spl,
                pot_izin=$pot_izin, pot_mangkir=$pot_mangkir
                WHERE id_karier='$id_karier'");
        } else {
            // Insert Baru jika belum ada data karier sama sekali
            // Catatan: Pastikan kolom id_site, id_departemen, dll di database mengizinkan NULL atau beri nilai default 0
            $koneksi->query("INSERT INTO tb_karier (
                id_karyawan, gaji_pokok, ot_pertama, ot_kedua, spl, ot1_spl, ot2_spl, 
                pot_izin, pot_mangkir, tgl_mulai
            ) VALUES (
                '$id_karyawan', $basic, $ot_pertama, $ot_kedua, $spl, $ot1_spl, $ot2_spl, 
                $pot_izin, $pot_mangkir, NOW()
            )");
            $id_karier = $koneksi->insert_id;
        }

        // 3. Kelola tb_tunjangan (Upsert Logic)
        $listTunjangan = [
            "Tunjangan Makan" => $t_makan,
            "Tunjangan Skil" => $t_skill,
            "Tunjangan Hidup" => $t_hidup,
            "Tunjangan Daerah" => $t_daerah,
            "Performance Bonus" => $p_bonus
        ];

        foreach ($listTunjangan as $nama_t => $nilai) {
            $cekTun = $koneksi->query("SELECT id_tunjangan FROM tb_tunjangan WHERE id_karier='$id_karier' AND nama_tunjangan='$nama_t'");
            if ($cekTun->num_rows > 0) {
                $koneksi->query("UPDATE tb_tunjangan SET jumlah_tunjangan=$nilai WHERE id_karier='$id_karier' AND nama_tunjangan='$nama_t'");
            } else {
                $koneksi->query("INSERT INTO tb_tunjangan (id_karier, nama_tunjangan, jumlah_tunjangan) VALUES ('$id_karier', '$nama_t', $nilai)");
            }
        }

        // 4. Kelola tb_potongan (Upsert Logic)
        $listPotongan = [
            "BPJS KESEHATAN 1%" => $pot_bpjs,
            "JHT 2%" => $pot_jht,
            "PPH21" => $pph21
        ];

        foreach ($listPotongan as $jenis_p => $nilai_p) {
            $cekPot = $koneksi->query("SELECT id_potongan FROM tb_potongan WHERE id_karier='$id_karier' AND jenis_potongan='$jenis_p'");
            if ($cekPot->num_rows > 0) {
                $koneksi->query("UPDATE tb_potongan SET jumlah_potongan=$nilai_p WHERE id_karier='$id_karier' AND jenis_potongan='$jenis_p'");
            } else {
                $koneksi->query("INSERT INTO tb_potongan (id_karier, jenis_potongan, jumlah_potongan) VALUES ('$id_karier', '$jenis_p', $nilai_p)");
            }
        }

        $response["berhasil"]++;
    }

    $response["success"] = true;
} catch (Exception $e) {
    $response["error"] = "Sistem Error: " . $e->getMessage();
}

echo json_encode($response);