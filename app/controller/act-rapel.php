<?php

/**
 * ============================================
 * RAPEL MANAGEMENT - ACTION CONTROLLER
 * ============================================
 * File: act-rapel.php
 * Purpose: Handle CRUD operations for rapel data
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
ini_set('error_log', __DIR__ . '/../logs/rapel_errors.log');

// ============================================
// FUNCTION: Send JSON Response
// ============================================
function sendResponse($status, $message, $data = null)
{
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

// Check if connection is active
if ($koneksi->connect_error) {
    sendResponse('error', 'Database connection error: ' . $koneksi->connect_error);
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
    // ACTION: ADD NEW RAPEL
    // ========================================
    case 'add':
        // Sanitize and validate input
        $id_karyawan = isset($_POST['id_karyawan']) ? intval($_POST['id_karyawan']) : 0;
        $tgl_rapel = isset($_POST['tgl_rapel']) ? trim($_POST['tgl_rapel']) : '';
        $nominal_rapel = isset($_POST['nominal_rapel']) ? floatval(str_replace('.', '', $_POST['nominal_rapel'])) : 0;

        // Ambil keterangan dan bersihkan spasi gandanya (Normalisasi)
        $ket_rapel = isset($_POST['ket_rapel']) ? trim($_POST['ket_rapel']) : '';
        $ket_rapel = preg_replace('/\s+/', ' ', $ket_rapel);

        // Validation
        $errors = [];

        if ($id_karyawan <= 0) {
            $errors[] = 'Karyawan harus dipilih';
        }

        if (empty($tgl_rapel)) {
            $errors[] = 'Tanggal rapel harus diisi';
        } else {
            // Validate date format
            $date = DateTime::createFromFormat('Y-m-d', $tgl_rapel);
            if (!$date || $date->format('Y-m-d') !== $tgl_rapel) {
                $errors[] = 'Format tanggal tidak valid';
            }
        }

        if ($nominal_rapel <= 0) {
            $errors[] = 'Nominal rapel harus lebih dari 0';
        }

        if ($nominal_rapel > 999999999) {
            $errors[] = 'Nominal rapel terlalu besar';
        }

        // If there are validation errors
        if (!empty($errors)) {
            sendResponse('error', implode(', ', $errors));
        }

        // Check if employee exists
        $checkStmt = $koneksi->prepare("SELECT id_karyawan FROM tb_karyawan WHERE id_karyawan = ?");
        $checkStmt->bind_param("i", $id_karyawan);
        $checkStmt->execute();
        $checkStmt->store_result();

        if ($checkStmt->num_rows === 0) {
            $checkStmt->close();
            sendResponse('error', 'Data karyawan tidak ditemukan');
        }
        $checkStmt->close();

        // PERUBAHAN: Cek duplikat data dengan menyertakan KETERANGAN
        $dupStmt = $koneksi->prepare("SELECT id_rapel FROM tb_rapel WHERE id_karyawan = ? AND tgl_rapel = ? AND ket_rapel = ?");
        $dupStmt->bind_param("iss", $id_karyawan, $tgl_rapel, $ket_rapel);
        $dupStmt->execute();
        $dupStmt->store_result();

        if ($dupStmt->num_rows > 0) {
            $dupStmt->close();
            sendResponse('error', 'Data rapel untuk karyawan ini dengan tanggal dan keterangan yang sama persis sudah ada!');
        }
        $dupStmt->close();

        // Prepare INSERT statement
        $stmt = $koneksi->prepare("INSERT INTO tb_rapel (id_karyawan, tgl_rapel, nominal_rapel, ket_rapel) VALUES (?, ?, ?, ?)");

        if (!$stmt) {
            sendResponse('error', 'Gagal menyiapkan query: ' . $koneksi->error);
        }

        // Bind parameters (i=integer, s=string, d=double/decimal)
        $stmt->bind_param("isds", $id_karyawan, $tgl_rapel, $nominal_rapel, $ket_rapel);

        // Execute query
        if ($stmt->execute()) {
            $inserted_id = $stmt->insert_id;
            $stmt->close();
            sendResponse('success', 'Data rapel berhasil ditambahkan', ['id_rapel' => $inserted_id]);
        } else {
            $error = $stmt->error;
            $stmt->close();
            sendResponse('error', 'Gagal menyimpan data: ' . $error);
        }
        break;

    // ========================================
    // ACTION: EDIT EXISTING RAPEL
    // ========================================
    case 'edit':
        // Sanitize and validate input
        $id_rapel = isset($_POST['id_rapel']) ? intval($_POST['id_rapel']) : 0;
        $id_karyawan = isset($_POST['id_karyawan']) ? intval($_POST['id_karyawan']) : 0;
        $tgl_rapel = isset($_POST['tgl_rapel']) ? trim($_POST['tgl_rapel']) : '';
        $nominal_rapel = isset($_POST['nominal_rapel']) ? floatval(str_replace('.', '', $_POST['nominal_rapel'])) : 0;

        // Ambil keterangan dan bersihkan spasi gandanya (Normalisasi)
        $ket_rapel = isset($_POST['ket_rapel']) ? trim($_POST['ket_rapel']) : '';
        $ket_rapel = preg_replace('/\s+/', ' ', $ket_rapel);

        // Validation
        $errors = [];

        if ($id_rapel <= 0) {
            $errors[] = 'ID rapel tidak valid';
        }

        if ($id_karyawan <= 0) {
            $errors[] = 'Karyawan harus dipilih';
        }

        if (empty($tgl_rapel)) {
            $errors[] = 'Tanggal rapel harus diisi';
        } else {
            // Validate date format
            $date = DateTime::createFromFormat('Y-m-d', $tgl_rapel);
            if (!$date || $date->format('Y-m-d') !== $tgl_rapel) {
                $errors[] = 'Format tanggal tidak valid';
            }
        }

        if ($nominal_rapel <= 0) {
            $errors[] = 'Nominal rapel harus lebih dari 0';
        }

        if ($nominal_rapel > 999999999) {
            $errors[] = 'Nominal rapel terlalu besar';
        }

        // If there are validation errors
        if (!empty($errors)) {
            sendResponse('error', implode(', ', $errors));
        }

        // Check if rapel exists
        $checkStmt = $koneksi->prepare("SELECT id_rapel FROM tb_rapel WHERE id_rapel = ?");
        $checkStmt->bind_param("i", $id_rapel);
        $checkStmt->execute();
        $checkStmt->store_result();

        if ($checkStmt->num_rows === 0) {
            $checkStmt->close();
            sendResponse('error', 'Data rapel tidak ditemukan');
        }
        $checkStmt->close();

        // Check if employee exists
        $checkKaryawan = $koneksi->prepare("SELECT id_karyawan FROM tb_karyawan WHERE id_karyawan = ?");
        $checkKaryawan->bind_param("i", $id_karyawan);
        $checkKaryawan->execute();
        $checkKaryawan->store_result();

        if ($checkKaryawan->num_rows === 0) {
            $checkKaryawan->close();
            sendResponse('error', 'Data karyawan tidak ditemukan');
        }
        $checkKaryawan->close();

        // PERUBAHAN: Cek duplikat saat edit (pastikan tidak bentrok dengan ID Rapel lain yang punya kombinasi Karyawan+Tanggal+Ket sama)
        $dupStmt = $koneksi->prepare("SELECT id_rapel FROM tb_rapel WHERE id_karyawan = ? AND tgl_rapel = ? AND ket_rapel = ? AND id_rapel != ?");
        $dupStmt->bind_param("issi", $id_karyawan, $tgl_rapel, $ket_rapel, $id_rapel);
        $dupStmt->execute();
        $dupStmt->store_result();

        if ($dupStmt->num_rows > 0) {
            $dupStmt->close();
            sendResponse('error', 'Data rapel untuk karyawan ini dengan tanggal dan keterangan tersebut sudah ada di baris lain!');
        }
        $dupStmt->close();

        // Prepare UPDATE statement
        $stmt = $koneksi->prepare("UPDATE tb_rapel SET id_karyawan = ?, tgl_rapel = ?, nominal_rapel = ?, ket_rapel = ? WHERE id_rapel = ?");

        if (!$stmt) {
            sendResponse('error', 'Gagal menyiapkan query: ' . $koneksi->error);
        }

        // Bind parameters
        $stmt->bind_param("isdsi", $id_karyawan, $tgl_rapel, $nominal_rapel, $ket_rapel, $id_rapel);

        // Execute query
        if ($stmt->execute()) {
            $affected_rows = $stmt->affected_rows;
            $stmt->close();

            if ($affected_rows > 0) {
                sendResponse('success', 'Data rapel berhasil diperbarui');
            } else {
                sendResponse('success', 'Tidak ada perubahan data');
            }
        } else {
            $error = $stmt->error;
            $stmt->close();
            sendResponse('error', 'Gagal mengupdate data: ' . $error);
        }
        break;

    // ========================================
    // ACTION: DELETE RAPEL
    // ========================================
    case 'delete':
        // Sanitize and validate input
        $id_rapel = isset($_POST['id_rapel']) ? intval($_POST['id_rapel']) : 0;

        // Validation
        if ($id_rapel <= 0) {
            sendResponse('error', 'ID rapel tidak valid');
        }

        // Check if rapel exists
        $checkStmt = $koneksi->prepare("SELECT id_rapel FROM tb_rapel WHERE id_rapel = ?");
        $checkStmt->bind_param("i", $id_rapel);
        $checkStmt->execute();
        $checkStmt->store_result();

        if ($checkStmt->num_rows === 0) {
            $checkStmt->close();
            sendResponse('error', 'Data rapel tidak ditemukan');
        }
        $checkStmt->close();

        // Prepare DELETE statement
        $stmt = $koneksi->prepare("DELETE FROM tb_rapel WHERE id_rapel = ?");

        if (!$stmt) {
            sendResponse('error', 'Gagal menyiapkan query: ' . $koneksi->error);
        }

        // Bind parameter
        $stmt->bind_param("i", $id_rapel);

        // Execute query
        if ($stmt->execute()) {
            $affected_rows = $stmt->affected_rows;
            $stmt->close();

            if ($affected_rows > 0) {
                sendResponse('success', 'Data rapel berhasil dihapus');
            } else {
                sendResponse('error', 'Data rapel tidak ditemukan atau sudah dihapus');
            }
        } else {
            $error = $stmt->error;
            $stmt->close();
            sendResponse('error', 'Gagal menghapus data: ' . $error);
        }
        break;

    // ========================================
    // ACTION: INVALID
    // ========================================
    default:
        sendResponse('error', 'Aksi tidak valid: ' . htmlspecialchars($action));
        break;
}

// Close database connection
if (isset($koneksi)) {
    $koneksi->close();
}
