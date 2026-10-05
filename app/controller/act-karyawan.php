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
    return isset($_POST[$data]) ? trim($_POST[$data]) : '';
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
            WHERE k.id_karyawan = ?";

    $q = $koneksi->prepare($sql);
    $q->execute([$id]);
    $r = $q->fetch();

    if ($r) {
        // Never send the stored password hash to the browser.
        unset($r['password']);

        // 2. Ambil data Kontak Darurat secara terpisah
        $q_kontak = $koneksi->prepare("SELECT * FROM tb_kontak_darurat WHERE id_karyawan = ? ORDER BY nomor_urut ASC");
        $q_kontak->execute([$id]);

        $contacts = [];
        while ($row = $q_kontak->fetch()) {
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

    $koneksi->beginTransaction();

    try {
        // insert tb_karyawan (TANPA FOTO DULU)
        // Siapkan variabel tanggal lahir
        $tgl_lahir_val = input('tgl_lahir') === '' ? null : input('tgl_lahir');

        // Ambil NIK dari inputan form
        $nik_input = input('nik_karyawan');

        // Jenis kelamin wajib L/P sesuai enum kolom (string kosong ditolak
        // MySQL strict mode dengan error "Data truncated").
        $jk_input = input('jenis_kelamin');
        if (!in_array($jk_input, ['L', 'P'], true)) {
            throw new Exception("Jenis kelamin wajib dipilih (Laki-laki/Perempuan).");
        }

        // Password: opsional dari form (khusus Master, min 8), default = md5(NIK)
        // agar tidak error "Field 'password' doesn't have a default value".
        $add_password = $_POST['password_karyawan'] ?? '';
        if ($add_password !== '') {
            if ((int)($_SESSION['level'] ?? 99) !== 0) {
                throw new Exception("Hanya user Master yang boleh mengatur password karyawan.");
            }
            if (strlen($add_password) < 8) {
                throw new Exception("Password minimal 8 karakter.");
            }
            $password_sql = md5($add_password);
        } else {
            $password_sql = md5($nik_input);
        }
        // Bersihkan nama untuk nama file (hilangkan spasi dan karakter aneh)
        $nama_bersih = preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '', input('nama_lengkap')));

        $st1 = $koneksi->prepare("INSERT INTO tb_karyawan
        (nik_karyawan, nama_lengkap, jenis_kelamin, tgl_lahir, tempat_lahir, email_pribadi, no_telp, nationality, agama, status_pernikahan, status_ptkp, status_karyawan, foto, password)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Aktif', '', ?)");
        $ok1 = $st1->execute([
            input('nik_karyawan'),
            input('nama_lengkap'),
            input('jenis_kelamin'),
            $tgl_lahir_val,
            input('tempat_lahir'),
            input('email_pribadi'),
            input('no_telp'),
            input('nationality'),
            input('agama'),
            input('status_pernikahan'),
            input('status_ptkp'),
            $password_sql
        ]);

        if (!$ok1) {
            throw new Exception("Gagal insert tb_karyawan");
        }

        $id_karyawan = (int)$koneksi->lastInsertId();

        // PROSES UPLOAD FOTO (Gunakan NIK)
        if (isset($_FILES['foto']['name']) && $_FILES['foto']['name'] != '') {
            $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
            // Format: NIK-NamaKaryawan.ext
            $foto_name = $nik_input . '-' . strtolower($nama_bersih) . '.' . strtolower($ext);

            $target_dir = '../uploads/profil/';
            if (!file_exists($target_dir)) { mkdir($target_dir, 0755, true); }

            if (move_uploaded_file($_FILES['foto']['tmp_name'], $target_dir . $foto_name)) {
                // Update nama foto ke database
                $stFoto = $koneksi->prepare("UPDATE tb_karyawan SET foto = ? WHERE id_karyawan = ?");
                $stFoto->execute([$foto_name, $id_karyawan]);
            }
        }

        // insert tb_identitas
        $st2 = $koneksi->prepare("INSERT INTO tb_identitas
        (id_karyawan, nik_ktp, kk_number, npwp, bpjs_tk, bpjs_kes, alamat_ktp, provinsi, kabupaten, kecamatan, desa, rt_rw, nama_ibu, last_education, major, education_level, nama_bank, atas_nama_rek, no_rek)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $ok2 = $st2->execute([
            $id_karyawan,
            input('nik_ktp'),
            input('kk_number'),
            input('npwp'),
            input('bpjs_tk'),
            input('bpjs_kes'),
            input('alamat_ktp'),
            input('provinsi'),
            input('kabupaten'),
            input('kecamatan'),
            input('desa'),
            input('rt_rw'),
            input('nama_ibu'),
            input('last_education'),
            input('major'),
            input('education_level'),
            input('nama_bank'),
            input('atas_nama_rek'),
            input('no_rek')
        ]);

        if (!$ok2) {
            throw new Exception("Gagal insert tb_identitas");
        }

        // insert tb_karier
        // Siapkan variabel tanggal (Jika kosong jadikan NULL)
        $tgl_mulai_val   = input('tgl_mulai') === '' ? null : input('tgl_mulai');
        $tgl_selesai_val = input('tgl_selesai') === '' ? null : input('tgl_selesai');
        // ID relasi kosong ('' dari dropdown "-- Pilih --") wajib jadi NULL,
        // karena kolom INT menolak string kosong (Incorrect integer value).
        $id_site_val       = input('id_site') === '' ? null : input('id_site');
        $id_departemen_val = input('id_departemen') === '' ? null : input('id_departemen');
        $id_jabatan_val    = input('id_jabatan') === '' ? null : input('id_jabatan');

        $st3 = $koneksi->prepare("INSERT INTO tb_karier (id_karyawan, id_site, id_departemen, id_jabatan, tgl_mulai, tgl_selesai)
        VALUES (?, ?, ?, ?, ?, ?)");
        $ok3 = $st3->execute([
            $id_karyawan,
            $id_site_val,
            $id_departemen_val,
            $id_jabatan_val,
            $tgl_mulai_val,
            $tgl_selesai_val
        ]);

        if (!$ok3) {
            throw new Exception("Gagal insert tb_karier");
        }

        // insert tb_kontak_darurat
        if (input('nama_kontak1') != "") {
            $st_k1 = $koneksi->prepare("INSERT INTO tb_kontak_darurat (id_karyawan, nomor_urut, nama_kontak, hubungan, no_telp_darurat) VALUES (
                ?, 1, ?, ?, ?)");
            $st_k1->execute([
                $id_karyawan,
                input('nama_kontak1'),
                input('hubungan1'),
                input('no_telp_darurat1')
            ]);
        }

        // Kontak 2
        if (input('nama_kontak2') != "") {
            $st_k2 = $koneksi->prepare("INSERT INTO tb_kontak_darurat (id_karyawan, nomor_urut, nama_kontak, hubungan, no_telp_darurat) VALUES (
                ?, 2, ?, ?, ?)");
            $st_k2->execute([
                $id_karyawan,
                input('nama_kontak2'),
                input('hubungan2'),
                input('no_telp_darurat2')
            ]);
        }

        $koneksi->commit();
        echo json_encode(["status" => "ok"]);
    } catch (PDOException $e) {
        if ($koneksi->inTransaction()) $koneksi->rollBack();
        error_log($e->getMessage());
        echo json_encode(["status" => "error", "msg" => "Gagal menyimpan data karyawan."]);
    } catch (Exception $e) {
        if ($koneksi->inTransaction()) $koneksi->rollBack();
        echo json_encode(["status" => "error", "msg" => $e->getMessage()]);
    }
    exit;
}


if ($action == "edit") {
    $koneksi->beginTransaction();

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

        // --- PROSES UPLOAD FOTO (EDIT) ---
        if (isset($_FILES['foto']['name']) && $_FILES['foto']['name'] != '') {
            // Ambil NIK dari inputan form
            $nik_input = input('nik_karyawan');
            // Bersihkan nama
            $nama_bersih = preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '', input('nama_lengkap')));

            $target_dir = '../uploads/profil/';
            if (!file_exists($target_dir)) { mkdir($target_dir, 0755, true); }

            // Cari foto lama untuk dihapus
            $q_old = $koneksi->prepare("SELECT foto FROM tb_karyawan WHERE id_karyawan = ?");
            $q_old->execute([$id]);
            if ($r_old = $q_old->fetch()) {
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
        $params_k = [];
        foreach ($k_data as $key => $val) {
            // CEK: Jika tgl_lahir kosong, set NULL. Selain itu pakai placeholder.
            if ($val === '' && $key === 'tgl_lahir') {
                $upd_k[] = "$key = NULL";
            } else {
                $upd_k[] = "$key = ?";
                $params_k[] = $val;
            }
        }
        $params_k[] = $id;
        $sql_k = "UPDATE tb_karyawan SET " . implode(", ", $upd_k) . " WHERE id_karyawan = ?";

        $stmtUpK = $koneksi->prepare($sql_k);
        $stmtUpK->execute($params_k);

        if ($new_password !== '') {
            // Keep employee mobile login compatible with its legacy MD5 check.
            $password_hash = md5($new_password);
            $password_stmt = $koneksi->prepare(
                'UPDATE tb_karyawan SET password = ? WHERE id_karyawan = ?'
            );
            $password_stmt->execute([$password_hash, $id]);
            if ($password_stmt->rowCount() !== 1) {
                throw new Exception('Password karyawan gagal diubah.');
            }

            $verify_stmt = $koneksi->prepare('SELECT password FROM tb_karyawan WHERE id_karyawan = ?');
            $verify_stmt->execute([$id]);
            $stored_password = $verify_stmt->fetch()['password'] ?? '';
            if (!hash_equals($stored_password, md5($new_password))) {
                throw new Exception('Password karyawan gagal diverifikasi setelah disimpan.');
            }
        }

        // VALIDASI: Jika ID tidak ditemukan di tb_karyawan
        if ($stmtUpK->rowCount() === 0 && $new_password === '') {
            // Kita tidak throw error di sini karena bisa jadi data memang tidak ada perubahan (isinya sama)
            // Tapi untuk testing, mari kita pastikan ID nya ada
            $check = $koneksi->prepare("SELECT id_karyawan FROM tb_karyawan WHERE id_karyawan = ?");
            $check->execute([$id]);
            if (!$check->fetch()) {
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
        $params_i = [];
        foreach ($i_data as $key => $val) {
            $upd_i[] = "$key = ?";
            $params_i[] = $val;
        }
        $params_i[] = $id;
        $sql_i = "UPDATE tb_identitas SET " . implode(", ", $upd_i) . " WHERE id_karyawan = ?";
        $stmtUpI = $koneksi->prepare($sql_i);
        $stmtUpI->execute($params_i);

        // 3. Update data karier (hanya jika field penempatan dikirim form;
        //    form edit karyawan tidak lagi memuatnya, jadi lewati agar tidak menimpa)
        $karier_keys = ['id_site', 'id_departemen', 'id_jabatan', 'tgl_mulai', 'tgl_selesai'];
        $karier_data = [];
        foreach ($karier_keys as $k) {
            if (array_key_exists($k, $_POST)) $karier_data[$k] = input($k);
        }

        if (!empty($karier_data)) {
            $upd_kar = [];
            $params_kar = [];
            foreach ($karier_data as $k => $v) {
                // CEK: Jika data kosong dan field-nya adalah tanggal, set sebagai NULL (tanpa kutip)
                if ($v === '' && in_array($k, ['tgl_mulai', 'tgl_selesai'])) {
                    $upd_kar[] = "$k = NULL";
                } else {
                    $upd_kar[] = "$k = ?"; // Set normal pakai placeholder
                    $params_kar[] = $v;
                }
            }

            $params_kar[] = $id;
            $sql_kar = "UPDATE tb_karier SET " . implode(",", $upd_kar) . " WHERE id_karyawan=?";
            $stmtUpKar = $koneksi->prepare($sql_kar);
            $stmtUpKar->execute($params_kar);
        }

        // 4. Update data kontak darurat
        // Strategi: Hapus semua kontak lama milik karyawan ini, lalu insert ulang yang baru dari form.
        // Ini menangani kasus perubahan data, penambahan kontak ke-2, atau penghapusan kontak.
        $delKon = $koneksi->prepare("DELETE FROM tb_kontak_darurat WHERE id_karyawan=?");
        $delKon->execute([$id]);

        // Insert Ulang Kontak 1
        if (input('nama_kontak1') != "") {
            $st_kon1 = $koneksi->prepare("INSERT INTO tb_kontak_darurat (id_karyawan, nomor_urut, nama_kontak, hubungan, no_telp_darurat) VALUES (
                ?, 1, ?, ?, ?)");
            $st_kon1->execute([
                $id,
                input('nama_kontak1'),
                input('hubungan1'),
                input('no_telp_darurat1')
            ]);
        }

        // Insert Ulang Kontak 2
        if (input('nama_kontak2') != "") {
            $st_kon2 = $koneksi->prepare("INSERT INTO tb_kontak_darurat (id_karyawan, nomor_urut, nama_kontak, hubungan, no_telp_darurat) VALUES (
                ?, 2, ?, ?, ?)");
            $st_kon2->execute([
                $id,
                input('nama_kontak2'),
                input('hubungan2'),
                input('no_telp_darurat2')
            ]);
        }


        $koneksi->commit();
        echo json_encode(["status" => "ok", "msg" => "Update berhasil untuk ID: $id"]);
    } catch (PDOException $e) {
        if ($koneksi->inTransaction()) $koneksi->rollBack();
        error_log($e->getMessage());
        echo json_encode(["status" => "error", "msg" => "Gagal memperbarui data karyawan."]);
    } catch (Exception $e) {
        if ($koneksi->inTransaction()) $koneksi->rollBack();
        echo json_encode(["status" => "error", "msg" => $e->getMessage()]);
    }
    exit;
}
// 3. DELETE (Opsional, tetap disertakan jika Anda butuh menghapus)
if ($action == "delete") {
    $id = intval($_POST['id_karyawan']);
    $koneksi->beginTransaction();
    try {
        // Hapus file fisik foto
        $q_foto = $koneksi->prepare("SELECT foto FROM tb_karyawan WHERE id_karyawan = ?");
        $q_foto->execute([$id]);
        if ($r_foto = $q_foto->fetch()) {
            if (!empty($r_foto['foto']) && file_exists('../uploads/profil/' . $r_foto['foto'])) {
                @unlink('../uploads/profil/' . $r_foto['foto']);
            }
        }

        $d1 = $koneksi->prepare("DELETE FROM tb_identitas WHERE id_karyawan = ?");
        $d1->execute([$id]);
        $d2 = $koneksi->prepare("DELETE FROM tb_karier WHERE id_karyawan = ?");
        $d2->execute([$id]);
        $d3 = $koneksi->prepare("DELETE FROM tb_karyawan WHERE id_karyawan = ?");
        $d3->execute([$id]);
        $d4 = $koneksi->prepare("DELETE FROM tb_kontak_darurat WHERE id_karyawan = ?");
        $d4->execute([$id]);
        $koneksi->commit();
        echo json_encode(["status" => "ok"]);
    } catch (PDOException $e) {
        if ($koneksi->inTransaction()) $koneksi->rollBack();
        error_log($e->getMessage());
        echo json_encode(["status" => "error", "msg" => "Gagal menghapus data"]);
    } catch (Exception $e) {
        if ($koneksi->inTransaction()) $koneksi->rollBack();
        echo json_encode(["status" => "error", "msg" => "Gagal menghapus data"]);
    }
    exit;
}
