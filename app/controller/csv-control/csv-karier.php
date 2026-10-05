<?php
// Response HARUS JSON murni. Di hosting, 3 hal ini merusak response
// (lokal lolos karena limit longgar & display_errors mati):
//  1. display_errors=1 -> notice/warning ikut tercetak ke body JSON.
//  2. ob_clean() tanpa buffer aktif -> notice "No buffer to delete".
//  3. Limit hosting ketat (128M/30 dtk) -> fatal memory/timeout = halaman HTML.
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('memory_limit', '1024M');
ini_set('max_execution_time', '300');
while (ob_get_level() > 0) {
    ob_end_clean();
}

header("Content-Type: application/json");

// Plumbing terakhir: fatal apa pun (OOM/timeout/require gagal) tetap JSON.
register_shutdown_function(function () {
    $err = error_get_last();
    $fatal = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR];
    if ($err && in_array($err['type'] ?? 0, $fatal, true)) {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
        }
        echo json_encode([
            'success' => false,
            'berhasil' => 0,
            'gagal' => 0,
            'errors' => [],
            'error' => 'Server error: ' . ($err['message'] ?? 'fatal'),
        ]);
    }
});

require __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../../config/Excel.php'; // Excel::cleanRupiah()/parseDate() terpusat
require_once __DIR__ . '/../../config/nomor_surat.php'; // generate_no_surat()
require_once __DIR__ . '/../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Cari ID berdasarkan nama (case-insensitive, trimmed).
 * $table/$nama_field/$id_field adalah nama internal (whitelist), nilai dicari via placeholder.
 */
function getId($koneksi, $table, $nama_field, $nama_value, $id_field)
{
    $allowedTables = ['tb_site' => ['nama_site' => 'id_site'], 'tb_departemen' => ['nama_departemen' => 'id_departemen'], 'tb_jabatan' => ['nama_jabatan' => 'id_jabatan']];
    if (!isset($allowedTables[$table][$nama_field]) || $allowedTables[$table][$nama_field] !== $id_field) {
        return null;
    }

    $stmt = $koneksi->prepare("SELECT $id_field FROM $table WHERE LOWER(TRIM($nama_field))=?");
    $stmt->execute([strtolower(trim($nama_value))]);
    $row = $stmt->fetch();

    return $row[$id_field] ?? null;
}

$response = ["success" => false, "berhasil" => 0, "gagal" => 0, "errors" => []];

if (!isset($_FILES['file_excel'])) {
    $response["error"] = "File belum dipilih";
    echo json_encode($response);
    exit;
}

$file = $_FILES['file_excel']['tmp_name'];
$ext = strtolower(pathinfo($_FILES['file_excel']['name'], PATHINFO_EXTENSION));

if (!in_array($ext, ['xls', 'xlsx'])) {
    $response["error"] = "Format tidak didukung. Hanya XLS/XLSX";
    echo json_encode($response);
    exit;
}

