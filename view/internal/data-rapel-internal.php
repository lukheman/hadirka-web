<style>
    .select2-container--bootstrap-5 .select2-selection {
        border: 1px solid #d9dee3 !important;
        height: 38px !important;
        padding: 0.375rem 0.75rem !important;
        display: flex !important;
        align-items: center;
        border-radius: 0.375rem !important;
    }

    .select2-container--bootstrap-5 {
        width: 100% !important;
    }

    .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
        padding-left: 0 !important;
        margin-top: -2px;
    }
</style>

<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold mb-3"><span class="text-muted fw-light">Sistem Keuangan /</span> Kelola Rapel Internal</h4>

        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title text-white mb-0"><i class="fas fa-file-excel"></i> Import Excel Rapel</h5>
            </div>
            <div class="card-body mt-3">
                <form id="formImportRapel" enctype="multipart/form-data">
                    <p class="instruction-text mb-2">
                        Format Import: <strong>A</strong>=No | <strong>B</strong>=NIK | <strong>C</strong>=Nama | <strong>D</strong>=Tanggal Rapel | <strong>E</strong>=Nominal | <strong>F</strong>=Keterangan
                    </p>
                    <div class="row align-items-end">
                        <div class="col-md-7">
                            <label class="form-label">Upload File Excel Rapel</label>
                            <input type="file" name="file_excel" class="form-control" accept=".xls,.xlsx" required>
                        </div>
                        <div class="col-md-5 mt-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-upload"></i> Import Rapel
                            </button>
                            <a href="assets/template-csv/Format_Impor_Rapel_Internal.xlsx" class="btn btn-outline-secondary ms-2" download>
                                <i class="fas fa-download"></i> Download Template
                            </a>
                        </div>
                    </div>
                </form>
                <div id="importRapelResult" class="mt-3"></div>
            </div>
        </div>

        <div class="mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalRapel" onclick="tambahRapel()"><i class="fa fa-plus"></i> Tambah Rapel Manual</button>
                <button class="btn btn-info btn-sm" onclick="exportRapel()"><i class="fa fa-file-export"></i> Export Log Rapel</button>
            </div>
            <!-- <div class="d-flex align-items-center gap-2">
                <input type="date" id="tglAwal" class="form-control form-control-sm" title="Mulai Tanggal">
                <span>-</span>
                <input type="date" id="tglAkhir" class="form-control form-control-sm" title="Sampai Tanggal">
            </div> -->
            <div class="d-flex align-items-center gap-2">
                <label class="mb-0 fw-bold">Periode:</label>
                <input type="month" id="filterBulan" class="form-control form-control-sm" title="Pilih Bulan">
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive text-nowrap">
                    <table class="table table-bordered table-hover" id="tableRapel" style="width:100%">
                        <thead class="table-primary">
                            <tr class="text-center">
                                <th>No</th>
                                <th>NIK</th>
                                <th>Nama Karyawan</th>
                                <th>Tgl Rapel</th>
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

