<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ob_clean();

header("Content-Type: application/json");
require '../../config/koneksi.php';
require '../../../vendor/autoload.php';

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

function formatTanggal($value)
{
    if (empty($value)) return null;

    try {
        return date('Y-m-d', strtotime($value));
    } catch (Exception $e) {
        return null;
    }
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
    $sheet = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

    foreach ($sheet as $i => $row) {
        if ($i == 1) continue;

        $nik = trim($row['B'] ?? '');
        if (empty($nik)) continue;

        // 1. Ambil Data Karyawan (Cek Departemen/Jabatan untuk backup insert karier baru)
        $cekKaryawan = $koneksi->query("SELECT id_karyawan FROM tb_karyawan WHERE nik_karyawan='$nik'");
        if ($cekKaryawan->num_rows == 0) {
            $response["gagal"]++;
            $response["errors"][] = "Row $i : NIK '$nik' tidak terdaftar";
            continue;
        }
        $dataKaryawan = $cekKaryawan->fetch_assoc();
        $id_karyawan = $dataKaryawan['id_karyawan'];

        // Ambil Nilai dari Excel
        $nama_site  = trim($row['D'] ?? '');
        $nama_departemen = trim($row['E'] ?? '');
        $nama_jabatan = trim($row['F'] ?? '');
        $basic      = intval($row['G'] ?? 0);
        $t_daerah   = intval($row['H'] ?? 0);
        $t_hidup    = intval($row['I'] ?? 0);
        $t_makan    = intval($row['J'] ?? 0);
        $t_skill    = intval($row['K'] ?? 0);
        $p_bonus    = intval($row['L'] ?? 0);
        $t_posisi   = intval($row['M'] ?? 0);
        $ot_pertama = intval($row['N'] ?? 0);
        $ot_kedua   = intval($row['O'] ?? 0);
        $spl        = intval($row['P'] ?? 0);
        $ot1_spl    = intval($row['Q'] ?? 0);
        $ot2_spl    = intval($row['R'] ?? 0);
        $pot_bpjs   = intval($row['S'] ?? 0);
        $pot_jht    = intval($row['T'] ?? 0);
        $pph21      = intval($row['U'] ?? 0);
        $pot_izin   = intval($row['V'] ?? 0);
        $pot_mangkir = intval($row['W'] ?? 0);
        $tgl_masuk_site = formatTanggal($row['X'] ?? '');
        $tgl_selesai_kontrak = formatTanggal($row['Y'] ?? '');


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

            // Update Karier Terakhir
            $koneksi->query("UPDATE tb_karier SET
                id_site='$id_site',
                id_departemen='$id_departemen',
                id_jabatan='$id_jabatan', 
                gaji_pokok=$basic,
                ot_pertama=$ot_pertama,
                ot_kedua=$ot_kedua,
                spl=$spl,
                ot1_spl=$ot1_spl,
                ot2_spl=$ot2_spl,
                pot_izin=$pot_izin,
                pot_mangkir=$pot_mangkir,
                " . implode(",", $setTanggal) . "
            WHERE id_karier='$id_karier'");
        } else {
            // Insert Baru jika belum ada data karier sama sekali
            // Catatan: Pastikan kolom id_site, id_departemen, dll di database mengizinkan NULL atau beri nilai default 0
            $tglMulai = $tgl_masuk_site ?: date('Y-m-d');
            $tglSelesai = $tgl_selesai_kontrak ?: null;

            $koneksi->query("INSERT INTO tb_karier (
                id_karyawan, gaji_pokok, ot_pertama, ot_kedua, spl, ot1_spl, ot2_spl, 
                pot_izin, pot_mangkir, tgl_mulai, tgl_selesai
            ) VALUES (
                '$id_karyawan', $basic, $ot_pertama, $ot_kedua, $spl, $ot1_spl, $ot2_spl, 
                $pot_izin, $pot_mangkir, '$tglMulai', " . ($tglSelesai ? "'$tglSelesai'" : "NULL") . "
            )");
            $id_karier = $koneksi->insert_id;
        }

        // 3. Kelola tb_tunjangan (Upsert Logic)
        $listTunjangan = [
            "Tunjangan Makan" => $t_makan,
            "Tunjangan Skill" => $t_skill,
            "Tunjangan Hidup" => $t_hidup,
            "Tunjangan Daerah" => $t_daerah,
            "Performance Bonus" => $p_bonus,
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
            "JHT 2%" => $pot_jht,
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
