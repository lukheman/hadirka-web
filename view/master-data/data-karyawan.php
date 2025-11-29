<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">

        <h4 class="fw-bold">
            <span class="text-muted fw-light">Master Data /</span> Data Karyawan
        </h4>

        <div class="pb-3">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">

                <!-- BUTTONS -->
                <div>
                    <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#ModalImport" data-bs-toggle="tooltip" title="Klik untuk mengimpor data karyawan dari file Excel">
                        <i class="fa fa-upload"></i> Import Data
                    </button>
                </div>

                <!-- FILTER AJAX -->
                <div class="d-flex align-items-center gap-2" style="max-width: 400px;">
                    <select id="filterSite" class="form-select form-select-sm" data-bs-toggle="tooltip" title="Filter berdasarkan site">
                        <option value="">-- Pilih Site --</option>
                        <?php foreach (data_site() as $d) {
                            echo "<option value='{$d['id_site']}'>{$d['nama_site']}</option>";
                        } ?>
                    </select>

                    <select id="filterJabatan" class="form-select form-select-sm" data-bs-toggle="tooltip" title="Filter berdasarkan jabatan">
                        <option value="">-- Semua Jabatan --</option>
                        <?php foreach (data_jabatan() as $d) {
                            echo "<option value='{$d['id_jabatan']}'>{$d['nama_jabatan']}</option>";
                        } ?>
                    </select>
                </div>

            </div>
        </div>

        <!-- TABLE -->
        <div class="card position-relative">
            <h5 class="card-header">
                <i class="fa fa-database"></i> Database Karyawan
            </h5>
            <div class="card-body">
                <!-- Spinner Loading -->
                <div class="text-center" id="loadingSpinner" style="display: none;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Memuat data...</span>
                    </div>
                    <p class="mt-2">Memuat data karyawan...</p>
                </div>
                <div class="table-responsive text-nowrap">
                    <table class="table table-hover table-bordered table-striped MyTableKaryawan">
                        <thead class="table-info">
                            <tr>
                                <th class="text-center"><i class="fa fa-hashtag"></i> No</th>
                                <th class="text-center"><i class="fa fa-user"></i> Nama Karyawan</th>
                                <th class="text-center"><i class="fa fa-id-card"></i> NIK KTP</th>
                                <th class="text-center"><i class="fa fa-briefcase"></i> Jabatan</th>
                                <th class="text-center"><i class="fa fa-venus-mars"></i> Jenis Kelamin</th>
                                <th class="text-center"><i class="fa fa-pray"></i> Agama</th>
                                <th class="text-center"><i class="fa fa-map-marker"></i> Tempat Lahir</th>
                                <th class="text-center"><i class="fa fa-calendar"></i> Tanggal Lahir</th>
                                <th class="text-center"><i class="fa fa-birthday-cake"></i> Umur</th>
                                <th class="text-center"><i class="fa fa-phone"></i> No. Telepon</th>
                                <th class="text-center"><i class="fa fa-cogs"></i> Actions</th>
                            </tr>
                        </thead>
                        <tbody id="tabelData"></tbody>
                    </table>
                </div>

            </div>
        </div>
        <!--/ TABLE -->
    </div>
</div>

<!-- MODAL IMPORT -->
<div class="modal fade" id="ModalImport" tabindex="-1" aria-labelledby="ModalImportLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="formImport" class="modal-content" enctype="multipart/form-data">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title" id="ModalImportLabel"><i class="fa fa-upload"></i> Upload Karyawan Excel</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <label for="file_excel" class="form-label"><i class="fa fa-file-excel"></i> Upload File (XLS/XLSX)</label>
                <input type="file" name="file_excel" id="file_excel" class="form-control" accept=".xls,.xlsx" required data-bs-toggle="tooltip" title="Pilih file Excel untuk import data karyawan">
                <a href="assets/teamplate-csv/Format Teamplate import Database Karyawan.xlsx" style="font-size: 2vh;">Download format xls</a>

                <div id="progressWrapper" class="progress mt-2" style="display:none;height:20px;">
                    <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-success" style="width:0%;">0%</div>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-success" type="submit" id="btnUpload" data-bs-toggle="tooltip" title="Klik untuk mengupload dan memproses file">
                    <i class="fa fa-upload"></i> Upload
                </button>
            </div>
        </form>
    </div>
