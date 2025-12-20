<?php
// include 'app/config/auth.php';
require_once 'app/config/koneksi.php';
require_once 'app/controller/read.php';
$queris = array();
parse_str($_SERVER['QUERY_STRING'], $queris);
error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
?>


<!DOCTYPE html>
<html lang="en" class="light-style layout-menu-fixed" dir="ltr" data-theme="theme-default" data-assets-path="assets/" data-template="vertical-menu-template-free">

<head>
    <base href="<?= url(); ?>" />
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

    <title>Dashboard - Analytics | Outsoorsing System</title>

    <meta name="description" content="" />

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="assets/img/logo/tjs-nobg.png" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
        rel="stylesheet" />

    <!-- Icons. Uncomment required icon fonts -->
    <link rel="stylesheet" href="assets/vendor/fonts/boxicons.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Core CSS -->
    <link rel="stylesheet" href="assets/vendor/css/core.css" class="template-customizer-core-css" />
    <link rel="stylesheet" href="assets/vendor/css/theme-default.css" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="assets/css/demo.css" />

    <!-- Vendors CSS -->
    <link rel="stylesheet" href="assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />

    <link rel="stylesheet" href="assets/vendor/libs/apex-charts/apex-charts.css" />

    <!-- DataTable -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/jquery.dataTables.min.css">


    <!-- Page CSS -->

    <!-- Helpers -->
    <script src="assets/vendor/js/helpers.js"></script>

    <script src="assets/js/config.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
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
            transform: scale(1.01);
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .btn-loading {
            pointer-events: none;
            opacity: 0.6;
        }

        /* .card-header {
            background: linear-gradient(90deg, #007bff, #0056b3);
            color: white;
        } */

        .table-info th {
            background-color: #d1ecf1 !important;
            color: #0c5460 !important;
        }

        .status-hadir {
            color: #28a745;
            font-weight: bold;
        }

        .status-izin {
            color: #ffc107;
            font-weight: bold;
        }

        .status-mangkir {
            color: #dc3545;
            font-weight: bold;
        }

        .status-sakit {
            color: #17a2b8;
            font-weight: bold;
        }

        .status-cuti {
            color: #6f42c1;
            font-weight: bold;
        }

        .blur-loading {
            filter: blur(2px);
            pointer-events: none;
            opacity: 0.6;
        }
    </style>
</head>

