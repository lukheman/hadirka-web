<?php
    require_once __DIR__ . '/../../config/koneksi.php';

    $sites = [];
    $departemens = [];
    $jabatans = [];

    $q1 = mysqli_query($koneksi, "SELECT id_site, nama_site FROM tb_site ORDER BY nama_site ASC");
    while ($r = mysqli_fetch_assoc($q1)) {
        $sites[] = $r;
    }

    $q2 = mysqli_query($koneksi, "SELECT id_departemen, nama_departemen FROM tb_departemen ORDER BY nama_departemen ASC");
    while ($r = mysqli_fetch_assoc($q2)) {
        $departemens[] = $r;
    }

    $q3 = mysqli_query($koneksi, "SELECT id_jabatan, nama_jabatan FROM tb_jabatan ORDER BY nama_jabatan ASC");
    while ($r = mysqli_fetch_assoc($q3)) {
        $jabatans[] = $r;
    }

    echo json_encode([
        "site" => $sites,
        "departemen" => $departemens,
        "jabatan" => $jabatans
    ]);
