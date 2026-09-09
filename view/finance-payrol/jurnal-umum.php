<?php require_once __DIR__ . '/../../app/config/koneksi.php'; ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row align-items-center mb-4">
        <div class="col-md-6">
            <h4 class="mb-1 fw-bold">Jurnal Umum</h4>
            <p class="text-muted mb-0">Catatan kronologis seluruh transaksi keuangan</p>
        </div>
        <div class="col-md-6 text-md-end">
            <select id="periodeJurnal" class="form-select d-inline-block w-auto bg-white shadow-sm" onchange="loadJurnalUmum()">
                <option value="semua">-- Semua Periode --</option>
                <?php
                $bulanIndo = array("Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember");

                $bulan_options = [];
                $tanggal_bulan = new DateTime('first day of this month');
                for ($i = 0; $i < 12; $i++) {
                    $value = $tanggal_bulan->format('Y-m');
                    $bulan_options[$value] = $bulanIndo[(int)$tanggal_bulan->format('m') - 1]
                        . ' ' . $tanggal_bulan->format('Y');
                    $tanggal_bulan->modify('-1 month');
                }
                foreach ($bulan_options as $value => $label) {
                    $selected = array_key_first($bulan_options) === $value ? ' selected' : '';
                    echo '<option value="' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '"' . $selected . '>'
                        . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</option>';
                }
                ?>
            </select>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle w-100" id="tableJurnal">
                    <thead class="table-light">
                        <tr>
                            <th width="12%">Tanggal</th>
                            <th width="35%">Keterangan / Akun</th>
                            <th width="13%">Ref (COA)</th>
                            <th width="20%" class="text-end">Debit</th>
                            <th width="20%" class="text-end">Kredit</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyJurnal"></tbody>
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td colspan="3" class="text-end">TOTAL JUMLAH</td>
                            <td id="totalDebit" class="text-end text-primary">Rp 0</td>
                            <td id="totalKredit" class="text-end text-primary">Rp 0</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

<script>
    async function loadJurnalUmum() {
        const idPeriode = document.getElementById('periodeJurnal').value;
        if ($.fn.DataTable.isDataTable('#tableJurnal')) {
            $('#tableJurnal').DataTable().destroy();
        }

        const tbody = document.getElementById('tbodyJurnal');
        tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4"><div class="spinner-border text-primary"></div></td></tr>';

        try {
            const res = await fetch(`app/controller/get-data/get-jurnal-umum.php?id_priode=${idPeriode}`);
            const result = await res.json();

            if (result.status === 'success') {
                tbody.innerHTML = '';
                let tDebit = 0,
                    tKredit = 0;

                result.data.forEach(row => {
                    tDebit += row.debit_raw;
                    tKredit += row.kredit_raw;

                    // Estetika Akun: Jika posisi Kredit, teks akun agak menjorok ke dalam (indent)
                    const paddingClass = row.tipe_neraca === 'kredit' ? 'ps-4 text-muted' : 'fw-semibold text-dark';

                    tbody.innerHTML += `
                        <tr>
                            <td>${row.tanggal}</td>
                            <td>
                                <div class="${paddingClass}">${row.nama_akun}</div>
                                <small class="text-muted d-block ps-2">Ket: ${row.keterangan}</small>
                            </td>
                            <td><code>${row.kode_akun}</code></td>
                            <td class="text-end">${row.debit}</td>
                            <td class="text-end">${row.kredit}</td>
                        </tr>
                    `;
                });

                const formatRp = (num) => 'Rp ' + new Intl.NumberFormat('id-ID').format(num);
                document.getElementById('totalDebit').innerText = formatRp(tDebit);
                document.getElementById('totalKredit').innerText = formatRp(tKredit);

                $('#tableJurnal').DataTable({
                    pageLength: 25,
                    ordering: false,
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json'
                    }
                });
            }
        } catch (e) {
            showToast('Gagal memuat Jurnal Umum', 'error');
        }
    }
    document.addEventListener("DOMContentLoaded", loadJurnalUmum);
</script>
