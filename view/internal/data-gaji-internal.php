<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold mb-3"><span class="text-muted fw-light">Data Internal /</span> Penggajian Internal</h4>

        <div class="card mb-4">
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-md-2">
                        <label class="form-label">Tgl Awal (Cut-Off)</label>
                        <input type="date" id="filterTglAwal" class="form-control">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Tgl Akhir (Cut-Off)</label>
                        <input type="date" id="filterTglAkhir" class="form-control">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Site</label>
                        <select id="filterSiteGaji" class="form-select">
                            <option value="">Semua Site</option>
                            <?php
                            $sites = $koneksi->query("SELECT * FROM tb_site ORDER BY nama_site ASC");
                            while ($s = $sites->fetch_assoc()) echo "<option value='{$s['id_site']}'>{$s['nama_site']}</option>";
                            ?>
                        </select>
                    </div>
                    <div class="col-md-6 text-end">
                        <button class="btn btn-dark" onclick="exportGajiInternal()"><i class="fa fa-file-excel"></i> Export Payroll</button>
                        <button class="btn btn-primary" onclick="postingGajiInternal()"><i class="fa fa-paper-plane"></i> Posting Payroll</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <h5 class="card-header bg-success text-white mb-0"><i class="fa fa-money-bill-wave"></i> Rekap Gaji Bersih Internal</h5>
            <div class="card-body mt-3">
                <div class="table-responsive text-nowrap">
                    <table class="table table-bordered table-hover" id="tableGajiInternal" style="width:100%">
                        <thead class="table-info">
                            <tr class="text-center">
                                <th>No</th>
                                <th>NIK</th>
                                <th>Nama Karyawan</th>
                                <th>Site</th>
                                <th>Gaji Pokok</th>
                                <th>Bonus</th>
                                <th>Rapel</th>
                                <th>Potongan</th>
                                <th>Pinjaman</th>
                                <th>Gaji Bersih</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        let today = new Date();
        let y = today.getFullYear();
        let m = today.getMonth();
        let d = today.getDate();

        let formatTgl = (year, month, day) => {
            let dateObj = new Date(year, month, day);
            return dateObj.getFullYear() + "-" + String(dateObj.getMonth() + 1).padStart(2, '0') + "-" + String(dateObj.getDate()).padStart(2, '0');
        };

        if (d > 5) {
            $("#filterTglAwal").val(formatTgl(y, m, 6));
            $("#filterTglAkhir").val(formatTgl(y, m + 1, 5));
        } else {
            $("#filterTglAwal").val(formatTgl(y, m - 1, 6));
            $("#filterTglAkhir").val(formatTgl(y, m, 5));
        }

        let dtGaji = $('#tableGajiInternal').DataTable({
            ajax: {
                url: "app/controller/get-data/get-gaji-internal.php",
                type: "POST",
                data: function(d) {
                    d.tgl_awal = $("#filterTglAwal").val();
                    d.tgl_akhir = $("#filterTglAkhir").val();
                    d.site = $("#filterSiteGaji").val();
                },
                dataSrc: "data"
            },
            columns: [{
                    data: "no",
                    className: "text-center"
                },
                {
                    data: "nik",
                    className: "text-center"
                },
                {
                    data: "nama"
                },
                {
                    data: "site"
                },
                {
                    data: "gapok",
                    className: "text-end"
                },
                {
                    data: "bonus",
                    className: "text-end"
                },
                {
                    data: "rapel",
                    className: "text-end"
                },
                {
                    data: "potongan",
                    className: "text-end text-danger"
                },
                {
                    data: "pinjaman",
                    className: "text-end text-danger"
                },
                {
                    data: "gaji_bersih",
                    className: "text-end fw-bold text-success"
                }
            ]
        });

        $("#filterTglAwal, #filterTglAkhir, #filterSiteGaji").on("change", () => dtGaji.ajax.reload());

        window.exportGajiInternal = function() {
            let tgl_awal = $("#filterTglAwal").val();
            let tgl_akhir = $("#filterTglAkhir").val();
            let site = $("#filterSiteGaji").val();
            window.location.href = `app/controller/export/export-gaji-internal.php?tgl_awal=${tgl_awal}&tgl_akhir=${tgl_akhir}&site=${site}`;
        }

        window.postingGajiInternal = function() {
            let tgl_awal = $("#filterTglAwal").val();
            let tgl_akhir = $("#filterTglAkhir").val();
            let tglBayarInfo = tgl_akhir;

            if (confirm(`Posting total gaji bersih periode (${tgl_awal} s/d ${tgl_akhir})?\nPengeluaran akan dicatat pada: ${tglBayarInfo}`)) {

                const btn = event.currentTarget;
                const originalText = btn.innerHTML;
                btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Memproses...';
                btn.disabled = true;

                $.ajax({
                    url: "app/controller/act-posting-gaji-internal.php",
                    type: "POST",
                    data: {
                        tgl_awal: tgl_awal,
                        tgl_akhir: tgl_akhir
                    },
                    dataType: "json",
                    success: function(res) {
                        if (res.status === 'success') {
                            alert(res.msg);
                        } else {
                            alert("Error: " + res.msg);
                        }
                    },
                    error: function() {
                        alert("Terjadi kesalahan pada server/koneksi saat memposting.");
                    },
                    complete: function() {
                        btn.innerHTML = originalText;
                        btn.disabled = false;
                    }
                });
            }
        }
    });
</script>