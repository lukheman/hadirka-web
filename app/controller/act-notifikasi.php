<?php
require_once __DIR__ . '/../config/koneksi.php';
header("Content-Type: application/json; charset=UTF-8");

// Cegah notice agar JSON tidak rusak
error_reporting(E_ERROR | E_PARSE);

$action = $_POST['action'] ?? $_GET['action'] ?? '';

/**
 * Whitelist kategori notifikasi aplikasi.
 */
function kategori_notif_valid($k)
{
    $allowed = ['presensi', 'penggajian', 'pengajuan', 'pengumuman'];
    return in_array($k, $allowed, true) ? $k : 'pengumuman';
}

switch ($action) {

    // --- KIRIM NOTIFIKASI (MASSAL / PER SITE / PER KARYAWAN) ---
    case "save":
        $judul = trim($_POST['judul'] ?? '');
        $pesan = trim($_POST['pesan'] ?? '');
        $kategori = kategori_notif_valid($_POST['kategori'] ?? '');
        $target = $_POST['target'] ?? 'semua';

        if ($judul === '' || $pesan === '') {
            echo json_encode(["status" => "error", "msg" => "Judul & pesan wajib diisi."]);
            exit;
        }

        try {
            $jumlah = 0;

            if ($target === 'site') {
                $id_site = (int)($_POST['id_site'] ?? 0);
                if ($id_site <= 0) {
                    echo json_encode(["status" => "error", "msg" => "Pilih site tujuan terlebih dahulu."]);
                    exit;
                }

                // Kirim ke semua karyawan Aktif yang kariernya di site tsb.
                $st = $koneksi->prepare("INSERT INTO tb_notifikasi (id_karyawan, judul, pesan, kategori)
                    SELECT k.id_karyawan, ?, ?, ?
                    FROM tb_karyawan k
                    JOIN tb_karier kr ON kr.id_karyawan = k.id_karyawan
                        AND (kr.tgl_selesai IS NULL OR kr.tgl_selesai >= CURDATE())
                    WHERE kr.id_site = ? AND k.status_karyawan = 'Aktif'
                    GROUP BY k.id_karyawan");
                $st->execute([$judul, $pesan, $kategori, $id_site]);
                $jumlah = $st->rowCount();
            } elseif ($target === 'karyawan') {
                $ids = $_POST['id_karyawan'] ?? [];
                if (!is_array($ids)) {
                    $ids = [$ids];
                }
                $ids = array_values(array_unique(array_map('intval', $ids)));
                $ids = array_values(array_filter($ids, fn ($v) => $v > 0));

                if (count($ids) === 0) {
                    echo json_encode(["status" => "error", "msg" => "Pilih minimal satu karyawan penerima."]);
                    exit;
                }

                $cek = $koneksi->prepare("SELECT COUNT(*) FROM tb_karyawan WHERE id_karyawan = ?");
                $ins = $koneksi->prepare("INSERT INTO tb_notifikasi (id_karyawan, judul, pesan, kategori) VALUES (?, ?, ?, ?)");

                foreach ($ids as $id_k) {
                    $cek->execute([$id_k]);
                    if (!$cek->fetchColumn()) {
                        continue;
                    }
                    $ins->execute([$id_k, $judul, $pesan, $kategori]);
                    $jumlah++;
                }
            } else {
                // Semua karyawan Aktif
                $st = $koneksi->prepare("INSERT INTO tb_notifikasi (id_karyawan, judul, pesan, kategori)
                    SELECT id_karyawan, ?, ?, ? FROM tb_karyawan WHERE status_karyawan = 'Aktif'");
                $st->execute([$judul, $pesan, $kategori]);
                $jumlah = $st->rowCount();
            }

            if ($jumlah <= 0) {
                echo json_encode(["status" => "error", "msg" => "Tidak ada karyawan yang cocok dengan target."]);
                exit;
            }

            echo json_encode(["status" => "success", "msg" => "Notifikasi terkirim ke $jumlah karyawan."]);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            echo json_encode(["status" => "error", "msg" => "Gagal mengirim notifikasi."]);
        }
        exit;

    // --- HAPUS SATU NOTIFIKASI ---
    case "delete":
        $id = (int)($_GET['id'] ?? 0);

        if ($id <= 0) {
            echo json_encode(["status" => "error", "msg" => "ID tidak valid."]);
            exit;
        }

        try {
            $st = $koneksi->prepare("DELETE FROM tb_notifikasi WHERE id_notifikasi = ?");
            $st->execute([$id]);

            if ($st->rowCount() > 0) {
                echo json_encode(["status" => "success", "msg" => "Notifikasi berhasil dihapus."]);
            } else {
                echo json_encode(["status" => "error", "msg" => "Notifikasi tidak ditemukan."]);
            }
        } catch (PDOException $e) {
            error_log($e->getMessage());
            echo json_encode(["status" => "error", "msg" => "Gagal menghapus notifikasi."]);
        }
        exit;
}

echo json_encode(["status" => "error", "msg" => "Action tidak valid"]);
