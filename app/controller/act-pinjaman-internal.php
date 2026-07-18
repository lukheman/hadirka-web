<?php
header("Content-Type: application/json; charset=utf-8");
require_once __DIR__ . '/../config/koneksi.php';

$action = $_POST['action'] ?? '';
$response = ['status' => 'error', 'msg' => 'Terjadi kesalahan sistem'];

try {
    if ($action === 'delete') {
        $id_pinjaman = intval($_POST['id_pinjaman'] ?? 0);
        $stmt = $koneksi->prepare("DELETE FROM tb_pinjaman_internal WHERE id_pinjaman = ?");
        $stmt->bind_param("i", $id_pinjaman);
        
        if ($stmt->execute()) {
            $response = ['status' => 'success', 'msg' => 'Data pinjaman berhasil dihapus'];
        } else {
            $response = ['status' => 'error', 'msg' => 'Gagal menghapus: ' . $stmt->error];
        }
        echo json_encode($response);
        exit;
    }

    if ($action === 'edit') {
        $id_pinjaman = intval($_POST['id_pinjaman'] ?? 0);
        $id_karyawan = intval($_POST['id_karyawan_internal'] ?? 0);
        $tgl_pinjaman = $_POST['tgl_pinjaman'] ?? '';
        $tgl_bayar = $_POST['tgl_bayar_pinjaman'] ?? '';
        $nominal = (float) preg_replace('/\D/', '', $_POST['nominal_pinjaman'] ?? '0');
        $ket = trim($_POST['ket_pinjaman'] ?? '');

        if($id_karyawan <= 0 || empty($tgl_pinjaman) || empty($tgl_bayar) || $nominal <= 0) {
            throw new Exception("Data tidak lengkap!");
        }

        $stmt = $koneksi->prepare("UPDATE tb_pinjaman_internal SET id_karyawan_internal=?, tgl_pinjaman=?, tgl_bayar_pinjaman=?, nominal_pinjaman=?, ket_pinjaman=? WHERE id_pinjaman=?");
        $stmt->bind_param("issdsi", $id_karyawan, $tgl_pinjaman, $tgl_bayar, $nominal, $ket, $id_pinjaman);

        if($stmt->execute()) {
            $response = ['status' => 'success', 'msg' => 'Baris cicilan berhasil diupdate'];
        } else {
            throw new Exception($stmt->error);
        }
    }

    if ($action === 'add') {
        $id_karyawan = intval($_POST['id_karyawan_internal'] ?? 0);
        $tgl_pinjaman = $_POST['tgl_pinjaman'] ?? '';
        $tgl_bayar_awal = $_POST['tgl_bayar_pinjaman'] ?? '';
        $nominal_total = (float) preg_replace('/\D/', '', $_POST['nominal_pinjaman'] ?? '0');
        $tenor = (int) ($_POST['tenor'] ?? 1);
        $ket = trim($_POST['ket_pinjaman'] ?? '');

        if($id_karyawan <= 0 || empty($tgl_pinjaman) || empty($tgl_bayar_awal) || $nominal_total <= 0 || $tenor < 1) {
            throw new Exception("Lengkapi seluruh form input!");
        }

        $cicilan_dasar = floor($nominal_total / $tenor);
        $sisa_bagi = $nominal_total - ($cicilan_dasar * $tenor);

        mysqli_begin_transaction($koneksi);
        $stmt = $koneksi->prepare("INSERT INTO tb_pinjaman_internal (id_karyawan_internal, tgl_pinjaman, tgl_bayar_pinjaman, nominal_pinjaman, ket_pinjaman) VALUES (?,?,?,?,?)");

        for ($j = 0; $j < $tenor; $j++) {
            $tgl_jatuh_tempo = date('Y-m-d', strtotime("+$j month", strtotime($tgl_bayar_awal)));
            
            $nominal_cicilan_ini = $cicilan_dasar;
            if ($j === ($tenor - 1)) {
                $nominal_cicilan_ini += $sisa_bagi; 
            }

            $ket_cicilan = $ket;
            if ($tenor > 1) {
                $cicilan_ke = $j + 1;
                $ket_cicilan = $ket . " (Cicilan $cicilan_ke/$tenor)";
            }

            $stmt->bind_param("issds", $id_karyawan, $tgl_pinjaman, $tgl_jatuh_tempo, $nominal_cicilan_ini, $ket_cicilan);
            if (!$stmt->execute()) {
                throw new Exception("Gagal insert cicilan ke-$cicilan_ke");
            }
        }
        $stmt->close();
        mysqli_commit($koneksi);
        $response = ['status' => 'success', 'msg' => 'Pinjaman & cicilan berhasil di-generate'];
    }

} catch (Exception $e) {
    if (isset($koneksi)) mysqli_rollback($koneksi);
    $response = ['status' => 'error', 'msg' => $e->getMessage()];
}

echo json_encode($response);
exit;