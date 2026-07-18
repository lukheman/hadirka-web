<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold mb-3">
            <span class="text-muted fw-light">Master Data /</span> Kelola Karyawan Internal
        </h4>

        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title text-white mb-0"><i class="fas fa-file-import"></i> Form Import Excel Karyawan Internal</h5>
            </div>
            <div class="card-body mt-3">
                <form id="formImportKaryawan" enctype="multipart/form-data">
                    <p class="instruction-text mb-3">
                        Format Excel/CSV: <strong>A</strong>=No | <strong>B</strong>=No. Induk Karyawan (INT-Nomor) | <strong>C</strong>=Nama | <strong>D</strong>=L/P | <strong>E</strong>=Site | <strong>F</strong>=Jabatan | <strong>G</strong>=Gaji | <strong>H</strong>=Bank | <strong>I</strong>=No Rek | <strong>J</strong>=Atas Nama | <strong>K</strong>=Status | <strong>L</strong>=Tgl Masuk | <strong>M</strong>=Tgl Keluar | <strong>N</strong>=Ket Keluar
                    </p>
                    <div class="row align-items-end">
                        <div class="col-md-7">
                            <label class="form-label">Upload File Excel Karyawan</label>
                            <input type="file" name="file_excel" class="form-control" accept=".xls,.xlsx" required>
                        </div>
                        <div class="col-md-5 mt-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-upload"></i> Import Data
                            </button>
                            <a href="assets/template-csv/Format_Impor_Karyawan_Internal.xlsx" class="btn btn-outline-secondary ms-2" download>
                                <i class="fas fa-download"></i> Download Template
                            </a>
                        </div>
                    </div>
                </form>
                <div id="importResult" class="mt-3"></div>
            </div>
        </div>

        <div class="mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalFormKaryawan" onclick="tambahKaryawan()"><i class="fa fa-plus"></i> Tambah Karyawan</button>
                <button class="btn btn-info btn-sm" onclick="exportKaryawan()"><i class="fa fa-file-export"></i> Export Karyawan</button>
            </div>

            <div class="d-flex align-items-center gap-2">
                <select id="filterSite" class="form-select form-select-sm">
                    <option value="">-- Semua Site --</option>
                    <?php
                    $sites = $koneksi->query("SELECT * FROM tb_site ORDER BY nama_site ASC");
                    while ($s = $sites->fetch_assoc()) {
                        echo "<option value='{$s['id_site']}'>{$s['nama_site']}</option>";
                    }
                    ?>
                </select>

                <select id="filterStatus" class="form-select form-select-sm">
                    <option value="Semua">-- Semua Status --</option>
                    <option value="Aktif" selected>Aktif</option>
                    <option value="Resign">Resign</option>
                    <option value="PHK">PHK</option>
                    <option value="Habis Kontrak">Habis Kontrak</option>
                </select>
            </div>
        </div>

        <div class="card">
            <h5 class="card-header"><i class="fa fa-users"></i> Database Karyawan Internal</h5>
            <div class="card-body">
                <div class="table-responsive text-nowrap">
                    <table class="table table-bordered table-hover" id="tableKaryawan" style="width:100%">
                        <thead class="table-info">
                            <tr class="text-center">
                                <th>No</th>
                                <th>NIK</th>
                                <th>Nama Karyawan</th>
                                <th>L/P</th>
                                <th>Site</th>
                                <th>Jabatan</th>
                                <th>Gaji Pokok</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalFormKaryawan" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form id="formKaryawan" class="modal-content">
            <input type="hidden" name="action" id="formAction" value="add">
            <input type="hidden" name="id_karyawan_internal" id="idKaryawan">

            <div class="modal-header bg-success text-white" id="modalHeaderBg">
                <h5 class="modal-title text-white" id="modalTitle"><i class="fa fa-user"></i> Form Karyawan</h5>
                <button type="button" class="btn-close btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body row g-3">
                <div class="col-md-4">
                    <label class="form-label">NIK Karyawan</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light">INT-</span>
                        <input type="text" name="nik_karyawan_internal" id="nik" class="form-control" placeholder="Contoh: 003" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Nama Karyawan</label>
                    <input type="text" name="nama_karyawan" id="nama" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Jenis Kelamin</label>
                    <select name="jenis_kelamin" id="jk" class="form-select" required>
                        <option value="">-- Pilih --</option>
                        <option value="L">Laki-laki</option>
                        <option value="P">Perempuan</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Site (Lokasi)</label>
                    <select name="id_site" id="site" class="form-select" required>
                        <option value="">-- Pilih Site --</option>
                        <?php
                        $sites->data_seek(0);
                        while ($s = $sites->fetch_assoc()) {
                            echo "<option value='{$s['id_site']}'>{$s['nama_site']}</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Jabatan</label>
                    <input type="text" name="jabatan" id="jabatan" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Tanggal Masuk</label>
                    <input type="date" name="tgl_masuk" id="tgl_masuk" class="form-control" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Gaji Pokok</label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="text" name="gaji_pokok" id="gaji" class="form-control nominal-mask" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Nama Bank</label>
                    <input type="text" name="nama_bank" id="bank" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Nomor Rekening</label>
                    <input type="text" name="no_rek" id="rek" class="form-control">
                </div>
                <div class="col-md-12">
                    <label class="form-label">Atas Nama Rekening</label>
                    <input type="text" name="atas_nama_rek" id="an" class="form-control">
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-success" id="btnSubmitForm">Simpan</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="modalEliminasi" tabindex="-1">
    <div class="modal-dialog">
        <form id="formEliminasi" class="modal-content">
            <input type="hidden" name="action" value="eliminasi">
            <input type="hidden" name="id_karyawan_internal" id="elim_id">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title text-white"><i class="fa fa-user-slash"></i> Eliminasi Karyawan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">Proses ini akan mengubah status karyawan menjadi tidak aktif.</div>
                <div class="mb-3">
                    <label>Nama Karyawan</label>
                    <input type="text" id="elim_nama" class="form-control" readonly>
                </div>
                <div class="mb-3">
                    <label>Jenis Keluar (Status)</label>
                    <select name="status_karyawan" class="form-select" required>
                        <option value="Resign">Resign</option>
                        <option value="PHK">PHK</option>
                        <option value="Habis Kontrak">Habis Kontrak</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label>Tanggal Keluar</label>
                    <input type="date" name="tgl_keluar" class="form-control" value="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="mb-3">
                    <label>Keterangan (Opsional)</label>
                    <textarea name="ket_keluar" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-danger">Simpan & Eliminasi</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        // Masking
        document.addEventListener("input", e => {
            // Masking Uang
            if (e.target.classList.contains("nominal-mask")) {
                e.target.value = e.target.value.replace(/\D/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            }

            // Auto-remove tulisan "INT" di form NIK jika admin iseng mengetiknya
            if (e.target.id === "nik") {
                e.target.value = e.target.value.replace(/^int[\s-]?/ig, "");
            }
        });

        let dtKaryawan = $('#tableKaryawan').DataTable({
            processing: true,
            serverSide: false,
            ajax: {
                url: "app/controller/get-data/get-karyawan-internal.php",
                type: "POST",
                data: function(d) {
                    d.site = $("#filterSite").val();
                    d.status = $("#filterStatus").val();
                },
                dataSrc: "data"
            },
            columns: [{
                    data: "no",
                    className: "text-center"
                },
                {
                    data: "nik"
                },
                {
                    data: "nama"
                },
                {
                    data: "jk",
                    className: "text-center"
                },
                {
                    data: "site"
                },
                {
                    data: "jabatan"
                },
                {
                    data: "gaji",
                    className: "text-end fw-bold text-success"
                },
                {
                    data: "status",
                    className: "text-center"
                },
                {
                    data: "aksi",
                    className: "text-center",
                    orderable: false
                }
            ]
        });

        $("#filterSite, #filterStatus").on("change", () => dtKaryawan.ajax.reload());

        window.tambahKaryawan = function() {
            document.getElementById("formKaryawan").reset();
            document.getElementById("formAction").value = "add";
            document.getElementById("modalTitle").innerHTML = '<i class="fa fa-plus"></i> Tambah Karyawan';
            document.getElementById("modalHeaderBg").className = "modal-header bg-success text-white";
        }

        // Modal Edit & Eliminasi
        $(document).on("click", ".btnEdit", function() {
            $.post("app/controller/act-karyawan-internal.php", {
                action: "get",
                id: $(this).data("id")
            }, function(res) {
                if (res.status == 'success') {
                    let d = res.data;
                    $("#formAction").val("edit");
                    $("#idKaryawan").val(d.id_karyawan_internal);
                    // Hilangkan awalan INT- jika ada saat ditampilkan di form edit
                    let nikBersih = d.nik_karyawan_internal.replace(/^INT-/, '');
                    $("#nik").val(nikBersih);
                    $("#nama").val(d.nama_karyawan);
                    $("#jk").val(d.jenis_kelamin);
                    $("#site").val(d.id_site);
                    $("#jabatan").val(d.jabatan);
                    $("#tgl_masuk").val(d.tgl_masuk);
                    $("#gaji").val(d.gaji_pokok.replace(/\B(?=(\d{3})+(?!\d))/g, "."));
                    $("#bank").val(d.nama_bank);
                    $("#rek").val(d.no_rek);
                    $("#an").val(d.atas_nama_rek);

                    $("#modalHeaderBg").attr('class', 'modal-header bg-primary text-white');
                    $("#modalFormKaryawan").modal("show");
                }
            }, "json");
        });

        $(document).on("click", ".btnEliminasi", function() {
            // 1. Reset form terlebih dahulu
            $("#formEliminasi")[0].reset();

            // 2. Baru isi data ID dan Nama dari tombol yang diklik
            $("#elim_id").val($(this).data("id"));
            $("#elim_nama").val($(this).data("nama"));

            // 3. Tampilkan modal
            $("#modalEliminasi").modal("show");
        });

        // Hapus
        $(document).on("click", ".btnDelete", function() {
            if (confirm("Yakin hapus karyawan ini? Log bonus juga akan terhapus!")) {
                $.post("app/controller/act-karyawan-internal.php", {
                    action: "delete",
                    id: $(this).data("id")
                }, function(res) {
                    showToast(res.msg, res.status);
                    if (res.status == 'success') dtKaryawan.ajax.reload();
                }, "json");
            }
        });

        // Submit Add/Edit
        $("#formKaryawan").on("submit", function(e) {
            e.preventDefault();
            let fd = new FormData(this);
            fd.set("gaji_pokok", fd.get("gaji_pokok").replace(/\./g, ""));
            $.ajax({
                url: "app/controller/act-karyawan-internal.php",
                type: "POST",
                data: fd,
                processData: false,
                contentType: false,
                success: function(res) {
                    showToast(res.msg, res.status);
                    if (res.status == 'success') {
                        $("#modalFormKaryawan").modal("hide");
                        dtKaryawan.ajax.reload();
                    }
                }
            });
        });

        // Submit Eliminasi
        $("#formEliminasi").on("submit", function(e) {
            e.preventDefault();
            $.post("app/controller/act-karyawan-internal.php", $(this).serialize(), function(res) {
                showToast(res.msg, res.status);
                if (res.status == 'success') {
                    $("#modalEliminasi").modal("hide");
                    dtKaryawan.ajax.reload();
                }
            }, "json");
        });

        // Import Excel
        $("#formImportKaryawan").on("submit", function(e) {
            e.preventDefault();
            $("#importResult").html('<div class="alert alert-info"><i class="fa fa-spin fa-spinner"></i> Memproses...</div>');
            $.ajax({
                url: "app/controller/csv-control/csv-karyawan-internal.php",
                type: "POST",
                data: new FormData(this),
                processData: false,
                contentType: false,
                success: function(res) {
                    if (res.success) {
                        showToast("Import Sukses", "success");
                        dtKaryawan.ajax.reload();
                        $("#importResult").html(`<div class="alert alert-success">Berhasil: ${res.berhasil}, Gagal: ${res.gagal}</div>`);
                    } else $("#importResult").html(`<div class="alert alert-danger">${res.error}</div>`);
                }
            });
        });

        window.exportKaryawan = function() {
            let site = $("#filterSite").val();
            let status = $("#filterStatus").val();
            window.location.href = `app/controller/export/export-karyawan-internal.php?site=${site}&status=${status}`;
        }
    });
</script>