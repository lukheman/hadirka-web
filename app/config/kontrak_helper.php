<?php
// Helper generate surat kontrak (.docx) dari template Word per jabatan.
//
// Variabel yang diisi:
//   Baru    : ${nomor_kontrak}, ${alamat}, ${durasi} (+ ${tgl_mulai}, ${tgl_selesai})
//             placeholder-nya disisipkan otomatis ke salinan template bila belum ada.
//   Lama    : ${nama}, ${nik}, ${jenis_kelamin}, ${tempat_tanggal_lahir}, ${jabatan},
//             ${gaji_pokok}, ${upah_lembur}, ${izin}, ${mangkir},
//             ${bonus}, ${tunjangan_daerah}, ${tunjangan_hidup},
//             ${tunjangan_skill}, ${tunjangan_uang_makan},
//             ${tunjangan_tetap}, ${tunjangan_tidak_tetap} (daftar per jenis)

require_once __DIR__ . '/nomor_surat.php';

if (!function_exists('terbilang_id')) {
    function terbilang_id($n)
    {
        $n = (int)$n;
        $w = ['Nol', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima', 'Enam', 'Tujuh', 'Delapan', 'Sembilan', 'Sepuluh', 'Sebelas'];
        if ($n < 12) return $w[$n];
        if ($n < 20) return terbilang_id($n - 10) . ' Belas';
        if ($n < 100) {
            $sisa = $n % 10;
            return terbilang_id(intval($n / 10)) . ' Puluh' . ($sisa ? ' ' . terbilang_id($sisa) : '');
        }
        if ($n < 200) return 'Seratus' . ($n % 100 ? ' ' . terbilang_id($n % 100) : '');
        if ($n < 1000) {
            $sisa = $n % 100;
            return terbilang_id(intval($n / 100)) . ' Ratus' . ($sisa ? ' ' . terbilang_id($sisa) : '');
        }
        if ($n < 2000) return 'Seribu' . ($n % 1000 ? ' ' . terbilang_id($n % 1000) : '');
        if ($n < 1000000) {
            $sisa = $n % 1000;
            return terbilang_id(intval($n / 1000)) . ' Ribu' . ($sisa ? ' ' . terbilang_id($sisa) : '');
        }
        if ($n < 1000000000) {
            $sisa = $n % 1000000;
            return terbilang_id(intval($n / 1000000)) . ' Juta' . ($sisa ? ' ' . terbilang_id($sisa) : '');
        }
        $sisa = $n % 1000000000;
        return terbilang_id(intval($n / 1000000000)) . ' Miliar' . ($sisa ? ' ' . terbilang_id($sisa) : '');
    }
}

if (!function_exists('kontrak_bulan_diff')) {
    // Selisih bulan kalender tgl_mulai -> tgl_selesai (tanpa penyesuaian tanggal),
    // mis. 2026-09-18 s/d 2027-03-17 = 6; 2026-01-01 s/d 2026-07-01 = 6.
    function kontrak_bulan_diff($tgl_mulai, $tgl_selesai)
    {
        $d1 = new DateTime($tgl_mulai);
        $d2 = new DateTime($tgl_selesai);
        if ($d2 < $d1) throw new Exception('Tanggal selesai kontrak lebih awal dari tanggal mulai.');
        return ((int)$d2->format('Y') - (int)$d1->format('Y')) * 12 + ((int)$d2->format('n') - (int)$d1->format('n'));
    }
}

if (!function_exists('kontrak_durasi')) {
    // "6 (Enam) bulan" / "3 (Tiga) bulan".
    function kontrak_durasi($tgl_mulai, $tgl_selesai)
    {
        if (empty($tgl_mulai) || empty($tgl_selesai) || $tgl_selesai === '0000-00-00') {
            throw new Exception('Tanggal mulai/selesai kontrak belum lengkap, durasi tidak bisa dihitung.');
        }
        $m = kontrak_bulan_diff($tgl_mulai, $tgl_selesai);
        return $m . ' (' . terbilang_id($m) . ') bulan';
    }
}

if (!function_exists('kontrak_tgl_id')) {
    // "2026-09-01" -> "1 September 2026".
    function kontrak_tgl_id($date)
    {
        if (empty($date) || $date === '0000-00-00') return '-';
        $bulan = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $d = new DateTime($date);
        return ((int)$d->format('j')) . ' ' . $bulan[(int)$d->format('n')] . ' ' . $d->format('Y');
    }
}

if (!function_exists('kontrak_rupiah')) {
    // "5000000" / "5.000.000" / "5000000.00" -> "Rp 5.000.000".
    function kontrak_rupiah($val)
    {
        if ($val === null || $val === '') return 'Rp 0';
        $s = str_replace('.', '', (string)$val);
        $s = str_replace(',', '.', $s);
        $n = is_numeric($s) ? floatval($s) : 0;
        return 'Rp ' . number_format($n, 0, ',', '.');
    }
}

if (!function_exists('kontrak_is_makan')) {
    // Tunjangan makan dihitung harian -> nominal ditampilkan dengan akhiran "/hari".
    function kontrak_is_makan($nama)
    {
        $k = strtolower(trim((string)$nama));
        return in_array($k, ['tunjangan makan', 'tunjangan uang makan'], true);
    }
}

if (!function_exists('kontrak_rupiah_makan')) {
    // Rupiah + "/hari" khusus tunjangan makan.
    function kontrak_rupiah_makan($val)
    {
        return kontrak_rupiah($val) . '/hari';
    }
}

if (!function_exists('kontrak_title')) {
    // Data master tersimpan HURUF BESAR semua -> tampilkan Title Case di dokumen
    // ("ABDUL KHAIR" -> "Abdul Khair"). Tidak mengubah data di database/payroll.
    function kontrak_title($s)
    {
        $s = trim((string)$s);
        if ($s === '') return $s;
        return mb_convert_case($s, MB_CASE_TITLE, 'UTF-8');
    }
}

if (!function_exists('kontrak_alamat')) {
    // Gabungan alamat KTP + wilayah (Title Case).
    function kontrak_alamat($row)
    {
        $parts = [];
        if (!empty($row['alamat_ktp'])) $parts[] = kontrak_title($row['alamat_ktp']);
        if (!empty($row['desa'])) $parts[] = 'Kel/Desa ' . kontrak_title($row['desa']);
        if (!empty($row['kecamatan'])) $parts[] = 'Kec. ' . kontrak_title($row['kecamatan']);
        if (!empty($row['kabupaten'])) $parts[] = 'Kab. ' . kontrak_title($row['kabupaten']);
        return $parts ? implode(', ', $parts) : '-';
    }
}

if (!function_exists('kontrak_xml_replace')) {
    // Ganti pola teks pada XML Word dengan penggabungan run terpecah.
    // $rules = [ [pattern_preg, pengganti], ... ].
    function kontrak_xml_replace($xml, array $rules)
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = true;
        if (!@$dom->loadXML($xml)) return $xml;
        $xp = new DOMXPath($dom);
        $xp->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');

        foreach ($rules as $rule) {
            list($pattern, $repl) = $rule;
            // Petakan run sekali; terapkan dari kecocokan TERAKHIR ke awal agar
            // offset yang lebih awal tetap valid (penggantian tak menambah/menghapus node).
            $nodes = [];
            foreach ($xp->query('//w:t') as $t) $nodes[] = $t;
            $full = '';
            $map = [];
            foreach ($nodes as $i => $t) {
                $txt = $t->textContent;
                $len = mb_strlen($txt, 'UTF-8');
                for ($k = 0; $k < $len; $k++) $map[] = [$i, $k];
                $full .= $txt;
            }
            if (!preg_match_all($pattern, $full, $all, PREG_OFFSET_CAPTURE)) continue;
            $hits = $all[0];
            if ($repl !== null) $hits = [reset($hits)]; // aturan sisip: cukup kemunculan pertama
            foreach (array_reverse($hits) as $h) {
                $use_repl = ($repl === null) ? $h[0] : $repl; // normalisasi: isi tidak berubah
                $byteStart = $h[1];
                $charStart = mb_strlen(substr($full, 0, $byteStart), 'UTF-8');
                $charLen = mb_strlen($h[0], 'UTF-8');
                if ($charLen === 0) continue;
                $charEnd = $charStart + $charLen - 1;

                list($si, $sc) = $map[$charStart];
                list($ei, $ec) = $map[$charEnd];
                // Baca ulang teks node saat ini (bisa berubah oleh penggantian di belakangnya).
                $sText = $nodes[$si]->textContent;
                // Sesuaikan offset dalam node: hitung ulang posisi via sisa peta? Sederhana dan
                // aman karena penggantian di belakang tak mengubah teks node ini: pakai $sc apa adanya
                // tetapi baca ulang panjang untuk $ec pada node akhir.
                $before = mb_substr($sText, 0, $sc, 'UTF-8');
                if ($si === $ei) {
                    $after = mb_substr($sText, $ec + 1, null, 'UTF-8');
                    $nodes[$si]->nodeValue = '';
                    $nodes[$si]->appendChild($dom->createTextNode($before . $use_repl . $after));
                } else {
                    $nodes[$si]->nodeValue = '';
                    $nodes[$si]->appendChild($dom->createTextNode($before . $use_repl));
                    for ($i = $si + 1; $i < $ei; $i++) {
                        $nodes[$i]->nodeValue = '';
                    }
                    $eText = $nodes[$ei]->textContent;
                    $nodes[$ei]->nodeValue = '';
                    $nodes[$ei]->appendChild($dom->createTextNode(mb_substr($eText, $ec + 1, null, 'UTF-8')));
                }
                foreach ([$si, $ei] as $ii) {
                    $nodes[$ii]->setAttribute('xml:space', 'preserve');
                }
            }
        }
        return $dom->saveXML();
    }
}

