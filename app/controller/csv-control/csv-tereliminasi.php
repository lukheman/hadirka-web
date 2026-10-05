<?php
// Import massal karyawan tereliminasi (eliminasi by NIK).
// Template: No | NIK Karyawan | Nama Lengkap | Tanggal Keluar | Jenis Keluar
// NIK adalah kunci (wajib terdaftar di tb_karyawan). Nama hanya verifikasi.
// Jenis Keluar yang diterima: RESIGN, END KONTRAK, PHK, MENGUNDURKAN DIRI,
// MERAWAT ORANGTUA SAKIT (dipetakan ke Resign/PHK/Habis Kontrak).

ini_set('memory_limit', '512M');
ini_set('max_execution_time', '300');

header("Content-Type: application/json");

require __DIR__ . '/../../config/koneksi.php';
require __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../../config/Excel.php'; // Excel::parseDate()/esc() terpusat

use PhpOffice\PhpSpreadsheet\IOFactory;

$mode = $_POST['action'] ?? 'import'; // preview = validasi saja (tanpa tulis DB), import = eksekusi

/**
 * Normalisasi input jenis keluar agar valid apa pun kapitalisasinya:
 * huruf dikecilkan, non-breaking space/tab/newline jadi spasi biasa,
 * spasi berlebih digabung menjadi satu.
 */
function normalisasiJenisKeluar($s)
{
    $s = (string)$s;
    $s = str_replace(["\xC2\xA0", "\t", "\n", "\r"], ' ', $s);
    $s = preg_replace('/\s+/', ' ', $s);
    return strtolower(trim($s));
}

/**
 * Validasi satu baris Excel (dipakai mode preview & import).
 * Tidak menulis ke database.
 */
function validasiBarisTereliminasi($koneksi, $cache_jenis, $alias_jenis, $row)
{
    $nik_karyawan = Excel::esc($koneksi, $row['B'] ?? '');
    $nama_file    = trim((string)($row['C'] ?? ''));
    $tgl_raw      = trim((string)($row['D'] ?? ''));
    $tgl_keluar   = Excel::parseDateFlexible($row['D'] ?? '');
    $jenis_raw    = trim((string)($row['E'] ?? ''));

    $r = [
        'nik' => $nik_karyawan,
        'nama_file' => $nama_file,
        'tgl_raw' => $tgl_raw,
        'tgl_keluar' => $tgl_keluar,
        'jenis_raw' => $jenis_raw,
        'jenis_mapped' => null,
        'id_jenis_keluar' => null,
        'status_baru' => null,
        'id_karyawan' => null,
        'nama_db' => null,
        'status_saat_ini' => null,
        'sudah_tereliminasi' => false,
        'valid' => false,
        'error' => null,
        'warning' => null,
    ];

    if ($nik_karyawan === '') {
        $r['error'] = 'NIK Karyawan kosong.';
        return $r;
    }
    if ($tgl_raw !== '' && $tgl_keluar === null) {
        $r['error'] = 'Tanggal keluar tidak dikenali. Contoh valid: 2026-09-20, "25 Februari 2025", 8/29/2026.';
        return $r;
    }
    if ($tgl_keluar === null) {
        $r['error'] = 'Tanggal keluar wajib diisi.';
        return $r;
    }
    if ($jenis_raw === '') {
        $r['error'] = 'Jenis keluar wajib diisi (RESIGN / END KONTRAK / PHK / MENGUNDURKAN DIRI).';
        return $r;
    }

    $key_jenis = $alias_jenis[normalisasiJenisKeluar($jenis_raw)] ?? normalisasiJenisKeluar($jenis_raw);
    if (!isset($cache_jenis[$key_jenis])) {
        $r['error'] = "Jenis keluar '$jenis_raw' tidak dikenal. Gunakan: RESIGN, END KONTRAK, PHK, MENGUNDURKAN DIRI.";
        return $r;
    }
    $r['id_jenis_keluar'] = $cache_jenis[$key_jenis]['id_jenis_keluar'];
    $r['jenis_mapped'] = $cache_jenis[$key_jenis]['nama_kategori'];
    // status_karyawan mengikuti nama kategori master (konsisten dgn act-eliminasi.php)
    $r['status_baru'] = $cache_jenis[$key_jenis]['nama_kategori'];

    $stmtKar = $koneksi->prepare("SELECT id_karyawan, nama_lengkap, status_karyawan FROM tb_karyawan WHERE nik_karyawan = ? LIMIT 1");
    $stmtKar->execute([$nik_karyawan]);
    $kar = $stmtKar->fetch();
    if (!$kar) {
        $r['error'] = "NIK '$nik_karyawan' tidak terdaftar di database. Daftarkan dulu di Data Karyawan.";
        return $r;
    }
    $r['id_karyawan'] = $kar['id_karyawan'];
    $r['nama_db'] = $kar['nama_lengkap'];
    $r['status_saat_ini'] = $kar['status_karyawan'];

    if ($nama_file !== '' && strtolower(trim($kar['nama_lengkap'])) !== strtolower($nama_file)) {
        $r['warning'] = "Nama di file ('$nama_file') berbeda dengan database ('{$kar['nama_lengkap']}'), diproses berdasarkan NIK.";
    }

    $stmtElim = $koneksi->prepare("SELECT id_tereliminasi FROM tb_tereliminasi WHERE id_karyawan = ? LIMIT 1");
    $stmtElim->execute([$kar['id_karyawan']]);
    $r['sudah_tereliminasi'] = (bool)$stmtElim->fetch();

    $r['valid'] = true;
    return $r;
}

