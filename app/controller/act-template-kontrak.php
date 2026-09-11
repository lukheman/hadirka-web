<?php
// app/controller/act-template-kontrak.php
// CRUD template file kontrak (tb_template_kontrak + pivot tb_template_kontrak_jabatan).
// Satu template bisa berlaku untuk BANYAK jabatan.
//
// Actions (POST, response {status, msg|message, ...}):
// - list          : daftar template + jabatan yang ditautkan
// - list_jabatan  : daftar jabatan untuk checkbox modal (GET/POST)
// - upload        : file_pdf (PDF, maks 2MB) + id_jabatan[] (min 1)
// - delete        : id_template (hapus pivot + baris + file fisik)

header("Content-Type: application/json; charset=utf-8");
require_once __DIR__ . '/../config/koneksi.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$upload_dir = __DIR__ . '/../uploads/kontrak/';

function tpl_json($status, $msg, array $extra = [])
{
    echo json_encode(array_merge(['status' => $status, 'msg' => $msg], $extra));
    exit;
}

// --- Daftar jabatan (untuk checkbox) ---
if ($action === 'list_jabatan') {
    $rows = [];
    $q = $koneksi->query("SELECT id_jabatan, nama_jabatan FROM tb_jabatan ORDER BY nama_jabatan ASC");
    if (!$q) {
        tpl_json('error', 'Gagal mengambil data jabatan: ' . $koneksi->error);
    }
    while ($r = $q->fetch_assoc()) $rows[] = $r;
    echo json_encode(['status' => 'success', 'data' => $rows]);
    exit;
}

// --- Daftar template + jabatan tertaut ---
if ($action === 'list') {
    $data = [];
    $q = $koneksi->query(
        "SELECT id_template, `file`, nama_asli, ukuran, created_at " .
        "FROM tb_template_kontrak ORDER BY id_template DESC"
    );
    if (!$q) tpl_json('error', 'Gagal mengambil data: ' . $koneksi->error);
    while ($r = $q->fetch_assoc()) {
        $id = (int)$r['id_template'];
        $jab = [];
        $qj = $koneksi->query(
            "SELECT j.id_jabatan, j.nama_jabatan " .
            "FROM tb_template_kontrak_jabatan p " .
            "JOIN tb_jabatan j ON j.id_jabatan = p.id_jabatan " .
            "WHERE p.id_template = '$id' ORDER BY j.nama_jabatan ASC"
        );
        if ($qj) {
            while ($jr = $qj->fetch_assoc()) $jab[] = $jr;
        }
        $r['jabatan'] = $jab;
        $r['jml_jabatan'] = count($jab);
        $data[] = $r;
    }
    echo json_encode(['status' => 'success', 'data' => $data]);
    exit;
}

// --- Upload template baru ---
if ($action === 'upload') {
    try {
        if (!isset($_FILES['file_pdf']) || $_FILES['file_pdf']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Pilih file PDF terlebih dahulu.");
        }

        $ids = $_POST['id_jabatan'] ?? [];
        if (!is_array($ids)) $ids = [$ids];
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids), function ($v) {
            return $v > 0;
        })));
        if (empty($ids)) {
            throw new Exception("Pilih minimal satu jabatan.");
        }

        // Validasi jabatan benar-benar ada
        $in = implode(',', $ids);
        $cek = $koneksi->query("SELECT COUNT(*) AS n FROM tb_jabatan WHERE id_jabatan IN ($in)");
        $n = $cek ? (int)$cek->fetch_assoc()['n'] : 0;
        if ($n !== count($ids)) {
            throw new Exception("Ada jabatan yang tidak valid.");
        }

        $file_tmp  = $_FILES['file_pdf']['tmp_name'];
        $file_name = $_FILES['file_pdf']['name'];
        $file_size = (int)$_FILES['file_pdf']['size'];

        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        if (!in_array($file_ext, ['doc', 'docx'], true)) {
            throw new Exception("Format file harus .doc atau .docx (Word).");
        }
        if ($file_size > 2097152) {
            throw new Exception("Ukuran file maksimal 2 MB.");
        }

        if (!is_dir($upload_dir) && !mkdir($upload_dir, 0755, true)) {
            throw new Exception("Gagal menyiapkan folder upload.");
        }

        $new_file = 'template_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $file_ext;
        if (!move_uploaded_file($file_tmp, $upload_dir . $new_file)) {
            throw new Exception("Gagal mengupload file ke server.");
        }

        $koneksi->begin_transaction();
        try {
            $stmt = $koneksi->prepare(
                "INSERT INTO tb_template_kontrak (`file`, nama_asli, ukuran) VALUES (?, ?, ?)"
            );
            if (!$stmt) throw new Exception("Gagal menyiapkan data: " . $koneksi->error);
            $stmt->bind_param("ssi", $new_file, $file_name, $file_size);
            if (!$stmt->execute()) throw new Exception("Gagal menyimpan template: " . $stmt->error);
            $id_template = $stmt->insert_id;
            $stmt->close();

            $stmt2 = $koneksi->prepare(
                "INSERT IGNORE INTO tb_template_kontrak_jabatan (id_template, id_jabatan) VALUES (?, ?)"
            );
            if (!$stmt2) throw new Exception("Gagal menyiapkan relasi: " . $koneksi->error);
            foreach ($ids as $id_jab) {
                $stmt2->bind_param("ii", $id_template, $id_jab);
                if (!$stmt2->execute()) throw new Exception("Gagal menautkan jabatan: " . $stmt2->error);
            }
            $stmt2->close();

            $koneksi->commit();
        } catch (Exception $e) {
            $koneksi->rollback();
            @unlink($upload_dir . $new_file);
            throw $e;
        }

        tpl_json('success', 'Template berhasil diupload untuk ' . count($ids) . ' jabatan.', [
            'id_template' => $id_template,
        ]);
    } catch (Exception $e) {
        tpl_json('error', $e->getMessage());
    }
}

// --- Hapus template ---
if ($action === 'delete') {
    $id = (int)($_POST['id_template'] ?? 0);
    if ($id <= 0) tpl_json('error', 'ID template tidak valid.');

    $q = $koneksi->query("SELECT `file` FROM tb_template_kontrak WHERE id_template = '$id'");
    $row = $q ? $q->fetch_assoc() : null;
    if (!$row) tpl_json('error', 'Data tidak ditemukan.');

    $koneksi->begin_transaction();
    try {
        if (!$koneksi->query("DELETE FROM tb_template_kontrak_jabatan WHERE id_template = '$id'")) {
            throw new Exception("Gagal menghapus relasi: " . $koneksi->error);
        }
        if (!$koneksi->query("DELETE FROM tb_template_kontrak WHERE id_template = '$id'")) {
            throw new Exception("Gagal menghapus template: " . $koneksi->error);
        }
        $koneksi->commit();
    } catch (Exception $e) {
        $koneksi->rollback();
        tpl_json('error', $e->getMessage());
    }

    if (!empty($row['file']) && file_exists($upload_dir . $row['file'])) {
        @unlink($upload_dir . $row['file']);
    }
    tpl_json('success', 'Template berhasil dihapus.');
}

tpl_json('error', 'Action tidak dikenali.');
