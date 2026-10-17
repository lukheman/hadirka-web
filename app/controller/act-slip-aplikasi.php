<?php
require_once __DIR__ . '/../config/koneksi.php';
header("Content-Type: application/json; charset=UTF-8");

// Cegah notice agar JSON tidak rusak
error_reporting(E_ERROR | E_PARSE);

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {

    // --- TERBITKAN SLIP GAJI KE APLIKASI ---
    case "save":
        $id_karyawan = (int)($_POST['id_karyawan'] ?? 0);
        $periode = trim($_POST['periode'] ?? '');
        $gajiPokok = (float)($_POST['gajiPokok'] ?? 0);
        $tunjangan = (float)($_POST['tunjangan'] ?? 0);
        $potongan = (float)($_POST['potongan'] ?? 0);
        $catatan = trim($_POST['catatan'] ?? '');

        if ($id_karyawan <= 0 || $periode === '') {
            echo json_encode(["status" => "error", "msg" => "Karyawan & periode wajib dipilih."]);
            exit;
        }
        if ($gajiPokok < 0 || $tunjangan < 0 || $potongan < 0) {
            echo json_encode(["status" => "error", "msg" => "Nominal tidak boleh negatif."]);
            exit;
        }

        try {
            $cek = $koneksi->prepare("SELECT COUNT(*) FROM tb_karyawan WHERE id_karyawan = ?");
            $cek->execute([$id_karyawan]);
            if (!$cek->fetchColumn()) {
                echo json_encode(["status" => "error", "msg" => "Karyawan tidak ditemukan."]);
                exit;
            }

            $total = $gajiPokok + $tunjangan - $potongan;

            $st = $koneksi->prepare("INSERT INTO tb_slip_gaji
                (id_karyawan, periode, gaji_pokok, tunjangan, potongan, total_diterima, status_rilis, catatan)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $st->execute([
                $id_karyawan,
                $periode,
                $gajiPokok,
                $tunjangan,
                $potongan,
                $total,
                'DITERBITKAN',
                $catatan !== '' ? $catatan : null,
            ]);

            echo json_encode(["status" => "success", "msg" => "Slip gaji periode $periode berhasil diterbitkan ke aplikasi."]);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            echo json_encode(["status" => "error", "msg" => "Gagal menerbitkan slip gaji."]);
        }
        exit;

    // --- HAPUS SLIP DARI APLIKASI ---
    case "delete":
        $id = (int)($_GET['id'] ?? 0);

        if ($id <= 0) {
            echo json_encode(["status" => "error", "msg" => "ID tidak valid."]);
            exit;
        }

        try {
            $st = $koneksi->prepare("DELETE FROM tb_slip_gaji WHERE id_slip_gaji = ?");
            $st->execute([$id]);

            if ($st->rowCount() > 0) {
                echo json_encode(["status" => "success", "msg" => "Slip gaji berhasil dihapus dari aplikasi."]);
            } else {
                echo json_encode(["status" => "error", "msg" => "Slip gaji tidak ditemukan."]);
            }
        } catch (PDOException $e) {
            error_log($e->getMessage());
            echo json_encode(["status" => "error", "msg" => "Gagal menghapus slip gaji."]);
        }
        exit;
}

echo json_encode(["status" => "error", "msg" => "Action tidak valid"]);
