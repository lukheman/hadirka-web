<?php
    require_once __DIR__ . '/../../config/koneksi.php';

    $sites = [];
    $departemens = [];
    $jabatans = [];

    $q1 = $koneksi->query("SELECT id_site, nama_site FROM tb_site ORDER BY nama_site ASC");
    while ($r = $q1->fetch()) {
        $sites[] = $r;
    }

    $q2 = $koneksi->query("SELECT id_departemen, nama_departemen FROM tb_departemen ORDER BY nama_departemen ASC");
    while ($r = $q2->fetch()) {
        $departemens[] = $r;
    }

    $q3 = $koneksi->query("SELECT id_jabatan, nama_jabatan FROM tb_jabatan ORDER BY nama_jabatan ASC");
    while ($r = $q3->fetch()) {
        $jabatans[] = $r;
    }

    echo json_encode([
        "site" => $sites,
        "departemen" => $departemens,
        "jabatan" => $jabatans
    ]);
