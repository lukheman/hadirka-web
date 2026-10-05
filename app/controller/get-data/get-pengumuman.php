<?php
include "../../config/koneksi.php";

function hex2rgba($color, $opacity = 0.1)
{
    if (empty($color)) return "rgba(0,0,0,0.1)";
    list($r, $g, $b) = sscanf($color, "#%02x%02x%02x");
    return "rgba($r, $g, $b, $opacity)";
}

$rowsPengumuman = $koneksi->query("SELECT * FROM tb_pengumuman ORDER BY waktu_buat DESC")->fetchAll();

if (count($rowsPengumuman) == 0) {
    echo '<div class="col-12 text-center py-5 text-muted">Belum ada pengumuman.</div>';
}

foreach ($rowsPengumuman as $row) {
    $hex = $row['warna_kode'];
    $bg  = hex2rgba($hex, 0.15);
    echo '
    <div class="col-md-6 mb-4">
        <div class="card h-100 shadow-none border" style="border-left: 5px solid ' . $hex . ' !important;">
            <div class="card-body d-flex align-items-center">
                <div class="me-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" 
                         style="width: 48px; height: 48px; background: ' . $bg . '; color: ' . $hex . ';">
                        <span class="material-symbols-rounded">' . $row['ikon_nama'] . '</span>
                    </div>
                </div>
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between">
                        <h6 class="fw-bold mb-0">' . $row['judul'] . '</h6>
                        <div class="dropdown">
                            <button class="btn btn-sm p-0" data-bs-toggle="dropdown"><i class="bx bx-dots-vertical-rounded"></i></button>
                            <ul class="dropdown-menu shadow">
                                <li><a class="dropdown-item" href="javascript:void(0)" onclick="editPengumuman(' . $row['id'] . ')"><i class="bx bx-edit me-2"></i>Edit</a></li>
                                <li><a class="dropdown-item text-danger" href="javascript:void(0)" onclick="deleteData(' . $row['id'] . ')"><i class="bx bx-trash me-2"></i>Hapus</a></li>
                            </ul>
                        </div>
                    </div>
                    <p class="text-muted small mb-0 mt-1">' . htmlspecialchars($row['sub_judul']) . '</p>
                </div>
            </div>
        </div>
    </div>';
}
?>