try {
    $spreadsheet = IOFactory::load($file);
    
    // PERBAIKAN PENTING:
    // Parameter ke-3 (formatData) diubah jadi FALSE agar Excel tidak memaksa 
    // mengambil string berformat "Rp", melainkan nilai angka aslinya.
    $sheet = $spreadsheet->getActiveSheet()->toArray(null, true, false, true);

    foreach ($sheet as $i => $row) {
        if ($i == 1) continue;

        $nik = trim($row['B'] ?? '');
        if (empty($nik)) continue;

        // 1. Ambil Data Karyawan (Cek Departemen/Jabatan untuk backup insert karier baru)
        $stmtKar = $koneksi->prepare("SELECT id_karyawan, status_karyawan FROM tb_karyawan WHERE nik_karyawan=?");
        $stmtKar->execute([$nik]);
        $dataKaryawan = $stmtKar->fetch();
        if (!$dataKaryawan) {
            $response["gagal"]++;
            $response["errors"][] = "Row $i : NIK '$nik' tidak terdaftar";
            continue;
        }

        // --- TAMBAHKAN VALIDASI STATUS DISINI ---
        // Pastikan variabel aman jika status ternyata kosong/null dari database
        $status_karyawan = strtolower(trim($dataKaryawan['status_karyawan'] ?? ''));

        if ($status_karyawan === 'resign' || 
            $status_karyawan === 'phk' || 
            $status_karyawan === 'habis kontrak' || 
            $status_karyawan === 'pensiun' ||
            $status_karyawan === 'black list') {
            
            $response["gagal"]++;
            $response["errors"][] = "Row $i : NIK '$nik' ditolak karena sudah $status_karyawan";
            continue; // Skip baris ini dan lanjut ke karyawan berikutnya di Excel
        }
        // ----------------------------------------

        $id_karyawan = $dataKaryawan['id_karyawan'];

        // Ambil Nilai dari Excel & Terapkan Excel::cleanRupiah() pada nilai angka/uang
        // Nama Karyawan di kolom C di skip
        $nama_site       = trim($row['D'] ?? '');
        $nama_departemen = trim($row['E'] ?? '');
        $nama_jabatan    = trim($row['F'] ?? '');
        
        $basic      = Excel::cleanRupiah($row['G'] ?? 0);
        // Kolom H-S (tunjangan/lembur/potongan) diabaikan: hanya gaji pokok (penyederhanaan Okt 2026).
        
        $tgl_masuk_site = Excel::parseDate($row['T'] ?? '');
        $tgl_selesai_kontrak = Excel::parseDate($row['U'] ?? '');
        // Kolom V (NO SURAT) diabaikan: nomor dibuat otomatis sistem.

        // === Ambil ID referensi ===
        $id_site       = getId($koneksi, 'tb_site', 'nama_site', $nama_site, 'id_site');
        $id_departemen = getId($koneksi, 'tb_departemen', 'nama_departemen', $nama_departemen, 'id_departemen');
        $id_jabatan    = getId($koneksi, 'tb_jabatan', 'nama_jabatan', $nama_jabatan, 'id_jabatan');

        // Validasi ID referensi
        if (!$id_site || !$id_departemen || !$id_jabatan) {
            $response["gagal"]++;
            $response["errors"][] = "Baris $i: Site '$nama_site', Departemen '$nama_departemen', atau Jabatan '$nama_jabatan' tidak ditemukan.";
            continue;
        }

        // 2. Kelola tb_karier
        $stmtCekKarier = $koneksi->prepare("SELECT id_karier FROM tb_karier WHERE id_karyawan=? ORDER BY tgl_mulai DESC, id_karier DESC LIMIT 1");
        $stmtCekKarier->execute([$id_karyawan]);
        $rowKarier = $stmtCekKarier->fetch();

        if ($rowKarier) {
            $id_karier = $rowKarier['id_karier'];

            $setTanggal = [];
            $paramsTanggal = [$id_site, $id_departemen, $id_jabatan, $basic];

            if ($tgl_masuk_site) {
                $setTanggal[] = "tgl_mulai=?";
                $paramsTanggal[] = $tgl_masuk_site;
            } else {
                $setTanggal[] = "tgl_mulai=NULL";
            }

            if ($tgl_selesai_kontrak) {
                $setTanggal[] = "tgl_selesai=?";
                $paramsTanggal[] = $tgl_selesai_kontrak;
            } else {
                $setTanggal[] = "tgl_selesai=NULL";
            }

            // Update Karier Terakhir (no_surat lama dipertahankan, tidak ditimpa)
            $paramsTanggal[] = $id_karier;
            try {
                $stmtUpdKarier = $koneksi->prepare("UPDATE tb_karier SET
                    id_site=?, id_departemen=?, id_jabatan=?,
                    gaji_pokok=?,
                    " . implode(",", $setTanggal) . "
                WHERE id_karier=?");
                $stmtUpdKarier->execute($paramsTanggal);
            } catch (PDOException $e) {
                error_log('csv-karier update: ' . $e->getMessage());
                $response["gagal"]++;
                $response["errors"][] = "Row $i : Gagal update karier.";
                continue;
            }
        } else {
            // Insert Baru jika belum ada data karier sama sekali (nomor surat otomatis)
            $tglMulai = $tgl_masuk_site ?: date('Y-m-d');
            $tglSelesai = $tgl_selesai_kontrak ?: null;
            $no_surat_val = null;
            $stmtKode = $koneksi->prepare("SELECT kode_jabatan FROM tb_jabatan WHERE id_jabatan = ? LIMIT 1");
            $stmtKode->execute([$id_jabatan]);
            $rowKode = $stmtKode->fetch();
            $kodeImp = $rowKode ? trim((string)($rowKode['kode_jabatan'] ?? '')) : '';
            if ($kodeImp !== '') {
                try {
                    $no_surat_val = generate_no_surat($koneksi, $kodeImp, $tglMulai);
                } catch (Exception $e) {
                    error_log('csv-karier no_surat: ' . $e->getMessage());
                    $no_surat_val = null; // jabatan tanpa kode: biarkan kosong, tidak menggagalkan baris
                }
            }

            try {
                $stmtInsKarier = $koneksi->prepare("INSERT INTO tb_karier (
                    id_karyawan, id_site, id_departemen, id_jabatan,
                    gaji_pokok, tgl_mulai, tgl_selesai, no_surat
                ) VALUES (
                    ?, ?, ?, ?, ?,
                    ?, ?, ?
                )");
                $stmtInsKarier->execute([
                    $id_karyawan, $id_site, $id_departemen, $id_jabatan, $basic,
                    $tglMulai, $tglSelesai, $no_surat_val
                ]);
            } catch (PDOException $e) {
                error_log('csv-karier insert: ' . $e->getMessage());
                $response["gagal"]++;
                $response["errors"][] = "Row $i : Gagal insert karier.";
                continue;
            }
        }

        // Tabel tunjangan/potongan dihapus (penyederhanaan gaji Okt 2026): hanya gaji pokok.
        $response["berhasil"]++;
    }

    $response["success"] = true;
} catch (Exception $e) {
    error_log('csv-karier: ' . $e->getMessage());
    $response["error"] = "Sistem Error: gagal memproses file.";
}

echo json_encode($response);