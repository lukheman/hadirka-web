<?php
// Fragment: form + tabel Tunjangan per Karier.
// Di-include di dalam tab Tunjangan pada modal Edit Karier (data-karier.php & import-karier.php).
// ID elemen dipertahankan agar JS di modal-karier-tunjangan-potongan.php tetap jalan.
?>
<h6 class="fw-bold"><i class="fa fa-money-bill"></i> Tunjangan - <span id="labelTunjanganNama">-</span></h6>
<form id="formModalTunjangan" class="row g-2 align-items-end">
    <input type="hidden" name="id_tunjangan" id="modalTunjanganId">
    <input type="hidden" name="id_karier" id="modalTunjanganKarierEnc">
    <div class="col-md-6">
        <label class="form-label">Nama Tunjangan</label>
        <select name="nama_tunjangan" id="modalTunjanganNama" class="form-select" required>
            <option value="">-- Pilih Tunjangan --</option>
            <option value="Tunjangan Daerah">Tunjangan Daerah</option>
            <option value="Tunjangan Hidup">Tunjangan Hidup</option>
            <option value="Tunjangan Makan">Tunjangan Makan</option>
            <option value="Tunjangan Skill">Tunjangan Skill</option>
            <option value="Bonus Kehadiran">Bonus Kehadiran</option>
            <option value="Tunjangan Posisi">Tunjangan Posisi</option>
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label">Jumlah (Rp)</label>
        <input type="number" name="jumlah_tunjangan" id="modalTunjanganJumlah" class="form-control" placeholder="0" required>
    </div>
    <div class="col-md-3 d-flex gap-1">
        <button type="submit" class="btn btn-success" id="btnModalTunjanganSubmit">
            <i class="fa fa-save"></i> Simpan
        </button>
        <button type="button" class="btn btn-secondary d-none" id="btnModalTunjanganCancel">Batal</button>
    </div>
</form>
<hr>
<div class="table-responsive">
    <table class="table table-bordered table-sm w-100">
        <thead class="table-info">
            <tr>
                <th class="text-center" style="width:5%">No</th>
                <th class="text-center">Nama Tunjangan</th>
                <th class="text-center">Jumlah (Rp)</th>
                <th class="text-center" style="width:15%">Aksi</th>
            </tr>
        </thead>
        <tbody id="tbodyModalTunjangan"></tbody>
    </table>
</div>
