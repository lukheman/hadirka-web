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

// Finance (level 6) read-only: blokir semua aksi tulis via endpoint ini.
// Aksi "get" tetap diizinkan karena hanya membaca satu record (dipakai modal detail).
if ((($_SESSION['level'] ?? 99) == 6) && in_array($action, ['add', 'edit', 'delete', 'delete_selected', 'delete_all'], true)) {
    echo json_encode(["status" => "error", "msg" => "Akses ditolak: akun Finance hanya boleh melihat data karyawan."]);
    exit;
}

// Hapus massal (terpilih / semua sesuai filter) hanya untuk user Master (level 0).
if (in_array($action, ['delete_selected', 'delete_all'], true) && (int)($_SESSION['level'] ?? 99) !== 0) {
    echo json_encode(["status" => "error", "msg" => "Akses ditolak: hanya user Master yang boleh menghapus data karyawan secara massal."]);
    exit;
}

function input($data)
{
    return isset($_POST[$data]) ? trim($_POST[$data]) : '';
}

/**
 * Hak kelola akun karyawan mengikuti hak edit Data Karyawan.
 * Finance (6) tetap read-only, sedangkan level lain sudah dibatasi oleh
 * routing halaman utama.
 */
function boleh_kelola_akun_karyawan(): bool
{
    return in_array((int)($_SESSION['level'] ?? 99), [0, 1, 2, 3, 5], true);
}

/**
 * Tentukan tipe karyawan dari input form secara longgar.
 * Jika id/nama tidak dikirim atau tidak dikenal, otomatis memakai tipe
 * yang tersedia (utamakan Kontrak). Tidak pernah melempar error.
 *
 * @return array{id:int, nama:string}
 */
function resolve_tipe_karyawan(PDO $koneksi, string $idInput, string $namaInput): array
{
    if ($idInput !== '' && ctype_digit($idInput)) {
        $st = $koneksi->prepare("SELECT id_tipe_karyawan, nama_tipe FROM tb_tipe_karyawan WHERE id_tipe_karyawan = ? LIMIT 1");
        $st->execute([(int)$idInput]);
        if ($row = $st->fetch()) {
            return ['id' => (int)$row['id_tipe_karyawan'], 'nama' => (string)$row['nama_tipe']];
        }
    }

    if ($namaInput !== '') {
        $st = $koneksi->prepare("SELECT id_tipe_karyawan, nama_tipe FROM tb_tipe_karyawan WHERE nama_tipe = ? LIMIT 1");
        $st->execute([$namaInput]);
        if ($row = $st->fetch()) {
            return ['id' => (int)$row['id_tipe_karyawan'], 'nama' => (string)$row['nama_tipe']];
        }
    }

    // Fallback: tipe Kontrak bila ada, selain itu tipe pertama.
    $q = $koneksi->query("SELECT id_tipe_karyawan, nama_tipe FROM tb_tipe_karyawan ORDER BY (nama_tipe = 'Kontrak') DESC, id_tipe_karyawan ASC LIMIT 1");
    if ($q && ($row = $q->fetch())) {
        return ['id' => (int)$row['id_tipe_karyawan'], 'nama' => (string)$row['nama_tipe']];
    }

    return ['id' => 1, 'nama' => 'Kontrak'];
}

/**
 * Hapus 1 karyawan beserta seluruh data turunannya.
 * - Tabel dengan ON DELETE CASCADE (tb_identitas, tb_karier, tb_kontak_darurat,
 *   tb_absen, tb_cuti, dll) ikut terhapus otomatis.
 * - tb_reset_otp & tb_tereliminasi tidak punya CASCADE, jadi dibersihkan manual.
 * - File foto fisik baru dihapus setelah baris database benar-benar terhapus.
 *
 * @return bool true bila baris tb_karyawan berhasil terhapus.
 * @throws PDOException bila masih terikat foreign key yang tidak bisa dilepas.
 */
