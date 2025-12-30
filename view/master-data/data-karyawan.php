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
                        <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#ModalAdd">
                            <i class="fa fa-plus"></i> Tambah Karyawan
                        </button>

                        <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#ModalImport" data-bs-toggle="tooltip" title="Klik untuk mengimpor data karyawan dari file Excel">
                            <i class="fa fa-upload"></i> Import Data
                        </button>
                    </div>

                    <!-- FILTER AJAX -->
                    <div class="d-flex align-items-center gap-2" style="max-width: 520px;">
                        <?php
                        if ($_SESSION['site'] == 0) { ?>
                            <select id="filterSite" class="form-select form-select-sm" data-bs-toggle="tooltip" title="Filter berdasarkan site">
                                <option value="">-- Pilih Site --</option>
                                <?php foreach (data_site() as $d) { ?>
                                    <option value="<?= htmlspecialchars($d['id_site']) ?>"><?= htmlspecialchars($d['nama_site']) ?></option>
                                <?php } ?>
                            </select>
                        <?php } else { ?>
                            <input id="filterSite" type="hidden" class="form-control-sm" data-bs-toggle="tooltip" title="Filter berdasarkan site" value="<?= $_SESSION['site'] ?>">
                        <?php
                        }
                        ?>

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
                                    <th class="text-center"><i class="fa fa-users"></i> Status</th>
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

    <!-- MODAL TAMBAH -->
    <div class="modal fade" id="modalTambahKaryawan" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">

                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Tambah Karyawan</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <form id="formTambahKaryawan" enctype="multipart/form-data">
                    <div class="modal-body">

                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Nama Lengkap</label>
                                <input type="text" class="form-control" name="nama_lengkap" required>

                                <label class="form-label mt-3">Jenis Kelamin</label>
                                <select class="form-control" name="jenis_kelamin">
                                    <option value="L">Laki-laki</option>
                                    <option value="P">Perempuan</option>
                                </select>

                                <label class="form-label mt-3">Agama</label>
                                <input type="text" class="form-control" name="agama">

                                <label class="form-label mt-3">Tempat Lahir</label>
                                <input type="text" class="form-control" name="tempat_lahir">

                                <label class="form-label mt-3">Tanggal Lahir</label>
                                <input type="date" class="form-control" name="tgl_lahir">

                                <label class="form-label mt-3">No. Telp</label>
                                <input type="text" class="form-control" name="no_telp">

                                <label class="form-label mt-3">Email Pribadi</label>
                                <input type="email" class="form-control" name="email_pribadi">

                                <label class="form-label mt-3">Nationality</label>
                                <input type="text" class="form-control" name="nationality">
                            </div>

                            <div class="col-md-6">

                                <label class="form-label">NIK (KTP)</label>
                                <input type="text" class="form-control" name="nik_ktp">

                                <label class="form-label mt-3">No. KK</label>
                                <input type="text" class="form-control" name="kk_number">

                                <label class="form-label mt-3">NPWP</label>
                                <input type="text" class="form-control" name="npwp">

                                <label class="form-label mt-3">BPJS TK</label>
                                <input type="text" class="form-control" name="bpjs_tk">

                                <label class="form-label mt-3">BPJS KES</label>
                                <input type="text" class="form-control" name="bpjs_kes">

                                <label class="form-label mt-3">Status Pernikahan</label>
                                <select class="form-control" name="status_pernikahan">
                                    <option value="">-- Pilih --</option>
                                    <option>Kawin</option>
                                    <option>Belum Kawin</option>
                                </select>

                                <label class="form-label mt-3">Nama Ibu Kandung</label>
                                <input type="text" class="form-control" name="nama_ibu">
                            </div>
                        </div>

                        <!-- Alamat -->
                        <hr>
                        <h6>Alamat KTP</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Alamat</label>
                                <textarea class="form-control" name="alamat_ktp"></textarea>

                                <label class="form-label mt-3">Provinsi</label>
                                <input type="text" class="form-control" name="provinsi">

                                <label class="form-label mt-3">Kabupaten/Kota</label>
                                <input type="text" class="form-control" name="kabupaten">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Kecamatan</label>
                                <input type="text" class="form-control" name="kecamatan">

                                <label class="form-label mt-3">Desa</label>
                                <input type="text" class="form-control" name="desa">

                                <label class="form-label mt-3">RT/RW</label>
                                <input type="text" class="form-control" name="rt_rw">
                            </div>
                        </div>

                        <!-- Pendidikan -->
                        <hr>
                        <h6>Pendidikan</h6>

                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Pendidikan Terakhir</label>
                                <input type="text" class="form-control" name="last_education">

                                <label class="form-label mt-3">Jurusan</label>
                                <input type="text" class="form-control" name="major">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tingkat Pendidikan</label>
                                <input type="text" class="form-control" name="education_level">
                            </div>
                        </div>

                        <!-- Karier -->
                        <hr>
                        <h6>Penempatan Kerja</h6>

                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Site</label>
                                <select class="form-control" name="id_site" id="siteSelectAdd">
                                    <option value="">-- Pilih Site --</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Jabatan</label>
                                <select class="form-control" name="id_jabatan" id="jabatanSelectAdd">
                                    <option value="">-- Pilih Jabatan --</option>
                                </select>
                            </div>
                        </div>

                        <div class="mt-3">
                            <label class="form-label">Tanggal Kontrak Selesai</label>
                            <input type="date" class="form-control" name="tgl_selesai">
                        </div>

                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Simpan</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    </div>

                </form>

            </div>
        </div>
    </div>


    <div class="modal fade" id="ModalEdit" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <form id="formEdit" class="modal-content">

                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fa fa-edit"></i> Edit Data Karyawan</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id_karyawan" id="edit_id_karyawan">

                    <h6 class="mt-1">Data Pribadi</h6>
                    <div class="row">
                        <div class="col-md-6">

                            <label>Nama Lengkap</label>
                            <input type="text" class="form-control" name="nama_lengkap" id="edit_nama_lengkap" required>
                            <label>Nik Karyawan</label>
                            <input type="text" name="nik_karyawan" id="edit_nik_karyawan" class="form-control" required>


                            <label class="mt-3">Jenis Kelamin</label>
                            <select class="form-control" name="jenis_kelamin" id="edit_jenis_kelamin">
                                <option value="L">Laki-laki</option>
                                <option value="P">Perempuan</option>
                            </select>

                            <label class="mt-3">Agama</label>
                            <input type="text" id="edit_agama" name="agama" class="form-control">

                            <label class="mt-3">Tempat Lahir</label>
                            <input type="text" id="edit_tempat_lahir" name="tempat_lahir" class="form-control">

                            <label class="mt-3">Tanggal Lahir</label>
                            <input type="date" id="edit_tgl_lahir" name="tgl_lahir" class="form-control">

                            <label class="mt-3">Status Pernikahan</label>
                            <select id="edit_status_pernikahan" name="status_pernikahan" class="form-control">
                                <option value="">-- Pilih --</option>
                                <option value="Kawin">Kawin</option>
                                <option value="Belum Kawin">Belum Kawin</option>
                            </select>

                            <label class="mt-3">Nama Ibu Kandung</label>
                            <input type="text" id="edit_nama_ibu" name="nama_ibu" class="form-control">

                        </div>

                        <div class="col-md-6">

                            <label>NIK (KTP)</label>
                            <input type="text" id="edit_nik_ktp" name="nik_ktp" class="form-control">

                            <label class="mt-3">No. KK</label>
                            <input type="text" id="edit_kk_number" name="kk_number" class="form-control">

                            <label class="mt-3">NPWP</label>
                            <input type="text" id="edit_npwp" name="npwp" class="form-control">

                            <label class="mt-3">BPJS TK</label>
                            <input type="text" id="edit_bpjs_tk" name="bpjs_tk" class="form-control">

                            <label class="mt-3">BPJS Kesehatan</label>
                            <input type="text" id="edit_bpjs_kes" name="bpjs_kes" class="form-control">

                            <label class="mt-3">Email Pribadi</label>
                            <input type="email" id="edit_email_pribadi" name="email_pribadi" class="form-control">

                            <label class="mt-3">No Telp</label>
                            <input type="text" id="edit_no_telp" name="no_telp" class="form-control">
                        </div>
                    </div>

                    <hr>
                    <h6>Alamat KTP</h6>

                    <div class="row">
                        <div class="col-md-6">
                            <label>Alamat</label>
                            <textarea id="edit_alamat_ktp" name="alamat_ktp" class="form-control"></textarea>

                            <label class="mt-3">Provinsi</label>
                            <input type="text" id="edit_provinsi" name="provinsi" class="form-control">

                            <label class="mt-3">Kabupaten/Kota</label>
                            <input type="text" id="edit_kabupaten" name="kabupaten" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label>Kecamatan</label>
                            <input type="text" id="edit_kecamatan" name="kecamatan" class="form-control">

                            <label class="mt-3">Desa</label>
                            <input type="text" id="edit_desa" name="desa" class="form-control">

                            <label class="mt-3">RT / RW</label>
                            <input type="text" id="edit_rt_rw" name="rt_rw" class="form-control">
                        </div>
                    </div>

                    <hr>
                    <h6>Pendidikan</h6>

                    <div class="row">
                        <div class="col-md-6">
                            <label>Pendidikan Terakhir</label>
                            <input type="text" id="edit_last_education" name="last_education" class="form-control">

                            <label class="mt-3">Jurusan</label>
                            <input type="text" id="edit_major" name="major" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label>Tingkat Pendidikan</label>
                            <input type="text" id="edit_education_level" name="education_level" class="form-control">
                        </div>
                    </div>

                    <hr>
                    <h6>Penempatan Kerja</h6>

                    <div class="row">
                        <div class="col-md-6">
                            <label>Site</label>
                            <select id="edit_id_site" name="id_site" class="form-control">
                                <option value="">-- Pilih Site --</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label>Jabatan</label>
                            <select id="edit_id_jabatan" name="id_jabatan" class="form-control">
                                <option value="">-- Pilih Jabatan --</option>
                            </select>
                        </div>

                        <div class="col-12 mt-3">
                            <label>Tanggal Kontrak Selesai</label>
                            <input type="date" id="edit_tgl_selesai" name="tgl_selesai" class="form-control">
                        </div>
                    </div>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-primary" type="submit">
                        <i class="fa fa-save"></i> Simpan Perubahan
                    </button>
                </div>

            </form>
        </div>
    </div>

    <!-- JS: jQuery, Bootstrap, DataTables, Toastr -->
    <script src="https://code.jquery.com/jquery-3.6.1.min.js"></script>
    <script>
        // 1. Helper Function: Detail Expandable Row
        function formatDetail(detailHtml) {
            return `<div class="p-4 bg-light border-start border-primary border-4 rounded shadow-sm">${detailHtml}</div>`;
        }

        // 2. Helper Function: Toast Notifications
        function showToast(message, type = "success") {
            const toastEl = document.getElementById('toastAlert');
            const toastBody = document.getElementById('toastMessage');

            toastEl.className = `toast text-bg-${type} border-0`;
            toastBody.innerHTML = message;

            const toast = new bootstrap.Toast(toastEl);
            toast.show();
        }

        let tableKaryawan = null;

        $(document).ready(function() {
            // Init Tooltip
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(t => new bootstrap.Tooltip(t));

            // 3. Inisialisasi DataTable
            tableKaryawan = $('#tableKaryawan').DataTable({
                processing: true,
                serverSide: false, // Set true jika data ribuan dan filter dilakukan di PHP
                ajax: {
                    url: "app/controller/get-data/get-karyawan.php",
                    type: "POST",
                    data: function(d) {
                        d.site = $("#filterSite").val();
                        d.jabatan = $("#filterJabatan").val();
                    },
                    dataSrc: "data"
                },
                columns: [{
                        className: 'dt-control text-center',
                        orderable: false,
                        data: null,
                        defaultContent: '<i class="fa fa-plus-circle text-primary"></i>'
                    },
                    {
                        data: "no",
                        className: "text-center"
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
                        data: "status_karyawan",
                        className: "text-center"
                    },
                    {
                        data: "action",
                        orderable: false,
                        className: "text-center"
                    }
                ],
                order: [
                    [1, "asc"]
                ],
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json"
                }
            });

            // 4. Handle Expand/Collapse
            $('#tableKaryawan tbody').on('click', 'td.dt-control', function() {
                let tr = $(this).closest('tr');
                let row = tableKaryawan.row(tr);

                if (row.child.isShown()) {
                    row.child.hide();
                    $(this).html('<i class="fa fa-plus-circle text-primary"></i>');
                } else {
                    row.child(formatDetail(row.data().detail)).show();
                    $(this).html('<i class="fa fa-minus-circle text-danger"></i>');
                }
            });

            // 5. Filter Reload
            $("#filterSite, #filterJabatan").on("change", function() {
                tableKaryawan.ajax.reload();
            });

            // 6. ACTION: TAMBAH DATA (Pastikan ID formTambahKaryawan sesuai HTML)
            $("#formTambahKaryawan").on("submit", function(e) {
                e.preventDefault();
                let formData = new FormData(this);
                formData.append("action", "add"); // Menambahkan marker action

                $.ajax({
                    url: "app/controller/act-karyawan.php",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        if (res.status === "ok") {
                            $("#modalTambahKaryawan").modal("hide");
                            $("#formTambahKaryawan")[0].reset();
                            tableKaryawan.ajax.reload(null, false);
                            showToast("Data karyawan berhasil ditambahkan!");
                        } else {
                            showToast("Gagal: " + (res.msg || "Terjadi kesalahan"), "danger");
                        }
                    }
                });
            });

            // 7. ACTION: GET DATA UNTUK EDIT (Event Delegation)
            $(document).on("click", ".btnEdit", function() {
                let id = $(this).data("id");
                $.ajax({
                    url: "app/controller/act-karyawan.php",
                    type: "POST",
                    data: {
                        action: "get",
                        id_karyawan: id
                    },
                    dataType: "json",
                    success: function(res) {
                        if (res.status === "ok") {
                            let d = res.data;
                            // Isi data ke form modal edit
                            $("#edit_id_karyawan").val(d.id_karyawan);
                            $("#edit_nik_karyawan").val(res.data.nik_karyawan);
                            $("#edit_nama_lengkap").val(d.nama_lengkap);
                            $("#edit_jenis_kelamin").val(d.jenis_kelamin);
                            $("#edit_agama").val(d.agama);
                            $("#edit_tempat_lahir").val(d.tempat_lahir);
                            $("#edit_tgl_lahir").val(d.tgl_lahir);
                            $("#edit_status_pernikahan").val(d.status_pernikahan);
                            $("#edit_nama_ibu").val(d.nama_ibu);
                            $("#edit_nik_ktp").val(d.nik_ktp);
                            $("#edit_kk_number").val(d.kk_number);
                            $("#edit_npwp").val(d.npwp);
                            $("#edit_bpjs_tk").val(d.bpjs_tk);
                            $("#edit_bpjs_kes").val(d.bpjs_kes);
                            $("#edit_email_pribadi").val(d.email_pribadi);
                            $("#edit_no_telp").val(d.no_telp);
                            $("#edit_alamat_ktp").val(d.alamat_ktp);
                            $("#edit_provinsi").val(d.provinsi);
                            $("#edit_kabupaten").val(d.kabupaten);
                            $("#edit_kecamatan").val(d.kecamatan);
                            $("#edit_desa").val(d.desa);
                            $("#edit_rt_rw").val(d.rt_rw);
                            $("#edit_last_education").val(d.last_education);
                            $("#edit_major").val(d.major);
                            $("#edit_education_level").val(d.education_level);
                            $("#edit_id_site").val(d.id_site);
                            $("#edit_id_jabatan").val(d.id_jabatan);
                            $("#edit_tgl_selesai").val(d.tgl_selesai);

                            $("#ModalEdit").modal("show");
                        }
                    }
                });
            });

            // 8. ACTION: SIMPAN PERUBAHAN (EDIT)
            $("#formEdit").on("submit", function(e) {
                e.preventDefault();
                $.ajax({
                    url: "app/controller/act-karyawan.php",
                    type: "POST",
                    data: $(this).serialize() + "&action=edit",
                    success: function(res) {
                        if (res.status === "ok") {
                            $("#ModalEdit").modal("hide");
                            tableKaryawan.ajax.reload(null, false);
                            showToast("Data berhasil diperbarui!");
                        } else {
                            showToast("Gagal memperbarui data", "danger");
                        }
                    }
                });
            });

            // 9. ACTION: DELETE DATA
            $(document).on("click", ".btnDelete", function() {
                let id = $(this).data("id");
                let nama = $(this).data("nama");

                if (confirm(`Hapus data karyawan: ${nama}?`)) {
                    $.ajax({
                        url: "app/controller/act-karyawan.php",
                        type: "POST",
                        data: {
                            action: "delete",
                            id_karyawan: id
                        },
                        success: function(res) {
                            if (res.status === "ok") {
                                tableKaryawan.ajax.reload(null, false);
                                showToast("Data berhasil dihapus!", "success");
                            } else {
                                showToast("Gagal menghapus data!", "danger");
                            }
                        }
                    });
                }
            });

            // 10. ACTION: IMPORT EXCEL
            $("#formImport").on("submit", function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                const btn = $("#btnUpload");

                $("#progressWrapper").show();
                btn.prop("disabled", true).html('<i class="fa fa-spinner fa-spin"></i> Processing...');

                $.ajax({
                    url: "app/controller/csv-control/csv-karyawan.php",
                    type: "POST",
                    data: formData,
                    contentType: false,
                    processData: false,
                    xhr: function() {
                        var xhr = new window.XMLHttpRequest();
                        xhr.upload.addEventListener("progress", function(evt) {
                            if (evt.lengthComputable) {
                                var percentComplete = Math.round((evt.loaded / evt.total) * 100);
                                $("#progressBar").css("width", percentComplete + "%").text(percentComplete + "%");
                            }
                        }, false);
                        return xhr;
                    },
                    success: function(res) {
                        console.log("Full Response:", res); // ← TAMBAHKAN INI DULU
                        console.log("Type:", typeof res);
                        try {
                            if (res.success) {
                                showToast(`Import Berhasil! (${res.berhasil} data)`, "success");
                                tableKaryawan.ajax.reload();
                                $("#ModalImport").modal("hide");
                            } else {
                                showToast("Gagal: " + res.error, "danger");
                            }
                        } catch (e) {
                            showToast("Error parsing response", "danger");
                        }
                    },
                    complete: function() {
                        $("#progressWrapper").hide();
                        btn.prop("disabled", false).html('<i class="fa fa-upload"></i> Upload');
                    }
                });
            });
        });
    </script>