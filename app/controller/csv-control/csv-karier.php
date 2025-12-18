<?php
error_reporting(0);
ob_clean();

header("Content-Type: application/json");
require '../../config/koneksi.php';
require '../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$response = [
    "success" => false,
    "berhasil" => 0,
    "gagal" => 0,
    "errors" => []
];

if (!isset($_FILES['file_excel'])) {
    $response["error"] = "File belum dipilih";
    echo json_encode($response);
    exit;
}

$file = $_FILES['file_excel']['tmp_name'];
$ext = strtolower(pathinfo($_FILES['file_excel']['name'], PATHINFO_EXTENSION));

if (!in_array($ext, ['xls', 'xlsx'])) {
    $response["error"] = "Format tidak didukung. Hanya XLS/XLSX";
    echo json_encode($response);
    exit;
}

try {
    $sheet = IOFactory::load($file)->getActiveSheet()->toArray(null, true, true, true);

    foreach ($sheet as $i => $row) {
        if ($i == 1) continue;

        $nik = trim($row['B'] ?? '');
        if ($nik == '') {
            $response["gagal"]++;
            $response["errors"][] = "Row $i : NIK kosong";
            continue;
        }

        // Payroll Base
        $basic      = intval($row['E'] ?? 0);
        $t_daerah   = intval($row['F'] ?? 0);
        $t_hidup    = intval($row['G'] ?? 0);
        $t_makan    = intval($row['H'] ?? 0);
        $t_skill    = intval($row['I'] ?? 0);

        // SPL & OT
        $ot_pertama = intval($row['J'] ?? 0);
        $ot_kedua   = intval($row['K'] ?? 0);

        $spl        = intval($row['L'] ?? 0);
        $ot1_spl    = intval($row['M'] ?? 0);
        $ot2_spl    = intval($row['N'] ?? 0);

        // Potongan & Pajak
        $pot_bpjs   = intval($row['O'] ?? 0);
        $pot_jht    = intval($row['P'] ?? 0);
        $pph21      = intval($row['Q'] ?? 0);
        $pot_izin   = intval($row['S'] ?? 0);
        $pot_mangkir= intval($row['T'] ?? 0);

        // Pastikan karyawan ada
        $cek = $koneksi->query("SELECT id_karyawan FROM tb_karyawan WHERE nik_karyawan='$nik'");
        if ($cek->num_rows == 0) {
            $response["gagal"]++;
            $response["errors"][] = "Row $i : Karyawan dengan NIK '$nik' tidak ditemukan";
            continue;
        }
        $id_karyawan = $cek->fetch_assoc()['id_karyawan'];

        // Cek karier terbaru
        $cekKarier = $koneksi->query("SELECT id_karier 
            FROM tb_karier
            WHERE id_karyawan='$id_karyawan'
            ORDER BY tgl_mulai DESC, id_karier DESC
            LIMIT 1");

        if ($cekKarier->num_rows == 0) {
            // INSERT jika belum punya karier
            $sql = "INSERT INTO tb_karier (
                id_karyawan, gaji_pokok, t_daerah, t_hidup, t_makan, t_skil,
                ot_pertama, ot_kedua, spl, ot1_spl, ot2_spl,
                pot_izin, pot_mangkir, tgl_mulai
            ) VALUES (
                '$id_karyawan',$basic,$t_daerah,$t_hidup,$t_makan,$t_skill,
                $ot_pertama,$ot_kedua,$spl,$ot1_spl,$ot2_spl,
                $pot_izin,$pot_mangkir,NOW()
            )";
            $koneksi->query($sql);
            $id_karier = $koneksi->insert_id;
        } else {
            // UPDATE jika sudah ada
            $id_karier = $cekKarier->fetch_assoc()['id_karier'];
            $sql = "UPDATE tb_karier SET
                gaji_pokok=$basic,
                t_daerah=$t_daerah,
                t_hidup=$t_hidup,
                t_makan=$t_makan,
                t_skil=$t_skill,
                ot_pertama=$ot_pertama,
                ot_kedua=$ot_kedua,
                spl=$spl,
                ot1_spl=$ot1_spl,
                ot2_spl=$ot2_spl,
                pot_izin=$pot_izin,
                pot_mangkir=$pot_mangkir
            WHERE id_karier='$id_karier'";
            $koneksi->query($sql);
        }

        // Tunjangan wajib
        $tunjanganList = [
            "BPJS KESEHATAN 1%" => $pot_bpjs,
            "JHT 2%" => $pot_jht,
            "PPH21" => $pph21,
        ];

        foreach ($tunjanganList as $nama_t => $jumlah) {
            $cekTun = $koneksi->query("SELECT id_tunjangan FROM tb_tunjangan 
                WHERE id_karier='$id_karier' AND nama_tunjangan='$nama_t'");
            
            if ($cekTun->num_rows == 0) {
                $koneksi->query("INSERT INTO tb_tunjangan 
                    (id_karier, nama_tunjangan, jumlah_tunjangan)
                    VALUES ('$id_karier','$nama_t',$jumlah)");
            } else {
                $id_tunjangan = $cekTun->fetch_assoc()['id_tunjangan'];
                $koneksi->query("UPDATE tb_tunjangan 
                    SET jumlah_tunjangan=$jumlah
                    WHERE id_tunjangan='$id_tunjangan'");
            }
        }

        $response["berhasil"]++;
    }

    $response["success"] = true;

} catch (Exception $e) {
    $response["error"] = $e->getMessage();
}

echo json_encode($response);
exit;
