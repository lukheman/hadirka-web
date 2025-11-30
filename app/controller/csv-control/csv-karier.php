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

        // ================================
        //  MAPPING EXCEL
        // ================================

        $basic      = intval($row['E'] ?? 0);

        // Tunjangan Tetap (HARUS MASUK tb_tunjangan)
        $t_daerah   = intval($row['F'] ?? 0);
        $t_hidup    = intval($row['G'] ?? 0);
        $t_makan    = intval($row['H'] ?? 0);
        $t_skill    = intval($row['I'] ?? 0);
        $p_bonus    = intval($row['J'] ?? 0);

        // SPL & OT
        $ot_pertama = intval($row['K'] ?? 0);
        $ot_kedua   = intval($row['L'] ?? 0);

        $spl        = intval($row['M'] ?? 0);
        $ot1_spl    = intval($row['N'] ?? 0);
        $ot2_spl    = intval($row['O'] ?? 0);

        // POTONGAN (HARUS MASUK tb_potongan)
        $pot_bpjs   = intval($row['P'] ?? 0);
        $pot_jht    = intval($row['Q'] ?? 0);
        $pph21      = intval($row['R'] ?? 0);

        $pot_izin   = intval($row['T'] ?? 0);
        $pot_mangkir= intval($row['U'] ?? 0);

        // ================================
        //  CEK KARYAWAN ADA?
        // ================================

        $cek = $koneksi->query("SELECT id_karyawan FROM tb_karyawan WHERE nik_karyawan='$nik'");
        if ($cek->num_rows == 0) {
            $response["gagal"]++;
            $response["errors"][] = "Row $i : Karyawan dengan NIK '$nik' tidak ditemukan";
            continue;
        }
        $id_karyawan = $cek->fetch_assoc()['id_karyawan'];

        // ================================
        // CEK KARIER TERBARU
        // ================================

        $cekKarier = $koneksi->query("
            SELECT id_karier 
            FROM tb_karier
            WHERE id_karyawan='$id_karyawan'
            ORDER BY tgl_mulai DESC, id_karier DESC
            LIMIT 1
        ");

        if ($cekKarier->num_rows == 0) {

            // INSERT KARIER BARU
            $sql = "INSERT INTO tb_karier (
                id_karyawan, gaji_pokok, 
                ot_pertama, ot_kedua, spl, ot1_spl, ot2_spl,
                pot_izin, pot_mangkir, tgl_mulai
            ) VALUES (
                '$id_karyawan',$basic,
                $ot_pertama,$ot_kedua,$spl,$ot1_spl,$ot2_spl,
                $pot_izin,$pot_mangkir,NOW()
            )";

            $koneksi->query($sql);
            $id_karier = $koneksi->insert_id;

        } else {

            // UPDATE KARIER EXISTING
            $id_karier = $cekKarier->fetch_assoc()['id_karier'];

            $sql = "UPDATE tb_karier SET
                gaji_pokok=$basic,
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

        // ================================
        //  INSERT / UPDATE TUNJANGAN TETAP
        // ================================

        $tunjanganTetap = [
            "TUNJANGAN DAERAH" => $t_daerah,
            "TUNJANGAN HIDUP"  => $t_hidup,
            "TUNJANGAN MAKAN"  => $t_makan,
            "TUNJANGAN SKILL"  => $t_skill,
            "PERFORMANCE BONUS"  => $p_bonus,
        ];

        foreach ($tunjanganTetap as $nama => $nominal) {
            if ($nominal <= 0) continue;

            $cekTun = $koneksi->query("SELECT id_tunjangan FROM tb_tunjangan 
                WHERE id_karier='$id_karier' AND nama_tunjangan='$nama'");

            if ($cekTun->num_rows == 0) {
                $koneksi->query("INSERT INTO tb_tunjangan
                    (id_karier, nama_tunjangan, nominal_tunjangan)
                    VALUES ('$id_karier','$nama',$nominal)");
            } else {
                $id_tunj = $cekTun->fetch_assoc()['id_tunjangan'];
                $koneksi->query("UPDATE tb_tunjangan SET nominal_tunjangan=$nominal 
                    WHERE id_tunjangan='$id_tunj'");
            }
        }

        // ================================
        //  INSERT / UPDATE POTONGAN WAJIB
        // ================================

        $potonganList = [
            "BPJS KESEHATAN 1%" => $pot_bpjs,
            "JHT 2%" => $pot_jht,
            "PPH21" => $pph21
        ];

        foreach ($potonganList as $nama => $jumlah) {
            if ($jumlah <= 0) continue;

            $cekPot = $koneksi->query("SELECT id_potongan FROM tb_potongan
                WHERE id_karier='$id_karier' AND jenis_potongan='$nama'");

            if ($cekPot->num_rows == 0) {
                $koneksi->query("INSERT INTO tb_potongan
                    (id_karier, jenis_potongan, jumlah_potongan)
                    VALUES ('$id_karier','$nama',$jumlah)");
            } else {
                $id_pot = $cekPot->fetch_assoc()['id_potongan'];
                $koneksi->query("UPDATE tb_potongan SET jumlah_potongan=$jumlah
                    WHERE id_potongan='$id_pot'");
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
