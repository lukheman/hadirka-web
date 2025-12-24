<?php
require_once 'app/config/koneksi.php';
require_once 'app/controller/read.php';
?>

<style>
    :root {
        --primary: #0b2b73;
        --muted: #777
    }

    body {
        font-family: Arial, Helvetica, sans-serif;
        background: #f3f4f6;
        padding: 18px
    }

    .sheet {
        max-width: 1000px;
        margin: 0 auto;
        background: #fff;
        padding: 18px;
        border: 1px solid #ddd;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.05)
    }

    .header {
        display: flex;
        gap: 18px;
        align-items: flex-start
    }

    .logo {
        width: 160px;
        object-fit: contain;
    }

    .company {
        flex: 1
    }

    .company h1 {
        margin: 0;
        font-size: 18px;
        color: var(--primary)
    }

    .company p {
        margin: 2px 0;
        color: var(--muted);
        font-size: 13px
    }

    .period {
        min-width: 200px;
        text-align: right
    }

    .period strong {
        display: block
    }

    .meta {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        margin-top: 14px;
        border-top: 2px solid #eee;
        padding-top: 12px
    }

    .grid-two {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 18px;
        margin-top: 18px
    }

    .card {
        border: 1px solid #e6e6e6;
        padding: 12px
    }

    .card h3 {
        margin: 0 0 8px 0;
        font-size: 14px
    }

    .row {
        display: flex;
        justify-content: space-between;
        padding: 6px 0;
        border-bottom: 1px dashed #f0f0f0
    }

    .amount {
        font-weight: 600
    }

    .summary {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 12px;
        padding-top: 8px;
        border-top: 2px solid #eee
    }

    .attendance {
        margin-top: 18px
    }

    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px
    }

    th,
    td {
        border: 1px solid #e8e8e8;
        padding: 6px;
        text-align: left
    }

    th {
        background: #f7f7f7
    }

    .center {
        text-align: center
    }

    .muted {
        color: var(--muted)
    }

    .controls {
        margin: 12px 0;
        display: flex;
        gap: 8px;
        align-items: center;
        flex-wrap: wrap;
    }

    input,
    select {
        padding: 6px;
        font-size: 14px
    }

    button {
        padding: 8px 12px;
        font-size: 14px;
        cursor: pointer
    }

    .tunjangan-list {
        margin-top: 8px;
        font-size: 13px
    }

    .tunjangan-list li {
        margin-bottom: 4px
    }

    /* ===== STATUS ROW COLORS ===== */
    .table-primary {
        background: #cce5ff !important;
    }

    .table-info {
        background: #bee5eb !important;
    }

    .table-danger {
        background: #f8d7da !important;
    }

    .table-warning {
        background: #fff3cd !important;
    }

    @media(max-width:720px) {
        .meta {
            grid-template-columns: 1fr
        }

        .grid-two {
            grid-template-columns: 1fr
        }

        .period {
            text-align: left;
            margin-top: 8px
        }
    }

    /* Print friendly */
    @media print {
        body {
            padding: 0;
            background: #fff
        }

        .controls {
            display: none
        }

        .sheet {
            box-shadow: none;
            border: none;
            margin: 0;
            width: 100%
        }
    }
</style>
<div class="controls">
    <label>ID Karyawan:
        <select id="id_karyawan">
            <?php
            $dr = data_karyawan('nik_karyawan');
            foreach ($dr as $n) { ?>
                <option value="<?= htmlspecialchars($n['nik_karyawan']) ?>"><?= htmlspecialchars($n['nik_karyawan']) ?></option>
            <?php
            }
            ?>
        </select>
    </label>

    <label>Tgl A: <input id="tgl_a" type="date"></label>
    <label>Tgl B: <input id="tgl_b" type="date"></label>
    <button id="loadBtn">Load Slip</button>
    <button id="exportPdfBtn">Export PDF</button>
    <!-- <button id="downloadServerPdfBtn">Download (server-side)</button> -->
</div>

