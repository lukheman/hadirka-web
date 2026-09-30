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
 * Cari ID berdasarkan nama (case-insensitive, trimmed)
 */
function getId($koneksi, $table, $nama_field, $nama_value, $id_field)
{
    $nama_value = mysqli_real_escape_string($koneksi, strtolower(trim($nama_value)));
    $nama_field_escaped = mysqli_real_escape_string($koneksi, $nama_field);
    $id_field_escaped = mysqli_real_escape_string($koneksi, $id_field);

    $query = "SELECT $id_field_escaped FROM $table WHERE LOWER(TRIM($nama_field_escaped))='$nama_value'";
    $result = $koneksi->query($query);

    return $result && $result->num_rows > 0 ? $result->fetch_assoc()[$id_field] : null;
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
        $cekKaryawan = $koneksi->query("SELECT id_karyawan, status_karyawan FROM tb_karyawan WHERE nik_karyawan='$nik'");
        if ($cekKaryawan->num_rows == 0) {
            $response["gagal"]++;
            $response["errors"][] = "Row $i : NIK '$nik' tidak terdaftar";
            continue;
        }
        $dataKaryawan = $cekKaryawan->fetch_assoc();

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
        $t_daerah   = Excel::cleanRupiah($row['H'] ?? 0);
        $t_hidup    = Excel::cleanRupiah($row['I'] ?? 0);
        $t_skill    = Excel::cleanRupiah($row['J'] ?? 0);
        $t_posisi   = Excel::cleanRupiah($row['K'] ?? 0);
        $t_makan    = Excel::cleanRupiah($row['L'] ?? 0);
        $p_bonus    = Excel::cleanRupiah($row['M'] ?? 0);
        $lembur     = Excel::cleanRupiah($row['N'] ?? 0); // Pengganti ke-5 kolom OT/SPL
        $pot_bpjs   = Excel::cleanRupiah($row['O'] ?? 0);
        $pot_jht    = Excel::cleanRupiah($row['P'] ?? 0);
        $pph21      = Excel::cleanRupiah($row['Q'] ?? 0);
        $pot_izin   = Excel::cleanRupiah($row['R'] ?? 0);
        $pot_mangkir = Excel::cleanRupiah($row['S'] ?? 0);
        
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
        $cekKarier = $koneksi->query("SELECT id_karier FROM tb_karier WHERE id_karyawan='$id_karyawan' ORDER BY tgl_mulai DESC, id_karier DESC LIMIT 1");

        if ($cekKarier->num_rows > 0) {
            $id_karier = $cekKarier->fetch_assoc()['id_karier'];

            $setTanggal = [];

            if ($tgl_masuk_site) {
                $setTanggal[] = "tgl_mulai='$tgl_masuk_site'";
            } else {
                $setTanggal[] = "tgl_mulai=NULL";
            }

            if ($tgl_selesai_kontrak) {
                $setTanggal[] = "tgl_selesai='$tgl_selesai_kontrak'";
            } else {
                $setTanggal[] = "tgl_selesai=NULL";
            }

            // Update Karier Terakhir (no_surat lama dipertahankan, tidak ditimpa)
            $koneksi->query("UPDATE tb_karier SET
                id_site='$id_site', id_departemen='$id_departemen', id_jabatan='$id_jabatan', 
                gaji_pokok=$basic,
                lembur=$lembur, 
                pot_izin=$pot_izin, 
                pot_mangkir=$pot_mangkir,
                " . implode(",", $setTanggal) . "
            WHERE id_karier='$id_karier'");
        } else {
            // Insert Baru jika belum ada data karier sama sekali (nomor surat otomatis)
            $tglMulai = $tgl_masuk_site ?: date('Y-m-d');
            $tglSelesai = $tgl_selesai_kontrak ?: null;
            $no_surat_sql = "NULL";
            $qKodeImp = $koneksi->query("SELECT kode_jabatan FROM tb_jabatan WHERE id_jabatan = '$id_jabatan' LIMIT 1");
            $kodeImp = ($qKodeImp && $qKodeImp->num_rows > 0) ? trim((string)($qKodeImp->fetch_assoc()['kode_jabatan'] ?? '')) : '';
            if ($kodeImp !== '') {
                try {
                    $no_surat_sql = "'" . $koneksi->real_escape_string(generate_no_surat($koneksi, $kodeImp, $tglMulai)) . "'";
                } catch (Exception $e) {
                    $no_surat_sql = "NULL"; // jabatan tanpa kode: biarkan kosong, tidak menggagalkan baris
                }
            }

            $koneksi->query("INSERT INTO tb_karier (
                id_karyawan, id_site, id_departemen, id_jabatan,
                gaji_pokok, lembur, pot_izin, pot_mangkir, spl, ot1_spl, ot2_spl, tgl_mulai, tgl_selesai, no_surat
            ) VALUES (
                '$id_karyawan', '$id_site', '$id_departemen', '$id_jabatan', $basic, $lembur,
                $pot_izin, $pot_mangkir, '', '', '', '$tglMulai', " . ($tglSelesai ? "'$tglSelesai'" : "NULL") . ", $no_surat_sql
            )");
            $id_karier = $koneksi->insert_id;
        }

        // 3. Kelola tb_tunjangan (Upsert Logic)
        $listTunjangan = [
            "Tunjangan Makan" => $t_makan,
            "Tunjangan Skill" => $t_skill,
            "Tunjangan Hidup" => $t_hidup,
            "Tunjangan Daerah" => $t_daerah,
            "Bonus Kehadiran" => $p_bonus, // <--- UBAH DISINI
            "Tunjangan Posisi" => $t_posisi
        ];

        $koneksi->query("DELETE FROM tb_tunjangan WHERE id_karier='$id_karier'");

        foreach ($listTunjangan as $nama_t => $nilai) {
            $koneksi->query("INSERT INTO tb_tunjangan 
                (id_karier,nama_tunjangan,jumlah_tunjangan)
                VALUES ('$id_karier','$nama_t',$nilai)");
        }

        // 4. Kelola tb_potongan (Upsert Logic)
        $listPotongan = [
            "BPJS KESEHATAN 1%" => $pot_bpjs,
            // BPJS KETENAGAKERJAAN 2% yg awalnya JHT 2%
            "BPJS KETENAGAKERJAAN 2%" => $pot_jht,
            "PPH21" => $pph21
        ];

        $koneksi->query("DELETE FROM tb_potongan WHERE id_karier='$id_karier'");

        foreach ($listPotongan as $jenis_p => $nilai_p) {
            $koneksi->query("INSERT INTO tb_potongan (id_karier, jenis_potongan, jumlah_potongan) VALUES ('$id_karier', '$jenis_p', $nilai_p)");
        }

        $response["berhasil"]++;
    }

    $response["success"] = true;
} catch (Exception $e) {
    $response["error"] = "Sistem Error: " . $e->getMessage();
}

echo json_encode($response);