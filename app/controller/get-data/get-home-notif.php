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

// 1. Notif pinjaman dihapus bersama tabelnya (penyederhanaan gaji Okt 2026).
// 2. Admin (Level 5) -> Notif Izin, Cuti & Bukti SKS Pending
if ($user_level == 5) {
    $start_date = date('Y-m-21', strtotime(date('Y-m-01') . ' -1 month'));
    $end_date   = date('Y-m-d');

    $q_izin = ($koneksi->query("SELECT COUNT(*) as jml FROM tb_pengajuan_izin WHERE status_izin = 'pending' AND tgl_izin BETWEEN '$start_date' AND '$end_date'")->fetch());
    $q_cuti = ($koneksi->query("SELECT COUNT(*) as jml FROM tb_pengajuan_cuti WHERE status_cuti = 'pending' AND tgl_awal BETWEEN '$start_date' AND '$end_date'")->fetch());
    $q_sks  = ($koneksi->query("SELECT COUNT(*) as jml FROM tb_bukti_sks WHERE status = 'Pending' AND tgl_sks BETWEEN '$start_date' AND '$end_date'")->fetch());

    $total_admin = $q_izin['jml'] + $q_cuti['jml'] + $q_sks['jml'];

    if ($total_admin > 0) {
        $show_notif = true;
        $response['title'] = "Antrean Validasi Kehadiran";

        $periode_str = date('d M', strtotime($start_date)) . " s/d " . date('d M Y');

        $msg = "<p style='text-align: left; color: #566a7f; margin-bottom: 10px;'>Daftar pengajuan periode <b>{$periode_str}</b> yang belum diproses:</p>";
        $msg .= "<div style='background: #f8f9fa; border-left: 4px solid #696cff; padding: 12px 15px; border-radius: 6px; text-align: left; color: #566a7f;'>";

        if ($q_cuti['jml'] > 0) {
            $msg .= "<div style='margin-bottom: 8px;'><i class='bx bx-calendar-event text-info' style='vertical-align: middle; font-size: 1.2rem; margin-right: 5px;'></i> <span style='vertical-align: middle;'><b>{$q_cuti['jml']}</b> Pengajuan Cuti</span></div>";
        }
        if ($q_izin['jml'] > 0) {
            $msg .= "<div style='margin-bottom: 8px;'><i class='bx bx-message-square-edit text-warning' style='vertical-align: middle; font-size: 1.2rem; margin-right: 5px;'></i> <span style='vertical-align: middle;'><b>{$q_izin['jml']}</b> Pengajuan Izin</span></div>";
        }
        if ($q_sks['jml'] > 0) {
            $msg .= "<div style='margin-bottom: 0;'><i class='bx bx-plus-medical text-danger' style='vertical-align: middle; font-size: 1.2rem; margin-right: 5px;'></i> <span style='vertical-align: middle;'><b>{$q_sks['jml']}</b> Bukti SKS/Sakit</span></div>";
        }

        $msg .= "</div>";
        $response['msg'] = $msg;
    }
}
// 3. Safety (Level 4) -> Notif APD Pending
elseif ($user_level == 4) {
    $q_apd = $koneksi->query("SELECT COUNT(*) as jml FROM tb_pengajuan_apd WHERE status_pengajuan = 'pending'");
    $cek_apd = $q_apd->fetch();

    if ($cek_apd['jml'] > 0) {
        $show_notif = true;
        $response['title'] = "Validasi APD Baru!";
        $response['msg']   = "
            <div style='background: #e7e7ff; border-left: 4px solid #696cff; padding: 15px; border-radius: 6px; text-align: left; color: #566a7f;'>
                Terdapat <b class='text-primary' style='font-size: 1.2rem;'>{$cek_apd['jml']}</b> pengajuan APD baru yang berstatus <b>PENDING</b>.
            </div>
            <p style='margin-top: 15px; text-align: left; color: #697a8d; font-size: 0.9rem;'>
                Harap segera lakukan pengecekan dan validasi pengajuan.
            </p>
        ";
    }
}

if ($show_notif) {
    $response['show'] = true;
}

echo json_encode($response);
exit;
