<?php
header("Content-Type: application/json");
require_once '../config/koneksi.php';

try {
    $id_karier = isset($_POST['id_karier']) ? intval($_POST['id_karier']) : 0;
    $id_karyawan = isset($_POST['id_karyawan']) ? intval($_POST['id_karyawan']) : 0;

    if ($id_karier <= 0) {
        throw new Exception("ID Karier tidak valid.");
    }

    if (!isset($_FILES['file_pdf']) || $_FILES['file_pdf']['error'] === UPLOAD_ERR_NO_FILE) {
        throw new Exception("Pilih file PDF terlebih dahulu.");
    }

    $file_tmp = $_FILES['file_pdf']['tmp_name'];
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

    // --- AMBIL NIK DAN NAMA DARI DATABASE ---
    $stmt_karyawan = $koneksi->prepare("SELECT nik_karyawan, nama_lengkap FROM tb_karyawan WHERE id_karyawan = ?");
    $stmt_karyawan->bind_param("i", $id_karyawan);
    $stmt_karyawan->execute();
    $res_karyawan = $stmt_karyawan->get_result();
    
    if ($res_karyawan->num_rows === 0) {
        throw new Exception("Data karyawan tidak ditemukan.");
    }
    
    $data_karyawan = $res_karyawan->fetch_assoc();
    $nik = $data_karyawan['nik_karyawan'];
    
    // Bersihkan nama dari spasi atau karakter aneh agar aman untuk nama file (spasi diganti underscore)
    $nama_bersih = preg_replace('/[^A-Za-z0-9\-]/', '_', $data_karyawan['nama_lengkap']);
    // Hapus underscore ganda jika ada
    $nama_bersih = preg_replace('/_+/', '_', $nama_bersih); 
    
    $tanggal_hari_ini = date('Y-m-d'); // Output: 2026-04-21

    // Buat nama file sesuai format: kontrak_nik_nama_tanggal.pdf
    $new_file_name = 'kontrak_' . $nik . '_' . $nama_bersih . '_' . $tanggal_hari_ini . '.pdf';
    
    // --- PENYESUAIAN PATH FOLDER ---
    // Karena act-kontrak.php ada di app/controller/, kita hanya perlu naik 1 tingkat (..) untuk masuk ke app/uploads/
    $upload_dir = '../uploads/kontrak/';
    
    // Cek apakah folder ada, jika tidak buat foldernya
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $upload_path = $upload_dir . $new_file_name;

    // Cek apakah data karier ini sudah punya file lama, jika ada, hapus file fisiknya
    $cekLama = $koneksi->query("SELECT file_kontrak FROM tb_karier WHERE id_karier='$id_karier'");
    if ($cekLama->num_rows > 0) {
        $file_lama = $cekLama->fetch_assoc()['file_kontrak'];
        if (!empty($file_lama) && file_exists($upload_dir . $file_lama)) {
            unlink($upload_dir . $file_lama); // Hapus file lama untuk menghemat penyimpanan
        }
    }

    // Proses pindah file dari temp ke folder tujuan
    if (move_uploaded_file($file_tmp, $upload_path)) {
        // Update database
        $stmt = $koneksi->prepare("UPDATE tb_karier SET file_kontrak = ? WHERE id_karier = ?");
        $stmt->bind_param("si", $new_file_name, $id_karier);
        
        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "msg" => "File berhasil diupload"]);
        } else {
            // Jika database gagal diupdate, hapus file yang telanjur terupload
            unlink($upload_path);
            throw new Exception("Gagal menyimpan ke database.");
        }
    } else {
        throw new Exception("Gagal mengupload file ke server.");
    }

} catch (Exception $e) {
    echo json_encode(["status" => "error", "msg" => $e->getMessage()]);
}
?>