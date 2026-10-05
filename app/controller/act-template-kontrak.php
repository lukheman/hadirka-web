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
//                   + gaji_pokok harian (opsional, default 0)
// - rename        : id_template + nama_kontrak (ubah nama kontrak)
// - update        : id_template + nama_kontrak + id_jabatan[] (min 1)
//                   + file_pdf opsional (pengganti, Word maks 2MB)
// - save_detail   : id_template + gaji_pokok harian (upsert)
// (Aksi tunjangan/potongan kontrak dihapus bersama tabelnya pada penyederhanaan gaji Okt 2026.)
// - delete        : id_template (hapus pivot + detail (cascade: tunjangan/potongan kontrak ikut terhapus) + baris + file fisik)

header("Content-Type: application/json; charset=utf-8");
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/nomor_surat.php';

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
    $q = $koneksi->prepare("SELECT id_detail FROM tb_detail_kontrak WHERE id_template = ? LIMIT 1");
    $q->execute([$id_template]);
    $row = $q->fetch();
    if ($row) return (int)$row['id_detail'];
    $ins = $koneksi->prepare("INSERT INTO tb_detail_kontrak (id_template, gaji_pokok) VALUES (?, 0)");
    $ins->execute([$id_template]);
    return (int)$koneksi->lastInsertId();
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';
// Penyimpanan dipisah: template master di template-kontrak/, hasil kontrak per karyawan di kontrak/.
// $legacy_upload_dir hanya fallback baca/hapus file lama yang belum dipindah.
$upload_dir = __DIR__ . '/../uploads/template-kontrak/';
$legacy_upload_dir = __DIR__ . '/../uploads/kontrak/';

function tpl_template_path($file)
{
    global $upload_dir, $legacy_upload_dir;
    $base = basename((string)$file);
    if ($base === '') return $upload_dir;
    if (is_file($upload_dir . $base)) return $upload_dir . $base;
    return (is_file($legacy_upload_dir . $base) ? $legacy_upload_dir . $base : $upload_dir . $base);
}

function tpl_json($status, $msg, array $extra = [])
{
    echo json_encode(array_merge(['status' => $status, 'msg' => $msg], $extra));
    exit;
}

// --- Daftar jabatan (untuk checkbox) ---
if ($action === 'list_jabatan') {
    $rows = [];
    try {
        $q = $koneksi->query("SELECT id_jabatan, nama_jabatan, kode_jabatan FROM tb_jabatan ORDER BY nama_jabatan ASC");
        while ($r = $q->fetch()) $rows[] = $r;
    } catch (PDOException $e) {
        error_log($e->getMessage());
        tpl_json('error', 'Gagal mengambil data jabatan.');
    }
    echo json_encode(['status' => 'success', 'data' => $rows]);
    exit;
}

