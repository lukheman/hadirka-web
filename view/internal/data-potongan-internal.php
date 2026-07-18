<style>
    .select2-container--bootstrap-5 .select2-selection {
        border: 1px solid #d9dee3 !important;
        height: 38px !important;
        padding: 0.375rem 0.75rem !important;
        display: flex !important;
        align-items: center;
        border-radius: 0.375rem !important;
    }
    .select2-container--bootstrap-5 { width: 100% !important; }
    .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
        padding-left: 0 !important; margin-top: -2px;
    }
</style>

<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold mb-3"><span class="text-muted fw-light">Sistem Keuangan /</span> Kelola Potongan Internal</h4>

        <!-- Card Import -->
        <div class="card mb-4">
            <div class="card-header bg-danger text-white">
                <h5 class="card-title text-white mb-0"><i class="fas fa-file-excel"></i> Import Excel Potongan</h5>
            </div>
            <div class="card-body mt-3">
                <form id="formImportPotongan" enctype="multipart/form-data">
                    <p class="instruction-text mb-2">
                        Format Import: <strong>A</strong>=No | <strong>B</strong>=NIK | <strong>C</strong>=Nama | <strong>D</strong>=Tanggal Potongan | <strong>E</strong>=Nominal | <strong>F</strong>=Keterangan
                    </p>
                    <div class="row align-items-end">
                        <div class="col-md-6">
                            <label class="form-label">Upload File Excel Potongan</label>
                            <input type="file" name="file_excel" class="form-control" accept=".xls,.xlsx" required>
                        </div>
                        <div class="col-md-6 mt-2">
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-upload"></i> Import Potongan
                            </button>
                            <a href="assets/template-csv/Format_Impor_Potongan_Internal.xlsx" class="btn btn-outline-secondary ms-2" download>
                                <i class="fas fa-download"></i> Download Template
                            </a>
                        </div>
                    </div>
                </form>
                <div id="importPotonganResult" class="mt-3"></div>
            </div>
        </div>

        <!-- Filter & Tombol Aksi -->
        <div class="mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modalPotongan" onclick="tambahPotongan()"><i class="fa fa-plus"></i> Tambah Potongan Manual</button>
                <button class="btn btn-info btn-sm" onclick="exportPotongan()"><i class="fa fa-file-export"></i> Export Log Potongan</button>
            </div>
            <div class="d-flex align-items-center gap-2">
                <input type="date" id="tglAwal" class="form-control form-control-sm" title="Mulai Tanggal">
                <span>-</span>
                <input type="date" id="tglAkhir" class="form-control form-control-sm" title="Sampai Tanggal">
            </div>
        </div>

        <!-- Tabel Data -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive text-nowrap">
                    <table class="table table-bordered table-hover" id="tablePotongan" style="width:100%">
                        <thead class="table-danger">
                            <tr class="text-center">
                                <th>No</th>
                                <th>NIK</th>
                                <th>Nama Karyawan</th>
                                <th>Tgl Potongan</th>
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

