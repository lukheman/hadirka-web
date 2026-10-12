/*M!999999\- enable the sandbox mode */
-- MariaDB dump 10.20-12.3.3-MariaDB, for Linux (x86_64)
--
-- Host: localhost    Database: ipm_hris
-- ------------------------------------------------------
-- Server version	12.3.3-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*M!100616 SET @OLD_NOTE_VERBOSITY=@@NOTE_VERBOSITY, NOTE_VERBOSITY=0 */;

--
-- Table structure for table `footer_shortcuts`
--

DROP TABLE IF EXISTS `footer_shortcuts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `footer_shortcuts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_shortcut` varchar(50) NOT NULL,
  `ikon_nama` varchar(50) NOT NULL,
  `tipe_aksi` enum('whatsapp','phone','email','url') DEFAULT 'whatsapp',
  `nilai_aksi` varchar(255) NOT NULL,
  `warna_ikon` varchar(7) DEFAULT '#2196F3',
  `urutan` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `m_jenis_keluar`
--

DROP TABLE IF EXISTS `m_jenis_keluar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `m_jenis_keluar` (
  `id_jenis_keluar` int(11) NOT NULL AUTO_INCREMENT,
  `nama_kategori` varchar(50) NOT NULL,
  PRIMARY KEY (`id_jenis_keluar`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `m_jenis_pelanggaran`
--

DROP TABLE IF EXISTS `m_jenis_pelanggaran`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `m_jenis_pelanggaran` (
  `id_pelanggaran` int(11) NOT NULL AUTO_INCREMENT,
  `nama_pelanggaran` varchar(255) NOT NULL,
  `keterangan_default` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_pelanggaran`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `m_ter_pph21`
--

DROP TABLE IF EXISTS `m_ter_pph21`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `m_ter_pph21` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kategori` char(1) NOT NULL,
  `bruto_min` bigint(20) NOT NULL,
  `bruto_max` bigint(20) NOT NULL,
  `tarif_persen` decimal(5,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_kat_bruto` (`kategori`,`bruto_min`,`bruto_max`)
) ENGINE=InnoDB AUTO_INCREMENT=126 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `master_apd`
--

DROP TABLE IF EXISTS `master_apd`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `master_apd` (
  `id_apd` int(11) NOT NULL AUTO_INCREMENT,
  `nama_apd` varchar(100) NOT NULL,
  `kategori_apd` varchar(50) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `masa_pakai_hari` int(11) DEFAULT 365,
  `stok` int(11) NOT NULL DEFAULT 0,
  `status_aktif` enum('aktif','nonaktif') DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_apd`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pengambilan_apd`
--

DROP TABLE IF EXISTS `pengambilan_apd`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `pengambilan_apd` (
  `id_pengambilan` int(11) NOT NULL AUTO_INCREMENT,
  `id_karyawan` varchar(50) NOT NULL,
  `id_pengajuan_apd` int(11) DEFAULT NULL,
  `id_apd` int(11) NOT NULL,
  `tanggal_pengambilan` date NOT NULL,
  `jumlah` int(11) DEFAULT 1,
  PRIMARY KEY (`id_pengambilan`),
  KEY `fk_pengambilan_apd` (`id_apd`),
  KEY `idx_karyawan` (`id_karyawan`),
  KEY `idx_tanggal` (`tanggal_pengambilan`),
  KEY `fk_transaksi_pengajuan` (`id_pengajuan_apd`),
  CONSTRAINT `fk_pengambilan_apd` FOREIGN KEY (`id_apd`) REFERENCES `master_apd` (`id_apd`) ON UPDATE CASCADE,
  CONSTRAINT `fk_transaksi_pengajuan` FOREIGN KEY (`id_pengajuan_apd`) REFERENCES `tb_pengajuan_apd` (`id_pengajuan_apd`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1329 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pengembalian_apd`
--

DROP TABLE IF EXISTS `pengembalian_apd`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `pengembalian_apd` (
  `id_pengembalian` int(11) NOT NULL AUTO_INCREMENT,
  `id_pengambilan` int(11) NOT NULL,
  `tgl_pengembalian` date NOT NULL,
  `kondisi_saat_kembali` enum('baik','rusak','hilang') NOT NULL,
  `keterangan` text DEFAULT NULL,
  `denda` decimal(10,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_pengembalian`),
  KEY `fk_pengembalian_pengambilan` (`id_pengambilan`),
  CONSTRAINT `fk_pengembalian_pengambilan` FOREIGN KEY (`id_pengambilan`) REFERENCES `pengambilan_apd` (`id_pengambilan`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tb_absen`
--

DROP TABLE IF EXISTS `tb_absen`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tb_absen` (
  `id_absen` int(11) NOT NULL AUTO_INCREMENT,
  `id_karyawan` int(11) NOT NULL,
  `tgl_absen` date NOT NULL,
  `jam_kerja` varchar(5) NOT NULL,
  `ket_absen` varchar(11) NOT NULL,
  PRIMARY KEY (`id_absen`),
  KEY `relasi_absen_karyawan` (`id_karyawan`),
  CONSTRAINT `relasi_absen_karyawan` FOREIGN KEY (`id_karyawan`) REFERENCES `tb_karyawan` (`id_karyawan`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=201891 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tb_apd`
--

DROP TABLE IF EXISTS `tb_apd`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tb_apd` (
  `id_apd` int(11) NOT NULL AUTO_INCREMENT,
  `nama_apd` varchar(100) NOT NULL,
  `kategori_apd` varchar(50) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `masa_pakai_hari` int(11) DEFAULT 365,
  `status_aktif` enum('aktif','nonaktif') DEFAULT 'aktif',
  PRIMARY KEY (`id_apd`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tb_bonus_internal`
--

DROP TABLE IF EXISTS `tb_bonus_internal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tb_bonus_internal` (
  `id_bonus` int(11) NOT NULL AUTO_INCREMENT,
  `id_karyawan_internal` int(11) NOT NULL,
  `tgl_bonus` datetime NOT NULL,
  `nominal` varchar(25) NOT NULL,
  `ket_bonus` text DEFAULT NULL,
  PRIMARY KEY (`id_bonus`),
  KEY `fk_karyawan_bonus` (`id_karyawan_internal`),
  CONSTRAINT `fk_karyawan_bonus` FOREIGN KEY (`id_karyawan_internal`) REFERENCES `tb_karyawan_internal` (`id_karyawan_internal`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tb_bukti_sks`
--

DROP TABLE IF EXISTS `tb_bukti_sks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tb_bukti_sks` (
  `id_bukti_sks` int(11) NOT NULL AUTO_INCREMENT,
  `id_karyawan` int(11) NOT NULL,
  `tgl_sks` date NOT NULL,
  `bukti_sks` varchar(100) NOT NULL,
  `ket_sks` text DEFAULT NULL,
  `tgl_kirim` timestamp NOT NULL DEFAULT current_timestamp(),
  `tgl_diterima` timestamp NULL DEFAULT NULL,
  `status` enum('Pending','Diterima','Ditolak') NOT NULL,
  `ket_admin` text DEFAULT NULL,
  PRIMARY KEY (`id_bukti_sks`)
) ENGINE=InnoDB AUTO_INCREMENT=227 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tb_calon_karyawan`
--

DROP TABLE IF EXISTS `tb_calon_karyawan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tb_calon_karyawan` (
  `id_calon` int(11) NOT NULL AUTO_INCREMENT,
  `id_loker` int(11) NOT NULL,
  `nama_lengkap` varchar(150) NOT NULL,
  `jenis_kelamin` enum('L','P') NOT NULL,
  `tgl_lahir` date NOT NULL,
  `tempat_lahir` varchar(150) NOT NULL,
  `email_pribadi` varchar(150) NOT NULL,
  `no_telp` varchar(30) NOT NULL,
  `nationality` varchar(100) DEFAULT NULL,
  `agama` varchar(50) DEFAULT NULL,
  `status_pernikahan` varchar(25) DEFAULT NULL,
  `jumlah_tanggungan` int(11) DEFAULT 0,
  `nik_ktp` varchar(50) NOT NULL,
  `kk_number` varchar(50) NOT NULL,
  `npwp` varchar(50) DEFAULT NULL,
  `alamat_ktp` text NOT NULL,
  `provinsi` varchar(100) DEFAULT NULL,
  `kabupaten` varchar(100) DEFAULT NULL,
  `kecamatan` varchar(100) DEFAULT NULL,
  `desa` varchar(100) DEFAULT NULL,
  `rt_rw` varchar(20) DEFAULT NULL,
  `last_education` varchar(100) DEFAULT NULL,
  `major` varchar(150) DEFAULT NULL,
  `education_level` varchar(50) DEFAULT NULL,
  `file_cv` varchar(255) NOT NULL,
  `file_ktp` varchar(255) NOT NULL,
  `file_kk` varchar(255) NOT NULL,
  `file_ijazah` varchar(255) NOT NULL,
  `file_skck` varchar(255) DEFAULT NULL,
  `file_sio` varchar(255) DEFAULT NULL,
  `file_sim` varchar(255) DEFAULT NULL,
  `file_ak1` varchar(255) DEFAULT NULL,
  `status_seleksi` enum('Proses','Diterima','Ditolak') DEFAULT 'Proses',
  `tgl_daftar` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_calon`),
  KEY `fk_calon_loker` (`id_loker`),
  CONSTRAINT `fk_calon_loker` FOREIGN KEY (`id_loker`) REFERENCES `tb_loker` (`id_loker`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tb_cuti`
--

DROP TABLE IF EXISTS `tb_cuti`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tb_cuti` (
  `id_cuti` int(11) NOT NULL AUTO_INCREMENT,
  `id_karyawan` int(11) NOT NULL,
  `tgl_awal_cuti` date NOT NULL,
  `tgl_akhir_cuti` date NOT NULL,
  `ket_cuti` text NOT NULL,
  PRIMARY KEY (`id_cuti`),
  KEY `cuti_karyawan` (`id_karyawan`),
  CONSTRAINT `cuti_karyawan` FOREIGN KEY (`id_karyawan`) REFERENCES `tb_karyawan` (`id_karyawan`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tb_departemen`
--

DROP TABLE IF EXISTS `tb_departemen`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tb_departemen` (
  `id_departemen` int(11) NOT NULL AUTO_INCREMENT,
  `nama_departemen` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id_departemen`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tb_identitas`
--

DROP TABLE IF EXISTS `tb_identitas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tb_identitas` (
  `id_identitas` int(11) NOT NULL AUTO_INCREMENT,
  `id_karyawan` int(11) DEFAULT NULL,
  `nik_ktp` varchar(50) DEFAULT NULL,
  `kk_number` varchar(50) DEFAULT NULL,
  `npwp` varchar(50) DEFAULT NULL,
  `bpjs_tk` varchar(50) DEFAULT NULL,
  `bpjs_kes` varchar(50) DEFAULT NULL,
  `alamat_ktp` text DEFAULT NULL,
  `provinsi` varchar(100) DEFAULT NULL,
  `kabupaten` varchar(100) DEFAULT NULL,
  `kecamatan` varchar(100) DEFAULT NULL,
  `desa` varchar(100) DEFAULT NULL,
  `rt_rw` varchar(20) DEFAULT NULL,
  `nama_ibu` varchar(150) DEFAULT NULL,
  `last_education` varchar(100) DEFAULT NULL,
  `major` varchar(150) DEFAULT NULL,
  `education_level` varchar(50) DEFAULT NULL,
  `nama_bank` varchar(25) NOT NULL,
  `no_rek` varchar(25) NOT NULL,
  `atas_nama_rek` varchar(35) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_identitas`),
  KEY `id_karyawan` (`id_karyawan`),
  CONSTRAINT `tb_identitas_ibfk_1` FOREIGN KEY (`id_karyawan`) REFERENCES `tb_karyawan` (`id_karyawan`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4536 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tb_jabatan`
--

DROP TABLE IF EXISTS `tb_jabatan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tb_jabatan` (
  `id_jabatan` int(11) NOT NULL AUTO_INCREMENT,
  `nama_jabatan` varchar(150) DEFAULT NULL,
  PRIMARY KEY (`id_jabatan`)
) ENGINE=InnoDB AUTO_INCREMENT=92 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tb_jatah_cuti`
--

DROP TABLE IF EXISTS `tb_jatah_cuti`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tb_jatah_cuti` (
  `id_jatah` int(11) NOT NULL AUTO_INCREMENT,
  `id_karyawan` int(11) NOT NULL,
  `tahun` year(4) NOT NULL,
  `sisa_cuti` int(11) NOT NULL DEFAULT 12,
  PRIMARY KEY (`id_jatah`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tb_karier`
--

DROP TABLE IF EXISTS `tb_karier`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tb_karier` (
  `id_karier` int(11) NOT NULL AUTO_INCREMENT,
  `id_karyawan` int(11) DEFAULT NULL,
  `id_site` int(11) DEFAULT NULL,
  `id_departemen` int(11) DEFAULT NULL,
  `id_jabatan` int(11) DEFAULT NULL,
  `gaji_pokok` varchar(25) DEFAULT NULL,
  `lembur` varchar(25) DEFAULT NULL,
  `ot_pertama` varchar(25) DEFAULT NULL,
  `ot_kedua` varchar(25) DEFAULT NULL,
  `spl` varchar(25) NOT NULL,
  `ot1_spl` varchar(25) NOT NULL,
  `ot2_spl` varchar(25) NOT NULL,
  `pot_izin` varchar(25) NOT NULL,
  `pot_mangkir` varchar(25) NOT NULL,
  `tgl_mulai` date DEFAULT NULL,
  `tgl_selesai` date DEFAULT NULL,
  `file_kontrak` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_karier`),
  KEY `tb_karier_ibfk_1` (`id_karyawan`),
  KEY `tb_karier_ibfk_2` (`id_site`),
  KEY `tb_karier_ibfk_4` (`id_departemen`),
  KEY `tb_karier_ibfk_6` (`id_jabatan`),
  CONSTRAINT `tb_karier_ibfk_1` FOREIGN KEY (`id_karyawan`) REFERENCES `tb_karyawan` (`id_karyawan`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `tb_karier_ibfk_2` FOREIGN KEY (`id_site`) REFERENCES `tb_site` (`id_site`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `tb_karier_ibfk_4` FOREIGN KEY (`id_departemen`) REFERENCES `tb_departemen` (`id_departemen`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `tb_karier_ibfk_6` FOREIGN KEY (`id_jabatan`) REFERENCES `tb_jabatan` (`id_jabatan`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3555 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tb_karyawan`
--

DROP TABLE IF EXISTS `tb_karyawan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tb_karyawan` (
  `id_karyawan` int(11) NOT NULL AUTO_INCREMENT,
  `nik_karyawan` varchar(30) DEFAULT NULL,
  `nama_lengkap` varchar(150) DEFAULT NULL,
  `jenis_kelamin` enum('L','P') DEFAULT NULL,
  `tgl_lahir` date DEFAULT NULL,
  `tempat_lahir` varchar(150) DEFAULT NULL,
  `email_pribadi` varchar(150) DEFAULT NULL,
  `no_telp` varchar(30) DEFAULT NULL,
  `nationality` varchar(100) DEFAULT NULL,
  `agama` varchar(50) DEFAULT NULL,
  `status_pernikahan` varchar(25) DEFAULT NULL,
  `status_ptkp` varchar(7) DEFAULT NULL,
  `status_karyawan` enum('Aktif','Resign','Cuti','Habis Kontrak','Pensiun','PHK','Black List') DEFAULT 'Aktif',
  `password` varchar(100) NOT NULL,
  `foto` varchar(225) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_karyawan`),
  UNIQUE KEY `nik_karyawan` (`nik_karyawan`)
) ENGINE=InnoDB AUTO_INCREMENT=8899 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tb_karyawan_internal`
--

DROP TABLE IF EXISTS `tb_karyawan_internal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tb_karyawan_internal` (
  `id_karyawan_internal` int(11) NOT NULL AUTO_INCREMENT,
  `nik_karyawan_internal` varchar(30) NOT NULL,
  `jenis_kelamin` enum('L','P') DEFAULT NULL,
  `id_site` int(11) DEFAULT NULL,
  `nama_karyawan` varchar(50) NOT NULL,
  `jabatan` varchar(50) NOT NULL,
  `gaji_pokok` varchar(25) NOT NULL,
  `nama_bank` varchar(25) DEFAULT NULL,
  `no_rek` varchar(25) DEFAULT NULL,
  `atas_nama_rek` varchar(50) DEFAULT NULL,
  `status_karyawan` varchar(30) DEFAULT 'Aktif',
  `tgl_masuk` date DEFAULT NULL,
  `tgl_keluar` date DEFAULT NULL,
  `ket_keluar` text DEFAULT NULL,
  PRIMARY KEY (`id_karyawan_internal`),
  UNIQUE KEY `idx_nik_internal` (`nik_karyawan_internal`),
  KEY `fk_karyawan_internal_site` (`id_site`),
  CONSTRAINT `fk_karyawan_internal_site` FOREIGN KEY (`id_site`) REFERENCES `tb_site` (`id_site`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tb_kategori_keuangan`
--

DROP TABLE IF EXISTS `tb_kategori_keuangan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tb_kategori_keuangan` (
  `id_kategori` int(11) NOT NULL AUTO_INCREMENT,
  `kode_akun` varchar(10) NOT NULL,
  `nama_akun` varchar(100) NOT NULL,
  `kelompok` enum('Aset','Kewajiban','Ekuitas','Pendapatan','Beban') NOT NULL,
  PRIMARY KEY (`id_kategori`)
) ENGINE=InnoDB AUTO_INCREMENT=90 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tb_kategori_pengeluaran`
--

DROP TABLE IF EXISTS `tb_kategori_pengeluaran`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tb_kategori_pengeluaran` (
  `id_kategori_pengeluaran` int(11) NOT NULL AUTO_INCREMENT,
  `nama_kategori` varchar(100) NOT NULL,
  PRIMARY KEY (`id_kategori_pengeluaran`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tb_kontak_darurat`
--

DROP TABLE IF EXISTS `tb_kontak_darurat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tb_kontak_darurat` (
  `id_kontak` int(11) NOT NULL AUTO_INCREMENT,
  `id_karyawan` int(11) DEFAULT NULL,
  `nama_kontak` varchar(150) DEFAULT NULL,
  `hubungan` varchar(50) DEFAULT NULL,
  `no_telp_darurat` varchar(30) DEFAULT NULL,
  `nomor_urut` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`id_kontak`),
  KEY `id_karyawan` (`id_karyawan`),
  CONSTRAINT `tb_kontak_darurat_ibfk_1` FOREIGN KEY (`id_karyawan`) REFERENCES `tb_karyawan` (`id_karyawan`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5493 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tb_libur`
--

DROP TABLE IF EXISTS `tb_libur`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tb_libur` (
  `id_libur` int(11) NOT NULL AUTO_INCREMENT,
  `tgl_awal_libur` date NOT NULL,
  `tgl_akhir_libur` date NOT NULL,
  `ket_libur` text NOT NULL,
  PRIMARY KEY (`id_libur`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tb_loker`
--

DROP TABLE IF EXISTS `tb_loker`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tb_loker` (
  `id_loker` int(11) NOT NULL AUTO_INCREMENT,
  `id_jabatan` int(11) NOT NULL,
  `status_loker` enum('Aktif','Nonaktif') DEFAULT 'Aktif',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_loker`),
  KEY `fk_loker_jabatan` (`id_jabatan`),
  CONSTRAINT `fk_loker_jabatan` FOREIGN KEY (`id_jabatan`) REFERENCES `tb_jabatan` (`id_jabatan`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tb_pengajuan_apd`
--

DROP TABLE IF EXISTS `tb_pengajuan_apd`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tb_pengajuan_apd` (
  `id_pengajuan_apd` int(11) NOT NULL AUTO_INCREMENT,
  `id_karyawan` int(11) NOT NULL,
  `id_apd` int(11) NOT NULL,
  `jumlah` int(11) NOT NULL DEFAULT 1,
  `tgl_pengajuan` datetime NOT NULL,
  `alasan` text NOT NULL,
  `bukti_rusak` varchar(255) DEFAULT NULL COMMENT 'Bisa NULL karena opsional',
  `status_pengajuan` enum('pending','disetujui','ditolak') NOT NULL DEFAULT 'pending',
  `ket_admin` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_pengajuan_apd`),
  KEY `fk_pengajuan_karyawan` (`id_karyawan`),
  KEY `fk_pengajuan_apd` (`id_apd`),
  CONSTRAINT `fk_pengajuan_apd` FOREIGN KEY (`id_apd`) REFERENCES `master_apd` (`id_apd`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_pengajuan_karyawan` FOREIGN KEY (`id_karyawan`) REFERENCES `tb_karyawan` (`id_karyawan`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=50 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tb_pengajuan_cuti`
--

DROP TABLE IF EXISTS `tb_pengajuan_cuti`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tb_pengajuan_cuti` (
  `id_pengajuan_cuti` int(11) NOT NULL AUTO_INCREMENT,
  `id_karyawan` int(11) NOT NULL,
  `tgl_awal` date NOT NULL,
  `tgl_akhir` date NOT NULL,
  `lama_cuti` int(11) NOT NULL,
  `alasan_cuti` text NOT NULL,
  `bukti_cuti` varchar(255) DEFAULT NULL,
  `status_cuti` enum('pending','disetujui','ditolak') DEFAULT 'pending',
  `ket_admin` text DEFAULT NULL,
  `waktu_update` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_pengajuan_cuti`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tb_pengajuan_izin`
--

DROP TABLE IF EXISTS `tb_pengajuan_izin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tb_pengajuan_izin` (
  `id_pengajuan_izin` int(11) NOT NULL AUTO_INCREMENT,
  `id_karyawan` varchar(50) NOT NULL,
  `tgl_izin` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `alasan_izin` text NOT NULL,
  `bukti_izin` varchar(255) DEFAULT NULL,
  `status_izin` enum('pending','disetujui','ditolak') DEFAULT 'pending',
  `ket_admin` text DEFAULT NULL,
  `waktu_update` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_pengajuan_izin`),
  KEY `idx_status` (`status_izin`),
  KEY `idx_tanggal` (`tgl_izin`)
) ENGINE=InnoDB AUTO_INCREMENT=147 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tb_pengajuan_pengeluaran`
--

DROP TABLE IF EXISTS `tb_pengajuan_pengeluaran`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tb_pengajuan_pengeluaran` (
  `id_pengajuan` int(11) NOT NULL AUTO_INCREMENT,
  `id_site` int(11) NOT NULL,
  `id_kategori_pengeluaran` int(11) NOT NULL,
  `nominal` bigint(20) NOT NULL DEFAULT 0,
  `keterangan` text DEFAULT NULL,
  `bukti_pengajuan` varchar(255) DEFAULT NULL,
  `status_pengajuan` enum('pending','disetujui','ditolak') NOT NULL DEFAULT 'pending',
  `bukti_transfer` varchar(255) DEFAULT NULL,
  `catatan_direktur` text DEFAULT NULL,
  `tgl_pengajuan` datetime NOT NULL DEFAULT current_timestamp(),
  `tgl_approval` datetime DEFAULT NULL,
  PRIMARY KEY (`id_pengajuan`),
  KEY `idx_site` (`id_site`),
  KEY `idx_kategori` (`id_kategori_pengeluaran`),
  KEY `idx_status` (`status_pengajuan`),
  CONSTRAINT `fk_pp_kategori` FOREIGN KEY (`id_kategori_pengeluaran`) REFERENCES `tb_kategori_pengeluaran` (`id_kategori_pengeluaran`) ON UPDATE CASCADE,
  CONSTRAINT `fk_pp_site` FOREIGN KEY (`id_site`) REFERENCES `tb_site` (`id_site`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tb_pengajuan_pinjaman`
--

DROP TABLE IF EXISTS `tb_pengajuan_pinjaman`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tb_pengajuan_pinjaman` (
  `id_pengajuan_pinjaman` int(11) NOT NULL AUTO_INCREMENT,
  `id_karyawan` int(11) NOT NULL,
  `tgl_pengajuan` datetime NOT NULL,
  `nominal_pinjaman` int(11) NOT NULL,
  `alasan_pinjaman` text NOT NULL,
  `foto_wajah` varchar(255) NOT NULL,
  `foto_id_card` varchar(255) NOT NULL,
  `status_pinjaman` enum('pending','disetujui','dicairkan','ditolak','lunas') NOT NULL DEFAULT 'pending',
  `keterangan_admin` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_pengajuan_pinjaman`)
) ENGINE=InnoDB AUTO_INCREMENT=752 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tb_pengaturan`
--

DROP TABLE IF EXISTS `tb_pengaturan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tb_pengaturan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_pengaturan` varchar(50) DEFAULT NULL,
  `nilai` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nama_pengaturan` (`nama_pengaturan`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tb_pengumuman`
--

DROP TABLE IF EXISTS `tb_pengumuman`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tb_pengumuman` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `judul` varchar(255) NOT NULL,
  `sub_judul` text NOT NULL,
  `detail_konten` text DEFAULT NULL,
  `waktu_buat` datetime DEFAULT current_timestamp(),
  `is_dibaca` tinyint(1) DEFAULT 0,
  `ikon_nama` varchar(50) NOT NULL,
  `warna_kode` varchar(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tb_pinjaman`
--

DROP TABLE IF EXISTS `tb_pinjaman`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tb_pinjaman` (
  `id_pinjaman` int(11) NOT NULL AUTO_INCREMENT,
  `id_karyawan` int(11) NOT NULL,
  `id_pengajuan_pinjaman` int(11) DEFAULT NULL,
  `nominal_pinjaman` varchar(25) NOT NULL,
  `tgl_pinjaman` date NOT NULL,
  `tgl_bayar_pinjaman` date NOT NULL,
  `ket_pinjaman` text NOT NULL,
  PRIMARY KEY (`id_pinjaman`),
  KEY `relasi_pinjaman_karyawan` (`id_karyawan`),
  KEY `fk_pinjaman_pengajuan` (`id_pengajuan_pinjaman`),
  CONSTRAINT `fk_pinjaman_pengajuan` FOREIGN KEY (`id_pengajuan_pinjaman`) REFERENCES `tb_pengajuan_pinjaman` (`id_pengajuan_pinjaman`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `relasi_pinjaman_karyawan` FOREIGN KEY (`id_karyawan`) REFERENCES `tb_karyawan` (`id_karyawan`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2073 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tb_pinjaman_internal`
--

DROP TABLE IF EXISTS `tb_pinjaman_internal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tb_pinjaman_internal` (
  `id_pinjaman` int(11) NOT NULL AUTO_INCREMENT,
  `id_karyawan_internal` int(11) NOT NULL,
  `tgl_pinjaman` date NOT NULL,
  `tgl_bayar_pinjaman` date NOT NULL,
  `nominal_pinjaman` double NOT NULL,
  `ket_pinjaman` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_pinjaman`),
  KEY `fk_pinjaman_internal_karyawan` (`id_karyawan_internal`),
  CONSTRAINT `fk_pinjaman_internal_karyawan` FOREIGN KEY (`id_karyawan_internal`) REFERENCES `tb_karyawan_internal` (`id_karyawan_internal`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tb_potongan`
--

DROP TABLE IF EXISTS `tb_potongan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tb_potongan` (
  `id_potongan` int(11) NOT NULL AUTO_INCREMENT,
  `id_karier` int(11) NOT NULL,
  `jenis_potongan` varchar(25) NOT NULL,
  `jumlah_potongan` varchar(25) NOT NULL,
  PRIMARY KEY (`id_potongan`),
  KEY `relasi_potongan_karyawan` (`id_karier`) USING BTREE,
  CONSTRAINT `relasi_potongan_karyawan` FOREIGN KEY (`id_karier`) REFERENCES `tb_karier` (`id_karier`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=39206 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tb_potongan_internal`
--

DROP TABLE IF EXISTS `tb_potongan_internal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tb_potongan_internal` (
  `id_potongan` int(11) NOT NULL AUTO_INCREMENT,
  `id_karyawan_internal` int(11) NOT NULL,
  `tgl_potongan` date NOT NULL,
  `nominal` double NOT NULL DEFAULT 0,
  `ket_potongan` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_potongan`),
  KEY `fk_potongan_karyawan_int` (`id_karyawan_internal`),
  CONSTRAINT `fk_potongan_karyawan_int` FOREIGN KEY (`id_karyawan_internal`) REFERENCES `tb_karyawan_internal` (`id_karyawan_internal`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tb_potongan_tidak_tetap`
--

DROP TABLE IF EXISTS `tb_potongan_tidak_tetap`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tb_potongan_tidak_tetap` (
  `id_potongan_tidak_tetap` int(11) NOT NULL AUTO_INCREMENT,
  `id_karyawan` int(11) NOT NULL,
  `tgl_potongan` date NOT NULL,
  `nominal_potongan` decimal(15,2) NOT NULL,
  `jenis_potongan` varchar(100) NOT NULL,
  `ket_potongan` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_potongan_tidak_tetap`)
) ENGINE=InnoDB AUTO_INCREMENT=1094 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tb_priode`
--

DROP TABLE IF EXISTS `tb_priode`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tb_priode` (
  `id_priode` int(11) NOT NULL AUTO_INCREMENT,
  `tgl_mulai` date NOT NULL,
  `tgl_selesai` date NOT NULL,
  `status_priode` enum('Open','Closed') NOT NULL,
  `type_priode` enum('absen','slip gaji') NOT NULL,
  PRIMARY KEY (`id_priode`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tb_rapel`
--

DROP TABLE IF EXISTS `tb_rapel`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tb_rapel` (
  `id_rapel` int(11) NOT NULL AUTO_INCREMENT,
  `id_karyawan` int(11) NOT NULL,
  `tgl_rapel` date NOT NULL,
  `nominal_rapel` varchar(11) NOT NULL,
  `ket_rapel` varchar(25) NOT NULL,
  PRIMARY KEY (`id_rapel`)
) ENGINE=InnoDB AUTO_INCREMENT=2837 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tb_rapel_internal`
--

DROP TABLE IF EXISTS `tb_rapel_internal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tb_rapel_internal` (
  `id_rapel` int(11) NOT NULL AUTO_INCREMENT,
  `id_karyawan_internal` int(11) NOT NULL,
  `tgl_rapel` date NOT NULL,
  `nominal` double NOT NULL DEFAULT 0,
  `ket_rapel` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_rapel`),
  KEY `fk_rapel_karyawan_int` (`id_karyawan_internal`),
  CONSTRAINT `fk_rapel_karyawan_int` FOREIGN KEY (`id_karyawan_internal`) REFERENCES `tb_karyawan_internal` (`id_karyawan_internal`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tb_reset_otp`
--

DROP TABLE IF EXISTS `tb_reset_otp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tb_reset_otp` (
  `id_otp` int(11) NOT NULL AUTO_INCREMENT,
  `id_karyawan` int(11) NOT NULL,
  `otp_code` varchar(6) NOT NULL,
  `expired_at` datetime NOT NULL,
  `is_used` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_otp`),
  KEY `id_karyawan` (`id_karyawan`),
  CONSTRAINT `tb_reset_otp_ibfk_1` FOREIGN KEY (`id_karyawan`) REFERENCES `tb_karyawan` (`id_karyawan`)
) ENGINE=InnoDB AUTO_INCREMENT=969 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tb_sakit`
--

DROP TABLE IF EXISTS `tb_sakit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tb_sakit` (
  `id_sakit` int(11) NOT NULL AUTO_INCREMENT,
  `id_karyawan` int(11) NOT NULL,
  `tgl_awal_sakit` date NOT NULL,
  `tgl_akhir_sakit` date NOT NULL,
  `foto_sakit` varchar(50) NOT NULL,
  `ket_sakit` varchar(11) NOT NULL,
  `status_sakit` enum('pending','disetujui','ditolak') NOT NULL DEFAULT 'pending',
  PRIMARY KEY (`id_sakit`),
  KEY `relasi_sakit_karyawan` (`id_karyawan`),
  CONSTRAINT `relasi_sakit_karyawan` FOREIGN KEY (`id_karyawan`) REFERENCES `tb_karyawan` (`id_karyawan`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=273 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tb_site`
--

DROP TABLE IF EXISTS `tb_site`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tb_site` (
  `id_site` int(11) NOT NULL AUTO_INCREMENT,
  `nama_site` varchar(150) DEFAULT NULL,
  `lokasi` varchar(150) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_site`),
  UNIQUE KEY `nama_site` (`nama_site`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tb_surat_peringatan`
--

DROP TABLE IF EXISTS `tb_surat_peringatan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tb_surat_peringatan` (
  `id_sp` int(11) NOT NULL AUTO_INCREMENT,
  `no_surat` varchar(100) NOT NULL,
  `id_karyawan` int(11) NOT NULL,
  `id_pelanggaran` int(11) NOT NULL,
  `jenis_sanksi` varchar(50) DEFAULT NULL,
  `masa_berlaku_bulan` int(11) DEFAULT NULL,
  `tgl_surat` date NOT NULL,
  `tgl_berakhir` date DEFAULT NULL,
  `keterangan_tambahan` text DEFAULT NULL,
  `status_surat` varchar(20) DEFAULT 'Aktif',
  `id_user_admin` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_sp`),
  KEY `id_karyawan` (`id_karyawan`),
  KEY `id_pelanggaran` (`id_pelanggaran`),
  CONSTRAINT `tb_surat_peringatan_ibfk_1` FOREIGN KEY (`id_karyawan`) REFERENCES `tb_karyawan` (`id_karyawan`) ON DELETE CASCADE,
  CONSTRAINT `tb_surat_peringatan_ibfk_2` FOREIGN KEY (`id_pelanggaran`) REFERENCES `m_jenis_pelanggaran` (`id_pelanggaran`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=81 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tb_tereliminasi`
--

DROP TABLE IF EXISTS `tb_tereliminasi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tb_tereliminasi` (
  `id_tereliminasi` int(11) NOT NULL AUTO_INCREMENT,
  `id_karyawan` int(11) NOT NULL,
  `tgl_keluar` date NOT NULL,
  `id_jenis_keluar` int(11) NOT NULL,
  `alasan_keluar` text DEFAULT NULL,
  `created_at` timestamp NOT NULL,
  PRIMARY KEY (`id_tereliminasi`)
) ENGINE=InnoDB AUTO_INCREMENT=439 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tb_thr`
--

DROP TABLE IF EXISTS `tb_thr`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tb_thr` (
  `id_thr` int(11) NOT NULL AUTO_INCREMENT,
  `id_karyawan` int(11) NOT NULL,
  `periode` year(4) NOT NULL,
  `tgl_mulai` date NOT NULL,
  `tgl_hari_raya` date NOT NULL,
  `gaji_pokok` varchar(25) NOT NULL,
  `tunjangan_lokasi` varchar(25) NOT NULL,
  `tunjangan_kebutuhan` varchar(25) NOT NULL,
  `masa_kerja` varchar(5) NOT NULL,
  `nominal_thr` varchar(25) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_thr`),
  KEY `id_karyawan` (`id_karyawan`),
  CONSTRAINT `tb_thr_ibfk_1` FOREIGN KEY (`id_karyawan`) REFERENCES `tb_karyawan` (`id_karyawan`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=571 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tb_transaksi_keuangan`
--

DROP TABLE IF EXISTS `tb_transaksi_keuangan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tb_transaksi_keuangan` (
  `id_transaksi` int(11) NOT NULL AUTO_INCREMENT,
  `kode_referensi` varchar(50) DEFAULT NULL,
  `tanggal_transaksi` date NOT NULL,
  `id_kategori` int(11) NOT NULL,
  `nominal` decimal(15,2) NOT NULL,
  `tipe_arus` enum('Masuk','Keluar') NOT NULL,
  `tipe_neraca` enum('debit','kredit') DEFAULT NULL,
  `bukti_transaksi` varchar(225) DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_transaksi`),
  KEY `id_kategori` (`id_kategori`),
  CONSTRAINT `tb_transaksi_keuangan_ibfk_1` FOREIGN KEY (`id_kategori`) REFERENCES `tb_kategori_keuangan` (`id_kategori`)
) ENGINE=InnoDB AUTO_INCREMENT=1127 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tb_tunjangan`
--

DROP TABLE IF EXISTS `tb_tunjangan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tb_tunjangan` (
  `id_tunjangan` int(11) NOT NULL AUTO_INCREMENT,
  `id_karier` int(11) NOT NULL,
  `nama_tunjangan` varchar(25) NOT NULL,
  `jumlah_tunjangan` varchar(25) NOT NULL,
  PRIMARY KEY (`id_tunjangan`),
  KEY `relasi_tunjangan_karyawan` (`id_karier`),
  CONSTRAINT `relasi_tunjangan_karyawan` FOREIGN KEY (`id_karier`) REFERENCES `tb_karier` (`id_karier`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=77644 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tb_user`
--

DROP TABLE IF EXISTS `tb_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tb_user` (
  `id_user` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(25) NOT NULL,
  `password` varchar(100) NOT NULL,
  `nama_user` varchar(25) NOT NULL,
  `jabatan` varchar(25) NOT NULL,
  `foto` varchar(100) NOT NULL,
  `site` int(11) NOT NULL,
  `level` int(11) NOT NULL,
  PRIMARY KEY (`id_user`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tb_user_token`
--

DROP TABLE IF EXISTS `tb_user_token`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tb_user_token` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL,
  `selector` char(12) NOT NULL,
  `validator_hash` char(64) NOT NULL,
  `expires` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_user` (`id_user`),
  CONSTRAINT `tb_user_token_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `tb_user` (`id_user`)
) ENGINE=InnoDB AUTO_INCREMENT=122 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*M!100616 SET NOTE_VERBOSITY=@OLD_NOTE_VERBOSITY */;

-- Dump completed on 2026-09-11 11:19:14