// --- Daftar template + jabatan tertaut ---
if ($action === 'list') {
    $data = [];
    try {
        $q = $koneksi->query(
            "SELECT id_template, nama_kontrak, `file`, nama_asli, ukuran, created_at " .
            "FROM tb_template_kontrak ORDER BY id_template DESC"
        );
        while ($r = $q->fetch()) {
            $id = (int)$r['id_template'];
            $jab = [];
            $qj = $koneksi->prepare(
                "SELECT j.id_jabatan, j.nama_jabatan, j.kode_jabatan " .
                "FROM tb_template_kontrak_jabatan p " .
                "JOIN tb_jabatan j ON j.id_jabatan = p.id_jabatan " .
                "WHERE p.id_template = ? ORDER BY j.nama_jabatan ASC"
            );
            $qj->execute([$id]);
            while ($jr = $qj->fetch()) $jab[] = $jr;
            $r['jabatan'] = $jab;
            $r['jml_jabatan'] = count($jab);
            $qd = $koneksi->prepare(
                "SELECT id_detail, gaji_pokok " .
                "FROM tb_detail_kontrak WHERE id_template = ? LIMIT 1"
            );
            $qd->execute([$id]);
            $detail = $qd->fetch();
            $r['detail'] = $detail === false ? null : $detail;
            $r['jml_tunjangan'] = 0;
            $r['jml_potongan'] = 0;
            $data[] = $r;
        }
    } catch (PDOException $e) {
        error_log($e->getMessage());
        tpl_json('error', 'Gagal mengambil data.');
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
        $n = $cek ? (int)$cek->fetch()['n'] : 0;
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

        $koneksi->beginTransaction();
        try {
            $stmt = $koneksi->prepare(
                "INSERT INTO tb_template_kontrak (nama_kontrak, `file`, nama_asli, ukuran) VALUES (?, ?, ?, ?)"
            );
            $stmt->execute([$nama_kontrak, $new_file, $file_name, $file_size]);
            $id_template = (int)$koneksi->lastInsertId();

            $stmt2 = $koneksi->prepare(
                "INSERT IGNORE INTO tb_template_kontrak_jabatan (id_template, id_jabatan) VALUES (?, ?)"
            );
            foreach ($ids as $id_jab) {
                $stmt2->execute([$id_template, $id_jab]);
            }

            $stmt3 = $koneksi->prepare(
                "INSERT INTO tb_detail_kontrak (id_template, gaji_pokok) VALUES (?, ?)"
            );
            $stmt3->execute([$id_template, $gaji_pokok]);
            $n_tunjangan = 0;
            $n_potongan = 0;

            $koneksi->commit();
        } catch (PDOException $e) {
            if ($koneksi->inTransaction()) $koneksi->rollBack();
            @unlink($upload_dir . $new_file);
            error_log($e->getMessage());
            throw new Exception("Gagal menyimpan template.");
        } catch (Exception $e) {
            if ($koneksi->inTransaction()) $koneksi->rollBack();
            @unlink($upload_dir . $new_file);
            throw $e;
        }

        tpl_json('success', 'Template berhasil diupload untuk ' . count($ids) . ' jabatan.', [
            'id_template' => $id_template,
            'tunjangan' => $n_tunjangan,
            'potongan' => $n_potongan,
        ]);
    } catch (PDOException $e) {
        error_log($e->getMessage());
        tpl_json('error', 'Gagal menyimpan template.');
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

    try {
        $stmt = $koneksi->prepare("UPDATE tb_template_kontrak SET nama_kontrak = ? WHERE id_template = ?");
        $stmt->execute([$nama_kontrak, $id]);
        if ($stmt->rowCount() === 0) {
            $cek = $koneksi->prepare("SELECT id_template FROM tb_template_kontrak WHERE id_template = ?");
            $cek->execute([$id]);
            if (!$cek->fetch()) tpl_json('error', 'Data tidak ditemukan.');
        }
    } catch (PDOException $e) {
        error_log($e->getMessage());
        tpl_json('error', 'Gagal menyimpan data.');
    }
    tpl_json('success', 'Nama kontrak berhasil disimpan.');
}

// --- Ubah template (nama, jabatan tertaut, opsional ganti file) ---
if ($action === 'update') {
    try {
        $id_template = (int)($_POST['id_template'] ?? 0);
        if ($id_template <= 0) throw new Exception("ID template tidak valid.");

        $q = $koneksi->prepare("SELECT `file` FROM tb_template_kontrak WHERE id_template = ? LIMIT 1");
        $q->execute([$id_template]);
        $lama = $q->fetch();
        if (!$lama) throw new Exception("Template tidak ditemukan.");

        $nama_kontrak = trim($_POST['nama_kontrak'] ?? '');
        if ($nama_kontrak === '') throw new Exception("Nama kontrak wajib diisi.");
        if (strlen($nama_kontrak) > 150) throw new Exception("Nama kontrak maksimal 150 karakter.");

        $ids = $_POST['id_jabatan'] ?? [];
        if (!is_array($ids)) $ids = [$ids];
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids), function ($v) {
            return $v > 0;
        })));
        if (empty($ids)) throw new Exception("Pilih minimal satu jabatan.");

        $in = implode(',', $ids);
        $cek = $koneksi->query("SELECT COUNT(*) AS n FROM tb_jabatan WHERE id_jabatan IN ($in)");
        $n = $cek ? (int)$cek->fetch()['n'] : 0;
        if ($n !== count($ids)) throw new Exception("Ada jabatan yang tidak valid.");

        // File pengganti (opsional, kosongkan bila tidak diganti).
        $new_file = null;
        $file_name = null;
        $file_size = null;
        if (isset($_FILES['file_pdf']) && $_FILES['file_pdf']['error'] !== UPLOAD_ERR_NO_FILE) {
            if ($_FILES['file_pdf']['error'] !== UPLOAD_ERR_OK) throw new Exception("Gagal membaca file pengganti.");
            $file_tmp  = $_FILES['file_pdf']['tmp_name'];
            $file_name = $_FILES['file_pdf']['name'];
            $file_size = (int)$_FILES['file_pdf']['size'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            if (!in_array($file_ext, ['doc', 'docx'], true)) throw new Exception("Format file harus .doc atau .docx (Word).");
            if ($file_size > 2097152) throw new Exception("Ukuran file maksimal 2 MB.");
            if (!is_dir($upload_dir) && !mkdir($upload_dir, 0755, true)) throw new Exception("Gagal menyiapkan folder upload.");
            $new_file = 'template_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $file_ext;
            if (!move_uploaded_file($file_tmp, $upload_dir . $new_file)) throw new Exception("Gagal mengupload file ke server.");
        }

        $koneksi->beginTransaction();
        try {
            if ($new_file !== null) {
                $stmt = $koneksi->prepare(
                    "UPDATE tb_template_kontrak SET nama_kontrak = ?, `file` = ?, nama_asli = ?, ukuran = ? WHERE id_template = ?"
                );
                $stmt->execute([$nama_kontrak, $new_file, $file_name, $file_size, $id_template]);
            } else {
                $stmt = $koneksi->prepare("UPDATE tb_template_kontrak SET nama_kontrak = ? WHERE id_template = ?");
                $stmt->execute([$nama_kontrak, $id_template]);
            }

            $delRel = $koneksi->prepare("DELETE FROM tb_template_kontrak_jabatan WHERE id_template = ?");
            $delRel->execute([$id_template]);
            $stmt2 = $koneksi->prepare(
                "INSERT INTO tb_template_kontrak_jabatan (id_template, id_jabatan) VALUES (?, ?)"
            );
            foreach ($ids as $id_jab) {
                $stmt2->execute([$id_template, $id_jab]);
            }
            $koneksi->commit();
        } catch (PDOException $e) {
            if ($koneksi->inTransaction()) $koneksi->rollBack();
            if ($new_file !== null) @unlink($upload_dir . $new_file);
            error_log($e->getMessage());
            throw new Exception("Gagal memperbarui template.");
        } catch (Exception $e) {
            if ($koneksi->inTransaction()) $koneksi->rollBack();
            if ($new_file !== null) @unlink($upload_dir . $new_file);
            throw $e;
        }

        if ($new_file !== null && !empty($lama['file'])) {
            $old_base = basename((string)$lama['file']);
            if ($old_base !== '' && file_exists($upload_dir . $old_base)) @unlink($upload_dir . $old_base);
            if ($old_base !== '' && file_exists($legacy_upload_dir . $old_base)) @unlink($legacy_upload_dir . $old_base);
        }
        tpl_json('success', 'Template berhasil diperbarui.');
    } catch (PDOException $e) {
        error_log($e->getMessage());
        tpl_json('error', 'Gagal memperbarui template.');
    } catch (Exception $e) {
        tpl_json('error', $e->getMessage());
    }
}

