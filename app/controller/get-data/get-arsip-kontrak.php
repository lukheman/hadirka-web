<?php
declare(strict_types=1);
ini_set('display_errors', '0');
require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../../config/kontrak_schema.php';
ensure_kontrak_schema($koneksi);

if (($_GET['action'] ?? '') === 'list') {
    header('Content-Type: application/json; charset=utf-8');
    $st = $koneksi->prepare('SELECT id_arsip,file,nama_asli,ukuran,keterangan,created_at FROM tb_arsip_kontrak WHERE id_karyawan = ? ORDER BY created_at DESC,id_arsip DESC');
    $st->execute([(int)($_GET['id_karyawan'] ?? 0)]);
    echo json_encode(['status' => 'success', 'data' => $st->fetchAll()]);
    exit;
}

$sql = 'SELECT k.id_karyawan,k.nik_karyawan,k.nama_lengkap,k.status_karyawan,s.nama_site,j.nama_jabatan,
 (SELECT COUNT(*) FROM tb_arsip_kontrak a WHERE a.id_karyawan=k.id_karyawan) jml_arsip,
 (SELECT a.file FROM tb_arsip_kontrak a WHERE a.id_karyawan=k.id_karyawan ORDER BY a.created_at DESC,a.id_arsip DESC LIMIT 1) file_terbaru,
 (SELECT a.created_at FROM tb_arsip_kontrak a WHERE a.id_karyawan=k.id_karyawan ORDER BY a.created_at DESC,a.id_arsip DESC LIMIT 1) tgl_terbaru
 FROM tb_karyawan k LEFT JOIN tb_karier kr ON kr.id_karier=(SELECT x.id_karier FROM tb_karier x WHERE x.id_karyawan=k.id_karyawan ORDER BY x.tgl_mulai DESC,x.id_karier DESC LIMIT 1)
 LEFT JOIN tb_site s ON s.id_site=kr.id_site LEFT JOIN tb_jabatan j ON j.id_jabatan=kr.id_jabatan WHERE 1=1';
$params = [];
if (($status = trim((string)($_GET['status'] ?? ''))) !== '') { $sql .= ' AND k.status_karyawan=?'; $params[] = $status; }
if (($site = (int)($_GET['site'] ?? 0)) > 0) { $sql .= ' AND kr.id_site=?'; $params[] = $site; }
if (($q = trim((string)($_GET['q'] ?? ''))) !== '') { $sql .= ' AND (k.nik_karyawan LIKE ? OR k.nama_lengkap LIKE ?)'; $params[] = "%$q%"; $params[] = "%$q%"; }
$sql .= ' ORDER BY k.nama_lengkap';
$st = $koneksi->prepare($sql); $st->execute($params);
header('Content-Type: text/html; charset=utf-8');
foreach ($st->fetchAll() as $i => $r) {
    $id=(int)$r['id_karyawan']; $nama=htmlspecialchars((string)$r['nama_lengkap'], ENT_QUOTES, 'UTF-8');
    $file=$r['file_terbaru'] ? '<a target="_blank" href="app/uploads/arsip-kontrak/'.rawurlencode(basename((string)$r['file_terbaru'])).'" class="btn btn-sm btn-success"><i class="fa fa-file-pdf"></i></a>' : '<span class="text-muted">Belum ada</span>';
    $dokumen = $r['jml_arsip']
        ? '<span class="badge bg-primary">'.$r['jml_arsip'].' dokumen</span><br><small class="text-muted">'.htmlspecialchars((string)($r['tgl_terbaru'] ?? '-'), ENT_QUOTES, 'UTF-8').'</small>'
        : '<span class="badge bg-warning text-dark">Belum ada</span>';
    echo '<tr><td>'.($i+1).'</td><td>'.htmlspecialchars((string)$r['nik_karyawan']).'</td><td class="text-start">'.$nama.'</td><td>'.htmlspecialchars((string)($r['nama_site']??'-')).'</td><td>'.htmlspecialchars((string)($r['nama_jabatan']??'-')).'</td><td>'.htmlspecialchars((string)($r['status_karyawan']??'-')).'</td><td>'.$dokumen.'</td><td>'.$file.' <button type="button" class="btn btn-sm btn-primary btnUploadArsip" data-id="'.$id.'" data-nama="'.$nama.'"><i class="fa fa-upload"></i></button> <button type="button" class="btn btn-sm btn-info btnRiwayatArsip" data-id="'.$id.'" data-nama="'.$nama.'"><i class="fa fa-list"></i></button></td></tr>';
}
