<div class="container-xxl mt-3">

    <h4 class="fw-bold mb-3">
        <span class="text-muted fw-light">Payroll /</span> Import Payroll Excel
    </h4>

    <div class="card">
        <div class="card-body">

            <form id="formImportPayroll" enctype="multipart/form-data">
                <label class="form-label">Upload File Excel Karier</label>
                <input type="file" name="file_excel" class="form-control" accept=".xls,.xlsx" required>
                <a href="assets/teamplate-csv/Format Teamplate Import Data Karier.xlsx">Download Teamplate CSV</a> <br>
                <button type="submit" class="btn btn-primary mt-3">
                    <i class="fas fa-upload"></i> Import
                </button>
            </form>

            <div id="importResult" class="mt-3"></div>

        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", () => {

        const form = document.getElementById("formImportPayroll");
        const importResult = document.getElementById("importResult");

        form.addEventListener("submit", function(e) {
            e.preventDefault();

            let fd = new FormData(this);

            importResult.innerHTML = `
                <div class="alert alert-info">
                    <i class="fas fa-spinner fa-spin"></i> Memproses file, mohon tunggu...
                </div>
            `;

            fetch("app/controller/csv-control/csv-karier.php", {
                    method: "POST",
                    body: fd
                })
                .then(res => res.text())
                .then(text => {
                    console.log("RAW RESPONSE:", text);

                    let data;
                    try {
                        data = JSON.parse(text);
                    } catch (error) {
                        showToast("Response bukan JSON!", "error");
                        return;
                    }

                    if (data.success) {
                        showToast(`Import berhasil! Data berhasil: ${data.berhasil}, Gagal: ${data.gagal}`, "success");
                    } else {
                        showToast(data.error || "Import gagal!", "error");
                    }

                    importResult.innerHTML = "";
                })
                .catch(err => {
                    console.error("FETCH ERROR:", err);
                    showToast("Request error: " + err.message, "error");
                    importResult.innerHTML = "";
                });

        });

    });
</script>