// --- Simpan/ubah nilai kontrak (upsert) ---
if ($action === 'save_detail') {
    $id_template = (int)($_POST['id_template'] ?? 0);
    if ($id_template <= 0) tpl_json('error', 'ID template tidak valid.');

    try {
        $cek = $koneksi->prepare("SELECT id_template FROM tb_template_kontrak WHERE id_template = ?");
        $cek->execute([$id_template]);
        if (!$cek->fetch()) tpl_json('error', 'Template tidak ditemukan.');

        $gaji_pokok       = tpl_float($_POST['gaji_pokok'] ?? 0);

        $stmt = $koneksi->prepare(
            "INSERT INTO tb_detail_kontrak (id_template, gaji_pokok) " .
            "VALUES (?, ?) ON DUPLICATE KEY UPDATE " .
            "gaji_pokok = VALUES(gaji_pokok)"
        );
        $stmt->execute([$id_template, $gaji_pokok]);

        tpl_json('success', 'Nilai kontrak berhasil disimpan.');
    } catch (PDOException $e) {
        error_log($e->getMessage());
        tpl_json('error', 'Gagal menyimpan detail.');
    }
}

// Aksi tunjangan/potongan kontrak dihapus bersama tabelnya
// (penyederhanaan gaji Okt 2026). Panggilan lama ditolak tegas.
if (in_array($action, ['list_tunjangan','save_tunjangan','delete_tunjangan',
    'list_potongan','save_potongan','delete_potongan'], true)) {
    tpl_json('error', 'Fitur tunjangan/potongan sudah dihapus dari sistem.');
}

