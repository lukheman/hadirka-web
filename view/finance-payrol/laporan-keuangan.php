<?php
// app/views/laporan-keuangan.php
require_once __DIR__ . '/../../app/config/koneksi.php';
?>

<style>
    /* Styling Kartu */
    .card-laporan {
        border: none;
        border-radius: 12px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        transition: transform 0.2s ease-in-out;
    }

    .card-laporan:hover {
        transform: translateY(-5px);
    }

    .card-header-laporan {
        background-color: #fff;
        border-bottom: 1px solid #f0f2f5;
        border-top-left-radius: 12px !important;
        border-top-right-radius: 12px !important;
        font-weight: 600;
        padding: 1rem 1.25rem;
    }

    /* Ikon pada Widget Rangkuman */
    .icon-shape {
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
    }

    /* Wrapper untuk Canvas Grafik */
    .chart-container {
        position: relative;
        height: 300px;
        width: 100%;
    }
</style>

<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header & Action Buttons -->
    <div class="row align-items-center mb-4">
        <div class="col-md-6 mb-3 mb-md-0">
            <h4 class="mb-1 fw-bold">Laporan Keuangan</h4>
            <p class="text-muted mb-1">Project Manpower Supply (IPIP HBB)</p>
            <p class="text-muted mb-0">Periode: <span id="ui_periode" class="fw-bold text-primary">MEMUAT...</span></p>
        </div>

        <div class="col-md-6 text-md-end">
            <div class="d-flex flex-column flex-sm-row justify-content-md-end align-items-sm-center gap-2">
                <!-- Filter Dropdown AJAX -->
                <select id="periodeDropdown" class="form-select form-select-sm fw-bold cursor-pointer bg-white shadow-sm" onchange="loadLaporan(this.value)" style="width: auto; min-width: 200px;">
                    <?php
                    $bulanIndo = array("Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember");

                    $bulan_options = [];
                    $tanggal_bulan = new DateTime('first day of this month');
                    for ($i = 0; $i < 12; $i++) {
                        $value = $tanggal_bulan->format('Y-m');
                        $bulan_options[$value] = $bulanIndo[(int)$tanggal_bulan->format('m') - 1]
                            . ' ' . $tanggal_bulan->format('Y');
                        $tanggal_bulan->modify('-1 month');
                    }
                    foreach ($bulan_options as $value => $label) {
                        $selected = array_key_first($bulan_options) === $value ? ' selected' : '';
                        echo '<option value="' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '"' . $selected . '>'
                            . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</option>';
                    }
                    ?>
                </select>

                <!-- Grup Tombol Aksi -->
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-outline-primary shadow-sm w-100 text-nowrap"><i class="bx bx-printer me-1"></i> Cetak</button>
                    <button class="btn btn-sm btn-primary shadow-sm w-100 text-nowrap"><i class="bx bx-download me-1"></i> Export Excel</button>
                </div>
            </div>
        </div>
    </div>

    <!-- TATA LETAK GRAFIK (CHART) -->
    <div class="row g-4 mb-4">
        <!-- Grafik Pendapatan -->
        <div class="col-lg-5">
            <div class="card card-laporan h-100">
                <div class="card-header card-header-laporan d-flex align-items-center">
                    <i class="bx bx-pie-chart-alt-2 text-primary me-2 fs-5"></i> Rincian Pendapatan
                </div>
                <div class="card-body mt-3">
                    <div class="chart-container">
                        <canvas id="chartPendapatan"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Grafik Pengeluaran (Beban) -->
        <div class="col-lg-7">
            <div class="card card-laporan h-100">
                <div class="card-header card-header-laporan d-flex align-items-center">
                    <i class="bx bx-bar-chart-alt-2 text-danger me-2 fs-5"></i> Rincian Pengeluaran Terbanyak
                </div>
                <div class="card-body mt-3">
                    <div class="chart-container">
                        <canvas id="chartBeban"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Widget Rangkuman 4 Card (Dinamis) -->
    <div class="row g-4 mb-4">
        <div class="col-md-6 col-12">
            <div class="card card-laporan h-100">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <p class="text-sm mb-1 text-muted fw-semibold">Total Pendapatan</p>
                            <h5 id="ui_w_pendapatan" class="font-weight-bolder mb-0">Rp 0</h5>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon-shape bg-primary text-white shadow-sm d-inline-flex"><i class="bx bx-wallet fs-4"></i></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-12">
            <div class="card card-laporan h-100">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <p class="text-sm mb-1 text-muted fw-semibold">Total Beban</p>
                            <h5 id="ui_w_beban" class="font-weight-bolder mb-0 text-danger">Rp 0</h5>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon-shape bg-danger text-white shadow-sm d-inline-flex"><i class="bx bx-trending-down fs-4"></i></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-12">
            <div class="card card-laporan h-100 border-success border-bottom border-3">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <p class="text-sm mb-1 text-muted fw-semibold">Laba Bersih</p>
                            <h5 id="ui_w_laba" class="font-weight-bolder mb-0 text-success">Rp 0</h5>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon-shape bg-success text-white shadow-sm d-inline-flex"><i class="bx bx-line-chart fs-4"></i></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-12">
            <div class="card card-laporan h-100">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <p class="text-sm mb-1 text-muted fw-semibold">Total Kas & Bank</p>
                            <h5 id="ui_w_saldo" class="font-weight-bolder mb-0 text-info">Rp 0</h5>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon-shape bg-info text-white shadow-sm d-inline-flex"><i class="bx bxs-bank fs-4"></i></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Layout Tabel Rincian -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card card-laporan h-100">
                <div class="card-header card-header-laporan d-flex align-items-center">
                    <i class="bx bx-balance text-primary me-2 fs-5"></i> Laporan Laba Rugi
                </div>
                <div class="card-body mt-3">
                    <div class="table-responsive">
                        <table class="table table-borderless align-middle mb-0">
                            <tr>
                                <td colspan="2" class="text-primary fw-bold pt-3 pb-2"><i class="bx bx-down-arrow-alt me-1"></i> PENDAPATAN</td>
                            </tr>
                            <tr>
                                <td class="ps-4">Total Pemasukan / Pendapatan</td>
                                <td id="ui_t_pendapatan_1" class="text-end fw-semibold">Rp 0</td>
                            </tr>
                            <tr class="table-light border-bottom">
                                <td class="ps-4 fw-bold text-muted">Total Pendapatan</td>
                                <td id="ui_t_pendapatan_2" class="text-end fw-bold text-dark">Rp 0</td>
                            </tr>

                            <tr>
                                <td colspan="2" class="text-danger fw-bold pt-4 pb-2"><i class="bx bx-up-arrow-alt me-1"></i> BEBAN PENGELUARAN</td>
                            </tr>
                            <tr>
                                <td class="ps-4">Biaya Operasional & Lapangan</td>
                                <td id="ui_t_operasional" class="text-end fw-semibold">Rp 0</td>
                            </tr>
                            <tr>
                                <td class="ps-4">Gaji & Rapel Karyawan <span class="badge rounded-pill bg-secondary ms-1"><i class="bx bx-bot me-1"></i>Sistem HR</span></td>
                                <td id="ui_t_gaji" class="text-end fw-semibold">Rp 0</td>
                            </tr>
                            <tr class="table-light border-bottom">
                                <td class="ps-4 fw-bold text-muted">Total Beban</td>
                                <td id="ui_t_beban" class="text-end fw-bold text-dark">Rp 0</td>
                            </tr>
                        </table>
                    </div>

                    <div class="p-3 bg-success bg-opacity-10 rounded-3 mt-3 d-flex justify-content-between align-items-center border border-success">
                        <h6 class="mb-0 text-white fw-bold text-success">LABA BERSIH BULAN INI</h6>
                        <h5 id="ui_t_laba" class="mb-0 text-white fw-bolder text-success">Rp 0</h5>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Baris 3 Kartu Berjejer -->
    <div class="row g-4">
        <!-- Kolom 1: Rincian Kas Bank -->
        <div class="col-lg-4 col-md-6">
            <div class="card card-laporan h-100">
                <div class="card-header card-header-laporan d-flex align-items-center">
                    <i class="bx bx-wallet-alt text-success me-2 fs-5"></i> Rincian Saldo Bank
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush m-0" id="ui_rincian_bank">
                        <li class="list-group-item text-center p-3 text-muted">Memuat data bank...</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Kolom 2: Arus Kas -->
        <div class="col-lg-4 col-md-6">
            <div class="card card-laporan h-100">
                <div class="card-header card-header-laporan d-flex align-items-center">
                    <i class="bx bx-transfer-alt text-warning me-2 fs-5"></i> Pergerakan Arus Kas
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush m-0">
                        <li class="list-group-item d-flex justify-content-between align-items-center p-3 border-0 border-bottom">
                            <span class="text-muted">Saldo Awal Periode</span>
                            <span id="ui_k_awal" class="fw-semibold">Rp 0</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center p-3 border-0 border-bottom">
                            <span><i class="bx bx-plus-circle text-success me-2"></i>Uang Masuk</span>
                            <span id="ui_k_masuk" class="text-success fw-bold">+ Rp 0</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center p-3 border-0 border-bottom">
                            <span><i class="bx bx-minus-circle text-danger me-2"></i>Uang Keluar</span>
                            <span id="ui_k_keluar" class="text-danger fw-bold">- Rp 0</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center p-3 bg-light border-0">
                            <span class="fw-bold">Total Saldo di Akhir Bulan</span>
                            <span id="ui_k_akhir" class="fw-bold fs-5 text-dark">Rp 0</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Kolom 3: Pantauan Kasbon -->
        <div class="col-lg-4 col-md-12">
            <div class="card card-laporan h-100">
                <div class="card-header card-header-laporan d-flex align-items-center">
                    <i class="bx bx-group text-info me-2 fs-5"></i> Pantauan Kasbon
                </div>
                <div class="card-body mt-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-sm">Pencairan Kasbon Baru</span>
                        <span id="ui_p_cair" class="text-dark fw-semibold">Rp 0</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-sm">Cicilan Masuk (Potong)</span>
                        <span id="ui_p_cicil" class="text-success fw-semibold">- Rp 0</span>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-info" role="progressbar" style="width: 75%"></div>
                    </div>
                    <div class="d-flex justify-content-between mt-3 pt-3 border-top">
                        <span class="fw-bold">Total Piutang Berjalan</span>
                        <span id="ui_p_piutang" class="fw-bold text-primary">Rp 0</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Masukkan CDN Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- JAVASCRIPT AJAX FETCH -->
