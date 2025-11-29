<style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        padding: 20px;
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        margin: 0;
        color: #333;
    }

    .container {
        max-width: 1200px;
        margin: auto;
        background: #fff;
        padding: 30px;
        border-radius: 15px;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
    }

    .form-section {
        margin-bottom: 30px;
        padding: 20px;
        background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
        border-radius: 10px;
        border: 1px solid #90caf9;
    }

    .form-section label {
        display: block;
        margin-bottom: 8px;
        font-weight: bold;
        color: #1565c0;
        font-size: 14px;
    }

    .form-section input,
    .form-section select {
        width: 100%;
        padding: 10px;
        margin-bottom: 15px;
        border: 1px solid #90caf9;
        border-radius: 6px;
        font-size: 14px;
        background: #fff;
        transition: border-color 0.3s;
    }

    .form-section input:focus,
    .form-section select:focus {
        border-color: #42a5f5;
        outline: none;
    }

    .form-section button {
        background: linear-gradient(135deg, #4caf50 0%, #66bb6a 100%);
        color: #fff;
        border: none;
        padding: 12px 25px;
        cursor: pointer;
        border-radius: 8px;
        font-size: 16px;
        font-weight: bold;
        transition: background 0.3s, transform 0.2s;
    }

    .form-section button:hover {
        background: linear-gradient(135deg, #388e3c 0%, #4caf50 100%);
        transform: translateY(-2px);
    }

    .download-btn {
        margin: 30px 0;
        background: linear-gradient(135deg, #2196f3 0%, #42a5f5 100%);
        color: #fff;
        border: none;
        padding: 15px 30px;
        cursor: pointer;
        border-radius: 8px;
        font-size: 16px;
        font-weight: bold;
        transition: background 0.3s, transform 0.2s;
        display: block;
        width: 100%;
        max-width: 200px;
        margin-left: auto;
        margin-right: auto;
    }

    .download-btn:hover {
        background: linear-gradient(135deg, #1976d2 0%, #2196f3 100%);
        transform: translateY(-2px);
    }

    .slip-container {
        width: 100%;
        background: #fff;
        padding: 30px;
        border: 3px solid #2196f3;
        border-radius: 15px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .header {
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 20px;
        border-bottom: 2px solid #e0e0e0;
        padding-bottom: 15px;
    }

    .logo {
        width: 100px;
        margin-right: 20px;
    }

    h2,
    h3 {
        text-align: center;
        margin: 0;
        font-weight: bold;
        color: #2196f3;
    }

    h2 {
        font-size: 28px;
        margin-bottom: 5px;
    }

    h3 {
        font-size: 20px;
        margin-bottom: 10px;
    }

    .right {
        text-align: right;
        font-size: 14px;
        color: #666;
        margin-bottom: 20px;
        font-style: italic;
    }

    .employee-info {
        display: flex;
        justify-content: space-between;
        margin-bottom: 20px;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 8px;
        border: 1px solid #dee2e6;
    }

    .employee-info div {
        flex: 1;
        padding: 5px;
    }

    .employee-info label {
        font-weight: bold;
        color: #495057;
        display: block;
        margin-bottom: 5px;
    }

    .employee-info span {
        color: #007bff;
        font-weight: bold;
    }

    /* Separate Tables for Pendapatan and Potongan */
    .tables-container {
        display: flex;
        gap: 20px;
        margin-bottom: 20px;
    }

    .table-section {
        flex: 1;
        background: #fff;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .table-section h4 {
        background: linear-gradient(135deg, #e9ecef 0%, #f8f9fa 100%);
        margin: 0;
        padding: 10px;
        text-align: right;
        font-weight: bold;
        color: #495057;
        border-bottom: 1px solid #dee2e6;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
    }

    table,
    th,
    td {
        border: 1px solid #dee2e6;
        padding: 12px;
        text-align: left;
    }

    th {
        background: linear-gradient(135deg, #e9ecef 0%, #f8f9fa 100%);
        font-weight: bold;
        color: #495057;
        text-align: center;
    }

    tbody tr:nth-child(even) {
        background: #f8f9fa;
    }

    tbody tr:hover {
        background: #e3f2fd;
        transform: scale(1.01);
        transition: all 0.2s ease;
    }

    .center {
        text-align: center;
        font-weight: bold;
        color: #28a745;
    }

    .highlight {
        background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
        font-weight: bold;
        color: #155724;
        text-align: center;
    }

    .gaji-bersih {
        text-align: center;
        font-size: 24px;
        font-weight: bold;
        color: #28a745;
        margin: 20px 0;
        padding: 15px;
        background: linear-gradient(135deg, #e8f5e8 0%, #d4edda 100%);
        border-radius: 10px;
        border: 2px solid #28a745;
    }

    .summary-info {
        display: flex;
        justify-content: space-around;
        margin: 20px 0;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 8px;
        border: 1px solid #dee2e6;
    }

    .summary-info div {
        text-align: center;
        flex: 1;
    }

    .summary-info label {
        font-weight: bold;
        color: #495057;
        display: block;
        margin-bottom: 5px;
    }

    .summary-info span {
        font-size: 18px;
        font-weight: bold;
        color: #007bff;
    }

    .note {
        margin-top: 20px;
        font-size: 12px;
        color: #666;
        text-align: center;
        font-style: italic;
    }

    /* Absen Table Specific */
    #absenTable {
        margin-top: 20px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        border-radius: 8px;
        overflow: hidden;
    }

    #absenTable th {
        background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
        color: #856404;
    }

    #absenTable tbody tr {
        border-bottom: 1px solid #dee2e6;
    }

    .izin {
        background-color: #cce5ff !important;
        color: #004085;
        font-weight: bold;
    }

    .mangkir {
        background-color: #f8d7da !important;
        color: #721c24;
        font-weight: bold;
    }

    .cuti {
        background-color: #fff3cd !important;
        color: #856404;
        font-weight: bold;
    }

    .sakit {
        background-color: #d1ecf1 !important;
        color: #0c5460;
        font-weight: bold;
    }

    .off {
        background-color: #fff3cd !important;
        color: #856404;
        font-weight: bold;
    }

    .hadir {
        background-color: #d4edda !important;
        color: #155724;
        font-weight: bold;
    }

    #absenTable td {
        background-color: inherit !important;
        vertical-align: middle;
    }

    .signature {
        margin-top: 40px;
        text-align: right;
        font-style: italic;
        border-top: 2px solid #e0e0e0;
        padding-top: 20px;
    }

    .signature b {
        color: #2196f3;
        font-size: 16px;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .tables-container {
            flex-direction: column;
        }
    }
</style>

<div class="container">
    <!-- Form generate slip -->
    <div class="form-section">
        <h3>Generate Slip Gaji</h3>
        <label for="nik">NIK Karyawan:</label>
        <select id="nik" class="select-search">
            <?php
            $dataKaryawan = data_karyawan('nik_karyawan');
            foreach ($dataKaryawan as $karyawan) {
                echo '<option value="' . ($karyawan['nik_karyawan']) . '">' . ($karyawan['nik_karyawan'] . ' - ' . $karyawan['nama_lengkap']) . '</option>';
            }
            ?>
        </select>
        <label for="startDate">Tanggal Awal:</label>
        <input type="date" id="startDate">
        <label for="endDate">Tanggal Akhir:</label>
        <input type="date" id="endDate">
        <button onclick="generateSlip()">Generate Slip</button>
    </div>

    <button class="download-btn" onclick="downloadPDF()">Download PDF</button>

    <div class="slip-container" id="slip">
        <div class="header">
            <img src="assets/img/logo/kpp-logo.jpg" alt="Logo Perusahaan" class="logo">
            <div>
                <h2>PT. KARYA PUTRA POMALAA</h2>
                <h3>SLIP GAJI</h3>
            </div>
        </div>
        <p class="center">
            JL. BUNGA KAMBOJA NO. 8 PELAMBUA | <span id="period">21/09 - 20/10 2025</span><br>
            TLP/WA 0852-9840-0028 | POMALAA, KABUPATEN KOLAKA
        </p>

        <div class="employee-info">
            <div>
                <label>NIK:</label>
                <span id="nikDisplay">KPP-0058</span>
            </div>
            <div>
                <label>JABATAN:</label>
                <span id="jabatan">CREW</span>
            </div>
            <div>
                <label>NAMA KARYAWAN:</label>
                <span id="nama">Ignasius Toni</span>
            </div>
        </div>

        <div class="tables-container">
            <!-- Pendapatan -->
            <div class="table-section">
                <h4>PENDAPATAN</h4>
                <table>
                    <thead>
                        <tr>
                            <th>NAMA</th>
                            <th class="center">Rp</th>
                        </tr>
                    </thead>
                    <tbody id="pendapatanBody"></tbody>
                    <tr class="highlight">
                        <td>JUMLAH PENDAPATAN</td>
                        <td class="center" id="totalPendapatan"><b>0</b></td>
                    </tr>
                </table>
            </div>

            <!-- Potongan -->
            <div class="table-section">
                <h4>POTONGAN</h4>
                <table>
                    <thead>
                        <tr>
                            <th>NAMA</th>
                            <th class="center">Rp</th>
                        </tr>
                    </thead>
                    <tbody id="potonganBody"></tbody>
                    <tr class="highlight">
                        <td>JUMLAH POTONGAN</td>
                        <td class="center" id="totalPotongan"><b>0</b></td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="gaji-bersih">
            GAJI BERSIH : Rp <span id="gajiBersih">0</span>
        </div>

        <div class="summary-info">
            <div>
                <label>HARI KERJA</label>
                <span id="hariKerja">0</span>
            </div>
            <div>
                <label>IZIN</label>
                <span id="izin">0</span>
            </div>
            <div>
                <label>SAKIT</label>
                <span id="sakit">0</span>
            </div>
            <div>
                <label>MANGKIR</label>
                <span id="mangkir">0</span>
            </div>
        </div>

        <p class="note">WL: WAJIB LEMBUR | OT: OVERTIME | SPL: MINGGU & TGL MERAH</p>

        <table id="absenTable">
            <thead>
                <tr>
                    <th>NO</th>
                    <th>Hari</th>
                    <th>TANGGAL</th>
                    <th>JML JAM</th>
                    <th>WL (Jam Ke 8)</th>
                    <th>9+</th>
                    <th>KETERANGAN</th>
                </tr>
            </thead>
            <tbody></tbody>
            <tfoot>
                <tr class="highlight">
                    <th colspan="3">TOTAL Jam Normal</th>
                    <th id="normalJam1_7" class="center">0</th>
                    <th id="normalWL8" class="center">0</th>
                    <th id="normalOT9up" class="center">0</th>
                    <th></th>
                </tr>
                <tr class="highlight">
                    <th colspan="3">Total Spl</th>
                    <th id="splJam1_7" class="center">0</th>
                    <th id="splOT8" class="center">0</th>
                    <th id="splOT9up" class="center">0</th>
                    <th></th>
                </tr>
            </tfoot>
        </table>

        <p class="signature">
            HR. CFO <br><br>
            <b>AHMAD ALBAR, S.H</b>
        </p>
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.3/html2pdf.bundle.min.js"></script>
<script>
    // Format rupiah
    function formatRupiah(value) {
        if (value == null || value == undefined) return '-';
        const n = Number(value);
        if (isNaN(n)) return value;
        return n.toLocaleString('id-ID');
    }

    async function generateSlip() {
        const nik = document.getElementById('nik').value;
        const startDate = document.getElementById('startDate').value;
        const endDate = document.getElementById('endDate').value;

        if (!nik || !startDate || !endDate) {
            alert('Mohon isi NIK dan tanggal');
            return;
        }

        try {
            const resp = await fetch('app/controller/get-slip/new-slip.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    nik,
                    startDate,
                    endDate
                })
            });

            if (!resp.ok) throw new Error(await resp.text());
            const data = await resp.json();
            if (data.error) {
                alert(data.error);
                return;
            }

            // Header
            document.getElementById('period').textContent = data.period || `${startDate} - ${endDate}`;
            document.getElementById('nikDisplay').textContent = data.nik || nik;
            document.getElementById('jabatan').textContent = data.jabatan || '-';
            document.getElementById('nama').textContent = data.nama || '-';

            // Reset tables
            const pendapatanBody = document.getElementById('pendapatanBody');
            const potonganBody = document.getElementById('potonganBody');
            pendapatanBody.innerHTML = '';
            potonganBody.innerHTML = '';

            // Pendapatan
            const pendapatan = [{
                    nama: 'BASIC',
                    value: data.basic
                },
                {
                    nama: 'TUNJANGAN HIDUP',
                    value: data.t_hidup
                },
                {
                    nama: 'TUNJANGAN DAERAH',
                    value: data.t_daerah
                },
                {
                    nama: 'TUNJANGAN MAKAN',
                    value: data.t_makan
                },
                {
                    nama: 'TUNJANGAN SKIL',
                    value: data.t_skil
                },
                {
                    nama: 'LEMBUR',
                    value: data.total_lembur
                },
                {
                    nama: 'OVERTIME',
                    value: data.total_overtime
                },
                {
                    nama: 'SPL 1-7',
                    value: data.total_spl_1_7
                },
                {
                    nama: 'SPL 8',
                    value: data.total_spl_8
                },
                {
                    nama: 'SPL 9+',
                    value: data.total_spl_9_up
                },
            ];
            pendapatan.forEach(p => {
                const row = document.createElement('tr');
                row.innerHTML = `<td>${p.nama}</td><td class="right">${formatRupiah(p.value)}</td>`;
                pendapatanBody.appendChild(row);
            });
            (data.tunjangan_pendapatan || []).forEach(t => {
                const row = document.createElement('tr');
                row.innerHTML = `<td>${t.nama}</td><td class="right">${formatRupiah(t.jumlah)}</td>`;
                pendapatanBody.appendChild(row);
            });

            // Potongan
            const potongan = [
                // {
                //     nama: 'POT PKWT',
                //     value: data.pot_pkwt
                // },
                {
                    nama: 'IZIN',
                    value: data.pot_izin
                },
                {
                    nama: 'MANGKIR',
                    value: data.pot_mangkir
                },
                {
                    nama: 'PINJAMAN',
                    value: data.total_potongan_pinjaman
                }
            ];
            (data.potongan_tunjangan || []).forEach(t => {
                const row = document.createElement('tr');
                row.innerHTML = `<td>${t.nama}</td><td class="right">${formatRupiah(t.jumlah)}</td>`;
                potonganBody.appendChild(row);
            });
            potongan.forEach(p => {
                const row = document.createElement('tr');
                row.innerHTML = `<td>${p.nama}</td><td class="right">${formatRupiah(p.value)}</td>`;
                potonganBody.appendChild(row);
            });

            // Total & gaji bersih
            document.getElementById('totalPendapatan').textContent = formatRupiah(data.total_pendapatan);
            document.getElementById('totalPotongan').textContent = formatRupiah(data.total_potongan);
            document.getElementById('gajiBersih').textContent = formatRupiah(data.gaji_bersih);

            // Absen
            const absenBody = document.getElementById('absenTable').getElementsByTagName('tbody')[0];
            absenBody.innerHTML = '';

            let normalJam1_7 = 0,
                normalWL8 = 0,
                normalOT9up = 0;
            let splJam1_7 = 0,
                splOT8 = 0,
                splOT9up = 0;

            (data.absen || []).forEach(r => {
                const ket = (r.ket || '').toUpperCase();
                const row = absenBody.insertRow();
                const dateObj = new Date(r.tanggal);
                const hariNama = dateObj.toLocaleDateString('id-ID', {
                    weekday: 'long'
                });

                // Class berdasarkan status
                let cls = 'hadir'; // default hadir
                switch (ket) {
                    case 'IZIN':
                        cls = 'izin';
                        break;
                    case 'SAKIT':
                        cls = 'sakit';
                        break;
                    case 'MANGKIR':
                        cls = 'mangkir';
                        break;
                    case 'CUTI':
                        cls = 'cuti';
                        break;
                    case 'OFF':
                    case 'SPL':
                        cls = 'off';
                        break;
                }
                row.className = cls;

                row.insertCell(0).textContent = r.no;
                row.insertCell(1).textContent = hariNama;
                row.insertCell(2).textContent = r.tanggal;
                row.insertCell(3).textContent = r.jam || '-';
                row.insertCell(4).textContent = r.wl || '-';
                row.insertCell(5).textContent = r.ot || '-';
                row.insertCell(6).textContent = ket;

                const jam = Number(r.jam) || 0;
                let normal = 0,
                    wl = 0,
                    ot = 0;

                // Hitung jam normal/WL/OT
                if (!['IZIN', 'SAKIT', 'MANGKIR', 'CUTI', 'OFF', 'SPL'].includes(ket)) {
                    normal = Math.min(7, jam);
                    wl = jam > 7 ? Math.min(1, jam - 7) : 0;
                    ot = jam > 8 ? jam - 8 : 0;
                    normalJam1_7 += normal;
                    normalWL8 += wl;
                    normalOT9up += ot;
                } else if (['OFF', 'SPL'].includes(ket)) {
                    splJam1_7 += Math.min(jam, 7);
                    splOT8 += Math.min(Math.max(jam - 7, 0), 1);
                    splOT9up += Math.max(jam - 8, 0);
                }
            });

            // Update footer totals
            document.getElementById('normalJam1_7').textContent = normalJam1_7;
            document.getElementById('normalWL8').textContent = normalWL8;
            document.getElementById('normalOT9up').textContent = normalOT9up;
            document.getElementById('splJam1_7').textContent = splJam1_7;
            document.getElementById('splOT8').textContent = splOT8;
            document.getElementById('splOT9up').textContent = splOT9up;

            // Summary hari
            document.getElementById('hariKerja').textContent = data.hari_kerja || 0;
            document.getElementById('izin').textContent = data.izin || 0;
            document.getElementById('sakit').textContent = data.sakit || 0;
            document.getElementById('mangkir').textContent = data.mangkir || 0;

        } catch (err) {
            console.error(err);
            alert('Gagal mengambil data: ' + err.message);
        }
    }

    function setDefaultDates() {
        const today = new Date();
        let startDate, endDate;
        if (today.getDate() <= 20) {
            startDate = new Date(today.getFullYear(), today.getMonth() - 1, 22);
            endDate = new Date(today.getFullYear(), today.getMonth(), 21);
        } else {
            startDate = new Date(today.getFullYear(), today.getMonth() - 1, 22);
            endDate = new Date(today.getFullYear(), today.getMonth(), 21);
        }
        const format = d => d.toISOString().substring(0, 10);
        document.getElementById('startDate').value = format(startDate);
        document.getElementById('endDate').value = format(endDate);
    }
    window.onload = setDefaultDates;

    // Download PDF
    function downloadPDF() {
        const slip = document.getElementById('slip');

        const namaKaryawan = document.getElementById('nama').textContent.trim().replace(/\s+/g, '_');
        const jabatan = document.getElementById('jabatan').textContent.trim().replace(/\s+/g, '_');
        const fileName = `${namaKaryawan}-${jabatan}.pdf`;

        const opt = {
            margin: 0.5,
            filename: fileName,
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

        html2pdf().set(opt).from(slip).save();
    }
</script>