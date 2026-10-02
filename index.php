<?php
include 'app/config/auth.php';
require_once 'app/config/koneksi.php';
require_once 'app/controller/read.php';

// Inisialisasi query string dengan lebih aman
$queris = [];
parse_str($_SERVER['QUERY_STRING'], $queris);
error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));

/**
 * Routing Configuration
 * Memisahkan logika routing agar mudah dikelola
 */
$page = $_GET['page'] ?? 'home';

// CRITICAL FIX: CEGAT LOGOUT DI SINI SEBELUM HTML DI-RENDER SECARA NATIVE
if ($page === 'logout') {
    require_once 'app/controller/logout.php';
    exit;
}

$routes = [
    // Master Data
    'data-karyawan'   => 'view/master-data/data-karyawan.php',
    'data-karyawan-tereliminasi' => 'view/master-data/data-karyawan-tereliminasi.php',
    'data-site'       => 'view/master-data/data-site.php',
    'data-potongan-tidak-tetap' => 'view/penggajian/data-potongan-tidak-tetap.php',
    'data-departemen' => 'view/master-data/data-departemen.php',
    'karier'          => 'view/master-data/data-karier.php',
    'data-jabatan'    => 'view/master-data/data-jabatan.php',
    'import-karier'   => 'view/master-akses/import-karier.php',
    'data-template-kontrak' => 'view/master-data/data-template-kontrak.php',
    'cetak-kontrak' => 'view/master-data/cetak-kontrak.php',
    'data-bpjs-tk'   => 'view/master-data/data-bpjs-tk.php',
    'data-calon-karyawan' => 'view/master-data/data-calon-karyawan.php',
    'data-loker' => 'view/master-data/data-loker.php',

    // Surat Peringatan
    'master-pelanggaran' => 'view/surat/master-pelanggaran.php',
    'surat-peringatan'   => 'view/surat/surat-peringatan.php',

    // Kehadiran
    'rekapan-absensi' => 'view/kehadiran/rekapan-absensi.php',
    'dayli-absen'     => 'view/kehadiran/dayli-absen.php',
    'pengajuan-izin'  => 'view/kehadiran/pengajuan-izin.php',
    'pengajuan-cuti'  => 'view/kehadiran/pengajuan-cuti.php',
    'bukti-sks'       => 'view/kehadiran/bukti-sks.php',

    // Penggajian & Finance
    'data-periode'    => 'view/finance-payrol/data-periode.php',
    'payroll'         => 'view/penggajian/data-payrol.php',
    // 'slipgajinew'       => 'view/penggajian/slipgajinew.php',
    'data-pengajuan-pinjaman'   => 'view/penggajian/data-pengajuan-pinjaman.php',
    'data-pinjaman'   => 'view/penggajian/data-pinjaman.php',
    'data-rapel'      => 'view/penggajian/data-rapel.php',
    'data-thr'        => 'view/penggajian/data-thr.php',

    // --- ROUTE BARU AKUNTANSI ---
    'data-coa' => 'view/finance-payrol/data-coa.php',
    'kelola-transaksi' => 'view/finance-payrol/kelola-transaksi.php',
    'jurnal-umum'     => 'view/finance-payrol/jurnal-umum.php',
    'buku-besar'      => 'view/finance-payrol/buku-besar.php',
    'neraca-saldo'    => 'view/finance-payrol/neraca-saldo.php',
    'laporan-keuangan' => 'view/finance-payrol/laporan-keuangan.php',

    // APD
    'master-apd'      => 'view/k3/master-apd.php',
    'transaksi-apd'   => 'view/k3/transaksi-apd.php',
    'pengajuan-apd'   => 'view/k3/pengajuan-apd.php',

    // Lainnya
    'pengumuman'      => 'view/broadcast/broadcast.php',
    'data-users-login' => 'view/users.php',

    // Sistem
    'logout'          => 'app/controller/logout.php',
    'home'            => 'view/home.php'
];