<script>
    // Inisialisasi variabel untuk Chart
    let chartPendapatanInstance = null;
    let chartBebanInstance = null;

    async function loadLaporan(idPriode) {
        document.getElementById('ui_periode').innerText = "Menghitung...";

        try {
            const url = `app/controller/get-data/get-laporan-keuangan.php?id_priode=${idPriode}`;
            const res = await fetch(url);
            const data = await res.json();

            if (data.status === 'success') {
                // Update Teks Periode
                document.getElementById('ui_periode').innerText = data.teks_periode;

                // Update Widget Atas
                document.getElementById('ui_w_pendapatan').innerText = 'Rp ' + data.pendapatan.toLocaleString('id-ID');
                document.getElementById('ui_w_beban').innerText = 'Rp ' + data.total_beban.toLocaleString('id-ID');
                document.getElementById('ui_w_laba').innerText = 'Rp ' + data.laba_bersih.toLocaleString('id-ID');
                document.getElementById('ui_w_saldo').innerText = 'Rp ' + data.saldo_bank.toLocaleString('id-ID');

                // Update Laba Rugi
                document.getElementById('ui_t_pendapatan_1').innerText = 'Rp ' + data.pendapatan.toLocaleString('id-ID');
                document.getElementById('ui_t_pendapatan_2').innerText = 'Rp ' + data.pendapatan.toLocaleString('id-ID');
                document.getElementById('ui_t_operasional').innerText = 'Rp ' + data.operasional.toLocaleString('id-ID');
                document.getElementById('ui_t_gaji').innerText = 'Rp ' + data.gaji_hr.toLocaleString('id-ID');
                document.getElementById('ui_t_beban').innerText = 'Rp ' + data.total_beban.toLocaleString('id-ID');
                document.getElementById('ui_t_laba').innerText = 'Rp ' + data.laba_bersih.toLocaleString('id-ID');

                // Update Arus Kas
                document.getElementById('ui_k_awal').innerText = 'Rp ' + data.saldo_awal_bulan.toLocaleString('id-ID');
                document.getElementById('ui_k_masuk').innerText = '+ Rp ' + data.total_uang_masuk.toLocaleString('id-ID');
                document.getElementById('ui_k_keluar').innerText = '- Rp ' + data.total_uang_keluar.toLocaleString('id-ID');
                document.getElementById('ui_k_akhir').innerText = 'Rp ' + data.saldo_bank.toLocaleString('id-ID');

                // Update Rincian Kas & Bank
                const uiRincianBank = document.getElementById('ui_rincian_bank');
                uiRincianBank.innerHTML = '';
                if (data.rincian_bank && data.rincian_bank.length > 0) {
                    data.rincian_bank.forEach(bank => {
                        const isNegative = bank.saldo < 0;
                        const colorClass = isNegative ? 'text-danger' : 'text-dark';
                        uiRincianBank.innerHTML += `
                            <li class="list-group-item d-flex justify-content-between align-items-center p-3 border-0 border-bottom">
                                <span><code class="me-2">${bank.kode_akun}</code> ${bank.nama_akun}</span>
                                <span class="fw-semibold ${colorClass}">Rp ${bank.saldo.toLocaleString('id-ID')}</span>
                            </li>
                        `;
                    });
                } else {
                    uiRincianBank.innerHTML = '<li class="list-group-item text-center p-3 text-muted">Belum ada transaksi di akun Kas/Bank.</li>';
                }

                // Update Kasbon
                document.getElementById('ui_p_cair').innerText = 'Rp ' + data.pinjaman_keluar.toLocaleString('id-ID');
                document.getElementById('ui_p_cicil').innerText = '- Rp ' + data.pelunasan_masuk.toLocaleString('id-ID');
                document.getElementById('ui_p_piutang').innerText = 'Rp ' + data.piutang_berjalan.toLocaleString('id-ID');

                // ============================================
                // RENDER GRAFIK PENDAPATAN (Doughnut Chart)
                // ============================================
                const ctxPendapatan = document.getElementById('chartPendapatan').getContext('2d');
                if (chartPendapatanInstance) {
                    chartPendapatanInstance.destroy();
                }

                const labelPendapatan = data.chart_pendapatan.labels.length > 0 ? data.chart_pendapatan.labels : ['Belum Ada Pendapatan'];
                const dataPendapatan = data.chart_pendapatan.data.length > 0 ? data.chart_pendapatan.data : [1];

                chartPendapatanInstance = new Chart(ctxPendapatan, {
                    type: 'doughnut',
                    data: {
                        labels: labelPendapatan,
                        datasets: [{
                            data: dataPendapatan,
                            backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#858796'],
                            hoverOffset: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        let value = context.raw;
                                        if (data.chart_pendapatan.data.length === 0) return ' Rp 0';
                                        return ' Rp ' + value.toLocaleString('id-ID');
                                    }
                                }
                            },
                            legend: {
                                position: 'right'
                            }
                        }
                    }
                });

                // ============================================
                // RENDER GRAFIK PENGELUARAN (Horizontal Bar)
                // ============================================
                const ctxBeban = document.getElementById('chartBeban').getContext('2d');
                if (chartBebanInstance) {
                    chartBebanInstance.destroy();
                }

                const labelBeban = data.chart_beban.labels.length > 0 ? data.chart_beban.labels : ['Belum Ada Pengeluaran'];
                const dataBeban = data.chart_beban.data.length > 0 ? data.chart_beban.data : [0];

                chartBebanInstance = new Chart(ctxBeban, {
                    type: 'bar',
                    data: {
                        labels: labelBeban,
                        datasets: [{
                            label: 'Total (Rp)',
                            data: dataBeban,
                            backgroundColor: '#e74a3b',
                            borderRadius: 4,
                            minBarLength: 8 // <-- INI KUNCI SOLUSINYA. Bar sekecil apapun akan tetap tampil minimal 8 pixel
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return ' Rp ' + context.raw.toLocaleString('id-ID');
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                // Menambahkan sedikit padding di kanan agar bar panjang tidak menabrak batas
                                grace: '5%',
                                ticks: {
                                    callback: function(value) {
                                        if (value >= 1000000000) return 'Rp ' + (value / 1000000000) + 'M'; // Miliar
                                        if (value >= 1000000) return 'Rp ' + (value / 1000000) + 'Jt'; // Juta
                                        if (value >= 1000) return 'Rp ' + (value / 1000) + 'Rb'; // Ribu
                                        return 'Rp ' + value;
                                    }
                                }
                            }
                        }
                    }
                });

            }
        } catch (error) {
            console.error("Gagal memuat laporan keuangan:", error);
            alert("Gagal mengambil data laporan dari server.");
        }
    }

    // Auto-load saat halaman pertama kali dibuka
    document.addEventListener("DOMContentLoaded", () => {
        const dropdown = document.getElementById("periodeDropdown");
        if (dropdown) {
            loadLaporan(dropdown.value);
        } else {
            loadLaporan('');
        }
    });
</script>