<div class="sheet" id="slip">
    <div class="header">
        <img class="logo" id="logoImg" src="assets/img/logo/tjs-nobg.png" alt="logo">
        <div class="company">
            <h1>PT. TRIPUTRA JAYA SULTRA</h1>
            <p>JL. EKONOMI, TELP : 0852-5526-4538</p>
            <p>POMALAA, KABUPATEN KOLAKA</p>
        </div>
        <div class="period">
            <small class="muted">SLIP GAJI</small>
            <strong id="periodText">-</strong>
        </div>
    </div>

    <div class="meta" style="margin-bottom:6px">
        <div>
            <div><strong>NIK:</strong> <span id="nikTxt">-</span></div>
            <div><strong>NAMA KARYAWAN:</strong> <span id="namaTxt">-</span></div>
        </div>
        <div style="text-align:right">
            <div><strong>JABATAN:</strong> <span id="jabatanTxt">-</span></div>
        </div>
    </div>

    <div class="grid-two">
        <div class="card">
            <h3>PENDAPATAN</h3>
            <div class="row">
                <div>BASIC</div>
                <div class="amount" id="basicTxt">Rp -</div>
            </div>
            <div class="row">
                <div>T. DAERAH</div>
                <div class="amount" id="tdaerahTxt">Rp -</div>
            </div>
            <div class="row">
                <div>T. HIDUP</div>
                <div class="amount" id="thidupTxt">Rp -</div>
            </div>
            <div class="row">
                <div>T. MAKAN</div>
                <div class="amount" id="tmakanTxt">Rp -</div>
            </div>
            <div class="row">
                <div>T. SKILL</div>
                <div class="amount" id="tskillTxt">Rp -</div>
            </div>

            <div class="row">
                <div>TUNJANGAN LAIN</div>
                <div class="amount" id="totalTunjanganTxt">Rp -</div>
            </div>
            <div class="tunjangan-list">
                <ul id="listTunjangan"></ul>
            </div>

            <div class="row">
                <div>LEMBUR / OT</div>
                <div class="amount" id="lemburTxt">Rp -</div>
            </div>
            <div class="row">
                <div>OVERTIME</div>
                <div class="amount" id="overtimeTxt">Rp -</div>
            </div>
            <div class="row">
                <div>UPAH SPL JAM 1-7</div>
                <div class="amount" id="spl1Txt">Rp -</div>
            </div>
            <div class="row">
                <div>UPAH SPL JAM 8</div>
                <div class="amount" id="spl8Txt">Rp -</div>
            </div>
            <div class="row">
                <div>UPAH SPL JAM ≥9</div>
                <div class="amount" id="spl9Txt">Rp -</div>
            </div>

            <div class="row">
                <div>JUMLAH PENDAPATAN</div>
                <div class="amount" id="totalPendapatanTxt">Rp -</div>
            </div>
        </div>

        <div class="card">
            <h3>POTONGAN</h3>
            <div class="row">
                <div>BPJS KESEHATAN (<span id="bpjsPctTxt">1%</span>)</div>
                <div class="amount" id="bpjsTxt">Rp -</div>
            </div>
            <div class="row">
                <div>JHT (<span id="jhtPctTxt">2%</span>)</div>
                <div class="amount" id="jhtTxt">Rp -</div>
            </div>
            <div class="row">
                <div>PPH 21</div>
                <div class="amount" id="pphTxt">Rp -</div>
            </div>
            <div class="row">
                <div>POTONGAN IZIN</div>
                <div class="amount" id="potIzinTxt">Rp -</div>
            </div>
            <div class="row">
                <div>POTONGAN MANGKIR</div>
                <div class="amount" id="potMangkirTxt">Rp -</div>
            </div>
            <div class="row">
                <div>JUMLAH POTONGAN</div>
                <div class="amount" id="totalPotonganTxt">Rp -</div>
            </div>
        </div>
    </div>

    <div class="summary">
        <div>
            <div class="muted">GAJI BERSIH</div>
            <div style="font-size:18px;font-weight:700" id="netSalaryTxt">Rp -</div>
        </div>
        <div style="text-align:right">
            <div class="muted">HR. CFO</div>
            <div style="margin-top:34px">AHMAD ALBAR, S.H</div>
        </div>
    </div>

    <!-- Attendance table -->
    <div class="attendance">
        <h3>DAFTAR HARI & JAM KERJA</h3>
        <table id="attTable">
            <thead>
                <tr>
                    <th style="width:40px">NO</th>
                    <th style="width:120px">HARI</th>
                    <th style="width:120px">TANGGAL</th>
                    <th style="width: 120px;">KET / JAM KERJA</th>
                    <th>OT1</th>
                    <th>OT2</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

