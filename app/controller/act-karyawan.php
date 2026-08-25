<?php
error_reporting(0);
ini_set('display_errors', 0);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

header("Content-Type: application/json");

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
    // 1. Ambil data utama (Karyawan, Identitas, Karier)
    // Kita tidak men-JOIN kontak darurat di sini agar tidak terjadi duplikasi baris jika kontak > 1
    $sql = "SELECT k.*, i.*, 
            kar.id_site, kar.id_departemen, kar.id_jabatan, kar.tgl_mulai, kar.tgl_selesai
            FROM tb_karyawan k 
            LEFT JOIN tb_identitas i ON i.id_karyawan = k.id_karyawan 
            LEFT JOIN tb_karier kar ON kar.id_karyawan = k.id_karyawan
            WHERE k.id_karyawan = '$id'";

    $q = $koneksi->query($sql);
    $r = $q->fetch_assoc();

    if ($r) {
        // Never send the stored password hash to the browser.
        unset($r['password']);

        // 2. Ambil data Kontak Darurat secara terpisah
        $sql_kontak = "SELECT * FROM tb_kontak_darurat WHERE id_karyawan = '$id' ORDER BY nomor_urut ASC";
        $q_kontak = $koneksi->query($sql_kontak);

        $contacts = [];
        while ($row = $q_kontak->fetch_assoc()) {
            $contacts[] = $row;
        }

        // 3. Mapping data kontak ke format form (1 dan 2)
        // Kontak 1
        $r['nama_kontak1']       = $contacts[0]['nama_kontak'] ?? '';
        $r['hubungan1']          = $contacts[0]['hubungan'] ?? '';
        $r['no_telp_darurat1']   = $contacts[0]['no_telp_darurat'] ?? '';

        // Kontak 2
        $r['nama_kontak2']       = $contacts[1]['nama_kontak'] ?? '';
        $r['hubungan2']          = $contacts[1]['hubungan'] ?? '';
        $r['no_telp_darurat2']   = $contacts[1]['no_telp_darurat'] ?? '';

        echo json_encode(["status" => "ok", "data" => $r]);
    } else {
        echo json_encode(["status" => "error", "msg" => "Data tidak ditemukan"]);
    }
    exit;
}


