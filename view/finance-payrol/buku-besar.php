<?php require_once __DIR__ . '/../../app/config/koneksi.php'; ?>
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-center">
                <div class="col-md-4">
                    <h5 class="fw-bold mb-0">Buku Besar Per Akun</h5>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Pilih Akun (COA)</label>
                    <select id="bbAkun" class="form-select form-select-sm" onchange="loadBukuBesar()">
                        <?php
                        $q = mysqli_query($koneksi, "SELECT * FROM tb_kategori_keuangan ORDER BY kode_akun ASC");
                        while ($r = mysqli_fetch_assoc($q)) {
                            echo "<option value='{$r['id_kategori']}'>[{$r['kode_akun']}] - {$r['nama_akun']}</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Periode</label>
                    <select id="bbPeriode" class="form-select form-select-sm" onchange="loadBukuBesar()">
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
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <table class="table table-bordered align-middle w-100">
                <thead class="table-light">
                    <tr>
                        <th width="12%">Tanggal</th>
                        <th width="38%">Keterangan Transaksi</th>
                        <th width="15%" class="text-end">Debit</th>
                        <th width="15%" class="text-end">Kredit</th>
                        <th width="20%" class="text-end">Saldo Akhir</th>
                    </tr>
                </thead>
                <tbody id="tbodyBukuBesar"></tbody>
            </table>
        </div>
    </div>
</div>

<script>
    async function loadBukuBesar() {
        const idKategori = document.getElementById('bbAkun').value;
        const idPeriode = document.getElementById('bbPeriode').value;
        const tbody = document.getElementById('tbodyBukuBesar');

        if (!idKategori) return;
        tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4"><div class="spinner-border text-primary"></div></td></tr>';

        try {
            const res = await fetch(`app/controller/get-data/get-buku-besar.php?id_kategori=${idKategori}&id_priode=${idPeriode}`);
            const result = await res.json();

            if (result.status === 'success') {
                tbody.innerHTML = '';
                let runningBalance = 0;
                const kelompok = result.kelompok;

                // Aturan Saldo Normal Akuntansi
                // Aset & Beban bertambah di Debit (+)
                // Kewajiban, Ekuitas, Pendapatan bertambah di Kredit (+)
                const isNormalDebit = (kelompok === 'Aset' || kelompok === 'Beban');

                if (result.data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-3">Tidak ada data mutasi pada periode ini.</td></tr>';
                    return;
                }

                const formatRp = (num) => 'Rp ' + new Intl.NumberFormat('id-ID').format(num);

                result.data.forEach(row => {
                    if (row.tipe_neraca === 'debit') {
                        runningBalance += isNormalDebit ? row.nominal_raw : -row.nominal_raw;
                    } else {
                        runningBalance += isNormalDebit ? -row.nominal_raw : row.nominal_raw;
                    }

                    tbody.innerHTML += `
                        <tr>
                            <td>${row.tanggal}</td>
                            <td>${row.keterangan}</td>
                            <td class="text-end text-success">${row.tipe_neraca === 'debit' ? formatRp(row.nominal_raw) : '-'}</td>
                            <td class="text-end text-danger">${row.tipe_neraca === 'kredit' ? formatRp(row.nominal_raw) : '-'}</td>
                            <td class="text-end fw-bold">${formatRp(runningBalance)}</td>
                        </tr>
                    `;
                });
            }
        } catch (e) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center text-danger">Gagal memuat data buku besar.</td></tr>';
        }
    }
    document.addEventListener("DOMContentLoaded", loadBukuBesar);
</script>
