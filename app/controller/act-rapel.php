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

        try {
            // Check if employee exists
            $checkStmt = $koneksi->prepare("SELECT id_karyawan FROM tb_karyawan WHERE id_karyawan = ?");
            $checkStmt->execute([$id_karyawan]);

            if (!$checkStmt->fetch()) {
                sendResponse('error', 'Data karyawan tidak ditemukan');
            }

            // PERUBAHAN: Cek duplikat data dengan menyertakan KETERANGAN
            $dupStmt = $koneksi->prepare("SELECT id_rapel FROM tb_rapel WHERE id_karyawan = ? AND tgl_rapel = ? AND ket_rapel = ?");
            $dupStmt->execute([$id_karyawan, $tgl_rapel, $ket_rapel]);

            if ($dupStmt->fetch()) {
                sendResponse('error', 'Data rapel untuk karyawan ini dengan tanggal dan keterangan yang sama persis sudah ada!');
            }

            // Prepare INSERT statement
            $stmt = $koneksi->prepare("INSERT INTO tb_rapel (id_karyawan, tgl_rapel, nominal_rapel, ket_rapel) VALUES (?, ?, ?, ?)");

            // Execute query
            if ($stmt->execute([$id_karyawan, $tgl_rapel, $nominal_rapel, $ket_rapel])) {
                $inserted_id = (int)$koneksi->lastInsertId();
                sendResponse('success', 'Data rapel berhasil ditambahkan', ['id_rapel' => $inserted_id]);
            } else {
                sendResponse('error', 'Gagal menyimpan data.');
            }
        } catch (PDOException $e) {
            error_log($e->getMessage());
            sendResponse('error', 'Gagal menyimpan data.');
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

        try {
            // Check if rapel exists
            $checkStmt = $koneksi->prepare("SELECT id_rapel FROM tb_rapel WHERE id_rapel = ?");
            $checkStmt->execute([$id_rapel]);

            if (!$checkStmt->fetch()) {
                sendResponse('error', 'Data rapel tidak ditemukan');
            }

            // Check if employee exists
            $checkKaryawan = $koneksi->prepare("SELECT id_karyawan FROM tb_karyawan WHERE id_karyawan = ?");
            $checkKaryawan->execute([$id_karyawan]);

            if (!$checkKaryawan->fetch()) {
                sendResponse('error', 'Data karyawan tidak ditemukan');
            }

            // PERUBAHAN: Cek duplikat saat edit (pastikan tidak bentrok dengan ID Rapel lain yang punya kombinasi Karyawan+Tanggal+Ket sama)
            $dupStmt = $koneksi->prepare("SELECT id_rapel FROM tb_rapel WHERE id_karyawan = ? AND tgl_rapel = ? AND ket_rapel = ? AND id_rapel != ?");
            $dupStmt->execute([$id_karyawan, $tgl_rapel, $ket_rapel, $id_rapel]);

            if ($dupStmt->fetch()) {
                sendResponse('error', 'Data rapel untuk karyawan ini dengan tanggal dan keterangan tersebut sudah ada di baris lain!');
            }

            // Prepare UPDATE statement
            $stmt = $koneksi->prepare("UPDATE tb_rapel SET id_karyawan = ?, tgl_rapel = ?, nominal_rapel = ?, ket_rapel = ? WHERE id_rapel = ?");

            // Execute query
            if ($stmt->execute([$id_karyawan, $tgl_rapel, $nominal_rapel, $ket_rapel, $id_rapel])) {
                $affected_rows = $stmt->rowCount();

                if ($affected_rows > 0) {
                    sendResponse('success', 'Data rapel berhasil diperbarui');
                } else {
                    sendResponse('success', 'Tidak ada perubahan data');
                }
            } else {
                sendResponse('error', 'Gagal mengupdate data.');
            }
        } catch (PDOException $e) {
            error_log($e->getMessage());
            sendResponse('error', 'Gagal mengupdate data.');
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

        try {
            // Check if rapel exists
            $checkStmt = $koneksi->prepare("SELECT id_rapel FROM tb_rapel WHERE id_rapel = ?");
            $checkStmt->execute([$id_rapel]);

            if (!$checkStmt->fetch()) {
                sendResponse('error', 'Data rapel tidak ditemukan');
            }

            // Prepare DELETE statement
            $stmt = $koneksi->prepare("DELETE FROM tb_rapel WHERE id_rapel = ?");

            // Execute query
            if ($stmt->execute([$id_rapel])) {
                $affected_rows = $stmt->rowCount();

                if ($affected_rows > 0) {
                    sendResponse('success', 'Data rapel berhasil dihapus');
                } else {
                    sendResponse('error', 'Data rapel tidak ditemukan atau sudah dihapus');
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
