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

if ($action == "eliminasi") { // Proses eliminasi
    $koneksi->begin_transaction();

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
        $sql_check = "SELECT id_karyawan FROM tb_karyawan WHERE nik_karyawan = '$nik_karyawan'";
        $result = $koneksi->query($sql_check);

        if ($result->num_rows === 0) {
            throw new Exception("Karyawan dengan NIK $nik_karyawan tidak ditemukan.");
        }

        $row = $result->fetch_assoc();
        $id_karyawan = $row['id_karyawan'];

        // Cari nama_kategori berdasarkan id_jenis_keluar
        $sql_jenis = "SELECT id_jenis_keluar FROM m_jenis_keluar WHERE id_jenis_keluar = '$id_jenis_keluar'";
        $result_jenis = $koneksi->query($sql_jenis);

        if ($result_jenis->num_rows === 0) {
            throw new Exception("Jenis keluar tidak valid.");
        }

        $row_jenis = $result_jenis->fetch_assoc();
        $id_jenis_keluar = $row_jenis['id_jenis_keluar'];


        // cek apakah sudah ada eliminasi
        $cekEliminasi = $koneksi->query("
            SELECT id_tereliminasi 
            FROM tb_tereliminasi 
            WHERE id_karyawan = '$id_karyawan'
        ");

        if ($cekEliminasi->num_rows > 0) {
            // UPDATE (koreksi data eliminasi)
            $sqlEliminasi = "UPDATE tb_tereliminasi SET
                tgl_keluar = '$tgl_keluar',
                id_jenis_keluar = '$id_jenis_keluar',
                alasan_keluar = '$alasan_keluar',
                updated_at = NOW()
            WHERE id_karyawan = '$id_karyawan'";
        } else {
            // INSERT (eliminasi baru)
            $sqlEliminasi = "INSERT INTO tb_tereliminasi
                (id_karyawan, tgl_keluar, id_jenis_keluar, alasan_keluar, created_at)
            VALUES
                ('$id_karyawan', '$tgl_keluar', '$id_jenis_keluar', '$alasan_keluar', NOW())";
        }

        if (!$koneksi->query($sqlEliminasi)) {
            throw new Exception("Gagal menyimpan data eliminasi: " . $koneksi->error);
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


        $sql_update = "UPDATE tb_karyawan 
                      SET status_karyawan = '$status_baru' 
                      WHERE id_karyawan = '$id_karyawan'";

        if (!$koneksi->query($sql_update)) {
            throw new Exception("Gagal update status karyawan: " . $koneksi->error);
        }

        $koneksi->commit();
        echo json_encode([
            "status" => "ok",
            "msg" => "Karyawan berhasil dieliminasi dengan status: $status_baru"
        ]);
    } catch (Exception $e) {
        $koneksi->rollback();
        echo json_encode([
            "status" => "error",
            "msg" => $e->getMessage()
        ]);
    }
    exit;
}

// Jika action tidak dikenali
echo json_encode(["status" => "error", "msg" => "Action tidak valid"]);
