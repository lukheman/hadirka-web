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
    $nama_bank = trim($nama_bank);
    $atas_nama_rek = trim($atas_nama_rek);
    $no_rek = trim($no_rek);

    // Check if record exists in tb_identitas
    $check = $koneksi->prepare("SELECT id_identitas FROM tb_identitas WHERE id_karyawan = ?");
    $check->execute([$id_karyawan]);
    $exists = $check->fetch();

    if ($exists) {
        // UPDATE existing record
        $st = $koneksi->prepare("UPDATE tb_identitas SET
                    nama_bank = ?,
                    atas_nama_rek = ?,
                    no_rek = ?
                WHERE id_karyawan = ?");
        $st->execute([$nama_bank, $atas_nama_rek, $no_rek, $id_karyawan]);
    } else {
        // INSERT new record
        $st = $koneksi->prepare("INSERT INTO tb_identitas (id_karyawan, nama_bank, atas_nama_rek, no_rek)
                VALUES (?, ?, ?, ?)");
        $st->execute([$id_karyawan, $nama_bank, $atas_nama_rek, $no_rek]);
    }

    echo json_encode([
        "status" => "success",
        "message" => "Data rekening berhasil diupdate"
    ]);

} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Gagal memperbarui data rekening."
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}

exit;
