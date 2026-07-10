<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold mb-3"><span class="text-muted fw-light">Sistem Keuangan /</span> Kelola Bonus Internal</h4>

        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="card-title text-white mb-0"><i class="fas fa-file-excel"></i> Import Excel Bonus</h5>
            </div>
            <div class="card-body mt-3">
                <form id="formImportBonus" enctype="multipart/form-data">
                    <p class="instruction-text mb-2">
                        Format Import: <strong>A</strong>=No | <strong>B</strong>=NIK | <strong>C</strong>=Nama | <strong>D</strong>=Tanggal Bonus | <strong>E</strong>=Nominal | <strong>F</strong>=Keterangan
                    </p>
                    <div class="row align-items-end">
                        <div class="col-md-7">
                            <label class="form-label">Upload File Excel Bonus</label>
                            <input type="file" name="file_excel" class="form-control" accept=".xls,.xlsx" required>
                        </div>
                        <div class="col-md-5 mt-2">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-upload"></i> Import Bonus
                            </button>
                            <a href="assets/template-csv/Format_Bonus_Internal.xlsx" class="btn btn-outline-secondary ms-2" download>
                                <i class="fas fa-download"></i> Download Template
                            </a>
                        </div>
                    </div>
                </form>
                <div id="importBonusResult" class="mt-3"></div>
            </div>
        </div>

        <div class="mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalBonus" onclick="tambahBonus()"><i class="fa fa-plus"></i> Tambah Bonus Manual</button>
                <button class="btn btn-info btn-sm" onclick="exportBonus()"><i class="fa fa-file-export"></i> Export Log Bonus</button>
            </div>
            <div class="d-flex align-items-center">
                <input type="month" id="filterBulan" class="form-control form-control-sm" data-bs-toggle="tooltip" title="Filter Berdasarkan Bulan">
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive text-nowrap">
                    <table class="table table-bordered table-hover" id="tableBonus" style="width:100%">
                        <thead class="table-info">
                            <tr class="text-center">
                                <th>No</th>
                                <th>NIK</th>
                                <th>Nama Karyawan</th>
                                <th>Tgl Bonus</th>
                                <th>Nominal</th>
                                <th>Keterangan</th>
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

<div class="modal fade" id="modalBonus" tabindex="-1">
    <div class="modal-dialog">
        <form id="formBonus" class="modal-content">
            <input type="hidden" name="action" id="b_action" value="add">
            <input type="hidden" name="id_bonus" id="id_bonus">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title text-white">Form Kelola Bonus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body row g-3">
                <div class="col-12">
                    <label>Karyawan Internal</label>
                    <select name="id_karyawan_internal" id="b_karyawan" class="form-select select2-init" required>
                        <option value="">-- Pilih --</option>
                        <?php
                        $k = $koneksi->query("SELECT id_karyawan_internal, nik_karyawan_internal, nama_karyawan FROM tb_karyawan_internal WHERE status_karyawan='Aktif'");
                        while ($r = $k->fetch_assoc()) echo "<option value='{$r['id_karyawan_internal']}'>{$r['nik_karyawan_internal']} - {$r['nama_karyawan']}</option>";
                        ?>
                    </select>
                </div>
                <div class="col-12">
                    <label>Tanggal Bonus</label>
                    <input type="date" name="tgl_bonus" id="b_tgl" class="form-control" required>
                </div>
                <div class="col-12">
                    <label>Nominal</label>
                    <input type="text" name="nominal" id="b_nominal" class="form-control nominal-mask" required>
                </div>
                <div class="col-12">
                    <label>Keterangan</label>
                    <textarea name="ket_bonus" id="b_ket" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-success">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        $('.select2-init').select2({
            theme: 'bootstrap-5',
            dropdownParent: $('#modalBonus'),
            width: '100%'
        });

        document.addEventListener("input", e => {
            if (e.target.classList.contains("nominal-mask")) {
                e.target.value = e.target.value.replace(/\D/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            }
        });

        let dtBonus = $('#tableBonus').DataTable({
            ajax: {
                url: "app/controller/get-data/get-bonus-internal.php",
                type: "POST",
                data: function(d) {
                    // Kirim data filter bulan ke backend
                    d.bulan = $("#filterBulan").val();
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
                    data: "tgl",
                    className: "text-center"
                },
                {
                    data: "nominal",
                    className: "text-end fw-bold text-success"
                },
                {
                    data: "ket"
                },
                {
                    data: "aksi",
                    className: "text-center"
                }
            ],
            order: [
                [3, 'desc']
            ]
        });

        // Trigger reload tabel saat bulan diubah
        $("#filterBulan").on("change", () => dtBonus.ajax.reload());

        window.tambahBonus = function() {
            $("#formBonus")[0].reset();
            $("#b_karyawan").val("").trigger("change");
            $("#b_action").val("add");
        }

        $(document).on("click", ".btnEdit", function() {
            let d = $(this).data();
            $("#b_action").val("edit");
            $("#id_bonus").val(d.id);
            $("#b_karyawan").val(d.karyawan).trigger("change");
            $("#b_tgl").val(d.tgl);
            $("#b_nominal").val(d.nominal.toString().replace(/\B(?=(\d{3})+(?!\d))/g, "."));
            $("#b_ket").val(d.ket);
            $("#modalBonus").modal("show");
        });

        $(document).on("click", ".btnDelete", function() {
            if (confirm("Hapus bonus ini?")) {
                $.post("app/controller/act-bonus-internal.php", {
                    action: "delete",
                    id_bonus: $(this).data("id")
                }, function(res) {
                    showToast(res.msg, res.status);
                    dtBonus.ajax.reload();
                }, "json");
            }
        });

        $("#formBonus").on("submit", function(e) {
            e.preventDefault();
            let fd = new FormData(this);
            fd.set("nominal", fd.get("nominal").replace(/\./g, ""));
            $.ajax({
                url: "app/controller/act-bonus-internal.php",
                type: "POST",
                data: fd,
                processData: false,
                contentType: false,
                success: function(res) {
                    showToast(res.msg, res.status);
                    if (res.status == 'success') {
                        $("#modalBonus").modal("hide");
                        dtBonus.ajax.reload();
                    }
                }
            });
        });

        $("#formImportBonus").on("submit", function(e) {
            e.preventDefault();
            $("#importBonusResult").html('<div class="alert alert-info">Memproses...</div>');
            $.ajax({
                url: "app/controller/csv-control/csv-bonus-internal.php",
                type: "POST",
                data: new FormData(this),
                processData: false,
                contentType: false,
                success: function(res) {
                    if (res.success) {
                        showToast("Sukses", "success");
                        dtBonus.ajax.reload();
                        $("#importBonusResult").html(`<div class="alert alert-success">Berhasil: ${res.berhasil}</div>`);
                    } else $("#importBonusResult").html(`<div class="alert alert-danger">${res.error}</div>`);
                }
            });
        });

        // Bawa param bulan_tahun ke file export agar data yang didownload sesuai filter
        window.exportBonus = () => {
            let bulan_tahun = $("#filterBulan").val();
            window.location.href = `app/controller/export/export-bonus-internal.php?bulan_tahun=${bulan_tahun}`;
        };
    });
</script>