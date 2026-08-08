<?php
header("Content-Type: application/json");
require_once __DIR__ . '/../../config/koneksi.php';

$action = $_POST['action'] ?? '';

if ($action === 'generate_batch') {
    $koneksi->begin_transaction();
    try {
        $ids_karyawan = $_POST['id_karyawan'] ?? [];
        $id_pelanggaran = intval($_POST['id_pelanggaran']);
        $jenis_sanksi = $koneksi->real_escape_string($_POST['jenis_sanksi']);
        $masa = intval($_POST['masa_berlaku_bulan']);
        $tgl_surat = $_POST['tgl_surat'];
        $input_no_surat = $koneksi->real_escape_string($_POST['no_surat']);
        $ket = $koneksi->real_escape_string($_POST['keterangan_tambahan']);

        $tgl_berakhir = date('Y-m-d', strtotime("+$masa months", strtotime($tgl_surat)));

        $stmt = $koneksi->prepare("INSERT INTO tb_surat_peringatan (no_surat, id_karyawan, id_pelanggaran, jenis_sanksi, masa_berlaku_bulan, tgl_surat, tgl_berakhir, keterangan_tambahan) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

        $parts = explode('/', $input_no_surat, 2);
        $no_awal = intval($parts[0]);
        $suffix = isset($parts[1]) ? '/' . $parts[1] : '';

        foreach ($ids_karyawan as $id_k) {
            $id_k = intval($id_k);
            $no_surat_unik = sprintf("%03d", $no_awal) . $suffix;
            $stmt->bind_param("siisisss", $no_surat_unik, $id_k, $id_pelanggaran, $jenis_sanksi, $masa, $tgl_surat, $tgl_berakhir, $ket);
            $stmt->execute();
            $no_awal++;
        }

        $koneksi->commit();
        echo json_encode(["status" => "success", "msg" => count($ids_karyawan) . " Surat Sanksi berhasil dicatat."]);
    } catch (Exception $e) {
        $koneksi->rollback();
        echo json_encode(["status" => "error", "msg" => "Error: " . $e->getMessage()]);
    }
} elseif ($action === 'get_print_data') {
    $id_sp = intval($_POST['id_sp']);
    $q = $koneksi->query("SELECT sp.*, kr.nik_karyawan, kr.nama_lengkap, j.nama_jabatan 
        FROM tb_surat_peringatan sp
        INNER JOIN tb_karyawan kr ON sp.id_karyawan = kr.id_karyawan
        LEFT JOIN tb_karier k ON kr.id_karyawan = k.id_karyawan
        LEFT JOIN tb_jabatan j ON k.id_jabatan = j.id_jabatan
        WHERE sp.id_sp = $id_sp 
        ORDER BY k.id_karier DESC LIMIT 1");

    echo json_encode(["status" => "success", "data" => $q->fetch_assoc()]);
} elseif ($action === 'delete_sp') {
    $stmt = $koneksi->prepare("DELETE FROM tb_surat_peringatan WHERE id_sp=?");
    $stmt->bind_param("i", $_POST['id_sp']);
    echo json_encode(["status" => $stmt->execute() ? "success" : "error"]);
}
