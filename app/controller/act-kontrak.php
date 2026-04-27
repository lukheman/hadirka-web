<?php
header("Content-Type: application/json");
require_once '../config/koneksi.php';

try {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    $id_jabatan = isset($_POST['id_jabatan']) ? intval($_POST['id_jabatan']) : 0;
    $upload_dir = '../uploads/kontrak/';

    if ($id_jabatan <= 0) {
        throw new Exception("ID Jabatan tidak valid.");
    }

    // --- FITUR HAPUS MASSAL ---
    if ($action === 'delete_massal') {
        // 1. Ambil nama file fisik yang ada di jabatan ini (bisa jadi ada bbrp file jika pernah manual)
        $qFile = $koneksi->query("
            SELECT DISTINCT k.file_kontrak 
            FROM tb_karier k 
            JOIN tb_karyawan kr ON k.id_karyawan = kr.id_karyawan 
            WHERE k.id_jabatan = '$id_jabatan' AND kr.status_karyawan = 'Aktif' AND k.file_kontrak IS NOT NULL
        ");

        // Hapus fisik filenya
        while ($rowFile = $qFile->fetch_assoc()) {
            $file = $rowFile['file_kontrak'];
            if (!empty($file) && file_exists($upload_dir . $file)) {
                unlink($upload_dir . $file);
            }
        }

        // 2. Set NULL di database
        $koneksi->query("
            UPDATE tb_karier k 
            JOIN tb_karyawan kr ON k.id_karyawan = kr.id_karyawan 
            SET k.file_kontrak = NULL 
            WHERE k.id_jabatan = '$id_jabatan' AND kr.status_karyawan = 'Aktif'
        ");

        echo json_encode(["status" => "success", "msg" => "File berhasil dihapus"]);
        exit;
    }

    // --- FITUR UPLOAD MASSAL ---
    if ($action === 'upload_massal') {
        if (!isset($_FILES['file_pdf']) || $_FILES['file_pdf']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Pilih file PDF terlebih dahulu.");
        }

        $file_tmp  = $_FILES['file_pdf']['tmp_name'];
        $file_name = $_FILES['file_pdf']['name'];
        $file_size = $_FILES['file_pdf']['size'];
        
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        if ($file_ext !== 'pdf') throw new Exception("Gagal! Format file harus .pdf");
        if ($file_size > 2097152) throw new Exception("Gagal! Ukuran file maksimal 2 MB.");

        // Dapatkan nama jabatan untuk nama file
        $cekJab = $koneksi->query("SELECT nama_jabatan FROM tb_jabatan WHERE id_jabatan='$id_jabatan'");
        $nama_jabatan = $cekJab->fetch_assoc()['nama_jabatan'];
        $nama_bersih = preg_replace('/[^A-Za-z0-9\-]/', '_', $nama_jabatan);
        $nama_bersih = preg_replace('/_+/', '_', $nama_bersih); 

        // Format nama: kontrak_massal_JABATAN_Timestamp.pdf
        $new_file_name = 'kontrak_massal_' . strtoupper($nama_bersih) . '_' . time() . '.pdf';
        
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        $upload_path = $upload_dir . $new_file_name;

        // Bersihkan file lama sebelum upload baru
        $qLama = $koneksi->query("SELECT DISTINCT k.file_kontrak FROM tb_karier k JOIN tb_karyawan kr ON k.id_karyawan = kr.id_karyawan WHERE k.id_jabatan = '$id_jabatan' AND kr.status_karyawan = 'Aktif' AND k.file_kontrak IS NOT NULL");
        while ($rowLama = $qLama->fetch_assoc()) {
            $f_lama = $rowLama['file_kontrak'];
            if (!empty($f_lama) && file_exists($upload_dir . $f_lama)) unlink($upload_dir . $f_lama);
        }

        // Pindah file baru
        if (move_uploaded_file($file_tmp, $upload_path)) {
            // Update Massal ke tabel Karier
            $queryUpdate = "
                UPDATE tb_karier k
                INNER JOIN tb_karyawan kr ON k.id_karyawan = kr.id_karyawan
                SET k.file_kontrak = ?
                WHERE k.id_jabatan = ? 
                  AND kr.status_karyawan = 'Aktif'
                  AND (k.tgl_selesai IS NULL OR k.tgl_selesai >= CURDATE())
            ";
            
            $stmt = $koneksi->prepare($queryUpdate);
            $stmt->bind_param("si", $new_file_name, $id_jabatan);
            
            if ($stmt->execute()) {
                echo json_encode(["status" => "success", "affected" => $stmt->affected_rows]);
            } else {
                unlink($upload_path); // Rollback
                throw new Exception("Gagal menyimpan nama file ke database.");
            }
        } else {
            throw new Exception("Gagal mengupload file ke server.");
        }
    }

} catch (Exception $e) {
    echo json_encode(["status" => "error", "msg" => $e->getMessage()]);
}
?>