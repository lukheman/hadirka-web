<?php
// File: slip-thr.php
require_once 'app/config/koneksi.php';

// Menangkap parameter tahun dari URL
$tahun_aktif = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');
?>
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Cetak Slip THR - Tahun <?= htmlspecialchars($tahun_aktif) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.3/html2pdf.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.7.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>
    <style>
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
            padding: 20px;
            border: 1px solid #ccc;
            box-sizing: border-box;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 10px;
            border-bottom: 2px solid #222;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }

        .company {
            line-height: 1.3;
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
            font-size: 18px;
            color: var(--accent);
        }

        .title .period {
            font-weight: 600;
            font-size: 14px;
            color: #222;
            margin-top: 5px;
        }

        .meta {
            display: flex;
            gap: 20px;
            margin: 15px 0;
            background: #f9f9f9;
            padding: 10px;
            border-radius: 5px;
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
            font-weight: bold;
        }

        .meta span {
            font-weight: 600;
        }

        .box {
            border: 1px solid #bbb;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 15px;
        }

        .box h5 {
            margin: 0 0 10px 0;
            font-size: 14px;
            border-bottom: 2px solid #ddd;
            padding-bottom: 8px;
            color: var(--accent);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        th,
        td {
            padding: 8px 10px;
            border: 1px dashed #e6e6e6;
        }

        .right {
            text-align: right;
            font-weight: 600;
        }

        .summary-box {
            background: #eaf3ff;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: 1px solid #b6d4fe;
        }

        .summary-box .label {
            font-size: 16px;
            font-weight: 700;
            color: #084298;
        }

        .summary-box .value {
            font-size: 22px;
            font-weight: 800;
            color: #084298;
        }

        .signature {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
        }

        .sig-box {
            text-align: center;
            font-size: 13px;
        }

        .sig-box .name {
            margin-top: 60px;
            font-weight: bold;
            text-decoration: underline;
        }

        @media print {
            body {
                margin: 0;
            }

            .wrap {
                border: 0;
                box-shadow: none;
                padding: 0;
            }

            .controls {
                display: none !important;
            }
        }

        .controls {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            background: #f1f1f1;
            padding: 15px;
            border-radius: 8px;
            align-items: center;
            flex-wrap: wrap;
        }

        button {
            padding: 8px 15px;
            border: 0;
            background: var(--accent);
            color: #fff;
            cursor: pointer;
            border-radius: 5px;
            font-weight: bold;
        }

        button.secondary {
            background: #198754;
        }

        select,
        input {
            padding: 8px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }
    </style>
</head>

<body>

    <div class="controls">
        <div style="display: flex; align-items: center; gap: 10px; border-right: 2px solid #ccc; padding-right: 15px; margin-right: 5px;">
            <strong style="color: var(--muted);">Periode:</strong>
            <select id="filterTahunUI" onchange="window.location.href='?tahun='+this.value" style="font-weight: bold; width: 100px;">
                <?php
                // Ambil daftar tahun unik dari tb_thr
                $sqlTahun = $koneksi->query("SELECT DISTINCT periode FROM tb_thr ORDER BY periode DESC");
                if ($sqlTahun && $sqlTahun->num_rows > 0) {
                    while ($t = $sqlTahun->fetch_assoc()) {
                        $thn = $t['periode'];
                        $sel = ($thn == $tahun_aktif) ? 'selected' : '';
                        echo "<option value='$thn' $sel>$thn</option>";
                    }
                } else {
                    echo "<option value='$tahun_aktif' selected>$tahun_aktif</option>";
                }
                ?>
            </select>
        </div>

        <input type="hidden" id="tahunAktif" value="<?= htmlspecialchars($tahun_aktif) ?>">

        <select id="karyawanSelect" class="search" style="width: 300px;">
            <option value="">-- Pilih Karyawan --</option>
            <?php
            // Ambil daftar karyawan yang HANYA mendapat THR di tahun tersebut
            $sqlKaryawan = $koneksi->query("SELECT r.id_karyawan, k.nik_karyawan, k.nama_lengkap 
                                            FROM tb_thr r 
                                            JOIN tb_karyawan k ON r.id_karyawan = k.id_karyawan 
                                            WHERE r.periode = '$tahun_aktif' 
                                            ORDER BY k.nama_lengkap ASC");
            while ($k = $sqlKaryawan->fetch_assoc()) {
                $val = implode('|', [$k['id_karyawan'], $k['nik_karyawan'], $k['nama_lengkap']]);
                echo '<option value="' . htmlspecialchars($val) . '">' . htmlspecialchars($k['nik_karyawan'] . ' - ' . $k['nama_lengkap']) . '</option>';
            }
            ?>
        </select>

        <button onclick="generateSlipTHR()">Tampilkan Slip</button>
        <button onclick="downloadPDF()" id="downloadBtn" class="secondary" disabled>Unduh PDF Slip Ini</button>
        <button onclick="downloadSlipZip()" class="secondary" style="background: #dc3545;">Download ZIP Semua Slip</button>

        <div id="progressContainer" style="display:none; margin-top:15px; width: 100%;">
            <div style="width:100%; background:#ddd; height:22px; border-radius:5px;">
                <div id="progressBar" style="height:22px; width:0%; background:#4CAF50; border-radius:5px; transition:width 0.3s;"></div>
            </div>
            <p id="progressText" style="margin-top:5px; font-weight:bold;">Mulai memproses...</p>
        </div>
    </div>

    <div class="wrap" id="slip">
        <div class="header">
            <div class="company" style="display:flex; align-items:center; gap:15px;">
                <img src="assets/img/logo/tjs-done.png" alt="Logo Perusahaan" style="height:60px; object-fit:contain;">
                <div>
                    <div style="font-size:18px; font-weight:900;">PT. TRIPUTRA JAYASULTRA</div>
                    <small>JL. EKONOMI · TLP/WA 0852-5526-4538</small>
                    <small>POMALAA, KABUPATEN KOLAKA</small>
                </div>
            </div>
            <div class="title">
                <div>SLIP TUNJANGAN HARI RAYA (THR)</div>
                <div class="period">Periode Tahun: <span id="slipTahun">-</span></div>
            </div>
        </div>

        <div class="meta">
            <div class="left">
                <div><label>NIK</label>: <span id="sNik">-</span></div>
                <div style="margin-top:5px;"><label>NAMA</label>: <span id="sNama">-</span></div>
            </div>
            <div class="right">
                <div><label>JABATAN</label>: <span id="sJabatan">-</span></div>
                <div style="margin-top:5px;"><label>MASA KERJA</label>: <span id="sMasaKerja">-</span> Bulan</div>
            </div>
        </div>

        <div class="box">
            <h5>RINCIAN DASAR PERHITUNGAN THR</h5>
            <table>
                <tbody>
                    <tr>
                        <td width="70%">Gaji Pokok</td>
                        <td class="right" id="sGapok">Rp 0</td>
                    </tr>
                    <tr>
                        <td>Tunjangan Daerah / Lokasi</td>
                        <td class="right" id="sLokasi">Rp 0</td>
                    </tr>
                    <tr>
                        <td>Tunjangan Hidup / Kebutuhan</td>
                        <td class="right" id="sKebutuhan">Rp 0</td>
                    </tr>
                    <tr style="background:#f9f9f9;">
                        <td class="right" style="font-weight:bold; font-size:12px;">Total Komponen Gaji:</td>
                        <td class="right" style="font-weight:bold; color:var(--accent);" id="sTotalKomponen">Rp 0</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <p class="notes" style="margin-bottom: 0;">
            <strong>Catatan Perhitungan:</strong><br>
            - Karyawan Masa Kerja &ge; 12 Bulan: <em>1 x Total Komponen Gaji</em><br>
            - Karyawan Masa Kerja &lt; 12 Bulan (Pro-rata): <em>(Masa Kerja / 12) x Total Komponen Gaji</em>
        </p>

        <div class="summary-box">
            <div class="label">NOMINAL THR DITERIMA</div>
            <div class="value" id="sNominalTHR">Rp 0</div>
        </div>

        <div class="signature">
            <div class="sig-box">
                <div style="color:var(--muted)">Diterima Oleh,</div>
                <div class="name" id="sNamaTtd">Nama Karyawan</div>
            </div>
            <div class="sig-box">
                <div style="color:var(--muted)">Disetujui Oleh,</div>
                <div class="name">HR. CFO / Manajemen</div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.search').select2();
        });

        function fmt(n) {
            if (n === null || n === undefined || n === '') return '-';
            const x = Math.round(Number(n) || 0);
            return 'Rp ' + x.toLocaleString('id-ID');
        }

        function renderDataTHR(data) {
            document.getElementById('slipTahun').textContent = data.periode;
            document.getElementById('sNik').textContent = data.nik;
            document.getElementById('sNama').textContent = data.nama;
            document.getElementById('sNamaTtd').textContent = data.nama;
            document.getElementById('sJabatan').textContent = data.jabatan;
            document.getElementById('sMasaKerja').textContent = data.masa_kerja;

            document.getElementById('sGapok').textContent = fmt(data.gaji_pokok);
            document.getElementById('sLokasi').textContent = fmt(data.tunjangan_lokasi);
            document.getElementById('sKebutuhan').textContent = fmt(data.tunjangan_kebutuhan);

            const totalKomp = Number(data.gaji_pokok) + Number(data.tunjangan_lokasi) + Number(data.tunjangan_kebutuhan);
            document.getElementById('sTotalKomponen').textContent = fmt(totalKomp);

            document.getElementById('sNominalTHR').textContent = fmt(data.nominal_thr);
        }

        async function generateSlipTHR() {
            const val = document.getElementById('karyawanSelect').value;
            const tahun = document.getElementById('tahunAktif').value;

            if (!val) {
                Swal.fire({ icon: 'warning', title: 'Perhatian', text: "Silakan pilih karyawan terlebih dahulu!" });
                return;
            }

            const id_karyawan = val.split('|')[0];

            try {
                const resp = await fetch('app/controller/get-slip/get-slip-thr.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        id_karyawan: id_karyawan,
                        tahun: tahun
                    })
                });

                if (!resp.ok) {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: "Data tidak ditemukan atau terjadi kesalahan server." });
                    return;
                }

                const data = await resp.json();
                renderDataTHR(data);
                document.getElementById('downloadBtn').disabled = false;

            } catch (e) {
                console.error(e);
                Swal.fire({ icon: 'error', title: 'Error', text: "Terjadi kesalahan sistem saat memuat slip." });
            }
        }

        function downloadPDF() {
            const slipElem = document.getElementById('slip');
            const nama = document.getElementById('sNama').textContent.trim() || 'Karyawan';
            const tahun = document.getElementById('tahunAktif').value;
            const safe = (s) => s.replace(/[\/\\:?<>|"]/g, '-');

            const filename = `Slip THR - ${safe(nama)} - ${tahun}.pdf`;

            const opt = {
                margin: 0.5,
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
                } // Ubah ke A4 Portrait
            };

            html2pdf().set(opt).from(slipElem).save();
        }

        async function downloadSlipZip() {
            const select = document.getElementById("karyawanSelect");
            const options = Array.from(select.options).filter(opt => opt.value !== "");
            const tahun = document.getElementById("tahunAktif").value;

            if (options.length === 0) {
                Swal.fire({ icon: 'warning', title: 'Perhatian', text: "Tidak ada karyawan yang menerima THR di periode ini." });
                return;
            }

            if (!confirm(`Generate dan unduh ${options.length} Slip THR dalam format ZIP?`)) return;

            const container = document.getElementById("progressContainer");
            const bar = document.getElementById("progressBar");
            const text = document.getElementById("progressText");
            const slipElem = document.getElementById("slip");

            container.style.display = "block";
            bar.style.width = "0%";
            text.innerHTML = "Menyiapkan proses...";
            slipElem.style.display = "block";

            let zip = new JSZip();
            const total = options.length;

            for (let i = 0; i < total; i++) {
                const optVals = options[i].value.split("|");
                const id_karyawan = optVals[0];
                const nik = optVals[1];
                const nama = optVals[2] || "Karyawan";

                text.innerHTML = `Memproses Slip ${i + 1} dari ${total} (${nama})...`;
                let percent = Math.round(((i + 1) / total) * 100);
                bar.style.width = percent + "%";

                try {
                    const resp = await fetch("app/controller/get-slip/get-slip-thr.php", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json"
                        },
                        body: JSON.stringify({
                            id_karyawan: id_karyawan,
                            tahun: tahun
                        })
                    });

                    if (resp.ok) {
                        const data = await resp.json();
                        renderDataTHR(data);

                        await new Promise(r => setTimeout(r, 400));

                        const safe = (s) => s.replace(/[\/\\:?<>|"]/g, "-");
                        const filename = `Slip THR - ${safe(nama)} - ${tahun}.pdf`;

                        const pdfBlob = await html2pdf()
                            .set({
                                margin: 0.5,
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
                                } // Ubah ke A4 Portrait
                            })
                            .from(slipElem)
                            .outputPdf("blob");

                        zip.file(filename, pdfBlob);
                    }
                } catch (e) {
                    console.error("Gagal cetak untuk ID:", id_karyawan, e);
                }
            }

            text.innerHTML = "Menggabungkan ke dalam file ZIP...";

            zip.generateAsync({
                type: "blob"
            }).then(function(content) {
                saveAs(content, `Slip-THR-${tahun}.zip`);
                text.innerHTML = "Selesai! ZIP berhasil di-download.";
                bar.style.width = "100%";

                setTimeout(() => {
                    container.style.display = "none";
                }, 2000);
            });
        }
    </script>
</body>

</html>