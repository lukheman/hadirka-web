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
     * Parse tanggal sel Excel menjadi 'Y-m-d' (fleksibel, untuk input manual).
     * Mendukung semua format parseDate() plus:
     * - tanggal teks Indonesia/Inggris: "25 Februari 2025", "Feb 25, 2026"
     * - slash Amerika: "8/29/2026" (m/d/Y, didahulukan sebelum d/m/Y)
     * - angka tanpa nol depan: "6/8/2026", "3/2/2026"
     * - auto-convert: titik antar-angka ("25.02.2025"), label ("tgl ..."),
     *   jam menempel ("2026-09-20 10:30"), tahun 2 digit ("25/02/25")
     * Tanggal yang benar-benar tak valid (mis. 30 Feb) tetap null.
     * Tidak mengubah parseDate() agar importer lama tidak terpengaruh.
     */
    public static function parseDateFlexible($value)
    {
        if (empty($value)) return null;

        try {
            if (is_numeric($value)) {
                $cls = 'PhpOffice\\PhpSpreadsheet\\Shared\\Date';
                if (class_exists($cls)) {
                    return $cls::excelToDateTimeObject($value)->format('Y-m-d');
                }
                return null;
            }

            $candidates = [trim((string)$value)];
            $candidates[] = self::bersihkanTanggal($candidates[0]);

            foreach (array_unique($candidates) as $v) {
                if ($v === '') continue;

                // 1. Tanggal teks (Indonesia/Inggris, hari dulu atau bulan dulu)
                $tglTeks = self::parseTanggalTeks($v);
                if ($tglTeks !== null) return $tglTeks;

                // 2. Format terstruktur; 'm/d/Y' sebelum 'd/m/Y' (kolom slash
                //    dari Excel locale-US). Pengecekan via warning/error parser
                //    (bukan perbandingan string) agar "6/8/2026" lolos.
                foreach (['Y-m-d', 'd-m-Y', 'm/d/Y', 'd/m/Y', 'Y/m/d', 'd.m.Y', 'm.d.Y', 'Y.m.d', 'm/d/y', 'd/m/y', 'd-m-y', 'y-m-d'] as $format) {
                    $date = DateTime::createFromFormat('!' . $format, $v);
                    $err = DateTime::getLastErrors();
                    if ($date && ($err['warning_count'] ?? 0) === 0 && ($err['error_count'] ?? 0) === 0) {
                        // Tolak tahun tak wajar (mis. "26" terbaca tahun 26 Masehi)
                        if ((int)$date->format('Y') < 1000) continue;
                        return $date->format('Y-m-d');
                    }
                }
            }
            return null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Bersihkan noise umum sebelum parsing: label "tgl/tanggal",
     * jam yang menempel ("2026-09-20 10:30", "...T10:30:00"),
     * titik singkatan ("Feb."), koma/titik di ujung.
     */
    public static function bersihkanTanggal($value)
    {
        $v = trim((string)$value);
        // label di depan: "tgl 25 ...", "tanggal: 25 ...", "date 25 ..."
        $v = preg_replace('/^(tgl|tanggal|date)\s*[:.]?\s*/i', '', $v);
        // jam di belakang: "10:30", "10:30:00", "T10:30", "10.30", "... AM/PM"
        $v = preg_replace('/[\sT]\d{1,2}:\d{2}(:\d{2})?(\s*[AP]\.?M\.?)?$/i', '', $v);
        // tahun 2 digit -> 4 digit ("6/8/26" -> "6/8/2026", "25-02-25" -> "25-02-2025")
        $v = preg_replace_callback(
            '/^(\d{1,2}[\/\-.]\d{1,2}[\/\-.])(\d{2})$/',
            function ($m) { return $m[1] . self::tahun4Digit($m[2]); },
            $v
        );
        // titik singkatan ("Feb.") — bukan titik antar-angka ("25.02.2025")
        $v = preg_replace('/(?<!\d)\.(?!\d)/', '', $v);
        $v = trim(rtrim($v, "., "));
        return trim(preg_replace('/\s+/', ' ', $v));
    }

    /**
     * "25 Februari 2025" / "February 5, 2026" -> "Y-m-d".
     * Null bila tidak cocok/tidak valid. Tahun 2 digit: <70 -> 20xx.
     */
    public static function parseTanggalTeks($value)
    {
        $bulan = [
            'januari' => '01', 'january' => '01', 'jan' => '01',
            'februari' => '02', 'february' => '02', 'feb' => '02', 'pebruari' => '02',
            'maret' => '03', 'march' => '03', 'mar' => '03',
            'april' => '04', 'apr' => '04',
            'mei' => '05', 'may' => '05',
            'juni' => '06', 'june' => '06', 'jun' => '06',
            'juli' => '07', 'july' => '07', 'jul' => '07',
            'agustus' => '08', 'august' => '08', 'agu' => '08', 'agust' => '08', 'augustus' => '08', 'aug' => '08',
            'september' => '09', 'sep' => '09', 'sept' => '09',
            'oktober' => '10', 'october' => '10', 'okt' => '10', 'oct' => '10',
            'november' => '11', 'nov' => '11', 'nopember' => '11',
            'desember' => '12', 'december' => '12', 'des' => '12', 'dec' => '12',
        ];

        $value = preg_replace('/\s+/', ' ', strtolower(trim((string)$value)));

        // "25 Februari 2025" / "25 Feb 25" / "15-Feb-25" / "15/Feb/2025"
        // (pemisah spasi, slash, titik, atau strip; bulan teks Indo/Inggris).
        if (preg_match('/^(\d{1,2})[\s\/\-.]+([a-z]+)[\s\/\-.]+(\d{2,4})$/', $value, $m)) {
            $tgl = (int)$m[1];
            $thn = self::tahun4Digit($m[3]);
            $bln = $bulan[$m[2]] ?? null;
        // "September 5, 2026" / "Sep 5 2026"
        } elseif (preg_match('/^([a-z]+)\s+(\d{1,2}),?\s+(\d{2,4})$/', $value, $m)) {
            $tgl = (int)$m[2];
            $thn = self::tahun4Digit($m[3]);
            $bln = $bulan[$m[1]] ?? null;
        } else {
            return null;
        }

        if ($bln === null || $thn === null) return null;
        if (!checkdate((int)$bln, $tgl, $thn)) return null;

        return sprintf('%04d-%02d-%02d', $thn, (int)$bln, $tgl);
    }

    /**
     * "25" -> 2025, "99" -> 1999, "2025" -> 2025. Null bila bukan angka.
     */
    public static function tahun4Digit($y)
    {
        $y = trim((string)$y);
        if (!ctype_digit($y)) return null;
        if (strlen($y) === 4) return (int)$y;
        if (strlen($y) === 2) {
            $n = (int)$y;
            return $n >= 70 ? 1900 + $n : 2000 + $n;
        }
        return null;
    }

    /** Alias lama, dipertahankan agar kompatibel. */
    public static function parseTanggalIndonesia($value)
    {
        return self::parseTanggalTeks(self::bersihkanTanggal($value));
    }
    public static function esc($koneksi, $value)
    {
        return mysqli_real_escape_string($koneksi, trim((string)$value));
    }
}