function hapus_karyawan_lengkap(PDO $koneksi, int $id): bool
{
    $q_foto = $koneksi->prepare("SELECT foto FROM tb_karyawan WHERE id_karyawan = ?");
    $q_foto->execute([$id]);
    $r_foto = $q_foto->fetch();

    if (!$r_foto) {
        return false; // data tidak ditemukan / sudah terhapus
    }

    $koneksi->prepare("DELETE FROM tb_reset_otp WHERE id_karyawan = ?")->execute([$id]);
    $koneksi->prepare("DELETE FROM tb_tereliminasi WHERE id_karyawan = ?")->execute([$id]);
    $koneksi->prepare("DELETE FROM tb_identitas WHERE id_karyawan = ?")->execute([$id]);
    $koneksi->prepare("DELETE FROM tb_karier WHERE id_karyawan = ?")->execute([$id]);
    $koneksi->prepare("DELETE FROM tb_kontak_darurat WHERE id_karyawan = ?")->execute([$id]);

    $del = $koneksi->prepare("DELETE FROM tb_karyawan WHERE id_karyawan = ?");
    $del->execute([$id]);

    if ($del->rowCount() > 0) {
        if (!empty($r_foto['foto']) && file_exists('../uploads/profil/' . $r_foto['foto'])) {
            @unlink('../uploads/profil/' . $r_foto['foto']);
        }
        return true;
    }

    return false;
}

/**
 * Hapus banyak karyawan dalam 1 transaksi (dipakai delete_selected & delete_all).
 * Tiap karyawan memakai SAVEPOINT agar 1 kegagalan (mis. masih terikat data THR)
 * tidak ikut membatalkan karyawan lain yang sudah berhasil dihapus.
 *
 * @param int[] $ids
 * @return array{berhasil:int, dilewati:int, gagal:string[]}
 */
function hapus_karyawan_massal(PDO $koneksi, array $ids): array
{
    $hasil = ['berhasil' => 0, 'dilewati' => 0, 'gagal' => []];

    if (empty($ids)) {
        return $hasil;
    }

    // Ambil identitas karyawan yang benar-benar ada (untuk pesan hasil)
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $q = $koneksi->prepare("SELECT id_karyawan, nik_karyawan, nama_lengkap FROM tb_karyawan WHERE id_karyawan IN ($placeholders)");
    $q->execute($ids);

    $info = [];
    while ($r = $q->fetch()) {
        $info[(int)$r['id_karyawan']] = $r;
    }

    foreach ($ids as $id) {
        if (!isset($info[$id])) {
            $hasil['dilewati']++;
            continue;
        }

        $koneksi->exec("SAVEPOINT sp_hapus_karyawan");
        try {
            if (hapus_karyawan_lengkap($koneksi, $id)) {
                $hasil['berhasil']++;
            } else {
                $hasil['dilewati']++;
            }
        } catch (PDOException $e) {
            $koneksi->exec("ROLLBACK TO SAVEPOINT sp_hapus_karyawan");
            error_log("Hapus karyawan id={$id} gagal: " . $e->getMessage());

            // 1451 = tidak bisa dihapus karena masih direferensikan tabel lain (mis. tb_thr tanpa CASCADE).
            $alasan = (($e->errorInfo[1] ?? 0) === 1451)
                ? 'masih terhubung dengan data lain (mis. riwayat THR/payroll)'
                : 'terjadi kendala database';

            $hasil['gagal'][] = htmlspecialchars($info[$id]['nik_karyawan'] ?? '-') . ' - ' .
                htmlspecialchars($info[$id]['nama_lengkap'] ?? '-') . ' (' . $alasan . ')';
        }
    }

    return $hasil;
}

/**
 * Susun respons JSON standar untuk hasil hapus massal.
 *
 * @param array{berhasil:int, dilewati:int, gagal:string[]} $hasil
 */
