<?php
require '../../../config/koneksi.php'; // sesuaikan path

$action = $_POST['action'] ?? '';

// ===================================================================
// GET DATA KARYAWAN BY ID (UNTUK EDIT)
// ===================================================================
if ($action == "get") {

    $id = $_POST['id_karyawan'];

    $sql = "
        SELECT k.*, i.*
        FROM tb_karyawan k
        LEFT JOIN tb_identitas i ON k.id_karyawan = i.id_karyawan
        WHERE k.id_karyawan = '$id'
    ";

    $q = $conn->query($sql);

    if ($q->num_rows > 0) {
        $data = $q->fetch_assoc();
        echo json_encode(["status" => "success", "data" => $data]);
    } else {
        echo json_encode(["status" => "error", "message" => "Data tidak ditemukan"]);
    }
    exit;
}

// ===================================================================
// ADD KARYAWAN
// ===================================================================
if ($action == "add") {

    $nik_karyawan       = $_POST['nik_karyawan'];
    $nama_lengkap       = $_POST['nama_lengkap'];
    $jenis_kelamin      = $_POST['jenis_kelamin'];
    $tgl_lahir          = $_POST['tgl_lahir'];
    $tempat_lahir       = $_POST['tempat_lahir'];
    $email_pribadi      = $_POST['email_pribadi'];
    $no_telp            = $_POST['no_telp'];
    $agama              = $_POST['agama'];
    $status_pernikahan  = $_POST['status_pernikahan'];

    $nik_ktp        = $_POST['nik_ktp'];
    $kk_number      = $_POST['kk_number'];
    $npwp           = $_POST['npwp'];
    $bpjs_tk        = $_POST['bpjs_tk'];
    $bpjs_kes       = $_POST['bpjs_kes'];
    $alamat_ktp     = $_POST['alamat_ktp'];

    // Insert ke tb_karyawan
    $sql1 = "
        INSERT INTO tb_karyawan (
            nik_karyawan, nama_lengkap, jenis_kelamin, tgl_lahir, tempat_lahir,
            email_pribadi, no_telp, agama, status_pernikahan
        ) VALUES (
            '$nik_karyawan', '$nama_lengkap', '$jenis_kelamin', '$tgl_lahir', '$tempat_lahir',
            '$email_pribadi', '$no_telp', '$agama', '$status_pernikahan'
        )
    ";

    if ($conn->query($sql1)) {

        $id_karyawan = $conn->insert_id;

        // Insert ke tb_identitas
        $sql2 = "
            INSERT INTO tb_identitas (
                id_karyawan, nik_ktp, kk_number, npwp, bpjs_tk, bpjs_kes, alamat_ktp
            ) VALUES (
                '$id_karyawan', '$nik_ktp', '$kk_number', '$npwp', '$bpjs_tk', '$bpjs_kes', '$alamat_ktp'
            )
        ";

        $conn->query($sql2);

        echo json_encode(["status" => "success", "message" => "Data karyawan berhasil ditambahkan"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Gagal menambahkan data"]);
    }
    exit;
}

// ===================================================================
// EDIT KARYAWAN
// ===================================================================
if ($action == "edit") {

    $id_karyawan        = $_POST['id_karyawan'];

    $nik_karyawan       = $_POST['nik_karyawan'];
    $nama_lengkap       = $_POST['nama_lengkap'];
    $jenis_kelamin      = $_POST['jenis_kelamin'];
    $tgl_lahir          = $_POST['tgl_lahir'];
    $tempat_lahir       = $_POST['tempat_lahir'];
    $email_pribadi      = $_POST['email_pribadi'];
    $no_telp            = $_POST['no_telp'];
    $agama              = $_POST['agama'];
    $status_pernikahan  = $_POST['status_pernikahan'];

    $nik_ktp        = $_POST['nik_ktp'];
    $kk_number      = $_POST['kk_number'];
    $npwp           = $_POST['npwp'];
    $bpjs_tk        = $_POST['bpjs_tk'];
    $bpjs_kes       = $_POST['bpjs_kes'];
    $alamat_ktp     = $_POST['alamat_ktp'];

    // Update tb_karyawan
    $sql1 = "
        UPDATE tb_karyawan SET
            nik_karyawan       = '$nik_karyawan',
            nama_lengkap       = '$nama_lengkap',
            jenis_kelamin      = '$jenis_kelamin',
            tgl_lahir          = '$tgl_lahir',
            tempat_lahir       = '$tempat_lahir',
            email_pribadi      = '$email_pribadi',
            no_telp            = '$no_telp',
            agama              = '$agama',
            status_pernikahan  = '$status_pernikahan'
        WHERE id_karyawan = '$id_karyawan'
    ";

    // Update tb_identitas
    $sql2 = "
        UPDATE tb_identitas SET
            nik_ktp        = '$nik_ktp',
            kk_number      = '$kk_number',
            npwp           = '$npwp',
            bpjs_tk        = '$bpjs_tk',
            bpjs_kes       = '$bpjs_kes',
            alamat_ktp     = '$alamat_ktp'
        WHERE id_karyawan = '$id_karyawan'
    ";

    if ($conn->query($sql1) && $conn->query($sql2)) {
        echo json_encode(["status" => "success", "message" => "Data karyawan berhasil diperbarui"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Gagal mengupdate data"]);
    }
    exit;
}

// ===================================================================
// DELETE KARYAWAN
// ===================================================================
if ($action == "delete") {

    $id = $_POST['id_karyawan'];

    // hapus identitas dulu (FK)
    $conn->query("DELETE FROM tb_identitas WHERE id_karyawan = '$id'");
    $conn->query("DELETE FROM tb_karyawan WHERE id_karyawan = '$id'");

    echo json_encode(["status" => "success", "message" => "Data berhasil dihapus"]);
    exit;
}

echo json_encode(["status" => "error", "message" => "Invalid request"]);
exit;
?>
