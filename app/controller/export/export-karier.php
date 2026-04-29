<?php
// Matikan error reporting agar tidak merusak format CSV jika ada warning
error_reporting(0);
ini_set('display_errors', 0);

require '../../config/koneksi.php';

// Tangkap parameter filter dari GET
$site    = $_GET['site'] ?? '';
$jabatan = $_GET['jabatan'] ?? '';
$status  = $_GET['status'] ?? 'Aktif'; 
$bulan   = $_GET['bulan'] ?? '';

// Build WHERE Clause (Sama seperti get-karier.php)
$where = ["1=1"];

if (!empty($site)) {
    $where[] = "k.id_site = '" . $koneksi->real_escape_string($site) . "'";
}
if (!empty($jabatan)) {
    $where[] = "k.id_jabatan = '" . $koneksi->real_escape_string($jabatan) . "'";
}
if (!empty($status)) {
    $where[] = "kr.status_karyawan = '" . $koneksi->real_escape_string($status) . "'";
}
if (!empty($bulan)) {
    $where[] = "MONTH(k.tgl_selesai) = '" . $koneksi->real_escape_string($bulan) . "'";
}

$where_sql = implode(" AND ", $where);

// Eksekusi Query Utama
$query = "SELECT k.*, j.nama_jabatan, d.nama_departemen, s.nama_site, kr.nik_karyawan, kr.nama_lengkap 
    FROM tb_karier k
    INNER JOIN tb_karyawan kr ON k.id_karyawan = kr.id_karyawan
    INNER JOIN tb_jabatan j ON k.id_jabatan = j.id_jabatan
    INNER JOIN tb_departemen d ON k.id_departemen = d.id_departemen
    INNER JOIN tb_site s ON k.id_site = s.id_site
    WHERE $where_sql
    ORDER BY kr.nama_lengkap ASC";

$result = $koneksi->query($query);

// Set header untuk memicu download file CSV
$filename = "Export_Data_Karier_" . date('Ymd_His') . ".csv";
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// Buka output stream
$output = fopen('php://output', 'w');

// Tulis Header Kolom (Sesuai dengan abjad Excel A - Y)
fputcsv($output, [
    'NO', 'NIK KARYAWAN', 'NAMA LENGKAP (Info)', 'SITE', 'DEPARTEMEN', 'JABATAN', 'GAJI BASIC', 
    'TUNJANGAN DAERAH', 'TUNJANGAN HIDUP', 'TUNJANGAN MAKAN', 'TUNJANGAN SKILL', 
    'PERFORMANCE BONUS', 'TUNJANGAN POSISI', 'OT PERTAMA', 'OT KEDUA', 'SPL', 
    'OT1 SPL', 'OT2 SPL', 'POTONGAN BPJS KES', 'POTONGAN JHT', 'PPH21', 
    'POTONGAN IZIN', 'POTONGAN MANGKIR', 'TGL MASUK SITE', 'TGL SELESAI KONTRAK'
]);

$no = 1;
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $id_karier = $row['id_karier'];

        // Siapkan variabel default untuk Tunjangan
        $t_daerah = 0; $t_hidup = 0; $t_makan = 0; $t_skill = 0; $p_bonus = 0; $t_posisi = 0;
        
        // Ambil Data Tunjangan
        $q_tunjangan = $koneksi->query("SELECT nama_tunjangan, jumlah_tunjangan FROM tb_tunjangan WHERE id_karier='$id_karier'");
        while ($t = $q_tunjangan->fetch_assoc()) {
            $nama_t = strtoupper(trim($t['nama_tunjangan']));
            if ($nama_t === 'TUNJANGAN DAERAH') $t_daerah = $t['jumlah_tunjangan'];
            if ($nama_t === 'TUNJANGAN HIDUP') $t_hidup = $t['jumlah_tunjangan'];
            if ($nama_t === 'TUNJANGAN MAKAN') $t_makan = $t['jumlah_tunjangan'];
            if ($nama_t === 'TUNJANGAN SKILL') $t_skill = $t['jumlah_tunjangan'];
            if ($nama_t === 'PERFORMANCE BONUS') $p_bonus = $t['jumlah_tunjangan'];
            if ($nama_t === 'TUNJANGAN POSISI') $t_posisi = $t['jumlah_tunjangan'];
        }

        // Siapkan variabel default untuk Potongan
        $pot_bpjs = 0; $pot_jht = 0; $pph21 = 0;

        // Ambil Data Potongan
        $q_potongan = $koneksi->query("SELECT jenis_potongan, jumlah_potongan FROM tb_potongan WHERE id_karier='$id_karier'");
        while ($p = $q_potongan->fetch_assoc()) {
            $jenis_p = strtoupper(trim($p['jenis_potongan']));
            if (strpos($jenis_p, 'BPJS KESEHATAN') !== false) $pot_bpjs = $p['jumlah_potongan'];
            if (strpos($jenis_p, 'BPJS KETENAGAKERJAAN') !== false) $pot_jht = $p['jumlah_potongan'];
            if ($jenis_p === 'PPH21') $pph21 = $p['jumlah_potongan'];
        }

        // Mapping Data ke dalam baris Array sesuai format Excel Import
        $baris = [
            $no++,                               // A: No
            $row['nik_karyawan'],                // B: NIK
            $row['nama_lengkap'],                // C: Nama Lengkap (hanya untuk bacaan HR, diabaikan saat import)
            $row['nama_site'],                   // D: Site
            $row['nama_departemen'],             // E: Departemen
            $row['nama_jabatan'],                // F: Jabatan
            $row['gaji_pokok'],                  // G: Basic
            $t_daerah,                           // H: Tunjangan Daerah
            $t_hidup,                            // I: Tunjangan Hidup
            $t_makan,                            // J: Tunjangan Makan
            $t_skill,                            // K: Tunjangan Skill
            $p_bonus,                            // L: Performance Bonus
            $t_posisi,                           // M: Tunjangan Posisi
            $row['ot_pertama'],                  // N: OT Pertama
            $row['ot_kedua'],                    // O: OT Kedua
            $row['spl'],                         // P: SPL
            $row['ot1_spl'],                     // Q: OT1 SPL
            $row['ot2_spl'],                     // R: OT2 SPL
            $pot_bpjs,                           // S: Potongan BPJS
            $pot_jht,                            // T: Potongan JHT
            $pph21,                              // U: PPH21
            $row['pot_izin'],                    // V: Potongan Izin
            $row['pot_mangkir'],                 // W: Potongan Mangkir
            $row['tgl_mulai'],                   // X: Tgl Mulai
            $row['tgl_selesai']                  // Y: Tgl Selesai
        ];

        // Tulis baris ke file CSV
        fputcsv($output, $baris);
    }
}

fclose($output);
exit;
?>