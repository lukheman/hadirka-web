<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/function.php';

header("Content-Type: application/json");

$action = $_POST['action'] ?? '';

// Finance (level 6) read-only: blokir eliminasi/reaktivasi.
// Aksi "get" tetap diizinkan karena hanya membaca.
if ((($_SESSION['level'] ?? 99) == 6) && in_array($action, ['eliminasi', 'reaktivasi'], true)) {
    echo json_encode(["status" => "error", "msg" => "Akses ditolak: akun Finance hanya boleh melihat data karyawan."]);
    exit;
}

function input($data)
{
    return isset($_POST[$data]) ? trim($_POST[$data]) : '';
}

if ($action == "get") { // Ambil data eliminasi untuk koreksi
    $id_karyawan = isset($_POST['id_karyawan']) ? (int)$_POST['id_karyawan'] : 0;
    if ($id_karyawan <= 0) {
        echo json_encode(["status" => "error", "msg" => "ID Karyawan tidak valid"]);
        exit;
    }
    $st = $koneksi->prepare("SELECT id_tereliminasi, id_karyawan, tgl_keluar, id_jenis_keluar, alasan_keluar FROM tb_tereliminasi WHERE id_karyawan = ? LIMIT 1");
    $st->execute([$id_karyawan]);
    $row = $st->fetch();
    if ($row) {
        echo json_encode(["status" => "ok", "data" => $row]);
    } else {
        echo json_encode(["status" => "error", "msg" => "Data eliminasi tidak ditemukan"]);
    }
    exit;
}

if ($action == "reaktivasi") { // Kembalikan karyawan menjadi Aktif
    $koneksi->beginTransaction();
    try {
        $id_karyawan = isset($_POST['id_karyawan']) ? (int)$_POST['id_karyawan'] : 0;
        if ($id_karyawan <= 0) {
            throw new Exception("ID Karyawan tidak valid.");
        }

        $cek = $koneksi->prepare("SELECT id_karyawan, nama_lengkap FROM tb_karyawan WHERE id_karyawan = ?");
        $cek->execute([$id_karyawan]);
        $cekRow = $cek->fetch();
        if (!$cekRow) {
            throw new Exception("Karyawan tidak ditemukan.");
        }
        $nama = $cekRow['nama_lengkap'];

        $upd = $koneksi->prepare("UPDATE tb_karyawan SET status_karyawan = 'Aktif' WHERE id_karyawan = ?");
        if (!$upd->execute([$id_karyawan])) {
            throw new Exception("Gagal mengaktifkan karyawan.");
        }
        $del = $koneksi->prepare("DELETE FROM tb_tereliminasi WHERE id_karyawan = ?");
        if (!$del->execute([$id_karyawan])) {
            throw new Exception("Gagal menghapus data eliminasi.");
        }

        $koneksi->commit();
        echo json_encode(["status" => "ok", "msg" => "Karyawan $nama berhasil diaktifkan kembali."]);
    } catch (PDOException $e) {
        if ($koneksi->inTransaction()) $koneksi->rollBack();
        error_log($e->getMessage());
        echo json_encode(["status" => "error", "msg" => "Gagal memproses reaktivasi."]);
    } catch (Exception $e) {
        if ($koneksi->inTransaction()) $koneksi->rollBack();
        echo json_encode(["status" => "error", "msg" => $e->getMessage()]);
    }
    exit;
}

