<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y py-4">
        <h4 class="fw-bold mb-3">
            <span class="text-muted fw-light">Rekrutmen /</span> Data Loker
        </h4>

        <div class="pb-3 d-flex justify-content-between">
            <div>
                <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambahLoker">
                    <i class="fa fa-plus"></i> Tambah Loker
                </button>
            </div>
        </div>

        <div class="card">
            <h5 class="card-header"><i class="fa fa-briefcase"></i> Data Lowongan Pekerjaan</h5>
            <div class="card-body">
                <div class="table-responsive text-nowrap">
                    <table class="table table-bordered table-hover MyTableLoker">
                        <thead class="table-info">
                            <tr>
                                <th class="text-center" width="5%">No</th>
                                <th>Posisi / Jabatan</th>
                                <th class="text-center">Status</th>
                                <th class="text-center" width="20%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyLoker"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalTambahLoker" tabindex="-1">
        <div class="modal-dialog">
            <form id="formTambahLoker" class="modal-content" method="POST">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title text-white">Tambah Loker Baru</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label>Posisi Jabatan</label>
                    <select name="id_jabatan" class="form-select" required>
                        <option value="">-- Pilih Jabatan --</option>
                        <?php 
                        foreach (data_jabatan() as $j): ?>
                            <option value="<?= $j['id_jabatan'] ?>"><?= $j['nama_jabatan'] ?></option>
                        <?php endforeach; ?>
                    </select>

                    <label class="mt-3">Status Loker</label>
                    <select name="status_loker" class="form-select" required>
                        <option value="Aktif">Aktif</option>
                        <option value="Nonaktif">Nonaktif</option>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="modalEditLoker" tabindex="-1">
        <div class="modal-dialog">
            <form id="formEditLoker" class="modal-content" method="POST">
                <input type="hidden" name="id_loker" id="edit_id_loker">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white">Edit Loker</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label>Posisi Jabatan</label>
                    <select name="id_jabatan" id="edit_id_jabatan" class="form-select" required>
                        <option value="">-- Pilih Jabatan --</option>
                        <?php foreach (data_jabatan() as $j): ?>
                            <option value="<?= $j['id_jabatan'] ?>"><?= $j['nama_jabatan'] ?></option>
                        <?php endforeach; ?>
                    </select>

                    <label class="mt-3">Status Loker</label>
                    <select name="status_loker" id="edit_status_loker" class="form-select" required>
                        <option value="Aktif">Aktif</option>
                        <option value="Nonaktif">Nonaktif</option>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Edit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- jQuery global via CDN di <head> index.php -->
<script>
$(document).ready(function() {
    
    // Fungsi Render DataTable
    function loadLoker() {
        $.ajax({
            url: "app/controller/get-data/get-loker.php",
            type: "GET",
            success: function(html) {
                if ($.fn.DataTable.isDataTable(".MyTableLoker")) {
                    $(".MyTableLoker").DataTable().destroy();
                }
                $("#tbodyLoker").html(html);
                $(".MyTableLoker").DataTable({ responsive: true });
            },
            error: function(err) {
                console.error("Gagal load tabel:", err.responseText);
            }
        });
    }

    // [CREATE] Tambah Data
    $("#formTambahLoker").on("submit", function(e) {
        e.preventDefault();
        let fd = new FormData(this);
        fd.append("action", "add");
        
        $.ajax({
            url: "app/controller/act-loker.php",
            type: "POST",
            data: fd,
            processData: false,
            contentType: false,
            dataType: "json",
            success: function(res) {
                if (res.status === "success") {
                    showToast("Loker berhasil ditambah!", "success");
                    $("#modalTambahLoker").modal("hide");
                    $("#formTambahLoker")[0].reset();
                    loadLoker();
                } else {
                    showToast(res.msg || "Terjadi kesalahan sistem", "error");
                }
            },
            error: function(xhr) {
                showToast("Error Server: " + xhr.responseText, "error");
            }
        });
    });

    // [UPDATE] Edit Data
    $("#formEditLoker").on("submit", function(e) {
        e.preventDefault();
        let fd = new FormData(this);
        fd.append("action", "edit");

        $.ajax({
            url: "app/controller/act-loker.php",
            type: "POST",
            data: fd,
            processData: false,
            contentType: false,
            dataType: "json",
            success: function(res) {
                if (res.status === "success") {
                    showToast("Loker berhasil diupdate!", "success");
                    $("#modalEditLoker").modal("hide");
                    loadLoker();
                } else {
                    showToast(res.msg || "Terjadi kesalahan sistem", "error");
                }
            },
            error: function(xhr) {
                showToast("Error Server: " + xhr.responseText, "error");
            }
        });
    });

    // [MODAL TRIGGER] Tampilkan Modal Edit beserta Datanya
    $(document).on("click", ".btnEditLoker", function() {
        let id = $(this).data("id");
        let jabatan = $(this).data("jabatan");
        let status = $(this).data("status");
        
        $("#edit_id_loker").val(id);
        $("#edit_id_jabatan").val(jabatan);
        $("#edit_status_loker").val(status);
        
        $("#modalEditLoker").modal("show");
    });

    // [DELETE] Hapus Loker
    $(document).on("click", ".btnDeleteLoker", async function() {
        let id = $(this).data("id");

        if (await confirmDialog("Yakin hapus loker? Data yang terhapus tidak dapat dikembalikan.")) {
            $.ajax({
                url: "app/controller/act-loker.php",
                type: "POST",
                data: { action: "delete", id_loker: id },
                dataType: "json",
                success: function(res) {
                    if (res.status === "success") {
                        showToast("Data loker berhasil dihapus.", "success");
                        loadLoker();
                    } else {
                        showToast(res.msg || "Gagal menghapus data", "error");
                    }
                },
                error: function(xhr) {
                    showToast("Error Server: " + xhr.responseText, "error");
                }
            });
        }
    });

    // Load data pertama kali saat halaman dibuka
    loadLoker();
});
</script>