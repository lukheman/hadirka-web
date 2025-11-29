<style>
    /* --- CARD & BUTTON BEAUTIFY --- */
    .card {
        border-radius: 12px;
        box-shadow: 0 4px 18px rgba(0, 0, 0, 0.08);
    }

    .btn {
        border-radius: 8px;
        padding: 8px 16px;
        font-weight: 600;
    }

    .btn-primary {
        background: #4e73df;
        border: none;
    }

    .btn-success {
        background: #1cc88a;
        border: none;
    }

    /* --- TABLE STYLE --- */
    table th {
        background: #f1f1f1;
        font-weight: 600;
    }

    tbody tr {
        transition: 0.2s ease;
    }

    tbody tr:hover {
        background: #eef3ff;
    }

    /* --- LOADING OVERLAY --- */
    #loadingOverlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.7);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 9999;
    }

    .spinner {
        border: 6px solid #ddd;
        border-top: 6px solid #4e73df;
        border-radius: 50%;
        width: 55px;
        height: 55px;
        animation: spin 0.7s linear infinite;
    }

    @keyframes spin {
        100% {
            transform: rotate(360deg);
        }
    }

    tbody tr.fade-in {
        animation: fadeIn 0.4s ease-in-out;
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
</style>

<!-- LOADING OVERLAY -->
<div id="loadingOverlay">
    <div class="spinner"></div>
</div>

<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">

        <h4 class="fw-bold mb-3">
            <span class="text-muted fw-light">Master Data /</span> Payroll
        </h4>

        <div class="row mb-3">
            <div class="col-md-3">
                <label>Dari Tanggal</label>
                <input type="date" id="startDate" class="form-control">
            </div>

            <div class="col-md-3">
                <label>Sampai Tanggal</label>
                <input type="date" id="endDate" class="form-control">
            </div>

            <div class="col-md-4">
                <label>&nbsp;</label><br>
                <button id="btnLoadPayrol" class="btn btn-primary me-1">
                    <i class="bx bx-refresh"></i> Load Data
                </button>

                <button id="btnExportExcel" class="btn btn-success">
                    <i class="bx bx-download"></i> Export Excel
                </button>
            </div>
        </div>

        <div class="card">
            <h5 class="card-header">Database Payroll Karyawan</h5>
            <div class="card-body">
                <div class="table-responsive text-nowrap">
                    <table id="payrolTable" class="table table-bordered">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>Jabatan</th>
                                <th>Atas Nama Rekening</th>
                                <th>No Rekening</th>
                                <th>Nama Bank</th>
                                <th>Gaji Bersih</th>
                            </tr>
                        </thead>
                        <tbody id="dataPayrol"></tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

<script>
    function showLoading() {
        document.getElementById("loadingOverlay").style.display = "flex";
    }

    function hideLoading() {
        document.getElementById("loadingOverlay").style.display = "none";
    }

    function setDefaultDateRange() {
        let today = new Date();
        let year = today.getFullYear();
        let month = today.getMonth(); // bulan sekarang (0 = Januari)

        // bulan kemarin
        let prevMonth = month - 1;
        let prevYear = year;

        if (prevMonth < 0) {
            prevMonth = 11; // Desember
            prevYear = year - 1;
        }

        function f(yyyy, mm, dd) {
            mm = (mm + 1).toString().padStart(2, "0");
            dd = dd.toString().padStart(2, "0");
            return `${yyyy}-${mm}-${dd}`;
        }

        // start = 21 bulan kemarin
        document.getElementById("startDate").value =
            f(prevYear, prevMonth, 21);

        // end = 20 bulan ini
        document.getElementById("endDate").value =
            f(year, month, 20);
    }

    function loadPayrol() {
        let startDate = document.getElementById("startDate").value;
        let endDate = document.getElementById("endDate").value;

        showLoading();

        $.ajax({
            url: "app/controller/get-data/get-payrol.php",
            type: "POST",
            data: {
                startDate,
                endDate
            },
            dataType: "json",

            success: function(res) {
                hideLoading();

                const tbody = document.getElementById("dataPayrol");
                tbody.innerHTML = "";

                if (res.status !== "success") return;

                res.data.forEach(row => {
                    let tr = document.createElement("tr");
                    tr.classList.add("fade-in");

                    tr.innerHTML = `
                    <td>${row.no}</td>
                    <td>${row.nama}</td>
                    <td>${row.jabatan}</td>
                    <td>${row.rekening_nama ?? ""}</td>
                    <td>${row.rekening_no ?? ""}</td>
                    <td>${row.bank ?? ""}</td>
                    <td class="text-end fw-bold">Rp ${row.gaji_bersih}</td>
                `;

                    tbody.appendChild(tr);
                });

                // Reset DataTables
                // if ($.fn.DataTable.isDataTable("#payrolTable")) {
                //     $("#payrolTable").DataTable().destroy();
                // }

                // $("#payrolTable").DataTable({
                //     pageLength: 20,
                //     responsive: true
                // });
            },

            error: function() {
                hideLoading();
                alert("Gagal mengambil data!");
            }
        });
    }

    function exportExcel() {

        let table = document.getElementById("payrolTable");
        let wb = XLSX.utils.table_to_book(table, {
            sheet: "Payroll"
        });

        let start = document.getElementById("startDate").value || "0000-00-00";
        let end = document.getElementById("endDate").value || "0000-00-00";

        let fileName = `Payroll_${start}_sd_${end}.xlsx`;

        XLSX.writeFile(wb, fileName);
    }


    document.addEventListener("DOMContentLoaded", () => {
        setDefaultDateRange();
        document.getElementById("btnLoadPayrol").addEventListener("click", loadPayrol);
        document.getElementById("btnExportExcel").addEventListener("click", exportExcel);
    });
</script>