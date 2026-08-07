<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.3/html2pdf.bundle.min.js"></script>

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

    /* CSS Khusus Cetak Slip */
    .slip-box {
        border: 1px solid #bbb;
        padding: 8px;
        box-sizing: border-box;
        height: 100%;
    }

    .slip-box h6 {
        margin: 0 0 6px 0;
        font-size: 14px;
        font-weight: bold;
        border-bottom: 1px solid #ddd;
        padding-bottom: 6px;
        color: #000;
    }

    .slip-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
        color: #000;
    }

    .slip-table th,
    .slip-table td {
        padding: 4px 6px;
        border: 1px solid #e6e6e6;
    }

    .slip-table th {
        background: #f5f5f5;
        text-align: left;
    }
</style>

<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold mb-3"><span class="text-muted fw-light">Data Internal /</span> Cetak Slip Gaji Internal</h4>

        <div class="card mb-4">
            <div class="card-body row g-3">
                <!-- <div class="col-md-2">
                    <label>Tanggal Awal</label>
                    <input type="date" id="slipTglAwal" class="form-control">
                </div>
                <div class="col-md-2">
                    <label>Tanggal Akhir</label>
                    <input type="date" id="slipTglAkhir" class="form-control">
                </div> -->
                <div class="col-md-4">
                    <label>Periode Bulan</label>
                    <input type="month" id="filterBulan" class="form-control">
                </div>
                <div class="col-md-4">
                    <label>Pilih Karyawan Internal</label>
                    <select id="slipKaryawan" class="form-select select2">
                        <?php
                        $k = $koneksi->query("SELECT nik_karyawan_internal, nama_karyawan FROM tb_karyawan_internal WHERE status_karyawan='Aktif'");
                        while ($r = $k->fetch_assoc()) echo "<option value='{$r['nik_karyawan_internal']}'>{$r['nik_karyawan_internal']} / {$r['nama_karyawan']}</option>";
                        ?>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button class="btn btn-primary w-100" onclick="generateSlipInternal()"><i class="fa fa-print"></i> Buat Slip</button>
                </div>
            </div>
        </div>

        <!-- AREA SLIP PDF -->
        <div class="card p-4 mx-auto" id="slipInternal" style="max-width: 800px; display: none; color: #000;">
            <div class="d-flex justify-content-between align-items-start border-bottom pb-3 mb-3" style="border-color: #000 !important;">
                <div class="d-flex align-items-center" style="gap: 10px;">
                    <img src="assets/img/logo/tjs-done.png" alt="Logo Perusahaan" style="height:50px; object-fit:contain;">
                    <div style="line-height: 1.2;">
                        <div style="font-size:16px; font-weight:800; color: #000;">PT. TRIPUTRA JAYASULTRA</div>
                        <small class="d-block" style="font-weight: 400; color: #000;">JL. EKONOMI · TLP/WA 0852-5526-4538</small>
                        <small class="d-block" style="font-weight: 400; color: #000;">POMALAA, KABUPATEN KOLAKA</small>
                    </div>
                </div>

                <div class="text-end" style="line-height: 1.2;">
                    <div style="font-size:16px; font-weight:700; color: #000;">SLIP GAJI INTERNAL</div>
                    <small id="txtPeriode" class="d-block mt-1" style="font-weight: 500; font-size: 13px; color: #000;"></small>
                    <small id="txtTglBayar" class="fw-bold d-block mt-1" style="font-size: 13px; color: #000;"></small>
                </div>
            </div>

            <div class="row mb-3" style="font-size: 14px;">
                <div class="col-6"><strong>NIK:</strong> <span id="txtNIK"></span></div>
                <div class="col-6 text-end"><strong>Jabatan:</strong> <span id="txtJabatan"></span></div>
                <div class="col-6"><strong>Nama:</strong> <span id="txtNama"></span></div>
            </div>

            <div class="row mb-3">
                <!-- Kolom Pendapatan -->
                <div class="col-6 pe-2">
                    <div class="slip-box">
                        <h6>PENDAPATAN</h6>
                        <table class="slip-table">
                            <tbody id="pendapatanBody"></tbody>
                        </table>
                    </div>
                </div>
                <!-- Kolom Potongan -->
                <div class="col-6 ps-2">
                    <div class="slip-box">
                        <h6>POTONGAN</h6>
                        <table class="slip-table">
                            <tbody id="potonganBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between p-2 border" style="background-color: #f8f9fa; border-color: #000 !important;">
                <strong>TOTAL GAJI BERSIH (DITERIMA)</strong>
                <h5 class="mb-0 fw-bold" id="txtTotal" style="color: #000;"></h5>
            </div>

            <div class="text-end mt-5">
                <p class="mb-5">HR. CFO</p>
                <strong>MUH. FADLY SAPUTRA</strong>
            </div>
        </div>

        <div class="text-center mt-3" id="btnDownloadDiv" style="display:none;">
            <button class="btn btn-danger" onclick="downloadPDF()"><i class="fa fa-file-pdf"></i> Download PDF</button>
        </div>
    </div>
</div>

