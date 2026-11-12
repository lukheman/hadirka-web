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
    <!-- Tampil untuk semua role -->
    <?php if (in_array($user_level, [0, 1, 2, 3, 4, 5, 6])): ?>
        <li class="menu-item <?= isActive('home', $current_page); ?>">
            <a href="?page=home" class="menu-link">
                <i class="menu-icon tf-icons bx bx-home-circle"></i>
                <div data-i18n="Analytics">Dashboard</div>
            </a>
        </li>
    <?php endif; ?>

    <!-- Hanya Tampil jika Master, Direktur, HRD, Supervisor, Admin -->
    <?php if (in_array($user_level, [0, 1, 2, 3, 5])): ?>
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Data Utama</span>
        </li>

        <?php
        $master_pages = ['data-karyawan', 'data-karyawan-tereliminasi', 'karier', 'import-karier', 'data-site', 'data-departemen', 'data-jabatan', 'data-tipe-karyawan', 'data-template-kontrak', 'data-arsip-kontrak', 'cetak-kontrak', 'surat-pengalaman-kerja', 'data-bpjs-tk'];
        ?>
        <li class="menu-item <?= isMenuOpen($master_pages, $current_page); ?>" data-menu="master">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-collection"></i>
                <div data-i18n="Master Data">Master Data</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item <?= isActive('data-karyawan', $current_page); ?>">
                    <a class="menu-link" href="?page=data-karyawan">
                        <div>Data Karyawan</div>
                    </a>
                </li>
                <li class="menu-item <?= isActive('data-tipe-karyawan', $current_page); ?>">
                    <a class="menu-link" href="?page=data-tipe-karyawan">
                        <div>Tipe Karyawan</div>
                    </a>
                </li>
                <li class="menu-item <?= isActive('data-karyawan-tereliminasi', $current_page); ?>">
                    <a class="menu-link" href="?page=data-karyawan-tereliminasi">
                        <div>Karyawan Tereliminasi</div>
                    </a>
                </li>
                <li class="menu-item <?= isActive('import-karier', $current_page); ?>">
                    <a class="menu-link" href="?page=import-karier">
                        <div>Data Karier</div>
                    </a>
                </li>
                <li class="menu-item <?= isActive('data-site', $current_page); ?>">
                    <a class="menu-link" href="?page=data-site">
                        <div>Data Site (Lokasi)</div>
                    </a>
                </li>
                <li class="menu-item <?= isActive('data-departemen', $current_page); ?>">
                    <a class="menu-link" href="?page=data-departemen">
                        <div>Data Departemen</div>
                    </a>
                </li>
                <li class="menu-item <?= isActive('data-jabatan', $current_page); ?>">
                    <a class="menu-link" href="?page=data-jabatan">
                        <div>Data Jabatan</div>
                    </a>
                </li>
                <li class="menu-item <?= isActive('data-template-kontrak', $current_page); ?>">
                    <a class="menu-link" href="?page=data-template-kontrak">
                        <div>Template Kontrak</div>
                    </a>
                </li>
                <li class="menu-item <?= isActive('data-arsip-kontrak', $current_page); ?>">
                    <a class="menu-link" href="?page=data-arsip-kontrak">
                        <div>Arsip Kontrak</div>
                    </a>
                </li>
                <li class="menu-item <?= isActive('cetak-kontrak', $current_page); ?>">
                    <a class="menu-link" href="?page=cetak-kontrak">
                        <div>Cetak Kontrak</div>
                    </a>
                </li>
                <li class="menu-item <?= isActive('surat-pengalaman-kerja', $current_page); ?>">
                    <a class="menu-link" href="?page=surat-pengalaman-kerja">
                        <div>Surat Pengalaman Kerja</div>
                    </a>
                </li>
                <li class="menu-item <?= isActive('data-bpjs-tk', $current_page); ?>">
                    <a class="menu-link" href="?page=data-bpjs-tk">
                        <div>Data BPJS Ketenagakerjaan</div>
                    </a>
                </li>
            </ul>
        </li>

        <?php
        $master_pages = ['data-loker', 'data-calon-karyawan'];
        ?>
        <li class="menu-item <?= isMenuOpen($master_pages, $current_page); ?>" data-menu="rekruitmen">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-collection"></i>
                <div data-i18n="Master Data">Rekruitmen</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item <?= isActive('data-loker', $current_page); ?>">
                    <a class="menu-link" href="?page=data-loker">
                        <div>Data Loker</div>
                    </a>
                </li>
                <li class="menu-item <?= isActive('data-calon-karyawan', $current_page); ?>">
                    <a class="menu-link" href="?page=data-calon-karyawan">
                        <div>Data Calon Karyawan</div>
                    </a>
                </li>
            </ul>
        </li>

        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Manajemen Indisipliner</span>
        </li>

        <?php $indisipliner_pages = ['master-pelanggaran', 'surat-peringatan']; ?>
        <li class="menu-item <?= isMenuOpen($indisipliner_pages, $current_page); ?>" data-menu="indisipliner">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-error-alt"></i>
                <div data-i18n="Indisipliner">Sanksi & Indisipliner</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item <?= isActive('master-pelanggaran', $current_page); ?>">
                    <a class="menu-link" href="?page=master-pelanggaran">
                        <div>Master Pelanggaran</div>
                    </a>
                </li>
                <li class="menu-item <?= isActive('surat-peringatan', $current_page); ?>">
                    <a class="menu-link" href="?page=surat-peringatan">
                        <div>Kelola Surat Peringatan</div>
                    </a>
                </li>
            </ul>
        </li>


        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Presensi & Izin</span>
        </li>

        <?php
        $absensi_pages = ['dayli-absen', 'rekapan-absensi', 'absen-mobile', 'pengajuan-izin', 'pengajuan-cuti', 'bukti-sks'];
        ?>
        <li class="menu-item <?= isMenuOpen($absensi_pages, $current_page); ?>" data-menu="absensi">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-calendar-check"></i>
                <div data-i18n="Manajemen Absensi">Manajemen Absensi</div>
            </a>
            <ul class="menu-sub">
                <!-- DISEMBUNYIKAN: Absensi Harian
                <li class="menu-item <?= isActive('dayli-absen', $current_page); ?>">
                    <a class="menu-link" href="?page=dayli-absen">
                        <div>Absensi Harian</div>
                    </a>
                </li>
                -->
                <li class="menu-item <?= isActive('rekapan-absensi', $current_page); ?>">
                    <a class="menu-link" href="?page=rekapan-absensi">
                        <div>Rekapitulasi Bulanan</div>
                    </a>
                </li>
                <li class="menu-item <?= isActive('absen-mobile', $current_page); ?>">
                    <a class="menu-link" href="?page=absen-mobile">
                        <div>Absensi Mobile (GPS)</div>
                    </a>
                </li>
                <li class="menu-item <?= isActive('pengajuan-izin', $current_page); ?>">
                    <a class="menu-link" href="?page=pengajuan-izin">
                        <div>Pengajuan Izin</div>
                    </a>
                </li>
                <li class="menu-item <?= isActive('pengajuan-cuti', $current_page); ?>">
                    <a class="menu-link" href="?page=pengajuan-cuti">
                        <div>Pengajuan Cuti</div>
                    </a>
                </li>
                <li class="menu-item <?= isActive('bukti-sks', $current_page); ?>">
                    <a class="menu-link" href="?page=bukti-sks">
                        <div>Data SKS</div>
                    </a>
                </li>
            </ul>
        </li>

    <?php endif; ?>

    <!-- Hanya Tampil jika Master, Direktur, HRD, Finance -->
    <?php if (in_array($user_level, [0, 1, 2, 6])): ?>
        <?php if ($user_level == 6): ?>
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Data Karyawan</span>
        </li>
        <li class="menu-item <?= isActive('data-karyawan', $current_page); ?>">
            <a href="?page=data-karyawan" class="menu-link">
                <i class="menu-icon tf-icons bx bx-user"></i>
                <div data-i18n="Data Karyawan">Data Karyawan</div>
            </a>
        </li>
        <?php endif; ?>
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Finance & Payroll</span>
        </li>

        <?php
        $payroll_pages = ['data-periode', 'payroll', 'pengajuan-pinjaman', 'data-thr'];
        ?>
        <li class="menu-item <?= isMenuOpen($payroll_pages, $current_page); ?>" data-menu="payroll">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-money"></i>
                <div data-i18n="Payroll System">Payroll System</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item <?= isActive('data-periode', $current_page); ?>">
                    <a class="menu-link" href="?page=data-periode">
                        <div>Periode Penggajian</div>
                    </a>
                </li>
                <li class="menu-item <?= isActive('payroll', $current_page); ?>">
                    <a class="menu-link" href="?page=payroll">
                        <div>Proses Gaji</div>
                    </a>
                </li>
                <li class="menu-item <?= isActive('slip-gaji', $current_page); ?>">
                    <a class="menu-link" href="slipgajinew.php" target="_blank">
                        <div>Slip Gaji Karyawan</div>
                    </a>
                </li>
                <li class="menu-item <?= isActive('pengajuan-pinjaman', $current_page); ?>">
                    <a class="menu-link" href="?page=pengajuan-pinjaman">
                        <div>Pengajuan Pinjaman</div>
                    </a>
                </li>
                <!-- DISEMBUNYIKAN (tabel di-drop pada penyederhanaan gaji Okt 2026)
                <li class="menu-item <?= isActive('data-pengajuan-pinjaman', $current_page); ?>">
                    <a class="menu-link" href="?page=data-pengajuan-pinjaman">
                        <div>Pengajuan Pinjaman Dana</div>
                    </a>
                </li>
                <li class="menu-item <?= isActive('data-pinjaman', $current_page); ?>">
                    <a class="menu-link" href="?page=data-pinjaman">
                        <div>Pinjaman Dana</div>
                    </a>
                </li>
                -->
                <!-- DISEMBUNYIKAN (penyederhanaan gaji Okt 2026: hanya hadir x gaji pokok)
                <li class="menu-item <?= isActive('data-potongan-tidak-tetap', $current_page); ?>">
                    <a class="menu-link" href="?page=data-potongan-tidak-tetap">
                        <div>Potongan Tidak Tetap</div>
                    </a>
                </li>
                <li class="menu-item <?= isActive('data-rapel', $current_page); ?>">
                    <a class="menu-link" href="?page=data-rapel">
                        <div>Kalkulasi Rapel</div>
                    </a>
                </li>
                -->
                <li class="menu-item <?= isActive('data-thr', $current_page); ?>">
                    <a class="menu-link" href="?page=data-thr">
                        <div>Sub THR Karyawan</div>
                    </a>
                </li>
            </ul>
        </li>

        <?php
        // DISEMBUNYIKAN (Okt 2026): Fitur Manajemen Keuangan disembunyikan sesuai permintaan.
        // REVISI: Gabungkan sub-menu baru ke variabel deteksi accordion open
        $finance_pages = ['data-coa', 'kelola-transaksi', 'jurnal-umum', 'buku-besar', 'neraca-saldo', 'laporan-keuangan'];
        ?>
        <?php if (false): // Manajemen Keuangan disembunyikan ?>
        <li class="menu-item <?= isMenuOpen($finance_pages, $current_page); ?>" data-menu="finance">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-spreadsheet"></i>
                <div data-i18n="Manajemen Keuangan">Manajemen Keuangan</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item <?= isActive('kelola-transaksi', $current_page); ?>">
                    <a class="menu-link" href="?page=kelola-transaksi">
                        <div>Kelola Transaksi</div>
                    </a>
                </li>
                <li class="menu-item <?= isActive('jurnal-umum', $current_page); ?>">
                    <a class="menu-link" href="?page=jurnal-umum">
                        <div>Jurnal Umum</div>
                    </a>
                </li>
                <li class="menu-item <?= isActive('buku-besar', $current_page); ?>">
                    <a class="menu-link" href="?page=buku-besar">
                        <div>Buku Besar</div>
                    </a>
                </li>
                <li class="menu-item <?= isActive('neraca-saldo', $current_page); ?>">
                    <a class="menu-link" href="?page=neraca-saldo">
                        <div>Neraca Saldo</div>
                    </a>
                </li>
                <li class="menu-item <?= isActive('laporan-keuangan', $current_page); ?>">
                    <a class="menu-link" href="?page=laporan-keuangan">
                        <div>Laporan Laba Rugi</div>
                    </a>
                </li>
            </ul>
        </li>
        <?php endif; // end sembunyi Manajemen Keuangan ?>

    <?php endif; ?>

    <!-- Hanya Tampil jika Master, Direktur, Safety, Supervisor  -->
    <?php if (in_array($user_level, [0, 1, 3, 4])): ?>
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Inventaris K3</span>
        </li>

        <?php
        $apd_pages = ['master-apd', 'transaksi-apd', 'pengajuan-apd'];
        ?>
        <li class="menu-item <?= isMenuOpen($apd_pages, $current_page); ?>" data-menu="apd">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-shield-quarter"></i>
                <div data-i18n="Manajemen APD">Manajemen APD</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item <?= isActive('master-apd', $current_page); ?>">
                    <a class="menu-link" href="?page=master-apd">
                        <div>Stok & Jenis APD</div>
                    </a>
                </li>
                <li class="menu-item <?= isActive('pengajuan-apd', $current_page); ?>">
                    <a class="menu-link" href="?page=pengajuan-apd">
                        <div>Pengajuan APD</div>
                    </a>
                </li>
                <li class="menu-item <?= isActive('transaksi-apd', $current_page); ?>">
                    <a class="menu-link" href="?page=transaksi-apd">
                        <div>Distribusi APD</div>
                    </a>
                </li>
            </ul>
        </li>

    <?php endif; ?>

    <!-- Hanya Tampil jika Master, Direktur, HRD, Supervisor, Admin  -->
    <?php if (in_array($user_level, [0, 1, 2, 3, 5])): ?>

        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Informasi</span>
        </li>
        <li class="menu-item <?= isActive('pengumuman', $current_page); ?>">
            <a href="?page=pengumuman" class="menu-link">
                <i class="menu-icon tf-icons bx bx-bullseye"></i>
                <div data-i18n="Broadcast">Broadcast Pengumuman</div>
            </a>
        </li>
        <li class="menu-item <?= isActive('notifikasi-app', $current_page); ?>">
            <a href="?page=notifikasi-app" class="menu-link">
                <i class="menu-icon tf-icons bx bx-bell"></i>
                <div data-i18n="Notifikasi">Notifikasi Aplikasi</div>
            </a>
        </li>
    <?php endif; ?>

    <!-- Hanya Tampil jika Admin -->
    <?php if (in_array($user_level, [0])): ?>
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
        <li class="menu-item <?= isMenuOpen($util_pages, $current_page); ?>" data-menu="utility">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-cog"></i>
                <div data-i18n="Utility">System Utility</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item <?= isActive('maintenance', $current_page); ?>">
                    <a class="menu-link" href="?page=maintenance">
                        <div>Maintenance Mode</div>
                    </a>
                </li>
                <li class="menu-item <?= isActive('error', $current_page); ?>">
                    <a class="menu-link" href="?page=error">
                        <div>Log Errors</div>
                    </a>
                </li>
                <li class="menu-item <?= isActive('documentation', $current_page); ?>">
                    <a class="menu-link" href="?page=documentation">
                        <div>Dokumentasi API</div>
                    </a>
                </li>
            </ul>
        </li>
    <?php endif; ?>

</ul>