<body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <!-- Menu -->

            <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
                <div class="app-brand demo">
                    <a href="" class="app-brand-link">
                        <!-- <span class="app-brand-logo demo">
                            <svg
                                width="25"
                                viewBox="0 0 25 42"
                                version="1.1"
                                xmlns="http://www.w3.org/2000/svg"
                                xmlns:xlink="http://www.w3.org/1999/xlink">
                                <defs>
                                    <path
                                        d="M13.7918663,0.358365126 L3.39788168,7.44174259 C0.566865006,9.69408886 -0.379795268,12.4788597 0.557900856,15.7960551 C0.68998853,16.2305145 1.09562888,17.7872135 3.12357076,19.2293357 C3.8146334,19.7207684 5.32369333,20.3834223 7.65075054,21.2172976 L7.59773219,21.2525164 L2.63468769,24.5493413 C0.445452254,26.3002124 0.0884951797,28.5083815 1.56381646,31.1738486 C2.83770406,32.8170431 5.20850219,33.2640127 7.09180128,32.5391577 C8.347334,32.0559211 11.4559176,30.0011079 16.4175519,26.3747182 C18.0338572,24.4997857 18.6973423,22.4544883 18.4080071,20.2388261 C17.963753,17.5346866 16.1776345,15.5799961 13.0496516,14.3747546 L10.9194936,13.4715819 L18.6192054,7.984237 L13.7918663,0.358365126 Z"
                                        id="path-1"></path>
                                    <path
                                        d="M5.47320593,6.00457225 C4.05321814,8.216144 4.36334763,10.0722806 6.40359441,11.5729822 C8.61520715,12.571656 10.0999176,13.2171421 10.8577257,13.5094407 L15.5088241,14.433041 L18.6192054,7.984237 C15.5364148,3.11535317 13.9273018,0.573395879 13.7918663,0.358365126 C13.5790555,0.511491653 10.8061687,2.3935607 5.47320593,6.00457225 Z"
                                        id="path-3"></path>
                                    <path
                                        d="M7.50063644,21.2294429 L12.3234468,23.3159332 C14.1688022,24.7579751 14.397098,26.4880487 13.008334,28.506154 C11.6195701,30.5242593 10.3099883,31.790241 9.07958868,32.3040991 C5.78142938,33.4346997 4.13234973,34 4.13234973,34 C4.13234973,34 2.75489982,33.0538207 2.37032616e-14,31.1614621 C-0.55822714,27.8186216 -0.55822714,26.0572515 -4.05231404e-15,25.8773518 C0.83734071,25.6075023 2.77988457,22.8248993 3.3049379,22.52991 C3.65497346,22.3332504 5.05353963,21.8997614 7.50063644,21.2294429 Z"
                                        id="path-4"></path>
                                    <path
                                        d="M20.6,7.13333333 L25.6,13.8 C26.2627417,14.6836556 26.0836556,15.9372583 25.2,16.6 C24.8538077,16.8596443 24.4327404,17 24,17 L14,17 C12.8954305,17 12,16.1045695 12,15 C12,14.5672596 12.1403557,14.1461923 12.4,13.8 L17.4,7.13333333 C18.0627417,6.24967773 19.3163444,6.07059163 20.2,6.73333333 C20.3516113,6.84704183 20.4862915,6.981722 20.6,7.13333333 Z"
                                        id="path-5"></path>
                                </defs>
                                <g id="g-app-brand" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                    <g id="Brand-Logo" transform="translate(-27.000000, -15.000000)">
                                        <g id="Icon" transform="translate(27.000000, 15.000000)">
                                            <g id="Mask" transform="translate(0.000000, 8.000000)">
                                                <mask id="mask-2" fill="white">
                                                    <use xlink:href="#path-1"></use>
                                                </mask>
                                                <use fill="#696cff" xlink:href="#path-1"></use>
                                                <g id="Path-3" mask="url(#mask-2)">
                                                    <use fill="#696cff" xlink:href="#path-3"></use>
                                                    <use fill-opacity="0.2" fill="#FFFFFF" xlink:href="#path-3"></use>
                                                </g>
                                                <g id="Path-4" mask="url(#mask-2)">
                                                    <use fill="#696cff" xlink:href="#path-4"></use>
                                                    <use fill-opacity="0.2" fill="#FFFFFF" xlink:href="#path-4"></use>
                                                </g>
                                            </g>
                                            <g
                                                id="Triangle"
                                                transform="translate(19.000000, 11.000000) rotate(-300.000000) translate(-19.000000, -11.000000) ">
                                                <use fill="#696cff" xlink:href="#path-5"></use>
                                                <use fill-opacity="0.2" fill="#FFFFFF" xlink:href="#path-5"></use>
                                            </g>
                                        </g>
                                    </g>
                                </g>
                            </svg>
                        </span> -->
                        <span class="app-brand-text demo menu-text fw-bolder ms-2 text-uppercase">TJS</span>
                    </a>

                    <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
                        <i class="bx bx-chevron-left bx-sm align-middle"></i>
                    </a>
                </div>

                <div class="menu-inner-shadow"></div>
                <?php
                require_once 'layout/menu.php';
                ?>
            </aside>
            <!-- / Menu -->

            <!-- Layout container -->
            <div class="layout-page">
                <!-- Navbar -->

                <nav
                    class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme"
                    id="layout-navbar">
                    <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
                        <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
                            <i class="bx bx-menu bx-sm"></i>
                        </a>
                    </div>

                    <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
                        <!-- Search -->
                        <div class="navbar-nav align-items-center">
                            <div class="nav-item d-flex align-items-center">
                                <i class="bx bx-search fs-4 lh-0"></i>
                                <input
                                    type="text"
                                    class="form-control border-0 shadow-none"
                                    placeholder="Search..."
                                    aria-label="Search..." />
                            </div>
                        </div>
                        <!-- /Search -->

                        <ul class="navbar-nav flex-row align-items-center ms-auto">
                            <!-- Place this tag where you want the button to render. -->
                            <li class="nav-item lh-1 me-3">
                                <a
                                    class="github-button"
                                    href="https://github.com/themeselection/sneat-html-admin-template-free"
                                    data-icon="octicon-star"
                                    data-size="large"
                                    data-show-count="true"
                                    aria-label="Star themeselection/sneat-html-admin-template-free on GitHub">Star</a>
                            </li>

                            <!-- User -->
                            <li class="nav-item navbar-dropdown dropdown-user dropdown">
                                <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                                    <div class="avatar avatar-online">
                                        <img src="assets/img/avatars/1.png" alt class="w-px-40 h-auto rounded-circle" />
                                    </div>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item" href="#">
                                            <div class="d-flex">
                                                <div class="flex-shrink-0 me-3">
                                                    <div class="avatar avatar-online">
                                                        <img src="assets/img/avatars/1.png" alt class="w-px-40 h-auto rounded-circle" />
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <span class="fw-semibold d-block">John Doe</span>
                                                    <small class="text-muted">Admin</small>
                                                </div>
                                            </div>
                                        </a>
                                    </li>
                                    <li>
                                        <div class="dropdown-divider"></div>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="#">
                                            <i class="bx bx-user me-2"></i>
                                            <span class="align-middle">My Profile</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="#">
                                            <i class="bx bx-cog me-2"></i>
                                            <span class="align-middle">Settings</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="#">
                                            <span class="d-flex align-items-center align-middle">
                                                <i class="flex-shrink-0 bx bx-credit-card me-2"></i>
                                                <span class="flex-grow-1 align-middle">Billing</span>
                                                <span class="flex-shrink-0 badge badge-center rounded-pill bg-danger w-px-20 h-px-20">4</span>
                                            </span>
                                        </a>
                                    </li>
                                    <li>
                                        <div class="dropdown-divider"></div>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="logout">
                                            <i class="bx bx-power-off me-2"></i>
                                            <span class="align-middle">Log Out</span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <!--/ User -->
                        </ul>
                    </div>
                </nav>

                <!-- / Navbar -->

                <!-- Content wrapper -->
                <div class="content-wrapper">
                    <!-- TOAST -->
                    <div class="toast-container position-fixed bottom-0 end-0 p-3">
                        <div id="toastAlert" class="toast text-bg-primary border-0" role="alert" data-bs-delay="4000">
                            <div class="d-flex">
                                <div class="toast-body" id="toastMessage">Processing...</div>
                                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                            </div>
                        </div>
                    </div>
                    <!-- Content -->
                    <?php
                    switch ($_GET['page']) {
                        // START MASTER DATA 
                        case 'data-karyawan':
                            include 'view/master-data/data-karyawan.php';
                            break;
                        case 'data-site':
                            include 'view/master-data/data-site.php';
                            break;

                        case 'data-tunjangan':
                            include 'view/master-data/data-tunjangan.php';
                            break;
                        case 'data-departemen':
                            include 'view/master-data/data-departemen.php';
                            break;
                        case 'karier':
                            include 'view/master-data/data-karier.php';
                            break;
                        case 'data-devisi':
                            include 'view/master-data/data-devisi.php';
                            break;
                        case 'data-jabatan':
                            include 'view/master-data/data-jabatan.php';
                            break;

                        case 'import-karier':   //  MASTER AKSES
                            include 'view/master-akses/import-karier.php';
                            break;
                        // END DATA MASTER 

                        // START DATA KEHADIRAN 
                        case 'rekapan-absensi':
                            include 'view/kehadiran/rekapan-absensi.php';
                            break;
                        case 'dayli-absen':
                            include 'view/kehadiran/dayli-absen.php';
                            break;
                        // END DATA KEHADIRAN


                        // START PENGGAJIAN 
                        case 'slip-gaji':
                            include 'view/penggajian/slip-gaji.php';
                            break;
                        case 'data-pinjaman':
                            include 'view/penggajian/data-pinjaman.php';
                            break;
                        case 'data-rapel':
                            include 'view/penggajian/data-rapel.php';
                            break;
                        case 'payroll':
                            include 'view/penggajian/data-payrol.php';
                            break;

                        case 'data-kandidat':
                            include 'view/data-kandidat.php';
                            break;


                        case 'data-users-login':
                            include 'view/users.php';
                            break;
                        case 'logout':
                            include 'app/controller/logout.php';
                            break;
                        default:
                            include 'view/home.php';
                            break;
                    }
                    ?>
                    <!-- / Content -->

                    <!-- Footer -->
                    <footer class="content-footer footer bg-footer-theme">
                        <div class="container-xxl d-flex flex-wrap justify-content-between py-2 flex-md-row flex-column">
                            <div class="mb-2 mb-md-0">
                                ©
                                <!-- <script>
                                    document.write(new Date().getFullYear());
                                </script> -->
                                2025
                                , made with by
                                <a href="#" target="_blank" class="footer-link fw-bolder">Outsoorsing</a>
                            </div>
                            <div>
                                <a
                                    href="https://rianrauf.my.id/"
                                    target="_blank"
                                    class="footer-link me-4">RiankSapriandRauf</a>

                                <a
                                    href="#"
                                    target="_blank"
                                    class="footer-link me-4">Support</a>
                            </div>
                        </div>
                    </footer>
                    <!-- / Footer -->

                    <div class="content-backdrop fade"></div>
                </div>
                <!-- Content wrapper -->
            </div>
            <!-- / Layout page -->
        </div>

        <!-- Overlay -->
        <div class="layout-overlay layout-menu-toggle"></div>
    </div>
    <!-- / Layout wrapper -->

    <!-- <div class="buy-now">
        <a
            href="https://themeselection.com/products/sneat-bootstrap-html-admin-template/"
            target="_blank"
            class="btn btn-danger btn-buy-now">Upgrade to Pro</a>
    </div> -->

    <!-- Core JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <!-- build:js assets/vendor/js/core.js -->
    <script src="assets/vendor/libs/jquery/jquery.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="assets/vendor/libs/popper/popper.js"></script>
    <script src="assets/vendor/js/bootstrap.js"></script>
    <script src="assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>

    <script src="assets/vendor/js/menu.js"></script>
    <!-- endbuild -->

    <!-- Vendors JS -->
    <script src="assets/vendor/libs/apex-charts/apexcharts.js"></script>

    <!-- Main JS -->
    <script src="assets/js/main.js"></script>

    <!-- Page JS -->
    <script src="assets/js/dashboards-analytics.js"></script>

    <!-- Place this tag in your head or just before your close body tag. -->
    <!-- <script async defer src="https://buttons.github.io/buttons.js"></script> -->

    <!-- datatables -->
    <script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
    <script>
        function showToast(message, type = "success", position = "top-right", duration = 5000) {
            // ICONS (tetap subtle)
            const icons = {
                success: "✅",
                error: "❌",
                warning: "⚠️",
                info: "ℹ️"
            };

            // POSITIONS (tetap sama)
            const positions = {
                "top-right": {
                    top: "20px",
                    right: "20px",
                    bottom: "auto",
                    left: "auto"
                },
                "top-left": {
                    top: "20px",
                    left: "20px",
                    bottom: "auto",
                    right: "auto"
                },
                "bottom-right": {
                    bottom: "20px",
                    right: "20px",
                    top: "auto",
                    left: "auto"
                },
                "bottom-left": {
                    bottom: "20px",
                    left: "20px",
                    top: "auto",
                    right: "auto"
                },
                "top-center": {
                    top: "20px",
                    left: "50%",
                    transform: "translateX(-50%)",
                    right: "auto",
                    bottom: "auto"
                },
                "bottom-center": {
                    bottom: "20px",
                    left: "50%",
                    transform: "translateX(-50%)",
                    top: "auto",
                    right: "auto"
                }
            };

            // WRAPPER (tetap sama)
            let wrapperId = `toastWrapper-${position}`;
            let wrapper = document.getElementById(wrapperId);
            if (!wrapper) {
                wrapper = document.createElement("div");
                wrapper.id = wrapperId;
                wrapper.style.position = "fixed";
                wrapper.style.display = "flex";
                wrapper.style.flexDirection = "column";
                wrapper.style.gap = "10px";
                wrapper.style.zIndex = "99999";
                wrapper.style.pointerEvents = "none";
                Object.assign(wrapper.style, positions[position]);
                document.body.appendChild(wrapper);
            }

            // TOAST (model lebih sederhana: kurangi border-radius, animasi lebih halus, kurangi efek glassmorphism)
            let toast = document.createElement("div");
            toast.style.minWidth = "280px"; // Sedikit lebih kecil
            toast.style.maxWidth = "380px";
            toast.style.padding = "14px 18px"; // Padding lebih kecil
            toast.style.borderRadius = "8px"; // Lebih kotak, kurangi dari 20px
            toast.style.color = "white";
            toast.style.display = "flex";
            toast.style.alignItems = "center";
            toast.style.justifyContent = "space-between";
            toast.style.boxShadow = "0 4px 12px rgba(0,0,0,0.15)"; // Shadow lebih sederhana, kurangi blur
            toast.style.fontSize = "15px";
            toast.style.fontFamily = "'Segoe UI', Tahoma, Geneva, Verdana, sans-serif";
            toast.style.opacity = "0";
            toast.style.transform = "translateY(-10px)"; // Animasi masuk lebih subtle, tanpa scale
            toast.style.transition = "all 0.4s ease"; // Transisi lebih cepat dan sederhana
            toast.style.backdropFilter = "blur(5px)"; // Kurangi blur untuk efek glassmorphism yang lebih ringan
            toast.style.border = "1px solid rgba(255,255,255,0.1)"; // Border lebih tipis
            toast.style.pointerEvents = "auto";

            // Background (tetap balanced seperti sebelumnya)
            const backgrounds = {
                success: "linear-gradient(135deg, #66bb6a, #4caf50)",
                error: "linear-gradient(135deg, #ef5350, #f44336)",
                warning: "linear-gradient(135deg, #ffb74d, #ff9800)",
                info: "linear-gradient(135deg, #42a5f5, #2196f3)"
            };
            toast.style.background = backgrounds[type] || backgrounds.success;

            // Progress bar (lebih sederhana, tanpa gradient)
            let progressBar = document.createElement("div");
            progressBar.style.position = "absolute";
            progressBar.style.bottom = "0";
            progressBar.style.left = "0";
            progressBar.style.height = "2px"; // Lebih tipis
            progressBar.style.width = "100%";
            progressBar.style.background = "rgba(255,255,255,0.6)"; // Solid color, lebih sederhana
            progressBar.style.borderRadius = "0 0 8px 8px"; // Sesuai border-radius toast
            progressBar.style.transition = "width linear";
            progressBar.style.width = "100%";

            toast.innerHTML = `
        <div style="display:flex; align-items:center; gap:10px; flex:1;">
            <span style="font-size:18px;">${icons[type]}</span>
            <span style="flex:1;">${message}</span>
        </div>
        <button style="
            border:none;
            background:transparent;
            color:white;
            font-size:16px;
            font-weight:bold;
            cursor:pointer;
            opacity:0.7;
            transition:opacity 0.2s;
        " class="closeToast" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.7'">✖</button>
    `;
            toast.appendChild(progressBar);
            toast.style.position = "relative";

            wrapper.appendChild(toast);

            // Animasi masuk (lebih halus)
            setTimeout(() => {
                toast.style.opacity = "1";
                toast.style.transform = "translateY(0)";
            }, 20);

            // Progress bar animation
            let startTime = Date.now();
            let progressInterval = setInterval(() => {
                let elapsed = Date.now() - startTime;
                let remaining = Math.max(0, duration - elapsed);
                progressBar.style.width = `${(remaining / duration) * 100}%`;
            }, 50);

            // Auto-hide
            let autoClose = setTimeout(() => hideToast(), duration);

            // Fungsi hide (animasi keluar lebih sederhana)
            function hideToast() {
                clearInterval(progressInterval);
                toast.style.opacity = "0";
                toast.style.transform = "translateY(-10px)";
                setTimeout(() => toast.remove(), 400);
            }

            // Klik tombol close
            toast.querySelector(".closeToast").addEventListener("click", () => {
                clearTimeout(autoClose);
                clearInterval(progressInterval);
                hideToast();
            });

            // Hover to pause
            toast.addEventListener("mouseenter", () => {
                clearTimeout(autoClose);
                clearInterval(progressInterval);
            });
            toast.addEventListener("mouseleave", () => {
                autoClose = setTimeout(() => hideToast(), duration - (Date.now() - startTime));
                progressInterval = setInterval(() => {
                    let elapsed = Date.now() - startTime;
                    let remaining = Math.max(0, duration - elapsed);
                    progressBar.style.width = `${(remaining / duration) * 100}%`;
                }, 50);
            });
        }

        // CSS tambahan (kurangi efek hover)
        const style = document.createElement('style');
        style.textContent = `
    .closeToast:hover {
        opacity: 1;
    }
`;
        document.head.appendChild(style);
    </script>

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.select-search').select2();
        });
    </script>

</body>

</html>