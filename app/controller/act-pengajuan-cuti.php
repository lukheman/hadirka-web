<?php
header('Content-Type: application/json');
include __DIR__ . "/../config/koneksi.php";
$action = $_GET['action'] ?? '';

// ==========================================
// BLOK BARU: AMBIL SEMUA DATA KARYAWAN UNTUK DROPDOWN
// ==========================================
if ($action == 'get-all-karyawan') {
    $query = mysqli_query($koneksi, "SELECT id_karyawan, nik_karyawan, nama_lengkap FROM tb_karyawan ORDER BY nama_lengkap ASC");
    $data = [];
    while ($row = mysqli_fetch_assoc($query)) {
        $data[] = $row;
    }
    echo json_encode($data);
    exit;
}

// ==========================================
// BLOK 1: TAMBAH CUTI OLEH ADMIN (BYPASS)
// ==========================================
if ($action == 'tambah_admin' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_karyawan = mysqli_real_escape_string($koneksi, $_POST['id_karyawan']);
    $tgl_awal    = mysqli_real_escape_string($koneksi, $_POST['tgl_awal']);
    $tgl_akhir   = mysqli_real_escape_string($koneksi, $_POST['tgl_akhir']);
    $lama_cuti   = (int)$_POST['lama_cuti'];
    $alasan_cuti = mysqli_real_escape_string($koneksi, $_POST['alasan_cuti']);
    $status_cuti = mysqli_real_escape_string($koneksi, $_POST['status_cuti']); // pending / disetujui

    $tahun_cuti = date('Y', strtotime($tgl_awal));
    $path_bukti = NULL;

    // A. Logika Pengecekan Jatah Cuti (Jika Admin memilih langsung Disetujui)
    if ($status_cuti == 'disetujui') {
        $q_jatah = mysqli_query($koneksi, "SELECT * FROM tb_jatah_cuti WHERE id_karyawan = '$id_karyawan' AND tahun = '$tahun_cuti'");
        $data_jatah = mysqli_fetch_assoc($q_jatah);

        // 👇 PERUBAHAN DI SINI: AUTO-GENERATE JATAH JIKA BELUM ADA 👇
        if (!$data_jatah) {
            // Otomatis buatkan jatah 12 hari untuk tahun tersebut
            mysqli_query($koneksi, "INSERT INTO tb_jatah_cuti (id_karyawan, tahun, sisa_cuti) VALUES ('$id_karyawan', '$tahun_cuti', 12)");
            $data_jatah = ['sisa_cuti' => 12]; // Set nilai sementara untuk pengecekan di bawah
        }
        // 👆 ======================================================= 👆

        if ($data_jatah['sisa_cuti'] < $lama_cuti) {
            echo json_encode(['status' => 'error', 'message' => "Sisa cuti tidak mencukupi. Sisa jatah: " . $data_jatah['sisa_cuti'] . " hari."]);
            exit;
        }
    }

    // B. Proses Upload Gambar (Opsional)
    if (isset($_FILES['bukti_cuti']) && $_FILES['bukti_cuti']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../../uploads/cuti/';
        if (!file_exists($upload_dir)) mkdir($upload_dir, 0755, true);

        $file_ext = strtolower(pathinfo($_FILES['bukti_cuti']['name'], PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png', 'webp', 'pdf'];

        if (!in_array($file_ext, $allowed_ext)) {
            echo json_encode(['status' => 'error', 'message' => "Format file tidak didukung."]);
            exit;
        }

        // Penamaan file: cuti_idkaryawan_tglawal_timestamp.ext
        $file_name = 'cuti_' . $id_karyawan . '_' . date('Ymd', strtotime($tgl_awal)) . '_' . time() . '.' . $file_ext;
        $target_path = $upload_dir . $file_name;

        if (move_uploaded_file($_FILES['bukti_cuti']['tmp_name'], $target_path)) {
            // Simpan path relatif ke database (menyesuaikan kebiasaan folder Anda)
            $path_bukti = 'uploads/cuti/' . $file_name;
        } else {
            echo json_encode(['status' => 'error', 'message' => "Gagal mengunggah file."]);
            exit;
        }
    }

    // C. Insert ke tb_pengajuan_cuti
    // Perhatikan kita menggunakan NULL atau string '$path_bukti'
    $sql_insert = "INSERT INTO tb_pengajuan_cuti (id_karyawan, tgl_awal, tgl_akhir, lama_cuti, alasan_cuti, bukti_cuti, status_cuti) 
                   VALUES ('$id_karyawan', '$tgl_awal', '$tgl_akhir', $lama_cuti, '$alasan_cuti', " . ($path_bukti ? "'$path_bukti'" : "NULL") . ", '$status_cuti')";

    if (mysqli_query($koneksi, $sql_insert)) {
        // Jika statusnya disetujui, langsung potong jatah
        if ($status_cuti == 'disetujui') {
            mysqli_query($koneksi, "UPDATE tb_jatah_cuti SET sisa_cuti = sisa_cuti - $lama_cuti WHERE id_karyawan = '$id_karyawan' AND tahun = '$tahun_cuti'");
        }
        echo json_encode(['status' => 'success', 'message' => 'Pengajuan cuti berhasil ditambahkan.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan ke database: ' . mysqli_error($koneksi)]);
    }
    exit;
}

// ==========================================
// BLOK 2: UPDATE STATUS (PERSETUJUAN)
// ==========================================
if ($action == 'update-status') {
    $id = mysqli_real_escape_string($koneksi, $_GET['id']);
    $new_status = mysqli_real_escape_string($koneksi, $_GET['status']);
    $ket_admin = mysqli_real_escape_string($koneksi, $_POST['ket_admin'] ?? '');

    $q_cek = mysqli_query($koneksi, "SELECT * FROM tb_pengajuan_cuti WHERE id_pengajuan_cuti = '$id'");
    $data_cuti = mysqli_fetch_assoc($q_cek);

    if (!$data_cuti) {
        echo json_encode(['status' => 'error', 'message' => 'Data tidak ditemukan']);
        exit;
    }

    $old_status = $data_cuti['status_cuti'];
    $lama_cuti = $data_cuti['lama_cuti'];
    $id_karyawan = $data_cuti['id_karyawan'];
    $tahun_cuti = date('Y', strtotime($data_cuti['tgl_awal']));

    if ($old_status != $new_status) {
        $q_jatah = mysqli_query($koneksi, "SELECT * FROM tb_jatah_cuti WHERE id_karyawan = '$id_karyawan' AND tahun = '$tahun_cuti'");
        $data_jatah = mysqli_fetch_assoc($q_jatah);

        // AUTO-GENERATE SAAT UPDATE STATUS KE DISETUJUI
        if (!$data_jatah && $new_status == 'disetujui') {
            // Otomatis buatkan jatah 12 hari
            mysqli_query($koneksi, "INSERT INTO tb_jatah_cuti (id_karyawan, tahun, sisa_cuti) VALUES ('$id_karyawan', '$tahun_cuti', 12)");
            $data_jatah = ['sisa_cuti' => 12];
        }

        if ($new_status == 'disetujui') {
            if ($data_jatah['sisa_cuti'] < $lama_cuti) {
                echo json_encode(['status' => 'error', 'message' => 'Sisa cuti tidak mencukupi. Sisa: ' . $data_jatah['sisa_cuti'] . ' hari.']);
                exit;
            }
            mysqli_query($koneksi, "UPDATE tb_jatah_cuti SET sisa_cuti = sisa_cuti - $lama_cuti WHERE id_karyawan = '$id_karyawan' AND tahun = '$tahun_cuti'");
        } elseif ($old_status == 'disetujui' && ($new_status == 'pending' || $new_status == 'ditolak')) {
            mysqli_query($koneksi, "UPDATE tb_jatah_cuti SET sisa_cuti = sisa_cuti + $lama_cuti WHERE id_karyawan = '$id_karyawan' AND tahun = '$tahun_cuti'");
        }
    }

    $sql_update = "UPDATE tb_pengajuan_cuti SET status_cuti = '$new_status', ket_admin = '$ket_admin' WHERE id_pengajuan_cuti = '$id'";

    if (mysqli_query($koneksi, $sql_update)) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => mysqli_error($koneksi)]);
    }
    exit;
}

