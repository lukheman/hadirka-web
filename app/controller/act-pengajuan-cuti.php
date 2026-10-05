<?php
header('Content-Type: application/json');
include __DIR__ . "/../config/koneksi.php";
$action = $_POST['action'] ?? $_GET['action'] ?? '';

// ==========================================
// BLOK BARU: AMBIL SEMUA DATA KARYAWAN UNTUK DROPDOWN
// ==========================================
if ($action == 'get-all-karyawan') {
    $query = $koneksi->query("SELECT id_karyawan, nik_karyawan, nama_lengkap FROM tb_karyawan ORDER BY nama_lengkap ASC");
    $data = $query->fetchAll();
    echo json_encode($data);
    exit;
}

// ==========================================
// BLOK 1: TAMBAH CUTI OLEH ADMIN (BYPASS)
// ==========================================
if ($action == 'tambah_admin' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_karyawan = $_POST['id_karyawan'];
    $tgl_awal    = $_POST['tgl_awal'];
    $tgl_akhir   = $_POST['tgl_akhir'];
    $lama_cuti   = (int)$_POST['lama_cuti'];
    $alasan_cuti = $_POST['alasan_cuti'];
    $status_cuti = $_POST['status_cuti']; // pending / disetujui

    $tahun_cuti = date('Y', strtotime($tgl_awal));
    $path_bukti = NULL;

    // --- TAMBAHKAN VALIDASI MAKSIMAL 6 HARI DI SINI ---
    if ($lama_cuti > 6) {
        echo json_encode(['status' => 'error', 'message' => "Maksimal cuti dalam satu kali pengajuan adalah 6 hari."]);
        exit;
    }

    try {
        // A. Logika Pengecekan Jatah Cuti (Jika Admin memilih langsung Disetujui)
        if ($status_cuti == 'disetujui') {
            $q_jatah = $koneksi->prepare("SELECT * FROM tb_jatah_cuti WHERE id_karyawan = ? AND tahun = ?");
            $q_jatah->execute([$id_karyawan, $tahun_cuti]);
            $data_jatah = $q_jatah->fetch();

            // 👇 PERUBAHAN DI SINI: AUTO-GENERATE JATAH JIKA BELUM ADA 👇
            if (!$data_jatah) {
                // Otomatis buatkan jatah 12 hari untuk tahun tersebut
                $ins = $koneksi->prepare("INSERT INTO tb_jatah_cuti (id_karyawan, tahun, sisa_cuti) VALUES (?, ?, 12)");
                $ins->execute([$id_karyawan, $tahun_cuti]);
                $data_jatah = ['sisa_cuti' => 12]; // Set nilai sementara untuk pengecekan di bawah
            }
            // 👆 ======================================================= 👆

            if ($data_jatah['sisa_cuti'] < $lama_cuti) {
                echo json_encode(['status' => 'error', 'message' => "Sisa cuti tidak mencukupi. Sisa jatah: " . $data_jatah['sisa_cuti'] . " hari."]);
                exit;
            }
        }

        // B. Proses Upload & Kompresi Gambar (Opsional)
        if (isset($_FILES['bukti_cuti']) && $_FILES['bukti_cuti']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../../uploads/cuti/';
            if (!file_exists($upload_dir)) mkdir($upload_dir, 0755, true);

            $file_ext = strtolower(pathinfo($_FILES['bukti_cuti']['name'], PATHINFO_EXTENSION));
            $allowed_ext = ['jpg', 'jpeg', 'png', 'webp', 'pdf'];

            if (!in_array($file_ext, $allowed_ext)) {
                echo json_encode(['status' => 'error', 'message' => "Format file tidak didukung."]);
                exit;
            }

            // Penamaan file (selalu simpan sebagai JPG jika itu gambar, agar ukuran lebih kecil)
            $final_ext = ($file_ext === 'pdf') ? 'pdf' : 'jpg';
            $file_name = 'cuti_' . $id_karyawan . '_' . date('Ymd', strtotime($tgl_awal)) . '_' . time() . '.' . $final_ext;
            $target_path = $upload_dir . $file_name;
            $file_tmp = $_FILES['bukti_cuti']['tmp_name'];

            $upload_sukses = false;

            // Jika file adalah PDF, langsung pindahkan (tidak bisa dikompresi sbg gambar)
            if ($file_ext === 'pdf') {
                if (move_uploaded_file($file_tmp, $target_path)) {
                    $upload_sukses = true;
                }
            }
            // Jika file adalah gambar, lakukan kompresi
            else {
                // 1. Dapatkan informasi gambar asli
                list($width, $height, $type) = getimagesize($file_tmp);

                // Tentukan rasio kompresi (maksimal lebar atau tinggi 1024px)
                $max_dim = 1024;
                $ratio = $width / $height;

                if ($width > $max_dim || $height > $max_dim) {
                    if ($ratio > 1) {
                        $new_width = $max_dim;
                        $new_height = $max_dim / $ratio;
                    } else {
                        $new_height = $max_dim;
                        $new_width = $max_dim * $ratio;
                    }
                } else {
                    $new_width = $width;
                    $new_height = $height;
                }

                // 2. Buat kanvas gambar baru
                $image_p = imagecreatetruecolor($new_width, $new_height);

                // Pastikan background transparan untuk PNG/WEBP ditangani dengan benar jika diubah ke JPG (dijadikan putih)
                $white = imagecolorallocate($image_p, 255, 255, 255);
                imagefill($image_p, 0, 0, $white);

                // 3. Buat objek gambar dari file sementara berdasarkan tipe aslinya
                $image = null;
                switch ($type) {
                    case IMAGETYPE_JPEG:
                        $image = imagecreatefromjpeg($file_tmp);
                        break;
                    case IMAGETYPE_PNG:
                        $image = imagecreatefrompng($file_tmp);
                        break;
                    case IMAGETYPE_WEBP:
                        $image = imagecreatefromwebp($file_tmp);
                        break;
                }

                if ($image !== null) {
                    // 4. Salin dan ubah ukuran gambar asli ke kanvas baru
                    imagecopyresampled($image_p, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

                    // 5. Simpan gambar hasil kompresi (kualitas 75% cukup baik dan sangat menghemat ukuran)
                    if (imagejpeg($image_p, $target_path, 75)) {
                        $upload_sukses = true;
                    }

                    // Bersihkan memory
                    imagedestroy($image_p);
                    imagedestroy($image);
                }
            }

            if ($upload_sukses) {
                $path_bukti = 'uploads/cuti/' . $file_name;
            } else {
                echo json_encode(['status' => 'error', 'message' => "Gagal mengunggah dan mengompres file."]);
                exit;
            }
        }

        // C. Insert ke tb_pengajuan_cuti
        $st = $koneksi->prepare("INSERT INTO tb_pengajuan_cuti (id_karyawan, tgl_awal, tgl_akhir, lama_cuti, alasan_cuti, bukti_cuti, status_cuti)
                       VALUES (?, ?, ?, ?, ?, ?, ?)");
        $st->execute([$id_karyawan, $tgl_awal, $tgl_akhir, $lama_cuti, $alasan_cuti, $path_bukti, $status_cuti]);

        // Jika statusnya disetujui, langsung potong jatah
        if ($status_cuti == 'disetujui') {
            $pot = $koneksi->prepare("UPDATE tb_jatah_cuti SET sisa_cuti = sisa_cuti - ? WHERE id_karyawan = ? AND tahun = ?");
            $pot->execute([$lama_cuti, $id_karyawan, $tahun_cuti]);
        }
        echo json_encode(['status' => 'success', 'message' => 'Pengajuan cuti berhasil ditambahkan.']);
    } catch (PDOException $e) {
        error_log($e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan ke database.']);
    }
    exit;
}

// ==========================================
// BLOK 2: UPDATE STATUS (PERSETUJUAN)
// ==========================================
if ($action == 'update-status') {
    // Tangkap dari POST, fallback ke GET jika tidak ada
    $id = $_POST['id'] ?? $_GET['id'];
    $new_status = $_POST['status'] ?? $_GET['status'];
    $ket_admin = $_POST['ket_admin'] ?? '';

    try {
        $q_cek = $koneksi->prepare("SELECT * FROM tb_pengajuan_cuti WHERE id_pengajuan_cuti = ?");
        $q_cek->execute([$id]);
        $data_cuti = $q_cek->fetch();

        if (!$data_cuti) {
            echo json_encode(['status' => 'error', 'message' => 'Data tidak ditemukan']);
            exit;
        }

        $old_status = $data_cuti['status_cuti'];
        $lama_cuti = $data_cuti['lama_cuti'];
        $id_karyawan = $data_cuti['id_karyawan'];
        $tahun_cuti = date('Y', strtotime($data_cuti['tgl_awal']));

        if ($old_status != $new_status) {
            $q_jatah = $koneksi->prepare("SELECT * FROM tb_jatah_cuti WHERE id_karyawan = ? AND tahun = ?");
            $q_jatah->execute([$id_karyawan, $tahun_cuti]);
            $data_jatah = $q_jatah->fetch();

            // AUTO-GENERATE SAAT UPDATE STATUS KE DISETUJUI
            if (!$data_jatah && $new_status == 'disetujui') {
                // Otomatis buatkan jatah 12 hari
                $ins = $koneksi->prepare("INSERT INTO tb_jatah_cuti (id_karyawan, tahun, sisa_cuti) VALUES (?, ?, 12)");
                $ins->execute([$id_karyawan, $tahun_cuti]);
                $data_jatah = ['sisa_cuti' => 12];
            }

            if ($new_status == 'disetujui') {
                if ($data_jatah['sisa_cuti'] < $lama_cuti) {
                    echo json_encode(['status' => 'error', 'message' => 'Sisa cuti tidak mencukupi. Sisa: ' . $data_jatah['sisa_cuti'] . ' hari.']);
                    exit;
                }
                $pot = $koneksi->prepare("UPDATE tb_jatah_cuti SET sisa_cuti = sisa_cuti - ? WHERE id_karyawan = ? AND tahun = ?");
                $pot->execute([$lama_cuti, $id_karyawan, $tahun_cuti]);
            } elseif ($old_status == 'disetujui' && ($new_status == 'pending' || $new_status == 'ditolak')) {
                $ret = $koneksi->prepare("UPDATE tb_jatah_cuti SET sisa_cuti = sisa_cuti + ? WHERE id_karyawan = ? AND tahun = ?");
                $ret->execute([$lama_cuti, $id_karyawan, $tahun_cuti]);
            }
        }

        $st = $koneksi->prepare("UPDATE tb_pengajuan_cuti SET status_cuti = ?, ket_admin = ? WHERE id_pengajuan_cuti = ?");
        $st->execute([$new_status, $ket_admin, $id]);
        echo json_encode(['status' => 'success']);
    } catch (PDOException $e) {
        error_log($e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Gagal memperbarui status.']);
    }
    exit;
}

// ==========================================
// BLOK 3: DELETE DATA
// ==========================================
if ($action == 'delete') {
    $id = $_GET['id'];

    try {
        $query_cari = $koneksi->prepare("SELECT * FROM tb_pengajuan_cuti WHERE id_pengajuan_cuti = ?");
        $query_cari->execute([$id]);
        $data_cuti = $query_cari->fetch();

        if ($data_cuti) {
            if ($data_cuti['status_cuti'] == 'disetujui') {
                $id_karyawan = $data_cuti['id_karyawan'];
                $lama_cuti = $data_cuti['lama_cuti'];
                $tahun_cuti = date('Y', strtotime($data_cuti['tgl_awal']));
                $ret = $koneksi->prepare("UPDATE tb_jatah_cuti SET sisa_cuti = sisa_cuti + ? WHERE id_karyawan = ? AND tahun = ?");
                $ret->execute([$lama_cuti, $id_karyawan, $tahun_cuti]);
            }

            $nama_file = $data_cuti['bukti_cuti'];
            $path_file = "../../" . $nama_file;
            if (!empty($nama_file) && file_exists($path_file)) {
                unlink($path_file);
            }
        }

        $st = $koneksi->prepare("DELETE FROM tb_pengajuan_cuti WHERE id_pengajuan_cuti = ?");
        $st->execute([$id]);
        echo json_encode(['status' => 'success']);
    } catch (PDOException $e) {
        error_log($e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus data.']);
    }
    exit;
}
