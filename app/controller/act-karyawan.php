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
        // Ambil ID dari POST
        $id = isset($_POST['id_karyawan']) ? intval($_POST['id_karyawan']) : 0;
        
        // Cek apakah ID kosong
        if ($id <= 0) {
            throw new Exception("ID Karyawan tidak terdeteksi oleh sistem.");
        }

        // 1. UPDATE tb_karyawan
        $k_data = [
            'nik_karyawan'      => input('nik_karyawan'),
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
        foreach ($k_data as $key => $val) { $upd_k[] = "$key = '$val'"; }
        $sql_k = "UPDATE tb_karyawan SET " . implode(", ", $upd_k) . " WHERE id_karyawan = '$id'";
        
        $koneksi->query($sql_k);
        
        // VALIDASI: Jika ID tidak ditemukan di tb_karyawan
        if ($koneksi->affected_rows === 0) {
            // Kita tidak throw error di sini karena bisa jadi data memang tidak ada perubahan (isinya sama)
            // Tapi untuk testing, mari kita pastikan ID nya ada
            $check = $koneksi->query("SELECT id_karyawan FROM tb_karyawan WHERE id_karyawan = '$id'");
            if ($check->num_rows === 0) {
                throw new Exception("Gagal Update: ID Karyawan ($id) tidak ditemukan di database.");
            }
        }

        // 2. UPDATE tb_identitas
        $i_data = [
            'nik_ktp'           => input('nik_ktp'),
            'kk_number'         => input('kk_number'),
            'npwp'              => input('npwp'),
            'bpjs_tk'           => input('bpjs_tk'),
            'bpjs_kes'          => input('bpjs_kes'),
            'alamat_ktp'        => input('alamat_ktp'),
            'provinsi'          => input('provinsi'),
            'kabupaten'         => input('kabupaten'),
            'kecamatan'         => input('kecamatan'),
            'desa'              => input('desa'),
            'rt_rw'             => input('rt_rw'),
            'nama_ibu'          => input('nama_ibu'),
            'last_education'    => input('last_education'),
            'major'             => input('major'),
            'education_level'   => input('education_level')
        ];

        $upd_i = [];
        foreach ($i_data as $key => $val) { $upd_i[] = "$key = '$val'"; }
        $sql_i = "UPDATE tb_identitas SET " . implode(", ", $upd_i) . " WHERE id_karyawan = '$id'";
        $koneksi->query($sql_i);

        $koneksi->commit();
        echo json_encode(["status" => "ok", "msg" => "Update berhasil untuk ID: $id"]);

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
