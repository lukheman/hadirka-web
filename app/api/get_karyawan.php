<?php
// POST_karyawan.php (FINAL: Mengambil Data Dasar + Karier Aktif Tanpa Duplikasi)

include 'koneksi-api.php'; // Pastikan jalur koneksi benar

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Asumsi: Variabel global $koneksi adalah objek koneksi MySQLi yang sudah valid
// if (!isset($koneksi) || $koneksi === false) {
//     http_response_code(500);
//     echo json_encode(['status' => 'error', 'message' => 'Koneksi database ($koneksi) tidak ditemukan atau gagal.']);
//     exit;
// }

// Fungsi Helper untuk menghitung Masa Kerja
if (!function_exists('hitungMasaKerja')) {
    function hitungMasaKerja($tgl_mulai)
    {
        if (empty($tgl_mulai) || $tgl_mulai === '0000-00-00') {
            return 'N/A';
        }
        try {
            $awal_kerja = new DateTime($tgl_mulai);
            $sekarang = new DateTime(date('Y-m-d'));
            $diff = $awal_kerja->diff($sekarang);

            $years = $diff->y;
            $months = $diff->m;

            if ($years > 0) {
                $masa_kerja = "$years Tahun";
                if ($months > 0) {
                    $masa_kerja .= " $months Bulan";
                }
            } elseif ($months > 0) {
                $masa_kerja = "$months Bulan";
            } else {
                $masa_kerja = "Baru bergabung";
            }
            return $masa_kerja;
        } catch (\Exception $e) {
            return 'Error Hitung Masa Kerja';
        }
    }
}

// Fungsi Helper untuk menghitung SISA KONTRAK
if (!function_exists('hitungSisaKontrak')) {
    function hitungSisaKontrak($tgl_selesai)
    {
        if (empty($tgl_selesai) || $tgl_selesai === '0000-00-00') {
            return 'Kontrak Tetap';
        }

        try {
            $hari_ini = new DateTime('today');
            $akhir_kontrak = new DateTime($tgl_selesai);

            if ($akhir_kontrak < $hari_ini) {
                return 'Kontrak Berakhir';
            }

            $diff = $hari_ini->diff($akhir_kontrak);

            // Konversi total tahun ke bulan
            $total_bulan = ($diff->y * 12) + $diff->m;
            $hari = $diff->d;

            $output = [];
            if ($total_bulan > 0) {
                $output[] = "$total_bulan Bulan";
            }
            if ($hari > 0) {
                $output[] = "$hari Hari";
            }

            if (empty($output)) {
                return 'Berakhir Hari Ini';
            }

            $teks_sisa = implode(' ', $output);

            // Tambahkan styling jika sisa waktu kritis (misal di bawah 30 hari)
            if ($total_bulan == 0 && $hari <= 30) {
                return "$teks_sisa";
            }

            return $teks_sisa;

        } catch (Exception $e) {
            return 'Format Tanggal Salah';
        }
    }
}



$response = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ID karyawan yang dikirim dari Flutter
    $id_karyawan = isset($_POST['id_karyawan']) ? mysqli_real_escape_string($koneksi, $_POST['id_karyawan']) : '';
    $today = date('Y-m-d');

    if (empty($id_karyawan)) {
        http_response_code(400); // Bad Request
        $response = ['status' => 'error', 'message' => 'ID Karyawan wajib diisi.'];
    } else {

        // =======================================================
        // 1. Query untuk mengambil Data Dasar Karyawan
        // =======================================================
        $query_base = "SELECT k.*, i.* FROM tb_karyawan k 
                        LEFT JOIN tb_identitas i ON k.id_karyawan = i.id_karyawan 
                        WHERE k.id_karyawan = '$id_karyawan' 
                        LIMIT 1";

        $result_base = mysqli_query($koneksi, $query_base);
        $data_karyawan_base = mysqli_fetch_assoc($result_base);

        if (!$data_karyawan_base) {
            http_response_code(404);
            $response = ['status' => 'error', 'message' => 'Data karyawan tidak ditemukan.'];
            goto end_script;
        }

        // =======================================================
        // 2. Query untuk mengambil Karier AKTIF (Paling Baru) + Detail Nama
        // =======================================================
        $query_karier = "SELECT 
                            cr.*,
                            j.nama_jabatan,
                            d.nama_departemen,
                            s.nama_site
                        FROM 
                            tb_karier cr
                        LEFT JOIN tb_jabatan j ON cr.id_jabatan = j.id_jabatan
                        LEFT JOIN tb_departemen d ON cr.id_departemen = d.id_departemen
                        LEFT JOIN tb_site s ON cr.id_site = s.id_site
                        WHERE 
                            cr.id_karyawan = '$id_karyawan'
                        AND cr.tgl_mulai <= '$today'
                        AND (
                            cr.tgl_selesai IS NULL 
                            OR cr.tgl_selesai = '0000-00-00'
                            OR cr.tgl_selesai >= '$today'
                        )
                        ORDER BY 
                            cr.tgl_mulai DESC
                        LIMIT 1";

        $result_karier = mysqli_query($koneksi, $query_karier);
        $data_karier_aktif = mysqli_fetch_assoc($result_karier);

        // =======================================================
        // 3. Susun Data Final (Flattened Structure for Flutter)
        // =======================================================

        $final_data = $data_karyawan_base;

        // --- 3a. Inisialisasi/Default Karier ---
        $final_data['jabatan'] = 'Non-Aktif/Belum Ada Karier';
        $final_data['divisi'] = 'Non-Aktif/Belum Ada Karier';
        $final_data['site'] = 'N/A';
        $final_data['sisa_kontrak'] = 'N/A';

        // --- 3b. Proses Karier Aktif ---
        if ($data_karier_aktif) {

            // Kolom yang diduplikat di output JSON sebelumnya:
            // - id_karyawan (sudah ada dari tb_karyawan)
            // - nama_jabatan, nama_departemen, nama_site (dipindahkan ke level atas)

            // 1. Masukkan Detail NAMA Karier ke Level Atas
            $final_data['jabatan'] = $data_karier_aktif['nama_jabatan'] ?? 'N/A';
            $final_data['divisi'] = $data_karier_aktif['nama_departemen'] ?? 'N/A';
            $final_data['site'] = $data_karier_aktif['nama_site'] ?? 'N/A';

            // 2. Hitung dan tambahkan masa kerja
            $tgl_selesai_kontrak = $data_karier_aktif['tgl_selesai'] ?? null;
            $final_data['sisa_kontrak'] = hitungSisaKontrak($tgl_selesai_kontrak);


            // 3. Bersihkan detail karier aktif untuk menghindari duplikasi key di JSON
            unset($data_karier_aktif['id_karyawan']);
            unset($data_karier_aktif['nama_jabatan']);
            unset($data_karier_aktif['nama_departemen']);
            unset($data_karier_aktif['nama_site']);

            // 4. Tambahkan Detail Karier yang sudah bersih
            $final_data['detail_karier_aktif'] = $data_karier_aktif;
        } else {
            // Karyawan ada, tapi tidak ada karier aktif hari ini
            $final_data['message_karier'] = 'Data karier tidak aktif atau belum dimulai hari ini.';
        }

        http_response_code(200);
        $response = [
            'status' => 'success',
            'message' => 'Data Karyawan dan Karier Aktif berhasil dimuat.',
            'data_karyawan' => $final_data
        ];
    }
} else {
    http_response_code(405);
    $response = ['status' => 'error', 'message' => 'Metode request tidak diizinkan.'];
}

end_script:
echo json_encode($response, JSON_UNESCAPED_UNICODE);
mysqli_close($koneksi);