if (!function_exists('kontrak_patch_docx')) {
    // Salin template -> $dst sambil menyisipkan placeholder baru bila belum ada.
    function kontrak_patch_docx($src, $dst)
    {
        if (!copy($src, $dst)) throw new Exception('Gagal menyalin template kontrak.');
        $zip = new ZipArchive();
        if ($zip->open($dst) !== true) throw new Exception('File template kontrak rusak.');
        $targets = ['word/document.xml'];
        for ($i = 1; $i <= 9; $i++) $targets[] = "word/header{$i}.xml";
        for ($i = 1; $i <= 3; $i++) $targets[] = "word/footer{$i}.xml";
        $rules = [
            ['/TPJS-KONTRAK-[.\/]+/', '${nomor_kontrak}'],
            ['/Alamat:\s*[.\s]*,\s*Kel\/Desa\s*[.\s]*,\s*Kec\s*[.\s]*,\s*Kab\s*[.\s]*/u', 'Alamat: ${alamat}'],
            ['/berlaku sejak tanggal sampai dengan tanggal atau selama\s*\d+\s*\([^)]*\)\s*bulan/u', 'berlaku sejak ${tgl_mulai} sampai dengan ${tgl_selesai} atau selama ${durasi}'],
            ['/\$\{[^}]+\}/', null], // normalisasi placeholder terpecah antar-run (isi tetap)
        ];
        foreach ($targets as $name) {
            $idx = $zip->locateName($name);
            if ($idx === false) continue;
            $xml = $zip->getFromIndex($idx);
            $new = kontrak_xml_replace($xml, $rules);
            $zip->deleteName($name);
            $zip->addFromString($name, $new);
        }
        $zip->close();
    }
}

