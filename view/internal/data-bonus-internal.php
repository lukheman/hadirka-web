<style>
    /* Pastikan ini ada di dalam tag <style> kamu */
    .select2-container--bootstrap-5 .select2-selection {
        border: 1px solid #d9dee3 !important;
        height: 38px !important;
        /* Menyamakan tinggi dengan input form-control */
        padding: 0.375rem 0.75rem !important;
        /* Menyamakan padding */
        display: flex !important;
        align-items: center;

        /* KODE BARU: MENGATUR KELENGKUNGAN SUDUT (BORDER RADIUS) */
        border-radius: 0.375rem !important;
    }

    /* Memastikan Select2 mengisi lebar penuh */
    .select2-container--bootstrap-5 {
        width: 100% !important;
    }

    /* Memperbaiki posisi arrow dropdown agar tidak berantakan */
    .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
        padding-left: 0 !important;
        margin-top: -2px;
    }
</style>
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
                            <a href="assets/template-csv/Format_Impor_Bonus_Internal.xlsx" class="btn btn-outline-secondary ms-2" download>
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
                    <select name="id_karyawan_internal" id="b_karyawan" class="select2-init" required>
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
        // // 1. SET DEFAULT FILTER (Misal: Tanggal 6 bulan lalu s/d Tanggal 5 bulan ini)
        // let today = new Date();
        // let y = today.getFullYear();
        // let m = today.getMonth(); // Bulan ini (0-11)
        // let d = today.getDate();

        // // Format YYYY-MM-DD
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

        // Default bulan otomatis: tgl 1-5 = bulan lalu (terpusat di assets/js/periode.js)
        setDefaultBulan();

        // getPeriodeBulan(): pakai assets/js/periode.js (terpusat, jangan definisikan ulang).

        // 2. INISIALISASI PLUGIN & EVENT LISTENER
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

        // 3. DATATABLES CONFIGURATION
        let dtBonus = $('#tableBonus').DataTable({
            ajax: {
                url: "app/controller/get-data/get-bonus-internal.php",
                type: "POST",
                data: function(d) {
                    // KIRIM TANGGAL AWAL DAN AKHIR
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

        // 4. TRIGGER RELOAD TABEL SAAT BULAN DIUBAH
        // $("#tglAwal, #tglAkhir").on("change", () => dtBonus.ajax.reload());
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

        $(document).on("click", ".btnDelete", async function() {
            if (await confirmDialog("Hapus bonus ini?")) {
                $.ajax({
                    url: "app/controller/act-bonus-internal.php",
                    type: "POST",
                    data: {
                        action: "delete",
                        id_bonus: $(this).data("id")
                    },
                    dataType: "json",
                    success: function(res) {
                        showToast(res.msg, res.status);
                        if (res.status === 'success') {
                            dtBonus.ajax.reload(null, false); // false agar tidak kembali ke halaman 1
                        }
                    },
                    error: function(xhr) {
                        showToast("Terjadi kesalahan pada server saat menghapus data", "error");
                        console.error(xhr.responseText);
                    }
                });
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

        // 5. BAWA PARAMETER BULAN SAAT EXPORT KE EXCEL
        window.exportBonus = () => {
            // let tgl_awal = $("#tglAwal").val();
            // let tgl_akhir = $("#tglAkhir").val();
            // window.location.href = `app/controller/export/export-bonus-internal.php?tgl_awal=${tgl_awal}&tgl_akhir=${tgl_akhir}`;
            let p = getPeriodeBulan();
            window.location.href = `app/controller/export/export-bonus-internal.php?tgl_awal=${p.awal}&tgl_akhir=${p.akhir}`;
        };
    });
</script>