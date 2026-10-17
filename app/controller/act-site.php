<?php
require_once __DIR__ . '/../config/koneksi.php';
header("Content-Type: application/json; charset=UTF-8");

// Cegah Notis muncul agar JSON tidak rusak
error_reporting(E_ERROR | E_PARSE);

$action = $_POST['action'] ?? '';

$response = ["status" => "error"];

/**
 * Normalisasi & validasi kolom geofence dari form site.
 * Koordinat kosong disimpan NULL = site belum dipetakan (tidak dikirim
 * ke aplikasi absen mobile oleh endpoint /api/geofence).
 */
function ambil_geofence()
{
    $latitude = trim($_POST['latitude'] ?? '');
    $longitude = trim($_POST['longitude'] ?? '');
    $radius = trim($_POST['radius_meter'] ?? '');

    if ($latitude !== '' && (!is_numeric($latitude) || (float)$latitude < -90 || (float)$latitude > 90)) {
        echo json_encode(["status" => "error", "msg" => "Latitude harus berupa angka -90 s/d 90."]);
        exit;
    }
    if ($longitude !== '' && (!is_numeric($longitude) || (float)$longitude < -180 || (float)$longitude > 180)) {
        echo json_encode(["status" => "error", "msg" => "Longitude harus berupa angka -180 s/d 180."]);
        exit;
    }
    if ($radius !== '' && (!is_numeric($radius) || (float)$radius <= 0)) {
        echo json_encode(["status" => "error", "msg" => "Radius harus berupa angka lebih dari 0 meter."]);
        exit;
    }

    return [
        'kode_lokasi'      => trim($_POST['kode_lokasi'] ?? '') ?: null,
        'latitude'         => $latitude === '' ? null : (float)$latitude,
        'longitude'        => $longitude === '' ? null : (float)$longitude,
        'radius_meter'     => $radius === '' ? 80 : (float)$radius,
        'deskripsi_lokasi' => trim($_POST['deskripsi_lokasi'] ?? '') ?: null,
        'is_aktif'         => ($_POST['is_aktif'] ?? '1') === '1' ? 1 : 0,
    ];
}

switch ($action) {

    case "add":
        $nama = $_POST['nama_site'] ?? '';
        $lokasi = $_POST['lokasi'] ?? '';
        $geo = ambil_geofence();

        if ($nama == "" || $lokasi == "") {
            echo json_encode(["status" => "error", "msg" => "Data tidak lengkap"]);
            exit;
        }

        $sql = $koneksi->prepare("INSERT INTO tb_site
            (nama_site, lokasi, kode_lokasi, latitude, longitude, radius_meter, deskripsi_lokasi, is_aktif)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        try {
            $response["status"] = $sql->execute([
                $nama,
                $lokasi,
                $geo['kode_lokasi'],
                $geo['latitude'],
                $geo['longitude'],
                $geo['radius_meter'],
                $geo['deskripsi_lokasi'],
                $geo['is_aktif'],
            ]) ? "success" : "error";
        } catch (PDOException $e) {
            error_log($e->getMessage());
            $response["status"] = "error";
        }
        echo json_encode($response);
        exit;


    case "edit":
        $id = $_POST['id_site'] ?? '';
        $nama = $_POST['nama_site'] ?? '';
        $lokasi = $_POST['lokasi'] ?? '';
        $geo = ambil_geofence();

        $sql = $koneksi->prepare("UPDATE tb_site SET
                nama_site=?, lokasi=?, kode_lokasi=?, latitude=?, longitude=?,
                radius_meter=?, deskripsi_lokasi=?, is_aktif=?
            WHERE id_site=?");
        try {
            $response["status"] = $sql->execute([
                $nama,
                $lokasi,
                $geo['kode_lokasi'],
                $geo['latitude'],
                $geo['longitude'],
                $geo['radius_meter'],
                $geo['deskripsi_lokasi'],
                $geo['is_aktif'],
                $id,
            ]) ? "success" : "error";
        } catch (PDOException $e) {
            error_log($e->getMessage());
            $response["status"] = "error";
        }
        echo json_encode($response);
        exit;


    case "delete":
        $id = $_POST['id_site'] ?? '';

        $sql = $koneksi->prepare("DELETE FROM tb_site WHERE id_site=?");
        try {
            $response["status"] = $sql->execute([$id]) ? "success" : "error";
        } catch (PDOException $e) {
            error_log($e->getMessage());
            $response["status"] = "error";
        }
        echo json_encode($response);
        exit;
}

echo json_encode(["status" => "error", "msg" => "Invalid action"]);