if ($action == "add") {

    $koneksi->begin_transaction();

    try {
        // insert tb_karyawan (TANPA FOTO DULU)
        // Siapkan variabel tanggal lahir
        $tgl_lahir_val = input('tgl_lahir') === '' ? "NULL" : "'" . input('tgl_lahir') . "'";

        // Ambil NIK dari inputan form
        $nik_input = input('nik_karyawan');
        // Bersihkan nama untuk nama file (hilangkan spasi dan karakter aneh)
        $nama_bersih = preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '', input('nama_lengkap')));

        $sql1 = "INSERT INTO tb_karyawan 
        (nik_karyawan, nama_lengkap, jenis_kelamin, tgl_lahir, tempat_lahir, email_pribadi, no_telp, nationality, agama, status_pernikahan, status_ptkp, status_karyawan, foto)
        VALUES (
            '" . input('nik_karyawan') . "',
            '" . input('nama_lengkap') . "',
            '" . input('jenis_kelamin') . "',
            $tgl_lahir_val,
            '" . input('tempat_lahir') . "',
            '" . input('email_pribadi') . "',
            '" . input('no_telp') . "',
            '" . input('nationality') . "',
            '" . input('agama') . "',
            '" . input('status_pernikahan') . "',
            '" . input('status_ptkp') . "',
            'Aktif',
            ''
        )";

        if (!$koneksi->query($sql1)) {
            throw new Exception("Gagal insert tb_karyawan");
        }

        $id_karyawan = $koneksi->insert_id;

        // PROSES UPLOAD FOTO (Gunakan NIK)
        if (isset($_FILES['foto']['name']) && $_FILES['foto']['name'] != '') {
            $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
            // Format: NIK-NamaKaryawan.ext
            $foto_name = $nik_input . '-' . strtolower($nama_bersih) . '.' . strtolower($ext);
            
            $target_dir = '../uploads/profil/';
            if (!file_exists($target_dir)) { mkdir($target_dir, 0755, true); }

            if (move_uploaded_file($_FILES['foto']['tmp_name'], $target_dir . $foto_name)) {
                // Update nama foto ke database
                $koneksi->query("UPDATE tb_karyawan SET foto = '$foto_name' WHERE id_karyawan = '$id_karyawan'");
            }
        }

        // insert tb_identitas
        $sql2 = "INSERT INTO tb_identitas 
        (id_karyawan, nik_ktp, kk_number, npwp, bpjs_tk, bpjs_kes, alamat_ktp, provinsi, kabupaten, kecamatan, desa, rt_rw, nama_ibu, last_education, major, education_level, nama_bank, atas_nama_rek, no_rek)
        VALUES (
            '$id_karyawan',
            '" . input('nik_ktp') . "',
            '" . input('kk_number') . "',
            '" . input('npwp') . "',
            '" . input('bpjs_tk') . "',
            '" . input('bpjs_kes') . "',
            '" . input('alamat_ktp') . "',
            '" . input('provinsi') . "',
            '" . input('kabupaten') . "',
            '" . input('kecamatan') . "',
            '" . input('desa') . "',
            '" . input('rt_rw') . "',
            '" . input('nama_ibu') . "',
            '" . input('last_education') . "',
            '" . input('major') . "',
            '" . input('education_level') . "',
            '" . input('nama_bank') . "',
            '" . input('atas_nama_rek') . "',
            '" . input('no_rek') . "'
        )";

        if (!$koneksi->query($sql2)) {
            throw new Exception("Gagal insert tb_identitas");
        }

        // insert tb_karier
        // Siapkan variabel tanggal (Jika kosong jadikan NULL, jika ada isinya bungkus dengan kutip)
        $tgl_mulai_val   = input('tgl_mulai') === '' ? "NULL" : "'" . input('tgl_mulai') . "'";
        $tgl_selesai_val = input('tgl_selesai') === '' ? "NULL" : "'" . input('tgl_selesai') . "'";

        $sql3 = "INSERT INTO tb_karier (id_karyawan, id_site, id_departemen, id_jabatan, tgl_mulai, tgl_selesai)
        VALUES (
            '$id_karyawan',
            '" . input('id_site') . "',
            '" . input('id_departemen') . "',
            '" . input('id_jabatan') . "',
            $tgl_mulai_val, 
            $tgl_selesai_val
        )";

        if (!$koneksi->query($sql3)) {
            throw new Exception("Gagal insert tb_karier");
        }

        // insert tb_kontak_darurat
        if (input('nama_kontak1') != "") {
            $sql_k1 = "INSERT INTO tb_kontak_darurat (id_karyawan, nomor_urut, nama_kontak, hubungan, no_telp_darurat) VALUES (
                '$id_karyawan', 1,
                '" . input('nama_kontak1') . "',
                '" . input('hubungan1') . "',
                '" . input('no_telp_darurat1') . "'
            )";
            $koneksi->query($sql_k1);
        }

        // Kontak 2
        if (input('nama_kontak2') != "") {
            $sql_k2 = "INSERT INTO tb_kontak_darurat (id_karyawan, nomor_urut, nama_kontak, hubungan, no_telp_darurat) VALUES (
                '$id_karyawan', 2,
                '" . input('nama_kontak2') . "',
                '" . input('hubungan2') . "',
                '" . input('no_telp_darurat2') . "'
            )";
            $koneksi->query($sql_k2);
        }

        $koneksi->commit();
        echo json_encode(["status" => "ok"]);
    } catch (Exception $e) {
        $koneksi->rollback();
        echo json_encode(["status" => "error", "msg" => $e->getMessage()]);
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

        $new_password = $_POST['password_karyawan'] ?? '';
        if ($new_password !== '') {
            if ((int)($_SESSION['level'] ?? 99) !== 0) {
                throw new Exception("Hanya user Master yang boleh mengubah password karyawan.");
            }
            if (strlen($new_password) < 8) {
                throw new Exception("Password minimal 8 karakter.");
            }
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
            'status_pernikahan' => input('status_pernikahan'),
            'status_ptkp'       => input('status_ptkp')
        ];

        if ($new_password !== '') {
            $k_data['password'] = password_hash($new_password, PASSWORD_DEFAULT);
        }

        // --- PROSES UPLOAD FOTO (EDIT) ---
        if (isset($_FILES['foto']['name']) && $_FILES['foto']['name'] != '') {
            // Ambil NIK dari inputan form
            $nik_input = input('nik_karyawan');
            // Bersihkan nama
            $nama_bersih = preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '', input('nama_lengkap')));
            
            $target_dir = '../uploads/profil/';
            if (!file_exists($target_dir)) { mkdir($target_dir, 0755, true); }

            // Cari foto lama untuk dihapus
            $q_old = $koneksi->query("SELECT foto FROM tb_karyawan WHERE id_karyawan = '$id'");
            if ($r_old = $q_old->fetch_assoc()) {
                if (!empty($r_old['foto']) && file_exists($target_dir . $r_old['foto'])) {
                    @unlink($target_dir . $r_old['foto']);
                }
            }
            
            // Upload foto baru...
            $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
            $foto_name = $nik_input . '-' . strtolower($nama_bersih) . '.' . strtolower($ext);
            
            if (move_uploaded_file($_FILES['foto']['tmp_name'], $target_dir . $foto_name)) {
                // Masukkan nama foto ke dalam array untuk diupdate
                $k_data['foto'] = $foto_name;
            }
        }

        $upd_k = [];
        foreach ($k_data as $key => $val) {
            // CEK: Jika tgl_lahir kosong, set NULL. Selain itu bungkus pakai kutip.
            if ($val === '' && $key === 'tgl_lahir') {
                $upd_k[] = "$key = NULL";
            } else {
                $upd_k[] = "$key = '$val'";
            }
        }
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
            'education_level'   => input('education_level'),
            'nama_bank'         => input('nama_bank'),
            'atas_nama_rek'     => input('atas_nama_rek'),
            'no_rek'            => input('no_rek')
        ];

        $upd_i = [];
        foreach ($i_data as $key => $val) {
            $upd_i[] = "$key = '$val'";
        }
        $sql_i = "UPDATE tb_identitas SET " . implode(", ", $upd_i) . " WHERE id_karyawan = '$id'";
        $koneksi->query($sql_i);

        // 3. Update data karier
        $karier_data = [
            'id_site'    => input('id_site'),
            'id_departemen' => input('id_departemen'),
            'id_jabatan' => input('id_jabatan'),
            'tgl_mulai' => input('tgl_mulai'),
            'tgl_selesai' => input('tgl_selesai'),
        ];

        $upd_kar = [];
        foreach ($karier_data as $k => $v) {
            // CEK: Jika data kosong dan field-nya adalah tanggal, set sebagai NULL (tanpa kutip)
            if ($v === '' && in_array($k, ['tgl_mulai', 'tgl_selesai'])) {
                $upd_kar[] = "$k = NULL";
            } else {
                $upd_kar[] = "$k = '$v'"; // Set normal pakai kutip
            }
        }

        $sql_kar = "UPDATE tb_karier SET " . implode(",", $upd_kar) . " WHERE id_karyawan='$id'";
        $koneksi->query($sql_kar);

        // 4. Update data kontak darurat
        // Strategi: Hapus semua kontak lama milik karyawan ini, lalu insert ulang yang baru dari form.
        // Ini menangani kasus perubahan data, penambahan kontak ke-2, atau penghapusan kontak.
        $koneksi->query("DELETE FROM tb_kontak_darurat WHERE id_karyawan='$id'");

        // Insert Ulang Kontak 1
        if (input('nama_kontak1') != "") {
            $sql_kon1 = "INSERT INTO tb_kontak_darurat (id_karyawan, nomor_urut, nama_kontak, hubungan, no_telp_darurat) VALUES (
                '$id', 1,
                '" . input('nama_kontak1') . "',
                '" . input('hubungan1') . "',
                '" . input('no_telp_darurat1') . "'
            )";
            $koneksi->query($sql_kon1);
        }

        // Insert Ulang Kontak 2
        if (input('nama_kontak2') != "") {
            $sql_kon2 = "INSERT INTO tb_kontak_darurat (id_karyawan, nomor_urut, nama_kontak, hubungan, no_telp_darurat) VALUES (
                '$id', 2,
                '" . input('nama_kontak2') . "',
                '" . input('hubungan2') . "',
                '" . input('no_telp_darurat2') . "'
            )";
            $koneksi->query($sql_kon2);
        }


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
        // Hapus file fisik foto
        $q_foto = $koneksi->query("SELECT foto FROM tb_karyawan WHERE id_karyawan = '$id'");
        if ($r_foto = $q_foto->fetch_assoc()) {
            if (!empty($r_foto['foto']) && file_exists('../uploads/profil/' . $r_foto['foto'])) {
                @unlink('../uploads/profil/' . $r_foto['foto']);
            }
        }

        $koneksi->query("DELETE FROM tb_identitas WHERE id_karyawan = '$id'");
        $koneksi->query("DELETE FROM tb_karier WHERE id_karyawan = '$id'");
        $koneksi->query("DELETE FROM tb_karyawan WHERE id_karyawan = '$id'");
        $koneksi->query("DELETE FROM tb_kontak_darurat WHERE id_karyawan = '$id'");
        $koneksi->commit();
        echo json_encode(["status" => "ok"]);
    } catch (Exception $e) {
        $koneksi->rollback();
        echo json_encode(["status" => "error", "msg" => "Gagal menghapus data"]);
    }
    exit;
}