if ($action == "eliminasi") { // Proses eliminasi
    $koneksi->beginTransaction();

    try {
        // Ambil data dari form
        $nik_karyawan = input('nik_karyawan');
        $tgl_keluar = input('tgl_keluar');
        $id_jenis_keluar = input('id_jenis_keluar');
        $alasan_keluar = input('alasan_keluar');

        // Validasi NIK dan tgl tidak boleh kosong
        if (empty($nik_karyawan)) {
            throw new Exception("NIK Karyawan tidak boleh kosong.");
        }
        if (empty($tgl_keluar)) {
            throw new Exception("Tanggal keluar wajib diisi.");
        }


        // Cari id_karyawan berdasarkan NIK
        $stCheck = $koneksi->prepare("SELECT id_karyawan FROM tb_karyawan WHERE nik_karyawan = ?");
        $stCheck->execute([$nik_karyawan]);
        $row = $stCheck->fetch();

        if (!$row) {
            throw new Exception("Karyawan dengan NIK $nik_karyawan tidak ditemukan.");
        }

        $id_karyawan = $row['id_karyawan'];

        // Cari nama_kategori berdasarkan id_jenis_keluar
        $stJenis = $koneksi->prepare("SELECT id_jenis_keluar FROM m_jenis_keluar WHERE id_jenis_keluar = ?");
        $stJenis->execute([$id_jenis_keluar]);
        $row_jenis = $stJenis->fetch();

        if (!$row_jenis) {
            throw new Exception("Jenis keluar tidak valid.");
        }

        $id_jenis_keluar = $row_jenis['id_jenis_keluar'];


        // cek apakah sudah ada eliminasi
        $cekEliminasi = $koneksi->prepare("
            SELECT id_tereliminasi
            FROM tb_tereliminasi
            WHERE id_karyawan = ?
        ");
        $cekEliminasi->execute([$id_karyawan]);
        $elimAda = $cekEliminasi->fetch();

        if ($elimAda) {
            // UPDATE (koreksi data eliminasi)
            $stElim = $koneksi->prepare("UPDATE tb_tereliminasi SET
                tgl_keluar = ?,
                id_jenis_keluar = ?,
                alasan_keluar = ?,
                created_at = NOW()
            WHERE id_karyawan = ?");
            $okElim = $stElim->execute([$tgl_keluar, $id_jenis_keluar, $alasan_keluar, $id_karyawan]);
        } else {
            // INSERT (eliminasi baru)
            $stElim = $koneksi->prepare("INSERT INTO tb_tereliminasi
                (id_karyawan, tgl_keluar, id_jenis_keluar, alasan_keluar, created_at)
            VALUES
                (?, ?, ?, ?, NOW())");
            $okElim = $stElim->execute([$id_karyawan, $tgl_keluar, $id_jenis_keluar, $alasan_keluar]);
        }

        if (!$okElim) {
            throw new Exception("Gagal menyimpan data eliminasi.");
        }

        // 2. Update status_karyawan di tb_karyawan menjadi 'Resign' atau 'Cut'
        switch ($id_jenis_keluar) {
            case 1: // Habis Kontrak
                $status_baru = 'Habis Kontrak';
                break;
            case 2: // Pensiun
                $status_baru = 'Pensiun';
                break;
            case 3: // PHK
                $status_baru = 'PHK';
                break;
            case 4: // Resign
                $status_baru = 'Resign';
                break;
            case 5: // Black List
                $status_baru = 'Black List';
                break;
            default:
                throw new Exception("Jenis keluar tidak valid");
        }


        $stUpd = $koneksi->prepare("UPDATE tb_karyawan
                      SET status_karyawan = ?
                      WHERE id_karyawan = ?");

        if (!$stUpd->execute([$status_baru, $id_karyawan])) {
            throw new Exception("Gagal update status karyawan.");
        }

        $koneksi->commit();
        echo json_encode([
            "status" => "ok",
            "msg" => "Karyawan berhasil dieliminasi dengan status: $status_baru"
        ]);
    } catch (PDOException $e) {
        if ($koneksi->inTransaction()) $koneksi->rollBack();
        error_log($e->getMessage());
        echo json_encode([
            "status" => "error",
            "msg" => "Gagal memproses eliminasi."
        ]);
    } catch (Exception $e) {
        if ($koneksi->inTransaction()) $koneksi->rollBack();
        echo json_encode([
            "status" => "error",
            "msg" => $e->getMessage()
        ]);
    }
    exit;
}

// Jika action tidak dikenali
echo json_encode(["status" => "error", "msg" => "Action tidak valid"]);
