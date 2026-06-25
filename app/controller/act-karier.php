<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Content-Type: application/json");

require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/function.php';

$action = $_POST['action'] ?? '';
$response = [];

// KOREKSI: Fungsi pintar floatOrZero untuk membersihkan titik ribuan & mengubah koma desimal
function floatOrZero($val)
{
    if (!isset($val) || $val === '') return 0;

    // 1. Hilangkan titik ribuan terlebih dahulu agar tidak merusak is_numeric PHP
    $val = str_replace('.', '', $val);

    // 2. Konversi koma desimal menjadi titik standar PHP/Database
    $val = str_replace(',', '.', $val);

    return is_numeric($val) ? floatval($val) : 0;
}

// Valid tanggal
function validDate($date)
{
    return preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) === 1;
}

try {

    if (!in_array($action, ['add', 'edit', 'delete', 'extend_single', 'extend_batch'])) {
        throw new Exception("Action tidak valid");
    }

    // EXTEND SINGLE (Per Karyawan)
    if ($action === 'extend_single') {
        $id_karier = intval($_POST['id_karier'] ?? 0);
        if ($id_karier <= 0) throw new Exception("ID karier tidak valid");

        // Cek status karyawan
        $cek = $koneksi->query("
            SELECT kr.status_karyawan, k.tgl_selesai 
            FROM tb_karier k 
            INNER JOIN tb_karyawan kr ON k.id_karyawan = kr.id_karyawan 
            WHERE k.id_karier = $id_karier
        ");

        if ($cek->num_rows === 0) throw new Exception("Data karier tidak ditemukan.");
        $row = $cek->fetch_assoc();

        if (strtolower($row['status_karyawan']) !== 'aktif') {
            throw new Exception("Ditolak: Hanya karyawan dengan status Aktif yang bisa diperpanjang otomatis.");
        }
        if (empty($row['tgl_selesai']) || $row['tgl_selesai'] == '0000-00-00') {
            throw new Exception("Tanggal selesai kosong, tidak bisa diperpanjang otomatis.");
        }

        // Tambah 6 Bulan
        $koneksi->query("UPDATE tb_karier SET tgl_selesai = DATE_ADD(tgl_selesai, INTERVAL 6 MONTH) WHERE id_karier = $id_karier");
        $response = ['status' => 'success', 'msg' => 'Kontrak berhasil diperpanjang 6 bulan.'];
    }

    // EXTEND BATCH (Massal)
    elseif ($action === 'extend_batch') {
        $ids = $_POST['id_karier'] ?? [];
        if (empty($ids) || !is_array($ids)) {
            throw new Exception("Tidak ada karyawan yang dipilih.");
        }

        // Sanitasi array ID
        $ids = array_map('intval', $ids);
        $ids_str = implode(',', $ids);

        // Update masal HANYA untuk status aktif dan tgl_selesai tidak null
        $sql = "UPDATE tb_karier k
                INNER JOIN tb_karyawan kr ON k.id_karyawan = kr.id_karyawan
                SET k.tgl_selesai = DATE_ADD(k.tgl_selesai, INTERVAL 6 MONTH)
                WHERE k.id_karier IN ($ids_str) 
                AND LOWER(kr.status_karyawan) = 'aktif' 
                AND k.tgl_selesai IS NOT NULL";

        if (!$koneksi->query($sql)) {
            throw new Exception("Gagal melakukan perpanjangan massal: " . $koneksi->error);
        }

        $affected = $koneksi->affected_rows;
        if ($affected > 0) {
            $response = ['status' => 'success', 'msg' => "$affected kontrak karyawan aktif berhasil diperpanjang 6 bulan."];
        } else {
            $response = ['status' => 'error', 'msg' => "Tidak ada data yang diperbarui (pastikan karyawan yang dicentang berstatus Aktif)."];
        }
    }

    // DELETE
    elseif ($action === 'delete') {
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
        // --- SMART DECRYPT ---
        $raw_id_karyawan = $_POST['id_karyawan'] ?? '';
        if (is_numeric($raw_id_karyawan)) {
            // Jika berupa angka, berarti dikirim dari import-karier.php (master)
            $id_karyawan = intval($raw_id_karyawan);
        } else {
            // Jika berupa string acak, berarti dikirim dari data-karier.php (encrypted)
            $id_karyawan = !empty($raw_id_karyawan) ? intval(decrypt($raw_id_karyawan)) : 0;
        }
        // ---------------------

        $id_site       = intval($_POST['id_site']);
        $id_departemen = intval($_POST['id_departemen']);
        $id_jabatan    = intval($_POST['id_jabatan']);

        $gaji_pokok  = floatOrZero($_POST['gaji_pokok']);
        $lembur      = floatOrZero($_POST['lembur']);
        $pot_izin    = floatOrZero($_POST['pot_izin']);
        $pot_mangkir = floatOrZero($_POST['pot_mangkir']);

        $tgl_mulai   = $_POST['tgl_mulai'];
        $tgl_selesai = !empty($_POST['tgl_selesai']) ? $_POST['tgl_selesai'] : null;

        if (!validDate($tgl_mulai))
            throw new Exception("Tanggal mulai tidak valid");

        // ADD
        if ($action === 'add') {

            // --- TAMBAHKAN VALIDASI STATUS DISINI ---
            $cekStatus = $koneksi->query("SELECT status_karyawan FROM tb_karyawan WHERE id_karyawan = '$id_karyawan'");
            if ($cekStatus->num_rows > 0) {
                $status = $cekStatus->fetch_assoc()['status_karyawan'];
                // Sesuaikan kata 'Resign' dengan value yang ada di databasemu
                if (strtolower($status) === 'resign' || strtolower($status) === 'tidak aktif') {
                    throw new Exception("Ditolak: Karyawan ini sudah berstatus Resign. Sesuai aturan, tidak bisa masuk kembali.");
                }
            }
            // ----------------------------------------

            $sql = "INSERT INTO tb_karier (id_karyawan,id_site,id_departemen,id_jabatan,gaji_pokok,lembur,pot_izin,pot_mangkir,tgl_mulai,tgl_selesai) VALUES (?,?,?,?,?,?,?,?,?,?)";
            $stmt = $koneksi->prepare($sql);
            $stmt->bind_param("iiiiddddss", $id_karyawan, $id_site, $id_departemen, $id_jabatan, $gaji_pokok, $lembur, $pot_izin, $pot_mangkir, $tgl_mulai, $tgl_selesai);
        }

        // EDIT
        elseif ($action === 'edit') {
            if ($tgl_selesai === null) {
                $sql = "UPDATE tb_karier SET id_site=?, id_departemen=?, id_jabatan=?, gaji_pokok=?, lembur=?, pot_izin=?, pot_mangkir=?, tgl_mulai=?, tgl_selesai=NULL WHERE id_karier=?";
                $stmt = $koneksi->prepare($sql);
                // KOREKSI: Mengubah tipe data dari "iiiidddsi" menjadi "iiiddddsi"
                $stmt->bind_param("iiiddddsi", $id_site, $id_departemen, $id_jabatan, $gaji_pokok, $lembur, $pot_izin, $pot_mangkir, $tgl_mulai, $id_karier);
            } else {
                $sql = "UPDATE tb_karier SET id_site=?, id_departemen=?, id_jabatan=?, gaji_pokok=?, lembur=?, pot_izin=?, pot_mangkir=?, tgl_mulai=?, tgl_selesai=? WHERE id_karier=?";
                $stmt = $koneksi->prepare($sql);
                // KOREKSI: Mengubah tipe data dari "iiiidddssi" menjadi "iiiddddssi"
                $stmt->bind_param("iiiddddssi", $id_site, $id_departemen, $id_jabatan, $gaji_pokok, $lembur, $pot_izin, $pot_mangkir, $tgl_mulai, $tgl_selesai, $id_karier);
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
