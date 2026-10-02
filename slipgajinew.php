<?php
// slip.php
require_once 'app/config/koneksi.php';
require_once 'app/config/function.php';
require_once 'app/controller/read.php';
?>
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Slip Gaji - Simple</title>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <!-- html2pdf -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.3/html2pdf.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* SweetAlert2 selalu di paling depan */
        .swal2-container {
            z-index: 20000 !important;
        }
        /* Simple, print-friendly styling to mimic contoh */
        :root {
            --border: #222;
            --muted: #666;
            --accent: #0b5ed7;
            --highlight-yellow: #fff3b0;
            --highlight-red: #ffd7d7;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            margin: 20px;
            color: #111;
            background: #fff;
        }

        .wrap {
            max-width: 900px;
            margin: 0 auto;
            padding: 14px;
            border: 1px solid #ccc;
            box-sizing: border-box;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 10px;
            margin-bottom: 8px;
        }

        .company {
            line-height: 1.2;
            font-weight: 600;
        }

        .company small {
            display: block;
            font-weight: 400;
            color: var(--muted);
        }

        .title {
            text-align: right;
            font-weight: 700;
            font-size: 14px;
        }

        .title .period {
            font-weight: 500;
            font-size: 13px;
            color: var(--muted);
        }

        .meta {
            display: flex;
            gap: 20px;
            margin: 10px 0 14px;
        }

        .meta .left,
        .meta .right {
            flex: 1;
            font-size: 14px;
        }

        .meta label {
            display: inline-block;
            width: 140px;
            color: var(--muted);
        }

        .two-cols {
            display: flex;
            gap: 16px;
            margin-bottom: 8px;
        }

        .box {
            flex: 1;
            border: 1px solid #bbb;
            padding: 8px;
            box-sizing: border-box;
        }

        .box h5 {
            margin: 0 0 6px 0;
            font-size: 13px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 6px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        th,
        td {
            padding: 6px 8px;
            border: 1px solid #e6e6e6;
            /* vertical-align: top; */
        }

        th {
            background: #f5f5f5;
            text-align: left;
            font-weight: 600;
        }

        .right {
            text-align: right;
        }

        .bold {
            font-weight: 700;
        }

        .summary {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
            gap: 10px;
        }

        .summary .left {
            flex: 1;
        }

        .summary .right {
            flex: 1;
            text-align: right;
        }

        .notes {
            margin-top: 10px;
            font-size: 12px;
            color: var(--muted);
        }

        /* Tampilan lama: tabel absensi */
        #absenTable thead th {
            background: #0b5ed7;
            color: #fff;
            text-align: center;
            font-size: 13px;
        }

        #absenTable td.center {
            text-align: center;
        }

        .absen-table th,
        .absen-table td {
            border: 1px solid #000;
            padding: 6px;
        }

        /* highlight cells (tampilan lama) */
        .off {
            background: var(--highlight-yellow);
        }

        .sd {
            background: var(--highlight-red);
            background-color: #689ef0ff;
        }

        .izin {
            background: var(--highlight-red);
            background-color: #0066ffff;
        }

        .cm {
            background: var(--highlight-red);
            background-color: #ffe342ff;
        }

        /* Toggle tampilan absen */
        .absen-toggle {
            display: flex;
            gap: 6px;
            margin-bottom: 8px;
        }

        .absen-toggle button {
            padding: 5px 12px;
            font-size: 12px;
            background: #e9ecef;
            color: #333;
        }

        .absen-toggle button.active {
            background: var(--accent);
            color: #fff;
        }

        /* Kalender absensi (sama seperti rekapitulasi bulanan) */
        .kal-scroll {
            overflow-x: auto;
        }
        .kal-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 6px;
            min-width: 720px;
        }
        .kal-head {
            text-align: center;
            font-weight: 700;
            font-size: 0.8rem;
            color: #566a7f;
            padding: 6px 0;
            background: #f0f2f4;
            border-radius: 6px;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .kal-month {
            grid-column: 1 / -1;
            font-weight: 700;
            color: #fff;
            background: #0b5ed7;
            border-radius: 6px;
            padding: 4px 12px;
            margin-top: 4px;
            font-size: 0.85rem;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .kal-day {
            min-height: 78px;
            border-radius: 8px;
            padding: 6px;
            border: 1px solid #e3e6ea;
            background: #fff;
            display: flex;
            flex-direction: column;
            gap: 3px;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .kal-day.empty {
            background: transparent;
            border-style: dashed;
        }
        .kal-date {
            font-weight: 700;
            font-size: 0.9rem;
            line-height: 1;
        }
        .kal-date small {
            font-weight: 400;
            color: #8592a3;
        }
        .kal-status {
            font-size: 0.65rem;
            font-weight: 700;
            border-radius: 20px;
            padding: 1px 8px;
            display: inline-block;
            width: fit-content;
        }
        .kal-jam {
            font-size: 0.68rem;
            color: #566a7f;
            line-height: 1.3;
        }
        .kal-Masuk { background: #e8fadf; border-color: #71dd37; }
        .kal-Masuk .kal-status { background: #71dd37; color: #fff; }
        .kal-SPL { background: #fff2d6; border-color: #ffab00; }
        .kal-SPL .kal-status { background: #ffab00; color: #fff; }
        .kal-OFF { background: #f1f2f4; border-color: #d9dee3; }
        .kal-OFF .kal-status { background: #8592a3; color: #fff; }
        .kal-Izin { background: #e7e7ff; border-color: #696cff; }
        .kal-Izin .kal-status { background: #696cff; color: #fff; }
        .kal-Sakit { background: #d7f5fc; border-color: #03c3ec; }
        .kal-Sakit .kal-status { background: #03c3ec; color: #fff; }
        .kal-Cuti { background: #ede4f7; border-color: #6f42c1; }
        .kal-Cuti .kal-status { background: #6f42c1; color: #fff; }
        .kal-Mangkir { background: #ffe0db; border-color: #ff3e1d; }
        .kal-Mangkir .kal-status { background: #ff3e1d; color: #fff; }
        .kal-Resign, .kal-New { background: #e9eaec; border-color: #8592a3; }
        .kal-Resign .kal-status, .kal-New .kal-status { background: #566a7f; color: #fff; }
        .kal-legend {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-bottom: 8px;
        }
        .kal-legend span {
            font-size: 0.68rem;
            font-weight: 700;
            border-radius: 20px;
            padding: 2px 10px;
            color: #fff;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* signature */
        .signature {
            margin-top: 24px;
            text-align: right;
            font-size: 13px;
        }

        /* print adjustments */
        @media print {
            body {
                margin: 0;
            }

            .wrap {
                border: 0;
                box-shadow: none;
            }

            .no-print {
                display: none !important;
            }
        }

        /* small screen */
        @media (max-width:640px) {
            .meta {
                flex-direction: column;
            }

            .two-cols {
                flex-direction: column;
            }

            .title {
                text-align: left;
                margin-top: 8px;
            }
        }

        /* simple button */
        .controls {
            display: flex;
            gap: 10px;
            margin: 10px 0;
            flex-wrap: wrap;
        }

        button {
            padding: 8px 12px;
            border: 0;
            background: var(--accent);
            color: #fff;
            cursor: pointer;
            border-radius: 4px;
            font-weight: 600;
        }

        button.secondary {
            background: #6c757d;
        }

        .slip-alert {
            display: none;
            max-width: 900px;
            margin: 10px auto;
            padding: 12px 16px;
            border: 1px solid #f1b0b7;
            border-radius: 4px;
            background: #f8d7da;
            color: #842029;
            font-size: 14px;
            font-weight: 600;
        }

        select,
        input[type="date"] {
            padding: 6px 8px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }
    </style>
</head>

<body>
    <div class="controls">
        <select id="siteFilter" class="search" style="min-width: 150px;">
            <option value="">-- Semua Site --</option>
            <?php
            // Ambil daftar site unik langsung dari database
            $qSite = mysqli_query($koneksi, "SELECT DISTINCT nama_site FROM tb_site WHERE nama_site IS NOT NULL ORDER BY nama_site ASC");
            if($qSite) {
                while($s = mysqli_fetch_assoc($qSite)){
                    echo '<option value="'.htmlspecialchars($s['nama_site']).'">'.htmlspecialchars($s['nama_site']).'</option>';
                }
            }
            ?>
        </select>

        <select id="nikSelect" class="search">
            <?php
            $dataKaryawan = data_karyawan('nama_lengkap');
            
            // Mapping Site untuk memastikan info site tersedia pada dropdown karyawan
            $siteMapping = [];
            $qMapping = mysqli_query($koneksi, "SELECT k.nik_karyawan, s.nama_site FROM tb_karyawan k LEFT JOIN tb_karier kr ON k.id_karyawan = kr.id_karyawan LEFT JOIN tb_site s ON kr.id_site = s.id_site");
            if($qMapping) {
                while($rowM = mysqli_fetch_assoc($qMapping)) {
                    $siteMapping[$rowM['nik_karyawan']] = $rowM['nama_site'];
                }
            }

            foreach ($dataKaryawan as $karyawan) {
                $val = implode('|', [
                    $karyawan['nik_karyawan'],
                    $karyawan['nama_lengkap'] ?? '',
                    $karyawan['nama_jabatan'] ?? ''
                ]);
                
                // Dapatkan nama site karyawan
                $site = $karyawan['nama_site'] ?? ($siteMapping[$karyawan['nik_karyawan']] ?? 'Tanpa Site');
                
                // Sisipkan atribut data-site
                echo '<option value="' . htmlspecialchars($val) . '" data-site="'.htmlspecialchars($site).'">' . htmlspecialchars($karyawan['nik_karyawan'] . ' - ' . $karyawan['nama_lengkap']) . '</option>';
            }
            ?>
        </select>

        <input type="date" id="startDate" />
        <input type="date" id="endDate" />
        <button onclick="generateSlip()">Generate Slip</button>
        <button onclick="downloadPDF()" id="downloadBtn" class="secondary">Download PDF</button>
        <button onclick="downloadSlipZip()" class="secondary">Download ZIP Semua Slip</button>
        
        <div id="progressContainer" style="display:none; margin-top:15px;">
            <div style="width:100%; background:#ddd; height:22px; border-radius:5px;">
                <div id="progressBar" style="height:22px; width:0%; background:#4CAF50; border-radius:5px; transition:width 0.3s;"></div>
            </div>
            <p id="progressText" style="margin-top:5px; font-weight:bold;">Mulai memproses...</p>
        </div>
    </div>

    <div id="slipAlert" class="slip-alert" role="alert"></div>

    <div class="wrap" id="slip">
        <div class="header">
            <div class="company" style="display:flex; align-items:center; gap:10px;">
                <img src="assets/img/logo/ipm-logo.png" alt="Logo Perusahaan" style="height:50px; object-fit:contain;">
                <div>
                    <div style="font-size:16px; font-weight:800;">PT. TRIPUTRA JAYASULTRA</div>
                    <small>JL. EKONOMI · TLP/WA 0852-5526-4538</small>
                    <small>POMALAA, KABUPATEN KOLAKA</small>
                </div>
            </div>
            <div class="title">
                <div style="font-size:16px;">SLIP GAJI</div>
                <div class="period" id="period">-</div>
            </div>
        </div>


        <div class="meta">
            <div class="left">
                <div><label>NIK :</label><span id="nikDisplay">-</span></div>
                <div><label>NAMA KARYAWAN :</label> <span id="nama">-</span></div>
            </div>
            <div class="right">
                <div><label>JABATAN :</label> <span id="jabatan">-</span></div>
            </div>
        </div>

        <div class="two-cols">
            <div class="box">
                <h5>PENDAPATAN</h5>
                <table>
                    <tbody id="pendapatanBody"></tbody>
                </table>
            </div>

            <div class="box">
                <h5>POTONGAN</h5>
                <table>
                    <tbody id="potonganBody"></tbody>
                </table>
            </div>
        </div>

        <div class="summary">
            <div class="left">
                <div><span class="bold">GAJI BERSIH</span></div>
                <div style="font-size:16px; margin-top:4px;" id="gajiBersih">Rp -</div>
            </div>
            <div class="right">
                <table>
                    <tr>
                        <td>Wajib Lembur</td>
                        <td id="total_jam_wl">0 Jam</td>
                        <td>HARI KERJA</td>
                        <td class="right" id="hariKerja">0 Hari</td>
                    </tr>
                    <tr>
                        <td>Overtime</td>
                        <td id="total_jam_ot">0 Jam</td>
                        <td>SAKIT</td>
                        <td class="right" id="sakit">0 Hari</td>
                    </tr>
                    <tr>
                        <td>SPL 1-7</td>
                        <td id="total_jam_spl_1_7">0 Jam</td>
                        <td>IZIN</td>
                        <td class="right" id="izin">0 Hari</td>
                    </tr>
                    <tr>
                        <td>SPL Jam Ke-8</td>
                        <td id="total_jam_spl_8">0 Jam</td>
                        <td>MANGKIR</td>
                        <td class="right" id="mangkir">0 Hari</td>
                    </tr>
                    <tr>
                        <td>SPL Jam 9+</td>
                        <td id="total_jam_spl_9_up">0 Jam</td>
                        <td>Kurang Jam</td>
                        <td class="right" id="total_jam_kurang">0 Jam</td>
                    </tr>
                </table>
            </div>
        </div>

        <p class="notes">
            WL: WAJIB LEMBUR · OT: OVER TIME · SPL: MINGGU & TANGGAL MERAH LIBUR NASIONAL
        </p>

        <!-- Absensi: tampilan lama (tabel) + tampilan kalender, default kalender -->
        <div style="margin-top:10px;">
            <h5 style="margin:0 0 6px 0;font-size:13px;">REKAP ABSENSI</h5>
            <div class="absen-toggle no-print" data-html2canvas-ignore="true">
                <button type="button" id="btnAbsenKalender" class="active" onclick="setAbsenView('kalender')">Kalender</button>
                <button type="button" id="btnAbsenTabel" class="secondary" onclick="setAbsenView('tabel')">Tabel</button>
            </div>
            <div id="absenKalenderWrap">
                <div class="kal-legend" id="kalLegend"></div>
                <div class="kal-scroll">
                    <div class="kal-grid" id="absenKalGrid"></div>
                </div>
            </div>
            <div id="absenTabelWrap" style="display:none;">
                <table id="absenTable">
                    <thead>
                        <tr>
                            <th rowspan="2" style="width:40px">NO</th>
                            <th rowspan="2">HARI DAN TANGGAL</th>
                            <th colspan="3">HARI BIASA / SPL</th>
                        </tr>
                    </thead>
                    <tbody id="absenBody"></tbody>
                </table>
            </div>
        </div>

        <div class="signature">
            HR. CFO<br><br><strong>MUH. FADLY SAPUTRA</strong>
        </div>
    </div>

    <script>
        // helper: format rupiah bulat, dibulatkan ke angka terdekat
        function fmt(n) {
            if (n === null || n === undefined || n === '') return '-';
            const x = Math.round(Number(n) || 0); // dibulatkan ke integer
            return 'Rp ' + x.toLocaleString('id-ID', {
                maximumFractionDigits: 0
            });
        }


        // helper: format date dd-mm-yyyy
        function fmtDateForFilename(d) {
            const dd = String(d.getDate()).padStart(2, '0');
            const mm = String(d.getMonth() + 1).padStart(2, '0');
            const yyyy = d.getFullYear();
            return dd + '-' + mm + '-' + yyyy;
        }

        function render(data) {
            hideSlipAlert();

            // =============== METADATA ===============
            document.getElementById('period').textContent = data.period || '-';
            document.getElementById('nikDisplay').textContent = data.nik || '-';
            document.getElementById('nama').textContent = data.nama || '-';
            document.getElementById('jabatan').textContent = data.jabatan || '-';
            document.getElementById('total_jam_wl').textContent = data.total_jam_wl || '-';
            document.getElementById('total_jam_ot').textContent = data.total_jam_ot || '-';
            document.getElementById('total_jam_spl_1_7').textContent = data.total_jam_spl_1_7 || '-';
            document.getElementById('total_jam_spl_8').textContent = data.total_jam_spl_8 || '-';
            document.getElementById('total_jam_spl_9_up').textContent = data.total_jam_spl_9_up || '-';

            // =======================================================
            // ===============   SUSUN ULANG PENDAPATAN   ===============
            // =======================================================
            const pendBody = document.getElementById("pendapatanBody");
            pendBody.innerHTML = "";

            // 1) BASIC
            pendBody.innerHTML += `
        <tr><td>BASIC</td><td class="right">${fmt(data.basic)}</td></tr>
    `;

            // 2) TUNJANGAN DINAMIS
            if (Array.isArray(data.tunjangan_pendapatan)) {
                data.tunjangan_pendapatan.forEach(t => {
                    pendBody.innerHTML += `
                <tr><td style="text-transform:uppercase;">${t.nama}</td><td class="right">${fmt(t.jumlah)}</td></tr>
            `;
                });
            }

            // 3) LEMBUR & SPL
            const lemburList = [{
                    key: "total_lembur",
                    label: "OT PERTAMA"
                },
                {
                    key: "total_overtime",
                    label: "OT KEDUA"
                },
                {
                    key: "total_spl_1_7",
                    label: "SPL 1–7"
                },
                {
                    key: "total_spl_8",
                    label: "SPL 8"
                },
                {
                    key: "total_spl_9_up",
                    label: "SPL 9+"
                }
            ];

            lemburList.forEach(l => {
                let val = data[l.key] || 0;
                pendBody.innerHTML += `
            <tr><td>${l.label}</td><td class="right">${fmt(val)}</td></tr>
        `;
            });

            // // 5) KELEBIHAN HARI
            // if (data.hari_kerja > 25) {
            //     pendBody.innerHTML += `
            //      <tr><td>KELEBIHAN HARI (${data.kelebihan_hari} Hari)</td><td class="right">${fmt(data.pendapatan_kelebihan_hari)}</td></tr>
            //  `;
            // }

            // 6) RAPEL
            if (Array.isArray(data.rapel)) {
                data.rapel.forEach(r => {
                    pendBody.innerHTML += `
                <tr><td>RAPEL - ${r.nama} (${r.tanggal})</td><td class="right">${fmt(r.jumlah)}</td></tr>
            `;
                });
            }

            // 7) TOTAL PENDAPATAN
            pendBody.innerHTML += `
        <tr class="bold">
            <td>JUMLAH PENDAPATAN</td>
            <td class="right">${fmt(data.total_pendapatan)}</td>
        </tr>
    `;

            // =======================================================
            // ===============     SUSUN ULANG POTONGAN     ===============
            // =======================================================
            const potBody = document.getElementById("potonganBody");
            potBody.innerHTML = "";

            // 1) POTONGAN DINAMIS (BPJS, JHT DLL)
            if (Array.isArray(data.potongan_tunjangan)) {
                data.potongan_tunjangan.forEach(p => {
                    potBody.innerHTML += `
                <tr><td>${p.nama}</td><td class="right">${fmt(p.jumlah)}</td></tr>
            `;
                });
            }

            // 2) POTONGAN TETAP
            const potTetap = [{
                    key: "pot_pkwt",
                    label: "POTONGAN PKWT"
                },
                {
                    key: "pot_izin",
                    label: "POTONGAN IZIN"
                },
                {
                    key: "pot_mangkir",
                    label: "POTONGAN MANGKIR"
                },
                {
                    key: "potongan_perjam",
                    label: "KE-KURANGAN JAM NORMAL"
                },
                {
                    key: "potongan_cuti_khusus", // <--- TAMBAHKAN KEY BARU
                    label: "POTONGAN CUTI HARIAN"
                }
            ];

            potTetap.forEach(p => {
                let val = data[p.key] || 0;
                // Logika: Tampilkan jika bukan potongan_cuti_khusus, ATAU jika potongan_cuti_khusus ada nilainya (> 0)
                if (p.key !== "potongan_cuti_khusus" || val > 0) {
                    potBody.innerHTML += `
                    <tr><td>${p.label}</td><td class="right">${fmt(val)}</td></tr>
                    `;
                }
            });


            // 3) PINJAMAN (Kasbon & Cicilan SIO)
            if (Array.isArray(data.pinjaman)) {
                data.pinjaman.forEach(p => {
                    potBody.innerHTML += `
                <tr><td style="text-transform:uppercase;">${p.nama}</td><td class="right">${fmt(p.jumlah)}</td></tr>
            `;
                });
            }

            // 4) POTONGAN TIDAK TETAP (Denda, Sumbangan, dll)
            if (Array.isArray(data.potongan_lain)) {
                data.potongan_lain.forEach(p => {
                    potBody.innerHTML += `
                <tr><td style="text-transform:uppercase;">${p.nama}</td><td class="right">${fmt(p.jumlah)}</td></tr>
            `;
                });
            }

            // 5) TOTAL POTONGAN
            potBody.innerHTML += `
        <tr class="bold">
            <td>JUMLAH POTONGAN</td>
            <td class="right">${fmt(data.total_potongan)}</td>
        </tr>
    `;

            // =============================
            //        RENDER RINGKASAN
            // =============================
            document.getElementById('gajiBersih').textContent = fmt(data.gaji_bersih);
            document.getElementById('hariKerja').textContent = data.hari_kerja || 0;
            document.getElementById('sakit').textContent = data.sakit || 0;
            document.getElementById('izin').textContent = data.izin || 0;
            document.getElementById('mangkir').textContent = data.mangkir || 0;
            document.getElementById('total_jam_kurang').textContent = data.total_jam_kurang || 0;

            // =============================
            //  ABSENSI: render dua tampilan (tabel lama + kalender), default kalender
            // =============================
            renderAbsenTabel(data.absen || []);
            renderAbsenKalender(data.absen || []);
            applyAbsenView();
        }

        // Default tampilan kalender
        var absenView = 'kalender';

        function setAbsenView(view) {
            absenView = (view === 'tabel') ? 'tabel' : 'kalender';
            applyAbsenView();
        }

        function applyAbsenView() {
            const kalWrap = document.getElementById("absenKalenderWrap");
            const tabelWrap = document.getElementById("absenTabelWrap");
            const btnKal = document.getElementById("btnAbsenKalender");
            const btnTabel = document.getElementById("btnAbsenTabel");
            const isKal = absenView !== 'tabel';
            if (kalWrap) kalWrap.style.display = isKal ? "" : "none";
            if (tabelWrap) tabelWrap.style.display = isKal ? "none" : "";
            if (btnKal) btnKal.classList.toggle("active", isKal);
            if (btnTabel) btnTabel.classList.toggle("active", !isKal);
        }

        // Tampilan lama: tabel absensi
        function renderAbsenTabel(absenList) {
            const absBody = document.getElementById("absenBody");
            if (!absBody) return;
            absBody.innerHTML = "";

            if (Array.isArray(absenList)) {
                absenList.forEach((r, index) => {
                    let cls = "";
                    const ket = String(r.ket).toUpperCase();

                    if (ket.includes("OFF") || ket.includes("SPL")) cls = "off";
                    if (ket.includes("SAKIT")) cls = "sd";
                    if (ket.includes("IZIN")) cls = "izin";
                    if (ket.includes("CUTI")) cls = "cm";

                    const hariNama = new Date(r.tanggal).toLocaleString("id-ID", {
                        weekday: "long",
                    }).toUpperCase();

                    const nomorUrut = index + 1;

                    absBody.innerHTML += `
                <tr>
                    <td class="center ${cls}">${nomorUrut}</td>
                    <td class="${cls}">
                        ${hariNama}<br>
                        <small style="color:var(--muted)">${r.tanggal}</small>
                    </td>
                    <td class="center ${cls}">${r.jam == 0 ? r.ket : r.jam}</td>
                </tr>
            `;
                });
            }
        }

        // Mapping ket slip -> status kalender rekap
        function mapKetKalender(ketRaw) {
            const k = String(ketRaw || '').toUpperCase();
            if (k === 'HADIR') return 'Masuk';
            if (k === 'SPL') return 'SPL';
            if (k === 'OFF') return 'OFF';
            if (k === 'IZIN') return 'Izin';
            if (k === 'SAKIT') return 'Sakit';
            if (k === 'CUTI') return 'Cuti';
            if (k === 'MANGKIR') return 'Mangkir';
            if (k === 'RESIGN' || k === 'R') return 'Resign';
            if (k === 'NEW') return 'New';
            return 'Mangkir';
        }

        function renderAbsenKalender(absenList) {
            const grid = document.getElementById("absenKalGrid");
            const legend = document.getElementById("kalLegend");
            if (!grid) return;
            const KAL_COLOR = {
                "Masuk": "#71dd37", "SPL": "#ffab00", "OFF": "#8592a3",
                "Izin": "#696cff", "Sakit": "#03c3ec", "Cuti": "#6f42c1",
                "Mangkir": "#ff3e1d", "Resign": "#566a7f", "New": "#566a7f"
            };
            if (legend) {
                legend.innerHTML = Object.keys(KAL_COLOR)
                    .map(k => `<span style="background:${KAL_COLOR[k]}">${k}</span>`).join("");
            }
            const NAMA_HARI = ["Sen", "Sel", "Rab", "Kam", "Jum", "Sab", "Min"];
            const NAMA_BULAN = {
                "01": "Januari", "02": "Februari", "03": "Maret", "04": "April",
                "05": "Mei", "06": "Juni", "07": "Juli", "08": "Agustus",
                "09": "September", "10": "Oktober", "11": "November", "12": "Desember"
            };
            const NAMA_BULAN_SINGKAT = { "01": "Jan", "02": "Feb", "03": "Mar", "04": "Apr", "05": "Mei", "06": "Jun", "07": "Jul", "08": "Agu", "09": "Sep", "10": "Okt", "11": "Nov", "12": "Des" };

            let html = NAMA_HARI.map(h => `<div class="kal-head">${h}</div>`).join("");
            let lastMonth = "";

            // Pastikan urut tanggal
            const rows = [...absenList].sort((a, b) => String(a.tanggal).localeCompare(String(b.tanggal)));

            rows.forEach((r, idx) => {
                const tglStr = String(r.tanggal || '');
                const parts = tglStr.split('-');
                if (parts.length !== 3) return;
                const [y, m, dNum] = parts;
                const monthKey = `${y}-${m}`;
                const dt = new Date(y, parseInt(m, 10) - 1, parseInt(dNum, 10));
                let weekday = dt.getDay(); // 0=Min .. 6=Sab
                weekday = weekday === 0 ? 7 : weekday; // 1=Sen .. 7=Min

                if (monthKey !== lastMonth) {
                    html += `<div class="kal-month">${NAMA_BULAN[m] || m} ${y}</div>`;
                    lastMonth = monthKey;
                }
                if (idx === 0 || String(rows[idx - 1].tanggal || '').substring(0, 7) !== monthKey) {
                    for (let i = 1; i < weekday; i++) html += `<div class="kal-day empty"></div>`;
                }

                const status = mapKetKalender(r.ket);
                const jam = parseFloat(r.jam) || 0;
                const jamInfo = (status === "Masuk" || status === "SPL") && jam > 0
                    ? `<div class="kal-jam">${jam} jam</div>`
                    : "";
                const title = `${dNum} ${NAMA_BULAN[m] || m} — ${status}${jam > 0 ? ` (${jam} jam)` : ""}`;

                html += `<div class="kal-day kal-${status}" title="${title}">
                    <div class="kal-date">${parseInt(dNum, 10)} <small>${NAMA_BULAN_SINGKAT[m] || ""}</small></div>
                    <span class="kal-status">${status}</span>
                    ${jamInfo}
                </div>`;
            });

            grid.innerHTML = html;
        }

        function clearAbsenKalender() {
            const grid = document.getElementById("absenKalGrid");
            if (grid) grid.innerHTML = "";
            const legend = document.getElementById("kalLegend");
            if (legend) legend.innerHTML = "";
            const absBody = document.getElementById("absenBody");
            if (absBody) absBody.innerHTML = "";
        }

        function showSlipAlert(message) {
            const alertBox = document.getElementById('slipAlert');
            alertBox.textContent = message;
            alertBox.style.display = 'block';
        }

        function hideSlipAlert() {
            const alertBox = document.getElementById('slipAlert');
            alertBox.textContent = '';
            alertBox.style.display = 'none';
        }


        // generateSlip: fetch data slip dari backend get-slip-gaji.php
        async function generateSlip() {
            const nikSel = document.getElementById('nikSelect').value;
            const [nik, namaDefault, jabDefault] = nikSel.split('|');
            const start = document.getElementById('startDate').value;
            const end = document.getElementById('endDate').value;

            if (!start || !end) {
                showSlipAlert("Tanggal mulai dan akhir wajib diisi.");
                return;
            }

            // show temporary placeholders while loading
            document.getElementById('period').textContent = 'Memuat...';
            document.getElementById('nama').textContent = namaDefault || '-';
            document.getElementById('jabatan').textContent = jabDefault || '-';

            try {
                const resp = await fetch('app/controller/get-slip/get-slip-gaji.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        nik,
                        startDate: start,
                        endDate: end
                    })
                });

                if (!resp.ok) {
                    const txt = await resp.text();
                    console.error('server error', txt);
                    showSlipAlert("Gagal mengambil data slip dari server.");
                    return;
                }

                const data = await resp.json();

                // --- TAMBAHAN CEK SKIP ---
                if (data.skip || data.error) {
                    const errorMessage = data.error || "Data tidak dapat diproses.";
                    showSlipAlert(errorMessage);
                    document.getElementById('period').textContent = '-';
                    document.getElementById('nama').textContent = '-';
                    document.getElementById('jabatan').textContent = '-';
                    document.getElementById('gajiBersih').textContent = 'Rp -';
                    document.getElementById("pendapatanBody").innerHTML = "";
                    document.getElementById("potonganBody").innerHTML = "";
                    if (typeof clearAbsenKalender === 'function') clearAbsenKalender();
                    return; // Hentikan proses render
                }

                if (typeof data.basic === 'undefined' || typeof data.total_pendapatan === 'undefined') {
                    showSlipAlert("Response slip tidak lengkap. Periksa data karier dan absensi karyawan.");
                    return;
                }

                // render data
                render(data);

            } catch (e) {
                console.error(e);
                showSlipAlert("Terjadi kesalahan saat mengambil data slip.");
            }
        }

        // download PDF with filename "nama karyawan - jabatan periode.pdf"
        function downloadPDF() {
            const slipElem = document.getElementById('slip');
            const nama = document.getElementById('nama').textContent.trim().replace(/\s+/g, ' ') || 'karyawan';
            const jab = document.getElementById('jabatan').textContent.trim().replace(/\s+/g, ' ') || 'jabatan';
            
            // --- MENGAMBIL PERIODE DARI INPUT ---
            const startPeriod = document.getElementById('startDate').value;
            const endPeriod = document.getElementById('endDate').value;
            const periodeStr = `Periode ${startPeriod} sd ${endPeriod}`; 
            // ------------------------------------

            const safe = (s) => s.replace(/[\/\\:?<>|"]/g, '-'); // filter illegal filename chars
            const filename = `${safe(nama)} - ${safe(jab)} ${periodeStr}.pdf`;

            const opt = {
                margin: 0.4,
                filename: filename,
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
            html2pdf().set(opt).from(slipElem).save();
        }

        // auto set default period (21st previous - 20th current logic)
        (function setDefaultDates() {
            const today = new Date();
            let start, end;
            if (today.getDate() <= 20) {
                start = new Date(today.getFullYear(), today.getMonth() - 1, 22);
                end = new Date(today.getFullYear(), today.getMonth(), 21);
            } else {
                start = new Date(today.getFullYear(), today.getMonth() - 1, 22);
                end = new Date(today.getFullYear(), today.getMonth(), 21);
            }
            const f = d => d.toISOString().substring(0, 10);
            document.getElementById('startDate').value = f(start);
            document.getElementById('endDate').value = f(end);
        })();

        // DOWNLOAD SEKALIGUS FILE PDF SLIP GAJI
        async function downloadSlipZip() {
            const options = document.querySelectorAll("#nikSelect option");
            const start = document.getElementById("startDate").value;
            const end = document.getElementById("endDate").value;

            // 1. Ambil nama Site yang sedang dipilih pada Dropdown
            const selectedSite = document.getElementById("siteFilter").value;

            if (!start || !end) {
                Swal.fire({ icon: 'warning', title: 'Perhatian', text: "Tanggal mulai & akhir wajib diisi." });
                return;
            }

            if (!(await confirmDialog("Generate semua slip dan download ZIP?"))) return;

            // ELEMENT PROGRESS
            const container = document.getElementById("progressContainer");
            const bar = document.getElementById("progressBar");
            const text = document.getElementById("progressText");

            container.style.display = "block";
            bar.style.width = "0%";
            text.innerHTML = "Menyiapkan proses...";

            // ZIP
            let zip = new JSZip();
            const slipElem = document.getElementById("slip");

            slipElem.style.visibility = "hidden";

            const total = options.length;

            const safe = (s) => s.replace(/[\/\\:?<>|"]/g, "-");

            for (let i = 0; i < total; i++) {
                const opt = options[i].value.split("|");
                const nik = opt[0];
                const nama = opt[1] || "Karyawan";
                const jab = opt[2] || "Jabatan";

                // Update progress text
                text.innerHTML = `Memproses ${i + 1} dari ${total} karyawan...`;

                // Update progress bar
                let percent = Math.round(((i + 1) / total) * 100);
                bar.style.width = percent + "%";

                // Ambil data slip
                const resp = await fetch("app/controller/get-slip/get-slip-gaji.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({
                        nik,
                        startDate: start,
                        endDate: end
                    })
                });

                const data = await resp.json();

                // --- TAMBAHAN CEK SKIP UNTUK ZIP ---
                if (data.skip || data.error) {
                    console.log(`Melewati NIK ${nik}: ${data.error}`);
                    // Lanjut ke iterasi (karyawan) berikutnya, abaikan render & PDF
                    continue; 
                }

                // Render slip
                render(data);
                await new Promise(r => setTimeout(r, 700));

                slipElem.style.visibility = "visible";

                // Nama file PDF
                // --- MENGGUNAKAN PERIODE YANG SUDAH DIDEKLARASIKAN DI AWAL FUNGSI ---
                const periodeStr = `Periode ${start} sd ${end}`;
                
                const filename = `${safe(nama)}-${safe(jab)}-${periodeStr}.pdf`;
                // --------------------------------------------------------------------

                // Generate PDF BLOB
                const pdfBlob = await html2pdf()
                    .set({
                        margin: 0.4,
                        image: {
                            type: "jpeg",
                            quality: 0.98
                        },
                        html2canvas: {
                            scale: 2
                        },
                        jsPDF: {
                            unit: "in",
                            format: "a4",
                            orientation: "portrait"
                        }
                    })
                    .from(slipElem)
                    .outputPdf("blob");

                // Masukkan PDF ke ZIP
                zip.file(filename, pdfBlob);

                slipElem.style.visibility = "hidden";

                await new Promise(r => setTimeout(r, 300));
            }

            text.innerHTML = "Menggabungkan ke dalam file ZIP...";

            // Generate ZIP final
            zip.generateAsync({
                type: "blob"
            }).then(function(content) {
                // 3. MENGATUR NAMA FILE ZIP MENJADI DINAMIS
                // Jika tidak milih site, namanya "Semua-Site"
                const siteName = selectedSite ? safe(selectedSite) : "Semua-Site"; 
                const zipFilename = `Batch-Slip-Gaji-${siteName}-Periode-${start}-sd-${end}.zip`;
                
                // Simpan dengan nama baru
                saveAs(content, zipFilename);

                text.innerHTML = "Selesai! ZIP berhasil di-download.";
                bar.style.width = "100%";

                // Sembunyikan progress setelah 1 detik
                setTimeout(() => {
                    container.style.display = "none";
                }, 1200);

                slipElem.style.visibility = "visible";
            });
        }
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.7.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        // Helper konfirmasi (standalone, tanpa index.php). Pakai Swal jika ada.
        if (typeof window.confirmDialog === 'undefined') {
            window.confirmDialog = function(message) {
                var text = String(message == null ? '' : message);
                if (typeof Swal !== 'undefined') {
                    return Swal.fire({ icon: 'warning', title: 'Konfirmasi', text: text, showCancelButton: true, confirmButtonText: 'Ya, Lanjutkan', cancelButtonText: 'Batal', confirmButtonColor: '#0b5ed7', reverseButtons: true, focusCancel: true }).then(function(r) { return !!(r && r.isConfirmed); });
                }
                return Promise.resolve(window['confirm'](text));
            };
        }
        $(document).ready(function() {
            // Inisialisasi Select2
            $('.search').select2({});

            // 1. Simpan semua opsi asli karyawan ke dalam array memori
            let allKaryawanOptions = [];
            $('#nikSelect option').each(function() {
                allKaryawanOptions.push({
                    val: $(this).val(),
                    text: $(this).text(),
                    site: $(this).attr('data-site')
                });
            });

            // 2. Memicu event saat filter Site diubah
            $('#siteFilter').on('change', function() {
                let selectedSite = $(this).val();
                let $nikSelect = $('#nikSelect');
                
                // Kosongkan opsi dropdown karyawan saat ini
                $nikSelect.empty();
                
                // Kembalikan hanya karyawan yang sitenya sesuai filter
                allKaryawanOptions.forEach(function(opt) {
                    if (!selectedSite || opt.site === selectedSite) {
                        let newOption = new Option(opt.text, opt.val, false, false);
                        $(newOption).attr('data-site', opt.site);
                        $nikSelect.append(newOption);
                    }
                });
                
                // Perbarui tampilan antarmuka Select2
                $nikSelect.trigger('change');
            });
        });
    </script>
</body>

</html>

<!-- Fungsi Download Slip langsung 3 periode, pke di console dengan memilih karyawan terlebih dahulu-->
<!-- async function downloadTigaBulan() {
    // Ambil NIK dari dropdown
    const nikSel = document.getElementById('nikSelect').value;
    const [nik] = nikSel.split('|');

    // Definisikan 3 periode yang diminta
    const periods = [
        { start: '2025-12-21', end: '2026-01-20' },
        { start: '2026-01-21', end: '2026-02-20' },
        { start: '2026-02-21', end: '2026-03-20' }
    ];

    const slipElem = document.getElementById("slip");

    console.log("Memulai proses download...");

    for (let i = 0; i < periods.length; i++) {
        const p = periods[i];
        console.log(`Memproses periode: ${p.start} s/d ${p.end}`);

        try {
            // 1. Fetch data dari backend
            const resp = await fetch('app/controller/get-slip/get-slip-gaji.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ nik, startDate: p.start, endDate: p.end })
            });

            if (!resp.ok) {
                console.error(`Gagal fetch data untuk periode ${p.start}`);
                continue;
            }

            const data = await resp.json();

                // --- TAMBAHAN CEK SKIP UNTUK ZIP ---
                if (data.skip || data.error) {
                    console.log(`Melewati NIK ${nik}: ${data.error}`);
                    // Lanjut ke iterasi (karyawan) berikutnya, abaikan render & PDF
                    continue; 
                }

            // 2. Render ke tampilan
            render(data);

            // Jeda 800ms agar DOM selesai menggambar ulang tabel
            await new Promise(r => setTimeout(r, 800));

            // --- PERBAIKAN DI SINI ---
            // Mengambil nama dan jabatan langsung dari data real bulan tersebut
            const namaKaryawan = data.nama || 'Karyawan';
            const jabatanKaryawan = data.jabatan || 'Jabatan';
            // -------------------------

            // 3. Eksekusi html2pdf
            const periodeStr = `Periode ${p.start} sd ${p.end}`;
            const safe = (s) => s.replace(/[\/\\:?<>|"]/g, "-");
            const filename = `${safe(namaKaryawan)} - ${safe(jabatanKaryawan)} ${periodeStr}.pdf`;

            await html2pdf().set({
                margin: 0.4,
                filename: filename,
                image: { type: "jpeg", quality: 0.98 },
                html2canvas: { scale: 2 },
                jsPDF: { unit: "in", format: "a4", orientation: "portrait" }
            }).from(slipElem).save();

            // Jeda 1.5 detik sebelum memproses bulan berikutnya
            await new Promise(r => setTimeout(r, 1500));

        } catch (error) {
            console.error(`Error pada periode ${p.start}:`, error);
        }
    }
    
    console.log("Selesai! 3 file PDF seharusnya sudah ter-download.");
    Swal.fire({ icon: 'success', title: 'Selesai', text: "Proses download 3 bulan selesai!" });
}

// Jalankan fungsinya
downloadTigaBulan(); -->
