<?php
// Mendapatkan halaman aktif dari URL
$current_page = $_GET['page'] ?? 'home';

// Fungsi helper untuk menentukan class active
function isActive($page_name, $current_page) {
    return ($page_name === $current_page) ? 'active' : '';
}

// Fungsi helper untuk membuka menu dropdown jika salah satu sub-menunya aktif
function isMenuOpen($pages_array, $current_page) {
    return in_array($current_page, $pages_array) ? 'active open' : '';
}
?>

<ul class="menu-inner py-1">

    <li class="menu-item <?= isActive('home', $current_page); ?>">
        <a href="?page=home" class="menu-link">
            <i class="menu-icon tf-icons bx bx-home-circle"></i>
            <div data-i18n="Analytics">Dashboard</div>
        </a>
    </li>

    <li class="menu-header small text-uppercase">
        <span class="menu-header-text">Data Utama</span>
    </li>
    
    <?php 
    $master_pages = ['data-karyawan', 'import-karier', 'data-site', 'data-departemen', 'data-jabatan', 'data-devisi', 'data-tunjangan', 'data-potongan'];
    ?>
    <li class="menu-item <?= isMenuOpen($master_pages, $current_page); ?>">
        <a href="javascript:void(0);" class="menu-link menu-toggle">
            <i class="menu-icon tf-icons bx bx-collection"></i>
            <div data-i18n="Master Data">Master Data</div>
        </a>
        <ul class="menu-sub">
            <li class="menu-item <?= isActive('data-karyawan', $current_page); ?>">
                <a class="menu-link" href="?page=data-karyawan"><div>Data Karyawan</div></a>
            </li>
            <li class="menu-item <?= isActive('import-karier', $current_page); ?>">
                <a class="menu-link" href="?page=import-karier"><div>Riwayat Karier</div></a>
            </li>
            <li class="menu-item <?= isActive('data-site', $current_page); ?>">
                <a class="menu-link" href="?page=data-site"><div>Data Site (Lokasi)</div></a>
            </li>
            <li class="menu-item <?= isActive('data-departemen', $current_page); ?>">
                <a class="menu-link" href="?page=data-departemen"><div>Data Departemen</div></a>
            </li>
            <li class="menu-item <?= isActive('data-jabatan', $current_page); ?>">
                <a class="menu-link" href="?page=data-jabatan"><div>Data Jabatan</div></a>
            </li>
        </ul>
    </li>

    <li class="menu-header small text-uppercase">
        <span class="menu-header-text">Presensi & Izin</span>
    </li>
    
    <?php 
    $absensi_pages = ['dayli-absen', 'rekapan-absensi', 'pengajuan-izin', 'bukti-sks'];
    ?>
    <li class="menu-item <?= isMenuOpen($absensi_pages, $current_page); ?>">
        <a href="javascript:void(0);" class="menu-link menu-toggle">
            <i class="menu-icon tf-icons bx bx-calendar-check"></i>
            <div data-i18n="Manajemen Absensi">Manajemen Absensi</div>
        </a>
        <ul class="menu-sub">
            <li class="menu-item <?= isActive('dayli-absen', $current_page); ?>">
                <a class="menu-link" href="?page=dayli-absen"><div>Absensi Harian</div></a>
            </li>
            <li class="menu-item <?= isActive('rekapan-absensi', $current_page); ?>">
                <a class="menu-link" href="?page=rekapan-absensi"><div>Rekapitulasi Bulanan</div></a>
            </li>
            <li class="menu-item <?= isActive('pengajuan-izin', $current_page); ?>">
                <a class="menu-link" href="?page=pengajuan-izin"><div>Pengajuan Izin</div></a>
            </li>
            <li class="menu-item <?= isActive('bukti-sks', $current_page); ?>">
                <a class="menu-link" href="?page=bukti-sks"><div>Data SKS</div></a>
            </li>
        </ul>
    </li>

    <li class="menu-header small text-uppercase">
        <span class="menu-header-text">Finance & Payroll</span>
    </li>
    
    <?php 
    $payroll_pages = ['data-periode', 'payroll', 'slip-gaji', 'data-pinjaman', 'data-rapel'];
    ?>
    <li class="menu-item <?= isMenuOpen($payroll_pages, $current_page); ?>">
        <a href="javascript:void(0);" class="menu-link menu-toggle">
            <i class="menu-icon tf-icons bx bx-money"></i>
            <div data-i18n="Payroll System">Payroll System</div>
        </a>
        <ul class="menu-sub">
            <li class="menu-item <?= isActive('data-periode', $current_page); ?>">
                <a class="menu-link" href="?page=data-periode"><div>Periode Penggajian</div></a>
            </li>
            <li class="menu-item <?= isActive('payroll', $current_page); ?>">
                <a class="menu-link" href="?page=payroll"><div>Proses Gaji</div></a>
            </li>
            <li class="menu-item <?= isActive('slip-gaji', $current_page); ?>">
                <a class="menu-link" href="?page=slip-gaji"><div>Slip Gaji Karyawan</div></a>
            </li>
            <li class="menu-item <?= isActive('data-pinjaman', $current_page); ?>">
                <a class="menu-link" href="?page=data-pinjaman"><div>Pinjaman Dana</div></a>
            </li>
            <li class="menu-item <?= isActive('data-rapel', $current_page); ?>">
                <a class="menu-link" href="?page=data-rapel"><div>Kalkulasi Rapel</div></a>
            </li>
        </ul>
    </li>

    <li class="menu-header small text-uppercase">
        <span class="menu-header-text">Inventaris K3</span>
    </li>
    
    <?php 
    $apd_pages = ['master-apd', 'transaksi-apd'];
    ?>
    <li class="menu-item <?= isMenuOpen($apd_pages, $current_page); ?>">
        <a href="javascript:void(0);" class="menu-link menu-toggle">
            <i class="menu-icon tf-icons bx bx-shield-quarter"></i>
            <div data-i18n="Manajemen APD">Manajemen APD</div>
        </a>
        <ul class="menu-sub">
            <li class="menu-item <?= isActive('master-apd', $current_page); ?>">
                <a class="menu-link" href="?page=master-apd"><div>Stok & Jenis APD</div></a>
            </li>
            <li class="menu-item <?= isActive('transaksi-apd', $current_page); ?>">
                <a class="menu-link" href="?page=transaksi-apd"><div>Distribusi APD</div></a>
            </li>
        </ul>
    </li>

    <li class="menu-header small text-uppercase">
        <span class="menu-header-text">Informasi</span>
    </li>
    <li class="menu-item <?= isActive('pengumuman', $current_page); ?>">
        <a href="?page=pengumuman" class="menu-link">
            <i class="menu-icon tf-icons bx bx-bullseye"></i>
            <div data-i18n="Broadcast">Broadcast Pengumuman</div>
        </a>
    </li>

    <li class="menu-header small text-uppercase">
        <span class="menu-header-text">Sistem & Akses</span>
    </li>
    <li class="menu-item <?= isActive('data-users-login', $current_page); ?>">
        <a href="?page=data-users-login" class="menu-link">
            <i class="menu-icon tf-icons bx bx-user-pin"></i>
            <div data-i18n="Users">Manajemen User</div>
        </a>
    </li>
    
    <?php 
    $util_pages = ['maintenance', 'error', 'documentation'];
    ?>
    <li class="menu-item <?= isMenuOpen($util_pages, $current_page); ?>">
        <a href="javascript:void(0);" class="menu-link menu-toggle">
            <i class="menu-icon tf-icons bx bx-cog"></i>
            <div data-i18n="Utility">System Utility</div>
        </a>
        <ul class="menu-sub">
            <li class="menu-item <?= isActive('maintenance', $current_page); ?>">
                <a class="menu-link" href="?page=maintenance"><div>Maintenance Mode</div></a>
            </li>
            <li class="menu-item <?= isActive('error', $current_page); ?>">
                <a class="menu-link" href="?page=error"><div>Log Errors</div></a>
            </li>
            <li class="menu-item <?= isActive('documentation', $current_page); ?>">
                <a class="menu-link" href="?page=documentation"><div>Dokumentasi API</div></a>
            </li>
        </ul>
    </li>

</ul>