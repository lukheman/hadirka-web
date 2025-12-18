$(document).ready(function () {
    let table;

    // LOAD DATATABLE
    function loadTable() {
        table = $('#tableKaryawan').DataTable({
            processing: true,
            serverSide: false,
            destroy: true,
            ajax: {
                url: "ajax/get-karyawan.php",
                type: "POST",
                data: function (d) {
                    d.site = $("#filterSite").val();
                    d.jabatan = $("#filterJabatan").val();
                },
                beforeSend: function () {
                    $("#loadingTable").show();
                },
                complete: function () {
                    $("#loadingTable").hide();
                }
            },
            columns: [
                {
                    className: "dt-control",
                    orderable: false,
                    data: null,
                    defaultContent: '<i class="fa fa-plus text-primary"></i>',
                    width: "20px"
                },
                { data: "no" },
                { data: "nama" },
                { data: "nik" },
                { data: "jabatan" },
                { data: "action", orderable: false }
            ],
            createdRow: function (row, data) {
                if (data.rowClass !== "") {
                    $(row).addClass(data.rowClass);
                }
            }
        });
    }

    loadTable();

    // REFRESH ketika filter dipilih
    $("#filterSite, #filterJabatan").change(function () {
        table.ajax.reload();
    });

    // EXPAND CHILD ROW
    $('#tableKaryawan tbody').on('click', 'td.dt-control', function () {
        let tr = $(this).closest('tr');
        let row = table.row(tr);

        if (row.child.isShown()) {
            row.child.hide();
            tr.removeClass('shown');
            $(this).html('<i class="fa fa-plus text-primary"></i>');
        } else {
            row.child(row.data().detail).show();
            tr.addClass('shown');
            $(this).html('<i class="fa fa-minus text-danger"></i>');
        }
    });

    // OPEN MODAL EDIT
    $(document).on("click", ".btnEdit", function () {
        let id = $(this).data("id");

        $.ajax({
            url: "ajax/get-single-karyawan.php",
            type: "POST",
            data: { id: id },
            dataType: "json",
            success: function (r) {
                if (r.status === "ok") {
                    $("#edit_id").val(r.data.id_karyawan);
                    $("#edit_nama").val(r.data.nama_lengkap);
                    $("#edit_jenis_kelamin").val(r.data.jenis_kelamin);
                    $("#edit_tgl_lahir").val(r.data.tgl_lahir);
                    $("#edit_no_telp").val(r.data.no_telp);
                    $("#modalEditKaryawan").modal("show");
                } else {
                    Swal.fire("Error", r.message, "error");
                }
            }
        });
    });

    // SAVE UPDATE
    $("#formEditKaryawan").submit(function (e) {
        e.preventDefault();

        $.ajax({
            url: "ajax/act-karyawan.php",
            type: "POST",
            data: $(this).serialize() + "&action=update",
            dataType: "json",
            success: function (res) {
                if (res.status === "ok") {
                    Swal.fire("Success", res.message, "success");
                    $("#modalEditKaryawan").modal("hide");
                    table.ajax.reload();
                } else {
                    Swal.fire("Error", res.message, "error");
                }
            }
        });
    });

    // DELETE
    $(document).on("click", ".btnDelete", function () {
        let id = $(this).data("id");

        Swal.fire({
            title: "Hapus data?",
            text: "Data tidak dapat dikembalikan!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Ya, hapus",
            cancelButtonText: "Batal"
        }).then((res) => {
            if (res.isConfirmed) {
                $.ajax({
                    url: "ajax/act-karyawan.php",
                    type: "POST",
                    data: { id: id, action: "delete" },
                    dataType: "json",
                    success: function (r) {
                        if (r.status === "ok") {
                            Swal.fire("Deleted", r.message, "success");
                            table.ajax.reload();
                        } else {
                            Swal.fire("Error", r.message, "error");
                        }
                    }
                });
            }
        });
    });

    // ADD KARYAWAN
    $("#formAddKaryawan").submit(function (e) {
        e.preventDefault();

        $.ajax({
            url: "ajax/act-karyawan.php",
            type: "POST",
            data: $(this).serialize() + "&action=add",
            dataType: "json",
            success: function (res) {
                if (res.status === "ok") {
                    Swal.fire("Success", res.message, "success");
                    $("#formAddKaryawan")[0].reset();
                    $("#modalAddKaryawan").modal("hide");
                    table.ajax.reload();
                } else {
                    Swal.fire("Error", res.message, "error");
                }
            }
        });
    });
});