<div class="modal fade" id="modalRapel" tabindex="-1">
    <div class="modal-dialog">
        <form id="formRapel" class="modal-content">
            <input type="hidden" name="action" id="r_action" value="add">
            <input type="hidden" name="id_rapel" id="id_rapel">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white">Form Kelola Rapel</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body row g-3">
                <div class="col-12">
                    <label>Karyawan Internal</label>
                    <select name="id_karyawan_internal" id="r_karyawan" class="select2-init" required>
                        <option value="">-- Pilih --</option>
                        <?php
                        $k = $koneksi->query("SELECT id_karyawan_internal, nik_karyawan_internal, nama_karyawan FROM tb_karyawan_internal WHERE status_karyawan='Aktif'");
                        while ($r = $k->fetch_assoc()) echo "<option value='{$r['id_karyawan_internal']}'>{$r['nik_karyawan_internal']} / {$r['nama_karyawan']}</option>";
                        ?>
                    </select>
                </div>
                <div class="col-12">
                    <label>Tanggal Rapel</label>
                    <input type="date" name="tgl_rapel" id="r_tgl" class="form-control" required>
                </div>
                <div class="col-12">
                    <label>Nominal</label>
                    <input type="text" name="nominal" id="r_nominal" class="form-control nominal-mask" required>
                </div>
                <div class="col-12">
                    <label>Keterangan</label>
                    <textarea name="ket_rapel" id="r_ket" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        // let today = new Date();
        // let y = today.getFullYear();
        // let m = today.getMonth(); 
        // let d = today.getDate(); // Ambil tanggal hari ini

        // let formatTgl = (year, month, day) => {
        //     let dateObj = new Date(year, month, day);
        //     return dateObj.getFullYear() + "-" + String(dateObj.getMonth() + 1).padStart(2, '0') + "-" + String(dateObj.getDate()).padStart(2, '0');
        // };

        // // Logika Cut-Off Dinamis:
        // // Jika tanggal hari ini lebih dari tanggal 5, masuk periode baru (Tgl 6 bulan ini s/d Tgl 5 bulan depan)
        // // Jika belum lewat tanggal 5, masih periode lama (Tgl 6 bulan lalu s/d Tgl 5 bulan ini)
        // if (d > 5) {
        //     $("#tglAwal").val(formatTgl(y, m, 6)); 
        //     $("#tglAkhir").val(formatTgl(y, m + 1, 5)); 
        // } else {
        //     $("#tglAwal").val(formatTgl(y, m - 1, 6)); 
        //     $("#tglAkhir").val(formatTgl(y, m, 5)); 
        // }

        // Logika Otomatis: Tgl 1-5 tampilkan bulan lalu, Tgl 6 ke atas tampilkan bulan berjalan
        let today = new Date();
        let tglHariIni = today.getDate(); // Ambil tanggal hari ini (1 s/d 31)

        let targetDate = new Date();
        targetDate.setDate(1); // Set ke tgl 1 dulu untuk menghindari bug rollover jika hari ini tgl 31

        // Jika hari ini masih tanggal 1, 2, 3, 4, atau 5
        if (tglHariIni <= 5) {
            targetDate.setMonth(targetDate.getMonth() - 1); // Mundur ke bulan lalu
        }
        // Jika hari ini tanggal 6 ke atas, targetDate dibiarkan (tetap bulan berjalan saat ini)

        let y = targetDate.getFullYear();
        let m = String(targetDate.getMonth() + 1).padStart(2, '0');

        $("#filterBulan").val(`${y}-${m}`);

        // Fungsi helper kalkulasi tgl 1 s/d tgl akhir bulan
        function getPeriodeBulan() {
            let val = $("#filterBulan").val();
            if (!val) return {
                awal: '',
                akhir: ''
            };
            let [year, month] = val.split('-');
            let lastDay = new Date(year, month, 0).getDate();
            return {
                awal: `${year}-${month}-01`,
                akhir: `${year}-${month}-${lastDay}`
            };
        }

        $('.select2-init').select2({
            theme: 'bootstrap-5',
            dropdownParent: $('#modalRapel'),
            width: '100%'
        });

        document.addEventListener("input", e => {
            if (e.target.classList.contains("nominal-mask")) {
                e.target.value = e.target.value.replace(/\D/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            }
        });

        let dtRapel = $('#tableRapel').DataTable({
            ajax: {
                url: "app/controller/get-data/get-rapel-internal.php",
                type: "POST",
                data: function(d) {
                    // d.tgl_awal = $("#tglAwal").val();
                    // d.tgl_akhir = $("#tglAkhir").val();
                    let p = getPeriodeBulan();
                    d.tgl_awal = p.awal;
                    d.tgl_akhir = p.akhir;
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
                    className: "text-end fw-bold text-primary"
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

        // $("#tglAwal, #tglAkhir").on("change", () => dtRapel.ajax.reload());
        $("#filterBulan").on("change", () => dtRapel.ajax.reload());

        window.tambahRapel = function() {
            $("#formRapel")[0].reset();
            $("#r_karyawan").val("").trigger("change");
            $("#r_action").val("add");
        }

        $(document).on("click", ".btnEditRapel", function() {
            let d = $(this).data();
            $("#r_action").val("edit");
            $("#id_rapel").val(d.id);
            $("#r_karyawan").val(d.karyawan).trigger("change");
            $("#r_tgl").val(d.tgl);
            $("#r_nominal").val(d.nominal.toString().replace(/\B(?=(\d{3})+(?!\d))/g, "."));
            $("#r_ket").val(d.ket);
            $("#modalRapel").modal("show");
        });

        $(document).on("click", ".btnDeleteRapel", function() {
            if (confirm("Hapus data rapel ini?")) {
                $.ajax({
                    url: "app/controller/act-rapel-internal.php",
                    type: "POST",
                    data: {
                        action: "delete",
                        id_rapel: $(this).data("id")
                    },
                    dataType: "json",
                    success: function(res) {
                        showToast(res.msg, res.status);
                        if (res.status === 'success') dtRapel.ajax.reload(null, false);
                    }
                });
            }
        });

        $("#formRapel").on("submit", function(e) {
            e.preventDefault();
            let fd = new FormData(this);
            fd.set("nominal", fd.get("nominal").replace(/\./g, ""));
            $.ajax({
                url: "app/controller/act-rapel-internal.php",
                type: "POST",
                data: fd,
                processData: false,
                contentType: false,
                success: function(res) {
                    showToast(res.msg, res.status);
                    if (res.status == 'success') {
                        $("#modalRapel").modal("hide");
                        dtRapel.ajax.reload();
                    }
                }
            });
        });

        // KODINGAN AJAX UNTUK IMPORT EXCEL RAPEL
        $("#formImportRapel").on("submit", function(e) {
            e.preventDefault();
            $("#importRapelResult").html('<div class="alert alert-info"><i class="fa fa-spinner fa-spin"></i> Memproses...</div>');

            $.ajax({
                url: "app/controller/csv-control/csv-rapel-internal.php",
                type: "POST",
                data: new FormData(this),
                processData: false,
                contentType: false,
                success: function(res) {
                    if (res.success) {
                        showToast("Import Sukses", "success");
                        dtRapel.ajax.reload();
                        $("#importRapelResult").html(`<div class="alert alert-success">Berhasil diimport: ${res.berhasil} baris | Gagal/Dilewati: ${res.gagal} baris</div>`);
                        $("#formImportRapel")[0].reset(); // Mengosongkan input file
                    } else {
                        $("#importRapelResult").html(`<div class="alert alert-danger">${res.error}</div>`);
                    }
                },
                error: function(xhr) {
                    $("#importRapelResult").html('<div class="alert alert-danger">Terjadi kesalahan pada server (500)</div>');
                    console.error(xhr.responseText);
                }
            });
        });

        // Export Logic
        window.exportRapel = () => {
            // let tgl_awal = $("#tglAwal").val();
            // let tgl_akhir = $("#tglAkhir").val();
            // window.location.href = `app/controller/export/export-rapel-internal.php?tgl_awal=${tgl_awal}&tgl_akhir=${tgl_akhir}`;
            let p = getPeriodeBulan();
            window.location.href = `app/controller/export/export-rapel-internal.php?tgl_awal=${p.awal}&tgl_akhir=${p.akhir}`;
        };
    });
</script>