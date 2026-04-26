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
</style>

<?php
require_once 'function.php'; // Pastikan pathnya benar
require_once 'get-laporan-keuangan.php';
?>

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold">Laporan Keuangan</h4>
            <p class="text-muted mb-0">Project Manpower Supply (IPIP HBB) - Periode April 2026</p>
        </div>
        <div>
            <button class="btn btn-outline-primary me-2"><i class="bx bx-printer me-1"></i> Cetak</button>
            <button class="btn btn-primary"><i class="bx bx-download me-1"></i> Export Excel</button>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-sm-6">
            <div class="card card-laporan h-100">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <p class="text-sm mb-1 text-muted fw-semibold">Total Pendapatan</p>
                            <h5 class="font-weight-bolder mb-0">Rp 500 Juta</h5>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon-shape bg-primary text-white shadow-sm d-inline-flex">
                                <i class="bx bx-wallet fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card card-laporan h-100">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <p class="text-sm mb-1 text-muted fw-semibold">Total Beban</p>
                            <h5 class="font-weight-bolder mb-0 text-danger">Rp 350 Juta</h5>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon-shape bg-danger text-white shadow-sm d-inline-flex">
                                <i class="bx bx-trending-down fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card card-laporan h-100 border-success border-bottom border-3">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <p class="text-sm mb-1 text-muted fw-semibold">Laba Bersih</p>
                            <h5 class="font-weight-bolder mb-0 text-success">Rp 150 Juta</h5>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon-shape bg-success text-white shadow-sm d-inline-flex">
                                <i class="bx bx-line-chart fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card card-laporan h-100">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <p class="text-sm mb-1 text-muted fw-semibold">Saldo Bank</p>
                            <h5 class="font-weight-bolder mb-0 text-info">Rp 235 Juta</h5>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon-shape bg-info text-white shadow-sm d-inline-flex">
                                <i class="bx bxs-bank fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card card-laporan h-100">
                <div class="card-header card-header-laporan d-flex align-items-center">
                    <i class="bx bx-balance text-primary me-2 fs-5"></i> 
                    Laporan Laba Rugi
                </div>
                <div class="card-body mt-3">
                    <div class="table-responsive">
                        <table class="table table-borderless align-middle">
                            <tr>
                                <td colspan="2" class="text-primary fw-bold pt-3 pb-2"><i class="bx bx-down-arrow-alt me-1"></i> PENDAPATAN</td>
                            </tr>
                            <tr>
                                <td class="ps-4">Jasa Manpower Supply (PT HBB)</td>
                                <td class="text-end fw-semibold">Rp 500.000.000</td>
                            </tr>
                            <tr class="table-light border-bottom">
                                <td class="ps-4 fw-bold text-muted">Total Pendapatan</td>
                                <td class="text-end fw-bold text-dark">Rp 500.000.000</td>
                            </tr>

                            <tr>
                                <td colspan="2" class="text-danger fw-bold pt-4 pb-2"><i class="bx bx-up-arrow-alt me-1"></i> BEBAN PENGELUARAN</td>
                            </tr>
                            <tr>
                                <td class="ps-4">Biaya Operasional & Lapangan</td>
                                <td class="text-end fw-semibold">Rp 50.000.000</td>
                            </tr>
                            <tr>
                                <td class="ps-4">Gaji & Rapel Karyawan <span class="badge rounded-pill bg-secondary ms-1"><i class="bx bx-bot me-1"></i>Sistem HR</span></td>
                                <td class="text-end fw-semibold">Rp 300.000.000</td>
                            </tr>
                            <tr class="table-light border-bottom">
                                <td class="ps-4 fw-bold text-muted">Total Beban</td>
                                <td class="text-end fw-bold text-dark">Rp 350.000.000</td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="p-3 bg-success bg-opacity-10 rounded-3 mt-3 d-flex justify-content-between align-items-center border border-success">
                        <h6 class="mb-0 text-white fw-bold">LABA BERSIH BULAN INI</h6>
                        <h5 class="mb-0 text-white fw-bolder">Rp 150.000.000</h5>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card card-laporan mb-4">
                <div class="card-header card-header-laporan d-flex align-items-center">
                    <i class="bx bx-transfer-alt text-warning me-2 fs-5"></i> 
                    Pergerakan Arus Kas
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush m-0">
                        <li class="list-group-item d-flex justify-content-between align-items-center p-3 border-0 border-bottom">
                            <span class="text-muted">Saldo Awal Bulan</span>
                            <span class="fw-semibold">Rp 100.000.000</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center p-3 border-0 border-bottom">
                            <span><i class="bx bx-plus-circle text-success me-2"></i>Uang Masuk</span>
                            <span class="text-success fw-bold">+ Rp 505.000.000</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center p-3 border-0 border-bottom">
                            <span><i class="bx bx-minus-circle text-danger me-2"></i>Uang Keluar</span>
                            <span class="text-danger fw-bold">- Rp 370.000.000</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center p-3 bg-light border-0">
                            <span class="fw-bold">Saldo Akhir di Bank</span>
                            <span class="fw-bold fs-5 text-dark">Rp 235.000.000</span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="card card-laporan">
                <div class="card-header card-header-laporan d-flex align-items-center">
                    <i class="bx bx-group text-info me-2 fs-5"></i> 
                    Pantauan Kasbon Karyawan
                </div>
                <div class="card-body mt-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-sm">Pencairan Kasbon Baru</span>
                        <span class="text-dark fw-semibold">Rp 20.000.000</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-sm">Cicilan Masuk (Potong Gaji)</span>
                        <span class="text-success fw-semibold">- Rp 5.000.000</span>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-info" role="progressbar" style="width: 75%"></div>
                    </div>
                    <div class="d-flex justify-content-between mt-3 pt-3 border-top">
                        <span class="fw-bold">Total Piutang Berjalan</span>
                        <span class="fw-bold text-primary">Rp 15.000.000</span>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</div>


