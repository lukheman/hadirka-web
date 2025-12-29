<?php
// File: periode_slip.php (Endpoint untuk Daftar Periode Slip Gaji yang Open)

header('Content-Type: application/json; charset=utf-8');

include 'koneksi-api.php';

// ====================================================================
//          2. FUNGSI UTAMA: getAvailablePeriods
// ====================================================================

function getAvailablePeriods()
{
    global $koneksi;

    $periods = [];
    
    // Query untuk mengambil periode yang statusnya 'Open' dan tipenya 'Slip gaji'
    $sql = "SELECT id_priode, tgl_mulai, tgl_selesai, status_priode
            FROM tb_priode
            WHERE status_priode = 'Open' AND type_priode = 'Slip gaji'
            ORDER BY tgl_mulai DESC";
    
    $q = mysqli_query($koneksi, $sql);

    if (!$q) {
         return ['error' => 'Query database gagal: ' . mysqli_error($koneksi)];
    }

    if (mysqli_num_rows($q) == 0) {
        return []; // Mengembalikan array kosong jika tidak ada data
    }

    $is_first = true;
    
    while ($r = mysqli_fetch_assoc($q)) {
        $tgl_mulai_display = date('d M Y', strtotime($r['tgl_mulai']));
        $tgl_selesai_display = date('d M Y', strtotime($r['tgl_selesai']));
        
        $display_name = "$tgl_mulai_display - $tgl_selesai_display";
        
        // Tambahkan tag (Terbaru) pada periode paling atas
        if ($is_first) {
            $display_name .= " (Terbaru)";
            $is_first = false;
        }

        $periods[] = [
            'id' => $r['id_priode'],
            'display' => $display_name,
            'startDate' => $r['tgl_mulai'], // YYYY-MM-DD
            'endDate' => $r['tgl_selesai'], // YYYY-MM-DD
        ];
    }
    
    return $periods;
}

// ====================================================================
//          3. API ENDPOINT HANDLER
// ====================================================================

$response = getAvailablePeriods();

if (isset($koneksi) && is_object($koneksi)) $koneksi->close(); 

// Kirim header 200 OK
http_response_code(200);

// Jika kosong, $response akan berisi []
// Jika ada data, $response akan berisi daftar periode
echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>