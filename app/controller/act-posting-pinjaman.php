<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../config/koneksi.php';

$bulan = $_POST['bulan'] ?? ''; // Format: YYYY-MM dari filter frontend

if (empty($bulan)) {
    echo json_encode(['status' => 'error', 'message' => 'Bulan tidak valid!']);
    exit;
}

try {
    // 1. Ambil ID Akun 1301 dan 1001 (Piutang Pinjaman Karyawan & Kas)
    $qAkun = $koneksi->query("SELECT id_kategori FROM tb_kategori_keuangan WHERE kode_akun = '1301' LIMIT 1");
    $rowAkun = $qAkun->fetch();
    $id_piutang = $rowAkun['id_kategori'] ?? null;

    $qKas = $koneksi->query("SELECT id_kategori FROM tb_kategori_keuangan WHERE kode_akun = '1001' LIMIT 1");
    $rowKas = $qKas->fetch();
    $id_kas = $rowKas['id_kategori'] ?? null;

    if (!$id_piutang) {
        throw new Exception("Akun Piutang Pinjaman Karyawan (1301) belum diatur di master kategori.");
    }

    $koneksi->beginTransaction();

    // 2. Bersihkan data postingan transaksi (Debit dan Kredit) LAMA di bulan ini (Mencegah Duplikat saat Posting Ulang)
    $delLike = 'Auto-Posting Pencairan Pinjaman:%';
    $del = $koneksi->prepare("DELETE FROM tb_transaksi_keuangan
             WHERE keterangan LIKE ?
             AND DATE_FORMAT(tanggal_transaksi, '%Y-%m') = ?");
    $del->execute([$delLike, $bulan]);

    // 3. Ambil total pinjaman per hari di bulan tersebut
    $stPin = $koneksi->prepare("SELECT tgl_pinjaman, SUM(nominal_pinjaman) as total_per_hari
                    FROM tb_pinjaman
                    WHERE DATE_FORMAT(tgl_pinjaman, '%Y-%m') = ?
                    GROUP BY tgl_pinjaman");
    $stPin->execute([$bulan]);

    $total_terposting = 0;
    $count_hari = 0;

    while ($row = $stPin->fetch()) {
        $tgl = $row['tgl_pinjaman'];
        $total_harian = (float) $row['total_per_hari'];

        if ($total_harian > 0) {
            $ket = "Auto-Posting Pencairan Pinjaman: Tanggal " . date('d/m/Y', strtotime($tgl));

            // A. DEBIT: Piutang Pinjaman Karyawan Bertambah (1301)
            $stmt1 = $koneksi->prepare("INSERT INTO tb_transaksi_keuangan (tanggal_transaksi, id_kategori, nominal, tipe_arus, tipe_neraca, keterangan) VALUES (?, ?, ?, 'Keluar', 'debit', ?)");
            $stmt1->execute([$tgl, $id_piutang, $total_harian, $ket]);

            // B. KREDIT: Kas Berkurang (1001)
            if ($id_kas) {
                $stmt2 = $koneksi->prepare("INSERT INTO tb_transaksi_keuangan (tanggal_transaksi, id_kategori, nominal, tipe_arus, tipe_neraca, keterangan) VALUES (?, ?, ?, 'Keluar', 'kredit', ?)");
                $stmt2->execute([$tgl, $id_kas, $total_harian, $ket]);
            }

            $total_terposting += $total_harian;
            $count_hari++;
        }
    }

    $koneksi->commit();

    if ($total_terposting > 0) {
        echo json_encode([
            'status' => 'success',
            'message' => "Berhasil memposting total Rp " . number_format($total_terposting, 0, ',', '.') . " dari $count_hari tanggal pencairan."
        ]);
    } else {
        echo json_encode([
            'status' => 'warning',
            'message' => 'Tidak ada data pencairan pinjaman di bulan ini.'
        ]);
    }
} catch (PDOException $e) {
    if ($koneksi->inTransaction()) $koneksi->rollBack();
    error_log($e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Gagal memposting data.']);
} catch (Exception $e) {
    if ($koneksi->inTransaction()) $koneksi->rollBack();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
