<?php
header("Content-Type: application/json");

try {
    include '../config/koneksi.php';

    if (!$koneksi) {
        throw new Exception("Database connection failed");
    }

    // Get POST data
    $id_karyawan = $_POST['id_karyawan'] ?? null;
    $nama_bank = $_POST['nama_bank'] ?? null;
    $atas_nama_rek = $_POST['atas_nama_rek'] ?? '';
    $no_rek = $_POST['no_rek'] ?? '';

    // Validation
    if (!$id_karyawan) {
        throw new Exception("ID Karyawan tidak valid");
    }

    if (!$nama_bank) {
        throw new Exception("Nama bank wajib dipilih");
    }

    // Validasi: Jika bukan "BELUM_PUNYA", maka atas_nama_rek dan no_rek wajib diisi
    if ($nama_bank !== 'BELUM_PUNYA') {
        if (empty(trim($atas_nama_rek))) {
            throw new Exception("Atas nama rekening wajib diisi untuk bank " . $nama_bank);
        }
        if (empty(trim($no_rek))) {
            throw new Exception("Nomor rekening wajib diisi untuk bank " . $nama_bank);
        }
    }

    // Jika BELUM_PUNYA, set empty values
    if ($nama_bank === 'BELUM_PUNYA') {
        $nama_bank = 'BELUM PUNYA REKENING';
        $atas_nama_rek = '';
        $no_rek = '';
    }

    // Sanitize input
    $nama_bank = mysqli_real_escape_string($koneksi, $nama_bank);
    $atas_nama_rek = mysqli_real_escape_string($koneksi, trim($atas_nama_rek));
    $no_rek = mysqli_real_escape_string($koneksi, trim($no_rek));
    $id_karyawan = mysqli_real_escape_string($koneksi, $id_karyawan);

    // Check if record exists in tb_identitas
    $checkSql = "SELECT id_identitas FROM tb_identitas WHERE id_karyawan = '$id_karyawan'";
    $checkResult = mysqli_query($koneksi, $checkSql);

    if (mysqli_num_rows($checkResult) > 0) {
        // UPDATE existing record
        $sql = "UPDATE tb_identitas SET 
                    nama_bank = '$nama_bank',
                    atas_nama_rek = '$atas_nama_rek',
                    no_rek = '$no_rek'
                WHERE id_karyawan = '$id_karyawan'";
    } else {
        // INSERT new record
        $sql = "INSERT INTO tb_identitas (id_karyawan, nama_bank, atas_nama_rek, no_rek) 
                VALUES ('$id_karyawan', '$nama_bank', '$atas_nama_rek', '$no_rek')";
    }

    $result = mysqli_query($koneksi, $sql);

    if (!$result) {
        throw new Exception("Query failed: " . mysqli_error($koneksi));
    }

    echo json_encode([
        "status" => "success",
        "message" => "Data rekening berhasil diupdate"
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}

exit;