// Ambil level user dari session saat login. Jika tidak ada, anggap level 99 (tidak punya akses khusus)
$user_level = $_SESSION['level'] ?? 99;

// 1. Modul HRD Umum (Hanya Master, Direktur, HRD, Supervisor, Admin)
$hrd_pages = [
    'data-karyawan',
    'data-karyawan-tereliminasi',
    'data-site',
    'data-departemen',
    'karier',
    'data-jabatan',
    'import-karier',
    'data-template-kontrak',
    'cetak-kontrak',
    'data-bpjs-tk',
    'data-calon-karyawan',
    'data-loker',
    'rekapan-absensi',
    'dayli-absen',
    'pengajuan-izin',
    'pengajuan-cuti',
    'bukti-sks',
    'pengumuman',
    'master-pelanggaran',
    'surat-peringatan'
];

if (!in_array($user_level, [0, 1, 2, 3, 5]) && in_array($page, $hrd_pages)) {
    echo "<script src=\"https://cdn.jsdelivr.net/npm/sweetalert2@11\"></script><script>Swal.fire({icon:'error',title:'Akses Ditolak!',text:'Khusus Master, Direktur, HRD, Supervisor, dan Admin.',confirmButtonText:'OK'}).then(()=>{window.location.href='?page=home';});</script>";
    exit;
}

// 1.b Modul Finance & Payroll (Hanya Master, Direktur, HRD, Finance)
$finance_pages = [
    'data-periode',
    'payroll',
    'data-pengajuan-pinjaman',
    'data-pinjaman',
    'data-potongan-tidak-tetap',
    'data-rapel',
    'data-thr',
    'laporan-keuangan',
    'kelola-transaksi',
    'data-coa',
    'jurnal-umum',
    'buku-besar',
    'neraca-saldo'
];

if (!in_array($user_level, [0, 1, 2, 6]) && in_array($page, $finance_pages)) {
    echo "<script src=\"https://cdn.jsdelivr.net/npm/sweetalert2@11\"></script><script>Swal.fire({icon:'error',title:'Akses Ditolak!',text:'Modul Keuangan khusus Master, Direktur, HRD, dan Finance.',confirmButtonText:'OK'}).then(()=>{window.location.href='?page=home';});</script>";
    exit;
}

// 2. Modul K3 (Hanya Master, Direktur, Safety, Supervisor)
$k3_pages = ['master-apd', 'transaksi-apd', 'pengajuan-apd'];
if (!in_array($user_level, [0, 1, 3, 4]) && in_array($page, $k3_pages)) {
    echo "<script src=\"https://cdn.jsdelivr.net/npm/sweetalert2@11\"></script><script>Swal.fire({icon:'error',title:'Akses Ditolak!',text:'Khusus tim K3 dan Supervisor.',confirmButtonText:'OK'}).then(()=>{window.location.href='?page=home';});</script>";
    exit;
}

// 3. Modul Sistem & Setup (Hanya Master)
$system_pages = ['data-users-login'];
if (!in_array($user_level, [0]) && in_array($page, $system_pages)) {
    echo "<script src=\"https://cdn.jsdelivr.net/npm/sweetalert2@11\"></script><script>Swal.fire({icon:'error',title:'Akses Ditolak!',text:'Khusus Master Data.',confirmButtonText:'OK'}).then(()=>{window.location.href='?page=home';});</script>";
    exit;
}

// ======= AMBIL DATA USER YANG SEDANG LOGIN =======
$id_session = $_SESSION['id_user'] ?? 0;
$query_active_user = mysqli_query($koneksi, "SELECT nama_user, jabatan, foto FROM tb_user WHERE id_user = '$id_session'");
$active_user = mysqli_fetch_assoc($query_active_user);

$nama_login = $active_user['nama_user'] ?? 'User';
$jabatan_login = $active_user['jabatan'] ?? 'Karyawan';

