<?php
header("Content-Type: application/json");
require_once __DIR__ . '/../../config/koneksi.php';

$action = $_POST['action'] ?? '';

if ($action === 'generate_batch') {
    $koneksi->begin_transaction();
    try {
        $ids_karyawan = $_POST['id_karyawan'] ?? [];
        $id_pelanggaran = intval($_POST['id_pelanggaran']);
        $tgl_surat = $_POST['tgl_surat'];
        $input_no_surat = $koneksi->real_escape_string($_POST['no_surat']);
        $ket = $koneksi->real_escape_string($_POST['keterangan_tambahan']);

        $qPel = $koneksi->query("SELECT masa_berlaku_bulan FROM m_jenis_pelanggaran WHERE id_pelanggaran = $id_pelanggaran");
        $masa = intval($qPel->fetch_assoc()['masa_berlaku_bulan']);
        $tgl_berakhir = date('Y-m-d', strtotime("+$masa months", strtotime($tgl_surat)));

        $stmt = $koneksi->prepare("INSERT INTO tb_surat_peringatan (no_surat, id_karyawan, id_pelanggaran, tgl_surat, tgl_berakhir, keterangan_tambahan) VALUES (?, ?, ?, ?, ?, ?)");

        // Pecah input nomor surat untuk mendapatkan angka depannya (Contoh: "344" dari "344/TJS/V/2026")
        $parts = explode('/', $input_no_surat, 2);
        $no_awal = intval($parts[0]); 
        $suffix = isset($parts[1]) ? '/' . $parts[1] : '';

        // Looping pembuatan surat
        foreach ($ids_karyawan as $id_k) {
            $id_k = intval($id_k);
            
            // Format ulang nomor surat (344/..., 345/..., 346/...)
            $no_surat_unik = sprintf("%03d", $no_awal) . $suffix;
            
            $stmt->bind_param("siisss", $no_surat_unik, $id_k, $id_pelanggaran, $tgl_surat, $tgl_berakhir, $ket);
            $stmt->execute();
            
            $no_awal++; // Tambah counter untuk karyawan berikutnya
        }

        $koneksi->commit();
        echo json_encode(["status" => "success", "msg" => count($ids_karyawan) . " Surat Sanksi berhasil dicatat."]);
    } catch (Exception $e) {
        $koneksi->rollback();
        echo json_encode(["status" => "error", "msg" => "Error: " . $e->getMessage()]);
    }
}

elseif ($action === 'get_print_data') {
    $id_sp = intval($_POST['id_sp']);
    $q = $koneksi->query("SELECT sp.*, kr.nik_karyawan, kr.nama_lengkap, j.nama_jabatan, mp.jenis_sanksi, mp.masa_berlaku_bulan 
        FROM tb_surat_peringatan sp
        INNER JOIN tb_karyawan kr ON sp.id_karyawan = kr.id_karyawan
        LEFT JOIN tb_karier k ON kr.id_karyawan = k.id_karyawan
        LEFT JOIN tb_jabatan j ON k.id_jabatan = j.id_jabatan
        INNER JOIN m_jenis_pelanggaran mp ON sp.id_pelanggaran = mp.id_pelanggaran
        WHERE sp.id_sp = $id_sp 
        ORDER BY k.id_karier DESC LIMIT 1");
    
    echo json_encode(["status" => "success", "data" => $q->fetch_assoc()]);
}

elseif ($action === 'delete_sp') {
    $stmt = $koneksi->prepare("DELETE FROM tb_surat_peringatan WHERE id_sp=?");
    $stmt->bind_param("i", $_POST['id_sp']);
    echo json_encode(["status" => $stmt->execute() ? "success" : "error"]);
}