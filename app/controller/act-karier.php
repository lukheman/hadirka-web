<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Content-Type: application/json");

require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/function.php';

$action = $_POST['action'] ?? '';
$response = [];

// Konversi integer
function intOrZero($val)
{
    return (isset($val) && is_numeric($val)) ? intval($val) : 0;
}

// Valid tanggal
function validDate($date)
{
    return preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) === 1;
}

try {

    if (!in_array($action, ['add', 'edit', 'delete'])) {
        throw new Exception("Action tidak valid");
    }

    // DELETE
    if ($action === 'delete') {
        $id_karier = intval($_POST['id_karier']);

        if ($id_karier <= 0)
            throw new Exception("ID karier tidak valid");

        $stmt = $koneksi->prepare("DELETE FROM tb_karier WHERE id_karier=?");
        $stmt->bind_param("i", $id_karier);
        $stmt->execute();

        $response = ['status' => 'success'];
    }

    // ADD / EDIT
    else {

        $id_karier     = intval($_POST['id_karier'] ?? 0);
        $id_karyawan   = intval(decrypt($_POST['id_karyawan'] ?? 0));
        $id_site       = intval($_POST['id_site']);
        $id_departemen = intval($_POST['id_departemen']);
        $id_jabatan    = intval($_POST['id_jabatan']);

        $gaji_pokok  = intOrZero($_POST['gaji_pokok']);
        $ot_pertama  = intOrZero($_POST['ot_pertama']);
        $ot_kedua    = intOrZero($_POST['ot_kedua']);
        $spl         = intOrZero($_POST['spl']);
        $ot1_spl     = intOrZero($_POST['ot1_spl']);
        $ot2_spl     = intOrZero($_POST['ot2_spl']);
        $pot_izin    = intOrZero($_POST['pot_izin']);
        $pot_mangkir = intOrZero($_POST['pot_mangkir']);


        $tgl_mulai   = $_POST['tgl_mulai'];
        $tgl_selesai = !empty($_POST['tgl_selesai']) ? $_POST['tgl_selesai'] : null;

        if (!validDate($tgl_mulai))
            throw new Exception("Tanggal mulai tidak valid");

        // ADD
        if ($action === 'add') {

            $sql = "INSERT INTO tb_karier 
                (id_karyawan,id_site,id_departemen,id_jabatan,
                gaji_pokok,ot_pertama,ot_kedua,spl,ot1_spl,ot2_spl,
                pot_izin,pot_mangkir,
                tgl_mulai,tgl_selesai)
                VALUES (?,?,?,?,?,?,?,?,?,?)";

            $stmt = $koneksi->prepare($sql);
            $stmt->bind_param(
                "iiiiiiiiiiiiss",
                $id_karyawan,
                $id_site,
                $id_departemen,
                $id_jabatan,
                $gaji_pokok,
                $ot_pertama,
                $ot_kedua,
                $spl,
                $ot1_spl,
                $ot2_spl,
                $pot_izin,
                $pot_mangkir,
                $tgl_mulai,
                $tgl_selesai
            );
        }

        // EDIT
        elseif ($action === 'edit') {

            if ($tgl_selesai === null) {

                $sql = "UPDATE tb_karier SET
                    id_site=?, id_departemen=?, id_jabatan=?,
                    gaji_pokok=?, ot_pertama=?, ot_kedua=?, spl=?, 
                    ot1_spl=?, ot2_spl=?, pot_izin=?, pot_mangkir=?,
                    tgl_mulai=?, tgl_selesai=NULL
                    WHERE id_karier=?";

                $stmt = $koneksi->prepare($sql);
                $stmt->bind_param(
                    "iiiidddssi",
                    $id_site,
                    $id_departemen,
                    $id_jabatan,
                    $gaji_pokok,
                    $ot_pertama,
                    $ot_kedua,
                    $spl,
                    $ot1_spl,
                    $ot2_spl,
                    $pot_izin,
                    $pot_mangkir,
                    $tgl_mulai,
                    $id_karier
                );
            } else {

                $sql = "UPDATE tb_karier SET
                    id_site=?, id_departemen=?, id_jabatan=?,
                    gaji_pokok=?, ot_pertama=?, ot_kedua=?, spl=?, 
                    ot1_spl=?, ot2_spl=?, pot_izin=?, pot_mangkir=?,
                    tgl_mulai=?, tgl_selesai=?
                    WHERE id_karier=?";

                $stmt = $koneksi->prepare($sql);
                $stmt->bind_param(
                    "iiiidddddddssi",
                    $id_site,
                    $id_departemen,
                    $id_jabatan,
                    $gaji_pokok,
                    $ot_pertama,
                    $ot_kedua,
                    $spl,
                    $ot1_spl,
                    $ot2_spl,
                    $pot_izin,
                    $pot_mangkir,
                    $tgl_mulai,
                    $tgl_selesai,
                    $id_karier
                );
            }
        }

        if (!$stmt->execute())
            throw new Exception($stmt->error);

        $response = ['status' => 'success'];
    }
} catch (Exception $e) {
    $response = ['status' => 'error', 'msg' => $e->getMessage()];
}

echo json_encode($response);
