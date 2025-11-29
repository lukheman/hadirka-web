<?php
require_once '../../config/koneksi.php';
require_once '../../config/function.php';
require_once '../../config/function-slip.php';

// ================= Panggil function =================
$input = json_decode(file_get_contents('php://input'), true);
$nik = $input['nik'] ?? null;
$startDate = $input['startDate'] ?? null;
$endDate = $input['endDate'] ?? null;

if (!$nik || !$startDate || !$endDate) {
    http_response_code(400);
    echo json_encode(['error' => 'nik, startDate, endDate wajib']);
    exit;
}

$response = generateSlipGaji($nik, $startDate, $endDate);
echo json_encode($response, JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);
?>