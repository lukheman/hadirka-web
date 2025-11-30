<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config/koneksi.php';

$filterSite = $_POST['site'] ?? "";
$filterJabatan = $_POST['jabatan'] ?? "";

// BASE QUERY: LEFT JOIN ke tb_identitas supaya data identitas tersedia bila ada
$sql = "
    SELECT 
        k.*, 
        kr.id_site, 
        kr.id_jabatan, 
        kr.tgl_selesai,
        s.nama_site,
        j.nama_jabatan,
        idt.* 
    FROM tb_karyawan k
    LEFT JOIN tb_karier kr ON k.id_karyawan = kr.id_karyawan
    LEFT JOIN tb_site s ON kr.id_site = s.id_site
    LEFT JOIN tb_jabatan j ON kr.id_jabatan = j.id_jabatan
    LEFT JOIN tb_identitas idt ON idt.id_karyawan = k.id_karyawan
";

$where = [];

if ($filterSite == "") {
    // hanya tampilkan karyawan baru atau expired kontrak
    $where[] = "(kr.id_karier IS NULL OR (kr.tgl_selesai < CURDATE()))";
} else {
    $where[] = "kr.id_site = '" . $koneksi->real_escape_string($filterSite) . "'";

    if ($filterJabatan != "") {
        $where[] = "kr.id_jabatan = '" . $koneksi->real_escape_string($filterJabatan) . "'";
    }
}

if ($where) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$sql .= " ORDER BY k.id_karyawan DESC";

$q = $koneksi->query($sql);

$data = [];
$no = 1;