<script>
    // 1. Fungsi getPeriodeBulan() diletakkan di LUAR agar bisa dibaca oleh semua fungsi
    function getPeriodeBulan() {
        let val = $("#filterBulan").val();
        if (!val) return { awal: '', akhir: '' };
        let [year, month] = val.split('-');
        let lastDay = new Date(year, month, 0).getDate();
        return {
            awal: `${year}-${month}-01`,
            akhir: `${year}-${month}-${lastDay}`
        };
    }
    
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
        //     $("#slipTglAwal").val(formatTgl(y, m, 6));
        //     $("#slipTglAkhir").val(formatTgl(y, m + 1, 5));
        // } else {
        //     $("#slipTglAwal").val(formatTgl(y, m - 1, 6));
        //     $("#slipTglAkhir").val(formatTgl(y, m, 5));
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

        $('.select2').select2({
            theme: 'bootstrap-5'
        });
    });

    function generateSlipInternal() {
        // let tgl_awal = $("#slipTglAwal").val();
        // let tgl_akhir = $("#slipTglAkhir").val();
        // let nik = $("#slipKaryawan").val();

        // if (!tgl_awal || !tgl_akhir) return alert("Pilih tanggal cut-off!");

        // $.post("app/controller/get-slip/get-slip-internal.php", {
        //     tgl_awal: tgl_awal,
        //     tgl_akhir: tgl_akhir,
        //     nik: nik
        // }, function(res) {

        let p = getPeriodeBulan();
        let tgl_awal = p.awal;
        let tgl_akhir = p.akhir;
        let nik = $("#slipKaryawan").val();

        if (!tgl_awal || !tgl_akhir) return alert("Pilih bulan terlebih dahulu!");

        $.post("app/controller/get-slip/get-slip-internal.php", {
            tgl_awal: tgl_awal,
            tgl_akhir: tgl_akhir,
            nik: nik
        }, function(res) {
            if (res.status == 'error') return alert(res.msg);

            let d = res.data;
            let tArr = tgl_akhir.split("-");
            let formatTglBayar = `Dibayarkan: ${tArr[2]}-${tArr[1]}-${tArr[0]}`;

            $("#txtPeriode").text(`Periode: ${tgl_awal} s/d ${tgl_akhir}`);
            $("#txtTglBayar").text(formatTglBayar);
            $("#txtNIK").text(d.nik);
            $("#txtNama").text(d.nama);
            $("#txtJabatan").text(d.jabatan);

            // ================= PENDAPATAN =================
            let htmlPend = `<tr><td>Gaji Pokok</td><td class="text-end">Rp ${d.gapok.toLocaleString('id-ID')}</td></tr>`;

            d.bonus.forEach(b => {
                let kt = b.ket ? `(${b.ket})` : '';
                htmlPend += `<tr><td>Bonus ${kt}</td><td class="text-end">Rp ${parseInt(b.nominal).toLocaleString('id-ID')}</td></tr>`;
            });
            d.rapel.forEach(r => {
                let kt = r.ket ? `(${r.ket})` : '';
                htmlPend += `<tr><td>Rapel ${kt}</td><td class="text-end">Rp ${parseInt(r.nominal).toLocaleString('id-ID')}</td></tr>`;
            });

            htmlPend += `<tr style="border-top: 2px solid #000; font-weight:bold;"><td>TOTAL PENDAPATAN</td><td class="text-end">Rp ${parseInt(d.total_pendapatan).toLocaleString('id-ID')}</td></tr>`;
            $("#pendapatanBody").html(htmlPend);

            // ================= POTONGAN =================
            let htmlPot = ``;
            if (d.potongan.length === 0 && d.pinjaman.length === 0) {
                htmlPot += `<tr><td colspan="2" class="text-center text-muted"><i>Tidak ada potongan</i></td></tr>`;
            } else {
                d.potongan.forEach(p => {
                    let kt = p.ket ? `(${p.ket})` : '';
                    htmlPot += `<tr><td>Potongan ${kt}</td><td class="text-end">Rp ${parseInt(p.nominal).toLocaleString('id-ID')}</td></tr>`;
                });
                d.pinjaman.forEach(pj => {
                    let kt = pj.ket ? `(${pj.ket})` : '';
                    htmlPot += `<tr><td>Pinjaman ${kt}</td><td class="text-end">Rp ${parseInt(pj.nominal).toLocaleString('id-ID')}</td></tr>`;
                });
            }
            htmlPot += `<tr style="border-top: 2px solid #000; font-weight:bold;"><td>TOTAL POTONGAN</td><td class="text-end">Rp ${parseInt(d.total_potongan_semua).toLocaleString('id-ID')}</td></tr>`;
            $("#potonganBody").html(htmlPot);

            // ================= TOTAL BERSIH =================
            $("#txtTotal").text("Rp " + d.gaji_bersih.toLocaleString('id-ID'));

            $("#slipInternal").show();
            $("#btnDownloadDiv").show();
        }, "json");
    }

    function downloadPDF() {
        let opt = {
            margin: 0.4,
            filename: `Slip_Internal_${$("#txtNama").text()}.pdf`,
            image: {
                type: 'jpeg',
                quality: 0.98
            },
            html2canvas: {
                scale: 2
            },
            jsPDF: {
                unit: 'in',
                format: 'a4',
                orientation: 'portrait'
            }
        };
        html2pdf().set(opt).from(document.getElementById('slipInternal')).save();
    }
</script>