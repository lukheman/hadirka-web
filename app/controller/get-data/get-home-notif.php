<?php
session_start();
error_reporting(0);
header('Content-Type: application/json');

include __DIR__ . "/../../config/koneksi.php";

$user_level = $_SESSION['level'] ?? 99;
$response = [
    'show'  => false,
    'title' => '',
    'msg'   => ''
];

$show_notif = false;

// 1. HRD (Level 2) & Direktur (Level 1) -> Notif Pinjaman Pending
if (in_array($user_level, [1, 2])) {
    $q_pinjaman = mysqli_query($koneksi, "SELECT COUNT(*) as jml FROM tb_pengajuan_pinjaman WHERE status_pinjaman = 'pending'");
    $cek_pinjaman = mysqli_fetch_assoc($q_pinjaman);

    if ($cek_pinjaman['jml'] > 0) {
        $show_notif = true;
        $response['title'] = "Menunggu Approval!";
        $response['msg']   = "Terdapat <b>{$cek_pinjaman['jml']}</b> data pengajuan pinjaman karyawan yang berstatus PENDING dan perlu segera Anda proses.";
    }
}
// 2. Admin (Level 5) -> Notif Izin, Cuti & Bukti SKS Pending
elseif ($user_level == 5) {
    $q_izin = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as jml FROM tb_pengajuan_izin WHERE status_izin = 'pending'"));
    $q_cuti = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as jml FROM tb_pengajuan_cuti WHERE status_cuti = 'pending'"));
    // Tambahan Query Bukti SKS
    $q_sks  = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as jml FROM tb_bukti_sks WHERE status = 'Pending'"));

    // Tambahkan $q_sks['jml'] ke total
    $total_admin = $q_izin['jml'] + $q_cuti['jml'] + $q_sks['jml'];

    if ($total_admin > 0) {
        $show_notif = true;
        $response['title'] = "Pemberitahuan Kehadiran!";

        $msg = "Terdapat pengajuan karyawan yang belum diproses:<br><br>";
        if ($q_cuti['jml'] > 0) $msg .= "📌 <b>{$q_cuti['jml']}</b> Pengajuan Cuti<br>";
        if ($q_izin['jml'] > 0) $msg .= "📌 <b>{$q_izin['jml']}</b> Pengajuan Izin<br>";
        // Tambahan baris untuk menampilkan jumlah SKS jika ada
        if ($q_sks['jml'] > 0)  $msg .= "📌 <b>{$q_sks['jml']}</b> Bukti SKS/Sakit<br>";

        $response['msg'] = $msg;
    }
}
// 3. Safety (Level 4) -> Notif APD Pending
elseif ($user_level == 4) {
    $q_apd = mysqli_query($koneksi, "SELECT COUNT(*) as jml FROM tb_pengajuan_apd WHERE status_pengajuan = 'pending'");
    $cek_apd = mysqli_fetch_assoc($q_apd);

    if ($cek_apd['jml'] > 0) {
        $show_notif = true;
        $response['title'] = "Validasi APD Baru!";
        $response['msg']   = "Terdapat <b>{$cek_apd['jml']}</b> pengajuan Alat Pelindung Diri (APD) baru yang berstatus PENDING dan perlu divalidasi.";
    }
}

// Session dihilangkan agar query selalu dicek dan notif muncul setiap ke dashboard
if ($show_notif) {
    $response['show'] = true;
}

// Kembalikan data dalam format JSON ke frontend
echo json_encode($response);
exit;
