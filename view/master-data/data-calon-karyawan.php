<style>
    td.dt-control {
        cursor: pointer;
        color: #696cff; 
        transition: 0.3s;
    }

    td.dt-control:hover {
        color: #5f61e6;
        transform: scale(1.1);
    }

    .info-table {
        width: 100%;
        table-layout: fixed;
    }

    .info-table td {
        padding: 4px 8px;
        vertical-align: top;
        word-wrap: break-word;
        white-space: normal;
    }

    /* Kolom 1: Khusus Label (NIK, Email, dll) */
    .info-table td:nth-child(1) {
        width: 135px; /* Lebar teks pass tidak terlalu jauh */
        font-weight: 600;
        color: #566a7f;
    }

    /* Kolom 2: Khusus Titik Dua (:) */
    .info-table td:nth-child(2) {
        width: 20px; /* Lebar dibuat sangat kecil */
        text-align: center;
        padding-left: 0;
        padding-right: 0;
    }

    .document-list .btn {
        margin-right: 5px;
        margin-bottom: 5px;
        border-radius: 8px;
    }
</style>

<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y py-4">
        <h4 class="fw-bold mb-3">
            <span class="text-muted fw-light">Rekrutmen /</span> Calon Karyawan
        </h4>

        <div class="pb-3 d-flex justify-content-between align-items-center">
            <div>
                <a href="https://career.triputrajayasultra.com" target="_blank" class="btn btn-primary btn-sm">
                    <i class="fa fa-external-link-alt me-1"></i> Tambah Lamaran
                </a>
            </div>
        </div>

        <div class="card position-relative shadow-sm">
            <h5 class="card-header bg-white border-bottom pb-3"><i class="fa fa-users text-primary me-2"></i> Database Pelamar</h5>
            <div class="card-body mt-3">
                <div class="table-responsive text-nowrap">
                    <table id="tablePelamar" class="table table-hover table-bordered table-striped w-100 align-middle">
                        <thead class="table-primary">
                            <tr>
                                <th class="text-center" width="5%"></th>
                                <th class="text-center" width="5%">No</th>
                                <th>Informasi Pelamar</th>
                                <th class="text-center">Posisi Dilamar</th>
                                <th class="text-center">Tgl Apply</th>
                                <th class="text-center">Status</th>
                                <th class="text-center" width="15%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {

        let tablePelamar = $('#tablePelamar').DataTable({
            processing: true,
            serverSide: false,
            responsive: true,
            ajax: {
                url: "app/controller/get-data/get-calon-karyawan.php",
                type: "POST",
                dataSrc: function(json) {
                    if (json.error) {
                        console.error("SQL Error: ", json.error);
                        showToast("Kesalahan Database!", "error");
                        return [];
                    }
                    return json.data || [];
                },
                error: function(xhr, error, thrown) {
                    console.error("Gagal load data: ", xhr.responseText);
                    showToast("Koneksi ke server terputus!", "error");
                }
            },
            columns: [
                {
                    className: 'dt-control text-center align-middle',
                    orderable: false,
                    data: null,
                    defaultContent: '<i class="bx bx-chevron-down fs-4"></i>' // Icon yang lebih modern
                },
                {
                    data: "no",
                    className: "text-center align-middle fw-bold"
                },
                {
                    data: "nama",
                    className: "align-middle"
                },
                {
                    data: "posisi",
                    className: "text-center align-middle"
                },
                {
                    data: "waktu",
                    className: "text-center align-middle"
                },
                {
                    data: "status",
                    className: "text-center align-middle"
                },
                {
                    data: "action",
                    orderable: false,
                    className: "text-center align-middle"
                }
            ],
            language: {
                emptyTable: "Belum ada data pelamar yang terdaftar.",
                zeroRecords: "Pencarian tidak menemukan hasil."
            }
        });

        // Detail Baris (Expand/Collapse)
        $('#tablePelamar tbody').on('click', 'td.dt-control', function() {
            let tr = $(this).closest('tr');
            let row = tablePelamar.row(tr);
            let icon = $(this).find('i');

            if (row.child.isShown()) {
                row.child.hide();
                tr.removeClass('shown');
                icon.removeClass('bx-chevron-up').addClass('bx-chevron-down text-primary');
            } else {
                let detail = row.data().detail;
                row.child(`<div class="p-3 mb-2 bg-label-secondary border-start border-primary border-4 rounded" style="animation: fadeIn 0.3s ease-in-out;">${detail}</div>`).show();
                tr.addClass('shown');
                icon.removeClass('bx-chevron-down text-primary').addClass('bx-chevron-up text-danger');
            }
        });

        // 1. Aksi Terima (ACC)
        $(document).on("click", ".btnAccPelamar", function() {
            let id = $(this).data("id");
            let nama = $(this).data("nama");

            if (confirm("Ubah status pelamar " + nama + " menjadi DITERIMA?")) {
                $.post("app/controller/act-calon-karyawan.php", {
                    action: "update_status",
                    id_calon: id,
                    status: "Diterima"
                }, function(res) {
                    if (res.status === 'success') {
                        showToast(nama + " berhasil diterima.", "success");
                        tablePelamar.ajax.reload(null, false);
                    } else {
                        showToast("Gagal memproses data.", "error");
                    }
                }, "json");
            }
        });

        // 2. Aksi Tolak
        $(document).on("click", ".btnTolakPelamar", function() {
            let id = $(this).data("id");
            let nama = $(this).data("nama");

            if (confirm("Ubah status pelamar " + nama + " menjadi DITOLAK?")) {
                $.post("app/controller/act-calon-karyawan.php", {
                    action: "update_status",
                    id_calon: id,
                    status: "Ditolak"
                }, function(res) {
                    if (res.status === 'success') {
                        showToast("Status " + nama + " ditolak.", "warning");
                        tablePelamar.ajax.reload(null, false);
                    } else {
                        showToast("Gagal memproses data.", "error");
                    }
                }, "json");
            }
        });

        // 3. Aksi Hapus
        $(document).on("click", ".btnDeletePelamar", function() {
            let id = $(this).data("id");

            if (confirm("Hapus data pelamar secara permanen beserta berkasnya?")) {
                $.post("app/controller/act-calon-karyawan.php", {
                    action: "delete",
                    id_calon: id
                }, function(res) {
                    if (res.status === 'success') {
                        showToast("Data pelamar berhasil dihapus.", "success");
                        tablePelamar.ajax.reload(null, false);
                    } else {
                        showToast("Gagal menghapus data.", "error");
                    }
                }, "json").fail(function(xhr) {
                    showToast("Error Server: " + xhr.responseText, "error");
                });
            }
        });

    }); 
</script>