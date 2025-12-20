<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Content-Type: application/json");

require '../../config/koneksi.php';
require '../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

/* =========================
   HELPER
========================= */

function esc($c, $v)
{
    return mysqli_real_escape_string($c, trim((string)$v));
}

function excelDate($v) {

    if ($v === null || $v === '') return null;

    // Angka tapi TIDAK masuk range tanggal Excel
    if (is_numeric($v)) {
        if ($v < 25569 || $v > 60000) {
            return null; // abaikan angka aneh
        }

        try {
            return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($v)
                ->format('Y-m-d');
        } catch (Exception $e) {
            return null;
        }
    }

    // String tanggal biasa
    $t = strtotime($v);
    return $t ? date('Y-m-d', $t) : null;
}


function plus3Month($tgl)
{
    return $tgl ? date('Y-m-d', strtotime("+3 months", strtotime($tgl))) : null;
}

function getId($c, $table, $field, $value, $idField)
{
    $allowed = ['tb_site', 'tb_departemen', 'tb_jabatan'];
    if (!in_array($table, $allowed)) return null;

    $sql = "SELECT $idField FROM $table WHERE LOWER(TRIM($field))=?";
    $stmt = $c->prepare($sql);
    $v = strtolower(trim($value));
    $stmt->bind_param("s", $v);
    $stmt->execute();
    $r = $stmt->get_result();
    return $r->num_rows ? $r->fetch_assoc()[$idField] : null;
}

/* =========================
   RESPONSE
========================= */

$res = [
    "success" => false,
    "berhasil" => 0,
    "gagal" => 0,
    "errors" => []
];

/* =========================
   VALIDASI FILE
========================= */

if (!isset($_FILES['file_excel']) || $_FILES['file_excel']['error'] !== 0) {
    $res["errors"][] = "File tidak valid";
    echo json_encode($res);
    exit;
}

$ext = strtolower(pathinfo($_FILES['file_excel']['name'], PATHINFO_EXTENSION));
if (!in_array($ext, ['xls', 'xlsx'])) {
    $res["errors"][] = "Format file harus XLS/XLSX";
    echo json_encode($res);
    exit;
}

/* =========================
   LOAD EXCEL
========================= */

$spreadsheet = IOFactory::load($_FILES['file_excel']['tmp_name']);
$worksheet = $spreadsheet->getActiveSheet();

/**
 * PARAMETER PENTING:
 *  - formatData = false  ⬅️ MATIKAN AUTO DATE
 */
$sheet = $worksheet->toArray(
    null,   // nullValue
    true,   // calculateFormulas
    false,  // formatData  ⬅️ INI KUNCINYA
    true    // returnCellRef
);


/* =========================
   LOOP
========================= */

