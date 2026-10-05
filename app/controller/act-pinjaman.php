<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/function.php';

$action = $_POST['action'] ?? '';

switch($action){
    case 'add':
        $id_karyawan = $_POST['id_karyawan'] ?? '';
        $tgl_pinjaman = $_POST['tgl_pinjaman'] ?? '';
        $tgl_bayar_awal = $_POST['tgl_bayar_pinjaman'] ?? '';
        $nominal_total = (float) preg_replace('/\D/', '', $_POST['nominal_pinjaman'] ?? '0');
        $tenor = (int) ($_POST['tenor'] ?? 1);
        $ket = $_POST['ket_pinjaman'] ?? '';

        if(!$id_karyawan || !$tgl_pinjaman || !$tgl_bayar_awal || $nominal_total <= 0 || $tenor < 1){
            echo json_encode(['status'=>'error','msg'=>'Semua field wajib diisi dengan benar']); exit;
        }

        // Bagi rata nominal pinjaman
        $cicilan_dasar = floor($nominal_total / $tenor);
        $sisa_bagi = $nominal_total - ($cicilan_dasar * $tenor);

        // Gunakan transaksi agar jika 1 error, semua dibatalkan
        $koneksi->beginTransaction();
        try {
            $stmt = $koneksi->prepare("INSERT INTO tb_pinjaman(id_karyawan, tgl_pinjaman, tgl_bayar_pinjaman, nominal_pinjaman, ket_pinjaman) VALUES(?,?,?,?,?)");

            for ($j = 0; $j < $tenor; $j++) {
            $tgl_jatuh_tempo = date('Y-m-d', strtotime("+$j month", strtotime($tgl_bayar_awal)));

            // Cicilan bulan terakhir menanggung sisa pembagian agar totalnya genap
            $nominal_cicilan_ini = $cicilan_dasar;
            if ($j === ($tenor - 1)) {
                $nominal_cicilan_ini += $sisa_bagi;
            }

            $ket_cicilan = $ket;
            if ($tenor > 1) {
                $cicilan_ke = $j + 1;
                $ket_cicilan = $ket . " (Cicilan $cicilan_ke/$tenor)";
            }

                if (!$stmt->execute([$id_karyawan, $tgl_pinjaman, $tgl_jatuh_tempo, $nominal_cicilan_ini, $ket_cicilan])) {
                    throw new Exception("Gagal menyimpan data cicilan ke-$cicilan_ke");
                }
            }

            $koneksi->commit();
            echo json_encode(['status'=>'success']);

        } catch (PDOException $e) {
            if ($koneksi->inTransaction()) $koneksi->rollBack();
            error_log($e->getMessage());
            echo json_encode(['status'=>'error','msg'=>'Gagal menyimpan data pinjaman.']);
        } catch (Exception $e) {
            if ($koneksi->inTransaction()) $koneksi->rollBack();
            echo json_encode(['status'=>'error','msg'=>$e->getMessage()]);
        }
    break;

    case 'edit':
        $id = $_POST['id_pinjaman'] ?? '';
        $id_karyawan = $_POST['id_karyawan'] ?? '';
        $tgl_pinjaman = $_POST['tgl_pinjaman'] ?? '';
        $tgl_bayar = $_POST['tgl_bayar_pinjaman'] ?? null;
        $nominal = (float) preg_replace('/\D/', '', $_POST['nominal_pinjaman'] ?? '0');
        $ket = $_POST['ket_pinjaman'] ?? '';

        if(!$id || !$id_karyawan || !$tgl_pinjaman || !$nominal){
            echo json_encode(['status'=>'error','msg'=>'Field wajib diisi']); exit;
        }

        $stmt = $koneksi->prepare("UPDATE tb_pinjaman SET id_karyawan=?, tgl_pinjaman=?, tgl_bayar_pinjaman=?, nominal_pinjaman=?, ket_pinjaman=? WHERE id_pinjaman=?");
        try {
            echo $stmt->execute([$id_karyawan,$tgl_pinjaman,$tgl_bayar,$nominal,$ket,$id]) ? json_encode(['status'=>'success']) : json_encode(['status'=>'error','msg'=>'Gagal memperbarui data.']);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            echo json_encode(['status'=>'error','msg'=>'Gagal memperbarui data.']);
        }
    break;

    case 'delete':
        $id = $_POST['id_pinjaman'] ?? '';
        if(!$id){ echo json_encode(['status'=>'error','msg'=>'ID tidak ditemukan']); exit;}

        try {
            // 1. Cek apakah pinjaman ini punya relasi ke tabel pengajuan mobile
            $cekStmt = $koneksi->prepare("SELECT id_pengajuan_pinjaman FROM tb_pinjaman WHERE id_pinjaman = ?");
            $cekStmt->execute([$id]);
            $dataCek = $cekStmt->fetch();

            // 2. Jika data pengajuan ada (tidak NULL), ubah status pengajuannya jadi 'ditolak'
            if ($dataCek && !empty($dataCek['id_pengajuan_pinjaman'])) {
                $id_pengajuan = $dataCek['id_pengajuan_pinjaman'];
                $keterangan = "Ditunda oleh Admin langsung dari Sistem.";
                $status_batal = "pending"; // Bisa kamu ganti jadi 'pending' jika ingin dikembalikan ke antrean

                $updateStmt = $koneksi->prepare("UPDATE tb_pengajuan_pinjaman SET status_pinjaman = ?, keterangan_admin = ? WHERE id_pengajuan_pinjaman = ?");
                $updateStmt->execute([$status_batal, $keterangan, $id_pengajuan]);
            }

            // 3. Eksekusi hapus data dari master data pinjaman
            $stmt = $koneksi->prepare("DELETE FROM tb_pinjaman WHERE id_pinjaman=?");
            echo $stmt->execute([$id]) ? json_encode(['status'=>'success']) : json_encode(['status'=>'error','msg'=>'Gagal menghapus data.']);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            echo json_encode(['status'=>'error','msg'=>'Gagal menghapus data.']);
        }
    break;

    default:
        echo json_encode(['status'=>'error','msg'=>'Action tidak valid']);
    break;
}
