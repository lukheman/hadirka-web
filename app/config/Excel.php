<?php
// app/config/Excel.php
// Helper terpusat untuk import/export Excel/CSV (DRY).
// Menggantikan definisi lokal yang duplikat di app/controller/csv-control/*:
// - cleanRupiah()      (6x: csv-potongan-tidak-tetap, csv-pinjaman, csv-rapel,
//                       csv-thr, csv-karier, csv-pinjaman-internal)
// - sendJsonResponse() (3x: csv-rapel, csv-thr, csv-bpjs-tk)
// - formatTanggal()    (3x: csv-karyawan [strict], csv-karier, csv-transaksi-apd)
// - escapeString()     (2x: csv-karyawan, csv-transaksi-apd)
//
// Cara pakai di endpoint csv-control:
//   require_once __DIR__ . '/../../config/Excel.php';
//   $nominal = Excel::cleanRupiah($row['F'] ?? 0);
//   $tanggal = Excel::parseDate($row['E'] ?? '');
//   $nama    = Excel::esc($koneksi, $row['B'] ?? '');
//   Excel::json('error', 'File tidak ditemukan');

final class Excel
{
    /**
     * Bersihkan format mata uang menjadi float.
     * Mendukung: angka murni Excel, "Rp 36.881,30" (Indo), "36,881.30" (English).
     */
    public static function cleanRupiah($val)
    {
        if (empty($val)) return 0;

        // Jika dari Excel sudah terbaca sebagai angka murni, langsung return
        if (is_numeric($val)) return floatval($val);

        $val = (string)$val;
        // Hapus karakter huruf (Rp, rp) dan spasi
        $val = preg_replace('/[a-zA-Z\s]/', '', $val);

        // Deteksi pemisah ribuan dan desimal (Contoh: 36.881,30)
        if (strpos($val, ',') !== false && strpos($val, '.') !== false) {
            // Jika koma berada setelah titik, berarti koma adalah desimal (Format Indo)
            if (strrpos($val, ',') > strrpos($val, '.')) {
                $val = str_replace('.', '', $val); // hapus titik ribuan
                $val = str_replace(',', '.', $val); // jadikan koma sebagai desimal
            } else {
                // Jika format English (Contoh: 36,881.30)
                $val = str_replace(',', '', $val); // hapus koma ribuan
            }
        } elseif (strpos($val, ',') !== false) {
            // Jika cuma ada koma (Contoh: 36881,30), asumsikan format Indo (koma = desimal)
            $val = str_replace(',', '.', $val);
        }

        // Konversi akhir ke tipe Float
        return floatval($val);
    }

    /**
     * Kirim response JSON {status, message, ...data} lalu hentikan eksekusi.
     * Pengganti sendJsonResponse(). Membersihkan output buffer yang bocor
     * (guarded, aman dipanggil walau tanpa ob_start).
     */
    public static function json($status, $message, array $data = [])
    {
        if (ob_get_length()) {
            ob_clean(); // bersihkan output kotor agar JSON valid
        }
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
        }
        echo json_encode(array_merge(['status' => $status, 'message' => $message], $data));
        exit;
    }

    /**
     * Parse tanggal sel Excel menjadi 'Y-m-d' (strict).
     * Mendukung: serial numerik Excel, 'Y-m-d', 'd-m-Y', 'd/m/Y', 'Y/m/d'.
     * Mengembalikan null bila kosong/tidak valid (baris bisa dihitung gagal).
     */
    public static function parseDate($value)
    {
        if (empty($value)) return null;

        try {
            // Serial numerik Excel (1900-based)
            if (is_numeric($value)) {
                $cls = 'PhpOffice\\PhpSpreadsheet\\Shared\\Date';
                if (class_exists($cls)) {
                    return $cls::excelToDateTimeObject($value)->format('Y-m-d');
                }
                return null;
            }

            $value = trim((string)$value);
            if ($value === '') return null;

            foreach (['Y-m-d', 'd-m-Y', 'd/m/Y', 'Y/m/d'] as $format) {
                $date = DateTime::createFromFormat('!' . $format, $value);
                if ($date && $date->format($format) === $value) {
                    return $date->format('Y-m-d');
                }
            }
            return null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Escape string untuk query (trim + mysqli_real_escape_string).
     */
    public static function esc($koneksi, $value)
    {
        return mysqli_real_escape_string($koneksi, trim((string)$value));
    }
}
