<?php
header("Content-Type: application/json");
require_once '../config/koneksi.php';

try {
    // Tangkap action (upload atau edit)
    $action      = isset($_POST['action']) ? $_POST['action'] : 'upload';
    $id_karier   = isset($_POST['id_karier']) ? intval($_POST['id_karier']) : 0;
    $id_karyawan = isset($_POST['id_karyawan']) ? intval($_POST['id_karyawan']) : 0;

    if ($id_karier <= 0) {
        throw new Exception("ID Karier tidak valid.");
    }

    // --- 1. JIKA ACTION = EDIT (Update Tanggal Dulu) ---
    if ($action === 'edit') {
        $tgl_mulai = $_POST['tgl_mulai'] ?? null;
        $tgl_selesai = !empty($_POST['tgl_selesai']) ? $_POST['tgl_selesai'] : null;

        if (empty($tgl_mulai)) {
            throw new Exception("Tanggal mulai wajib diisi.");
        }

        // Perbaikan Bug NULL: Pisahkan query agar SQL benar-benar menerima nilai NULL
        if ($tgl_selesai === null) {
            $stmt_tgl = $koneksi->prepare("UPDATE tb_karier SET tgl_mulai = ?, tgl_selesai = NULL WHERE id_karier = ?");
            $stmt_tgl->bind_param("si", $tgl_mulai, $id_karier);
        } else {
            $stmt_tgl = $koneksi->prepare("UPDATE tb_karier SET tgl_mulai = ?, tgl_selesai = ? WHERE id_karier = ?");
            $stmt_tgl->bind_param("ssi", $tgl_mulai, $tgl_selesai, $id_karier);
        }

        if (!$stmt_tgl->execute()) {
            throw new Exception("Gagal mengupdate tanggal kontrak.");
        }
    }

    // --- FITUR BARU: HAPUS FILE ---
    if ($action === 'delete_file') {
        $upload_dir = '../uploads/kontrak/';
        
        // Cari nama file di DB
        $cekData = $koneksi->query("SELECT file_kontrak FROM tb_karier WHERE id_karier='$id_karier'");
        if ($cekData->num_rows > 0) {
            $file_lama = $cekData->fetch_assoc()['file_kontrak'];
            
            // Hapus file fisik jika ada
            if (!empty($file_lama) && file_exists($upload_dir . $file_lama)) {
                unlink($upload_dir . $file_lama);
            }

            // Update database menjadi NULL
            $koneksi->query("UPDATE tb_karier SET file_kontrak = NULL WHERE id_karier='$id_karier'");
            
            echo json_encode(["status" => "success", "msg" => "File berhasil dihapus"]);
            exit; // Hentikan script di sini agar tidak menjalankan upload
        } else {
            throw new Exception("Data kontrak tidak ditemukan.");
        }
    }

    // --- 3. CEK FILE UPLOAD ---
    $has_file = isset($_FILES['file_pdf']) && $_FILES['file_pdf']['error'] === UPLOAD_ERR_OK;

    // Jika lewat form Upload, file wajib ada. Jika form Edit, file opsional.
    if ($action === 'upload' && !$has_file) {
        throw new Exception("Pilih file PDF terlebih dahulu.");
    }

    // --- 4. JIKA ADA FILE, PROSES UPLOAD ---
    if ($has_file) {
        $file_tmp  = $_FILES['file_pdf']['tmp_name'];
        $file_name = $_FILES['file_pdf']['name'];
        $file_size = $_FILES['file_pdf']['size'];
        
        // Validasi Ekstensi
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        if ($file_ext !== 'pdf') {
            throw new Exception("Gagal! Format file harus .pdf");
        }

        // Validasi Ukuran (Maks 2MB)
        if ($file_size > 2097152) {
            throw new Exception("Gagal! Ukuran file maksimal 2 MB.");
        }

        // Ambil NIK dan Nama dari database
        $stmt_karyawan = $koneksi->prepare("SELECT nik_karyawan, nama_lengkap FROM tb_karyawan WHERE id_karyawan = ?");
        $stmt_karyawan->bind_param("i", $id_karyawan);
        $stmt_karyawan->execute();
        $res_karyawan = $stmt_karyawan->get_result();
        
        if ($res_karyawan->num_rows === 0) {
            throw new Exception("Data karyawan tidak ditemukan.");
        }
        
        $data_karyawan = $res_karyawan->fetch_assoc();
        $nik = $data_karyawan['nik_karyawan'];
        
        $nama_bersih = preg_replace('/[^A-Za-z0-9\-]/', '_', $data_karyawan['nama_lengkap']);
        $nama_bersih = preg_replace('/_+/', '_', $nama_bersih); 
        $tanggal_hari_ini = date('Y-m-d');

        $new_file_name = 'kontrak_' . $nik . '_' . $nama_bersih . '_' . $tanggal_hari_ini . '.pdf';
        $upload_dir = '../uploads/kontrak/';
        
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $upload_path = $upload_dir . $new_file_name;

        // Hapus file lama jika ada
        $cekLama = $koneksi->query("SELECT file_kontrak FROM tb_karier WHERE id_karier='$id_karier'");
        if ($cekLama->num_rows > 0) {
            $file_lama = $cekLama->fetch_assoc()['file_kontrak'];
            if (!empty($file_lama) && file_exists($upload_dir . $file_lama)) {
                unlink($upload_dir . $file_lama);
            }
        }

        // Pindah file ke folder tujuan
        if (move_uploaded_file($file_tmp, $upload_path)) {
            $stmt_file = $koneksi->prepare("UPDATE tb_karier SET file_kontrak = ? WHERE id_karier = ?");
            $stmt_file->bind_param("si", $new_file_name, $id_karier);
            if (!$stmt_file->execute()) {
                unlink($upload_path); // Hapus jika gagal save DB
                throw new Exception("Gagal menyimpan nama file ke database.");
            }
        } else {
            throw new Exception("Gagal mengupload file ke server.");
        }
    }

    echo json_encode(["status" => "success", "msg" => "Data berhasil disimpan"]);

} catch (Exception $e) {
    echo json_encode(["status" => "error", "msg" => $e->getMessage()]);
}
?>