// Cek foto profil (Gunakan default jika tidak ada/file hilang)
$foto_login = 'assets/img/avatars/1.png';
if (!empty($active_user['foto']) && file_exists('assets/img/user-pic/' . $active_user['foto'])) {
    $foto_login = 'assets/img/user-pic/' . $active_user['foto'];
}
?>

<!DOCTYPE html>
<html lang="en" class="light-style layout-menu-fixed" dir="ltr" data-theme="theme-default" data-assets-path="assets/" data-template="vertical-menu-template-free">

<head>
    <base href="<?= url(); ?>" />
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no" />

    <title>Dashboard | Outsourcing System</title>

    <link rel="icon" type="image/x-icon" href="assets/img/logo/ipm-logo.png" />

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="assets/vendor/fonts/boxicons.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <link rel="stylesheet" href="assets/vendor/css/core.css" class="template-customizer-core-css" />
    <link rel="stylesheet" href="assets/vendor/css/theme-default.css" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="assets/css/demo.css" />
    <link rel="stylesheet" href="assets/css/ipm-theme.css?v=<?= filemtime('assets/css/ipm-theme.css') ?>" />

    <link rel="stylesheet" href="assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />
    <link rel="stylesheet" href="assets/vendor/libs/apex-charts/apex-charts.css" />
    <!-- CDN (single source of truth — jangan load ulang lib ini di view/*.php) -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <!-- CDN JS inti di <head> agar script inline di view/*.php (yang dieksekusi
         saat parse, sebelum footer) selalu dapat jQuery + plugin -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script src="assets/vendor/js/helpers.js"></script>
    <script src="assets/js/config.js"></script>

    <style>
        /* Custom UI Enhancement */
        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        #tabelAbsensi tbody tr:hover {
            background-color: #f8f9fa;
            transform: translateY(-2px);
            transition: all 0.2s ease;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
            cursor: pointer;
        }

        .btn-loading {
            pointer-events: none;
            opacity: 0.6;
        }

        .table-info th {
            background-color: #f0f2f4 !important;
            color: #566a7f !important;
        }

        /* Status Badges */
        .status-hadir {
            color: #28a745;
            font-weight: 600;
        }

        .status-izin {
            color: #ffc107;
            font-weight: 600;
        }

        .status-mangkir {
            color: #dc3545;
            font-weight: 600;
        }

        .status-sakit {
            color: #17a2b8;
            font-weight: 600;
        }

        .status-cuti {
            color: #6f42c1;
            font-weight: 600;
        }

        .blur-loading {
            filter: blur(2px);
            pointer-events: none;
            opacity: 0.6;
        }

        /* Command palette pencarian menu (Ninja Keys) */
        ninja-keys {
            --ninja-accent-color: #1D5C96;
            --ninja-z-index: 9999;
        }

        #navbarMenuSearch {
            cursor: pointer;
        }

        /* Branding Fix */
        .app-brand.demo {
            justify-content: center;
            height: 132px;
            margin-top: 0;
            padding: 10px 0;
        }
        .app-brand-link {
            overflow: hidden;
            max-width: 100%;
        }
        .app-brand-link img {
            padding-left: 0;
            margin: 0 auto;
            max-width: 240px;
            width: auto;
            height: 108px !important;
            max-height: 108px;
            object-fit: contain;
        }

        /* Sidebar menu icons: jangan terpotong */
        .menu-vertical .menu-inner > .menu-item .menu-link {
            overflow: visible;
        }
        .menu-vertical .menu-icon {
            flex-shrink: 0;
            width: 1.5rem;
            text-align: center;
            line-height: 1;
            overflow: visible;
        }
        .menu-vertical .menu-icon.bx,
        .menu-vertical .menu-icon.tf-icons {
            font-size: 1.25rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        /* Mode collapsed: icon tetap di tengah, tidak kepotong */
        .menu-collapsed:not(:hover) .menu-inner > .menu-item > .menu-link {
            padding-left: 0;
            padding-right: 0;
            justify-content: center;
        }
        .menu-collapsed:not(:hover) .menu-inner > .menu-item > .menu-link .menu-icon {
            margin-left: 0;
            margin-right: 0;
            width: 100%;
            text-align: center;
        }

        /* SweetAlert2 harus selalu di paling depan, di atas modal Bootstrap
           (backdrop 1050, modal 1055) dan di atas backdrop-nya sendiri.
           Toast custom (z 99999) tetap di atas Swal agar notif terlihat. */
        .swal2-container {
            z-index: 20000 !important;
        }
    </style>
</head>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">

            <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
                <div class="app-brand demo">
                    <a href="?page=home" class="app-brand-link">
                        <img src="assets/img/logo/ipm-logo.png" alt="Logo TJS">
                    </a>
                    <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-xl-none">
                        <i class="bx bx-chevron-left bx-sm align-middle"></i>
                    </a>
                </div>
                <div class="menu-inner-shadow"></div>
                <?php require_once 'layout/menu.php'; ?>
            </aside>

            <div class="layout-page">

                <nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme" id="layout-navbar">
                    <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
                        <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
                            <i class="bx bx-menu bx-sm"></i>
                        </a>
                    </div>
                    <div class="navbar-nav align-items-xl-center me-3 me-xl-0 d-none d-xl-block"
                        title="Tutup/buka sidebar">
                        <a id="btnSidebarToggle" class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
                            <i class="bx bx-menu bx-sm"></i>
                        </a>
                    </div>

                    <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
                        <div class="navbar-nav align-items-center">
                            <div class="nav-item d-flex align-items-center" title="Klik atau tekan Ctrl+K untuk mencari menu">
                                <i class="bx bx-search fs-4 lh-0"></i>
                                <input type="text" id="navbarMenuSearch" class="form-control border-0 shadow-none" placeholder="Cari menu... (Ctrl+K)" aria-label="Cari menu" readonly />
                            </div>
                        </div>

                        <ul class="navbar-nav flex-row align-items-center ms-auto">
                            <li class="nav-item navbar-dropdown dropdown-user dropdown">
                                <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                                    <div class="avatar avatar-online">
                                        <img src="<?= $foto_login; ?>" alt="Foto Profil" class="w-px-40 h-auto rounded-circle" style="object-fit: cover; aspect-ratio: 1/1;" />
                                    </div>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item" href="#">
                                            <div class="d-flex">
                                                <div class="flex-shrink-0 me-3">
                                                    <div class="avatar avatar-online">
                                                        <img src="<?= $foto_login; ?>" alt="Foto Profil" class="w-px-40 h-auto rounded-circle" style="object-fit: cover; aspect-ratio: 1/1;" />
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <span class="fw-semibold d-block"><?= htmlspecialchars($nama_login); ?></span>
                                                    <small class="text-muted"><?= htmlspecialchars($jabatan_login); ?></small>
                                                </div>
                                            </div>
                                        </a>
                                    </li>
                                    <li>
                                        <div class="dropdown-divider"></div>
                                    </li>
                                    <li><a class="dropdown-item" href="#"><i class="bx bx-user me-2"></i> Profile</a></li>
                                    <li><a class="dropdown-item" href="#"><i class="bx bx-cog me-2"></i> Settings</a></li>
                                    <li>
                                        <div class="dropdown-divider"></div>
                                    </li>
                                    <li><a class="dropdown-item" href="logout"><i class="bx bx-power-off me-2"></i> Log Out</a></li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                </nav>

                <div class="content-wrapper">

                    <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1100;">
                        <div id="toastAlert" class="toast text-bg-primary border-0" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="4000">
                            <div class="d-flex">
                                <div class="toast-body" id="toastMessage">Sedang memproses...</div>
                                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                            </div>
                        </div>
                    </div>

                    <div class="container-xxl flex-grow-1 container-p-y">
                        <?php
                        $file = $routes[$page] ?? $routes['home'];
                        if (file_exists($file)) {
                            include $file;
                        } else {
                            include 'view/home.php';
                        }
                        ?>
                    </div>

                    <footer class="content-footer footer bg-footer-theme">
                        <div class="container-xxl d-flex flex-wrap justify-content-between py-2 flex-md-row flex-column text-center text-md-start">
                            <div class="mb-2 mb-md-0">
                                © <?= date('Y'); ?> | <strong>Outsourcing System</strong>
                            </div>
                            <div>
                                <!-- <a href="https://rianrauf.my.id/" target="_blank" class="footer-link me-4">Developer</a> -->
                                <a href="#" class="footer-link me-4">Developer</a>
                                <a href="#" class="footer-link">Support</a>
                            </div>
                        </div>
                    </footer>

                    <div class="content-backdrop fade"></div>
                </div>
            </div>
        </div>
        <div class="layout-overlay layout-menu-toggle"></div>
    </div>

    <!-- Command palette pencarian menu (Ninja Keys, dibuka via Ctrl+K / klik search) -->
    <ninja-keys id="menuPalette" placeholder="Ketik nama menu..."></ninja-keys>

    <!-- Template lokal (tidak ada padanan CDN-nya): perfect-scrollbar, menu, apexcharts -->
    <script src="assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="assets/vendor/js/menu.js"></script>

    <script src="assets/vendor/libs/apex-charts/apexcharts.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <script src="assets/js/main.js"></script>
    <script src="assets/js/periode.js"></script><!-- getPeriodeBulan()/setDefaultBulan() terpusat -->
    <script>
        // Toggle sidebar mandiri (tidak tergantung assets/js/main.js).
        // vendor Helpers.setCollapsed() rusak untuk desktop (hanya menangani
        // layar kecil), jadi class di-toggle langsung:
        // - 'layout-menu-collapsed' di <html>  -> navbar/footer/konten menyesuaikan
        // - 'menu-collapsed' di #layout-menu   -> lebar sidebar jadi 5.25rem (core.css)
        (function() {
            var KEY = 'tjs-sidebar-collapsed';
            function apply(collapse) {
                document.documentElement.classList.toggle('layout-menu-collapsed', collapse);
                var menu = document.getElementById('layout-menu') || document.querySelector('.layout-menu');
                if (menu) menu.classList.toggle('menu-collapsed', collapse);
                setTimeout(function() { window.dispatchEvent(new Event('resize')); }, 60);
            }
            try {
                if (localStorage.getItem(KEY) === '1') apply(true);
            } catch (err) {}
            var btn = document.getElementById('btnSidebarToggle');
            if (!btn) return;
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                var willCollapse = !document.documentElement.classList.contains('layout-menu-collapsed');
                apply(willCollapse);
                try {
                    localStorage.setItem(KEY, willCollapse ? '1' : '0');
                } catch (err) {}
            });
        })();
    </script>

    <script type="module">
        // Pencarian menu via Ninja Keys (command palette, Ctrl+K).
        // Data aksi dibangun dari DOM sidebar yang ter-render, sehingga
        // otomatis hanya berisi menu yang boleh diakses role user aktif.
        import 'https://unpkg.com/ninja-keys?module';

        function sectionUntukLink(a) {
            const li = a.closest('li.menu-item');
            // Sub-menu: pakai judul grup induknya.
            const sub = a.closest('ul.menu-sub');
            if (sub) {
                const grup = sub.closest('li.menu-item');
                const toggle = grup ? grup.querySelector(':scope > a.menu-link div') : null;
                if (toggle) return toggle.textContent.trim().replace(/\s+/g, ' ');
            }
            // Menu atas: pakai menu-header terdekat di atasnya.
            if (li && li.parentElement) {
                let el = li.previousElementSibling;
                while (el) {
                    if (el.classList && el.classList.contains('menu-header')) {
                        const t = el.querySelector('.menu-header-text');
                        return ((t ? t.textContent : el.textContent) || '').trim();
                    }
                    el = el.previousElementSibling;
                }
            }
            return 'Menu';
        }

        function bangunAksiMenu() {
            const aksi = [];
            document.querySelectorAll('.menu-inner a.menu-link[href]').forEach(a => {
                const href = (a.getAttribute('href') || '').trim();
                if (!href || href.startsWith('javascript')) return; // toggle grup, bukan navigasi
                const judul = (a.textContent || '').trim().replace(/\s+/g, ' ');
                if (!judul) return;
                const ikon = a.querySelector('i.menu-icon');
                const target = (a.getAttribute('target') || '').trim();
                aksi.push({
                    id: href + '|' + judul,
                    title: judul,
                    section: sectionUntukLink(a),
                    keywords: judul,
                    icon: ikon ? `<i class="${ikon.className}"></i>` : undefined,
                    handler: () => {
                        if (target === '_blank') window.open(href, '_blank');
                        else window.location.href = href;
                    },
                });
            });
            return aksi;
        }

        const ninja = document.getElementById('menuPalette');
        if (ninja) {
            ninja.data = bangunAksiMenu();
            const pemicu = document.getElementById('navbarMenuSearch');
            if (pemicu) {
                pemicu.addEventListener('focus', () => {
                    ninja.open();
                    pemicu.blur();
                });
            }
        }
    </script>

    <script>
        $(document).ready(function() {
            // Initialize Select2
            // Catatan: select di dalam .modal dilewati — di-init per-modal
            // dengan dropdownParent agar dropdown tidak tertutup backdrop
            // modal (kelihatan seperti "tidak ada data").
            $('.select-search').not('.modal .select-search').select2({
                placeholder: "Pilih opsi...",
                allowClear: true
            });

            // Auto-init Flatpickr (jika ada input dengan class date-picker)
            if (typeof flatpickr !== "undefined") {
                $(".date-picker").flatpickr();
            }

            // Live Sidebar Menu Search -> digantikan command palette Ninja Keys
            // (lihat modul ninja-keys di bawah: Ctrl+K / klik kolom search navbar).
            // Filter substring manual dihapus agar tidak bentrok dengan palette.
        });

        /**
         * Enhanced Toast System — SINGLE GLOBAL DEFINITION (P1-JS-4).
         * Jangan definisikan ulang window.showToast di view/*.php, pakai yang ini.
         * Mendukung type: success/error/warning/info (+ alias bahasa).
         * Mendukung message string maupun object {message|msg}.
         */
        if (typeof window.__TJS_TOAST_V2__ === 'undefined') {
        window.__TJS_TOAST_V2__ = true;
        window.showToast = function(message, type = "success", position = "top-right", duration = 5000) {
            var allowed = ["success", "error", "warning", "info"];
            var t = String(type || "success").toLowerCase();
            if (t === "warn" || t === "perhatian") t = "warning";
            else if (t === "danger" || t === "gagal" || t === "fail" || t === "failed") t = "error";
            else if (t === "berhasil") t = "success";
            else if (t === "informasi") t = "info";
            if (allowed.indexOf(t) === -1) t = "success";
            type = t;

            if (message === null || message === undefined || message === "") message = "(tanpa pesan)";
            if (typeof message === "object") {
                message = message.message || message.msg || message.text || JSON.stringify(message);
            }
            message = String(message);

            const backgrounds = {
                success: "#71dd37",
                error: "#ff3e1d",
                warning: "#F2921D",
                info: "#2E7BB8"
            };

            const positions = {
                "top-right": {
                    top: "20px",
                    right: "20px"
                },
                "top-left": {
                    top: "20px",
                    left: "20px"
                },
                "bottom-right": {
                    bottom: "20px",
                    right: "20px"
                },
                "bottom-left": {
                    bottom: "20px",
                    left: "20px"
                },
                "top-center": {
                    top: "20px",
                    left: "50%",
                    transform: "translateX(-50%)"
                }
            };

            let wrapperId = `toastWrapper-${position}`;
            let wrapper = document.getElementById(wrapperId);
            if (!wrapper) {
                wrapper = document.createElement("div");
                wrapper.id = wrapperId;
                wrapper.style.cssText = `position:fixed; display:flex; flex-direction:column; gap:10px; z-index:99999; pointer-events:none;`;
                Object.assign(wrapper.style, positions[position]);
                document.body.appendChild(wrapper);
            }

            let toast = document.createElement("div");
            toast.style.cssText = `
                min-width: 300px; padding: 15px; border-radius: 10px; color: white;
                display: flex; align-items: center; justify-content: space-between;
                box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); font-size: 14px;
                opacity: 0; transform: translateY(-20px); transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
                pointer-events: auto; background: ${backgrounds[type] || backgrounds.success};
                position: relative; overflow: hidden;
            `;

            toast.innerHTML = `
                <div style="display:flex; align-items:center;">
                    <span>${message}</span>
                </div>
                <span class="closeToast" style="cursor:pointer; font-weight:bold; padding-left:10px;">✕</span>
                <div class="progress-bar" style="position:absolute; bottom:0; left:0; height:3px; background:rgba(255,255,255,0.5); width:100%;"></div>
            `;

            wrapper.appendChild(toast);
            setTimeout(() => {
                toast.style.opacity = "1";
                toast.style.transform = "translateY(0)";
            }, 10);

            const progressBar = toast.querySelector(".progress-bar");
            let progressInterval = setInterval(() => {
                let currentWidth = parseFloat(progressBar.style.width);
                if (currentWidth <= 0) clearInterval(progressInterval);
                progressBar.style.width = (currentWidth - (100 / (duration / 100))) + "%";
            }, 100);

            const removeToast = () => {
                toast.style.opacity = "0";
                toast.style.transform = "translateX(50px)";
                setTimeout(() => toast.remove(), 400);
            };

            let autoHide = setTimeout(removeToast, duration);
            toast.querySelector(".closeToast").onclick = removeToast;
        };

        // Convenience wrappers + normalisasi res.msg vs res.message (P1-JS-4).
        window.toastSuccess = function(msg) { window.showToast(msg, "success"); };
        window.toastError = function(msg) { window.showToast(msg, "error"); };
        window.toastWarning = function(msg) { window.showToast(msg, "warning"); };
        window.toastInfo = function(msg) { window.showToast(msg, "info"); };
        window.toastRes = function(res, fallback) {
            var msg = (res && (res.message || res.msg)) || fallback || "(tanpa pesan)";
            var st = (res && res.status) || "info";
            if (st !== "success" && st !== "error" && st !== "warning" && st !== "info") st = "info";
            window.showToast(msg, st);
        };

        /**
         * Global confirm dialog pengganti dialog konfirmasi bawaan browser.
         * @param {string} message teks konfirmasi (mendukung \n)
         * @param {object} opts {title, confirmText, cancelText, icon}
         * @returns {Promise<boolean>} true jika user klik Konfirmasi
         */
        window.confirmDialog = function(message, opts) {
            opts = opts || {};
            var text = String(message == null ? "" : message);
            if (typeof Swal !== "undefined") {
                return Swal.fire({
                    icon: opts.icon || "warning",
                    title: opts.title || "Konfirmasi",
                    html: "<pre style=\"text-align:left;white-space:pre-wrap;font-family:inherit;margin:0;\">" + text.replace(/</g, "&lt;") + "</pre>",
                    showCancelButton: true,
                    confirmButtonText: opts.confirmText || "Ya, Lanjutkan",
                    cancelButtonText: opts.cancelText || "Batal",
                    confirmButtonColor: opts.confirmColor || "#dc3545",
                    reverseButtons: true,
                    focusCancel: true
                }).then(function(r) { return !!(r && r.isConfirmed); });
            }
            if (typeof window.showToast === "function") {
                console.warn("Swal belum termuat, fallback konfirmasi native: " + text);
            }
            return Promise.resolve(window["confirm"](text));
        };
        } // end __TJS_TOAST_V2__ guard
    </script>
</body>

</html>