if ($q && $q->num_rows > 0) {
    while ($r = $q->fetch_assoc()) {
        $id_karyawan = $r['id_karyawan'];


        // mark expired text jika tgl_selesai < today
        $expiredClass = "";
        if (!empty($r['tgl_selesai']) && $r['tgl_selesai'] < date("Y-m-d")) {
            $expiredClass = "text-danger";
        }

        // cek warna baris: jika tunjangan BPJS ada dan jumlah_tunjangan == 0 maka bg-danger
        $rowcolor = "";
        $idKR = $r['id_karyawan'];
        $cek_karier = $koneksi->query("SELECT id_karier FROM tb_karier WHERE id_karyawan = '" . $koneksi->real_escape_string($idKR) . "' LIMIT 1");
        $rowKarier = $cek_karier ? $cek_karier->fetch_assoc() : null;
        $id_karier = $rowKarier['id_karier'] ?? null;

        if ($id_karier) {
            $cek_tunjagan = $koneksi->query("SELECT jumlah_tunjangan FROM tb_tunjangan WHERE id_karier='" . $koneksi->real_escape_string($id_karier) . "' AND nama_tunjangan LIKE '%BPJS%' LIMIT 1");
            $rw = $cek_tunjagan ? $cek_tunjagan->fetch_assoc() : null;
            if ($rw && isset($rw['jumlah_tunjangan']) && intval($rw['jumlah_tunjangan']) === 0) {
                $rowcolor = "table-danger"; // bootstrap class
            }
        }

        // ambil data identitas (kolom dari tb_identitas) dengan aman
        $nik_ktp = $r['nik_ktp'] ?? '';
        $kk_number = $r['kk_number'] ?? '';
        $npwp = $r['npwp'] ?? '';
        $bpjs_tk = $r['bpjs_tk'] ?? '';
        $bpjs_kes = $r['bpjs_kes'] ?? '';
        $alamat_ktp = $r['alamat_ktp'] ?? '';
        $provinsi = $r['provinsi'] ?? '';
        $kabupaten = $r['kabupaten'] ?? '';
        $kecamatan = $r['kecamatan'] ?? '';
        $desa = $r['desa'] ?? '';
        $rt_rw = $r['rt_rw'] ?? '';
        $nama_ibu = $r['nama_ibu'] ?? '';
        $last_education = $r['last_education'] ?? '';
        $major = $r['major'] ?? '';
        $education_level = $r['education_level'] ?? '';
        $email_pribadi = $r['email_pribadi'] ?? '';
        $nationality = $r['nationality'] ?? '';
        $status_pernikahan = $r['status_pernikahan'] ?? '';

        // detail HTML (child row) - tampilkan rapi
        // gunakan htmlspecialchars untuk menghindari XSS
        $detail = "
            <table class='table table-sm mb-0'>
                <tr><td style='width:180px'><b>Jenis Kelamin</b></td><td>: " . htmlspecialchars(jk($r['jenis_kelamin'])) . "</td></tr>
                <tr><td><b>Agama</b></td><td>: " . htmlspecialchars($r['agama']) . "</td></tr>
                <tr><td><b>Status Pernikahan</b></td><td>: " . htmlspecialchars($status_pernikahan) . "</td></tr>
                <tr><td><b>Tempat, Tgl Lahir</b></td><td>: " . htmlspecialchars($r['tempat_lahir']) . ", " . (empty($r['tgl_lahir']) ? '-' : date('d-m-Y', strtotime($r['tgl_lahir']))) . "</td></tr>
                <tr><td><b>Umur</b></td><td>: " . (empty($r['tgl_lahir']) ? '-' : htmlspecialchars(hitungUmur($r['tgl_lahir']) . ' Tahun')) . "</td></tr>
                <tr><td><b>Email</b></td><td>: " . htmlspecialchars($email_pribadi) . "</td></tr>
                <tr><td><b>No. Telp</b></td><td>: " . htmlspecialchars($r['no_telp']) . "</td></tr>
                <tr><td><b>Nationality</b></td><td>: " . htmlspecialchars($nationality) . "</td></tr>

                <tr><td colspan='2'><b>--- Identitas ---</b></td></tr>
                <tr><td><b>NIK (KTP)</b></td><td>: " . htmlspecialchars($nik_ktp) . "</td></tr>
                <tr><td><b>No. KK</b></td><td>: " . htmlspecialchars($kk_number) . "</td></tr>
                <tr><td><b>NPWP</b></td><td>: " . htmlspecialchars($npwp) . "</td></tr>
                <tr><td><b>BPJS TK</b></td><td>: " . htmlspecialchars($bpjs_tk) . "</td></tr>
                <tr><td><b>BPJS KES</b></td><td>: " . htmlspecialchars($bpjs_kes) . "</td></tr>

                <tr><td colspan='2'><b>--- Alamat KTP ---</b></td></tr>
                <tr><td><b>Alamat</b></td><td>: " . nl2br(htmlspecialchars($alamat_ktp)) . "</td></tr>
                <tr><td><b>Provinsi</b></td><td>: " . htmlspecialchars($provinsi) . "</td></tr>
                <tr><td><b>Kab/Kota</b></td><td>: " . htmlspecialchars($kabupaten) . "</td></tr>
                <tr><td><b>Kecamatan</b></td><td>: " . htmlspecialchars($kecamatan) . "</td></tr>
                <tr><td><b>Desa</b></td><td>: " . htmlspecialchars($desa) . "</td></tr>
                <tr><td><b>RT/RW</b></td><td>: " . htmlspecialchars($rt_rw) . "</td></tr>

                <tr><td colspan='2'><b>--- Keluarga & Pendidikan ---</b></td></tr>
                <tr><td><b>Nama Ibu</b></td><td>: " . htmlspecialchars($nama_ibu) . "</td></tr>
                <tr><td><b>Pendidikan Terakhir</b></td><td>: " . htmlspecialchars($last_education) . "</td></tr>
                <tr><td><b>Jurusan</b></td><td>: " . htmlspecialchars($major) . "</td></tr>
                <tr><td><b>Tingkat Pendidikan</b></td><td>: " . htmlspecialchars($education_level) . "</td></tr>

                <tr><td colspan='2'><b>Site</b></td></tr>
                <tr><td><b>Lokasi</b></td><td>: " . htmlspecialchars($r['nama_site'] ?? '-') . "</td></tr>
                <tr><td><b>Jabatan</b></td><td>: " . htmlspecialchars($r['nama_jabatan'] ?? '-') . "</td></tr>
                <tr><td><b>Kontrak selesai</b></td><td>: " . ($r['tgl_selesai'] ? date('d-m-Y', strtotime($r['tgl_selesai'])) : '-') . "</td></tr>
            </table>
        ";

        $nama = htmlspecialchars($r['nama_lengkap']);
        // gunakan badge untuk menampilkan nik_karyawan (dari tb_karyawan) — jika ingin gunakan nik_ktp pada identitas, ganti sesuai kebutuhan
        $nik_display = htmlspecialchars($r['nik_karyawan'] ?? '');
        $nik = "<span class='badge bg-info'>" . $nik_display . "</span>";
        $jabatan = htmlspecialchars($r['nama_jabatan'] ?: "None");
        $action = '<a href="karier/' . htmlspecialchars(encrypt($r['id_karyawan'])) . '" class="btn btn-sm btn-success">Karier</a>
        <button class="btn btn-sm btn-primary btnEdit" data-id="' . $id_karyawan . '">
                <i class="fa fa-edit"></i>
            </button>
            <button class="btn btn-sm btn-danger btnDelete" data-id="' . $id_karyawan . '">
                <i class="fa fa-trash"></i>
            </button>
        ';

        $data[] = [
            "detail" => $detail,
            "rowClass" => trim("$expiredClass $rowcolor"),
            "no" => $no++,
            "nama" => $nama,
            "nik" => $nik,
            "jabatan" => $jabatan,
            "action" => $action
        ];
    }
}

// kembalikan JSON seperti DataTables harapkan
echo json_encode(["data" => $data]);
exit;
