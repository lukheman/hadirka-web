<?php
error_reporting(0); 
header("Content-Type: application/json");

require_once __DIR__.'/../../config/koneksi.php'; 

$query = "
    SELECT c.*, j.nama_jabatan 
    FROM tb_calon_karyawan c
    LEFT JOIN tb_loker l ON c.id_loker = l.id_loker
    LEFT JOIN tb_jabatan j ON l.id_jabatan = j.id_jabatan
    ORDER BY c.id_calon DESC
";

$result = $koneksi->query($query);
$data = [];
$no = 1;

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $dir = "app/uploads/"; 
        
        // Mempercantik tombol download berkas
        $btnFile = function($file, $label, $color) use ($dir) {
            if(empty($file)) return "";
            return "<a href='{$dir}{$file}' target='_blank' class='btn btn-sm btn-outline-{$color} mb-2 me-2'>
                        <i class='bx bx-cloud-download me-1'></i> {$label}
                    </a>";
        };

        // Badge Status Seleksi
        $status_badge = "";
        if ($row['status_seleksi'] == 'Proses') {
            $status_badge = "<span class='badge bg-label-warning px-3 py-2 rounded-pill'>Proses</span>";
        } elseif ($row['status_seleksi'] == 'Diterima') {
            $status_badge = "<span class='badge bg-label-success px-3 py-2 rounded-pill'>Diterima</span>";
        } else {
            $status_badge = "<span class='badge bg-label-danger px-3 py-2 rounded-pill'>Ditolak</span>";
        }

        // Tampilan Detail (Row Child)
        $detail = "
        <div class='row g-4'>
            <div class='col-12'>
                <h6 class='text-primary border-bottom pb-2 mb-3'><i class='bx bx-user-pin fs-5 me-1'></i> Detail Informasi Pribadi</h6>
                <div class='row'>
                    <div class='col-md-6'>
                        <table class='table table-sm table-borderless info-table mb-0'>
                            <tr><td>NIK KTP</td><td>:</td><td>{$row['nik_ktp']}</td></tr>
                            <tr><td>Tempat, Tgl Lahir</td><td>:</td><td>{$row['tempat_lahir']}, " . date('d F Y', strtotime($row['tgl_lahir'])) . "</td></tr>
                            <tr><td>Alamat</td><td>:</td><td>{$row['alamat_ktp']}, {$row['desa']}, Kec. {$row['kecamatan']}, {$row['kabupaten']}, {$row['provinsi']}</td></tr>
                        </table>
                    </div>
                    <div class='col-md-6'>
                        <table class='table table-sm table-borderless info-table mb-0'>
                            <tr><td>Email</td><td>:</td><td>{$row['email_pribadi']}</td></tr>
                            <tr><td>No. Whatsapp</td><td>:</td><td>{$row['no_telp']}</td></tr>
                            <tr><td>Pendidikan</td><td>:</td><td>{$row['education_level']} - {$row['last_education']} ({$row['major']})</td></tr>
                        </table>
                    </div>
                </div>
            </div>

            <div class='col-12'>
                <h6 class='text-primary border-bottom pb-2 mb-3'><i class='bx bx-folder-open fs-5 me-1'></i> Lampiran Berkas</h6>
                <div class='document-list'>
                    " . $btnFile($row['file_cv'], 'Curriculum Vitae', 'primary') . "
                    " . $btnFile($row['file_ktp'], 'KTP', 'info') . "
                    " . $btnFile($row['file_kk'], 'Kartu Keluarga', 'info') . "
                    " . $btnFile($row['file_ijazah'], 'Ijazah Terakhir', 'success') . "
                    " . $btnFile($row['file_skck'], 'SKCK', 'warning') . "
                    " . $btnFile($row['file_ak1'], 'Kartu Kuning (AK-1)', 'secondary') . "
                    " . $btnFile($row['file_sim'], 'SIM', 'dark') . "
                    " . $btnFile($row['file_sio'], 'SIO', 'danger') . "
                </div>
            </div>
        </div>";

        // === PEMBUATAN TOMBOL AKSI DENGAN KONDISI ===
        
        // 1. Tombol Terima (Hilang jika status sudah 'Diterima')
        $btnAcc = "";
        if ($row['status_seleksi'] !== 'Diterima') {
            $btnAcc = "
                <button class='btn btn-icon btn-sm btn-success btnAccPelamar' data-id='{$row['id_calon']}' data-nama='{$row['nama_lengkap']}' title='Terima Pelamar'>
                    <i class='bx bx-check'></i>
                </button>";
        }

        // 2. Tombol Tolak (Hilang jika status sudah 'Ditolak')
        $btnTolak = "";
        if ($row['status_seleksi'] !== 'Ditolak') {
            $btnTolak = "
                <button class='btn btn-icon btn-sm btn-warning btnTolakPelamar' data-id='{$row['id_calon']}' data-nama='{$row['nama_lengkap']}' title='Tolak Pelamar'>
                    <i class='bx bx-x'></i>
                </button>";
        }

        // 3. Tombol Hapus (Selalu Muncul)
        $btnHapus = "
            <button class='btn btn-icon btn-sm btn-danger btnDeletePelamar' data-id='{$row['id_calon']}' title='Hapus Permanen'>
                <i class='bx bx-trash'></i>
            </button>";

        // Gabungkan semua tombol
        $action = "
            <div class='d-flex justify-content-center gap-1'>
                {$btnAcc}
                {$btnTolak}
                {$btnHapus}
            </div>
        ";

        $data[] = [
            'no' => $no++,
            'nama' => "<div class='d-flex flex-column'>
                            <span class='fw-semibold text-primary'>" . htmlspecialchars($row['nama_lengkap']) . "</span>
                            <small class='text-muted'>" . ($row['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan') . "</small>
                       </div>",
            'posisi' => "<span class='fw-medium'>" . htmlspecialchars($row['nama_jabatan'] ?? 'Posisi Dihapus') . "</span>",
            'waktu' => date('d M Y', strtotime($row['tgl_daftar'] ?? 'now')), 
            'status' => $status_badge,
            'detail' => $detail,
            'action' => $action
        ];
    }
} else {
    echo json_encode(['error' => $koneksi->error]);
    exit;
}

echo json_encode(['data' => $data]);