if (!function_exists('build_kontrak_docx')) {
    // Bangun .docx kontrak untuk satu id_karier. Return path file .docx.
    function build_kontrak_docx($koneksi, $id_karier)
    {
        $id_karier = (int)$id_karier;
        if ($id_karier <= 0) throw new Exception('ID karier tidak valid.');

        $sql = "SELECT k.*, kr.nik_karyawan, kr.nama_lengkap, kr.jenis_kelamin, kr.tempat_lahir, kr.tgl_lahir,
                j.nama_jabatan, j.kode_jabatan, s.nama_site, d.nama_departemen,
                i.nik_ktp, i.alamat_ktp, i.desa, i.kecamatan, i.kabupaten
            FROM tb_karier k
            JOIN tb_karyawan kr ON kr.id_karyawan = k.id_karyawan
            LEFT JOIN tb_jabatan j ON j.id_jabatan = k.id_jabatan
            LEFT JOIN tb_site s ON s.id_site = k.id_site
            LEFT JOIN tb_departemen d ON d.id_departemen = k.id_departemen
            LEFT JOIN tb_identitas i ON i.id_karyawan = kr.id_karyawan
            WHERE k.id_karier = '$id_karier' LIMIT 1";
        $q = $koneksi->query($sql);
        if (!$q || $q->num_rows === 0) throw new Exception('Data karier tidak ditemukan.');
        $r = $q->fetch_assoc();

        // Template terbaru yang berlaku untuk jabatan ini.
        $id_jabatan = (int)($r['id_jabatan'] ?? 0);
        $qt = $koneksi->query(
            "SELECT t.`file` FROM tb_template_kontrak_jabatan p " .
            "JOIN tb_template_kontrak t ON t.id_template = p.id_template " .
            "WHERE p.id_jabatan = '$id_jabatan' ORDER BY t.id_template DESC LIMIT 1"
        );
        if (!$qt || $qt->num_rows === 0) throw new Exception('Belum ada template kontrak untuk jabatan ini.');
        $tpl_file = $qt->fetch_assoc()['file'];
        // Template master di template-kontrak/, fallback ke kontrak/ untuk file lama.
        $tpl_base = basename((string)$tpl_file);
        $src = __DIR__ . '/../uploads/template-kontrak/' . $tpl_base;
        if (!is_file($src)) {
            $legacy = __DIR__ . '/../uploads/kontrak/' . $tpl_base;
            if (is_file($legacy)) $src = $legacy;
        }
        if (!is_file($src)) throw new Exception('File template kontrak tidak ditemukan di server.');

        // Nomor surat: pakai yang ada, atau terbitkan baru.
        $no_surat = trim((string)($r['no_surat'] ?? ''));
        if ($no_surat === '') {
            $no_surat = generate_no_surat($koneksi, $r['kode_jabatan'] ?? '', $r['tgl_mulai'] ?? '');
            $ns_esc = $koneksi->real_escape_string($no_surat);
            $koneksi->query("UPDATE tb_karier SET no_surat = '$ns_esc' WHERE id_karier = '$id_karier'");
        }

        // Tunjangan per nama (lowercase) + daftar per jenis (tetap / tidak_tetap).
        // Hanya yang tampil_template=1 yang dirender di dokumen; yang disembunyikan
        // tetap tersimpan (payroll) tapi tidak muncul di kontrak.
        // Tiap baris = [nama, rupiah] agar bisa dirender sebagai tabel Word (":" sejajar).
        $tun = [];
        $tun_tetap = [];
        $tun_tidak = [];
        $qtun = $koneksi->query("SELECT nama_tunjangan, jenis_tunjangan, jumlah_tunjangan, tampil_template FROM tb_tunjangan WHERE id_karier = '$id_karier' ORDER BY id_tunjangan ASC");
        if ($qtun) {
            while ($t = $qtun->fetch_assoc()) {
                if ((int)($t['tampil_template'] ?? 1) !== 1) continue;
                $tun[strtolower(trim((string)$t['nama_tunjangan']))] = $t['jumlah_tunjangan'];
                $rupiah = kontrak_is_makan($t['nama_tunjangan'] ?? '')
                    ? kontrak_rupiah_makan($t['jumlah_tunjangan'])
                    : kontrak_rupiah($t['jumlah_tunjangan']);
                $baris = [kontrak_title($t['nama_tunjangan']), $rupiah];
                if (($t['jenis_tunjangan'] ?? 'tetap') === 'tidak_tetap') {
                    $tun_tidak[] = $baris;
                } else {
                    $tun_tetap[] = $baris;
                }
            }
        }
        // Tabel Word 2 kolom (nama | ": Rp ...") agar ":" sejajar vertikal.
        // Dipakai via setComplexBlock karena placeholder berdiri sendiri satu paragraf.
        $tun_tabel = function ($rows) {
            $style = new \PhpOffice\PhpWord\Style\Table();
            $style->setBorderSize(0);
            $style->setBorderColor('FFFFFF');
            $style->setCellMargin(50);
            // Geser tabel ke kanan (3 cm = 1701 twips).
            $style->setIndent(new \PhpOffice\PhpWord\ComplexType\TblWidth(1701));
            $table = new \PhpOffice\PhpWord\Element\Table($style);
            $font = ['name' => 'Times New Roman', 'size' => 11];
            foreach ($rows as $row) {
                $table->addRow();
                $table->addCell(4500)->addText($row[0], $font);
                $table->addCell(4500)->addText(': ' . $row[1], $font);
            }
            return $table;
        };
        $tun_nilai = function ($keys) use ($tun) {
            foreach ((array)$keys as $k) {
                if (isset($tun[strtolower($k)])) return $tun[strtolower($k)];
            }
            return 0;
        };

        $jk = strtoupper(trim((string)($r['jenis_kelamin'] ?? '')));
        $jk_label = $jk === 'L' ? 'Laki-laki' : ($jk === 'P' ? 'Perempuan' : (!empty($r['jenis_kelamin']) ? kontrak_title($r['jenis_kelamin']) : '-'));
        $ttl = kontrak_title($r['tempat_lahir'] ?? '');
        if (!empty($r['tgl_lahir']) && $r['tgl_lahir'] !== '0000-00-00') {
            $ttl .= ($ttl !== '' ? ', ' : '') . kontrak_tgl_id($r['tgl_lahir']);
        }
        if ($ttl === '') $ttl = '-';

        $vars = [
            'nomor_kontrak' => $no_surat,
            // ${alamat} = alamat lengkap gabungan (jalan + Kel/Desa + Kec. + Kab.).
            // Kunci granular dipertahankan untuk kompatibilitas template lama.
            'alamat' => kontrak_alamat($r),
            'desa ' => kontrak_title($r['desa'] ?? ''),
            'desa' => kontrak_title($r['desa'] ?? ''),
            'kecamatan' => kontrak_title($r['kecamatan'] ?? ''),
            'kabupaten' => kontrak_title($r['kabupaten'] ?? ''),
            'durasi' => kontrak_durasi($r['tgl_mulai'] ?? '', $r['tgl_selesai'] ?? ''),
            'tgl_mulai' => kontrak_tgl_id($r['tgl_mulai'] ?? ''),
            'tgl_selesai' => kontrak_tgl_id($r['tgl_selesai'] ?? ''),
            'nama' => isset($r['nama_lengkap']) && trim((string)$r['nama_lengkap']) !== '' ? kontrak_title($r['nama_lengkap']) : '-',
            // NIK kontrak hanya dari nik_ktp (tb_identitas); kosong bila tidak ada.
            // nik_karyawan berisi ID karyawan, bukan nomor KTP.
            'nik' => trim((string)($r['nik_ktp'] ?? '')),
            'jenis_kelamin' => $jk_label,
            'tempat_tanggal_lahir' => $ttl,
            'jabatan' => isset($r['nama_jabatan']) && trim((string)$r['nama_jabatan']) !== '' ? kontrak_title($r['nama_jabatan']) : '-',
            'gaji_pokok' => kontrak_rupiah($r['gaji_pokok'] ?? 0),
            'upah_lembur' => kontrak_rupiah($r['lembur'] ?? 0),
            'izin' => kontrak_rupiah($r['pot_izin'] ?? 0),
            'mangkir' => kontrak_rupiah($r['pot_mangkir'] ?? 0),
            'bonus' => kontrak_rupiah($tun_nilai(['bonus kehadiran', 'bonus'])),
            'tunjangan_daerah' => kontrak_rupiah($tun_nilai(['tunjangan daerah'])),
            'tunjangan_hidup' => kontrak_rupiah($tun_nilai(['tunjangan hidup'])),
            'tunjangan_skill' => kontrak_rupiah($tun_nilai(['tunjangan skill'])),
            'tunjangan_uang_makan' => kontrak_rupiah_makan($tun_nilai(['tunjangan uang makan', 'tunjangan makan'])),
        ];

        $out_dir = __DIR__ . '/../uploads/kontrak/';
        if (!is_dir($out_dir) && !mkdir($out_dir, 0755, true)) throw new Exception('Gagal menyiapkan folder kontrak.');
        $fname = preg_replace('/[^A-Za-z0-9\-]+/', '-', $no_surat) . '.docx';
        if (strlen($fname) < 6) $fname = 'kontrak-' . $id_karier . '.docx';
        $dst = $out_dir . $fname;

        kontrak_patch_docx($src, $dst);

        $tp = new \PhpOffice\PhpWord\TemplateProcessor($dst);
        // Daftar tunjangan dirender sebagai tabel 2 kolom (nama | ": Rp ...")
        // agar ":" sejajar vertikal; daftar kosong diisi "-".
        foreach (['tunjangan_tetap' => $tun_tetap, 'tunjangan_tidak_tetap' => $tun_tidak] as $macro => $rows) {
            if ($rows) {
                $tp->setComplexBlock($macro, $tun_tabel($rows));
            } else {
                $tp->setValue($macro, '-');
            }
        }
        $tp->setValues($vars);
        $tp->saveAs($dst);

        $f_esc = $koneksi->real_escape_string($fname);
        $koneksi->query("UPDATE tb_karier SET file_kontrak = '$f_esc' WHERE id_karier = '$id_karier'");

        return ['docx' => $dst, 'file' => $fname, 'no_surat' => $no_surat];
    }
}
