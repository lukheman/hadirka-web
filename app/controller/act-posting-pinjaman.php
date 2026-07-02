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
    $qAkun = mysqli_query($koneksi, "SELECT id_kategori FROM tb_kategori_keuangan WHERE kode_akun = '1301' LIMIT 1");
    $id_piutang = mysqli_fetch_assoc($qAkun)['id_kategori'] ?? null;

    $qKas = mysqli_query($koneksi, "SELECT id_kategori FROM tb_kategori_keuangan WHERE kode_akun = '1001' LIMIT 1");
    $id_kas = mysqli_fetch_assoc($qKas)['id_kategori'] ?? null;

    if (!$id_piutang) {
        throw new Exception("Akun Piutang Pinjaman Karyawan (1301) belum diatur di master kategori.");
    }

    mysqli_begin_transaction($koneksi);

    // 2. Bersihkan data postingan transaksi (Debit dan Kredit) LAMA di bulan ini (Mencegah Duplikat saat Posting Ulang)
    $delQuery = "DELETE FROM tb_transaksi_keuangan 
             WHERE keterangan LIKE 'Auto-Posting Pencairan Pinjaman:%' 
             AND DATE_FORMAT(tanggal_transaksi, '%Y-%m') = '$bulan'";
    mysqli_query($koneksi, $delQuery);

    // 3. Ambil total pinjaman per hari di bulan tersebut
    $sqlPinjaman = "SELECT tgl_pinjaman, SUM(nominal_pinjaman) as total_per_hari 
                    FROM tb_pinjaman 
                    WHERE DATE_FORMAT(tgl_pinjaman, '%Y-%m') = '$bulan' 
                    GROUP BY tgl_pinjaman";
    $qPinjaman = mysqli_query($koneksi, $sqlPinjaman);

    $total_terposting = 0;
    $count_hari = 0;

    while ($row = mysqli_fetch_assoc($qPinjaman)) {
        $tgl = $row['tgl_pinjaman'];
        $total_harian = (float) $row['total_per_hari'];

        if ($total_harian > 0) {
            $ket = "Auto-Posting Pencairan Pinjaman: Tanggal " . date('d/m/Y', strtotime($tgl));

            // A. DEBIT: Piutang Pinjaman Karyawan Bertambah (1301)
            $stmt1 = $koneksi->prepare("INSERT INTO tb_transaksi_keuangan (tanggal_transaksi, id_kategori, nominal, tipe_arus, tipe_neraca, keterangan) VALUES (?, ?, ?, 'Keluar', 'debit', ?)");
            $stmt1->bind_param("sids", $tgl, $id_piutang, $total_harian, $ket);
            $stmt1->execute();
            $stmt1->close();

            // B. KREDIT: Kas Berkurang (1001)
            if ($id_kas) {
                $stmt2 = $koneksi->prepare("INSERT INTO tb_transaksi_keuangan (tanggal_transaksi, id_kategori, nominal, tipe_arus, tipe_neraca, keterangan) VALUES (?, ?, ?, 'Keluar', 'kredit', ?)");
                $stmt2->bind_param("sids", $tgl, $id_kas, $total_harian, $ket);
                $stmt2->execute();
                $stmt2->close();
            }

            $total_terposting += $total_harian;
            $count_hari++;
        }
    }

    mysqli_commit($koneksi);

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
} catch (Exception $e) {
    mysqli_rollback($koneksi);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
