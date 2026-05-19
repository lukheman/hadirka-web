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
$routes = [
    // Master Data
    'data-karyawan'   => 'view/master-data/data-karyawan.php',
    'data-site'       => 'view/master-data/data-site.php',
    'data-tunjangan'  => 'view/master-data/data-tunjangan.php',
    'data-potongan'   => 'view/master-data/data-potongan-tetap.php',
    'data-potongan-tidak-tetap' => 'view/penggajian/data-potongan-tidak-tetap.php',
    'data-departemen' => 'view/master-data/data-departemen.php',
    'karier'          => 'view/master-data/data-karier.php',
    'data-devisi'     => 'view/master-data/data-devisi.php',
    'data-jabatan'    => 'view/master-data/data-jabatan.php',
    'import-karier'   => 'view/master-akses/import-karier.php',
    'data-kontrak'   => 'view/master-data/data-kontrak.php',
    'data-bpjs-tk'   => 'view/master-data/data-bpjs-tk.php',

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
    'laporan-keuangan'=> 'view/finance-payrol/laporan-keuangan.php',
    'kelola-transaksi'=> 'view/finance-payrol/kelola-transaksi.php',

    // APD
    'master-apd'      => 'view/k3/master-apd.php',
    'transaksi-apd'   => 'view/k3/transaksi-apd.php',
    'pengajuan-apd'   => 'view/k3/pengajuan-apd.php',

    // Lainnya
    'data-kandidat'   => 'view/data-kandidat.php',
    'pengumuman'      => 'view/broadcast/broadcast.php',
    'data-users-login' => 'view/users.php',

    // Sistem
    'logout'          => 'app/controller/logout.php',
    'home'            => 'view/home.php'
];

// ======= TAMBAHKAN KODE INI DI BAWAH ARRAY $ROUTES =======
// Ambil level user dari session saat login. Jika tidak ada, anggap level 99 (tidak punya akses khusus)
$user_level = $_SESSION['level'] ?? 99;

// ======= ATURAN KETAT KHUSUS SAFETY (LEVEL 4) =======
if ($user_level == 4) {
    // Halaman yang diizinkan hanya K3 dan Logout
    $safety_allowed_pages = ['master-apd', 'transaksi-apd', 'pengajuan-apd', 'logout'];

    // Jika Safety mencoba buka halaman selain yang di atas (termasuk 'home')
    if (!in_array($page, $safety_allowed_pages)) {
        echo "<script>alert('Akses Ditolak! Anda hanya memiliki akses ke modul K3.'); window.location.href='?page=master-apd';</script>";
        exit;
    }
}

// 1. Modul HRD Umum (Hanya Admin, Direktur, HRD, Supervisor)
$hrd_pages = [
    'data-karyawan', 'data-site', 'data-tunjangan', 'data-potongan', 'data-departemen',
    'karier', 'data-devisi', 'data-jabatan', 'import-karier', 'data-kontrak', 'data-bpjs-tk',
    'rekapan-absensi', 'dayli-absen', 'pengajuan-izin', 'pengajuan-cuti', 'bukti-sks', 'pengumuman'
];

// Level 5 = Supervisor
if (!in_array($user_level, [0, 1, 2, 3]) && in_array($page, $hrd_pages)) {
    echo "<script>alert('Akses Ditolak! Khusus Admin, Direktur, HRD, dan Supervisor.'); window.location.href='?page=home';</script>";
    exit;
}

// 1.b Modul Finance & Payroll (Hanya Admin, Direktur, HRD) - SUPERVISOR DILARANG
$finance_pages = [
    'data-periode', 'payroll', 'data-pengajuan-pinjaman', 'data-pinjaman', 'data-potongan-tidak-tetap', 'data-rapel', 'data-thr', 'slip-gaji'
    , 'laporan-keuangan', 'kelola-transaksi'
];

if (!in_array($user_level, [0, 1, 2]) && in_array($page, $finance_pages)) {
    echo "<script>alert('Akses Ditolak! Modul Keuangan khusus Admin, Direktur, dan HRD.'); window.location.href='?page=home';</script>";
    exit;
}

// 2. Modul K3 (Hanya Admin, Direktur, Safety, Supervisor)
$k3_pages = ['master-apd', 'transaksi-apd', 'pengajuan-apd'];
if (!in_array($user_level, [0, 1, 3, 4]) && in_array($page, $k3_pages)) {
    echo "<script>alert('Akses Ditolak! Khusus tim K3 dan Supervisor.'); window.location.href='?page=home';</script>";
    exit;
}

// 3. Modul Sistem & Setup (Hanya Admin)
$system_pages = ['data-users-login', 'maintenance', 'error', 'documentation', 'data-kandidat'];
if (!in_array($user_level, [0]) && in_array($page, $system_pages)) {
    echo "<script>alert('Akses Ditolak! Khusus Admin.'); window.location.href='?page=home';</script>";
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

    <link rel="icon" type="image/x-icon" href="assets/img/logo/tjs-nobg.png" />

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="assets/vendor/fonts/boxicons.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <link rel="stylesheet" href="assets/vendor/css/core.css" class="template-customizer-core-css" />
    <link rel="stylesheet" href="assets/vendor/css/theme-default.css" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="assets/css/demo.css" />

    <link rel="stylesheet" href="assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />
    <link rel="stylesheet" href="assets/vendor/libs/apex-charts/apex-charts.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

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

        /* Branding Fix */
        .app-brand-link img {
            padding-left: 70px;
            max-width: 180px;
            object-fit: contain;
        }
    </style>
</head>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">

            <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
                <div class="app-brand demo">
                    <a href="?page=home" class="app-brand-link">
                        <img src="assets/img/logo/tjs-done.png" alt="Logo TJS" style="height: 11vh;">
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

                    <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
                        <div class="navbar-nav align-items-center">
                            <div class="nav-item d-flex align-items-center">
                                <i class="bx bx-search fs-4 lh-0"></i>
                                <input type="text" class="form-control border-0 shadow-none" placeholder="Search..." aria-label="Search..." />
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
                                <a href="https://rianrauf.my.id/" target="_blank" class="footer-link me-4">Developer</a>
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

    <script src="assets/vendor/libs/jquery/jquery.js"></script>
    <script src="assets/vendor/libs/popper/popper.js"></script>
    <script src="assets/vendor/js/bootstrap.js"></script>
    <script src="assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="assets/vendor/js/menu.js"></script>

    <script src="assets/vendor/libs/apex-charts/apexcharts.js"></script>
    <script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script src="assets/js/main.js"></script>

    <script>
        $(document).ready(function() {
            // Initialize Select2
            $('.select-search').select2({
                placeholder: "Pilih opsi...",
                allowClear: true
            });

            // Auto-init Flatpickr (jika ada input dengan class date-picker)
            if (typeof flatpickr !== "undefined") {
                $(".date-picker").flatpickr();
            }
        });

        /**
         * Enhanced Toast System
         */
        function showToast(message, type = "success", position = "top-right", duration = 5000) {
            const icons = {
                success: "✅",
                error: "❌",
                warning: "⚠️",
                info: "ℹ️"
            };
            const backgrounds = {
                success: "linear-gradient(135deg, #28a745, #20c997)",
                error: "linear-gradient(135deg, #dc3545, #f86c6b)",
                warning: "linear-gradient(135deg, #ffc107, #ffdb58)",
                info: "linear-gradient(135deg, #17a2b8, #36b9cc)"
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
                <div style="display:flex; align-items:center; gap:12px;">
                    <span style="font-size:20px;">${icons[type]}</span>
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
        }
    </script>
</body>

</html>