$response = [
    "success" => false,
    "berhasil" => 0,
    "gagal" => 0,
    "dilewati" => 0,
    "errors" => [],
    "debug" => [],
    "error" => "",
    "rows" => [],
    "valid" => 0,
    "invalid" => 0
];

if (!isset($_FILES['file_excel']) || $_FILES['file_excel']['error'] !== UPLOAD_ERR_OK) {
    if (empty($_FILES)) {
        $response["error"] = "File terlalu besar. Cek post_max_size di php.ini.";
    } else {
        $response["error"] = "File Excel belum dipilih atau terjadi error saat upload.";
    }
    echo json_encode($response);
    exit;
}

$file = $_FILES['file_excel']['tmp_name'];
$file_ext = strtolower(pathinfo($_FILES['file_excel']['name'], PATHINFO_EXTENSION));

if (!in_array($file_ext, ['xls', 'xlsx'])) {
    $response["error"] = "Format file tidak didukung. Harus XLS atau XLSX.";
    echo json_encode($response);
    exit;
}

// Cache m_jenis_keluar: nama lower => [id, nama_kategori]
$cache_jenis = [];
$stmtJenis = $koneksi->query("SELECT id_jenis_keluar, nama_kategori, LOWER(TRIM(nama_kategori)) as nama FROM m_jenis_keluar");
while ($r = $stmtJenis->fetch()) $cache_jenis[$r['nama']] = $r;

$alias_jenis = [
    'habis kontrak' => 'habis kontrak',
    'end kontrak' => 'habis kontrak',
    'end contract' => 'habis kontrak',
    'kontrak habis' => 'habis kontrak',
    'pensiun' => 'pensiun',
    'pension' => 'pensiun',
    'phk' => 'phk',
    'termination' => 'phk',
    'resign' => 'resign',
    'resignation' => 'resign',
    'mengundurkan diri' => 'resign',
    'megundurkan diri' => 'resign', // toleransi salah ketik di data
    'merawat orangtua sakit' => 'resign',
    'merawat orang tua sakit' => 'resign',
    'black list' => 'black list',
    'blacklist' => 'black list',
];

