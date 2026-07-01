<?php require_once __DIR__ . '/../../app/config/koneksi.php'; ?>
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row align-items-center mb-4">
        <div class="col-md-6">
            <h4 class="mb-1 fw-bold">Laporan Neraca Saldo</h4>
            <p class="text-muted mb-0">Ikhtisar saldo akhir seluruh rekening buku besar</p>
        </div>
        <div class="col-md-6 text-md-end">
            <select id="periodeNeraca" class="form-select d-inline-block w-auto bg-white shadow-sm" onchange="loadNeracaSaldo()">
                <option value="semua">-- S.d Periode Saat Ini --</option>
                <?php
                $bulanIndo = array("Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember");

                for ($i = 0; $i < 12; $i++) {
                    $value = date('Y-m', strtotime("-$i months"));
                    $bulanIdx = (int)date('m', strtotime("-$i months")) - 1;
                    $tahun = date('Y', strtotime("-$i months"));

                    $label = $bulanIndo[$bulanIdx] . " " . $tahun;
                    $selected = ($i == 0) ? "selected" : "";

                    echo "<option value='{$value}' $selected>{$label}</option>";
                }
                ?>
            </select>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div id="alertBalance" class="alert d-none mb-3" role="alert"></div>

            <table class="table table-bordered table-striped align-middle w-100">
                <thead class="table-light text-center">
                    <tr>
                        <th width="15%">Kode Akun</th>
                        <th width="45%" class="text-start">Nama Akun Akuntansi</th>
                        <th width="20%" class="text-end">Debit</th>
                        <th width="20%" class="text-end">Kredit</th>
                    </tr>
                </thead>
                <tbody id="tbodyNeraca"></tbody>
                <tfoot class="table-dark fw-bold text-end">
                    <tr>
                        <td colspan="2">TOTAL KESELURUHAN</td>
                        <td id="nsTotalDebit">Rp 0</td>
                        <td id="nsTotalKredit">Rp 0</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<script>
    async function loadNeracaSaldo() {
        const idPeriode = document.getElementById('periodeNeraca').value;
        const tbody = document.getElementById('tbodyNeraca');
        tbody.innerHTML = '<tr><td colspan="4" class="text-center py-4"><div class="spinner-border text-primary"></div></td></tr>';

        try {
            const res = await fetch(`app/controller/get-data/get-neraca-saldo.php?id_priode=${idPeriode}`);
            const result = await res.json();

            if (result.status === 'success') {
                tbody.innerHTML = '';
                let totalD = 0,
                    totalK = 0;

                result.data.forEach(row => {
                    totalD += row.debit;
                    totalK += row.kredit;

                    if (row.debit > 0 || row.kredit > 0) { // Hanya tampilkan akun ber-saldo
                        tbody.innerHTML += `
                            <tr>
                                <td class="text-center"><code>${row.kode_akun}</code></td>
                                <td>${row.nama_akun}</td>
                                <td class="text-end">${row.debit > 0 ? 'Rp ' + row.debit.toLocaleString('id-ID') : '-'}</td>
                                <td class="text-end">${row.kredit > 0 ? 'Rp ' + row.kredit.toLocaleString('id-ID') : '-'}</td>
                            </tr>
                        `;
                    }
                });

                document.getElementById('nsTotalDebit').innerText = 'Rp ' + totalD.toLocaleString('id-ID');
                document.getElementById('nsTotalKredit').innerText = 'Rp ' + totalK.toLocaleString('id-ID');

                // Validasi Indikator Balancing Akuntansi
                const alertEl = document.getElementById('alertBalance');
                alertEl.classList.remove('d-none', 'alert-success', 'alert-danger');
                if (totalD === totalK) {
                    alertEl.classList.add('alert-success');
                    alertEl.innerHTML = '<i class="bx bx-check-circle me-1"></i> <strong>Sempurna!</strong> Neraca Saldo berstatus seimbang (Balanced).';
                } else {
                    alertEl.classList.add('alert-danger');
                    alertEl.innerHTML = `<i class="bx bx-error-circle me-1"></i> <strong>Peringatan!</strong> Terjadi selisih sebesar Rp ${Math.abs(totalD - totalK).toLocaleString('id-ID')}. Periksa kembali input Jurnal Umum Anda.`;
                }
            }
        } catch (e) {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center text-danger">Gagal memuat neraca saldo.</td></tr>';
        }
    }
    document.addEventListener("DOMContentLoaded", loadNeracaSaldo);
</script>