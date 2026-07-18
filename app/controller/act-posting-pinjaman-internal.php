<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../config/koneksi.php';

// Menangkap range tanggal dari UI
$tgl_awal = $_POST['tgl_awal'] ?? '';
$tgl_akhir = $_POST['tgl_akhir'] ?? '';

if (empty($tgl_awal) || empty($tgl_akhir)) {
    echo json_encode(['status' => 'error', 'message' => 'Range tanggal tidak valid!']);
    exit;
}

try {
    // 1. Ambil ID Akun 1301 dan 1001 (Piutang Pinjaman Karyawan & Kas)
    $qAkun = mysqli_query($koneksi, "SELECT id_kategori FROM tb_kategori_keuangan WHERE kode_akun = '1301' LIMIT 1");
    $id_piutang = mysqli_fetch_assoc($qAkun)['id_kategori'] ?? null;

    $qKas = mysqli_query($koneksi, "SELECT id_kategori FROM tb_kategori_keuangan WHERE kode_akun = '1001' LIMIT 1");
    $id_kas = mysqli_fetch_assoc($qKas)['id_kategori'] ?? null;

    if (!$id_piutang || !$id_kas) {
        throw new Exception("Akun Piutang Pinjaman Karyawan (1301) atau Kas (1001) belum diatur di master kategori.");
    }

    mysqli_begin_transaction($koneksi);

    // 2. Bersihkan data postingan LAMA di range tanggal ini (Mencegah Duplikat saat Re-Posting)
    // Kata "Internal" ditambahkan pada keterangan agar tidak bentrok/menghapus postingan pinjaman karyawan reguler
    $delQuery = "DELETE FROM tb_transaksi_keuangan 
                 WHERE keterangan LIKE 'Auto-Posting Pencairan Pinjaman Internal:%' 
                 AND tanggal_transaksi BETWEEN '$tgl_awal' AND '$tgl_akhir'";
    mysqli_query($koneksi, $delQuery);

    // 3. Ambil total pinjaman per hari di rentang tanggal tersebut
    // Karena tabel menyimpan data per cicilan, SUM() nominal berdasarkan tgl_pinjaman (tgl pencairan) akan mendapatkan total PLAFON per hari
    $sqlPinjaman = "SELECT tgl_pinjaman, SUM(nominal_pinjaman) as total_per_hari 
                    FROM tb_pinjaman_internal 
                    WHERE tgl_pinjaman BETWEEN '$tgl_awal' AND '$tgl_akhir' 
                    GROUP BY tgl_pinjaman";
    $qPinjaman = mysqli_query($koneksi, $sqlPinjaman);

    $total_terposting = 0;
    $count_hari = 0;

    while ($row = mysqli_fetch_assoc($qPinjaman)) {
        $tgl = $row['tgl_pinjaman'];
        $total_harian = (float) $row['total_per_hari'];

        if ($total_harian > 0) {
            $ket = "Auto-Posting Pencairan Pinjaman Internal: Tanggal " . date('d/m/Y', strtotime($tgl));

            // A. DEBIT: Piutang Pinjaman Karyawan Bertambah (1301) - Tipe Arus Keluar & Neraca Debit
            $stmt1 = $koneksi->prepare("INSERT INTO tb_transaksi_keuangan (tanggal_transaksi, id_kategori, nominal, tipe_arus, tipe_neraca, keterangan) VALUES (?, ?, ?, 'Keluar', 'debit', ?)");
            $stmt1->bind_param("sids", $tgl, $id_piutang, $total_harian, $ket);
            $stmt1->execute();
            $stmt1->close();

            // B. KREDIT: Kas Berkurang (1001) - Tipe Arus Keluar & Neraca Kredit
            $stmt2 = $koneksi->prepare("INSERT INTO tb_transaksi_keuangan (tanggal_transaksi, id_kategori, nominal, tipe_arus, tipe_neraca, keterangan) VALUES (?, ?, ?, 'Keluar', 'kredit', ?)");
            $stmt2->bind_param("sids", $tgl, $id_kas, $total_harian, $ket);
            $stmt2->execute();
            $stmt2->close();

            $total_terposting += $total_harian;
            $count_hari++;
        }
    }

    mysqli_commit($koneksi);

    if ($total_terposting > 0) {
        echo json_encode([
            'status' => 'success',
            'message' => "Berhasil memposting total pencairan Rp " . number_format($total_terposting, 0, ',', '.') . " dari $count_hari tanggal pencairan berbeda."
        ]);
    } else {
        echo json_encode([
            'status' => 'warning',
            'message' => 'Tidak ada data pencairan pinjaman internal pada rentang tanggal tersebut.'
        ]);
    }
} catch (Exception $e) {
    mysqli_rollback($koneksi);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}