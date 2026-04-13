<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12 col-md-12 col-lg-12 order-3 order-md-2">
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
            </div>

            <div class="row">
                <div class="col-12 mb-4">
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

        <div class="col-lg-12 mb-4 order-0">
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

    // Fungsi untuk merender UI Persentase Naik/Turun
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

    // Fungsi utama mengambil data berdasarkan id_priode
    async function loadDashboard(idPriode = '') {
        // Tampilkan efek loading sederhana
        document.getElementById("totalGaji").innerText = "Menghitung...";
        document.getElementById("totalPinjaman").innerText = "Menghitung...";
        document.getElementById("totalGajiPinjaman").innerText = "Menghitung...";

        let url = 'app/controller/get-data/get-home.php';
        if (idPriode) {
            url += `?id_priode=${idPriode}`;
        }

        try {
            const res = await fetch(url);
            const data = await res.json();

            if (data.status === "success") {
                // Memformat teks periode
                let dates = data.periode.split(' - ');
                let textPeriode = data.periode;
                if(dates.length === 2) {
                    textPeriode = `${formatTanggalIndo(dates[0])} - ${formatTanggalIndo(dates[1])}`;
                }

                let totalGaji = Number(data.total_gaji) || 0;
                let totalPinjaman = Number(data.total_pinjaman) || 0;
                let totalKeseluruhan = totalGaji + totalPinjaman;

                document.getElementById("periodeText").innerText = "PERIODE " + textPeriode;
                document.getElementById("totalGaji").innerText = "Rp " + totalGaji.toLocaleString('id-ID');
                document.getElementById("totalPinjaman").innerText = "Rp " + totalPinjaman.toLocaleString('id-ID');
                document.getElementById("totalGajiPinjaman").innerText = "Rp " + totalKeseluruhan.toLocaleString('id-ID');
                
                // Update Persentase UI
                updatePercUI("percGaji", data.perc_gaji);
                updatePercUI("percPinjaman", data.perc_pinjaman);
                updatePercUI("percTotal", data.perc_total);

            } else {
                document.getElementById("periodeText").innerText = "TIDAK ADA PERIODE AKTIF";
                document.getElementById("totalGaji").innerText = "Rp 0";
                document.getElementById("totalPinjaman").innerText = "Rp 0";
                document.getElementById("totalGajiPinjaman").innerText = "Rp 0";
            }
        } catch (error) {
            console.error("Gagal mengambil data:", error);
            alert("Gagal terhubung ke server untuk mengambil statistik.");
        }
    }

    // Jalankan fungsi saat halaman pertama kali di-load
    // Mengambil value dari opsi pertama di dropdown secara otomatis
    document.addEventListener("DOMContentLoaded", () => {
        const dropdown = document.getElementById("periodeDropdown");
        if(dropdown.options.length > 0) {
            loadDashboard(dropdown.value);
        } else {
            loadDashboard('');
        }
    });
</script>