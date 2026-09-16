<?php
// app/controller/act-template-kontrak.php
// CRUD template file kontrak (tb_template_kontrak + pivot tb_template_kontrak_jabatan).
// Satu template bisa berlaku untuk BANYAK jabatan.
//
// Actions (POST, response {status, msg|message, ...}):
// - list          : daftar template + jabatan yang ditautkan + detail nilai kontrak
// - list_jabatan  : daftar jabatan untuk checkbox modal (GET/POST)
// - upload        : nama_kontrak (wajib, maks 150) + file_pdf (Word, maks 2MB)
//                   + id_jabatan[] (min 1)
//                   + gaji_pokok, upah_lembur, potongan_izin, potongan_mangkir (opsional, default 0)
// - rename        : id_template + nama_kontrak (ubah nama kontrak)
// - save_detail   : id_template + gaji_pokok, upah_lembur, potongan_izin, potongan_mangkir (upsert)
// - list_tunjangan / save_tunjangan / delete_tunjangan   : tunjangan milik detail kontrak (tb_tunjangan_kontrak)
// - list_potongan / save_potongan / delete_potongan       : potongan milik detail kontrak (tb_potongan_kontrak)
// - delete        : id_template (hapus pivot + detail (cascade: tunjangan/potongan kontrak ikut terhapus) + baris + file fisik)

header("Content-Type: application/json; charset=utf-8");
require_once __DIR__ . '/../config/koneksi.php';

// Nominal "1.500.000,50" -> float 1500000.50 (samakan dengan act-karier.php)
function tpl_float($val)
{
    if (!isset($val) || $val === '') return 0;
    $val = str_replace('.', '', (string)$val);
    $val = str_replace(',', '.', $val);
    return is_numeric($val) ? floatval($val) : 0;
}