</div>

<!-- html2pdf (client side export) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.3/html2pdf.bundle.min.js"></script>
<script>
    // ---------- CONFIG lokal (bisa diubah) ----------
    const CONFIG = {
        BPJS_PCT: 0.01, // 1% BPJS kesehatan
        JHT_PCT: 0.02, // 2% JHT
        PPH21_PCT: 0.00, // placeholder
        // path endpoint (sesuaikan jika perlu)
        ENDPOINT_SLIP: 'app/controller/get-slip/get-slip-gaji.php'
    };
    // -------------------------------------------------

    // util
    function rupiah(n) {
        if (n === null || n === undefined) return 'Rp -';
        return 'Rp ' + Number(n).toLocaleString('id-ID');
    }

    // default dates: set tgl_a = 21 prev month, tgl_b = 20 current month
    (function setDefaultPeriod() {
        const now = new Date();
        const year = now.getFullYear();
        const month = now.getMonth() + 1; // 1-12
        const tglB = new Date(year, month - 1, 21);
        const prev = new Date(year, month - 2, 22);

        function toISO(d) {
            return d.toISOString().slice(0, 10);
        }
        document.getElementById('tgl_a').value = toISO(prev);
        document.getElementById('tgl_b').value = toISO(tglB);
    })();

    document.getElementById('loadBtn').addEventListener('click', loadSlip);
    document.getElementById('exportPdfBtn').addEventListener('click', exportPdf);
    document.getElementById('downloadServerPdfBtn').addEventListener('click', downloadServerPdf);

    async function loadSlip() {
        const id = document.getElementById('id_karyawan').value.trim();
        const tgl_a = document.getElementById('tgl_a').value;
        const tgl_b = document.getElementById('tgl_b').value;
        if (!id || !tgl_a || !tgl_b) {
            alert('Lengkapi ID karyawan dan tanggal');
            return;
        }

        document.getElementById('periodText').innerText = `${tgl_a} - ${tgl_b}`;

        const url = `${CONFIG.ENDPOINT_SLIP}?id_karyawan=${encodeURIComponent(id)}&tgl_a=${tgl_a}&tgl_b=${tgl_b}`;

        let res, json;
        try {
            res = await fetch(url);
        } catch (err) {
            alert('Network error: ' + err.message);
            return;
        }

        // jika bukan JSON (mis. PHP warning), tangani aman
        const text = await res.text();
        try {
            json = JSON.parse(text);
        } catch (err) {
            // tampilkan preview error yang dikembalikan server di console dan alert singkat
            console.error('Server response (not JSON):', text);
            alert('Server mengembalikan response yang tidak valid (lihat console). Pastikan PHP tidak menampilkan warning/notice.');
            return;
        }

        if (!json || json.status !== 'success') {
            alert('Error: ' + (json.message || JSON.stringify(json)));
            return;
        }

        // isi UI berdasarkan struktur response (sesuai get-slip-gaji.php)
        const data = json;
        const karyawan = data.karyawan || {};
        const karier = data.karier || {};
        const tunjangan = data.tunjangan || [];
        const absensi = data.absensi || [];
        const summary = data.summary || {};

        document.getElementById('nikTxt').innerText = karyawan.nik_karyawan || karyawan.id_karyawan || id;
        document.getElementById('namaTxt').innerText = karyawan.nama_lengkap || karyawan.nama || '-';
        document.getElementById('jabatanTxt').innerText = karier.nama_jabatan || karier.jabatan || '-';

        // pendapatan dasar
        const gaji_pokok = Number(summary.gaji_pokok || karier.gaji_pokok || 0);
        const t_daerah = Number(karier.t_daerah || 0);
        const t_hidup = Number(karier.t_hidup || 0);
        const t_makan = Number(karier.t_makan || 0);
        const t_skill = Number(karier.t_skil || karier.t_skill || 0);

        document.getElementById('basicTxt').innerText = rupiah(gaji_pokok);
        document.getElementById('tdaerahTxt').innerText = rupiah(t_daerah);
        document.getElementById('thidupTxt').innerText = rupiah(t_hidup);
        document.getElementById('tmakanTxt').innerText = rupiah(t_makan);
        document.getElementById('tskillTxt').innerText = rupiah(t_skill);

        // tunjangan list
        const listT = document.getElementById('listTunjangan');
        listT.innerHTML = '';
        let totalTunjangan = Number(data.total_tunjangan || 0);
        document.getElementById('totalTunjanganTxt').innerText = rupiah(totalTunjangan);
        tunjangan.forEach(t => {
            const li = document.createElement('li');
            li.innerText = `${t.nama_tunjangan || t.nama || '-'}: ${rupiah(Number(t.jumlah_tunjangan||t.jumlah||0))}`;
            listT.appendChild(li);
        });

        // lembur + ot + spl from summary
        const total_spl = Number(summary.total_spl || 0);
        const total_ot1 = Number(summary.total_ot1 || 0);
        const total_ot2 = Number(summary.total_ot2 || 0);
        const total_lembur = total_spl + total_ot1 + total_ot2;

        // detail lembur dari backend
        const overtime = Number(summary.total_ot1 || 0); // overtime umum
        const spl1 = Number(summary.total_spl_1_7 || summary.total_spl1 || 0);
        const spl8 = Number(summary.total_spl_8 || summary.total_spl8 || 0);
        const spl9 = Number(summary.total_spl_9_up || summary.total_spl9 || 0);

        // tampilkan ke UI
        document.getElementById('overtimeTxt').innerText = rupiah(overtime);
        document.getElementById('spl1Txt').innerText = rupiah(spl1);
        document.getElementById('spl8Txt').innerText = rupiah(spl8);
        document.getElementById('spl9Txt').innerText = rupiah(spl9);


        document.getElementById('lemburTxt').innerText = total_lembur ? rupiah(total_lembur) : 'Rp -';

        const totalPendapatan = Number(summary.total_pendapatan || 0);
        document.getElementById('totalPendapatanTxt').innerText = rupiah(totalPendapatan);

        // potongan
        const bpjs = Number(summary.bpjs || Math.round(gaji_pokok * CONFIG.BPJS_PCT));
        const jht = Number(summary.jht || Math.round(gaji_pokok * CONFIG.JHT_PCT));
        const potIzin = Number(summary.pot_izin_total || 0);
        const potMangkir = Number(summary.pot_mangkir_total || 0);
        const totalPotongan = Number(summary.total_potongan || (bpjs + jht + potIzin + potMangkir));

        document.getElementById('bpjsTxt').innerText = rupiah(bpjs);
        document.getElementById('jhtTxt').innerText = rupiah(jht);
        document.getElementById('pphTxt').innerText = (summary.pph ? rupiah(summary.pph) : 'Rp -');
        document.getElementById('potIzinTxt').innerText = rupiah(potIzin);
        document.getElementById('potMangkirTxt').innerText = rupiah(potMangkir);
        document.getElementById('totalPotonganTxt').innerText = rupiah(totalPotongan);

        // gaji bersih
        const net = Number(summary.gaji_bersih || (totalPendapatan - totalPotongan));
        document.getElementById('netSalaryTxt').innerText = rupiah(net);

        // populate attendance table (absensi detail contains ot1, ot2 and pay per row if endpoint returns it)
        renderAbsensiTable(absensi);
    }

    function renderAbsensiTable(rows) {
        const tbody = document.querySelector('#attTable tbody');
        tbody.innerHTML = '';

        let totalJam = 0;
        let totalOT1 = 0;
        let totalOT2 = 0;
        let totalUpah = 0;

        rows.forEach((r, idx) => {
            const tr = document.createElement('tr');

            const day = new Date(r.tanggal || r.tgl_absen).getDay();
            const jamKerja = Number(r.jam_kerja || 0);
            const ketStatus = (r.ket_absen || '').toUpperCase();

            if (ketStatus === 'OFF') {
                tr.classList.add('table-warning');
                r.ket_absen = "OFF";
            } else if (ketStatus === 'IZIN') {
                tr.classList.add('table-primary');
                r.ket_absen = "IZIN";
            } else if (ketStatus === 'SAKIT') {
                tr.classList.add('table-info');
                r.ket_absen = "SAKIT";
            } else if (jamKerja === 0) {
                tr.classList.add('table-danger');
                r.ket_absen = "MANGKIR";
            } else {
                tr.classList.add('table-success');
                r.ket_absen = jamKerja + " JAM";
            }



            const ot1Jam = Number(r.ot1_hours || r.ot1 || 0);
            const ot2Jam = Number(r.ot2_hours || r.ot2 || 0);
            const upahRow = Number((r.upah || 0) + (r.spl_pay || 0) + (r.ot1_pay || 0) + (r.ot2_pay || 0));

            // akumulasi total
            totalJam += jamKerja;
            totalOT1 += ot1Jam;
            totalOT2 += ot2Jam;
            totalUpah += upahRow;

            const html = `
            <tr class="${tr.className}">
                <td class="center">${String(idx + 1).padStart(2, '0')}</td>
                <td>${r.hari || ''}</td>
                <td>${r.tanggal || r.tgl_absen || ''}</td>
                <td>${r.ket_absen}</td>
                <td class="center">${ot1Jam || ''}</td>
                <td class="center">${ot2Jam || ''}</td>
            </tr>
        `;
            tbody.insertAdjacentHTML("beforeend", html);
        });

        if (!rows.length) {
            tbody.innerHTML = '<tr><td colspan="6" class="center">Tidak ada data absensi</td></tr>';
            return;
        }

        // Tambahkan row total
        tbody.insertAdjacentHTML("beforeend", `
        <tr style="font-weight:bold;background:#e9ecef">
            <td colspan="3" class="center">TOTAL</td>
            <td>${totalJam} JAM</td>
            <td class="center">${totalOT1}</td>
            <td class="center">${totalOT2}</td>
        </tr>
    `);
    }



    function exportPdf() {
        const element = document.getElementById('slip');
        const opt = {
            margin: 0.3,
            filename: 'slip_gaji.pdf',
            image: {
                type: 'jpeg',
                quality: 0.98
            },
            html2canvas: {
                scale: 2,
                useCORS: true
            },
            jsPDF: {
                unit: 'in',
                format: 'a4',
                orientation: 'portrait'
            }
        };
        html2pdf().set(opt).from(element).save();
    }

    // Optional: download server-side PDF (requires server-side generator e.g. mPDF that accepts same params)
    // This expects an endpoint app/controller/get-slip/download-slip-pdf.php?id_karyawan=...&tgl_a=...&tgl_b=...
    async function downloadServerPdf() {
        const id = document.getElementById('id_karyawan').value.trim();
        const tgl_a = document.getElementById('tgl_a').value;
        const tgl_b = document.getElementById('tgl_b').value;
        if (!id || !tgl_a || !tgl_b) {
            alert('Lengkapi ID karyawan dan tanggal');
            return;
        }

        const url = `app/controller/get-slip/download-slip-pdf.php?id_karyawan=${encodeURIComponent(id)}&tgl_a=${tgl_a}&tgl_b=${tgl_b}`;
        // membuka url baru agar browser otomatis download / preview
        window.open(url, '_blank');
    }

    // safety: auto-load first record on page load (tidak otomatis klik)
    //document.getElementById('loadBtn').click();
</script>