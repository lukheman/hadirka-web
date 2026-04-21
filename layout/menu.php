<?php
// Pastikan ini ditambahkan di paling atas file menu.php
$user_level = $_SESSION['level'] ?? 99;
// Mendapatkan halaman aktif dari URL
$current_page = $_GET['page'] ?? 'home';

// Fungsi helper untuk menentukan class active
function isActive($page_name, $current_page)
{
    return ($page_name === $current_page) ? 'active' : '';
}

// Fungsi helper untuk membuka menu dropdown jika salah satu sub-menunya aktif
function isMenuOpen($pages_array, $current_page)
{
    return in_array($current_page, $pages_array) ? 'active open' : '';
}
?>

<ul class="menu-inner py-1">
    <!-- Hanya Tampil jika Admin, Direktur, HRD -->
    <?php if (in_array($user_level, [0, 1, 2])): ?>
        <li class="menu-item <?= isActive('home', $current_page); ?>">
            <a href="home" class="menu-link">
                <i class="menu-icon tf-icons bx bx-home-circle"></i>
                <div data-i18n="Analytics">Dashboard</div>
            </a>
        </li>
    <?php endif; ?>

    <!-- Hanya Tampil jika Admin, Direktur, HRD -->
    <?php if (in_array($user_level, [0, 1, 2])): ?>
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
                    <a class="menu-link" href="data-karyawan">
                        <div>Data Karyawan</div>
                    </a>
                </li>
                <li class="menu-item <?= isActive('import-karier', $current_page); ?>">
                    <a class="menu-link" href="import-karier">
                        <div>Riwayat Karier</div>
                    </a>
                </li>
                <li class="menu-item <?= isActive('data-site', $current_page); ?>">
                    <a class="menu-link" href="data-site">
                        <div>Data Site (Lokasi)</div>
                    </a>
                </li>
                <li class="menu-item <?= isActive('data-departemen', $current_page); ?>">
                    <a class="menu-link" href="data-departemen">
                        <div>Data Departemen</div>
                    </a>
                </li>
                <li class="menu-item <?= isActive('data-jabatan', $current_page); ?>">
                    <a class="menu-link" href="data-jabatan">
                        <div>Data Jabatan</div>
                    </a>
                </li>
                <li class="menu-item <?= isActive('data-kontrak', $current_page); ?>">
                    <a class="menu-link" href="data-kontrak">
                        <div>Data File Kontrak</div>
                    </a>
                </li>
                <li class="menu-item <?= isActive('data-bpjs-tk', $current_page); ?>">
                    <a class="menu-link" href="data-bpjs-tk">
                        <div>Data BPJS Ketenagakerjaan</div>
                    </a>
                </li>
            </ul>
        </li>

        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Presensi & Izin</span>
        </li>

        <?php
        $absensi_pages = ['dayli-absen', 'rekapan-absensi', 'pengajuan-izin', 'pengajuan-cuti', 'bukti-sks'];
        ?>
        <li class="menu-item <?= isMenuOpen($absensi_pages, $current_page); ?>">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-calendar-check"></i>
                <div data-i18n="Manajemen Absensi">Manajemen Absensi</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item <?= isActive('dayli-absen', $current_page); ?>">
                    <a class="menu-link" href="dayli-absen">
                        <div>Absensi Harian</div>
                    </a>
                </li>
                <li class="menu-item <?= isActive('rekapan-absensi', $current_page); ?>">
                    <a class="menu-link" href="rekapan-absensi">
                        <div>Rekapitulasi Bulanan</div>
                    </a>
                </li>
                <li class="menu-item <?= isActive('pengajuan-izin', $current_page); ?>">
                    <a class="menu-link" href="pengajuan-izin">
                        <div>Pengajuan Izin</div>
                    </a>
                </li>
                <li class="menu-item <?= isActive('pengajuan-cuti', $current_page); ?>">
                    <a class="menu-link" href="pengajuan-cuti">
                        <div>Pengajuan Cuti</div>
                    </a>
                </li>
                <li class="menu-item <?= isActive('bukti-sks', $current_page); ?>">
                    <a class="menu-link" href="bukti-sks">
                        <div>Data SKS</div>
                    </a>
                </li>
            </ul>
        </li>

        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Finance & Payroll</span>
        </li>

        <?php
        $payroll_pages = ['data-periode', 'payroll', 'slip-gaji', 'data-pengajuan-pinjaman', 'data-pinjaman', 'data-rapel', 'data-thr'];
        ?>
        <li class="menu-item <?= isMenuOpen($payroll_pages, $current_page); ?>">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-money"></i>
                <div data-i18n="Payroll System">Payroll System</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item <?= isActive('data-periode', $current_page); ?>">
                    <a class="menu-link" href="data-periode">
                        <div>Periode Penggajian</div>
                    </a>
                </li>
                <li class="menu-item <?= isActive('payroll', $current_page); ?>">
                    <a class="menu-link" href="payroll">
                        <div>Proses Gaji</div>
                    </a>
                </li>
                <li class="menu-item <?= isActive('slip-gaji', $current_page); ?>">
                    <a class="menu-link" href="slipgajinew" target="_blank">
                        <div>Slip Gaji Karyawan</div>
                    </a>
                </li>
                <li class="menu-item <?= isActive('data-pengajuan-pinjaman', $current_page); ?>">
                    <a class="menu-link" href="data-pengajuan-pinjaman">
                        <div>Pengajuan Pinjaman Dana</div>
                    </a>
                </li>
                <li class="menu-item <?= isActive('data-pinjaman', $current_page); ?>">
                    <a class="menu-link" href="data-pinjaman">
                        <div>Pinjaman Dana</div>
                    </a>
                </li>
                <li class="menu-item <?= isActive('data-rapel', $current_page); ?>">
                    <a class="menu-link" href="data-rapel">
                        <div>Kalkulasi Rapel</div>
                    </a>
                </li>
                <li class="menu-item <?= isActive('data-thr', $current_page); ?>">
                    <a class="menu-link" href="data-thr">
                        <div>Manajemen THR</div>
                    </a>
                </li>
            </ul>
        </li>