// Pastikan baris detail ada untuk template (template lama belum punya), kembalikan id_detail.
function tpl_ensure_detail($koneksi, $id_template)
{
    $id_template = (int)$id_template;
    $q = $koneksi->query("SELECT id_detail FROM tb_detail_kontrak WHERE id_template = '$id_template' LIMIT 1");
    if ($q && $q->num_rows > 0) return (int)$q->fetch_assoc()['id_detail'];
    $koneksi->query(
        "INSERT INTO tb_detail_kontrak (id_template, gaji_pokok, upah_lembur, potongan_izin, potongan_mangkir) " .
        "VALUES ('$id_template', 0, 0, 0, 0)"
    );
    return (int)$koneksi->insert_id;
}

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
        "SELECT id_template, nama_kontrak, `file`, nama_asli, ukuran, created_at " .
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
        $qd = $koneksi->query(
            "SELECT id_detail, gaji_pokok, upah_lembur, potongan_izin, potongan_mangkir " .
            "FROM tb_detail_kontrak WHERE id_template = '$id' LIMIT 1"
        );
        $detail = ($qd && $qd->num_rows > 0) ? $qd->fetch_assoc() : null;
        $r['detail'] = $detail;
        if ($detail) {
            $id_detail = (int)$detail['id_detail'];
            $qt = $koneksi->query("SELECT COUNT(*) AS n FROM tb_tunjangan_kontrak WHERE id_detail_kontrak = '$id_detail'");
            $qp = $koneksi->query("SELECT COUNT(*) AS n FROM tb_potongan_kontrak WHERE id_detail_kontrak = '$id_detail'");
            $r['jml_tunjangan'] = $qt ? (int)$qt->fetch_assoc()['n'] : 0;
            $r['jml_potongan'] = $qp ? (int)$qp->fetch_assoc()['n'] : 0;
        } else {
            $r['jml_tunjangan'] = 0;
            $r['jml_potongan'] = 0;
        }
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

        $nama_kontrak = trim($_POST['nama_kontrak'] ?? '');
        if ($nama_kontrak === '') {
            throw new Exception("Nama kontrak wajib diisi.");
        }
        if (strlen($nama_kontrak) > 150) {
            throw new Exception("Nama kontrak maksimal 150 karakter.");
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

        $gaji_pokok      = tpl_float($_POST['gaji_pokok'] ?? 0);
        $upah_lembur     = tpl_float($_POST['upah_lembur'] ?? 0);
        $potongan_izin   = tpl_float($_POST['potongan_izin'] ?? 0);
        $potongan_mangkir = tpl_float($_POST['potongan_mangkir'] ?? 0);

        $koneksi->begin_transaction();
        try {
            $stmt = $koneksi->prepare(
                "INSERT INTO tb_template_kontrak (nama_kontrak, `file`, nama_asli, ukuran) VALUES (?, ?, ?, ?)"
            );
            if (!$stmt) throw new Exception("Gagal menyiapkan data: " . $koneksi->error);
            $stmt->bind_param("sssi", $nama_kontrak, $new_file, $file_name, $file_size);
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

            $stmt3 = $koneksi->prepare(
                "INSERT INTO tb_detail_kontrak (id_template, gaji_pokok, upah_lembur, potongan_izin, potongan_mangkir) VALUES (?, ?, ?, ?, ?)"
            );
            if (!$stmt3) throw new Exception("Gagal menyiapkan detail: " . $koneksi->error);
            $stmt3->bind_param("idddd", $id_template, $gaji_pokok, $upah_lembur, $potongan_izin, $potongan_mangkir);
            if (!$stmt3->execute()) throw new Exception("Gagal menyimpan detail kontrak: " . $stmt3->error);
            $stmt3->close();

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

// --- Ubah nama kontrak ---
if ($action === 'rename') {
    $id = (int)($_POST['id_template'] ?? 0);
    if ($id <= 0) tpl_json('error', 'ID template tidak valid.');
    $nama_kontrak = trim($_POST['nama_kontrak'] ?? '');
    if ($nama_kontrak === '') tpl_json('error', 'Nama kontrak wajib diisi.');
    if (strlen($nama_kontrak) > 150) tpl_json('error', 'Nama kontrak maksimal 150 karakter.');

    $stmt = $koneksi->prepare("UPDATE tb_template_kontrak SET nama_kontrak = ? WHERE id_template = ?");
    if (!$stmt) tpl_json('error', 'Gagal menyiapkan data: ' . $koneksi->error);
    $stmt->bind_param("si", $nama_kontrak, $id);
    if (!$stmt->execute()) tpl_json('error', 'Gagal menyimpan: ' . $stmt->error);
    if ($stmt->affected_rows === 0) {
        $cek = $koneksi->query("SELECT id_template FROM tb_template_kontrak WHERE id_template = '$id'");
        if (!$cek || $cek->num_rows === 0) tpl_json('error', 'Data tidak ditemukan.');
    }
    $stmt->close();
    tpl_json('success', 'Nama kontrak berhasil disimpan.');
}

// --- Simpan/ubah nilai kontrak (upsert) ---
if ($action === 'save_detail') {
    $id_template = (int)($_POST['id_template'] ?? 0);
    if ($id_template <= 0) tpl_json('error', 'ID template tidak valid.');

    $cek = $koneksi->query("SELECT id_template FROM tb_template_kontrak WHERE id_template = '$id_template'");
    if (!$cek || $cek->num_rows === 0) tpl_json('error', 'Template tidak ditemukan.');

    $gaji_pokok       = tpl_float($_POST['gaji_pokok'] ?? 0);
    $upah_lembur      = tpl_float($_POST['upah_lembur'] ?? 0);
    $potongan_izin    = tpl_float($_POST['potongan_izin'] ?? 0);
    $potongan_mangkir = tpl_float($_POST['potongan_mangkir'] ?? 0);

    $stmt = $koneksi->prepare(
        "INSERT INTO tb_detail_kontrak (id_template, gaji_pokok, upah_lembur, potongan_izin, potongan_mangkir) " .
        "VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE " .
        "gaji_pokok = VALUES(gaji_pokok), upah_lembur = VALUES(upah_lembur), " .
        "potongan_izin = VALUES(potongan_izin), potongan_mangkir = VALUES(potongan_mangkir)"
    );
    if (!$stmt) tpl_json('error', 'Gagal menyiapkan data: ' . $koneksi->error);
    $stmt->bind_param("idddd", $id_template, $gaji_pokok, $upah_lembur, $potongan_izin, $potongan_mangkir);
    if (!$stmt->execute()) tpl_json('error', 'Gagal menyimpan detail: ' . $stmt->error);
    $stmt->close();

    tpl_json('success', 'Nilai kontrak berhasil disimpan.');
}

// --- Tunjangan milik detail kontrak (tabel khusus tb_tunjangan_kontrak) ---
if ($action === 'list_tunjangan') {
    $id_template = (int)($_POST['id_template'] ?? $_GET['id_template'] ?? 0);
    if ($id_template <= 0) tpl_json('error', 'ID template tidak valid.');
    $id_detail = tpl_ensure_detail($koneksi, $id_template);
    $rows = [];
    $q = $koneksi->query(
        "SELECT id_tunjangan_kontrak AS id_tunjangan, nama_tunjangan, jumlah_tunjangan " .
        "FROM tb_tunjangan_kontrak WHERE id_detail_kontrak = '$id_detail' ORDER BY id_tunjangan_kontrak ASC"
    );
    if (!$q) tpl_json('error', 'Gagal mengambil data: ' . $koneksi->error);
    while ($r = $q->fetch_assoc()) $rows[] = $r;
    echo json_encode(['status' => 'success', 'data' => $rows, 'id_detail' => $id_detail]);
    exit;
}

if ($action === 'save_tunjangan') {
    $id_template = (int)($_POST['id_template'] ?? 0);
    if ($id_template <= 0) tpl_json('error', 'ID template tidak valid.');
    $nama_raw = trim($_POST['nama_tunjangan'] ?? '');
    if ($nama_raw === '') tpl_json('error', 'Nama tunjangan wajib diisi.');
    if (strlen($nama_raw) > 25) tpl_json('error', 'Nama tunjangan maksimal 25 karakter.');
    $jumlah_raw = $_POST['jumlah_tunjangan'] ?? '';
    if ($jumlah_raw === '' || $jumlah_raw === null) tpl_json('error', 'Jumlah tunjangan wajib diisi.');
    $jumlah = (string)tpl_float($jumlah_raw);
    $id_detail = tpl_ensure_detail($koneksi, $id_template);
    $nama = $koneksi->real_escape_string($nama_raw);
    $jumlah_esc = $koneksi->real_escape_string($jumlah);

    $id_tunjangan = (int)($_POST['id_tunjangan'] ?? 0);
    if ($id_tunjangan > 0) {
        $ok = $koneksi->query(
            "UPDATE tb_tunjangan_kontrak SET nama_tunjangan = '$nama', jumlah_tunjangan = '$jumlah_esc' " .
            "WHERE id_tunjangan_kontrak = '$id_tunjangan' AND id_detail_kontrak = '$id_detail'"
        );
        if (!$ok) tpl_json('error', 'Gagal mengupdate: ' . $koneksi->error);
        if ($koneksi->affected_rows === 0) tpl_json('error', 'Data tidak ditemukan.');
        tpl_json('success', 'Tunjangan berhasil diupdate.');
    } else {
        $ok = $koneksi->query(
            "INSERT INTO tb_tunjangan_kontrak (id_detail_kontrak, nama_tunjangan, jumlah_tunjangan) " .
            "VALUES ('$id_detail', '$nama', '$jumlah_esc')"
        );
        if (!$ok) tpl_json('error', 'Gagal menyimpan: ' . $koneksi->error);
        tpl_json('success', 'Tunjangan berhasil ditambah.', ['id_tunjangan' => $koneksi->insert_id]);
    }
}

if ($action === 'delete_tunjangan') {
    $id_template = (int)($_POST['id_template'] ?? 0);
    $id_tunjangan = (int)($_POST['id_tunjangan'] ?? 0);
    if ($id_template <= 0 || $id_tunjangan <= 0) tpl_json('error', 'ID tidak valid.');
    $id_detail = tpl_ensure_detail($koneksi, $id_template);
    $ok = $koneksi->query(
        "DELETE FROM tb_tunjangan_kontrak WHERE id_tunjangan_kontrak = '$id_tunjangan' AND id_detail_kontrak = '$id_detail'"
    );
    if (!$ok) tpl_json('error', 'Gagal menghapus: ' . $koneksi->error);
    if ($koneksi->affected_rows === 0) tpl_json('error', 'Data tidak ditemukan.');
    tpl_json('success', 'Tunjangan berhasil dihapus.');
}

// --- Potongan milik detail kontrak (tabel khusus tb_potongan_kontrak) ---
if ($action === 'list_potongan') {
    $id_template = (int)($_POST['id_template'] ?? $_GET['id_template'] ?? 0);
    if ($id_template <= 0) tpl_json('error', 'ID template tidak valid.');
    $id_detail = tpl_ensure_detail($koneksi, $id_template);
    $rows = [];
    $q = $koneksi->query(
        "SELECT id_potongan_kontrak AS id_potongan, jenis_potongan, jumlah_potongan " .
        "FROM tb_potongan_kontrak WHERE id_detail_kontrak = '$id_detail' ORDER BY id_potongan_kontrak ASC"
    );
    if (!$q) tpl_json('error', 'Gagal mengambil data: ' . $koneksi->error);
    while ($r = $q->fetch_assoc()) $rows[] = $r;
    echo json_encode(['status' => 'success', 'data' => $rows, 'id_detail' => $id_detail]);
    exit;
}

if ($action === 'save_potongan') {
    $id_template = (int)($_POST['id_template'] ?? 0);
    if ($id_template <= 0) tpl_json('error', 'ID template tidak valid.');
    $jenis_raw = trim($_POST['jenis_potongan'] ?? '');
    if ($jenis_raw === '') tpl_json('error', 'Nama potongan wajib diisi.');
    if (strlen($jenis_raw) > 25) tpl_json('error', 'Nama potongan maksimal 25 karakter.');
    $jumlah_raw = $_POST['jumlah_potongan'] ?? '';
    if ($jumlah_raw === '' || $jumlah_raw === null) tpl_json('error', 'Jumlah potongan wajib diisi.');
    $jumlah = (string)tpl_float($jumlah_raw);
    $id_detail = tpl_ensure_detail($koneksi, $id_template);
    $jenis = $koneksi->real_escape_string($jenis_raw);
    $jumlah_esc = $koneksi->real_escape_string($jumlah);

    $id_potongan = (int)($_POST['id_potongan'] ?? 0);
    if ($id_potongan > 0) {
        $ok = $koneksi->query(
            "UPDATE tb_potongan_kontrak SET jenis_potongan = '$jenis', jumlah_potongan = '$jumlah_esc' " .
            "WHERE id_potongan_kontrak = '$id_potongan' AND id_detail_kontrak = '$id_detail'"
        );
        if (!$ok) tpl_json('error', 'Gagal mengupdate: ' . $koneksi->error);
        if ($koneksi->affected_rows === 0) tpl_json('error', 'Data tidak ditemukan.');
        tpl_json('success', 'Potongan berhasil diupdate.');
    } else {
        $ok = $koneksi->query(
            "INSERT INTO tb_potongan_kontrak (id_detail_kontrak, jenis_potongan, jumlah_potongan) " .
            "VALUES ('$id_detail', '$jenis', '$jumlah_esc')"
        );
        if (!$ok) tpl_json('error', 'Gagal menyimpan: ' . $koneksi->error);
        tpl_json('success', 'Potongan berhasil ditambah.', ['id_potongan' => $koneksi->insert_id]);
    }
}

if ($action === 'delete_potongan') {
    $id_template = (int)($_POST['id_template'] ?? 0);
    $id_potongan = (int)($_POST['id_potongan'] ?? 0);
    if ($id_template <= 0 || $id_potongan <= 0) tpl_json('error', 'ID tidak valid.');
    $id_detail = tpl_ensure_detail($koneksi, $id_template);
    $ok = $koneksi->query(
        "DELETE FROM tb_potongan_kontrak WHERE id_potongan_kontrak = '$id_potongan' AND id_detail_kontrak = '$id_detail'"
    );
    if (!$ok) tpl_json('error', 'Gagal menghapus: ' . $koneksi->error);
    if ($koneksi->affected_rows === 0) tpl_json('error', 'Data tidak ditemukan.');
    tpl_json('success', 'Potongan berhasil dihapus.');
}

// --- Info untuk preview surat kontrak sebelum karier disimpan ---
// Mengembalikan kode jabatan, estimasi no_surat (counter+1, nomor final terbit saat simpan),
// dan file template kontrak yang berlaku untuk jabatan tersebut.
if ($action === 'preview_info') {
    $id_jabatan = (int)($_POST['id_jabatan'] ?? $_GET['id_jabatan'] ?? 0);
    $tgl_mulai = (string)($_POST['tgl_mulai'] ?? $_GET['tgl_mulai'] ?? '');
    if ($id_jabatan <= 0) tpl_json('error', 'Jabatan belum dipilih.');

    $qj = $koneksi->query("SELECT kode_jabatan, nama_jabatan FROM tb_jabatan WHERE id_jabatan = '$id_jabatan' LIMIT 1");
    if (!$qj || $qj->num_rows === 0) tpl_json('error', 'Jabatan tidak ditemukan.');
    $jab = $qj->fetch_assoc();

    $qt = $koneksi->query(
        "SELECT t.id_template, t.nama_kontrak, t.`file`, t.nama_asli FROM tb_template_kontrak_jabatan p " .
        "JOIN tb_template_kontrak t ON t.id_template = p.id_template " .
        "WHERE p.id_jabatan = '$id_jabatan' ORDER BY t.id_template DESC LIMIT 1"
    );
    $tpl = ($qt && $qt->num_rows > 0) ? $qt->fetch_assoc() : null;

    // Nilai kontrak dari detail template (untuk prefill form tambah karier).
    $detail = null;
    if ($tpl && !empty($tpl['id_template'])) {
        $id_tpl = (int)$tpl['id_template'];
        $qd = $koneksi->query(
            "SELECT gaji_pokok, upah_lembur, potongan_izin, potongan_mangkir " .
            "FROM tb_detail_kontrak WHERE id_template = '$id_tpl' LIMIT 1"
        );
        if ($qd && $qd->num_rows > 0) $detail = $qd->fetch_assoc();
    }

    $tahun = preg_match('/^(\d{4})-\d{2}-\d{2}$/', $tgl_mulai, $m) ? $m[1] : date('Y');
    $qc = $koneksi->query("SELECT terakhir FROM tb_nomor_urut WHERE nama = 'kontrak' LIMIT 1");
    $next = ($qc && $qc->num_rows > 0) ? ((int)$qc->fetch_assoc()['terakhir'] + 1) : 1;

    $kode = trim((string)($jab['kode_jabatan'] ?? ''));
    tpl_json('success', 'OK', [
        'kode_jabatan' => $kode,
        'nama_jabatan' => $jab['nama_jabatan'] ?? '',
        'tahun' => $tahun,
        'no_surat_preview' => $kode !== '' ? sprintf('TPJS-KONTRAK-%s/%04d/%s', $kode, $next, $tahun) : null,
        'template' => $tpl,
        'detail' => $detail,
    ]);
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
