<?php
require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../../config/function.php';

header("Content-Type: application/json");

$action = $_POST['action'] ?? '';

/* ============================================================
   1. ADD KARYAWAN
   ============================================================ */
if ($action == "add") {

    $nama = $koneksi->real_escape_string($_POST['nama_lengkap']);
    $nik = $koneksi->real_escape_string($_POST['nik_karyawan']);
    $jk = $koneksi->real_escape_string($_POST['jenis_kelamin']);
    $agama = $koneksi->real_escape_string($_POST['agama']);
    $tgl = $koneksi->real_escape_string($_POST['tgl_lahir']);
    $tmp = $koneksi->real_escape_string($_POST['tempat_lahir']);
    $telp = $koneksi->real_escape_string($_POST['no_telp']);

    $q = $koneksi->query("
        INSERT INTO tb_karyawan (nama_lengkap, nik_karyawan, jenis_kelamin, agama, tgl_lahir, tempat_lahir, no_telp)
        VALUES ('$nama', '$nik', '$jk', '$agama', '$tgl', '$tmp', '$telp')
    ");

    if ($q) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "msg" => $koneksi->error]);
    }
    exit;
}

/* ============================================================
   2. GET DATA UNTUK EDIT (LOAD MODAL)
   ============================================================ */
if ($action == "get") {

    $id = intval($_POST['id_karyawan']);
    $q = $koneksi->query("SELECT * FROM tb_karyawan WHERE id_karyawan = '$id'");
    $r = $q->fetch_assoc();

    if (!$r) {
        echo json_encode(["status" => "error", "msg" => "Data tidak ditemukan"]);
        exit;
    }

    ob_start();
?>

    <!-- FORM EDIT (AUTO GENERATE) -->
    <div class="row">
        <div class="col-12 mb-2">
            <label>Nama Lengkap</label>
            <input type="text" class="form-control" name="nama_lengkap" value="<?= htmlspecialchars($r['nama_lengkap']) ?>">
        </div>

        <div class="col-12 mb-2">
            <label>NIK Karyawan</label>
            <input type="text" class="form-control" name="nik_karyawan" value="<?= htmlspecialchars($r['nik_karyawan']) ?>">
        </div>

        <div class="col-12 mb-2">
            <label>Jenis Kelamin</label>
            <select class="form-control" name="jenis_kelamin">
                <option value="L" <?= $r['jenis_kelamin'] == "L" ? "selected" : "" ?>>Laki-laki</option>
                <option value="P" <?= $r['jenis_kelamin'] == "P" ? "selected" : "" ?>>Perempuan</option>
            </select>
        </div>

        <div class="col-12 mb-2">
            <label>Agama</label>
            <input type="text" class="form-control" name="agama" value="<?= htmlspecialchars($r['agama']) ?>">
        </div>

        <div class="col-6 mb-2">
            <label>Tempat Lahir</label>
            <input type="text" class="form-control" name="tempat_lahir" value="<?= htmlspecialchars($r['tempat_lahir']) ?>">
        </div>

        <div class="col-6 mb-2">
            <label>Tanggal Lahir</label>
            <input type="date" class="form-control" name="tgl_lahir" value="<?= htmlspecialchars($r['tgl_lahir']) ?>">
        </div>

        <div class="col-12 mb-2">
            <label>No Telp</label>
            <input type="text" class="form-control" name="no_telp" value="<?= htmlspecialchars($r['no_telp']) ?>">
        </div>
    </div>

<?php
    $html = ob_get_clean();

    echo json_encode([
        "status" => "success",
        "form" => $html
    ]);
    exit;
}

/* ============================================================
   3. UPDATE KARYAWAN
   ============================================================ */
if ($action == "edit") {

    $id = intval($_POST['id_karyawan']);

    $nama = $koneksi->real_escape_string($_POST['nama_lengkap']);
    $nik = $koneksi->real_escape_string($_POST['nik_karyawan']);
    $jk = $koneksi->real_escape_string($_POST['jenis_kelamin']);
    $agama = $koneksi->real_escape_string($_POST['agama']);
    $tgl = $koneksi->real_escape_string($_POST['tgl_lahir']);
    $tmp = $koneksi->real_escape_string($_POST['tempat_lahir']);
    $telp = $koneksi->real_escape_string($_POST['no_telp']);

    $q = $koneksi->query("
        UPDATE tb_karyawan SET
        nama_lengkap='$nama',
        nik_karyawan='$nik',
        jenis_kelamin='$jk',
        agama='$agama',
        tgl_lahir='$tgl',
        tempat_lahir='$tmp',
        no_telp='$telp'
        WHERE id_karyawan='$id'
    ");

    if ($q) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "msg" => $koneksi->error]);
    }
    exit;
}

/* ============================================================
   4. DELETE KARYAWAN
   ============================================================ */
if ($action == "delete") {

    $id = intval($_POST['id_karyawan']);

    $q = $koneksi->query("DELETE FROM tb_karyawan WHERE id_karyawan = '$id'");

    if ($q) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "msg" => $koneksi->error]);
    }

    exit;
}

echo json_encode(["status" => "invalid"]);