</div>

<!-- TOAST -->
<div class="toast-container position-fixed bottom-0 end-0 p-3">
    <div id="toastAlert" class="toast text-bg-primary border-0" role="alert" data-bs-delay="4000">
        <div class="d-flex">
            <div class="toast-body" id="toastMessage">Processing...</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<script>
    let dataTable = null;

    async function loadData() {
        const site = $('#filterSite').val();
        const jabatan = $('#filterJabatan').val();

        const spinner = document.getElementById("loadingSpinner");
        spinner.style.display = "block"; // tampilkan loading

        const fd = new FormData();
        fd.append("site", site);
        fd.append("jabatan", jabatan);

        try {
            const req = await fetch("app/controller/get-data/get-karyawan.php", {
                method: "POST",
                body: fd
            });

            const html = await req.text();

            // Buat array data dari HTML
            const temp = document.createElement("tbody");
            temp.innerHTML = html;
            const rows = temp.querySelectorAll("tr");

            const dataArr = [];
            rows.forEach(tr => {
                const cells = tr.querySelectorAll("td");
                const rowData = [];
                cells.forEach(td => rowData.push(td.innerHTML));
                dataArr.push(rowData);
            });

            // UPDATE DATATABLE
            dataTable.clear();
            dataTable.rows.add(dataArr);
            dataTable.draw();

            // Animasi fade-in
            document.getElementById("tabelData").classList.add('fade-in');

        } catch (err) {
            console.error("ERR FETCH:", err);
            showToast("Gagal memuat data karyawan: " + err, "error");
        } finally {
            spinner.style.display = "none"; // sembunyikan loading
        }
    }


    // INIT DATATABLE
    document.addEventListener("DOMContentLoaded", () => {
        // Inisialisasi tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        dataTable = $(".MyTableKaryawan").DataTable({
            responsive: true,
            pageLength: 10,
            language: {
                search: "Cari:",
                lengthMenu: "Tampilkan _MENU_ entri per halaman",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
                paginate: {
                    first: "Pertama",
                    last: "Terakhir",
                    next: "Selanjutnya",
                    previous: "Sebelumnya"
                }
            }
        });

        $("#filterSite").on("change", loadData);
        $("#filterJabatan").on("change", loadData);

        loadData(); // load awal
    });

    // IMPORT SCRIPT
    document.getElementById("formImport").addEventListener("submit", function(e) {
        e.preventDefault();

        if (!confirm("Apakah Anda yakin ingin mengimpor file Excel ini? Data lama mungkin tertimpa.")) {
            return;
        }

        const formData = new FormData(this);
        const progressWrapper = document.getElementById("progressWrapper");
        const progressBar = document.getElementById("progressBar");
        const btnUpload = document.getElementById("btnUpload");

        progressWrapper.style.display = "block";
        progressBar.style.width = "0%";
        btnUpload.disabled = true;
        btnUpload.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Uploading...';

        const xhr = new XMLHttpRequest();

        xhr.upload.addEventListener("progress", e => {
            if (e.lengthComputable) {
                let p = Math.round((e.loaded / e.total) * 100);
                progressBar.style.width = p + "%";
                progressBar.textContent = p + "%";
            }
        });

        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                try {
                    const res = JSON.parse(xhr.responseText);

                    if (res.success) {
                        toastr.success(`Berhasil: ${res.berhasil}<br>Gagal: ${res.gagal}`, "Import selesai");
                        //loadData(); // reload data setelah import
                    } else {
                        // toastr.error(res.error || "Gagal import!", "Error");
                        showToast(res.error || "Gagal import!", "error");
                    }
                } catch (err) {
                    // toastr.error("JSON parsing error", "Error");
                    showToast("Terjadi kesalahan saat memproses respons server: " + err, "error");
                }

                setTimeout(() => {
                    progressWrapper.style.display = "none";
                    progressBar.style.width = "0%";
                    btnUpload.disabled = false;
                    btnUpload.innerHTML = '<i class="fa fa-upload"></i> Upload';
                }, 800);
            }
        };

        xhr.open("POST", "app/controller/csv-control/csv-karyawan.php");
        xhr.send(formData);
    });
</script>