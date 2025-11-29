<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/function.php';

$action = $_POST['action'] ?? '';

switch($action){
    case 'add':
        $id_karyawan = $_POST['id_karyawan'] ?? '';
        $tgl_pinjaman = $_POST['tgl_pinjaman'] ?? '';
        $tgl_bayar = $_POST['tgl_bayar_pinjaman'] ?? null;
        $nominal = $_POST['nominal_pinjaman'] ?? 0;
        $ket = $_POST['ket_pinjaman'] ?? '';

        if(!$id_karyawan || !$tgl_pinjaman || !$nominal){
            echo json_encode(['status'=>'error','msg'=>'Field wajib diisi']); exit;
        }

        $stmt = $koneksi->prepare("INSERT INTO tb_pinjaman(id_karyawan,tgl_pinjaman,tgl_bayar_pinjaman,nominal_pinjaman,ket_pinjaman) VALUES(?,?,?,?,?)");
        $stmt->bind_param("issds",$id_karyawan,$tgl_pinjaman,$tgl_bayar,$nominal,$ket);
        echo $stmt->execute() ? json_encode(['status'=>'success']) : json_encode(['status'=>'error','msg'=>$stmt->error]);
        $stmt->close();
    break;

    case 'edit':
        $id = $_POST['id_pinjaman'] ?? '';
        $id_karyawan = $_POST['id_karyawan'] ?? '';
        $tgl_pinjaman = $_POST['tgl_pinjaman'] ?? '';
        $tgl_bayar = $_POST['tgl_bayar_pinjaman'] ?? null;
        $nominal = $_POST['nominal_pinjaman'] ?? 0;
        $ket = $_POST['ket_pinjaman'] ?? '';

        if(!$id || !$id_karyawan || !$tgl_pinjaman || !$nominal){
            echo json_encode(['status'=>'error','msg'=>'Field wajib diisi']); exit;
        }

        $stmt = $koneksi->prepare("UPDATE tb_pinjaman SET id_karyawan=?, tgl_pinjaman=?, tgl_bayar_pinjaman=?, nominal_pinjaman=?, ket_pinjaman=? WHERE id_pinjaman=?");
        $stmt->bind_param("issdsi",$id_karyawan,$tgl_pinjaman,$tgl_bayar,$nominal,$ket,$id);
        echo $stmt->execute() ? json_encode(['status'=>'success']) : json_encode(['status'=>'error','msg'=>$stmt->error]);
        $stmt->close();
    break;

    case 'delete':
        $id = $_POST['id_pinjaman'] ?? '';
        if(!$id){ echo json_encode(['status'=>'error','msg'=>'ID tidak ditemukan']); exit;}
        $stmt = $koneksi->prepare("DELETE FROM tb_pinjaman WHERE id_pinjaman=?");
        $stmt->bind_param("i",$id);
        echo $stmt->execute() ? json_encode(['status'=>'success']) : json_encode(['status'=>'error','msg'=>$stmt->error]);
        $stmt->close();
    break;

    default:
        echo json_encode(['status'=>'error','msg'=>'Action tidak valid']);
    break;
}
