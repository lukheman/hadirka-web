<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Content-Type: application/json");

require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/function.php';
require_once __DIR__ . '/../config/nomor_surat.php';

$action = $_POST['action'] ?? '';
$response = [];

// KOREKSI: Fungsi pintar floatOrZero untuk membersihkan titik ribuan & mengubah koma desimal
function floatOrZero($val)
{
    if (!isset($val) || $val === '') return 0;

    // 1. Hilangkan titik ribuan terlebih dahulu agar tidak merusak is_numeric PHP
    $val = str_replace('.', '', $val);

    // 2. Konversi koma desimal menjadi titik standar PHP/Database
    $val = str_replace(',', '.', $val);

    return is_numeric($val) ? floatval($val) : 0;
}

// Valid tanggal
function validDate($date)
{
    return preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) === 1;
}

try {

    if (!in_array($action, ['add', 'edit', 'delete', 'extend_single', 'extend_batch'])) {
        throw new Exception("Action tidak valid");
    }

    // EXTEND SINGLE (Per Karyawan)
    if ($action === 'extend_single') {
        $id_karier = intval($_POST['id_karier'] ?? 0);
        if ($id_karier <= 0) throw new Exception("ID karier tidak valid");

        // Cek status karyawan
        $cek = $koneksi->prepare("
            SELECT kr.status_karyawan, k.tgl_selesai
            FROM tb_karier k
            INNER JOIN tb_karyawan kr ON k.id_karyawan = kr.id_karyawan
            WHERE k.id_karier = ?
        ");
        $cek->execute([$id_karier]);

        $row = $cek->fetch();
        if (!$row) throw new Exception("Data karier tidak ditemukan.");

        if (strtolower($row['status_karyawan']) !== 'aktif') {
            throw new Exception("Ditolak: Hanya karyawan dengan status Aktif yang bisa diperpanjang otomatis.");
        }
        if (empty($row['tgl_selesai']) || $row['tgl_selesai'] == '0000-00-00') {
            throw new Exception("Tanggal selesai kosong, tidak bisa diperpanjang otomatis.");
        }

        // Tambah 6 Bulan
        $upd = $koneksi->prepare("UPDATE tb_karier SET tgl_selesai = DATE_ADD(tgl_selesai, INTERVAL 6 MONTH) WHERE id_karier = ?");
        $upd->execute([$id_karier]);
        $response = ['status' => 'success', 'msg' => 'Kontrak berhasil diperpanjang 6 bulan.'];
    }

    // EXTEND BATCH (Massal)
    elseif ($action === 'extend_batch') {
        $ids = $_POST['id_karier'] ?? [];
        if (empty($ids) || !is_array($ids)) {
            throw new Exception("Tidak ada karyawan yang dipilih.");
        }

        // Sanitasi array ID
        $ids = array_map('intval', $ids);
        $ids_str = implode(',', $ids);

        // Update masal HANYA untuk status aktif dan tgl_selesai tidak null
        $sql = "UPDATE tb_karier k
                INNER JOIN tb_karyawan kr ON k.id_karyawan = kr.id_karyawan
                SET k.tgl_selesai = DATE_ADD(k.tgl_selesai, INTERVAL 6 MONTH)
                WHERE k.id_karier IN ($ids_str)
                AND LOWER(kr.status_karyawan) = 'aktif'
                AND k.tgl_selesai IS NOT NULL";

        try {
            $st = $koneksi->query($sql);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            throw new Exception("Gagal melakukan perpanjangan massal.");
        }

        $affected = $st->rowCount();
        if ($affected > 0) {
            $response = ['status' => 'success', 'msg' => "$affected kontrak karyawan aktif berhasil diperpanjang 6 bulan."];
        } else {
            $response = ['status' => 'error', 'msg' => "Tidak ada data yang diperbarui (pastikan karyawan yang dicentang berstatus Aktif)."];
        }
    }

    // DELETE
    elseif ($action === 'delete') {
        $id_karier = intval($_POST['id_karier']);

        if ($id_karier <= 0)
            throw new Exception("ID karier tidak valid");

        $stmt = $koneksi->prepare("DELETE FROM tb_karier WHERE id_karier=?");
        $stmt->execute([$id_karier]);

        $response = ['status' => 'success'];
    }

    // ADD / EDIT
    else {

        $id_karier     = intval($_POST['id_karier'] ?? 0);
        // --- SMART DECRYPT ---
        $raw_id_karyawan = $_POST['id_karyawan'] ?? '';
        if (is_numeric($raw_id_karyawan)) {
            // Jika berupa angka, berarti dikirim dari import-karier.php (master)
            $id_karyawan = intval($raw_id_karyawan);
        } else {
            // Jika berupa string acak, berarti dikirim dari data-karier.php (encrypted)
            $id_karyawan = !empty($raw_id_karyawan) ? intval(decrypt($raw_id_karyawan)) : 0;
        }
        // ---------------------

        $id_site       = intval($_POST['id_site']);
        $id_departemen = !empty($_POST['id_departemen']) ? intval($_POST['id_departemen']) : null;
        $id_jabatan    = intval($_POST['id_jabatan']);

        $gaji_pokok  = floatOrZero($_POST['gaji_pokok']);
        // Kolom lembur/pot_izin/pot_mangkir dihapus dari DB (penyederhanaan gaji Okt 2026).

        $tgl_mulai   = $_POST['tgl_mulai'];
        $tgl_selesai = !empty($_POST['tgl_selesai']) ? $_POST['tgl_selesai'] : null;

        if (!validDate($tgl_mulai))
            throw new Exception("Tanggal mulai tidak valid");

        // ADD
        if ($action === 'add') {

            // --- TAMBAHKAN VALIDASI STATUS DISINI ---
            $cekStatus = $koneksi->prepare("SELECT status_karyawan FROM tb_karyawan WHERE id_karyawan = ?");
            $cekStatus->execute([$id_karyawan]);
            $statusRow = $cekStatus->fetch();
            if ($statusRow) {
                $status = $statusRow['status_karyawan'];
                // Sesuaikan kata 'Resign' dengan value yang ada di databasemu
                if (strtolower($status) === 'resign' || strtolower($status) === 'tidak aktif') {
                    throw new Exception("Ditolak: Karyawan ini sudah berstatus Resign. Sesuai aturan, tidak bisa masuk kembali.");
                }
            }
            // ----------------------------------------

            // Nomor surat: bila Nomor Urut manual (4 digit) diisi, susun
            // TPJS-KONTRAK-{kode_jabatan}/{urut}/{tahun tgl_mulai} tanpa
            // mengurangi counter; selain itu nomor dibuat otomatis.
            // Gagal bila jabatan belum punya kode_jabatan.
            $no_urut_manual = substr(preg_replace('/\D/', '', (string)($_POST['no_urut'] ?? '')), 0, 4);
            if ($no_urut_manual !== '') {
                $qKode = $koneksi->prepare("SELECT kode_jabatan FROM tb_jabatan WHERE id_jabatan = ? LIMIT 1");
                $qKode->execute([$id_jabatan]);
                $kodeRow = $qKode->fetch();
                $kode_jabatan = $kodeRow ? trim((string)($kodeRow['kode_jabatan'] ?? '')) : '';
                if ($kode_jabatan === '') throw new Exception('Kode jabatan belum diisi. Lengkapi kode jabatan terlebih dahulu.');
                $no_surat = sprintf('TPJS-KONTRAK-%s/%04d/%s', $kode_jabatan, (int)$no_urut_manual, substr($tgl_mulai, 0, 4));
                $cekNo = $koneksi->prepare("SELECT id_karier FROM tb_karier WHERE no_surat = ? LIMIT 1");
                $cekNo->execute([$no_surat]);
                if ($cekNo->fetch()) throw new Exception("Nomor surat sudah dipakai, gunakan nomor urut lain.");
            } else {
                $qKode = $koneksi->prepare("SELECT kode_jabatan FROM tb_jabatan WHERE id_jabatan = ? LIMIT 1");
                $qKode->execute([$id_jabatan]);
                $kodeRow = $qKode->fetch();
                $kode_jabatan = $kodeRow ? ($kodeRow['kode_jabatan'] ?? '') : '';
                $no_surat = generate_no_surat($koneksi, $kode_jabatan, $tgl_mulai);
            }

            $sql = "INSERT INTO tb_karier (id_karyawan,id_site,id_departemen,id_jabatan,gaji_pokok,tgl_mulai,tgl_selesai,no_surat) VALUES (?,?,?,?,?,?,?,?)";
            $stmt = $koneksi->prepare($sql);
            try {
                $stmt->execute([$id_karyawan, $id_site, $id_departemen, $id_jabatan, $gaji_pokok, $tgl_mulai, $tgl_selesai, $no_surat]);
            } catch (PDOException $e) {
                error_log($e->getMessage());
                if (($e->errorInfo[1] ?? null) == 1062) throw new Exception("Nomor surat ganda, silakan ulangi simpan.");
                throw new Exception("Gagal menyimpan data karier.");
            }

            // Tabel tunjangan/potongan dihapus dari DB (penyederhanaan gaji Okt 2026):
            // tidak ada lagi penyalinan tunjangan/potongan dari tab maupun template.
            $response = ['status' => 'success', 'no_surat' => $no_surat, 'tunjangan' => 0, 'potongan' => 0];
        }

        // EDIT (nomor surat bisa dikoreksi manual; kosong = tanpa nomor)
        elseif ($action === 'edit') {
            $no_surat_manual = trim($_POST['no_surat'] ?? '');
            if (strlen($no_surat_manual) > 100) throw new Exception("Nomor surat maksimal 100 karakter");
            $no_surat_edit = $no_surat_manual !== '' ? $no_surat_manual : null;
            if ($no_surat_edit !== null) {
                $cekNo = $koneksi->prepare("SELECT id_karier FROM tb_karier WHERE no_surat = ? AND id_karier <> ? LIMIT 1");
                $cekNo->execute([$no_surat_edit, $id_karier]);
                if ($cekNo->fetch()) throw new Exception("Nomor surat sudah dipakai karier lain.");
            }
            try {
                if ($tgl_selesai === null) {
                    $sql = "UPDATE tb_karier SET id_site=?, id_departemen=?, id_jabatan=?, gaji_pokok=?, tgl_mulai=?, tgl_selesai=NULL, no_surat=? WHERE id_karier=?";
                    $stmt = $koneksi->prepare($sql);
                    $stmt->execute([$id_site, $id_departemen, $id_jabatan, $gaji_pokok, $tgl_mulai, $no_surat_edit, $id_karier]);
                } else {
                    $sql = "UPDATE tb_karier SET id_site=?, id_departemen=?, id_jabatan=?, gaji_pokok=?, tgl_mulai=?, tgl_selesai=?, no_surat=? WHERE id_karier=?";
                    $stmt = $koneksi->prepare($sql);
                    $stmt->execute([$id_site, $id_departemen, $id_jabatan, $gaji_pokok, $tgl_mulai, $tgl_selesai, $no_surat_edit, $id_karier]);
                }
            } catch (PDOException $e) {
                error_log($e->getMessage());
                throw new Exception("Gagal memperbarui data karier.");
            }

            $response = ['status' => 'success'];
        }
    }
} catch (PDOException $e) {
    error_log($e->getMessage());
    $response = ['status' => 'error', 'msg' => 'Terjadi kesalahan database.'];
} catch (Exception $e) {
    $response = ['status' => 'error', 'msg' => $e->getMessage()];
}

echo json_encode($response);
