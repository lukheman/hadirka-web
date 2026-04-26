<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-bottom fw-bold">
            <i class="bx bx-plus-circle text-primary me-2"></i> Input Transaksi Pemasukan / Pengeluaran Operasional
        </div>
        <div class="card-body mt-3">
            <form action="act-simpan-transaksi.php" method="POST">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Tanggal Transaksi</label>
                        <input type="date" class="form-control" name="tanggal_transaksi" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Kategori (Dari Akun PDF)</label>
                        <select class="form-select" name="id_kategori" required>
    <option value="">-- Pilih Kategori Akun --</option>
    
    <optgroup label="PENDAPATAN (Uang Masuk)">
        <option value="36">3001 - Jasa Manpower Supply di PT.HBB</option>
        <option value="37">3002 - Jasa Manpower Supply di IPIP</option>
    </optgroup>

    <optgroup label="BIAYA VARIABEL & OPERASIONAL (Uang Keluar)">
        <option value="38">3501 - Biaya Supir</option>
        <option value="39">3502 - Biaya Bensin</option>
        <option value="40">3503 - Biaya Legalitas Perusahaan</option>
        <option value="41">3504 - Biaya APD</option>
        <option value="42">3505 - Biaya Seragam</option>
        <option value="46">4003 - Beban Sewa</option>
        <option value="47">4004 - Beban ATK</option>
        <option value="50">4007 - Beban Internet</option>
        <option value="55">4012 - Beban Listrik, Telepon dan Air</option>
        <option value="57">4014 - Beban Operasional Lain-Lain</option>
    </optgroup>
    
    <optgroup label="ASET & LAINNYA">
        <option value="12">1501 - Inventaris Kantor (Beli Aset Baru)</option>
        <option value="13">1502 - Inventaris Kendaraan (Beli Aset Baru)</option>
    </optgroup>
</select>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Tipe Arus Kas</label>
                        <select class="form-select" name="tipe_arus" required>
                            <option value="Masuk">Uang Masuk (Kas Bertambah)</option>
                            <option value="Keluar">Uang Keluar (Kas Berkurang)</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Nominal (Rp)</label>
                        <input type="number" class="form-control" name="nominal" placeholder="Contoh: 5000000" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Keterangan Tambahan</label>
                    <textarea class="form-control" name="keterangan" rows="2" placeholder="Contoh: Pembayaran Invoice INV001" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary"><i class="bx bx-save me-1"></i> Simpan Transaksi</button>
            </form>
        </div>
    </div>
</div>