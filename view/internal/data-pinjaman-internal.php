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

    .format-badge {
        background: #e9ecef;
        color: #495057;
        padding: 4px 10px;
        border-radius: 6px;
        font-family: monospace;
        font-weight: bold;
    }
</style>

<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold mb-3"><span class="text-muted fw-light">Sistem Keuangan /</span> Kelola Pinjaman Internal</h4>

        <!-- Card Import -->
        <div class="card mb-4">
            <div class="card-header bg-warning text-dark">
                <h5 class="card-title text-dark mb-0"><i class="fas fa-file-excel"></i> Import Excel Pinjaman</h5>
            </div>
            <div class="card-body mt-3">
                <form id="formImportPinjaman" enctype="multipart/form-data">
                    <p class="instruction-text mb-2">
                        Format Import:
                        <span class="format-badge">A: No</span>
                        <span class="format-badge">B: NIK</span>
                        <span class="format-badge">C: Nama</span>
                        <span class="format-badge">D: Nominal Total</span>
                        <span class="format-badge">E: Tgl Pencairan</span>
                        <span class="format-badge">F: Tgl Bayar (Awal)</span>
                        <span class="format-badge">G: Keterangan</span>
                        <span class="format-badge bg-warning text-dark">H: Tenor (Bulan)</span>
                    </p>
                    <div class="row align-items-end">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <label class="form-label">Upload File Excel Pinjaman</label>
                            <input type="file" name="file_excel" class="form-control" accept=".xls,.xlsx,.csv" required>
                        </div>
                        <div class="col-md-6 d-flex gap-2">
                            <button type="submit" class="btn btn-warning text-dark text-nowrap fw-bold">
                                <i class="fas fa-upload"></i> Import Pinjaman
                            </button>
                            <a href="assets/template-csv/Format_Impor_Pinjaman_Internal.xlsx" class="btn btn-outline-secondary text-nowrap" download>
                                <i class="fas fa-download"></i> Download Template
                            </a>
                        </div>
                    </div>
                </form>
                <div id="importPinjamanResult" class="mt-3"></div>
            </div>
        </div>

        <!-- Filter & Tombol Aksi -->
        <div class="mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <button class="btn btn-warning text-dark fw-bold btn-sm" data-bs-toggle="modal" data-bs-target="#modalPinjaman" onclick="tambahPinjaman()">
                    <i class="fa fa-plus"></i> Tambah Pinjaman
                </button>
                <button class="btn btn-info btn-sm" onclick="exportPinjaman()"><i class="fa fa-file-export"></i> Export Pinjaman</button>
                <button class="btn btn-dark btn-sm ms-1" onclick="postingPinjamanInternal()">
                    <i class="fa fa-paper-plane"></i> Posting ke Keuangan
                </button>
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

        <!-- Tabel Data -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive text-nowrap">
                    <table class="table table-bordered table-hover" id="tablePinjaman" style="width:100%">
                        <thead class="table-warning">
                            <tr class="text-center text-dark">
                                <th>No</th>
                                <th>NIK</th>
                                <th>Nama Karyawan</th>
                                <th>Tgl Pencairan</th>
                                <th>Tgl Bayar</th>
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
<div class="modal fade" id="modalPinjaman" tabindex="-1">
    <div class="modal-dialog">
        <form id="formPinjaman" class="modal-content">
            <input type="hidden" name="action" id="pinj_action" value="add">
            <input type="hidden" name="id_pinjaman" id="id_pinjaman">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title text-dark">Form Kelola Pinjaman</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body row g-3">
                <div class="col-12">
                    <label>Karyawan Internal</label>
                    <select name="id_karyawan_internal" id="pinj_karyawan" class="select2-init" required>
                        <option value="">-- Pilih --</option>
                        <?php
                        $k = $koneksi->query("SELECT id_karyawan_internal, nik_karyawan_internal, nama_karyawan FROM tb_karyawan_internal WHERE status_karyawan='Aktif'");
                        while ($r = $k->fetch_assoc()) echo "<option value='{$r['id_karyawan_internal']}'>{$r['nik_karyawan_internal']} / {$r['nama_karyawan']}</option>";
                        ?>
                    </select>
                </div>

                <div class="col-12 mb-3">
                    <label>Tanggal Pencairan Pinjaman</label>
                    <input type="date" name="tgl_pinjaman" id="pinj_tgl_cair" class="form-control" required>
                </div>

                <!-- Bagian Total & Tenor -->
                <div class="col-12 row pe-0"> <!-- Hapus id="areaHitungCicilan" dari baris ini -->
                    <div class="col-md-8 mb-3 pe-0">
                        <label id="labelInputNominal">Total Pinjaman (Rp)</label>
                        <input type="text" name="nominal_pinjaman" id="pinj_plafon" class="form-control nominal-mask" required>
                    </div>
                    <!-- Pindahkan ID penyembunyi hanya pada kolom tenor dan small text -->
                    <div class="col-md-4 pe-0" id="areaTenor">
                        <label>Tenor (Bln)</label>
                        <input type="number" name="tenor" id="pinj_tenor" class="form-control" value="1" min="1" required>
                    </div>
                    <div class="col-12" id="areaKetTenor">
                        <small class="text-muted">* Biarkan angka 1 jika potong lunas 1x bayar.</small>
                    </div>
                </div>

                <div class="col-12">
                    <div class="alert alert-info py-2" style="display: none;" id="infoCicilan">
                        Dipotong per bulan: <b id="textCicilan">Rp 0</b>
                    </div>
                </div>

                <div class="col-12">
                    <label id="labelTglBayar">Tanggal Mulai Pembayaran</label>
                    <input type="date" name="tgl_bayar_pinjaman" id="pinj_tgl_bayar" class="form-control" required>
                </div>

                <div class="col-12">
                    <label>Keterangan</label>
                    <textarea name="ket_pinjaman" id="pinj_ket" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-warning text-dark fw-bold">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        // let today = new Date();
        // let y = today.getFullYear();
        // let m = today.getMonth();
        // let d = today.getDate();

        // let formatTgl = (year, month, day) => {
        //     let dateObj = new Date(year, month, day);
        //     return dateObj.getFullYear() + "-" + String(dateObj.getMonth() + 1).padStart(2, '0') + "-" + String(dateObj.getDate()).padStart(2, '0');
        // };

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
            dropdownParent: $('#modalPinjaman'),
            width: '100%'
        });

        // === EVENT LISTENER MASKING NOMINAL (VERSI ANTI-LOMPAT) ===
        document.addEventListener("input", e => {
            if (e.target.classList.contains("nominal-mask")) {
                let input = e.target;

                // Simpan posisi kursor saat ini
                let cursorPos = input.selectionStart;
                let prevLength = input.value.length;

                // Bersihkan dan format ulang angkanya
                let val = input.value.replace(/\D/g, "");
                let formatted = val.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                input.value = formatted;

                // Kembalikan posisi kursor ke tempat semula (disesuaikan dengan penambahan titik)
                let diff = formatted.length - prevLength;
                input.setSelectionRange(cursorPos + diff, cursorPos + diff);
            }
        });

        let dtPinjaman = $('#tablePinjaman').DataTable({
            ajax: {
                url: "app/controller/get-data/get-pinjaman-internal.php",
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
                    data: "tgl_cair",
                    className: "text-center"
                },
                {
                    data: "tgl_bayar",
                    className: "text-center"
                },
                {
                    data: "nominal",
                    className: "text-end fw-bold text-danger"
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
                [4, 'desc']
            ] // Sort berdasarkan tanggal bayar
        });

        // $("#tglAwal, #tglAkhir").on("change", () => dtPinjaman.ajax.reload());
        $("#filterBulan").on("change", () => dtPinjaman.ajax.reload());

        // Logika Hitung Cicilan
        const inputPlafon = document.getElementById("pinj_plafon");
        const inputTenor = document.getElementById("pinj_tenor");
        const infoCicilan = document.getElementById("infoCicilan");
        const textCicilan = document.getElementById("textCicilan");
        const labelTglBayar = document.getElementById("labelTglBayar");

        function hitungCicilan() {
            // TAMBAHAN: Matikan logika hitung cicilan jika sedang dalam mode EDIT
            if ($("#pinj_action").val() === "edit") {
                infoCicilan.style.display = "none";
                labelTglBayar.innerText = "Tanggal Pembayaran (Cicilan Ini)";
                return; // Hentikan fungsi sampai di sini
            }

            // Logika di bawah ini HANYA AKAN JALAN saat mode TAMBAH (add)
            let plafonStr = inputPlafon.value.replace(/\./g, "");
            let plafon = parseFloat(plafonStr) || 0;
            let tenor = parseInt(inputTenor.value) || 1;

            if (tenor > 1) {
                labelTglBayar.innerText = "Tanggal Mulai Pembayaran";
            } else {
                labelTglBayar.innerText = "Tanggal Pembayaran";
            }

            if (plafon > 0 && tenor > 1) {
                let cicilanDasar = Math.floor(plafon / tenor);
                let sisaBagi = plafon - (cicilanDasar * tenor);
                let cicilanTerakhir = cicilanDasar + sisaBagi;

                if (sisaBagi > 0) {
                    textCicilan.innerText = "Rp " + cicilanDasar.toLocaleString('id-ID') + " (Bulan terakhir: Rp " + cicilanTerakhir.toLocaleString('id-ID') + ")";
                } else {
                    textCicilan.innerText = "Rp " + cicilanDasar.toLocaleString('id-ID');
                }
                infoCicilan.style.display = "block";
            } else {
                infoCicilan.style.display = "none";
            }
        }

        inputPlafon.addEventListener("input", hitungCicilan);
        inputTenor.addEventListener("input", hitungCicilan);

        window.tambahPinjaman = function() {
            $("#formPinjaman")[0].reset();
            $("#pinj_karyawan").val("").trigger("change");
            $("#pinj_action").val("add");

            // Kembalikan tampilan ke mode Tambah (Munculkan Tenor)
            $("#areaTenor").show();
            $("#areaKetTenor").show();
            $("#labelInputNominal").text("Total Pinjaman (Rp)"); // Kembalikan label asli

            hitungCicilan();
        }

        $(document).on("click", ".btnEditPinjaman", function() {
            let d = $(this).data();
            $("#pinj_action").val("edit");
            $("#id_pinjaman").val(d.id);
            $("#pinj_karyawan").val(d.karyawan).trigger("change");
            $("#pinj_tgl_cair").val(d.tglcair);
            $("#pinj_tgl_bayar").val(d.tglbayar);
            $("#pinj_ket").val(d.ket);

            // Masukkan nominal ke input form
            $("#pinj_plafon").val(d.nominal.toString().replace(/\B(?=(\d{3})+(?!\d))/g, "."));

            // Mode Edit: Sembunyikan Tenor, ubah label Nominal
            $("#areaTenor").hide();
            $("#areaKetTenor").hide();
            $("#labelInputNominal").text("Nominal Cicilan Ini (Rp)"); // Ubah label agar relevan

            infoCicilan.style.display = "none";
            labelTglBayar.innerText = "Tanggal Pembayaran (Cicilan Ini)";

            $("#modalPinjaman").modal("show");
        });

        $(document).on("click", ".btnDeletePinjaman", function() {
            if (confirm("Hapus baris pinjaman ini?")) {
                $.ajax({
                    url: "app/controller/act-pinjaman-internal.php",
                    type: "POST",
                    data: {
                        action: "delete",
                        id_pinjaman: $(this).data("id")
                    },
                    dataType: "json",
                    success: function(res) {
                        showToast(res.msg, res.status);
                        if (res.status === 'success') dtPinjaman.ajax.reload(null, false);
                    }
                });
            }
        });

        $("#formPinjaman").on("submit", function(e) {
            e.preventDefault();
            let fd = new FormData(this);
            fd.set("nominal_pinjaman", fd.get("nominal_pinjaman").replace(/\./g, ""));
            $.ajax({
                url: "app/controller/act-pinjaman-internal.php",
                type: "POST",
                data: fd,
                processData: false,
                contentType: false,
                success: function(res) {
                    showToast(res.msg, res.status);
                    if (res.status == 'success') {
                        $("#modalPinjaman").modal("hide");
                        dtPinjaman.ajax.reload();
                    }
                }
            });
        });

        // AJAX IMPORT EXCEL
        $("#formImportPinjaman").on("submit", function(e) {
            e.preventDefault();
            $("#importPinjamanResult").html('<div class="alert alert-info"><i class="fa fa-spinner fa-spin"></i> Memproses file...</div>');

            $.ajax({
                url: "app/controller/csv-control/csv-pinjaman-internal.php",
                type: "POST",
                data: new FormData(this),
                processData: false,
                contentType: false,
                success: function(res) {
                    if (res.success || res.status === 'success' || res.status === 'warning') {
                        showToast("Proses Selesai", "success");
                        dtPinjaman.ajax.reload();

                        $("#importPinjamanResult").html(`<div class="alert alert-success fw-bold"><i class="fa fa-check-circle"></i> ${res.message}</div>`);

                        $("#formImportPinjaman")[0].reset();
                    } else {
                        $("#importPinjamanResult").html(`<div class="alert alert-danger">${res.error || res.message}</div>`);
                    }
                },
                error: function(xhr) {
                    $("#importPinjamanResult").html('<div class="alert alert-danger">Terjadi kesalahan server (500)</div>');
                }
            });
        });

        // Export Logic
        window.exportPinjaman = () => {
            // let tgl_awal = $("#tglAwal").val();
            // let tgl_akhir = $("#tglAkhir").val();
            // window.location.href = `app/controller/export/export-pinjaman-internal.php?tgl_awal=${tgl_awal}&tgl_akhir=${tgl_akhir}`;
            let p = getPeriodeBulan();
            window.location.href = `app/controller/export/export-pinjaman-internal.php?tgl_awal=${p.awal}&tgl_akhir=${p.akhir}`;
        };

        // Fungsi Posting Pinjaman Internal ke Laporan Keuangan
        window.postingPinjamanInternal = function() {
            let p = getPeriodeBulan();
            let tgl_awal = p.awal;
            let tgl_akhir = p.akhir;
            let bln = $("#filterBulan").val();

            if (!tgl_awal || !tgl_akhir) return showToast("Pilih bulan terlebih dahulu!", "warning");

            if (confirm(`Posting total pencairan pinjaman periode Bulan ${bln} (${tgl_awal} s/d ${tgl_akhir}) ke Laporan Keuangan?\n\n(Data posting lama di rentang tanggal ini akan ditimpa/direplace otomatis)`)) {

                const btn = event.currentTarget;
                const originalText = btn.innerHTML;
                btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Memproses...';
                btn.disabled = true;

                $.ajax({
                    url: "app/controller/act-posting-pinjaman-internal.php",
                    type: "POST",
                    data: {
                        tgl_awal: tgl_awal,
                        tgl_akhir: tgl_akhir
                    },
                    dataType: "json",
                    success: function(res) {
                        if (res.status === 'success') {
                            showToast(res.message, 'success');
                        } else if (res.status === 'warning') {
                            showToast("Info: " + res.message, 'info');
                        } else {
                            showToast("Error: " + res.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        showToast("Terjadi kesalahan pada server/koneksi saat memposting.", 'error');
                        console.error(xhr.responseText);
                    },
                    complete: function() {
                        btn.innerHTML = originalText;
                        btn.disabled = false;
                    }
                });
            }
        };
    });
</script>