function respon_hapus_massal(array $hasil): array
{
    $berhasil = (int)$hasil['berhasil'];
    $dilewati = (int)$hasil['dilewati'];
    $gagal    = count($hasil['gagal']);

    $msg = "Berhasil menghapus {$berhasil} karyawan.";
    if ($dilewati > 0) {
        $msg .= " {$dilewati} data dilewati (tidak ditemukan).";
    }
    if ($gagal > 0) {
        $msg .= " {$gagal} data gagal dihapus karena masih terhubung dengan data lain.";
    }

    return [
        "status"       => $berhasil > 0 ? "ok" : "error",
        "msg"          => $msg,
        "berhasil"     => $berhasil,
        "dilewati"     => $dilewati,
        "gagal"        => $gagal,
        "gagal_detail" => $hasil['gagal'],
    ];
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

        // Data kontak darurat tidak lagi dipakai form edit (biodata utama saja).

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

        // Jenis kelamin opsional: kosong/tidak valid otomatis L agar
        // kolom enum tetap terisi tanpa memblokir penyimpanan.
        $jk_input = input('jenis_kelamin');
        if (!in_array($jk_input, ['L', 'P'], true)) {
            $jk_input = 'L';
        }

        // Tipe karyawan opsional: otomatis memakai tipe yang tersedia.
        $tipeInfo    = resolve_tipe_karyawan($koneksi, input('id_tipe_karyawan'), input('tipe_karyawan'));
        $tipe_input  = $tipeInfo['nama'];
        $idTipeInput = $tipeInfo['id'];

        // Jam kerja opsional. Hanya disimpan untuk tipe Kontrak dan
        // otomatis 7 jam bila tidak dipilih.
        $jam_kerja_input = input('jam_kerja');
        if ($tipe_input === 'Kontrak') {
            if (!in_array($jam_kerja_input, ['7', '8'], true)) {
                $jam_kerja_input = '7';
            }
        } else {
            $jam_kerja_input = null;
        }

        // Password opsional tanpa batas panjang minimal.
        // Jika kosong, akun tetap belum diaktifkan.
        $add_password = $_POST['password_karyawan'] ?? '';
        if ($add_password !== '') {
            if (!boleh_kelola_akun_karyawan()) {
                throw new Exception("Akses ditolak: Anda tidak boleh mengatur password karyawan.");
            }
            $password_sql = password_hash($add_password, PASSWORD_DEFAULT);
        } else {
            $password_sql = '';
        }
        // Bersihkan nama untuk nama file (hilangkan spasi dan karakter aneh)
        $nama_bersih = preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '', input('nama_lengkap')));

        $st1 = $koneksi->prepare("INSERT INTO tb_karyawan
        (nik_karyawan, nama_lengkap, jenis_kelamin, tgl_lahir, tempat_lahir, email_pribadi, no_telp, agama, tipe_karyawan, id_tipe_karyawan, jam_kerja, status_karyawan, foto, password)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Aktif', '', ?)");
        $ok1 = $st1->execute([
            input('nik_karyawan'),
            input('nama_lengkap'),
            $jk_input,
            $tgl_lahir_val,
            input('tempat_lahir'),
            input('email_pribadi'),
            input('no_telp'),
            input('agama'),
            $tipe_input,
            $idTipeInput,
            $jam_kerja_input,
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

        // Data identitas lama tidak lagi dikumpulkan dari form. Baris tetap
        // dibuat agar relasi/query lama (slip, API) tidak error.
        $st2 = $koneksi->prepare("INSERT INTO tb_identitas
        (id_karyawan, nama_bank, no_rek, atas_nama_rek)
        VALUES (?, '', '', '')");
        $ok2 = $st2->execute([$id_karyawan]);

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

        // Kontak darurat tidak lagi dikumpulkan (biodata utama saja).

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

// Ubah password saja. Jangan jalankan update profil lengkap karena beberapa
// kolom profil lama dapat gagal validasi dan membatalkan perubahan password.
if ($action == "change_password") {
    try {
        $id = isset($_POST['id_karyawan']) ? intval($_POST['id_karyawan']) : 0;
        $new_password = (string)($_POST['password_karyawan'] ?? '');

        if ($id <= 0) {
            throw new Exception("ID Karyawan tidak terdeteksi oleh sistem.");
        }
        if (!boleh_kelola_akun_karyawan()) {
            throw new Exception("Akses ditolak: Anda tidak boleh mengubah password karyawan.");
        }
        if ($new_password === '') {
            throw new Exception("Password baru belum diisi.");
        }

        $check = $koneksi->prepare('SELECT id_karyawan FROM tb_karyawan WHERE id_karyawan = ? LIMIT 1');
        $check->execute([$id]);
        if (!$check->fetch()) {
            throw new Exception("Data karyawan tidak ditemukan.");
        }

        $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
        $password_stmt = $koneksi->prepare(
            'UPDATE tb_karyawan SET password = ? WHERE id_karyawan = ?'
        );
        $password_stmt->execute([$password_hash, $id]);

        $verify_stmt = $koneksi->prepare('SELECT password FROM tb_karyawan WHERE id_karyawan = ?');
        $verify_stmt->execute([$id]);
        $stored_password = (string)($verify_stmt->fetch()['password'] ?? '');
        if (!password_verify($new_password, $stored_password)) {
            throw new Exception('Password karyawan gagal diverifikasi setelah disimpan.');
        }

        echo json_encode(["status" => "ok", "msg" => "Password karyawan berhasil diubah."]);
    } catch (PDOException $e) {
        error_log('Ubah password karyawan gagal: ' . $e->getMessage());
        echo json_encode(["status" => "error", "msg" => "Password karyawan gagal disimpan."]);
    } catch (Exception $e) {
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
            if (!boleh_kelola_akun_karyawan()) {
                throw new Exception("Akses ditolak: Anda tidak boleh mengubah password karyawan.");
            }
        }

        // NIK boleh sama dengan karyawan lain (tidak divalidasi unik).
        $nik_input = trim(input('nik_karyawan'));

        // Tipe karyawan opsional: otomatis memakai tipe yang tersedia.
        $tipeInfo    = resolve_tipe_karyawan($koneksi, input('id_tipe_karyawan'), input('tipe_karyawan'));
        $tipe_input  = $tipeInfo['nama'];
        $idTipeInput = $tipeInfo['id'];

        // Jam kerja opsional. Hanya disimpan untuk tipe Kontrak dan
        // otomatis 7 jam bila tidak dipilih.
        $jam_kerja_input = input('jam_kerja');
        if ($tipe_input === 'Kontrak') {
            if (!in_array($jam_kerja_input, ['7', '8'], true)) {
                $jam_kerja_input = '7';
            }
        } else {
            $jam_kerja_input = null;
        }

        // Jenis kelamin opsional: kosong/tidak valid disimpan NULL.
        $jk_val = input('jenis_kelamin');
        if (!in_array($jk_val, ['L', 'P'], true)) {
            $jk_val = null;
        }

        // 1. UPDATE tb_karyawan
        $k_data = [
            'nik_karyawan'      => $nik_input,
            'nama_lengkap'      => input('nama_lengkap'),
            'jenis_kelamin'     => $jk_val,
            'tgl_lahir'         => input('tgl_lahir'),
            'tempat_lahir'      => input('tempat_lahir'),
            'email_pribadi'     => input('email_pribadi'),
            'no_telp'           => input('no_telp'),
            'agama'             => input('agama'),
            'tipe_karyawan'     => $tipe_input,
            'id_tipe_karyawan'  => $idTipeInput,
            'jam_kerja'         => $jam_kerja_input
        ];

        // --- PROSES UPLOAD FOTO (EDIT) ---
        if (isset($_FILES['foto']['name']) && $_FILES['foto']['name'] != '') {
            // $nik_input sudah diambil & divalidasi di atas
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
            // API login mendukung bcrypt dan password_hash lebih aman daripada MD5.
            $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
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
            if (!password_verify($new_password, $stored_password)) {
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

        // Data identitas lama tidak lagi ditimpa oleh form (biodata utama saja),
        // agar data historis tetap terjaga.

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

        // 4. Kontak darurat tidak lagi dikelola dari form (biodata utama saja),
        //    data lama sengaja dibiarkan agar tidak hilang.

        $koneksi->commit();
        echo json_encode(["status" => "ok", "msg" => "Update berhasil untuk ID: $id"]);
    } catch (PDOException $e) {
        if ($koneksi->inTransaction()) $koneksi->rollBack();
        error_log($e->getMessage());
        $msg = "Gagal memperbarui data karyawan.";
        if (strpos($e->getMessage(), "Duplicate entry") !== false) {
            $msg = "Data gagal disimpan: ada data duplikat (NIK mungkin sudah digunakan).";
        }
        echo json_encode(["status" => "error", "msg" => $msg]);
    } catch (Exception $e) {
        if ($koneksi->inTransaction()) $koneksi->rollBack();
        echo json_encode(["status" => "error", "msg" => $e->getMessage()]);
    }
    exit;
}
// 3. DELETE (hapus permanen 1 karyawan beserta data turunannya)
if ($action == "delete") {
    $id = intval($_POST['id_karyawan']);
    if ($id <= 0) {
        echo json_encode(["status" => "error", "msg" => "ID karyawan tidak valid."]);
        exit;
    }

    $koneksi->beginTransaction();
    try {
        $terhapus = hapus_karyawan_lengkap($koneksi, $id);
        $koneksi->commit();

        if ($terhapus) {
            echo json_encode(["status" => "ok"]);
        } else {
            echo json_encode(["status" => "error", "msg" => "Data tidak ditemukan atau sudah terhapus."]);
        }
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

// 4. DELETE MASSAL: hapus karyawan yang dipilih via checkbox — khusus Master.
if ($action == "delete_selected") {
    $ids = $_POST['id_karyawan'] ?? [];
    if (!is_array($ids)) {
        $ids = explode(',', (string)$ids);
    }

    $ids = array_values(array_unique(array_filter(
        array_map('intval', $ids),
        function ($v) {
            return $v > 0;
        }
    )));

    if (empty($ids)) {
        echo json_encode(["status" => "error", "msg" => "Tidak ada karyawan yang dipilih untuk dihapus."]);
        exit;
    }

    $koneksi->beginTransaction();
    try {
        $hasil = hapus_karyawan_massal($koneksi, $ids);
        $koneksi->commit();
        echo json_encode(respon_hapus_massal($hasil));
    } catch (Exception $e) {
        if ($koneksi->inTransaction()) $koneksi->rollBack();
        error_log($e->getMessage());
        echo json_encode(["status" => "error", "msg" => "Gagal menghapus data karyawan."]);
    }
    exit;
}

// 5. DELETE ALL: hapus semua karyawan sesuai filter aktif tabel — khusus Master.
if ($action == "delete_all") {
    $filter_site    = $_POST['site'] ?? '';
    $filter_jabatan = $_POST['jabatan'] ?? '';
    $filter_status  = $_POST['status'] ?? '';
    $filter_tipe    = $_POST['tipe_karyawan'] ?? '';
    $filter_bulan   = $_POST['bulan'] ?? '';

    // WHERE harus identik dengan filter tabel di get-data/get-karyawan.php
    // (kar = tb_karyawan, k = data karier terbaru).
    $where = ["1=1"];
    $params = [];

    if (!empty($filter_status)) {
        if ($filter_status !== 'Semua') {
            $where[] = "kar.status_karyawan = ?";
            $params[] = $filter_status;
        }
    } else {
        $where[] = "kar.status_karyawan = 'Aktif'";
    }

    if (!empty($filter_site)) {
        $where[] = "k.id_site = ?";
        $params[] = $filter_site;
    }

    if (!empty($filter_jabatan)) {
        $where[] = "k.id_jabatan = ?";
        $params[] = $filter_jabatan;
    }

    if (!empty($filter_tipe) && in_array($filter_tipe, ['Kontrak', 'Harian'], true)) {
        $where[] = "kar.tipe_karyawan = ?";
        $params[] = $filter_tipe;
    }

    if (!empty($filter_bulan)) {
        $where[] = "MONTH(k.tgl_selesai) = ?";
        $params[] = $filter_bulan;
        $where[] = "YEAR(k.tgl_selesai) = YEAR(CURDATE())";
    }

    $where_sql = implode(" AND ", $where);

    $q = $koneksi->prepare("SELECT kar.id_karyawan
        FROM tb_karyawan kar
        LEFT JOIN tb_karier k ON kar.id_karyawan = k.id_karyawan
            AND k.id_karier = (SELECT MAX(k2.id_karier) FROM tb_karier k2 WHERE k2.id_karyawan = kar.id_karyawan)
        WHERE $where_sql
        ORDER BY kar.nik_karyawan ASC");
    $q->execute($params);

    $ids = [];
    while ($r = $q->fetch()) {
        $ids[] = (int)$r['id_karyawan'];
    }
    $ids = array_values(array_unique(array_filter($ids, function ($v) {
        return $v > 0;
    })));

    if (empty($ids)) {
        echo json_encode(["status" => "error", "msg" => "Tidak ada karyawan yang cocok dengan filter aktif."]);
        exit;
    }

    $koneksi->beginTransaction();
    try {
        $hasil = hapus_karyawan_massal($koneksi, $ids);
        $koneksi->commit();
        echo json_encode(respon_hapus_massal($hasil));
    } catch (Exception $e) {
        if ($koneksi->inTransaction()) $koneksi->rollBack();
        error_log($e->getMessage());
        echo json_encode(["status" => "error", "msg" => "Gagal menghapus data karyawan."]);
    }
    exit;
}
