<?php
// SHIM kompatibilitas: perhitungan slip gaji sudah pindah ke helper
// app/helpers/slip_helper.php (DRY, reusable).
// File ini dipertahankan agar semua require lama tetap jalan tanpa diubah:
//   - app/controller/get-slip/get-slip-gaji.php
//   - app/controller/get-data/get-payrol.php
//   - app/controller/get-data/get-home.php
//   - app/controller/get-data/get-pengajuan-pinjaman.php
//   - app/controller/act-posting-payroll.php
// Kode baru disarankan require helper langsung:
//   require_once __DIR__ . '/../helpers/slip_helper.php'; // dari app/api/
//   require_once __DIR__ . '/../../helpers/slip_helper.php'; // dari app/controller/*/

require_once __DIR__ . '/../helpers/slip_helper.php';