// --- Info untuk preview surat kontrak sebelum karier disimpan ---
// Mengembalikan kode jabatan, estimasi no_surat (counter+1, nomor final terbit saat simpan),
// dan file template kontrak yang berlaku untuk jabatan tersebut.
if ($action === 'preview_info') {
    $id_jabatan = (int)($_POST['id_jabatan'] ?? $_GET['id_jabatan'] ?? 0);
    $tgl_mulai = (string)($_POST['tgl_mulai'] ?? $_GET['tgl_mulai'] ?? '');
    if ($id_jabatan <= 0) tpl_json('error', 'Jabatan belum dipilih.');

    try {
        $qj = $koneksi->prepare("SELECT kode_jabatan, nama_jabatan FROM tb_jabatan WHERE id_jabatan = ? LIMIT 1");
        $qj->execute([$id_jabatan]);
        $jab = $qj->fetch();
        if (!$jab) tpl_json('error', 'Jabatan tidak ditemukan.');

        $qt = $koneksi->prepare(
            "SELECT t.id_template, t.nama_kontrak, t.`file`, t.nama_asli FROM tb_template_kontrak_jabatan p " .
            "JOIN tb_template_kontrak t ON t.id_template = p.id_template " .
            "WHERE p.id_jabatan = ? ORDER BY t.id_template DESC"
        );
        $qt->execute([$id_jabatan]);
        // Semua template yang berlaku untuk jabatan (+ detail nilai masing-masing)
        // agar user bisa memilih bila jumlahnya lebih dari satu.
        $templates = [];
        while ($tr = $qt->fetch()) {
            $id_tpl = (int)$tr['id_template'];
            $qd = $koneksi->prepare(
                "SELECT id_detail, gaji_pokok " .
                "FROM tb_detail_kontrak WHERE id_template = ? LIMIT 1"
            );
            $qd->execute([$id_tpl]);
            $detailRow = $qd->fetch();
            $tr['tunjangan'] = [];
            $tr['potongan'] = [];
            if ($detailRow && !empty($detailRow['id_detail'])) {
                unset($detailRow['id_detail']);
            }
            $tr['detail'] = $detailRow === false ? null : $detailRow;
            $templates[] = $tr;
        }
    } catch (PDOException $e) {
        error_log($e->getMessage());
        tpl_json('error', 'Gagal mengambil data.');
    }
    $tpl = $templates[0] ?? null;

    // Nilai kontrak dari detail template terbaru (untuk prefill form tambah karier).
    $detail = $tpl['detail'] ?? null;

    $tahun = preg_match('/^(\d{4})-\d{2}-\d{2}$/', $tgl_mulai, $m) ? $m[1] : date('Y');
    // Estimasi TANPA mengonsumsi counter: lanjut dari yang terbesar (counter vs data) + 1.
    $next = peek_no_urut($koneksi);

    $kode = trim((string)($jab['kode_jabatan'] ?? ''));
    tpl_json('success', 'OK', [
        'kode_jabatan' => $kode,
        'nama_jabatan' => $jab['nama_jabatan'] ?? '',
        'tahun' => $tahun,
        'no_surat_preview' => $kode !== '' ? sprintf('TPJS-KONTRAK-%s/%04d/%s', $kode, $next, $tahun) : null,
        'template' => $tpl,
        'templates' => $templates,
        'detail' => $detail,
    ]);
}

// --- Hapus template ---
if ($action === 'delete') {
    $id = (int)($_POST['id_template'] ?? 0);
    if ($id <= 0) tpl_json('error', 'ID template tidak valid.');

    $q = $koneksi->prepare("SELECT `file` FROM tb_template_kontrak WHERE id_template = ?");
    $q->execute([$id]);
    $row = $q->fetch();
    if (!$row) tpl_json('error', 'Data tidak ditemukan.');

    $koneksi->beginTransaction();
    try {
        $delRel = $koneksi->prepare("DELETE FROM tb_template_kontrak_jabatan WHERE id_template = ?");
        $delRel->execute([$id]);
        $delTpl = $koneksi->prepare("DELETE FROM tb_template_kontrak WHERE id_template = ?");
        $delTpl->execute([$id]);
        $koneksi->commit();
    } catch (PDOException $e) {
        if ($koneksi->inTransaction()) $koneksi->rollBack();
        error_log($e->getMessage());
        tpl_json('error', 'Gagal menghapus template.');
    } catch (Exception $e) {
        if ($koneksi->inTransaction()) $koneksi->rollBack();
        tpl_json('error', $e->getMessage());
    }

    if (!empty($row['file'])) {
        $del_base = basename((string)$row['file']);
        if ($del_base !== '' && file_exists($upload_dir . $del_base)) @unlink($upload_dir . $del_base);
        if ($del_base !== '' && file_exists($legacy_upload_dir . $del_base)) @unlink($legacy_upload_dir . $del_base);
    }
    tpl_json('success', 'Template berhasil dihapus.');
}

tpl_json('error', 'Action tidak dikenali.');