<!-- Modal Form -->
<div class="modal fade" id="modalPotongan" tabindex="-1">
    <div class="modal-dialog">
        <form id="formPotongan" class="modal-content">
            <input type="hidden" name="action" id="p_action" value="add">
            <input type="hidden" name="id_potongan" id="id_potongan">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title text-white">Form Kelola Potongan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body row g-3">
                <div class="col-12">
                    <label>Karyawan Internal</label>
                    <select name="id_karyawan_internal" id="p_karyawan" class="select2-init" required>
                        <option value="">-- Pilih --</option>
                        <?php
                        $k = $koneksi->query("SELECT id_karyawan_internal, nik_karyawan_internal, nama_karyawan FROM tb_karyawan_internal WHERE status_karyawan='Aktif'");
                        while ($r = $k->fetch_assoc()) echo "<option value='{$r['id_karyawan_internal']}'>{$r['nik_karyawan_internal']} / {$r['nama_karyawan']}</option>";
                        ?>
                    </select>
                </div>
                <div class="col-12">
                    <label>Tanggal Potongan</label>
                    <input type="date" name="tgl_potongan" id="p_tgl" class="form-control" required>
                </div>
                <div class="col-12">
                    <label>Nominal Potongan</label>
                    <input type="text" name="nominal" id="p_nominal" class="form-control nominal-mask" required>
                </div>
                <div class="col-12">
                    <label>Keterangan</label>
                    <textarea name="ket_potongan" id="p_ket" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-danger">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        let today = new Date();
        let y = today.getFullYear();
        let m = today.getMonth();
        let d = today.getDate(); // Ambil tanggal hari ini

        let formatTgl = (year, month, day) => {
            let dateObj = new Date(year, month, day);
            return dateObj.getFullYear() + "-" + String(dateObj.getMonth() + 1).padStart(2, '0') + "-" + String(dateObj.getDate()).padStart(2, '0');
        }; 

        // Logika Cut-Off Dinamis:
        // Jika tanggal hari ini lebih dari tanggal 5, masuk periode baru (Tgl 6 bulan ini s/d Tgl 5 bulan depan)
        // Jika belum lewat tanggal 5, masih periode lama (Tgl 6 bulan lalu s/d Tgl 5 bulan ini)
        if (d > 5) {
            $("#tglAwal").val(formatTgl(y, m, 6)); 
            $("#tglAkhir").val(formatTgl(y, m + 1, 5)); 
        } else {
            $("#tglAwal").val(formatTgl(y, m - 1, 6)); 
            $("#tglAkhir").val(formatTgl(y, m, 5)); 
        }

        $('.select2-init').select2({
            theme: 'bootstrap-5',
            dropdownParent: $('#modalPotongan'),
            width: '100%'
        });

        document.addEventListener("input", e => {
            if (e.target.classList.contains("nominal-mask")) {
                e.target.value = e.target.value.replace(/\D/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            }
        });

        let dtPotongan = $('#tablePotongan').DataTable({
            ajax: {
                url: "app/controller/get-data/get-potongan-internal.php",
                type: "POST",
                data: function(d) {
                    d.tgl_awal = $("#tglAwal").val();
                    d.tgl_akhir = $("#tglAkhir").val();
                },
                dataSrc: "data"
            },
            columns: [
                { data: "no", className: "text-center" },
                { data: "nik" },
                { data: "nama" },
                { data: "tgl", className: "text-center" },
                { data: "nominal", className: "text-end fw-bold text-danger" },
                { data: "ket" },
                { data: "aksi", className: "text-center" }
            ],
            order: [[3, 'desc']]
        });

        $("#tglAwal, #tglAkhir").on("change", () => dtPotongan.ajax.reload());

        window.tambahPotongan = function() {
            $("#formPotongan")[0].reset();
            $("#p_karyawan").val("").trigger("change");
            $("#p_action").val("add");
        }

        $(document).on("click", ".btnEditPotongan", function() {
            let d = $(this).data();
            $("#p_action").val("edit");
            $("#id_potongan").val(d.id);
            $("#p_karyawan").val(d.karyawan).trigger("change");
            $("#p_tgl").val(d.tgl);
            $("#p_nominal").val(d.nominal.toString().replace(/\B(?=(\d{3})+(?!\d))/g, "."));
            $("#p_ket").val(d.ket);
            $("#modalPotongan").modal("show");
        });

        $(document).on("click", ".btnDeletePotongan", function() {
            if (confirm("Hapus data potongan ini?")) {
                $.ajax({
                    url: "app/controller/act-potongan-internal.php",
                    type: "POST",
                    data: { action: "delete", id_potongan: $(this).data("id") },
                    dataType: "json",
                    success: function(res) {
                        showToast(res.msg, res.status);
                        if (res.status === 'success') dtPotongan.ajax.reload(null, false);
                    }
                });
            }
        });

        $("#formPotongan").on("submit", function(e) {
            e.preventDefault();
            let fd = new FormData(this);
            fd.set("nominal", fd.get("nominal").replace(/\./g, ""));
            $.ajax({
                url: "app/controller/act-potongan-internal.php",
                type: "POST",
                data: fd,
                processData: false,
                contentType: false,
                success: function(res) {
                    showToast(res.msg, res.status);
                    if (res.status == 'success') {
                        $("#modalPotongan").modal("hide");
                        dtPotongan.ajax.reload();
                    }
                }
            });
        });

        // KODINGAN AJAX UNTUK IMPORT EXCEL POTONGAN
        $("#formImportPotongan").on("submit", function(e) {
            e.preventDefault();
            $("#importPotonganResult").html('<div class="alert alert-info"><i class="fa fa-spinner fa-spin"></i> Memproses...</div>');
            
            $.ajax({
                url: "app/controller/csv-control/csv-potongan-internal.php",
                type: "POST",
                data: new FormData(this),
                processData: false,
                contentType: false,
                success: function(res) {
                    if (res.success) {
                        showToast("Import Sukses", "success");
                        dtPotongan.ajax.reload();
                        $("#importPotonganResult").html(`<div class="alert alert-success">Berhasil diimport: ${res.berhasil} baris | Gagal/Dilewati: ${res.gagal} baris</div>`);
                        $("#formImportPotongan")[0].reset(); // Mengosongkan input file
                    } else {
                        $("#importPotonganResult").html(`<div class="alert alert-danger">${res.error}</div>`);
                    }
                },
                error: function(xhr) {
                    $("#importPotonganResult").html('<div class="alert alert-danger">Terjadi kesalahan pada server (500)</div>');
                    console.error(xhr.responseText);
                }
            });
        });
        
        // Export Logic
        window.exportPotongan = () => {
            let tgl_awal = $("#tglAwal").val();
            let tgl_akhir = $("#tglAkhir").val();
            window.location.href = `app/controller/export/export-potongan-internal.php?tgl_awal=${tgl_awal}&tgl_akhir=${tgl_akhir}`;
        };
    });
</script>