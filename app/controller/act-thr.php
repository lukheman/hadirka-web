<?php
/**
 * ============================================
 * THR MANAGEMENT - ACTION CONTROLLER
 * ============================================
 * File: act-thr.php
 * Purpose: Handle CRUD operations for thr data
 * Actions: add, edit, delete
 */

// Include database connection
require_once __DIR__ . '/../config/koneksi.php';

// Set JSON response header
header('Content-Type: application/json; charset=utf-8');

// Disable HTML error display (prevent JSON corruption)
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Log errors to file instead
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/thr_errors.log');

// ============================================
// FUNCTION: Send JSON Response
// ============================================
function sendResponse($status, $message, $data = null) {
    $response = [
        'status' => $status,
        'msg' => $message,
        'timestamp' => date('Y-m-d H:i:s')
    ];

    if ($data !== null) {
        $response['data'] = $data;
    }

    echo json_encode($response);
    exit;
}

// ============================================
// CHECK DATABASE CONNECTION
// ============================================
if (!isset($koneksi) || !$koneksi) {
    sendResponse('error', 'Koneksi database gagal. Silakan hubungi administrator.');
}

// ============================================
// GET ACTION FROM POST
// ============================================
$action = isset($_POST['action']) ? trim($_POST['action']) : '';

if (empty($action)) {
    sendResponse('error', 'Parameter action tidak ditemukan');
}

// ============================================
// PROCESS ACTIONS
// ============================================
switch ($action) {

    // ========================================
    // ACTION: ADD NEW THR
    // ========================================
    case 'add':
        $id_karyawan = intval($_POST['id_karyawan']);
        $tgl_mulai = trim($_POST['tgl_mulai']);
        $tgl_hari_raya = trim($_POST['tgl_hari_raya']);
        $gaji_pokok = trim($_POST['gaji_pokok']);
        $tunj_lokasi = trim($_POST['tunjangan_lokasi']);
        $tunj_kebutuhan = trim($_POST['tunjangan_kebutuhan']);
        $masa_kerja = trim($_POST['masa_kerja']);
        $nominal_thr = trim($_POST['nominal_thr']);

        // Ekstrak periode
        $periode = date('Y', strtotime($tgl_hari_raya));

        try {
            // Cek duplikat (Tahun dan Karyawan)
            $dupStmt = $koneksi->prepare("SELECT id_thr FROM tb_thr WHERE id_karyawan = ? AND periode = ?");
            $dupStmt->execute([$id_karyawan, $periode]);
            if ($dupStmt->fetch()) {
                sendResponse('error', 'Karyawan ini sudah memiliki data THR untuk periode tahun ' . $periode);
            }

            $stmt = $koneksi->prepare("
                INSERT INTO tb_thr (id_karyawan, periode, tgl_mulai, tgl_hari_raya, gaji_pokok, tunjangan_lokasi, tunjangan_kebutuhan, masa_kerja, nominal_thr)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            if ($stmt->execute([$id_karyawan, $periode, $tgl_mulai, $tgl_hari_raya, $gaji_pokok, $tunj_lokasi, $tunj_kebutuhan, $masa_kerja, $nominal_thr])) {
                sendResponse('success', 'Data THR berhasil ditambahkan');
            } else {
                sendResponse('error', 'Gagal menyimpan data.');
            }
        } catch (PDOException $e) {
            error_log($e->getMessage());
            sendResponse('error', 'Gagal menyimpan data.');
        }
        break;

    // ========================================
    // ACTION: EDIT EXISTING thr
    // ========================================
    case 'edit':
        $id_thr = intval($_POST['id_thr']);
        $id_karyawan = intval($_POST['id_karyawan']);
        $tgl_mulai = trim($_POST['tgl_mulai']);
        $tgl_hari_raya = trim($_POST['tgl_hari_raya']);
        $gaji_pokok = trim($_POST['gaji_pokok']);
        $tunj_lokasi = trim($_POST['tunjangan_lokasi']);
        $tunj_kebutuhan = trim($_POST['tunjangan_kebutuhan']);
        $masa_kerja = trim($_POST['masa_kerja']);
        $nominal_thr = trim($_POST['nominal_thr']);

        $periode = date('Y', strtotime($tgl_hari_raya));

        try {
            // --- TAMBAHKAN PENGECEKAN DUPLIKAT DI SINI ---
            // Cek apakah ada data di tahun yang sama, KECUALI data yang sedang diedit ini
            $dupStmt = $koneksi->prepare("SELECT id_thr FROM tb_thr WHERE id_karyawan = ? AND periode = ? AND id_thr != ?");
            $dupStmt->execute([$id_karyawan, $periode, $id_thr]);
            if ($dupStmt->fetch()) {
                sendResponse('error', 'Gagal update. Karyawan ini sudah memiliki data THR lain untuk periode tahun ' . $periode);
            }
            // ---------------------------------------------

            $stmt = $koneksi->prepare("
                UPDATE tb_thr SET
                id_karyawan=?, periode=?, tgl_mulai=?, tgl_hari_raya=?, gaji_pokok=?, tunjangan_lokasi=?, tunjangan_kebutuhan=?, masa_kerja=?, nominal_thr=?
                WHERE id_thr=?
            ");

            if ($stmt->execute([$id_karyawan, $periode, $tgl_mulai, $tgl_hari_raya, $gaji_pokok, $tunj_lokasi, $tunj_kebutuhan, $masa_kerja, $nominal_thr, $id_thr])) {
                sendResponse('success', 'Data THR berhasil diupdate');
            } else {
                sendResponse('error', 'Gagal memperbarui data.');
            }
        } catch (PDOException $e) {
            error_log($e->getMessage());
            sendResponse('error', 'Gagal memperbarui data.');
        }
        break;

    // ========================================
    // ACTION: DELETE thr
    // ========================================
    case 'delete':
        // Sanitize and validate input
        $id_thr = isset($_POST['id_thr']) ? intval($_POST['id_thr']) : 0;

        // Validation
        if ($id_thr <= 0) {
            sendResponse('error', 'ID thr tidak valid');
        }

        try {
            // Check if thr exists
            $checkStmt = $koneksi->prepare("SELECT id_thr FROM tb_thr WHERE id_thr = ?");
            $checkStmt->execute([$id_thr]);

            if (!$checkStmt->fetch()) {
                sendResponse('error', 'Data thr tidak ditemukan');
            }

            // Prepare DELETE statement
            $stmt = $koneksi->prepare("DELETE FROM tb_thr WHERE id_thr = ?");

            // Execute query
            if ($stmt->execute([$id_thr])) {
                $affected_rows = $stmt->rowCount();

                if ($affected_rows > 0) {
                    sendResponse('success', 'Data thr berhasil dihapus');
                } else {
                    sendResponse('error', 'Data thr tidak ditemukan atau sudah dihapus');
                }
            } else {
                sendResponse('error', 'Gagal menghapus data.');
            }
        } catch (PDOException $e) {
            error_log($e->getMessage());
            sendResponse('error', 'Gagal menghapus data.');
        }
        break;

    // ========================================
    // ACTION: INVALID
    // ========================================
    default:
        sendResponse('error', 'Aksi tidak valid: ' . htmlspecialchars($action));
        break;
}
?>
