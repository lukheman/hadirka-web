<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.3/html2pdf.bundle.min.js"></script>

<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold mb-3">
            <span class="text-muted fw-light">Sanksi & Indisipliner /</span> Kelola Surat Peringatan
        </h4>

        <div class="nav-align-top mb-4">
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item">
                    <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab" data-bs-target="#navs-buat-surat">
                        <i class="fa fa-envelope-open-text me-1"></i> Buat Surat
                    </button>
                </li>
                <li class="nav-item">
                    <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-histori">
                        <i class="fa fa-history me-1"></i> History Surat
                    </button>
                </li>
            </ul>
            <div class="tab-content">
                <!-- TAB 1: BUAT SURAT BATCH -->
                <div class="tab-pane fade show active" id="navs-buat-surat" role="tabpanel">
                    <div class="d-flex justify-content-between mb-3">
                        <button class="btn btn-danger btn-sm" id="btnBatchSurat">
                            <i class="fa fa-gavel"></i> Terbitkan Surat Sanksi (Batch)
                        </button>
                    </div>
                    <div class="table-responsive text-nowrap">
                        <table class="table table-bordered table-hover" id="tableKaryawanSP">
                            <thead class="table-info">
                                <tr>
                                    <th class="text-center"><input type="checkbox" id="checkAllSP"></th>
                                    <th>NIK</th>
                                    <th>Nama Karyawan</th>
                                    <th>Jabatan</th>
                                    <th>Site</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyKaryawanSP"></tbody>
                        </table>
                    </div>
                </div>

                <!-- TAB 2: HISTORI -->
                <div class="tab-pane fade" id="navs-histori" role="tabpanel">
                    <div class="table-responsive text-nowrap">
                        <table class="table table-bordered table-hover" id="tableHistoriSP" style="width:100%">
                            <thead class="table-info">
                                <tr>
                                    <th>No Surat</th>
                                    <th>Nama Karyawan</th>
                                    <th>Jenis Sanksi</th>
                                    <th>Tgl Terbit</th>
                                    <th>Tgl Berakhir</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyHistoriSP"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Generate Surat -->
    <div class="modal fade" id="modalGenerateSurat">
        <div class="modal-dialog modal-lg">
            <form id="formGenerateSurat" class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title text-white">Konfigurasi Surat Sanksi</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning mb-3">
                        <i class="fa fa-users"></i> <strong id="countSelected">0</strong> Karyawan terpilih.
                    </div>
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label>Jenis Pelanggaran</label>
                            <select name="id_pelanggaran" id="pelanggaranSelect" class="form-select" required>
                                <option value="">-- Pilih Pelanggaran --</option>
                                <?php
                                $qPel = $koneksi->query("SELECT * FROM m_jenis_pelanggaran ORDER BY nama_pelanggaran ASC");
                                while ($p = $qPel->fetch_assoc()) {
                                    echo "<option value='{$p['id_pelanggaran']}' data-ket='{$p['keterangan_default']}'>{$p['nama_pelanggaran']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label>Jenis Sanksi</label>
                            <select name="jenis_sanksi" class="form-select" required>
                                <option value="">-- Pilih Sanksi --</option>
                                <option value="Surat Peringatan I">Surat Peringatan I (SP1)</option>
                                <option value="Surat Peringatan II">Surat Peringatan II (SP2)</option>
                                <option value="Surat Peringatan III">Surat Peringatan III (SP3)</option>
                                <option value="Surat Peringatan Pertama & Terakhir">SP Pertama & Terakhir (SPPT)</option>
                                <option value="PHK">PHK</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label>Masa Berlaku (Bulan)</label>
                            <input type="number" name="masa_berlaku_bulan" class="form-control" placeholder="Ex: 3 atau 6" required>
                        </div>
                        <div class="col-md-6">
                            <label>No Surat (Bisa Prefix/Utuh)</label>
                            <input type="text" name="no_surat" class="form-control" placeholder="Ex: 344/TJS/V/2026" required>
                        </div>
                        <div class="col-md-6">
                            <label>Tanggal Surat</label>
                            <input type="date" name="tgl_surat" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-md-12">
                            <label>Kronologi / Keterangan Kejadian</label>
                            <textarea name="keterangan_tambahan" id="keteranganInput" class="form-control" rows="3" required></textarea>
                            <small class="text-muted">Akan dicetak di paragraf pertama surat.</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-danger" id="btnSubmitGenerate"><i class="fa fa-print"></i> Simpan & Generate</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- HIDDEN TEMPLATE PDF -->
<div style="display:none;">
    <div id="pdfTemplate" style="max-width: 680px; box-sizing: border-box; padding: 40px; background: #fff; font-family: 'Times New Roman', Times, serif; color: #000; position: relative;">
        <!-- Header Kop Surat -->
        <div style="position: relative; text-align: center; padding-bottom: 10px;">
            <!-- Logo menggunakan absolute positioning agar teks header presisi di tengah -->
            <img src="assets/img/logo/tjs-done.png" style="position: absolute; left: 0; top: 0; height: 85px;">

            <h4 style="margin: 0; color: #000000; font-weight: bold; font-size: 26px; letter-spacing: 0.5px;">PT. TRIPUTRA JAYA SULTRA</h4>
            <p style="margin: 3px 0; font-size: 15px; font-weight: bold;">KONTRAKTOR DAN PERDAGANGAN UMUM PUSAT POMALAA</p>
            <p style="margin: 0; font-size: 14px;">Jl. Nusantara No.82 Pomalaa, 93562, Kolaka - Sulawesi Tenggara</p>
            <p style="margin: 0; font-size: 14px;">Telp: (0405) 2310860</p>
        </div>
        <!-- Garis Ganda Kop Surat standar -->
        <div style="border-bottom: 3px solid #000; margin-bottom: 2px;"></div>
        <div style="border-bottom: 1px solid #000; margin-bottom: 30px;"></div>

        <!-- Judul Surat -->
        <div style="text-align: center; margin-bottom: 30px;">
            <h4 id="pdfTitle" style="margin: 0; text-decoration: underline; color: #000000; font-weight: bold; font-size: 18px;">SURAT TEGURAN</h4>
            <p style="margin: 0; font-size: 15px;">No.<span id="pdfNoSurat"></span></p>
        </div>

        <!-- Tujuan -->
        <p style="margin: 0; font-size: 15px;">Ditujukan Kepada,</p>
        <table style="width: 100%; margin-top: 5px; margin-bottom: 20px; font-size: 15px;">
            <tr>
                <td style="width: 100px;">NAMA</td>
                <td style="width: 15px;">:</td>
                <td id="pdfNama" style="text-transform: uppercase;">-</td>
            </tr>
            <tr>
                <td>JABATAN</td>
                <td>:</td>
                <td id="pdfJabatan" style="text-transform: uppercase;">-</td>
            </tr>
            <tr>
                <td>No. ID</td>
                <td>:</td>
                <td id="pdfNik" style="text-transform: uppercase;">-</td>
            </tr>
        </table>

        <!-- Isi Surat -->
        <p style="margin-bottom: 10px; text-align: justify; font-size: 15px; line-height: 1.5;">Dengan hormat,</p>
        <p style="margin-bottom: 10px; text-align: justify; font-size: 15px; line-height: 1.5; text-indent: 40px;">
            Kami sampaikan kepada Saudara, bahwa anda telah melakukan Tindakan atau perilaku pelanggaran prosedur operasi yaitu <span id="pdfKeterangan"></span>, yang Dimana perilaku atau Tindakan tersebut bertentangan dengan peraturan Perusahaan pihak pertama tempat anda bekerja. Akibat tindakan tersebut kami berikan sanksi kepada saudara berupa <span id="pdfSanksi"></span> yang berlaku <span id="pdfMasa"></span> bulan kedepan.
        </p>
        <p style="margin-bottom: 10px; text-align: justify; font-size: 15px; line-height: 1.5; text-indent: 40px;" id="pdfParagraf2">
            Mohon diperhatikan agar Tindakan atau perilaku yang bertentangan dengan peraturan Perusahaan pihak pertama tidak dilanggar, dan apabila masih mengulangi perbuatan tersebut dalam jangka waktu <span id="pdfMasa2"></span> bulan kedepan maka saudara akan kami berikan surat peringatan sesuai aturan yang berlaku.
        </p>
        <p style="margin-bottom: 40px; text-align: justify; font-size: 15px; line-height: 1.5; text-indent: 40px;">
            Demikian kami sampaikan untuk di taati atas perhatian saudara, kami ucapkan terima kasih.
        </p>

        <!-- TTD -->
        <div style="display: flex; justify-content: space-between; margin-top: 40px; font-size: 15px;">
            <div style="text-align: center; width: 250px;" id="boxPenerima">
                <!-- Muncul khusus SPPT -->
            </div>
            <div style="text-align: center; width: 250px;">
                <p style="margin: 0;">Pomalaa, <span id="pdfTanggal"></span></p>
                <p style="margin: 0; margin-bottom: 80px;">Hormat Kami,</p>
                <!-- Lokasi gambar TTD -->
                <img src="assets/img/ttd/hrd.png" alt="TTD HRD" style="height: 70px; margin-bottom: 5px;">
                <p style="margin: 0;">HRD PT. Triputra Jaya Sultra.</p>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        const tbodyKaryawan = document.getElementById("tbodyKaryawanSP");
        const tbodyHistori = document.getElementById("tbodyHistoriSP");

        function loadTables() {
            // Load Karyawan Aktif
            fetch("app/controller/surat/get-karyawan-sp.php")
                .then(res => res.text())
                .then(html => {
                    if ($.fn.DataTable.isDataTable("#tableKaryawanSP")) $("#tableKaryawanSP").DataTable().destroy();
                    tbodyKaryawan.innerHTML = html;
                    $("#tableKaryawanSP").DataTable({
                        columnDefs: [{
                            orderable: false,
                            targets: 0
                        }]
                    });
                });

            // Load Histori
            fetch("app/controller/surat/get-history-sp.php")
                .then(res => res.text())
                .then(html => {
                    if ($.fn.DataTable.isDataTable("#tableHistoriSP")) $("#tableHistoriSP").DataTable().destroy();
                    tbodyHistori.innerHTML = html;
                    $("#tableHistoriSP").DataTable({
                        order: [
                            [3, 'desc']
                        ]
                    });
                });
        }

        // Auto-fill Keterangan Default
        document.getElementById("pelanggaranSelect").addEventListener("change", function() {
            const option = this.options[this.selectedIndex];
            document.getElementById("keteranganInput").value = option.getAttribute("data-ket") || '';
        });

        // Check All Checkboxes
        document.getElementById("checkAllSP").addEventListener("change", e => {
            let table = $("#tableKaryawanSP").DataTable();
            table.$(".chk-sp").prop("checked", e.target.checked);
        });

        // Buka Modal Batch
        document.getElementById("btnBatchSurat").addEventListener("click", () => {
            let table = $("#tableKaryawanSP").DataTable();
            let selected = [];
            table.$(".chk-sp:checked").each(function() {
                selected.push($(this).val());
            });

            if (selected.length === 0) {
                showToast("Pilih karyawan yang akan di sanksi!", "error");
                return;
            }

            document.getElementById("countSelected").innerText = selected.length;
            document.getElementById("formGenerateSurat").reset();
            new bootstrap.Modal(document.getElementById("modalGenerateSurat")).show();
        });

        // Proses Simpan Database
        document.getElementById("formGenerateSurat").addEventListener("submit", e => {
            e.preventDefault();
            let btn = document.getElementById("btnSubmitGenerate");
            btn.disabled = true;
            btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Memproses...';

            let table = $("#tableKaryawanSP").DataTable();
            let fd = new FormData(e.target);
            fd.append("action", "generate_batch");
            table.$(".chk-sp:checked").each(function() {
                fd.append("id_karyawan[]", $(this).val());
            });

            fetch("app/controller/surat/act-surat.php", {
                    method: "POST",
                    body: fd
                })
                .then(res => res.json())
                .then(res => {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fa fa-print"></i> Simpan & Generate';

                    if (res.status === "success") {
                        showToast(res.msg, "success");
                        bootstrap.Modal.getInstance(document.getElementById("modalGenerateSurat")).hide();
                        loadTables();
                    } else {
                        showToast(res.msg, "error");
                    }
                });
        });

        // Trigger Print PDF dari Histori
        document.addEventListener("click", async e => {
            let btnPrint = e.target.closest(".btnPrintPDF");
            if (btnPrint) {
                const idSp = btnPrint.dataset.id;

                // 1. Fetch data surat
                let fd = new FormData();
                fd.append("action", "get_print_data");
                fd.append("id_sp", idSp);

                const res = await fetch("app/controller/surat/act-surat.php", {
                    method: "POST",
                    body: fd
                });
                const data = await res.json();

                if (data.status === "success") {
                    const d = data.data;

                    // 2. Inject ke Template
                    document.getElementById("pdfTitle").innerText = d.jenis_sanksi.toUpperCase();
                    document.getElementById("pdfNoSurat").innerText = d.no_surat;
                    document.getElementById("pdfNama").innerText = d.nama_lengkap;
                    document.getElementById("pdfJabatan").innerText = d.nama_jabatan;
                    document.getElementById("pdfNik").innerText = d.nik_karyawan;
                    document.getElementById("pdfKeterangan").innerText = d.keterangan_tambahan;
                    document.getElementById("pdfSanksi").innerText = d.jenis_sanksi;
                    document.getElementById("pdfMasa").innerText = d.masa_berlaku_bulan;
                    document.getElementById("pdfMasa2").innerText = d.masa_berlaku_bulan;

                    // Format Tanggal (Contoh: 20 Mei 2026)
                    const tgl = new Date(d.tgl_surat).toLocaleDateString('id-ID', {
                        day: 'numeric',
                        month: 'long',
                        year: 'numeric'
                    });
                    document.getElementById("pdfTanggal").innerText = tgl;

                    // Penyesuaian teks SPPT
                    if (d.jenis_sanksi.includes("Terakhir")) {
                        document.getElementById("pdfParagraf2").innerText = `Mohon diperhatikan agar Tindakan atau perilaku yang bertentangan dengan peraturan Perusahaan pihak pertama tidak dilanggar, dan apabila masih mengulangi perbuatan tersebut atau melakukan tindakan perilaku pelanggaran prosedur operasi dalam jangka waktu ${d.masa_berlaku_bulan} bulan kedepan maka saudara akan kami berikan surat pemutusan hubungan kerja (PHK).`;
                        document.getElementById("boxPenerima").innerHTML = `<p style="margin: 0; margin-bottom: 80px;">Penerima SPPT</p><p style="margin: 0;">${d.nama_lengkap.toUpperCase()}</p>`;
                    } else {
                        document.getElementById("pdfParagraf2").innerText = `Mohon diperhatikan agar Tindakan atau perilaku yang bertentangan dengan peraturan Perusahaan pihak pertama tidak dilanggar, dan apabila masih mengulangi perbuatan tersebut dalam jangka waktu ${d.masa_berlaku_bulan} bulan kedepan maka saudara akan kami berikan surat peringatan sesuai aturan yang berlaku.`;
                        document.getElementById("boxPenerima").innerHTML = "";
                    }

                    // 3. Eksekusi PDF
                    const slipElem = document.getElementById("pdfTemplate");
                    slipElem.parentElement.style.display = "block"; // Munculkan sebentar

                    const filename = `${d.jenis_sanksi.toUpperCase()} - ${d.nama_lengkap}.pdf`;
                    await html2pdf().set({
                        margin: 0.5,
                        filename: filename,
                        image: {
                            type: "jpeg",
                            quality: 1
                        },
                        html2canvas: {
                            scale: 2
                        },
                        jsPDF: {
                            unit: "in",
                            format: "a4",
                            orientation: "portrait"
                        }
                    }).from(slipElem).save();

                    slipElem.parentElement.style.display = "none"; // Sembunyikan lagi
                }
            }

            // Hapus Surat
            let btnDel = e.target.closest(".btnDeleteSP");
            if (btnDel && confirm("Hapus riwayat surat peringatan ini?")) {
                let fd = new FormData();
                fd.append("action", "delete_sp");
                fd.append("id_sp", btnDel.dataset.id);
                fetch("app/controller/surat/act-surat.php", {
                        method: "POST",
                        body: fd
                    })
                    .then(res => res.json())
                    .then(res => {
                        if (res.status === "success") {
                            showToast("Data dihapus", "success");
                            loadTables();
                        }
                    });
            }
        });

        loadTables();
    });
</script>