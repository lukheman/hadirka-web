<?php
require __DIR__.'/../config/koneksi.php';
header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

switch($action){
    case 'add':
        $id_k = $_POST['id_karyawan'] ?? '';
        $tgl = $_POST['tgl_rapel'] ?? '';
        $nominal = $_POST['nominal_rapel'] ?? 0;
        $ket = $_POST['ket_rapel'] ?? '';

        if(!$id_k || !$tgl || !$nominal){
            echo json_encode(['status'=>'error','msg'=>'Field wajib diisi']); exit;
        }

        $stmt = $koneksi->prepare("INSERT INTO tb_rapel(id_karyawan,tgl_rapel,nominal_rapel,ket_rapel) VALUES(?,?,?,?)");
        $stmt->bind_param("isds",$id_k,$tgl,$nominal,$ket);
        echo $stmt->execute()?json_encode(['status'=>'success']):json_encode(['status'=>'error','msg'=>$stmt->error]);
        $stmt->close();
    break;

    case 'edit':
        $id = $_POST['id_rapel'] ?? '';
        $id_k = $_POST['id_karyawan'] ?? '';
        $tgl = $_POST['tgl_rapel'] ?? '';
        $nominal = $_POST['nominal_rapel'] ?? 0;
        $ket = $_POST['ket_rapel'] ?? '';

        if(!$id || !$id_k || !$tgl || !$nominal){
            echo json_encode(['status'=>'error','msg'=>'Field wajib diisi']); exit;
        }

        $stmt = $koneksi->prepare("UPDATE tb_rapel SET id_karyawan=?, tgl_rapel=?, nominal_rapel=?, ket_rapel=? WHERE id_rapel=?");
        $stmt->bind_param("isdsi",$id_k,$tgl,$nominal,$ket,$id);
        echo $stmt->execute()?json_encode(['status'=>'success']):json_encode(['status'=>'error','msg'=>$stmt->error]);
        $stmt->close();
    break;

    case 'delete':
        $id = $_POST['id_rapel'] ?? '';
        if(!$id){ echo json_encode(['status'=>'error','msg'=>'ID tidak ditemukan']); exit; }
        $stmt = $koneksi->prepare("DELETE FROM tb_rapel WHERE id_rapel=?");
        $stmt->bind_param("i",$id);
        echo $stmt->execute()?json_encode(['status'=>'success']):json_encode(['status'=>'error','msg'=>$stmt->error]);
        $stmt->close();
    break;

    default:
        echo json_encode(['status'=>'error','msg'=>'Action tidak valid']);
    break;
}