<!-- 
        <?php
        $finance_pages = ['laporan-keuangan'];
        ?>
        <li class="menu-item <?= isMenuOpen($finance_pages, $current_page); ?>">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-money"></i>
                <div data-i18n="Payroll System">Manajemen Keuangan</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item <?= isActive('laporan-keuangan', $current_page); ?>">
                    <a class="menu-link" href="laporan-keuangan">
                        <div>Laporan Keuangan</div>
                    </a>
                </li>
            </ul>
        </li> -->

    <?php endif; ?>

    <!-- Hanya Tampil jika Admin, Direktur, Safety -->
    <?php if (in_array($user_level, [0, 1, 3])): ?>
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Inventaris K3</span>
        </li>

        <?php
        $apd_pages = ['master-apd', 'transaksi-apd', 'pengajuan-apd'];
        ?>
        <li class="menu-item <?= isMenuOpen($apd_pages, $current_page); ?>">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-shield-quarter"></i>
                <div data-i18n="Manajemen APD">Manajemen APD</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item <?= isActive('master-apd', $current_page); ?>">
                    <a class="menu-link" href="master-apd">
                        <div>Stok & Jenis APD</div>
                    </a>
                </li>
                <li class="menu-item <?= isActive('pengajuan-apd', $current_page); ?>">
                    <a class="menu-link" href="pengajuan-apd">
                        <div>Pengajuan APD</div>
                    </a>
                </li>
                <li class="menu-item <?= isActive('transaksi-apd', $current_page); ?>">
                    <a class="menu-link" href="transaksi-apd">
                        <div>Distribusi APD</div>
                    </a>
                </li>
            </ul>
        </li>

    <?php endif; ?>
    <?php if (in_array($user_level, [0, 1, 2])): ?>

        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Informasi</span>
        </li>
        <li class="menu-item <?= isActive('pengumuman', $current_page); ?>">
            <a href="pengumuman" class="menu-link">
                <i class="menu-icon tf-icons bx bx-bullseye"></i>
                <div data-i18n="Broadcast">Broadcast Pengumuman</div>
            </a>
        </li>
    <?php endif; ?>

    <!-- Hanya Tampil jika Admin -->
    <?php if (in_array($user_level, [0])): ?>
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Sistem & Akses</span>
        </li>
        <li class="menu-item <?= isActive('data-users-login', $current_page); ?>">
            <a href="data-users-login" class="menu-link">
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
                    <a class="menu-link" href="maintenance">
                        <div>Maintenance Mode</div>
                    </a>
                </li>
                <li class="menu-item <?= isActive('error', $current_page); ?>">
                    <a class="menu-link" href="error">
                        <div>Log Errors</div>
                    </a>
                </li>
                <li class="menu-item <?= isActive('documentation', $current_page); ?>">
                    <a class="menu-link" href="documentation">
                        <div>Dokumentasi API</div>
                    </a>
                </li>
            </ul>
        </li>
    <?php endif; ?>

</ul>