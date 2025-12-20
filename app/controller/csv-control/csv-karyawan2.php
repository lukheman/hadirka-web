<?php
header("Content-Type: application/json");

require '../../config/koneksi.php';
require '../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

// Fungsi cari ID berdasar nama (trim & lowercase)
function getId($koneksi, $table, $nama_field, $nama_value, $id_field)
{
    $nama_value = strtolower(trim($nama_value));
    $q = $koneksi->query("SELECT $id_field FROM $table WHERE LOWER(TRIM($nama_field))='$nama_value'");
    return $q->num_rows > 0 ? $q->fetch_assoc()[$id_field] : null;
}

// Fungsi tambah 3 bulan
function tglAkhir3Bulan($tgl_mulai)
{
    if (empty($tgl_mulai)) return null;

    $start = date('Y-m-d', strtotime($tgl_mulai));
    $akhir = date('Y-m-d', strtotime("+3 months", strtotime($start)));

    return $akhir;
}

// Response awal
$response = [
    "success" => false,
    "berhasil" => 0,
    "gagal" => 0,
    "errors" => [],
    "error" => ""
];

// Cek file
if (!isset($_FILES['file_excel'])) {
    $response["error"] = "File Excel belum dipilih.";
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

try {

    $sheet = IOFactory::load($file)
        ->getActiveSheet()
        ->toArray(null, true, true, true);

    foreach ($sheet as $rowNum => $row) {
        if ($rowNum == 1) continue; // Skip header

        // === Mapping data kolom Excel ===
        $nik_karyawan  = trim($row['B'] ?? '');
        $nama_lengkap  = trim($row['C'] ?? '');
        $jenis_kelamin = trim($row['D'] ?? '');

        $nama_site     = trim($row['E'] ?? '');
        $departemen    = trim($row['F'] ?? '');
        $jabatan       = trim($row['H'] ?? '');

        $tgl_masuk_site_raw = $row['O'] ?? '';
        $tgl_join_ipip_raw  = $row['P'] ?? '';
        $tgl_masuk_site = !empty($tgl_masuk_site_raw) ? date('Y-m-d', strtotime($tgl_masuk_site_raw)) : null;
        $tgl_join_ipip  = !empty($tgl_join_ipip_raw) ? date('Y-m-d', strtotime($tgl_join_ipip_raw)) : null;
        $tgl_akhir      = tglAkhir3Bulan($tgl_masuk_site);

        $tempat_lahir  = trim($row['U'] ?? '');
        $tgl_lahir     = !empty($row['V']) ? date('Y-m-d', strtotime($row['V'])) : null;
        $nationality   = trim($row['Z'] ?? '');
        $agama         = trim($row['AA'] ?? '');
        $pendidikan_terakhir = trim($row['AB'] ?? '');
        $nama_ibu      = trim($row['AH'] ?? '');
        $nik_ktp       = trim($row['AI'] ?? '');

        $desa          = trim($row['AM'] ?? '');
        $kecamatan     = trim($row['AN'] ?? '');
        $kabupaten     = trim($row['AO'] ?? '');
        $provinsi      = trim($row['AP'] ?? '');

        $email_pribadi = trim($row['AR'] ?? '');
        $no_telp       = trim($row['AS'] ?? '');
        $rt_rw         = trim($row['AL'] ?? '');

        $status_karyawan   = trim($row['BA'] ?? '');
        $npwp              = trim($row['BB'] ?? '');
        $bpjs_tk           = trim($row['BC'] ?? '');
        $bpjs_kes          = trim($row['BD'] ?? '');
        $kk_number         = trim($row['BE'] ?? '');
        $status_pernikahan = trim($row['BF'] ?? '');
        $alamat_ktp        = trim($row['AG'] ?? '');

        // === Validasi wajib ===
        if ($nik_karyawan == '' || $nama_lengkap == '') {
            $response["gagal"]++;
            $response["errors"][] = "Baris $rowNum : NIK atau Nama kosong.";
            continue;
        }

        // === Cek / Insert / Update tb_karyawan & tb_identitas ===
        $cek = $koneksi->query("SELECT nik_karyawan FROM tb_karyawan WHERE nik_karyawan='$nik_karyawan'");
        if ($cek->num_rows > 0) {
            $id_karyawan = $cek->fetch_assoc()['id_karyawan'];

            // UPDATE tb_karyawan
            $koneksi->query("UPDATE tb_karyawan SET
                    nama_lengkap='$nama_lengkap',
                    jenis_kelamin='$jenis_kelamin',
                    tgl_lahir=" . ($tgl_lahir ? "'$tgl_lahir'" : "NULL") . ",
                    tempat_lahir='$tempat_lahir',
                    email_pribadi='$email_pribadi',
                    no_telp='$no_telp',
                    nationality='$nationality',
                    agama='$agama',
                    status_pernikahan='$status_pernikahan',
                    status_karyawan='$status_karyawan'
                WHERE id_karyawan='$id_karyawan'
            ");

            // UPDATE tb_identitas
            $koneksi->query("UPDATE tb_identitas SET
                    nik_ktp='$nik_ktp',
                    kk_number='$kk_number',
                    npwp='$npwp',
                    bpjs_tk='$bpjs_tk',
                    bpjs_kes='$bpjs_kes',
                    alamat_ktp='$alamat_ktp',
                    provinsi='$provinsi',
                    kabupaten='$kabupaten',
                    kecamatan='$kecamatan',
                    desa='$desa',
                    rt_rw='$rt_rw',
                    nama_ibu='$nama_ibu'
                WHERE id_karyawan='$id_karyawan'
            ");
        } else {
            // INSERT tb_karyawan
            $koneksi->query("INSERT INTO tb_karyawan
                (nik_karyawan,nama_lengkap,jenis_kelamin,tgl_lahir,tempat_lahir,email_pribadi,no_telp,nationality,agama,status_pernikahan,status_karyawan)
                VALUES
                ('$nik_karyawan','$nama_lengkap','$jenis_kelamin'," . ($tgl_lahir ? "'$tgl_lahir'" : "NULL") . ",'$tempat_lahir','$email_pribadi','$no_telp','$nationality','$agama','$status_pernikahan','$status_karyawan')
            ");
            $id_karyawan = $koneksi->insert_id;

            // INSERT tb_identitas
            $koneksi->query("INSERT INTO tb_identitas
                (id_karyawan,nik_ktp,kk_number,npwp,bpjs_tk,bpjs_kes,alamat_ktp,provinsi,kabupaten,kecamatan,desa,rt_rw,nama_ibu)
                VALUES
                ('$id_karyawan','$nik_ktp','$kk_number','$npwp','$bpjs_tk','$bpjs_kes','$alamat_ktp','$provinsi','$kabupaten','$kecamatan','$desa','$rt_rw','$nama_ibu')
            ");
        }
        
        
        // --- Ambil ID site / departemen / jabatan ---
        $id_site       = getId($koneksi, 'tb_site', 'nama_site', $nama_site, 'id_site');
        $id_departemen = getId($koneksi, 'tb_departemen', 'nama_departemen', $departemen, 'id_departemen');
        $id_jabatan    = getId($koneksi, 'tb_jabatan', 'nama_jabatan', $jabatan, 'id_jabatan');

        // Debug ID
        $response["debug"][] = "Baris $rowNum : IDs -> Site=$id_site, Dept=$id_departemen, Jabatan=$id_jabatan";

        // Skip jika salah satu NULL
        if (!$id_site || !$id_departemen || !$id_jabatan) {
            $response["gagal"]++;
            $response["errors"][] = "Baris $rowNum : Site/Departemen/Jabatan tidak ditemukan";
            continue;
        }
        
        // Cek tb_karier
        $cekKarier = $koneksi->query("SELECT id_karier FROM tb_karier WHERE id_karyawan='$id_karyawan'");
        if ($cekKarier->num_rows == 0) {
            // Insert baru
            $sqlKarier = "INSERT INTO tb_karier (id_karyawan,id_site,id_departemen,id_jabatan,tgl_mulai,tgl_selesai)
                VALUES ('$id_karyawan','$id_site','$id_departemen','$id_jabatan',
                " . ($tgl_masuk_site ? "'$tgl_masuk_site'" : "NULL") . ",
                " . ($tgl_akhir ? "'$tgl_akhir'" : "NULL") . ")";
        } else {
            // Update hanya record terakhir
            $idKarier = $cekKarier->fetch_assoc()['id_karier'];
            $sqlKarier = "UPDATE tb_karier SET
                id_site='$id_site',
                id_departemen='$id_departemen',
                id_jabatan='$id_jabatan',
                tgl_mulai=" . ($tgl_masuk_site ? "'$tgl_masuk_site'" : "NULL") . ",
                tgl_selesai=" . ($tgl_akhir ? "'$tgl_akhir'" : "NULL") . "
                WHERE id_karier='$idKarier'";
        }

        // Eksekusi dan debug
        if (!$koneksi->query($sqlKarier)) {
            $response["gagal"]++;
            $response["errors"][] = "Baris $rowNum : Error tb_karier = " . $koneksi->error . " | SQL: $sqlKarier";
        } else {
            $response["debug"][] = "Baris $rowNum : tb_karier OK | SQL: $sqlKarier";
        }
    }

    $response["success"] = true;
} catch (Exception $e) {
    $response["error"] = "Gagal membaca file Excel: " . $e->getMessage();
}

echo json_encode($response);
exit;
