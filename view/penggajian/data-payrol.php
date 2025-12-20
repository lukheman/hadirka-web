<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

<style>
    .card { border-radius: 12px; box-shadow: 0 4px 18px rgba(0, 0, 0, 0.08); }
    .btn { border-radius: 8px; padding: 8px 16px; font-weight: 600; }
    #loadingOverlay {
        position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(255, 255, 255, 0.7); display: none;
        align-items: center; justify-content: center; z-index: 9999;
    }
    .spinner {
        border: 6px solid #ddd; border-top: 6px solid #4e73df;
        border-radius: 50%; width: 55px; height: 55px;
        animation: spin 0.7s linear infinite;
    }
    @keyframes spin { 100% { transform: rotate(360deg); } }
    
    /* Perbaikan agar tabel tidak meluber dan rapi di Bootstrap 5 */
    .dataTables_wrapper .row { margin-bottom: 10px; }
</style>

<div id="loadingOverlay"><div class="spinner"></div></div>

<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold mb-3"><span class="text-muted fw-light">Master Data /</span> Payroll</h4>

        <div class="row mb-4">
            <div class="col-md-3">
                <label class="form-label">Dari Tanggal</label>
                <input type="date" id="startDate" class="form-control">
            </div>
            <div class="col-md-3">
                <label class="form-label">Sampai Tanggal</label>
                <input type="date" id="endDate" class="form-control">
            </div>
            <div class="col-md-4">
                <label class="form-label">&nbsp;</label><br>
                <button id="btnLoadPayrol" class="btn btn-primary me-1"><i class="bx bx-refresh"></i> Load Data</button>
                <button id="btnExportExcel" class="btn btn-success"><i class="bx bx-download"></i> Export Excel</button>
            </div>
        </div>

        <div class="card">
            <h5 class="card-header">Database Payroll Karyawan</h5>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="payrolTable" class="table table-striped table-bordered w-100">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>NIK</th>
                                <th>Nama</th>
                                <th>Jabatan</th>
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

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

<script>
    let table;

    function showLoading() { $("#loadingOverlay").css("display", "flex"); }
    function hideLoading() { $("#loadingOverlay").hide(); }

    function setDefaultDateRange() {
        let today = new Date();
        let year = today.getFullYear();
        let month = today.getMonth();
        let prevMonth = month - 1;
        let prevYear = year;
        if (prevMonth < 0) { prevMonth = 11; prevYear = year - 1; }
        
        const f = (y, m, d) => `${y}-${(m+1).toString().padStart(2, "0")}-${d.toString().padStart(2, "0")}`;
        document.getElementById("startDate").value = f(prevYear, prevMonth, 21);
        document.getElementById("endDate").value = f(year, month, 20);
    }

    function loadPayrol() {
        let startDate = $("#startDate").val();
        let endDate = $("#endDate").val();

        showLoading();

        $.ajax({
            url: "app/controller/get-data/get-payrol.php",
            type: "POST",
            data: { startDate, endDate },
            dataType: "json",
            success: function(res) {
                hideLoading();
                
                if ($.fn.DataTable.isDataTable("#payrolTable")) {
                    table.destroy();
                }

                $("#dataPayrol").empty();
                if (res.status !== "success") return;

                let rows = "";
                res.data.forEach(row => {
                    rows += `<tr>
                        <td>${row.no}</td>
                        <td>${row.nik}</td>
                        <td>${row.nama}</td>
                        <td>${row.jabatan}</td>
                        <td class="text-end fw-bold">${row.gaji_bersih}</td>
                    </tr>`;
                });
                $("#dataPayrol").html(rows);

                // Inisialisasi DataTable standar (Tanpa dom 'B' agar layout tidak hancur)
                table = $("#payrolTable").DataTable({
                    "pageLength": 10,
                    "responsive": true,
                    "language": {
                        "search": "Cari:",
                        "lengthMenu": "Tampilkan _MENU_ data",
                        "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data"
                    }
                });
            },
            error: function() {
                hideLoading();
                alert("Gagal mengambil data!");
            }
        });
    }

    function exportExcel() {
        if (!table) {
            alert("Data belum di-load!");
            return;
        }

        // Trik agar export mengambil SEMUA data (bukan yang tampil saja)
        // Kita ambil data langsung dari instance table.rows().data()
        let allData = table.rows().data().toArray();
        let headers = [];
        $('#payrolTable thead th').each(function() {
            headers.push($(this).text());
        });

        // Gabungkan Header + Semua Data
        let worksheetData = [headers, ...allData];

        // Buat Workbook Excel
        let wb = XLSX.utils.book_new();
        let ws = XLSX.utils.aoa_to_sheet(worksheetData);
        XLSX.utils.book_append_sheet(wb, ws, "Payroll");

        // Nama File
        let fileName = `Payroll_${$("#startDate").val()}_sd_${$("#endDate").val()}.xlsx`;

        // Download
        XLSX.writeFile(wb, fileName);
    }

    $(document).ready(function() {
        setDefaultDateRange();
        $("#btnLoadPayrol").on("click", loadPayrol);
        $("#btnExportExcel").on("click", exportExcel);
    });
</script>