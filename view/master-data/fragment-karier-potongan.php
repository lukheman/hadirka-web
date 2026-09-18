<?php
// Fragment: form + tabel Potongan per Karier.
// Di-include di dalam tab Potongan pada modal Edit Karier (data-karier.php & import-karier.php).
// ID elemen dipertahankan agar JS di modal-karier-tunjangan-potongan.php tetap jalan.
?>
<h6 class="fw-bold"><i class="fa fa-scissors"></i> Potongan - <span id="labelPotonganNama">-</span></h6>
<form id="formModalPotongan" class="row g-2 align-items-end">
    <input type="hidden" name="id_potongan" id="modalPotonganId">
    <input type="hidden" name="id_karier" id="modalPotonganKarierEnc">
    <div class="col-md-6">
        <label class="form-label">Nama Potongan</label>
        <input type="text" name="jenis_potongan" id="modalPotonganNama" class="form-control" placeholder="Contoh: BPJS" required>
    </div>
    <div class="col-md-3">
        <label class="form-label">Jumlah (Rp)</label>
        <input type="number" name="jumlah_potongan" id="modalPotonganJumlah" class="form-control" placeholder="0" required>
    </div>
    <div class="col-md-3 d-flex gap-1">
        <button type="submit" class="btn btn-success" id="btnModalPotonganSubmit">
            <i class="fa fa-save"></i> Simpan
        </button>
        <button type="button" class="btn btn-secondary d-none" id="btnModalPotonganCancel">Batal</button>
    </div>
</form>
<hr>
<div class="table-responsive">
    <table class="table table-bordered table-sm w-100">
        <thead class="table-info">
            <tr>
                <th class="text-center" style="width:5%">No</th>
                <th class="text-center">Nama Potongan</th>
                <th class="text-center">Jumlah (Rp)</th>
                <th class="text-center" style="width:15%">Aksi</th>
            </tr>
        </thead>
        <tbody id="tbodyModalPotongan"></tbody>
    </table>
</div>
