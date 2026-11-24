<?php
$user_level = $_SESSION['level'] ?? 99;
$current_page = $_GET['page'] ?? 'home';

function isActive($page_name, $current_page)
{
    return ($page_name === $current_page) ? 'active' : '';
}

function isMenuOpen($pages_array, $current_page)
{
    return in_array($current_page, $pages_array, true) ? 'active open' : '';
}

$absensi_pages = ['data-karyawan', 'data-site', 'dayli-absen', 'rekapan-absensi', 'absen-mobile', 'pengajuan-izin', 'pengajuan-cuti', 'bukti-sks'];
?>
<ul class="menu-inner py-1">
    <li class="menu-item <?= isActive('home', $current_page); ?>">
        <a href="?page=home" class="menu-link">
            <i class="menu-icon tf-icons bx bx-home-circle"></i>
            <div>Dashboard Absensi</div>
        </a>
    </li>

    <?php if (in_array($user_level, [0, 1, 2, 3, 5, 6], true)): ?>
        <li class="menu-header small text-uppercase"><span class="menu-header-text">Data Pendukung</span></li>
        <li class="menu-item <?= isActive('data-karyawan', $current_page); ?>">
            <a href="?page=data-karyawan" class="menu-link"><i class="menu-icon tf-icons bx bx-user"></i><div>Data Karyawan</div></a>
        </li>
        <li class="menu-item <?= isActive('data-site', $current_page); ?>">
            <a href="?page=data-site" class="menu-link"><i class="menu-icon tf-icons bx bx-map"></i><div>Data Site / Geofence</div></a>
        </li>

        <li class="menu-header small text-uppercase"><span class="menu-header-text">Manajemen Absensi</span></li>
        <li class="menu-item <?= isMenuOpen($absensi_pages, $current_page); ?>">
            <a href="javascript:void(0);" class="menu-link menu-toggle"><i class="menu-icon tf-icons bx bx-calendar-check"></i><div>Presensi & Administrasi</div></a>
            <ul class="menu-sub">
                <li class="menu-item <?= isActive('dayli-absen', $current_page); ?>"><a class="menu-link" href="?page=dayli-absen"><div>Absensi Harian</div></a></li>
                <li class="menu-item <?= isActive('rekapan-absensi', $current_page); ?>"><a class="menu-link" href="?page=rekapan-absensi"><div>Rekapitulasi Absensi</div></a></li>
                <li class="menu-item <?= isActive('absen-mobile', $current_page); ?>"><a class="menu-link" href="?page=absen-mobile"><div>Absensi Mobile GPS</div></a></li>
                <li class="menu-item <?= isActive('pengajuan-izin', $current_page); ?>"><a class="menu-link" href="?page=pengajuan-izin"><div>Pengajuan Izin</div></a></li>
                <li class="menu-item <?= isActive('pengajuan-cuti', $current_page); ?>"><a class="menu-link" href="?page=pengajuan-cuti"><div>Pengajuan Cuti</div></a></li>
                <li class="menu-item <?= isActive('bukti-sks', $current_page); ?>"><a class="menu-link" href="?page=bukti-sks"><div>Data SKS</div></a></li>
            </ul>
        </li>
    <?php endif; ?>

    <li class="menu-header small text-uppercase"><span class="menu-header-text">Sistem</span></li>
    <li class="menu-item"><a href="?page=logout" class="menu-link"><i class="menu-icon tf-icons bx bx-power-off"></i><div>Keluar</div></a></li>
</ul>
