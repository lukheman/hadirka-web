<?php
// TAHAN SEMUA OUTPUT HTML/ERROR PHP AGAR TIDAK MERUSAK JSON
ob_start();

require_once __DIR__ . '/../config/koneksi.php';
header('Content-Type: application/json; charset=utf-8');

$action = $_POST['action'] ?? '';
$alamat_foto = '../../assets/img/user-pic/';
if (!is_dir($alamat_foto)) {
    mkdir($alamat_foto, 0755, true);
}

function json_resp($status, $msg = '') {
    $php_error = ob_get_clean(); // Tangkap error PHP yang bocor

    // Jika gagal dan ada pesan error dari PHP, masukkan ke dalam pesan alert
    if (!$status && $php_error && empty($msg)) {
        $msg = "Error PHP: " . strip_tags(substr($php_error, 0, 150));
    }

    echo json_encode(['status' => $status ? 'success' : 'error', 'msg' => $msg]);
    exit;
}

if ($action === 'add') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $nama = trim($_POST['nama_user'] ?? '');
    $jabatan = trim($_POST['jabatan'] ?? '');
    $site = $_POST['site'] ?? '';
    $level = isset($_POST['level']) ? (int)$_POST['level'] : 5;

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

    try {
        $stmt = $koneksi->prepare("INSERT INTO tb_user (username,password,nama_user,jabatan,site,level,foto) VALUES (?,?,?,?,?,?,?)");
        $ok = $stmt->execute([$username, $pass_hash, $nama, $jabatan, $site, $level, $foto_name]);
        json_resp($ok, $ok ? '' : 'Gagal menambah user.');
    } catch (PDOException $e) {
        error_log($e->getMessage());
        json_resp(false, 'Gagal menambah user.');
    }
}

if ($action === 'edit') {
    $id = (int)($_POST['id_user'] ?? 0);
    if (!$id) json_resp(false, 'ID tidak valid');

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $nama = trim($_POST['nama_user'] ?? '');
    $jabatan = trim($_POST['jabatan'] ?? '');
    $site = $_POST['site'] ?? '';
    $level = isset($_POST['level']) ? (int)$_POST['level'] : 5;

    if (empty($username) || empty($nama)) {
        json_resp(false, 'Lengkapi field wajib');
    }

    // ambil foto lama
    $stOld = $koneksi->prepare("SELECT foto FROM tb_user WHERE id_user = ?");
    $stOld->execute([$id]);
    $old = $stOld->fetch();
    $old_foto = $old['foto'] ?? '';

    $set_parts = [];
    $params = [];

    $set_parts[] = "username = ?"; $params[] = $username;
    $set_parts[] = "nama_user = ?"; $params[] = $nama;
    $set_parts[] = "jabatan = ?"; $params[] = $jabatan;
    $set_parts[] = "site = ?"; $params[] = $site;
    $set_parts[] = "level = ?"; $params[] = $level;

    if (!empty($password)) {
        $pw = password_hash($password, PASSWORD_DEFAULT);
        $set_parts[] = "password = ?";
        $params[] = $pw;
    }

    // foto baru
    if (!empty($_FILES['foto']['tmp_name'])) {
        $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $foto_name = time().'_'.bin2hex(random_bytes(6)).'.'.($ext ?: 'jpg');
        if (move_uploaded_file($_FILES['foto']['tmp_name'], $alamat_foto.$foto_name)) {
            if ($old_foto && file_exists($alamat_foto.$old_foto)) @unlink($alamat_foto.$old_foto);
            $set_parts[] = "foto = ?";
            $params[] = $foto_name;
        }
    }

    $set_sql = implode(', ', $set_parts);
    $sql = "UPDATE tb_user SET {$set_sql} WHERE id_user = ?";
    $params[] = $id;

    try {
        $stmt = $koneksi->prepare($sql);
        $ok = $stmt->execute($params);
        json_resp($ok, $ok ? '' : 'Gagal memperbarui user.');
    } catch (PDOException $e) {
        error_log($e->getMessage());
        json_resp(false, 'Gagal memperbarui user.');
    }
}

if ($action === 'delete') {
    $id = (int)($_POST['id_user'] ?? 0);
    if (!$id) json_resp(false, 'ID tidak valid');

    $stOld = $koneksi->prepare("SELECT foto FROM tb_user WHERE id_user = ?");
    $stOld->execute([$id]);
    $old = $stOld->fetch();
    if ($old && !empty($old['foto']) && file_exists($alamat_foto.$old['foto'])) {
        @unlink($alamat_foto.$old['foto']);
    }

    try {
        $stmt = $koneksi->prepare("DELETE FROM tb_user WHERE id_user = ?");
        $ok = $stmt->execute([$id]);
        json_resp($ok, $ok ? '' : 'Gagal menghapus user.');
    } catch (PDOException $e) {
        error_log($e->getMessage());
        json_resp(false, 'Gagal menghapus user.');
    }
}

json_resp(false, 'Action tidak dikenali');