try {
    $spreadsheet = IOFactory::load($file);
    $sheet = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

    // Validasi header toleran: abaikan kapitalisasi & spasi berlebih,
    // izinkan baris judul di atas header, abaikan kolom kosong di ujung.
    $expected_headers = ['no', 'nik karyawan', 'nama lengkap', 'tanggal keluar', 'jenis keluar'];

    $normHeader = function ($s) {
        $s = str_replace(["\xC2\xA0", "\t", "\n", "\r"], ' ', (string)$s);
        return strtolower(trim(preg_replace('/\s+/', ' ', $s)));
    };

    // Cari baris header (baris berisi kata NIK, maks. 5 baris pertama)
    $headerRowNum = null;
    $headers = [];
    foreach ($sheet as $num => $row) {
        if ($num > 5) break;
        $cells = array_values($row ?? []);
        if (stripos(implode(' ', $cells), 'nik') !== false) {
            $headerRowNum = $num;
            $headers = $cells;
            break;
        }
    }
    if ($headerRowNum === null) {
        $seen = implode(' | ', array_values($sheet[1] ?? []));
        throw new Exception('Baris header (yang berisi NIK) tidak ditemukan. Isi baris 1: [' . $seen . ']. Gunakan template "Format_Impor_Karyawan_Tereliminasi.xlsx".');
    }

    // Buang kolom kosong di ujung (sisa format Excel)
    while (!empty($headers) && trim((string)end($headers)) === '') array_pop($headers);

    $headersNorm = array_map($normHeader, array_slice($headers, 0, 5));
    if (count($headersNorm) < 5 || $headersNorm !== $expected_headers) {
        throw new Exception('Header tidak sesuai. Ditemukan: [' . implode(' | ', array_slice($headers, 0, 5)) . ']. Harus: No | NIK Karyawan | Nama Lengkap | Tanggal Keluar | Jenis Keluar.');
    }

    foreach ($sheet as $rowNum => $row) {
        if ($rowNum <= $headerRowNum) continue; // lewati judul & header

        // skip baris kosong (NIK dan Nama kosong)
        if (trim($row['B'] ?? '') === '' && trim($row['C'] ?? '') === '') continue;

        $v = validasiBarisTereliminasi($koneksi, $cache_jenis, $alias_jenis, $row);
        $v['baris'] = $rowNum;

        // MODE PREVIEW: kumpulkan hasil validasi saja, tanpa tulis DB
        if ($mode === 'preview') {
            $response['rows'][] = $v;
            if ($v['valid']) {
                $response['valid']++;
            } else {
                $response['invalid']++;
            }
            continue;
        }

        if (!$v['valid']) {
            $response["gagal"]++;
            $response["errors"][] = "Baris $rowNum: " . $v['error'];
            continue;
        }

        $nik_karyawan    = $v['nik'];
        $tgl_keluar      = $v['tgl_keluar'];
        $id_jenis_keluar = $v['id_jenis_keluar'];
        $status_baru     = $v['status_baru'];
        $id_karyawan     = $v['id_karyawan'];
        $alasan          = '';

        if ($v['warning']) {
            $response['debug'][] = "Baris $rowNum: Perhatian - " . $v['warning'];
        }

        $transaction_started = false;
        try {
            $koneksi->beginTransaction();
            $transaction_started = true;

            $stmtCekElim = $koneksi->prepare("SELECT id_tereliminasi FROM tb_tereliminasi WHERE id_karyawan = ? LIMIT 1");
            $stmtCekElim->execute([$id_karyawan]);
            $sudahAda = (bool)$stmtCekElim->fetch();
            if ($sudahAda) {
                $stmtElim = $koneksi->prepare("UPDATE tb_tereliminasi SET tgl_keluar = ?, id_jenis_keluar = ?, alasan_keluar = ?, created_at = NOW() WHERE id_karyawan = ?");
                $stmtElim->execute([$tgl_keluar, $id_jenis_keluar, $alasan, $id_karyawan]);
            } else {
                $stmtElim = $koneksi->prepare("INSERT INTO tb_tereliminasi (id_karyawan, tgl_keluar, id_jenis_keluar, alasan_keluar, created_at) VALUES (?, ?, ?, ?, NOW())");
                $stmtElim->execute([$id_karyawan, $tgl_keluar, $id_jenis_keluar, $alasan]);
            }

            $stmtKar = $koneksi->prepare("UPDATE tb_karyawan SET status_karyawan = ? WHERE id_karyawan = ?");
            $stmtKar->execute([$status_baru, $id_karyawan]);

            $koneksi->commit();
            $transaction_started = false;

            $response["berhasil"]++;
            $response["debug"][] = "Baris $rowNum: Berhasil - NIK: $nik_karyawan ($status_baru)";
        } catch (PDOException $e) {
            error_log("csv-tereliminasi DB baris $rowNum: " . $e->getMessage());
            if ($transaction_started && $koneksi->inTransaction()) {
                $koneksi->rollBack();
            }
            $response["gagal"]++;
            $response["errors"][] = "Baris $rowNum: Gagal menyimpan data eliminasi.";
        } catch (Exception $e) {
            if ($transaction_started && $koneksi->inTransaction()) {
                $koneksi->rollBack();
            }
            $response["gagal"]++;
            $response["errors"][] = "Baris $rowNum: " . $e->getMessage();
        }
    }

    $response["success"] = true;
    if ($mode === 'preview') {
        $response["preview"] = true;
        $response["rows"] = $response["rows"] ?? [];
        $response["valid"] = $response["valid"] ?? 0;
        $response["invalid"] = $response["invalid"] ?? 0;
        $response["message"] = "Validasi selesai. Siap diproses: {$response['valid']}, Bermasalah: {$response['invalid']}";
    } else {
        $response["message"] = "Import selesai. Berhasil: {$response['berhasil']}, Gagal: {$response['gagal']}";
    }
} catch (Exception $e) {
    error_log('csv-tereliminasi: ' . $e->getMessage());
    $response["error"] = "Gagal membaca file Excel: " . $e->getMessage();
}

echo json_encode($response);
exit;