// ==========================================
// BLOK 3: DELETE DATA
// ==========================================
if ($action == 'delete') {
    $id = mysqli_real_escape_string($koneksi, $_GET['id']);

    $query_cari = mysqli_query($koneksi, "SELECT * FROM tb_pengajuan_cuti WHERE id_pengajuan_cuti = '$id'");
    $data_cuti = mysqli_fetch_assoc($query_cari);

    if ($data_cuti) {
        if ($data_cuti['status_cuti'] == 'disetujui') {
            $id_karyawan = $data_cuti['id_karyawan'];
            $lama_cuti = $data_cuti['lama_cuti'];
            $tahun_cuti = date('Y', strtotime($data_cuti['tgl_awal']));
            mysqli_query($koneksi, "UPDATE tb_jatah_cuti SET sisa_cuti = sisa_cuti + $lama_cuti WHERE id_karyawan = '$id_karyawan' AND tahun = '$tahun_cuti'");
        }

        $nama_file = $data_cuti['bukti_cuti'];
        $path_file = "../../" . $nama_file;
        if (!empty($nama_file) && file_exists($path_file)) {
            unlink($path_file);
        }
    }

    $sql = "DELETE FROM tb_pengajuan_cuti WHERE id_pengajuan_cuti = '$id'";

    if (mysqli_query($koneksi, $sql)) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => mysqli_error($koneksi)]);
    }
    exit;
}
