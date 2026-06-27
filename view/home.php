<?php $user_level = $_SESSION['level'] ?? 99; ?>
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-lg-12 mb-4">
            <div class="card">
                <div class="d-flex align-items-end row">
                    <div class="col-sm-7">
                        <div class="card-body">
                            <h5 class="card-title text-primary">Selamat Datang, Admin! 🚀</h5>
                            <p class="mb-4">
                                Terus tingkatkan <span class="fw-bold">produktivitas hari ini.</span> Lihat perkembangan data dan aktivitas terbaru di dashboard.
                            </p>
                            <a href="javascript:;" class="btn btn-sm btn-outline-primary">View Badges</a>
                        </div>
                    </div>
                    <div class="col-sm-5 text-center text-sm-left">
                        <div class="card-body pb-0 px-0 px-md-4">
                            <img src="assets/img/illustrations/man-with-laptop-light.png" height="140" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        
        <?php if (in_array($user_level, [0, 1, 2, 6])): ?>
        <div class="col-lg-12 mb-4">
            <div class="row">
                <div class="col-6">
                    <span id="periodeText" class="small" style="font-weight:bold; text-transform: uppercase;">
                        PERIODE — MEMUAT...
                    </span>
                </div>

                <div class="col-6 text-end">
                    <div class="form-group mb-3" style="max-width:250px; float:right;">
                        <select id="periodeDropdown" class="form-select form-select-sm fw-bold cursor-pointer" onchange="loadDashboard(this.value)">
                            <?php
                            // Ambil khusus type_priode = 'slip gaji'
                            $qPeriode = mysqli_query($koneksi, "SELECT * FROM tb_priode WHERE type_priode = 'slip gaji' ORDER BY tgl_mulai DESC");
                            if (mysqli_num_rows($qPeriode) > 0) {
                                while ($rowP = mysqli_fetch_assoc($qPeriode)) {
                                    $tgl_str = date('d/m/Y', strtotime($rowP['tgl_mulai'])) . " - " . date('d/m/Y', strtotime($rowP['tgl_selesai']));
                                    echo "<option value='{$rowP['id_priode']}'>{$tgl_str}</option>";
                                }
                            } else {
                                echo "<option value=''>Belum Ada Periode Slip Gaji</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div class="col-6 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="card-title d-flex align-items-start justify-content-between">
                                <div class="avatar flex-shrink-0">
                                    <img src="assets/img/icons/unicons/paypal.png" alt="Credit Card" class="rounded" />
                                </div>
                            </div>
                            <span class="d-block mb-1">Total Gaji</span>
                            <h3 id="totalGaji" class="card-title mb-2">Rp 0</h3>
                            <small id="percGaji" class="text-secondary fw-semibold"><i class="bx bx-minus"></i> 0% dari periode lalu</small>
                        </div>
                    </div>
                </div>

                <div class="col-6 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="card-title d-flex align-items-start justify-content-between">
                                <div class="avatar flex-shrink-0">
                                    <img src="assets/img/icons/unicons/cc-primary.png" alt="Credit Card" class="rounded" />
                                </div>
                            </div>
                            <span class="fw-semibold d-block mb-1">Pinjaman Karyawan</span>
                            <h3 id="totalPinjaman" class="card-title mb-2">Rp 0</h3>
                            <small id="percPinjaman" class="text-secondary fw-semibold"><i class="bx bx-minus"></i> 0% dari periode lalu</small>
                        </div>
                    </div>
                </div>

                <div class="col-12 mb-0">
                    <div class="card">
                        <div class="card-body">
                            <div class="card-title d-flex align-items-start justify-content-between">
                                <div class="avatar flex-shrink-0">
                                    <img src="assets/img/icons/unicons/cc-success.png" alt="Credit Card" class="rounded" />
                                </div>
                            </div>
                            <span class="fw-semibold d-block mb-1">Total Gaji dan Pinjaman Karyawan</span>
                            <h3 id="totalGajiPinjaman" class="card-title mb-2">Rp 0</h3>
                            <small id="percTotal" class="text-secondary fw-semibold"><i class="bx bx-minus"></i> 0% dari periode lalu</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($user_level != 6): ?>
        <div class="col-lg-12 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between pb-2">
                    <h5 class="card-title m-0 me-2">Statistik Karyawan Aktif</h5>
                    
                    <select id="siteDropdown" class="form-select form-select-sm w-auto cursor-pointer" onchange="loadStatistikKaryawan()">
                        <option value="">Semua Site</option>
                        <?php
                        $qSite = mysqli_query($koneksi, "SELECT id_site, nama_site FROM tb_site ORDER BY nama_site ASC");
                        if ($qSite && mysqli_num_rows($qSite) > 0) {
                            while($rowS = mysqli_fetch_assoc($qSite)){
                                echo "<option value='{$rowS['id_site']}'>{$rowS['nama_site']}</option>";
                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="card-body" id="listKaryawanJabatan">
                    <div class="d-flex justify-content-center align-items-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <span class="ms-2 text-muted">Memuat data statistik...</span>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
    const MONTHS = [
        'JANUARI', 'FEBRUARI', 'MARET', 'APRIL', 'MEI', 'JUNI',
        'JULI', 'AGUSTUS', 'SEPTEMBER', 'OKTOBER', 'NOVEMBER', 'DESEMBER'
    ];

    function formatTanggalIndo(dateStr) {
        let date = new Date(dateStr);
        return `${date.getDate()} ${MONTHS[date.getMonth()]} ${date.getFullYear()}`;
    }

    function updatePercUI(elementId, value) {
        const el = document.getElementById(elementId);
        if (value > 0) {
            el.className = "text-success fw-semibold";
            el.innerHTML = `<i class="bx bx-up-arrow-alt"></i> +${value}% dari periode lalu`;
        } else if (value < 0) {
            el.className = "text-danger fw-semibold";
            el.innerHTML = `<i class="bx bx-down-arrow-alt"></i> ${value}% dari periode lalu`;
        } else {
            el.className = "text-secondary fw-semibold";
            el.innerHTML = `<i class="bx bx-minus"></i> 0% dari periode lalu`;
        }
    }

    // Fungsi modular khusus untuk merender HTML Progress Bar Karyawan
    function renderListKaryawan(karyawanData) {
        const listContainer = document.getElementById("listKaryawanJabatan");
        listContainer.innerHTML = ""; 

        if (karyawanData && karyawanData.length > 0) {
            let totalKaryawan = karyawanData.reduce((sum, item) => sum + item.jumlah, 0);
            
            let htmlList = `
                <div class="mb-4 d-flex justify-content-between align-items-center bg-lighter p-3 rounded">
                    <span class="text-muted fw-semibold">Total Karyawan Aktif</span>
                    <h4 class="mb-0 text-primary fw-bold">${totalKaryawan} <span class="fs-6 text-muted fw-normal">Orang</span></h4>
                </div>
            `;
            
            const colors = ['primary', 'success', 'info', 'warning', 'danger', 'dark'];
            
            karyawanData.forEach((item, index) => {
                let percentage = ((item.jumlah / totalKaryawan) * 100).toFixed(1);
                let color = colors[index % colors.length]; 
                
                htmlList += `
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="d-flex align-items-center gap-2">
                                <i class='bx bx-briefcase text-${color}'></i>
                                <span class="fw-semibold text-heading">${item.jabatan}</span>
                            </div>
                            <div class="text-end">
                                <span class="fw-bold">${item.jumlah}</span>
                                <span class="text-muted small">(${percentage}%)</span>
                            </div>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-${color}" role="progressbar" style="width: ${percentage}%" aria-valuenow="${percentage}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                `;
            });
            listContainer.innerHTML = htmlList;
        } else {
            listContainer.innerHTML = `
                <div class="text-center py-4">
                    <i class='bx bx-user-x text-muted mb-2' style='font-size: 3rem;'></i>
                    <p class="text-muted mb-0">Tidak ada data karyawan di site ini.</p>
                </div>
            `;
        }
    }

    // Fungsi fetch ringan yang HANYA dieksekusi saat Filter Site diubah
    async function loadStatistikKaryawan() {
        const listContainer = document.getElementById("listKaryawanJabatan");
        const idSite = document.getElementById("siteDropdown").value;
        
        listContainer.innerHTML = `
            <div class="d-flex justify-content-center align-items-center py-4">
                <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>
                <span class="ms-2 text-muted">Memfilter data site...</span>
            </div>
        `;

        try {
            // Tambahkan param only_karyawan=1 untuk bypass perhitungan gaji di backend
            const url = `app/controller/get-data/get-home.php?only_karyawan=1&id_site=${idSite}`;
            const res = await fetch(url);
            const data = await res.json();
            
            if (data.status === "success") {
                renderListKaryawan(data.karyawan_per_jabatan);
            }
        } catch (error) {
            console.error("Gagal mengambil data site:", error);
            listContainer.innerHTML = `<p class="text-center text-danger mt-3">Gagal mengambil data.</p>`;
        }
    }

    // Fungsi utama mengambil semua data (Gaji + Karyawan) saat ubah Periode atau pertama kali load
    async function loadDashboard(idPriode = '') {
        // Cek dulu apakah elemennya ada sebelum mengubah teksnya
        if(document.getElementById("totalGaji")) document.getElementById("totalGaji").innerText = "Menghitung...";
        if(document.getElementById("totalPinjaman")) document.getElementById("totalPinjaman").innerText = "Menghitung...";
        if(document.getElementById("totalGajiPinjaman")) document.getElementById("totalGajiPinjaman").innerText = "Menghitung...";

        const idSite = document.getElementById("siteDropdown").value;
        let url = `app/controller/get-data/get-home.php?id_site=${idSite}`;

        // Optimasi parameter API berdasarkan Level User
        // agar backend tidak membebani server dengan menghitung slip gaji.
        <?php if (in_array($user_level, [3, 4, 5])): // Supervisor, Safety, Admin ?>
        url += `&only_karyawan=1`;
        <?php else: // Master, Direktur, HRD, Finance ?>
        if (idPriode) url += `&id_priode=${idPriode}`;
        <?php endif; ?>

        try {
            const res = await fetch(url);
            const data = await res.json();

            if (data.status === "success") {
                // Render list karyawan
                renderListKaryawan(data.karyawan_per_jabatan);

                // Update UI Keuangan HANYA jika elemennya di-render di HTML
                if (document.getElementById("periodeText")) {
                    let dates = data.periode.split(' - ');
                    let textPeriode = data.periode;
                    if (dates.length === 2) {
                        textPeriode = `${formatTanggalIndo(dates[0])} - ${formatTanggalIndo(dates[1])}`;
                    }

                    let totalGaji = Number(data.total_gaji) || 0;
                    let totalPinjaman = Number(data.total_pinjaman) || 0;
                    let totalKeseluruhan = totalGaji + totalPinjaman;

                    document.getElementById("periodeText").innerText = "PERIODE " + textPeriode;
                    document.getElementById("totalGaji").innerText = "Rp " + totalGaji.toLocaleString('id-ID');
                    document.getElementById("totalPinjaman").innerText = "Rp " + totalPinjaman.toLocaleString('id-ID');
                    document.getElementById("totalGajiPinjaman").innerText = "Rp " + totalKeseluruhan.toLocaleString('id-ID');

                    updatePercUI("percGaji", data.perc_gaji);
                    updatePercUI("percPinjaman", data.perc_pinjaman);
                    updatePercUI("percTotal", data.perc_total);
                }
            } else {
                if(document.getElementById("periodeText")) {
                    document.getElementById("periodeText").innerText = "TIDAK ADA PERIODE AKTIF";
                    document.getElementById("totalGaji").innerText = "Rp 0";
                    document.getElementById("totalPinjaman").innerText = "Rp 0";
                    document.getElementById("totalGajiPinjaman").innerText = "Rp 0";
                }
                document.getElementById("listKaryawanJabatan").innerHTML = `<p class="text-center text-muted">Data tidak tersedia.</p>`;
            }
        } catch (error) {
            console.error("Gagal mengambil data:", error);
            alert("Gagal terhubung ke server untuk mengambil statistik.");
        }
    }

    // =========================================================
// FUNGSI MENGAMBIL NOTIFIKASI DARI API GET-HOME-NOTIF.PHP
// =========================================================
async function checkNotification() {
    try {
        const response = await fetch('app/controller/get-data/get-home-notif.php');
        const data = await response.json();

        if (data.show === true) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'info',
                    title: `<h4 class="mb-0 fw-bold text-primary">${data.title}</h4>`,
                    html: data.msg,
                    confirmButtonText: '<i class="bx bx-check-circle" style="vertical-align: middle; margin-right: 5px;"></i> Terima Kasih',
                    buttonsStyling: false,
                    customClass: {
                        confirmButton: 'btn btn-primary px-4 py-2 shadow-sm',
                        popup: 'rounded-3'
                    },
                    backdrop: `rgba(34, 40, 50, 0.6)` // Warna backdrop gelap yang lebih elegan
                });
            } else {
                alert(data.msg.replace(/<br>/g, '\n').replace(/<\/?[^>]+(>|$)/g, ""));
            }
        }
    } catch (error) {
        console.error("Gagal memuat notifikasi:", error);
    }
}

    document.addEventListener("DOMContentLoaded", () => {
        // 1. Load Statistik
        const dropdown = document.getElementById("periodeDropdown");
        // Jika dropdown ada (bukan supervisor), muat dengan value. Jika tidak ada, muat tanpa parameter (akan diselamatkan oleh 'only_karyawan=1')
        if (dropdown && dropdown.options.length > 0) {
            loadDashboard(dropdown.value);
        } else {
            loadDashboard('');
        }

        // 2. Load Notifikasi (Diberi sedikit delay agar dashboard mulus saat loading pertama)
        // PERBAIKAN 2: Sesuaikan pemanggilan dengan nama fungsi di atas
        setTimeout(checkNotification, 500); 
    });
</script>