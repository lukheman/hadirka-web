    <style>
        /* sedikit styling untuk icon expand */
        td.dt-control {
            cursor: pointer;
        }

        .fade-in {
            animation: fadeIn 300ms ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        /* pastikan table responsive */
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            padding: 0.2rem 0.6rem;
        }
    </style>
    <div class="content-wrapper">
        <div class="container-xxl flex-grow-1 container-p-y py-4">

            <h4 class="fw-bold mb-3">
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
                    <div class="d-flex align-items-center gap-2" style="max-width: 520px;">
                        <select id="filterSite" class="form-select form-select-sm" data-bs-toggle="tooltip" title="Filter berdasarkan site">
                            <option value="">-- Pilih Site --</option>
                            <?php foreach (data_site() as $d) {
                                echo "<option value='" . htmlspecialchars($d['id_site']) . "'>" . htmlspecialchars($d['nama_site']) . "</option>";
                            } ?>
                        </select>

                        <select id="filterJabatan" class="form-select form-select-sm" data-bs-toggle="tooltip" title="Filter berdasarkan jabatan">
                            <option value="">-- Semua Jabatan --</option>
                            <?php foreach (data_jabatan() as $d) {
                                echo "<option value='" . htmlspecialchars($d['id_jabatan']) . "'>" . htmlspecialchars($d['nama_jabatan']) . "</option>";
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
                        <table id="tableKaryawan" class="table table-hover table-bordered table-striped">
                            <thead class="table-info">
                                <tr>
                                    <th class="text-center"></th>
                                    <th class="text-center"><i class="fa fa-hashtag"></i> No</th>
                                    <th class="text-center"><i class="fa fa-user"></i> Nama Karyawan</th>
                                    <th class="text-center"><i class="fa fa-id-card"></i> NIK KTP</th>
                                    <th class="text-center"><i class="fa fa-briefcase"></i> Jabatan</th>
                                    <th class="text-center"><i class="fa fa-cogs"></i> Actions</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
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


    <div class="modal fade" id="ModalEdit" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <form id="formEdit" class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fa fa-edit"></i> Edit Data Karyawan</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body row g-3">

                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id_karyawan" id="edit_id_karyawan">

                    <div class="col-md-6">
                        <label>NIK Karyawan</label>
                        <input type="text" class="form-control" name="nik_karyawan" id="edit_nik_karyawan" required>
                    </div>

                    <div class="col-md-6">
                        <label>Nama Lengkap</label>
                        <input type="text" class="form-control" name="nama_lengkap" id="edit_nama_lengkap" required>
                    </div>

                    <div class="col-md-6">
                        <label>Jenis Kelamin</label>
                        <select class="form-select" name="jenis_kelamin" id="edit_jenis_kelamin">
                            <option value="L">Laki-laki</option>
                            <option value="P">Perempuan</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label>Tanggal Lahir</label>
                        <input type="date" class="form-control" name="tgl_lahir" id="edit_tgl_lahir">
                    </div>

                    <div class="col-md-6">
                        <label>Email</label>
                        <input type="email" class="form-control" name="email_pribadi" id="edit_email_pribadi">
                    </div>

                    <div class="col-md-6">
                        <label>No Telp</label>
                        <input type="text" class="form-control" name="no_telp" id="edit_no_telp">
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-primary" type="submit">
                        <i class="fa fa-save"></i> Perbarui
                    </button>
                </div>
            </form>
        </div>
    </div>


    <!-- JS: jQuery, Bootstrap, DataTables, Toastr -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        // format child detail (HTML) dari row.data().detail
        function formatDetail(detailHtml) {
            return `<div class="p-2">${detailHtml}</div>`;
        }

        let tableKaryawan = null;

        $(document).ready(function() {
            // tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // Inisialisasi DataTable
            tableKaryawan = $('#tableKaryawan').DataTable({
                ajax: {
                    url: "app/controller/get-data/get-karyawan.php",
                    type: "POST",
                    data: function(d) {
                        d.site = $("#filterSite").val();
                        d.jabatan = $("#filterJabatan").val();
                    },
                    dataSrc: "data" // dari JSON { data: [...] }
                },
                columns: [{
                        className: 'dt-control text-center',
                        orderable: false,
                        data: null,
                        defaultContent: '<i class="fa fa-plus-circle text-primary"></i>',
                        width: "40px"
                    },
                    {
                        data: "no",
                        className: "text-center",
                        width: "60px"
                    },
                    {
                        data: "nama"
                    },
                    {
                        data: "nik",
                        className: "text-center"
                    },
                    {
                        data: "jabatan",
                        className: "text-center"
                    },
                    {
                        data: "action",
                        orderable: false,
                        className: "text-center",
                        width: "120px"
                    }
                ],
                responsive: true,
                pageLength: 10,
                order: [
                    [1, "desc"]
                ],
                language: {
                    search: "Cari:",
                    lengthMenu: "Tampilkan _MENU_ entri per halaman",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
                    paginate: {
                        first: "Pertama",
                        last: "Terakhir",
                        next: "Selanjutnya",
                        previous: "Sebelumnya"
                    },
                    emptyTable: "Tidak ada data karyawan"
                },
                createdRow: function(row, data, dataIndex) {
                    // tambahkan kelas baris jika diset dari server (misal expired atau bg-danger)
                    if (data.rowClass) {
                        $(row).addClass(data.rowClass);
                    }
                },
                drawCallback: function(settings) {
                    // animasi
                    $('#tableKaryawan tbody').addClass('fade-in');
                }
            });

            // handle expand / collapse child row
            $('#tableKaryawan tbody').on('click', 'td.dt-control', function() {
                var tr = $(this).closest('tr');
                var row = tableKaryawan.row(tr);

                if (row.child.isShown()) {
                    // hide
                    row.child.hide();
                    tr.removeClass('shown');
                    $(this).html('<i class="fa fa-plus-circle text-primary"></i>');
                } else {
                    // show
                    row.child(formatDetail(row.data().detail)).show();
                    tr.addClass('shown');
                    $(this).html('<i class="fa fa-minus-circle text-danger"></i>');
                }
            });

            // reload saat filter berubah
            $("#filterSite, #filterJabatan").on("change", function() {
                $('#loadingSpinner').show();
                tableKaryawan.ajax.reload(function() {
                    $('#loadingSpinner').hide();
                });
            });

            // load awal
            tableKaryawan.ajax.reload();

            // IMPORT SCRIPT (sama seperti milik Anda, disertakan)
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
                                // reload tabel setelah import sukses
                                tableKaryawan.ajax.reload();
                            } else {
                                showToast(res.error || "Gagal import!", "error");
                            }
                        } catch (err) {
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
        });
    </script>

    <script>
        // OPEN MODAL EDIT
        $(document).on("click", ".btnEdit", function() {
            let id = $(this).data("id");

            $.ajax({
                url: "karyawan_action.php",
                type: "POST",
                data: {
                    action: "get",
                    id_karyawan: id
                },
                dataType: "json",
                success: function(res) {
                    if (res.status === "ok") {
                        // isi form
                        $("#edit_id_karyawan").val(res.data.id_karyawan);
                        $("#edit_nik_karyawan").val(res.data.nik_karyawan);
                        $("#edit_nama_lengkap").val(res.data.nama_lengkap);
                        $("#edit_jenis_kelamin").val(res.data.jenis_kelamin);
                        $("#edit_tgl_lahir").val(res.data.tgl_lahir);
                        $("#edit_email_pribadi").val(res.data.email_pribadi);
                        $("#edit_no_telp").val(res.data.no_telp);

                        $("#ModalEdit").modal("show");
                    } else {
                        alert("Data tidak ditemukan!");
                    }
                }
            });
        });


        $("#formEdit").on("submit", function(e) {
            e.preventDefault();

            $.ajax({
                url: "karyawan_action.php",
                type: "POST",
                data: $(this).serialize(),
                success: function(res) {
                    try {
                        let json = JSON.parse(res);

                        if (json.status === "ok") {
                            $("#ModalEdit").modal("hide");
                            tableKaryawan.ajax.reload(null, false); // reload dt
                            showToast("Data berhasil diperbarui!");
                        } else {
                            showToast("Gagal memperbarui data!", "danger");
                        }
                    } catch (e) {
                        alert("Invalid response!");
                    }
                }
            });
        });


        $(document).on("click", ".btnDelete", function() {
            let id = $(this).data("id");

            if (!confirm("Yakin ingin menghapus data ini?")) return;

            $.ajax({
                url: "karyawan_action.php",
                type: "POST",
                data: {
                    action: "delete",
                    id_karyawan: id
                },
                success: function(res) {
                    try {
                        let json = JSON.parse(res);

                        if (json.status === "ok") {
                            tableKaryawan.ajax.reload(null, false);
                            showToast("Data berhasil dihapus!");
                        } else {
                            showToast("Gagal menghapus data!", "danger");
                        }
                    } catch (e) {
                        alert("Invalid response!");
                    }
                }
            });
        });
    </script>