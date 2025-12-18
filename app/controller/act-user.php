<?php
// Tambah user
require_once __DIR__ . '/../config/koneksi.php';
header('Content-Type: application/json; charset=utf-8');

$action = $_POST['action'] ?? '';
$alamat_foto = '../../assets/img/user-pic/';

function json_resp($status, $msg = '') {
    echo json_encode(['status' => $status ? 'success' : 'error', 'msg' => $msg]);
    exit;
}

if ($action === 'add') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $nama = trim($_POST['nama_user'] ?? '');
    $jabatan = trim($_POST['jabatan'] ?? '');
    $site = $_POST['site'] ?? '';
    $level = $_POST['level'] ?? 'user';

    if (empty($username) || empty($password) || empty($nama)) {
        json_resp(false, 'Lengkapi field wajib');
    }

    // handle foto
    $foto_name = '';
    if (!empty($_FILES['foto']['tmp_name'])) {
        $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $foto_name = time().'_'.bin2hex(random_bytes(6)).'.'.($ext ?: 'jpg');
        move_uploaded_file($_FILES['foto']['tmp_name'], $alamat_foto.$foto_name);
    }

    $pass_hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $koneksi->prepare("INSERT INTO tb_user (username,password,nama_user,jabatan,site,level,foto) VALUES (?,?,?,?,?,?,?)");
    $stmt->bind_param("sssssss", $username, $pass_hash, $nama, $jabatan, $site, $level, $foto_name);
    $ok = $stmt->execute();
    json_resp($ok, $ok ? '' : $koneksi->error);
}

if ($action === 'edit') {
    $id = (int)($_POST['id_user'] ?? 0);
    if (!$id) json_resp(false, 'ID tidak valid');

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $nama = trim($_POST['nama_user'] ?? '');
    $jabatan = trim($_POST['jabatan'] ?? '');
    $site = $_POST['site'] ?? '';
    $level = $_POST['level'] ?? 'user';

    if (empty($username) || empty($nama)) {
        json_resp(false, 'Lengkapi field wajib');
    }

    // ambil foto lama
    $old = $koneksi->query("SELECT foto FROM tb_user WHERE id_user = ".$id)->fetch_assoc();
    $old_foto = $old['foto'] ?? '';

    $set_parts = [];
    $params = [];
    $types = '';

    $set_parts[] = "username = ?";
    $params[] = $username; $types .= 's';

    $set_parts[] = "nama_user = ?";
    $params[] = $nama; $types .= 's';

    $set_parts[] = "jabatan = ?";
    $params[] = $jabatan; $types .= 's';

    $set_parts[] = "site = ?";
    $params[] = $site; $types .= 's';

    $set_parts[] = "level = ?";
    $params[] = $level; $types .= 's';

    if (!empty($password)) {
        $pw = password_hash($password, PASSWORD_DEFAULT);
        $set_parts[] = "password = ?";
        $params[] = $pw; $types .= 's';
    }

    // foto baru
    if (!empty($_FILES['foto']['tmp_name'])) {
        $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $foto_name = time().'_'.bin2hex(random_bytes(6)).'.'.($ext ?: 'jpg');
        if (move_uploaded_file($_FILES['foto']['tmp_name'], $alamat_foto.$foto_name)) {
            // hapus foto lama jika ada
            if ($old_foto && file_exists($alamat_foto.$old_foto)) unlink($alamat_foto.$old_foto);
            $set_parts[] = "foto = ?";
            $params[] = $foto_name; $types .= 's';
        }
    }

    // build sql
    $set_sql = implode(', ', $set_parts);
    $sql = "UPDATE tb_user SET {$set_sql} WHERE id_user = ?";
    $params[] = $id; $types .= 'i';

    $stmt = $koneksi->prepare($sql);
    // bind dynamically
    $bind_names[] = $types;
    for ($i=0;$i<count($params);$i++){
        $bind_names[] = &$params[$i];
    }
    call_user_func_array([$stmt, 'bind_param'], $bind_names);
    $ok = $stmt->execute();
    json_resp($ok, $ok ? '' : $koneksi->error);
}

if ($action === 'delete') {
    $id = (int)($_POST['id_user'] ?? 0);
    if (!$id) json_resp(false, 'ID tidak valid');

    // hapus file foto dulu
    $old = $koneksi->query("SELECT foto FROM tb_user WHERE id_user = ".$id)->fetch_assoc();
    if ($old && !empty($old['foto']) && file_exists($alamat_foto.$old['foto'])) {
        @unlink($alamat_foto.$old['foto']);
    }

    $stmt = $koneksi->prepare("DELETE FROM tb_user WHERE id_user = ?");
    $stmt->bind_param("i", $id);
    $ok = $stmt->execute();
    json_resp($ok, $ok ? '' : $koneksi->error);
}

// default
json_resp(false, 'Action tidak dikenali');