foreach ($sheet as $i => $r) {

    if ($i == 1) continue; // header

    try {
        /* =========================
           MAPPING EXCEL (LENGKAP)
        ========================= */

        $nik_karyawan   = esc($koneksi, $r['B']);
        $nama_lengkap   = esc($koneksi, $r['C']);
        $jenis_kelamin  = esc($koneksi, $r['D']);

        $nama_site      = esc($koneksi, $r['E']);
        $departemen     = esc($koneksi, $r['F']);
        $jabatan        = esc($koneksi, $r['G']);

        $tgl_masuk_site = excelDate($r['H'] ?? null);
        $tgl_join_ipip  = excelDate($r['I'] ?? null);
        $tgl_akhir      = plus3Month($tgl_masuk_site);

        $tempat_lahir   = esc($koneksi, $r['J']);
        $tgl_lahir      = excelDate($r['K']);
        $nationality    = esc($koneksi, $r['L']);
        $agama          = esc($koneksi, $r['M']);

        $education_level = esc($koneksi, $r['N']);
        $last_education = esc($koneksi, $r['O']);
        $major          = esc($koneksi, $r['P']);

        $atas_nama_rek  = esc($koneksi, $r['Q']);
        $no_rek         = esc($koneksi, $r['R']);
        $nama_bank      = esc($koneksi, $r['S']);

        $nama_ibu       = esc($koneksi, $r['T']);
        $nik_ktp        = esc($koneksi, $r['U']);

        $alamat_ktp     = esc($koneksi, $r['V']);
        $rt_rw          = esc($koneksi, $r['W']);
        $desa           = esc($koneksi, $r['X']);
        $kecamatan      = esc($koneksi, $r['Y']);
        $kabupaten      = esc($koneksi, $r['Z']);
        $provinsi       = esc($koneksi, $r['AA']);

        $email_pribadi  = esc($koneksi, $r['AC']);
        $no_telp        = esc($koneksi, $r['AD']);

        $npwp           = esc($koneksi, $r['AM']);
        $bpjs_tk        = esc($koneksi, $r['AN']);
        $bpjs_kes       = esc($koneksi, $r['AO']);
        $status_nikah   = esc($koneksi, $r['AP']);
        $kk_number      = esc($koneksi, $r['AQ']);

        if (!$nik_karyawan || !$nama_lengkap) {
            throw new Exception("NIK / Nama kosong");
        }

        /* =========================
           REFERENSI
        ========================= */

        $id_site = getId($koneksi, 'tb_site', 'nama_site', $nama_site, 'id_site');
        $id_dep  = getId($koneksi, 'tb_departemen', 'nama_departemen', $departemen, 'id_departemen');
        $id_jab  = getId($koneksi, 'tb_jabatan', 'nama_jabatan', $jabatan, 'id_jabatan');

        if (!$id_site || !$id_dep || !$id_jab) {
            throw new Exception("Site / Departemen / Jabatan tidak ditemukan");
        }

        /* =========================
           CEK KARYAWAN
        ========================= */

        $cek = $koneksi->query("SELECT id_karyawan FROM tb_karyawan WHERE nik_karyawan='$nik_karyawan'");

        if ($cek->num_rows) {
            // UPDATE
            $id = $cek->fetch_assoc()['id_karyawan'];

            $koneksi->query("
                UPDATE tb_karyawan SET
                    nama_lengkap='$nama_lengkap',
                    jenis_kelamin='$jenis_kelamin',
                    tempat_lahir='$tempat_lahir',
                    tgl_lahir=" . ($tgl_lahir ? "'$tgl_lahir'" : "NULL") . ",
                    nationality='$nationality',
                    agama='$agama',
                    email_pribadi='$email_pribadi',
                    no_telp='$no_telp',
                    status_pernikahan='$status_nikah'
                WHERE id_karyawan='$id'
            ");

            $koneksi->query("
                UPDATE tb_identitas SET
                    nik_ktp='$nik_ktp',
                    kk_number='$kk_number',
                    npwp='$npwp',
                    bpjs_tk='$bpjs_tk',
                    bpjs_kes='$bpjs_kes',
                    alamat_ktp='$alamat_ktp',
                    rt_rw='$rt_rw',
                    desa='$desa',
                    kecamatan='$kecamatan',
                    kabupaten='$kabupaten',
                    provinsi='$provinsi',
                    nama_ibu='$nama_ibu',
                    last_education='$last_education',
                    major='$major',
                    education_level='$education_level',
                    nama_bank='$nama_bank',
                    no_rek='$no_rek',
                    atas_nama_rek='$atas_nama_rek'
                WHERE id_karyawan='$id'
            ");
        } else {
            // INSERT
            $koneksi->query("
                INSERT INTO tb_karyawan
                (nik_karyawan,nama_lengkap,jenis_kelamin,tempat_lahir,tgl_lahir,
                 nationality,agama,email_pribadi,no_telp,status_pernikahan)
                VALUES
                ('$nik_karyawan','$nama_lengkap','$jenis_kelamin','$tempat_lahir',
                 " . ($tgl_lahir ? "'$tgl_lahir'" : "NULL") . ",
                 '$nationality','$agama','$email_pribadi','$no_telp','$status_nikah')
            ");

            $id = $koneksi->insert_id;

            $koneksi->query("
                INSERT INTO tb_identitas
                (id_karyawan,nik_ktp,kk_number,npwp,bpjs_tk,bpjs_kes,
                 alamat_ktp,rt_rw,desa,kecamatan,kabupaten,provinsi,
                 nama_ibu,last_education,major,education_level,
                 nama_bank,no_rek,atas_nama_rek)
                VALUES
                ('$id','$nik_ktp','$kk_number','$npwp','$bpjs_tk','$bpjs_kes',
                 '$alamat_ktp','$rt_rw','$desa','$kecamatan','$kabupaten','$provinsi',
                 '$nama_ibu','$last_education','$major','$education_level',
                 '$nama_bank','$no_rek','$atas_nama_rek')
            ");
        }

        /* =========================
           KARIER
        ========================= */

        $koneksi->query("
            INSERT INTO tb_karier
            (id_karyawan,id_site,id_departemen,id_jabatan,
             tgl_mulai,tgl_selesai,tgl_join_ipip)
            VALUES
            ('$id','$id_site','$id_dep','$id_jab',
             " . ($tgl_masuk_site ? "'$tgl_masuk_site'" : "NULL") . ",
             " . ($tgl_akhir ? "'$tgl_akhir'" : "NULL") . ",
             " . ($tgl_join_ipip ? "'$tgl_join_ipip'" : "NULL") . ")
        ");

        $res["berhasil"]++;
    } catch (Exception $e) {
        $res["gagal"]++;
        $res["errors"][] = "Baris $i → " . $e->getMessage();
    }
}

$res["success"] = $res["berhasil"] > 0;
echo json_encode($res);
exit;
