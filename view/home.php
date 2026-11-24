<?php
$user_level = $_SESSION['level'] ?? 99;
?>
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row g-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h4 class="card-title text-primary mb-2">Dashboard Absensi Karyawan</h4>
                    <p class="mb-0 text-muted">Kelola presensi, rekap absensi, lokasi kerja, izin, cuti, dan SKS.</p>
                </div>
            </div>
        </div>
        <?php if (in_array($user_level, [0, 1, 2, 3, 5, 6], true)): ?>
            <div class="col-md-4">
                <a href="?page=dayli-absen" class="text-decoration-none">
                    <div class="card h-100 border-0 shadow-sm"><div class="card-body">
                        <i class="bx bx-calendar-check text-primary fs-2"></i>
                        <h5 class="mt-3 mb-1">Absensi Harian</h5><p class="text-muted mb-0">Input dan koreksi presensi karyawan.</p>
                    </div></div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="?page=rekapan-absensi" class="text-decoration-none">
                    <div class="card h-100 border-0 shadow-sm"><div class="card-body">
                        <i class="bx bx-bar-chart-alt-2 text-success fs-2"></i>
                        <h5 class="mt-3 mb-1">Rekapitulasi Absensi</h5><p class="text-muted mb-0">Lihat dan unduh rekap kehadiran.</p>
                    </div></div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="?page=absen-mobile" class="text-decoration-none">
                    <div class="card h-100 border-0 shadow-sm"><div class="card-body">
                        <i class="bx bx-mobile-alt text-info fs-2"></i>
                        <h5 class="mt-3 mb-1">Absensi Mobile</h5><p class="text-muted mb-0">Pantau absensi GPS dan foto selfie.</p>
                    </div></div>
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>
