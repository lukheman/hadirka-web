<?php
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/function.php';

header("Content-Type: application/json");

$action = $_POST['action'] ?? '';

function input($data)
{
    global $koneksi;
    return isset($_POST[$data]) ? $koneksi->real_escape_string(trim($_POST[$data])) : '';
}

if ($action == "get") {
    $id = intval($_POST['id_karyawan']);
    $sql = "SELECT k.*, i.*, kar.id_site, kar.id_jabatan, kar.tgl_selesai 
            FROM tb_karyawan k 
            LEFT JOIN tb_identitas i ON i.id_karyawan = k.id_karyawan 
            LEFT JOIN tb_karier kar ON kar.id_karyawan = k.id_karyawan
            WHERE k.id_karyawan = '$id'";

    $q = $koneksi->query($sql);
    $r = $q->fetch_assoc();

    if ($r) {
        echo json_encode(["status" => "ok", "data" => $r]);
    } else {
        echo json_encode(["status" => "error", "msg" => "Data tidak ditemukan"]);
    }
    exit;
}

if ($action == "edit") {
    $koneksi->begin_transaction();

    try {
        $id = intval($_POST['id_karyawan'] ?? 0);
        if ($id <= 0) throw new Exception("ID Karyawan tidak valid.");

        // Ambil data NIK Karyawan
        $nik_karyawan = input('nik_karyawan');

        // VALIDASI: Jangan biarkan NIK kosong jika di DB bersifat UNIQUE
        if (empty($nik_karyawan)) {
            throw new Exception("NIK Karyawan tidak boleh kosong.");
        }

        // 1. UPDATE tb_karyawan
        $k_data = [
            'nik_karyawan'      => $nik_karyawan,
            'nama_lengkap'      => input('nama_lengkap'),
            'jenis_kelamin'     => input('jenis_kelamin'),
            'tgl_lahir'         => input('tgl_lahir'),
            'tempat_lahir'      => input('tempat_lahir'),
            'email_pribadi'     => input('email_pribadi'),
            'no_telp'           => input('no_telp'),
            'nationality'       => input('nationality'),
            'agama'             => input('agama'),
            'status_pernikahan' => input('status_pernikahan')
        ];

        $upd_k = [];
        foreach ($k_data as $key => $val) { 
            $upd_k[] = "$key = '$val'"; 
        }
        $sql_k = "UPDATE tb_karyawan SET " . implode(", ", $upd_k) . " WHERE id_karyawan = '$id'";
        
        if (!$koneksi->query($sql_k)) {
            // Jika error karena NIK sudah dipakai orang lain
            if ($koneksi->errno == 1062) {
                throw new Exception("NIK Karyawan '$nik_karyawan' sudah terdaftar pada karyawan lain.");
            }
            throw new Exception("Gagal update tb_karyawan: " . $koneksi->error);
        }

        // ... Lanjutkan ke update tb_identitas dan tb_karier seperti kode sebelumnya ...

        $koneksi->commit();
        echo json_encode(["status" => "ok", "msg" => "Data berhasil diperbarui"]);

    } catch (Exception $e) {
        $koneksi->rollback();
        echo json_encode(["status" => "error", "msg" => $e->getMessage()]);
    }
    exit;
}
// 3. DELETE (Opsional, tetap disertakan jika Anda butuh menghapus)
if ($action == "delete") {
    $id = intval($_POST['id_karyawan']);
    $koneksi->begin_transaction();
    try {
        $koneksi->query("DELETE FROM tb_identitas WHERE id_karyawan = '$id'");
        $koneksi->query("DELETE FROM tb_karier WHERE id_karyawan = '$id'");
        $koneksi->query("DELETE FROM tb_karyawan WHERE id_karyawan = '$id'");
        $koneksi->commit();
        echo json_encode(["status" => "ok"]);
    } catch (Exception $e) {
        $koneksi->rollback();
        echo json_encode(["status" => "error", "msg" => "Gagal menghapus data"]);
    }
    exit;
}
