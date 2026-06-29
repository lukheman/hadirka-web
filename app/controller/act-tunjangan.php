<?php
// Matikan display error, kirim ke log
ini_set('display_errors', 0);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error_log.txt'); // Log error ke file

header('Content-Type: application/json');

$response = ["status" => "error", "message" => "Aksi tidak dikenal"];

try {
    // Include file yang dibutuhkan
    $path_koneksi = __DIR__ . '/../config/koneksi.php';
    $path_function = __DIR__ . '/../config/function.php';

    if (!file_exists($path_koneksi)) {
        throw new Exception("File koneksi.php tidak ditemukan");
    }
    if (!file_exists($path_function)) {
        throw new Exception("File function.php tidak ditemukan");
    }

    require_once $path_koneksi;
    require_once $path_function;

    if (!isset($koneksi) || !$koneksi) {
        throw new Exception("Koneksi database gagal");
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';

        // Variabel umum
        $id_tun = isset($_POST['id_tunjangan']) ? mysqli_real_escape_string($koneksi, trim($_POST['id_tunjangan'])) : '';
        $id_kar = isset($_POST['id_karier']) ? mysqli_real_escape_string($koneksi, decrypt(trim($_POST['id_karier']))) : '';
        $jumlah = isset($_POST['jumlah_tunjangan']) ? mysqli_real_escape_string($koneksi, trim($_POST['jumlah_tunjangan'])) : '';
        $nama_raw = trim($_POST['nama_tunjangan'] ?? '');

        // Validasi nama tunjangan HANYA dilakukan saat add atau edit
        if ($action == "add" || $action == "edit") {
            $allowed = [
                "Tunjangan Daerah", "Tunjangan Hidup", "Tunjangan Makan",
                "Tunjangan Skill", "Bonus Kehadiran", "Tunjangan Posisi"
            ];

            if (!in_array($nama_raw, $allowed, true)) {
                throw new Exception("Nama tunjangan tidak valid: [$nama_raw]");
            }

            $nama = mysqli_real_escape_string($koneksi, $nama_raw);

            // Cek kelengkapan (menggunakan === '' agar angka 0 tetap lolos)
            if (empty($nama) || $jumlah === '') {
                throw new Exception("Data tidak lengkap");
            }

            if ($action == "add") {
                if (empty($id_kar)) throw new Exception("ID Karier tidak valid");

                $q = $koneksi->query("INSERT INTO tb_tunjangan (id_karier, nama_tunjangan, jumlah_tunjangan) VALUES ('$id_kar', '$nama', '$jumlah')");
                if ($q) {
                    $response = ["status" => "success", "message" => "Data berhasil ditambahkan"];
                } else {
                    throw new Exception("Query gagal: " . $koneksi->error);
                }
            } 
            elseif ($action == "edit") {
                if (empty($id_tun)) throw new Exception("ID Tunjangan tidak valid");

                $q = $koneksi->query("UPDATE tb_tunjangan SET nama_tunjangan='$nama', jumlah_tunjangan='$jumlah' WHERE id_tunjangan='$id_tun'");
                if ($q) {
                    $response = ["status" => "success", "message" => "Data berhasil diupdate"];
                } else {
                    throw new Exception("Query gagal: " . $koneksi->error);
                }
            }
        } 
        // Proses Hapus Data (Tidak memedulikan nama tunjangan)
        elseif ($action == "delete") {
            if (empty($id_tun)) {
                throw new Exception("ID tidak valid");
            }

            $q = $koneksi->query("DELETE FROM tb_tunjangan WHERE id_tunjangan='$id_tun'");
            if ($q) {
                $response = ["status" => "success", "message" => "Data berhasil dihapus"];
            } else {
                throw new Exception("Query gagal: " . $koneksi->error);
            }
        }
    }
} catch (Exception $e) {
    $response = ["status" => "error", "message" => $e->getMessage()];
    error_log("Error di act-tunjangan.php: " . $e->getMessage()); 
}

echo json_encode($response);
exit;