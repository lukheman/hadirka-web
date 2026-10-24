/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.20-13.0.2-MariaDB, for Linux (x86_64)
--
-- Host: localhost    Database: ipm_hris
-- ------------------------------------------------------
-- Server version	13.0.2-MariaDB

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
-- Dumping data for table `footer_shortcuts`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `footer_shortcuts` WRITE;
/*!40000 ALTER TABLE `footer_shortcuts` DISABLE KEYS */;
INSERT INTO `footer_shortcuts` VALUES
(1,'Bantuan','help_outline','whatsapp','6285796548372','#2196F3',1,1),
(2,'Kontak HR','contact_phone','whatsapp','6287751792776','#4CAF50',2,1),
(3,'Saran','feedback_outlined','whatsapp','6285796548372','#FF9800',3,1);
/*!40000 ALTER TABLE `footer_shortcuts` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

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
-- Dumping data for table `m_jenis_keluar`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `m_jenis_keluar` WRITE;
/*!40000 ALTER TABLE `m_jenis_keluar` DISABLE KEYS */;
INSERT INTO `m_jenis_keluar` VALUES
(1,'Habis Kontrak'),
(2,'Pensiun'),
(3,'PHK'),
(4,'Resign'),
(5,'Black List');
/*!40000 ALTER TABLE `m_jenis_keluar` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

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
-- Dumping data for table `m_jenis_pelanggaran`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `m_jenis_pelanggaran` WRITE;
/*!40000 ALTER TABLE `m_jenis_pelanggaran` DISABLE KEYS */;
INSERT INTO `m_jenis_pelanggaran` VALUES
(1,'Tidak Pakai Helm / APD & Pelanggaran SOP','Tidak pakai helm di area kerja dan melakukan pelanggaran SOP (naik ke unit yang sedang diperbaiki)','2026-08-07 02:27:18'),
(2,'Mangkir Kerja (Tanpa Keterangan)','Mangkir pada tanggal [Sebutkan Tanggal]','2026-08-07 02:27:18'),
(4,'Izin Lebih Dari 3x Sebulan','Izin lebih dari 3x dalam sebulan','2026-08-07 02:27:18'),
(5,'Izin Terlalu Banyak (>= 5 Hari)','Izin terlalu banyak (5 hari)','2026-08-07 02:27:18'),
(6,'Sikap Emosional / Tidak Profesional','Terdapat tindakan melempar barang yang disertai sikap emosional (marah & berteriak) di area kerja yang tidak mencerminkan sikap profesional serta dapat mempengaruhi suasana kerja dan citra perusahaan.','2026-08-07 02:27:18'),
(7,'Pencurian / Pemalsuan Data Perusahaan','Melakukan pelanggaran berat berupa tindak pencurian, pemalsuan data, atau tindakan kriminal lainnya di area kerja yang merugikan perusahaan secara langsung.','2026-08-07 02:27:18');
/*!40000 ALTER TABLE `m_jenis_pelanggaran` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

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
-- Dumping data for table `master_apd`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `master_apd` WRITE;
/*!40000 ALTER TABLE `master_apd` DISABLE KEYS */;
INSERT INTO `master_apd` VALUES
(1,'Helm Safety ','Proteksi Kepala','Helm pelindung kepala standar proyek',365,98,'aktif','2025-12-18 06:22:37'),
(2,'Sarung Tangan Karet','Sarung Tangan','Sarung tangan anti slip',180,100,'aktif','2025-12-18 06:22:37'),
(3,'Sepatu Safety ','Lain-lain','Sepatu keselamatan dengan steel toe',365,100,'aktif','2025-12-18 06:22:37'),
(4,'Masker N95','Masker','Masker pelindung debu dan partikel',30,100,'aktif','2025-12-18 06:22:37'),
(5,'Kacamata Safety','Kacamata','Kacamata pelindung mata dari percikan',365,100,'aktif','2025-12-18 06:22:37'),
(6,'Baju APD','Pakaian Kerja','Baju keselamatan dengan strip reflektif',365,100,'aktif','2025-12-18 06:22:37'),
(7,'Ear Plug','Pelindung Telinga','Pelindung telinga dari kebisingan',90,99,'aktif','2025-12-18 06:22:37'),
(8,'Harness Full Body','Harness','Pengaman untuk bekerja di ketinggian',730,100,'aktif','2025-12-18 06:22:37');
/*!40000 ALTER TABLE `master_apd` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pengambilan_apd`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `pengambilan_apd` WRITE;
/*!40000 ALTER TABLE `pengambilan_apd` DISABLE KEYS */;
/*!40000 ALTER TABLE `pengambilan_apd` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

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
-- Dumping data for table `pengembalian_apd`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `pengembalian_apd` WRITE;
/*!40000 ALTER TABLE `pengembalian_apd` DISABLE KEYS */;
/*!40000 ALTER TABLE `pengembalian_apd` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `phinxlog`
--

DROP TABLE IF EXISTS `phinxlog`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `phinxlog` (
  `version` bigint(20) NOT NULL,
  `migration_name` varchar(100) DEFAULT NULL,
  `start_time` timestamp NULL DEFAULT NULL,
  `end_time` timestamp NULL DEFAULT NULL,
  `breakpoint` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phinxlog`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `phinxlog` WRITE;
/*!40000 ALTER TABLE `phinxlog` DISABLE KEYS */;
INSERT INTO `phinxlog` VALUES
(20260911120000,'Baseline','2026-09-16 02:29:31','2026-09-16 02:29:31',0),
(20260911140142,'CreateTbTemplateKontrak','2026-09-16 02:29:46','2026-09-16 02:29:46',0),
(20260911140709,'TemplateKontrakManyJabatan','2026-09-16 02:29:46','2026-09-16 02:29:46',0),
(20260916120000,'AddNoSuratToTbKarier','2026-09-16 02:29:46','2026-09-16 02:29:46',0),
(20260916130000,'CreateTbDetailKontrak','2026-09-16 02:29:46','2026-09-16 02:29:46',0),
(20260916140000,'LinkTunjanganPotonganToDetailKontrak','2026-09-16 02:29:46','2026-09-16 02:29:46',0),
(20260916150000,'SeparateTunjanganPotonganKontrak','2026-09-16 02:29:46','2026-09-16 02:29:46',0),
(20260916160000,'AddKodeJabatan','2026-09-16 02:29:46','2026-09-16 02:29:46',0),
(20260916170000,'NomorSuratKontrak','2026-09-16 02:29:46','2026-09-16 02:29:46',0),
(20260917000000,'AddNamaKontrakToTemplate','2026-09-16 04:53:59','2026-09-16 04:53:59',0),
(20260918000000,'RenameNamaFileToNamaAsli','2026-09-18 03:06:47','2026-09-18 03:06:48',0),
(20260922000000,'AddJenisTunjangan','2026-09-25 09:08:58','2026-09-25 09:08:58',0),
(20260923000000,'AddTampilTemplateToTunjangan','2026-09-25 09:08:58','2026-09-25 09:08:58',0),
(20260928000000,'StandardizeJenisPotongan','2026-09-28 01:18:33','2026-09-28 01:18:48',0),
(20261002000000,'DropInternalTables','2026-10-08 20:57:55','2026-10-08 20:57:55',0),
(20261012000000,'DropOvertimeColumnsKarier','2026-10-11 18:59:47','2026-10-11 18:59:47',0),
(20261017100000,'IntegrasiAbsenMobile','2026-10-16 18:19:55','2026-10-16 18:19:55',0),
(20261017200000,'SatukanTbAbsen','2026-10-16 19:00:28','2026-10-16 19:00:28',0),
(20261020000000,'DropTbSlipGaji','2026-10-19 14:01:51','2026-10-19 14:01:51',0),
(20261020100000,'AddMetodeCheckoutToTbAbsen','2026-10-19 14:01:51','2026-10-19 14:01:51',0),
(20261021100000,'AddTipeKaryawanToTbKaryawan','2026-10-20 17:29:26','2026-10-20 17:29:26',0),
(20261021110000,'AddJamKerjaToTbKaryawan','2026-10-20 17:29:26','2026-10-20 17:29:26',0),
(20261021120000,'CreateTbTipeKaryawan','2026-10-20 17:30:28','2026-10-20 17:30:28',0),
(20261021130000,'AddIdTipeKaryawanToTbKaryawan','2026-10-20 17:30:28','2026-10-20 17:30:28',0),
(20261021140000,'DropStatusFromTbTipeKaryawan','2026-10-20 18:03:36','2026-10-20 18:03:36',0);
/*!40000 ALTER TABLE `phinxlog` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

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
  `sumber` varchar(10) NOT NULL DEFAULT 'web' COMMENT 'web = input HRD/CSV; mobile = absen aplikasi',
  `id_lokasi` int(11) DEFAULT NULL COMMENT 'Site terdekat saat absen (tb_site.id_site)',
  `nama_lokasi` varchar(100) DEFAULT NULL,
  `latitude` double DEFAULT NULL,
  `longitude` double DEFAULT NULL,
  `jarak_meter` double DEFAULT NULL COMMENT 'Jarak ke titik site (meter)',
  `foto_selfie_path` text DEFAULT NULL,
  `waktu_masuk` varchar(20) DEFAULT NULL COMMENT 'Format: HH:MM WITA (dari aplikasi)',
  `waktu_keluar` varchar(20) DEFAULT NULL,
  `metode_checkout` varchar(20) DEFAULT NULL COMMENT 'manual atau otomatis; NULL bila belum checkout',
  `status_kehadiran` varchar(50) DEFAULT NULL COMMENT 'Tepat Waktu / Terlambat {n}m / Sudah Selesai',
  `keterlambatan_menit` int(11) NOT NULL DEFAULT 0,
  `is_tepat_waktu` tinyint(1) NOT NULL DEFAULT 1,
  `dibuat_pada` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_absen`),
  KEY `relasi_absen_karyawan` (`id_karyawan`),
  KEY `fk_absen_lokasi` (`id_lokasi`),
  CONSTRAINT `fk_absen_lokasi` FOREIGN KEY (`id_lokasi`) REFERENCES `tb_site` (`id_site`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `relasi_absen_karyawan` FOREIGN KEY (`id_karyawan`) REFERENCES `tb_karyawan` (`id_karyawan`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7364 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tb_absen`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `tb_absen` WRITE;
/*!40000 ALTER TABLE `tb_absen` DISABLE KEYS */;
INSERT INTO `tb_absen` VALUES
(7363,56,'2026-10-20','1','HADIR','mobile',29,'kos akmal',-4.3770656,121.5552011,9.22,'uploads/absensi/absen_20261020_061358_IPM-00002_a08327.jpg','06:13 WITA',NULL,NULL,'Tepat Waktu',0,1,'2026-10-20 06:13:58');
/*!40000 ALTER TABLE `tb_absen` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

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
-- Dumping data for table `tb_apd`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `tb_apd` WRITE;
/*!40000 ALTER TABLE `tb_apd` DISABLE KEYS */;
/*!40000 ALTER TABLE `tb_apd` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tb_bukti_sks`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `tb_bukti_sks` WRITE;
/*!40000 ALTER TABLE `tb_bukti_sks` DISABLE KEYS */;
/*!40000 ALTER TABLE `tb_bukti_sks` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

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
-- Dumping data for table `tb_calon_karyawan`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `tb_calon_karyawan` WRITE;
/*!40000 ALTER TABLE `tb_calon_karyawan` DISABLE KEYS */;
/*!40000 ALTER TABLE `tb_calon_karyawan` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tb_cuti`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `tb_cuti` WRITE;
/*!40000 ALTER TABLE `tb_cuti` DISABLE KEYS */;
/*!40000 ALTER TABLE `tb_cuti` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

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
-- Dumping data for table `tb_departemen`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `tb_departemen` WRITE;
/*!40000 ALTER TABLE `tb_departemen` DISABLE KEYS */;
/*!40000 ALTER TABLE `tb_departemen` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `tb_detail_kontrak`
--

DROP TABLE IF EXISTS `tb_detail_kontrak`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tb_detail_kontrak` (
  `id_detail` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_template` int(11) unsigned NOT NULL COMMENT 'Relasi ke tb_template_kontrak.id_template',
  `gaji_pokok` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Gaji pokok pada kontrak',
  `upah_lembur` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Upah lembur pada kontrak',
  `potongan_izin` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Potongan izin pada kontrak',
  `potongan_mangkir` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Potongan mangkir pada kontrak',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_detail`),
  UNIQUE KEY `uq_detail_template` (`id_template`),
  CONSTRAINT `fk_detail_template` FOREIGN KEY (`id_template`) REFERENCES `tb_template_kontrak` (`id_template`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Nilai kontrak per template (satu template satu detail)';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tb_detail_kontrak`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `tb_detail_kontrak` WRITE;
/*!40000 ALTER TABLE `tb_detail_kontrak` DISABLE KEYS */;
INSERT INTO `tb_detail_kontrak` VALUES
(1,1,3188130.00,21318.67,147525.20,173525.20,'2026-09-18 03:08:36','2026-09-18 03:08:36'),
(5,5,3200000.00,21387.28,148000.00,180000.00,'2026-09-25 09:13:19','2026-09-25 09:13:19');
/*!40000 ALTER TABLE `tb_detail_kontrak` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=98 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tb_identitas`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `tb_identitas` WRITE;
/*!40000 ALTER TABLE `tb_identitas` DISABLE KEYS */;
INSERT INTO `tb_identitas` VALUES
(55,55,'IPM-00001','','','','','Jakarta','DKI Jakarta','Jakarta Pusat','Senen','Senen','001/001','Ibu','S1','Informatika','S1','BRI','215001018450501','IRSAN','2026-10-19 20:51:39'),
(56,56,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'BRI','510101013363539','HANIAH','2026-10-16 13:31:16'),
(57,57,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'BRI','510101015615500','HAKRIADI','2026-10-16 13:31:16'),
(58,58,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'BRI','510101013363539','HANIAH','2026-10-16 13:31:16'),
(59,59,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'BRI','510101013363539','HANIAH','2026-10-16 13:31:16'),
(60,60,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'BRI','510101013363539','HANIAH','2026-10-16 13:31:16'),
(61,61,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'','','','2026-10-16 13:31:16'),
(62,62,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'','','','2026-10-16 13:31:16'),
(63,63,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Mandiri','1600003103484','JUSRY BONE','2026-10-16 13:31:16'),
(64,64,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'BRI','351301035979535','HARDIYANTI','2026-10-16 13:31:16'),
(65,65,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'','','','2026-10-16 13:31:16'),
(66,66,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'BRI','205201019212508','CHAIRIL ANWAR','2026-10-16 13:31:16'),
(67,67,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'BNI','2051700677','MARSYA SALSABILA','2026-10-16 13:31:16'),
(68,68,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Mandiri','1600003103484','JUSRY BONE','2026-10-16 13:31:16'),
(69,69,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'BRI','493301029654532','ZULKIFLI PRATAMA','2026-10-16 13:31:16'),
(70,70,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'BRI','493301049267535','RESKY AWALIA','2026-10-16 13:31:16'),
(71,71,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'BRI','215001015887505','RANDI EKO JULIANTO','2026-10-16 13:31:16'),
(72,72,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'BRI','021601019599532','JEFRI','2026-10-16 13:31:16'),
(73,73,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'BRI','351301016531504','FERIL SAPUTRA','2026-10-16 13:31:16'),
(74,74,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'BRI','507101033630537','AMBO TUWO','2026-10-16 13:31:16'),
(75,75,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'BRI','351301016531504','FERIL SAPUTRA','2026-10-16 13:31:16'),
(76,76,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'BRI','021601018587532','SUPANDI','2026-10-16 13:31:16'),
(77,77,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'BRI','351301065636539','RUSDI','2026-10-16 13:31:16'),
(78,78,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'BRI','351301034724539','ADRIANI LANTONG','2026-10-16 13:31:16'),
(79,79,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'BRI','215001021633504','AKIB','2026-10-16 13:31:16'),
(80,80,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'BRI','351301055624534','ASMANTO','2026-10-16 13:31:16'),
(81,81,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Seabank','901268850307','MUH FADEL AZARI','2026-10-16 13:31:16'),
(82,82,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'BRI','215001018329506','ALIF RAMADHAN','2026-10-16 13:31:16'),
(83,83,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'BRI','215001007864505','MUSLIANA ASIZ','2026-10-16 13:31:16'),
(84,84,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'BRI','351301055624534','ASMANTO','2026-10-16 13:31:16'),
(85,85,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Mandiri','1620010277618','MUH AKSAN SUBHAN','2026-10-16 13:31:16'),
(86,86,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'BRI','021601079127503','FAZRIL','2026-10-16 13:31:16'),
(87,87,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'BRI','493301017232506','PENGKI','2026-10-16 13:31:16'),
(88,88,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'BRI','215001013965501','YOYON SUBIANTO','2026-10-16 13:31:16'),
(89,89,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'BRI','215001015667507','FIRMAN','2026-10-16 13:31:16');
/*!40000 ALTER TABLE `tb_identitas` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `tb_jabatan`
--

DROP TABLE IF EXISTS `tb_jabatan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tb_jabatan` (
  `id_jabatan` int(11) NOT NULL AUTO_INCREMENT,
  `kode_jabatan` varchar(50) DEFAULT NULL COMMENT 'Kode unik jabatan (mis. JBT-001)',
  `nama_jabatan` varchar(150) DEFAULT NULL,
  PRIMARY KEY (`id_jabatan`),
  UNIQUE KEY `uq_jabatan_kode` (`kode_jabatan`)
) ENGINE=InnoDB AUTO_INCREMENT=113 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tb_jabatan`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `tb_jabatan` WRITE;
/*!40000 ALTER TABLE `tb_jabatan` DISABLE KEYS */;
INSERT INTO `tb_jabatan` VALUES
(33,'CRE','CREW'),
(108,NULL,'TK'),
(109,NULL,'HL'),
(112,NULL,'`');
/*!40000 ALTER TABLE `tb_jabatan` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tb_jatah_cuti`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `tb_jatah_cuti` WRITE;
/*!40000 ALTER TABLE `tb_jatah_cuti` DISABLE KEYS */;
/*!40000 ALTER TABLE `tb_jatah_cuti` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

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
  `tgl_mulai` date DEFAULT NULL,
  `tgl_selesai` date DEFAULT NULL,
  `file_kontrak` varchar(255) DEFAULT NULL,
  `no_surat` varchar(100) DEFAULT NULL COMMENT 'Nomor surat kontrak (mis. 344/TJS/V/2026)',
  PRIMARY KEY (`id_karier`),
  UNIQUE KEY `uq_karier_no_surat` (`no_surat`),
  KEY `tb_karier_ibfk_1` (`id_karyawan`),
  KEY `tb_karier_ibfk_2` (`id_site`),
  KEY `tb_karier_ibfk_4` (`id_departemen`),
  KEY `tb_karier_ibfk_6` (`id_jabatan`),
  CONSTRAINT `tb_karier_ibfk_1` FOREIGN KEY (`id_karyawan`) REFERENCES `tb_karyawan` (`id_karyawan`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `tb_karier_ibfk_2` FOREIGN KEY (`id_site`) REFERENCES `tb_site` (`id_site`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `tb_karier_ibfk_4` FOREIGN KEY (`id_departemen`) REFERENCES `tb_departemen` (`id_departemen`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `tb_karier_ibfk_6` FOREIGN KEY (`id_jabatan`) REFERENCES `tb_jabatan` (`id_jabatan`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=98 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tb_karier`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `tb_karier` WRITE;
/*!40000 ALTER TABLE `tb_karier` DISABLE KEYS */;
INSERT INTO `tb_karier` VALUES
(55,55,23,NULL,108,'200000',NULL,NULL,NULL,NULL),
(56,56,29,NULL,108,'180000','2026-10-18',NULL,NULL,NULL),
(57,57,23,NULL,108,'200000',NULL,NULL,NULL,NULL),
(58,58,23,NULL,109,'135000',NULL,NULL,NULL,NULL),
(59,59,23,NULL,109,'135000',NULL,NULL,NULL,NULL),
(60,60,23,NULL,109,'135000',NULL,NULL,NULL,NULL),
(61,61,23,NULL,109,'135000',NULL,NULL,NULL,NULL),
(62,62,29,NULL,109,'135000','2026-10-18',NULL,'kontrak_62_1792159762.pdf',NULL),
(63,63,23,NULL,108,'190000',NULL,NULL,NULL,NULL),
(64,64,23,NULL,108,'170000',NULL,NULL,NULL,NULL),
(65,65,23,NULL,108,'170000',NULL,NULL,NULL,NULL),
(66,66,23,NULL,109,'150000',NULL,NULL,NULL,NULL),
(67,67,23,NULL,109,'145000',NULL,NULL,NULL,NULL),
(68,68,23,NULL,109,'135000',NULL,NULL,NULL,NULL),
(69,69,23,NULL,109,'135000',NULL,NULL,NULL,NULL),
(70,70,23,NULL,109,'135000',NULL,NULL,NULL,NULL),
(71,71,23,NULL,109,'135000',NULL,NULL,NULL,NULL),
(72,72,23,NULL,109,'135000',NULL,NULL,NULL,NULL),
(73,73,23,NULL,112,'180000',NULL,NULL,NULL,NULL),
(74,74,23,NULL,108,'165000',NULL,NULL,NULL,NULL),
(75,75,23,NULL,109,'140000',NULL,NULL,NULL,NULL),
(76,76,23,NULL,109,'135000',NULL,NULL,NULL,NULL),
(77,77,23,NULL,108,'170000',NULL,NULL,NULL,NULL),
(78,78,23,NULL,108,'150000',NULL,NULL,NULL,NULL),
(79,79,23,NULL,109,'135000',NULL,NULL,NULL,NULL),
(80,80,23,NULL,109,'135000',NULL,NULL,NULL,NULL),
(81,81,23,NULL,109,'135000',NULL,NULL,NULL,NULL),
(82,82,23,NULL,109,'135000',NULL,NULL,NULL,NULL),
(83,83,23,NULL,109,'135000',NULL,NULL,NULL,NULL),
(84,84,23,NULL,109,'135000',NULL,NULL,NULL,NULL),
(85,85,23,NULL,109,'135000',NULL,NULL,NULL,NULL),
(86,86,23,NULL,109,'135000',NULL,NULL,NULL,NULL),
(87,87,23,NULL,109,'135000',NULL,NULL,NULL,NULL),
(88,88,23,NULL,109,'135000',NULL,NULL,NULL,NULL),
(89,89,23,NULL,109,'135000',NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `tb_karier` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

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
  `tipe_karyawan` enum('Kontrak','Harian') NOT NULL DEFAULT 'Kontrak' COMMENT 'Tipe karyawan: Kontrak / Harian',
  `id_tipe_karyawan` int(11) DEFAULT NULL,
  `password` varchar(100) NOT NULL,
  `foto` varchar(225) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_karyawan`),
  UNIQUE KEY `nik_karyawan` (`nik_karyawan`),
  KEY `id_tipe_karyawan` (`id_tipe_karyawan`),
  CONSTRAINT `1` FOREIGN KEY (`id_tipe_karyawan`) REFERENCES `tb_tipe_karyawan` (`id_tipe_karyawan`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=98 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tb_karyawan`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `tb_karyawan` WRITE;
/*!40000 ALTER TABLE `tb_karyawan` DISABLE KEYS */;
INSERT INTO `tb_karyawan` VALUES
(55,'IPM-00001','IRSAN','L','1990-01-01','Jakarta','irsan@ipm.local','08123456789','Indonesia','Islam','Belum Menikah','TK/0','Aktif','Kontrak',1,'ef57428631876d9291075ab0218caa53','','2026-10-21 01:30:28'),
(56,'IPM-00002','ZAENAL',NULL,NULL,NULL,NULL,'08123456789',NULL,NULL,NULL,NULL,'Aktif','Kontrak',1,'$2y$12$8IWPr2iHu/QW2uLToW3xP.TtJj7VvkIMz//BoqTaCYY8M3pDTmoWW','','2026-10-21 01:30:28'),
(57,'IPM-00003','AKRI',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Aktif','Kontrak',1,'',NULL,'2026-10-21 01:30:28'),
(58,'IPM-00004','M FAJRI',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Aktif','Kontrak',1,'',NULL,'2026-10-21 01:30:28'),
(59,'IPM-00005','IDRUZ',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Aktif','Kontrak',1,'',NULL,'2026-10-21 01:30:28'),
(60,'IPM-00006','ANDRE',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Aktif','Kontrak',1,'',NULL,'2026-10-21 01:30:28'),
(61,'IPM-00007','REHAN',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Aktif','Kontrak',1,'',NULL,'2026-10-21 01:30:28'),
(62,'IPM-00008','A RAHMAN',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Aktif','Kontrak',1,'$2y$12$3M.XL7/17wuhb6kSj4tJBOhAx2rM4J4gc9OsUyUzH5pcovpwVPKwm',NULL,'2026-10-21 01:30:28'),
(63,'IPM-00009','JUSRI',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Aktif','Kontrak',1,'',NULL,'2026-10-21 01:30:28'),
(64,'IPM-00010','GUSTI YANTO',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Aktif','Kontrak',1,'',NULL,'2026-10-21 01:30:28'),
(65,'IPM-00011','MOH ROMDON',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Aktif','Kontrak',1,'',NULL,'2026-10-21 01:30:28'),
(66,'IPM-00012','CHARIL ANWAR',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Aktif','Kontrak',1,'',NULL,'2026-10-21 01:30:28'),
(67,'IPM-00013','SALIM',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Aktif','Kontrak',1,'',NULL,'2026-10-21 01:30:28'),
(68,'IPM-00014','SURIADI/BARONG',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Aktif','Kontrak',1,'',NULL,'2026-10-21 01:30:28'),
(69,'IPM-00015','ZULKIFLI',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Aktif','Kontrak',1,'',NULL,'2026-10-21 01:30:28'),
(70,'IPM-00016','ANDIKA',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Aktif','Kontrak',1,'',NULL,'2026-10-21 01:30:28'),
(71,'IPM-00017','RANDI',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Aktif','Kontrak',1,'',NULL,'2026-10-21 01:30:28'),
(72,'IPM-00018','JEFRI',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Aktif','Kontrak',1,'',NULL,'2026-10-21 01:30:28'),
(73,'IPM-00019','BUDIANTO',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Aktif','Kontrak',1,'',NULL,'2026-10-21 01:30:28'),
(74,'IPM-00020','AMBO TUWO',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Aktif','Kontrak',1,'',NULL,'2026-10-21 01:30:28'),
(75,'IPM-00021','FERIL',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Aktif','Kontrak',1,'',NULL,'2026-10-21 01:30:28'),
(76,'IPM-00022','KUN',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Aktif','Kontrak',1,'',NULL,'2026-10-21 01:30:28'),
(77,'IPM-00023','RUSDI',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Aktif','Kontrak',1,'',NULL,'2026-10-21 01:30:28'),
(78,'IPM-00024','YUDIKA',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Aktif','Kontrak',1,'',NULL,'2026-10-21 01:30:28'),
(79,'IPM-00025','AKIB',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Aktif','Kontrak',1,'$2y$12$FyPM9horDVvK3kmz/WagSuMe3eMtAxnhF7LGC3LprItVdf7eraB0S',NULL,'2026-10-21 01:30:28'),
(80,'IPM-00026','DG SUTTE',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Aktif','Kontrak',1,'',NULL,'2026-10-21 01:30:28'),
(81,'IPM-00027','FADEL DARTA',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Aktif','Kontrak',1,'',NULL,'2026-10-21 01:30:28'),
(82,'IPM-00028','IBRAHIM',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Aktif','Kontrak',1,'',NULL,'2026-10-21 01:30:28'),
(83,'IPM-00029','JUNAEDI',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Aktif','Kontrak',1,'',NULL,'2026-10-21 01:30:28'),
(84,'IPM-00030','RIJAL',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Aktif','Kontrak',1,'',NULL,'2026-10-21 01:30:28'),
(85,'IPM-00031','AKSAN',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Aktif','Kontrak',1,'',NULL,'2026-10-21 01:30:28'),
(86,'IPM-00032','FAZRIL',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Aktif','Kontrak',1,'',NULL,'2026-10-21 01:30:28'),
(87,'IPM-00033','PENGKI',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Aktif','Kontrak',1,'',NULL,'2026-10-21 01:30:28'),
(88,'IPM-00034','YOYON SUBIANTO',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Aktif','Kontrak',1,'',NULL,'2026-10-21 01:30:28'),
(89,'IPM-00035','FIRMAN',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Aktif','Kontrak',1,'',NULL,'2026-10-21 01:30:28');
/*!40000 ALTER TABLE `tb_karyawan` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

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
-- Dumping data for table `tb_kategori_keuangan`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `tb_kategori_keuangan` WRITE;
/*!40000 ALTER TABLE `tb_kategori_keuangan` DISABLE KEYS */;
INSERT INTO `tb_kategori_keuangan` VALUES
(1,'1001','Kas','Aset'),
(2,'1002','BCA','Aset'),
(3,'1003','BRI','Aset'),
(4,'1004','Syariah Mandiri','Aset'),
(5,'1005','Mandiri','Aset'),
(6,'1101','Pengeluaran dibayar di Muka','Aset'),
(7,'1201','Persediaan Habis Pakai','Aset'),
(8,'1251','Persediaan','Aset'),
(9,'1301','Piutang Pinjaman Karyawan','Aset'),
(10,'1302','Piutang Usaha Pelanggan','Aset'),
(11,'1303','Piutang Lain-Lain','Aset'),
(12,'1501','Inventaris Kantor','Aset'),
(13,'1502','Inventaris Kendaraan','Aset'),
(14,'1503','Inventaris Elektronik','Aset'),
(15,'1504','Inventaris Tanah dan Bangunan','Aset'),
(16,'1601','Akumulasi Penyusutan Inventaris Kantor','Aset'),
(17,'1602','Akumulasi Penyusutan Inventaris Kendaraan','Aset'),
(18,'1603','Akumulasi Penyusutan Inventaris Elektronik','Aset'),
(19,'1604','Akumulasi Penyusutan Inventaris Tanah dan Bangunan','Aset'),
(20,'2001','Utang Usaha Supplier','Kewajiban'),
(21,'2002','Hutang Usaha','Kewajiban'),
(22,'2003','Uang Titipan','Kewajiban'),
(23,'2101','Pendapatan diterima di Muka','Kewajiban'),
(24,'2102','Deposit Jaminan','Kewajiban'),
(25,'2501','Hutang Modal Kerja 1','Kewajiban'),
(26,'2502','Hutang Modal Kerja 2','Kewajiban'),
(27,'2503','Hutang Modal Kerja 3','Kewajiban'),
(28,'2504','Hutang Bank','Kewajiban'),
(29,'2505','Hutang Lembaga Keuangan','Kewajiban'),
(30,'2901','Modal Pemilik','Ekuitas'),
(31,'2902','Modal Pemilik 2','Ekuitas'),
(32,'2903','Modal Lain-Lain','Ekuitas'),
(33,'2904','Prive Pemilik','Ekuitas'),
(34,'2905','Prive Pemilik 2','Ekuitas'),
(35,'2906','Laba Ditahan','Ekuitas'),
(36,'2907','Laba Tahun Berjalan','Ekuitas'),
(37,'3001','Jasa Manpower Supply di PT.HBB','Pendapatan'),
(38,'3002','Jasa Manpower Supply di IPIP','Pendapatan'),
(39,'3003','Jasa Manpower Supply di IPK','Pendapatan'),
(40,'3004','Pendapatan Lain-Lain','Pendapatan'),
(41,'3501','Biaya Supir','Beban'),
(42,'3502','Biaya Bensin','Beban'),
(43,'3503','Biaya Legalitas Perusahaan','Beban'),
(44,'3504','Biaya APD','Beban'),
(45,'3505','Biaya Seragam','Beban'),
(46,'3506','Biaya MCU Karyawan','Beban'),
(47,'4001','Beban Gaji Karyawan','Beban'),
(48,'4002','Beban Upah Tenaga Lepas','Beban'),
(49,'4003','Beban Sewa Kendaraan','Beban'),
(50,'4004','Beban ATK','Beban'),
(51,'4005','Beban Hadiah','Beban'),
(52,'4006','Beban Honor Trainer','Beban'),
(53,'4007','Beban Internet','Beban'),
(54,'4008','Beban Pembuatan Website','Beban'),
(55,'4009','Beban Konsumsi','Beban'),
(56,'4010','Beban Listrik, Telepon, Air dan WI-FI','Beban'),
(57,'4011','Beban Ongkos Kirim','Beban'),
(58,'4012','Beban Bunga','Beban'),
(59,'4013','Beban Pajak','Beban'),
(60,'4014','Beban Pelatihan','Beban'),
(61,'4015','Beban Perbaikan dan Pemeliharaan','Beban'),
(62,'4016','Beban Perjalanan Dinas','Beban'),
(63,'4017','Beban Rapat','Beban'),
(64,'4018','Beban Tunjangan Karyawan','Beban'),
(65,'4019','Beban Promosi','Beban'),
(66,'4020','Beban BPJS','Beban'),
(67,'4021','Beban Kegiatan Lain-Lain','Beban'),
(68,'4022','Beban Operasional Lain-Lain','Beban'),
(69,'4023','Beban Keuangan Lain-Lain','Beban'),
(70,'4024','Beban Penyusutan Piutang Tak Tertagih','Beban'),
(71,'4025','Beban Penyusutan Inventaris Kantor','Beban'),
(72,'4026','Beban Penyusutan Inventaris Kendaraan','Beban'),
(73,'4027','Beban Penyusutan Inventaris Elektronik','Beban'),
(74,'4028','Beban Penyusutan Inventaris Tanah dan Bangunan','Beban'),
(75,'2506','Utang Lain-Lain','Kewajiban'),
(76,'2507','Kewajiban Jangka Panjang','Kewajiban'),
(77,'4000','Beban Gaji Karyawan Internal','Beban'),
(89,'1304','Piutang Pinjaman Karyawan Internal','Aset');
/*!40000 ALTER TABLE `tb_kategori_keuangan` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tb_kontak_darurat`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `tb_kontak_darurat` WRITE;
/*!40000 ALTER TABLE `tb_kontak_darurat` DISABLE KEYS */;
/*!40000 ALTER TABLE `tb_kontak_darurat` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

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
-- Dumping data for table `tb_libur`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `tb_libur` WRITE;
/*!40000 ALTER TABLE `tb_libur` DISABLE KEYS */;
/*!40000 ALTER TABLE `tb_libur` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

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
-- Dumping data for table `tb_loker`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `tb_loker` WRITE;
/*!40000 ALTER TABLE `tb_loker` DISABLE KEYS */;
INSERT INTO `tb_loker` VALUES
(2,33,'Aktif','2026-08-06 05:48:42');
/*!40000 ALTER TABLE `tb_loker` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `tb_nomor_urut`
--

DROP TABLE IF EXISTS `tb_nomor_urut`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tb_nomor_urut` (
  `nama` varchar(50) NOT NULL COMMENT 'Nama counter, mis. kontrak',
  `terakhir` int(11) unsigned NOT NULL DEFAULT 0 COMMENT 'Nomor urut terakhir yang dipakai',
  PRIMARY KEY (`nama`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Counter nomor surat global';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tb_nomor_urut`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `tb_nomor_urut` WRITE;
/*!40000 ALTER TABLE `tb_nomor_urut` DISABLE KEYS */;
INSERT INTO `tb_nomor_urut` VALUES
('kontrak',225);
/*!40000 ALTER TABLE `tb_nomor_urut` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `tb_notifikasi`
--

DROP TABLE IF EXISTS `tb_notifikasi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tb_notifikasi` (
  `id_notifikasi` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_karyawan` int(11) NOT NULL,
  `judul` varchar(150) NOT NULL,
  `pesan` text NOT NULL,
  `kategori` varchar(50) NOT NULL DEFAULT 'presensi',
  `is_dibaca` tinyint(1) NOT NULL DEFAULT 0,
  `dibuat_pada` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_notifikasi`),
  KEY `idx_notifikasi_karyawan` (`id_karyawan`,`is_dibaca`),
  CONSTRAINT `fk_notifikasi_karyawan` FOREIGN KEY (`id_karyawan`) REFERENCES `tb_karyawan` (`id_karyawan`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=164 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Notifikasi in-app untuk aplikasi karyawan';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tb_notifikasi`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `tb_notifikasi` WRITE;
/*!40000 ALTER TABLE `tb_notifikasi` DISABLE KEYS */;
/*!40000 ALTER TABLE `tb_notifikasi` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tb_pengajuan_apd`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `tb_pengajuan_apd` WRITE;
/*!40000 ALTER TABLE `tb_pengajuan_apd` DISABLE KEYS */;
/*!40000 ALTER TABLE `tb_pengajuan_apd` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tb_pengajuan_cuti`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `tb_pengajuan_cuti` WRITE;
/*!40000 ALTER TABLE `tb_pengajuan_cuti` DISABLE KEYS */;
/*!40000 ALTER TABLE `tb_pengajuan_cuti` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tb_pengajuan_izin`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `tb_pengajuan_izin` WRITE;
/*!40000 ALTER TABLE `tb_pengajuan_izin` DISABLE KEYS */;
INSERT INTO `tb_pengajuan_izin` VALUES
(4,'56','2026-10-18 04:30:22','amakkaka (s/d 20/10/2026)',NULL,'disetujui','','2026-10-18 04:30:22'),
(5,'56','2026-10-19 22:17:20','zjs (s/d 22/10/2026)',NULL,'pending',NULL,'2026-10-19 22:17:20');
/*!40000 ALTER TABLE `tb_pengajuan_izin` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

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
  `foto_wajah` varchar(255) DEFAULT NULL,
  `foto_id_card` varchar(255) DEFAULT NULL,
  `status_pinjaman` enum('pending','disetujui','dicairkan','ditolak','lunas') NOT NULL DEFAULT 'pending',
  `keterangan_admin` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_pengajuan_pinjaman`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tb_pengajuan_pinjaman`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `tb_pengajuan_pinjaman` WRITE;
/*!40000 ALTER TABLE `tb_pengajuan_pinjaman` DISABLE KEYS */;
/*!40000 ALTER TABLE `tb_pengajuan_pinjaman` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

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
-- Dumping data for table `tb_pengaturan`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `tb_pengaturan` WRITE;
/*!40000 ALTER TABLE `tb_pengaturan` DISABLE KEYS */;
INSERT INTO `tb_pengaturan` VALUES
(1,'buka_pinjaman','0');
/*!40000 ALTER TABLE `tb_pengaturan` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

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
-- Dumping data for table `tb_pengumuman`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `tb_pengumuman` WRITE;
/*!40000 ALTER TABLE `tb_pengumuman` DISABLE KEYS */;
INSERT INTO `tb_pengumuman` VALUES
(1,'Peluncuran Resmi Aplikasi HRIS Perusahaan','Dalam rangka meningkatkan efektivitas pengelolaan sumber daya manusia serta memberikan kemudahan ...','Dalam rangka meningkatkan efektivitas pengelolaan sumber daya manusia serta memberikan kemudahan layanan bagi seluruh karyawan, perusahaan dengan bangga mengumumkan bahwa Aplikasi HRIS (Human Resource Information System) resmi diluncurkan pada tanggal 21 Desember 2025.\r\n\r\nAplikasi HRIS ini dirancang untuk mendukung proses administrasi kepegawaian secara digital, terintegrasi, dan transparan sehingga karyawan maupun manajemen dapat mengakses layanan HR dengan lebih cepat dan efisien.','2025-12-16 11:36:27',1,'verified','#4CAF50'),
(2,'Fitur Pengajuan Izin Karyawan','Salah satu fitur utama dalam aplikasi HRIS adalah Pengajuan Izin Karyawan, yang memungkinkan kary...','Salah satu fitur utama dalam aplikasi HRIS adalah Pengajuan Izin Karyawan, yang memungkinkan karyawan untuk:\r\n\r\n- Mengajukan izin secara online tanpa formulir manual\r\n\r\n- Memilih jenis izin (izin pribadi, sakit, dan lainnya sesuai kebijakan perusahaan)\r\n\r\n- Menentukan tanggal izin dan menambahkan keterangan\r\n\r\n- Memantau status persetujuan izin secara real-time\r\n\r\n- Mengurangi kesalahan administrasi dan mempercepat proses persetujuan\r\n\r\nDengan fitur ini, proses pengajuan izin menjadi lebih praktis, terdokumentasi dengan baik, dan dapat diakses kapan saja melalui aplikasi.','2025-12-15 12:35:27',1,'campaign','#2196F3'),
(3,'Fitur Upload Form SKS (Surat Keterangan Sakit)','','Aplikasi HRIS juga menyediakan fitur Upload Form SKS, yang berfungsi untuk mendukung kelengkapan dokumen izin sakit. Melalui fitur ini, karyawan dapat:\n\n- Mengunggah Surat Keterangan Sakit (SKS) langsung ke sistem\n\n- Mengambil foto atau memilih file SKS dari perangkat\n\n- Memastikan dokumen tersimpan aman dan terarsip secara digital\n\n- Memudahkan HR dalam melakukan verifikasi data dan dokumen\n\nFitur ini membantu menciptakan sistem administrasi yang lebih rapi, cepat, dan paperless.','2025-12-14 12:36:27',1,'check_circle_outline','#E6C229'),
(8,'Pastikan No. Rekening dan Nama Rekening Anda Benar','Silakan dipastikan ulang kesesuaian antara nama dan nomor rekening yang tertera di aplikasi HRIS-...','Silakan dipastikan ulang kesesuaian antara nama dan nomor rekening yang tertera di aplikasi HRIS-TJS Anda.\r\n\r\nJika ada kesalahan di kemudian hari terkait data rekening yang salah, ditanggung oleh karyawan.','2026-03-03 12:11:10',1,'warning','#ff3e1d'),
(9,'Amankan Akun Anda!','Amankan dan jaga NIK dan Password akun Anda dari orang lain untuk terhindar dari pembajakan akun.','Amankan dan jaga NIK dan Password akun Anda dari orang lain untuk terhindar dari pembajakan akun.','2026-03-03 13:29:37',1,'warning','#ff3e1d'),
(13,'HRIS-TJS v1.1.0 Telah Hadir!','Halo Rekan-rekan!\r\nNikmati kemudahan administrasi dengan update terbaru HRIS-TJS Versi 1.1.0. K...','Halo Rekan-rekan!\r\nNikmati kemudahan administrasi dengan update terbaru HRIS-TJS Versi 1.1.0. Kami telah menambahkan beberapa fitur baru untuk Anda:\r\n\r\nðŸ’° Pinjaman Karyawan: Ajukan pinjaman s.d. Rp1 Juta (cukup upload ID Card & Selfie).\r\n\r\nðŸ–ï¸ Cuti Tahunan: Ajukan cuti lebih praktis (maks. 6 hari/pengajuan).\r\n\r\nðŸ¦º Pengajuan APD: Request Alat Pelindung Diri langsung dari aplikasi.\r\n\r\nðŸ“„ Kontrak Kerja: Fitur baru untuk Upload & Download kontrak kerja Anda di Menu Profil.\r\n\r\nSegera perbarui aplikasi HRIS-TJS Anda sekarang untuk menikmati fitur-fitur di atas!','2026-06-22 10:50:10',1,'verified','#71dd37'),
(14,'Ketentuan Baru Pengajuan & Pencairan Pinjaman Karyawan','Halo Rekan-rekan,\r\n\r\nUntuk menjaga kelancaran dan efisiensi administrasi, kami menginformasik...','Halo Rekan-rekan,\r\n\r\nUntuk menjaga kelancaran dan efisiensi administrasi, kami menginformasikan pembaruan ketentuan terkait fasilitas pinjaman karyawan.\r\n\r\nBerikut adalah jadwal dan ketentuan yang mulai diberlakukan:\r\n\r\nðŸ—“ï¸ Periode Pengajuan: Pengajuan pinjaman hanya dapat dilakukan pada tanggal 10 hingga 25 setiap bulannya. Di luar batas tanggal tersebut, sistem akan otomatis mengunci fitur pengajuan.\r\n\r\nðŸ’³ Jadwal Pencairan: Seluruh dana pinjaman yang telah disetujui (ACC) oleh pihak manajemen akan ditransfer secara serentak pada hari Sabtu.','2026-06-23 18:01:02',1,'attach_money','#ffab00');
/*!40000 ALTER TABLE `tb_pengumuman` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tb_pinjaman`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `tb_pinjaman` WRITE;
/*!40000 ALTER TABLE `tb_pinjaman` DISABLE KEYS */;
/*!40000 ALTER TABLE `tb_pinjaman` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tb_potongan`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `tb_potongan` WRITE;
/*!40000 ALTER TABLE `tb_potongan` DISABLE KEYS */;
/*!40000 ALTER TABLE `tb_potongan` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `tb_potongan_kontrak`
--

DROP TABLE IF EXISTS `tb_potongan_kontrak`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tb_potongan_kontrak` (
  `id_potongan_kontrak` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_detail_kontrak` int(11) unsigned NOT NULL COMMENT 'Relasi ke tb_detail_kontrak.id_detail',
  `jenis_potongan` varchar(25) NOT NULL,
  `jumlah_potongan` varchar(25) NOT NULL,
  PRIMARY KEY (`id_potongan_kontrak`),
  KEY `idx_pk_detail` (`id_detail_kontrak`),
  CONSTRAINT `fk_pk_detail` FOREIGN KEY (`id_detail_kontrak`) REFERENCES `tb_detail_kontrak` (`id_detail`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Potongan milik detail kontrak (bisa banyak per detail)';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tb_potongan_kontrak`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `tb_potongan_kontrak` WRITE;
/*!40000 ALTER TABLE `tb_potongan_kontrak` DISABLE KEYS */;
INSERT INTO `tb_potongan_kontrak` VALUES
(1,1,'PPH21','0'),
(2,1,'BPJS KETENAGAKERJAAN 2%','110644'),
(3,1,'BPJS KESEHATAN 1%','36881'),
(4,5,'BPJS KESEHATAN 1%','31881'),
(5,5,'BPJS KETENAGAKERJAAN 2%','110644'),
(6,5,'PPH21','0');
/*!40000 ALTER TABLE `tb_potongan_kontrak` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=111 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tb_potongan_tidak_tetap`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `tb_potongan_tidak_tetap` WRITE;
/*!40000 ALTER TABLE `tb_potongan_tidak_tetap` DISABLE KEYS */;
INSERT INTO `tb_potongan_tidak_tetap` VALUES
(1,1,'2026-09-30',4400000.00,'PANJAR','Payroll ANTAM BG 2026-09','2026-10-12 03:11:05'),
(2,2,'2026-09-30',1500000.00,'PANJAR','Payroll ANTAM BG 2026-09','2026-10-12 03:11:05'),
(3,4,'2026-09-30',600000.00,'PANJAR','Payroll ANTAM BG 2026-09','2026-10-12 03:11:05'),
(4,5,'2026-09-30',700000.00,'PANJAR','Payroll ANTAM BG 2026-09','2026-10-12 03:11:05'),
(5,6,'2026-09-30',600000.00,'PANJAR','Payroll ANTAM BG 2026-09','2026-10-12 03:11:05'),
(6,7,'2026-09-30',700000.00,'PANJAR','Payroll ANTAM BG 2026-09','2026-10-12 03:11:05'),
(7,9,'2026-09-30',3375000.00,'PANJAR','Payroll ANTAM CCR 2026-09','2026-10-12 03:11:05'),
(8,10,'2026-09-30',1000000.00,'PANJAR','Payroll ANTAM CCR 2026-09','2026-10-12 03:11:05'),
(9,12,'2026-09-30',617000.00,'PANJAR','Payroll ANTAM CCR 2026-09','2026-10-12 03:11:05'),
(10,17,'2026-09-30',1138000.00,'PANJAR','Payroll ANTAM CCR 2026-09','2026-10-12 03:11:05'),
(11,18,'2026-09-30',300000.00,'PANJAR','Payroll ANTAM CCR 2026-09','2026-10-12 03:11:05'),
(12,19,'2026-09-30',400000.00,'PANJAR','Payroll ANTAM P3 2026-09','2026-10-12 03:11:05'),
(13,20,'2026-09-30',1150000.00,'PANJAR','Payroll ANTAM P3 2026-09','2026-10-12 03:11:05'),
(14,21,'2026-09-30',500000.00,'PANJAR','Payroll ANTAM P3 2026-09','2026-10-12 03:11:05'),
(15,23,'2026-09-30',400000.00,'PANJAR','Payroll ANTAM P5 2026-09','2026-10-12 03:11:05'),
(16,24,'2026-09-30',1000000.00,'PANJAR','Payroll ANTAM P5 2026-09','2026-10-12 03:11:05'),
(17,25,'2026-09-30',700000.00,'PANJAR','Payroll ANTAM P5 2026-09','2026-10-12 03:11:05'),
(18,29,'2026-09-30',500000.00,'PANJAR','Payroll ANTAM P5 2026-09','2026-10-12 03:11:05'),
(19,30,'2026-09-30',200000.00,'PANJAR','Payroll ANTAM P5 2026-09','2026-10-12 03:11:05'),
(20,32,'2026-09-30',100000.00,'PANJAR','Payroll ANTAM P5 2026-09','2026-10-12 03:11:05'),
(21,33,'2026-09-30',300000.00,'PANJAR','Payroll ANTAM P5 2026-09','2026-10-12 03:11:05'),
(22,35,'2026-09-30',600000.00,'PANJAR','Payroll ANTAM P5 2026-09','2026-10-12 03:11:05'),
(45,1,'2026-10-31',4400000.00,'PANJAR','Payroll ANTAM BG 2026-10','2026-10-12 03:44:55'),
(46,2,'2026-10-31',1500000.00,'PANJAR','Payroll ANTAM BG 2026-10','2026-10-12 03:44:55'),
(47,4,'2026-10-31',600000.00,'PANJAR','Payroll ANTAM BG 2026-10','2026-10-12 03:44:55'),
(48,5,'2026-10-31',700000.00,'PANJAR','Payroll ANTAM BG 2026-10','2026-10-12 03:44:55'),
(49,6,'2026-10-31',600000.00,'PANJAR','Payroll ANTAM BG 2026-10','2026-10-12 03:44:55'),
(50,7,'2026-10-31',700000.00,'PANJAR','Payroll ANTAM BG 2026-10','2026-10-12 03:44:55'),
(51,9,'2026-10-31',3375000.00,'PANJAR','Payroll ANTAM CCR 2026-10','2026-10-12 03:44:55'),
(52,10,'2026-10-31',1000000.00,'PANJAR','Payroll ANTAM CCR 2026-10','2026-10-12 03:44:55'),
(53,12,'2026-10-31',617000.00,'PANJAR','Payroll ANTAM CCR 2026-10','2026-10-12 03:44:55'),
(54,17,'2026-10-31',1138000.00,'PANJAR','Payroll ANTAM CCR 2026-10','2026-10-12 03:44:55'),
(55,18,'2026-10-31',300000.00,'PANJAR','Payroll ANTAM CCR 2026-10','2026-10-12 03:44:55'),
(56,19,'2026-10-31',400000.00,'PANJAR','Payroll ANTAM P3 2026-10','2026-10-12 03:44:55'),
(57,20,'2026-10-31',1150000.00,'PANJAR','Payroll ANTAM P3 2026-10','2026-10-12 03:44:55'),
(58,21,'2026-10-31',500000.00,'PANJAR','Payroll ANTAM P3 2026-10','2026-10-12 03:44:55'),
(59,23,'2026-10-31',400000.00,'PANJAR','Payroll ANTAM P5 2026-10','2026-10-12 03:44:55'),
(60,24,'2026-10-31',1000000.00,'PANJAR','Payroll ANTAM P5 2026-10','2026-10-12 03:44:55'),
(61,25,'2026-10-31',700000.00,'PANJAR','Payroll ANTAM P5 2026-10','2026-10-12 03:44:55'),
(62,29,'2026-10-31',500000.00,'PANJAR','Payroll ANTAM P5 2026-10','2026-10-12 03:44:55'),
(63,30,'2026-10-31',200000.00,'PANJAR','Payroll ANTAM P5 2026-10','2026-10-12 03:44:55'),
(64,32,'2026-10-31',100000.00,'PANJAR','Payroll ANTAM P5 2026-10','2026-10-12 03:44:55'),
(65,33,'2026-10-31',300000.00,'PANJAR','Payroll ANTAM P5 2026-10','2026-10-12 03:44:55'),
(66,35,'2026-10-31',600000.00,'PANJAR','Payroll ANTAM P5 2026-10','2026-10-12 03:44:55'),
(67,55,'2026-10-31',4400000.00,'PANJAR','Payroll ANTAM BG 2026-10','2026-10-19 20:51:39'),
(68,56,'2026-10-31',1500000.00,'PANJAR','Payroll ANTAM BG 2026-10','2026-10-19 20:51:39'),
(69,58,'2026-10-31',600000.00,'PANJAR','Payroll ANTAM BG 2026-10','2026-10-19 20:51:39'),
(70,59,'2026-10-31',700000.00,'PANJAR','Payroll ANTAM BG 2026-10','2026-10-19 20:51:39'),
(71,60,'2026-10-31',600000.00,'PANJAR','Payroll ANTAM BG 2026-10','2026-10-19 20:51:39'),
(72,61,'2026-10-31',700000.00,'PANJAR','Payroll ANTAM BG 2026-10','2026-10-19 20:51:39'),
(73,63,'2026-10-31',3375000.00,'PANJAR','Payroll ANTAM CCR 2026-10','2026-10-19 20:51:39'),
(74,64,'2026-10-31',1000000.00,'PANJAR','Payroll ANTAM CCR 2026-10','2026-10-19 20:51:39'),
(75,66,'2026-10-31',617000.00,'PANJAR','Payroll ANTAM CCR 2026-10','2026-10-19 20:51:39'),
(76,71,'2026-10-31',1138000.00,'PANJAR','Payroll ANTAM CCR 2026-10','2026-10-19 20:51:39'),
(77,72,'2026-10-31',300000.00,'PANJAR','Payroll ANTAM CCR 2026-10','2026-10-19 20:51:39'),
(78,73,'2026-10-31',400000.00,'PANJAR','Payroll ANTAM P3 2026-10','2026-10-19 20:51:39'),
(79,74,'2026-10-31',1150000.00,'PANJAR','Payroll ANTAM P3 2026-10','2026-10-19 20:51:39'),
(80,75,'2026-10-31',500000.00,'PANJAR','Payroll ANTAM P3 2026-10','2026-10-19 20:51:39'),
(81,77,'2026-10-31',400000.00,'PANJAR','Payroll ANTAM P5 2026-10','2026-10-19 20:51:39'),
(82,78,'2026-10-31',1000000.00,'PANJAR','Payroll ANTAM P5 2026-10','2026-10-19 20:51:39'),
(83,79,'2026-10-31',700000.00,'PANJAR','Payroll ANTAM P5 2026-10','2026-10-19 20:51:39'),
(84,83,'2026-10-31',500000.00,'PANJAR','Payroll ANTAM P5 2026-10','2026-10-19 20:51:39'),
(85,84,'2026-10-31',200000.00,'PANJAR','Payroll ANTAM P5 2026-10','2026-10-19 20:51:39'),
(86,86,'2026-10-31',100000.00,'PANJAR','Payroll ANTAM P5 2026-10','2026-10-19 20:51:39'),
(87,87,'2026-10-31',300000.00,'PANJAR','Payroll ANTAM P5 2026-10','2026-10-19 20:51:39'),
(88,89,'2026-10-31',600000.00,'PANJAR','Payroll ANTAM P5 2026-10','2026-10-19 20:51:39'),
(89,55,'2026-09-30',4400000.00,'PANJAR','Payroll ANTAM BG 2026-09','2026-10-19 21:12:24'),
(90,56,'2026-09-30',1500000.00,'PANJAR','Payroll ANTAM BG 2026-09','2026-10-19 21:12:24'),
(91,58,'2026-09-30',600000.00,'PANJAR','Payroll ANTAM BG 2026-09','2026-10-19 21:12:24'),
(92,59,'2026-09-30',700000.00,'PANJAR','Payroll ANTAM BG 2026-09','2026-10-19 21:12:24'),
(93,60,'2026-09-30',600000.00,'PANJAR','Payroll ANTAM BG 2026-09','2026-10-19 21:12:24'),
(94,61,'2026-09-30',700000.00,'PANJAR','Payroll ANTAM BG 2026-09','2026-10-19 21:12:24'),
(95,63,'2026-09-30',3375000.00,'PANJAR','Payroll ANTAM CCR 2026-09','2026-10-19 21:12:24'),
(96,64,'2026-09-30',1000000.00,'PANJAR','Payroll ANTAM CCR 2026-09','2026-10-19 21:12:24'),
(97,66,'2026-09-30',617000.00,'PANJAR','Payroll ANTAM CCR 2026-09','2026-10-19 21:12:24'),
(98,71,'2026-09-30',1138000.00,'PANJAR','Payroll ANTAM CCR 2026-09','2026-10-19 21:12:24'),
(99,72,'2026-09-30',300000.00,'PANJAR','Payroll ANTAM CCR 2026-09','2026-10-19 21:12:24'),
(100,73,'2026-09-30',400000.00,'PANJAR','Payroll ANTAM P3 2026-09','2026-10-19 21:12:24'),
(101,74,'2026-09-30',1150000.00,'PANJAR','Payroll ANTAM P3 2026-09','2026-10-19 21:12:24'),
(102,75,'2026-09-30',500000.00,'PANJAR','Payroll ANTAM P3 2026-09','2026-10-19 21:12:24'),
(103,77,'2026-09-30',400000.00,'PANJAR','Payroll ANTAM P5 2026-09','2026-10-19 21:12:24'),
(104,78,'2026-09-30',1000000.00,'PANJAR','Payroll ANTAM P5 2026-09','2026-10-19 21:12:24'),
(105,79,'2026-09-30',700000.00,'PANJAR','Payroll ANTAM P5 2026-09','2026-10-19 21:12:24'),
(106,83,'2026-09-30',500000.00,'PANJAR','Payroll ANTAM P5 2026-09','2026-10-19 21:12:24'),
(107,84,'2026-09-30',200000.00,'PANJAR','Payroll ANTAM P5 2026-09','2026-10-19 21:12:24'),
(108,86,'2026-09-30',100000.00,'PANJAR','Payroll ANTAM P5 2026-09','2026-10-19 21:12:24'),
(109,87,'2026-09-30',300000.00,'PANJAR','Payroll ANTAM P5 2026-09','2026-10-19 21:12:24'),
(110,89,'2026-09-30',600000.00,'PANJAR','Payroll ANTAM P5 2026-09','2026-10-19 21:12:24');
/*!40000 ALTER TABLE `tb_potongan_tidak_tetap` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tb_priode`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `tb_priode` WRITE;
/*!40000 ALTER TABLE `tb_priode` DISABLE KEYS */;
INSERT INTO `tb_priode` VALUES
(1,'2026-10-01','2026-10-31','Open','slip gaji'),
(2,'2026-10-01','2026-10-31','Open','absen'),
(3,'2026-09-01','2026-09-30','Open','absen'),
(4,'2026-09-01','2026-09-30','Open','slip gaji');
/*!40000 ALTER TABLE `tb_priode` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tb_rapel`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `tb_rapel` WRITE;
/*!40000 ALTER TABLE `tb_rapel` DISABLE KEYS */;
/*!40000 ALTER TABLE `tb_rapel` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tb_reset_otp`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `tb_reset_otp` WRITE;
/*!40000 ALTER TABLE `tb_reset_otp` DISABLE KEYS */;
/*!40000 ALTER TABLE `tb_reset_otp` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tb_sakit`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `tb_sakit` WRITE;
/*!40000 ALTER TABLE `tb_sakit` DISABLE KEYS */;
/*!40000 ALTER TABLE `tb_sakit` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `tb_site`
--

DROP TABLE IF EXISTS `tb_site`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tb_site` (
  `id_site` int(11) NOT NULL AUTO_INCREMENT,
  `nama_site` varchar(150) DEFAULT NULL,
  `kode_lokasi` varchar(50) DEFAULT NULL COMMENT 'Kode lokasi untuk aplikasi absensi',
  `lokasi` varchar(150) DEFAULT NULL,
  `latitude` double DEFAULT NULL COMMENT 'Titik pusat geofence (latitude)',
  `longitude` double DEFAULT NULL COMMENT 'Titik pusat geofence (longitude)',
  `radius_meter` double NOT NULL DEFAULT 80 COMMENT 'Radius absensi valid (meter)',
  `deskripsi_lokasi` text DEFAULT NULL COMMENT 'Deskripsi lokasi untuk aplikasi',
  `is_aktif` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Tampil di aplikasi absensi',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_site`),
  UNIQUE KEY `nama_site` (`nama_site`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tb_site`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `tb_site` WRITE;
/*!40000 ALTER TABLE `tb_site` DISABLE KEYS */;
INSERT INTO `tb_site` VALUES
(11,'PT. IPIP',NULL,'IPIP',NULL,NULL,80,NULL,1,'2025-11-19 06:45:18'),
(12,'PT. HBB',NULL,'IPIP',NULL,NULL,80,NULL,1,'2025-11-19 06:45:27'),
(23,'PT. Antam',NULL,'Antam',NULL,NULL,80,NULL,1,'2026-02-18 14:42:59'),
(24,'PT. TJS',NULL,'Pomalaa',-4.3770334,121.5551742,80,NULL,1,'2026-06-26 12:22:51'),
(29,'kos akmal',NULL,'kos akmal',-4.377145,121.555177,80,NULL,1,'2026-10-17 12:14:11');
/*!40000 ALTER TABLE `tb_site` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tb_surat_peringatan`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `tb_surat_peringatan` WRITE;
/*!40000 ALTER TABLE `tb_surat_peringatan` DISABLE KEYS */;
/*!40000 ALTER TABLE `tb_surat_peringatan` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `tb_template_kontrak`
--

DROP TABLE IF EXISTS `tb_template_kontrak`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tb_template_kontrak` (
  `id_template` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `nama_kontrak` varchar(150) DEFAULT NULL COMMENT 'Nama kontrak (mis. Kontrak Kerja Operator 2026)',
  `file` varchar(255) NOT NULL COMMENT 'Nama file tersimpan di app/uploads/kontrak/',
  `nama_asli` varchar(255) DEFAULT NULL COMMENT 'Nama file asli saat di-upload',
  `ukuran` int(11) NOT NULL DEFAULT 0 COMMENT 'Ukuran file dalam bytes',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_template`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Template file kontrak (PDF) per jabatan';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tb_template_kontrak`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `tb_template_kontrak` WRITE;
/*!40000 ALTER TABLE `tb_template_kontrak` DISABLE KEYS */;
INSERT INTO `tb_template_kontrak` VALUES
(1,'KARYAWAN BARU','template_1789700916_e2cdeb06.docx','Template Kontrak Kerja LV,Safety,Tally,Welder,Elektrikal,MT, 2026.docx',370657,'2026-09-18 03:08:36','2026-09-18 03:08:36'),
(5,'PERPANJANGAN TUKANG','template_1790327599_f966d617.docx','Template Kontrak Fadly.docx',539397,'2026-09-25 09:13:19','2026-09-25 09:13:19');
/*!40000 ALTER TABLE `tb_template_kontrak` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `tb_template_kontrak_jabatan`
--

DROP TABLE IF EXISTS `tb_template_kontrak_jabatan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tb_template_kontrak_jabatan` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_template` int(11) NOT NULL COMMENT 'Relasi ke tb_template_kontrak.id_template',
  `id_jabatan` int(11) NOT NULL COMMENT 'Relasi ke tb_jabatan.id_jabatan',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_template_jabatan` (`id_template`,`id_jabatan`),
  KEY `idx_jabatan` (`id_jabatan`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Relasi template kontrak <-> jabatan (banyak ke banyak)';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tb_template_kontrak_jabatan`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `tb_template_kontrak_jabatan` WRITE;
/*!40000 ALTER TABLE `tb_template_kontrak_jabatan` DISABLE KEYS */;
INSERT INTO `tb_template_kontrak_jabatan` VALUES
(2,1,88,'2026-09-18 03:14:53'),
(10,5,6,'2026-09-25 09:13:19'),
(11,5,5,'2026-09-25 09:13:19'),
(12,5,4,'2026-09-25 09:13:19');
/*!40000 ALTER TABLE `tb_template_kontrak_jabatan` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tb_tereliminasi`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `tb_tereliminasi` WRITE;
/*!40000 ALTER TABLE `tb_tereliminasi` DISABLE KEYS */;
/*!40000 ALTER TABLE `tb_tereliminasi` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tb_thr`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `tb_thr` WRITE;
/*!40000 ALTER TABLE `tb_thr` DISABLE KEYS */;
/*!40000 ALTER TABLE `tb_thr` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `tb_tipe_karyawan`
--

DROP TABLE IF EXISTS `tb_tipe_karyawan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tb_tipe_karyawan` (
  `id_tipe_karyawan` int(11) NOT NULL AUTO_INCREMENT,
  `nama_tipe` varchar(50) NOT NULL,
  `jam_kerja_mulai` time DEFAULT NULL,
  `jam_kerja_selesai` time DEFAULT NULL,
  `keterangan` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_tipe_karyawan`),
  UNIQUE KEY `uq_tipe_karyawan_nama` (`nama_tipe`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tb_tipe_karyawan`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `tb_tipe_karyawan` WRITE;
/*!40000 ALTER TABLE `tb_tipe_karyawan` DISABLE KEYS */;
INSERT INTO `tb_tipe_karyawan` VALUES
(1,'Kontrak','08:00:00','17:00:00','Karyawan kontrak bulanan'),
(2,'Harian','08:00:00','16:00:00','Karyawan harian (upah per hari)');
/*!40000 ALTER TABLE `tb_tipe_karyawan` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=1209 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tb_transaksi_keuangan`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `tb_transaksi_keuangan` WRITE;
/*!40000 ALTER TABLE `tb_transaksi_keuangan` DISABLE KEYS */;
INSERT INTO `tb_transaksi_keuangan` VALUES
(15,NULL,'2026-04-10',9,14300000.00,'Keluar','debit',NULL,'Auto-Posting Pencairan Pinjaman: Tanggal 10/04/2026','2026-05-19 05:57:36'),
(16,NULL,'2026-04-11',9,18749000.00,'Keluar','debit',NULL,'Auto-Posting Pencairan Pinjaman: Tanggal 11/04/2026','2026-05-19 05:57:36'),
(25,NULL,'2026-05-01',47,7815038127.00,'Keluar','debit',NULL,'Auto-Posting Payroll: 21/03/26 - 20/04/26 - Beban Gaji','2026-05-19 06:09:45'),
(26,NULL,'2026-05-01',9,34049000.00,'Masuk','debit',NULL,'Auto-Posting Payroll: 21/03/26 - 20/04/26 - Pelunasan Pinjaman','2026-05-19 06:09:45'),
(27,NULL,'2026-05-01',40,311635131.00,'Masuk','kredit',NULL,'Auto-Posting Payroll: 21/03/26 - 20/04/26 - Denda/Sanksi','2026-05-19 06:09:45'),
(28,NULL,'2026-05-01',22,98704347.00,'Masuk','kredit',NULL,'Auto-Posting Payroll: 21/03/26 - 20/04/26 - Sumbangan/Titipan','2026-05-19 06:09:45'),
(38,NULL,'2026-06-01',47,6988641605.00,'Keluar','debit',NULL,'Auto-Posting Payroll: 21/04/26 - 20/05/26 - Beban Gaji','2026-06-20 05:45:10'),
(39,NULL,'2026-06-01',9,113578450.00,'Masuk','debit',NULL,'Auto-Posting Payroll: 21/04/26 - 20/05/26 - Pelunasan Pinjaman','2026-06-20 05:45:10'),
(41,NULL,'2026-06-01',22,94796209.00,'Masuk','kredit',NULL,'Auto-Posting Payroll: 21/04/26 - 20/05/26 - Sumbangan/Titipan','2026-06-20 05:45:10'),
(42,NULL,'2026-01-12',64,851000.00,'Keluar','debit','','Biaya Pengobatan Yusran DT','2026-06-20 05:51:21'),
(43,NULL,'2026-01-13',42,880578.00,'Keluar','debit','','Pembelian BBM','2026-06-20 05:54:16'),
(44,NULL,'2026-01-12',1,851000.00,'Keluar','kredit','','Pengobatan Yusran DT','2026-06-22 01:11:16'),
(45,NULL,'2026-01-13',1,880578.00,'Keluar','kredit','','Pembelian BBM','2026-06-22 01:12:04'),
(46,NULL,'2026-01-16',42,500000.00,'Keluar','debit','','Pembelian BBM','2026-06-22 01:19:51'),
(47,NULL,'2026-01-16',1,500000.00,'Keluar','kredit','','Pembelian Bensin','2026-06-22 01:21:00'),
(48,NULL,'2026-01-24',42,900000.00,'Keluar','debit','','Pembelian Bensin','2026-06-22 01:21:56'),
(49,NULL,'2026-01-24',3,900000.00,'Keluar','kredit','','Pembelian BBM','2026-06-22 01:22:43'),
(52,NULL,'2026-02-05',6,11256500.00,'Keluar','debit','','DP Sepatu Safety','2026-06-22 01:32:41'),
(53,NULL,'2026-02-05',3,11256500.00,'Keluar','kredit','','DP Sepatu Safety','2026-06-22 01:34:10'),
(54,NULL,'2026-02-05',6,10500000.00,'Keluar','debit','','DP 100 Pcs Baju','2026-06-22 01:35:50'),
(55,NULL,'2026-02-05',3,10500000.00,'Keluar','kredit','','DP 100 Pcs Baju','2026-06-22 01:36:31'),
(56,NULL,'2026-02-12',6,14455000.00,'Keluar','debit','','DP Sepatu Safety','2026-06-22 01:37:51'),
(57,NULL,'2026-02-12',3,14455000.00,'Keluar','kredit','','DP Sepatu Safety','2026-06-22 01:38:44'),
(58,NULL,'2026-02-13',42,800000.00,'Keluar','debit','','Pembelian BBM','2026-06-22 01:40:02'),
(59,NULL,'2026-02-13',3,800000.00,'Keluar','kredit','','Pembelian BBM','2026-06-22 01:40:52'),
(60,NULL,'2026-02-13',55,307000.00,'Keluar','debit','','Pembelian Minuman','2026-06-22 01:47:12'),
(61,NULL,'2026-02-13',3,307000.00,'Keluar','kredit','','Pembelian Minuman','2026-06-22 01:47:52'),
(62,NULL,'2026-02-13',55,150000.00,'Keluar','debit','','Pembelian Minuman','2026-06-22 01:49:19'),
(63,NULL,'2026-02-14',50,655000.00,'Keluar','debit','','Pembelian ATK','2026-06-22 01:50:41'),
(65,NULL,'2026-02-14',3,655000.00,'Keluar','kredit','','Pembelian ATK','2026-06-22 01:52:57'),
(66,NULL,'2026-02-13',3,150000.00,'Keluar','kredit','','Pembelian Minuman','2026-06-22 01:56:17'),
(67,NULL,'2026-02-14',50,50000.00,'Keluar','debit','','Pembelian ATK','2026-06-22 02:03:19'),
(68,NULL,'2026-02-14',3,50000.00,'Keluar','kredit','','Pembelian ATK','2026-06-22 02:04:01'),
(69,NULL,'2026-02-15',61,580000.00,'Keluar','debit','','Pembelian Oli','2026-06-22 02:08:01'),
(70,NULL,'2026-02-15',3,580000.00,'Keluar','kredit','','Pembelian Oli','2026-06-22 02:09:42'),
(71,NULL,'2026-02-15',61,100000.00,'Keluar','debit','','Ongkos Kerja Kendaraan','2026-06-22 02:16:32'),
(72,NULL,'2026-02-15',3,100000.00,'Keluar','kredit','','Ongkos Kerja','2026-06-22 02:17:11'),
(73,NULL,'2026-02-15',61,600000.00,'Keluar','debit','','Ongkos Kerja Kendaraan','2026-06-22 02:19:35'),
(74,NULL,'2026-02-15',3,600000.00,'Keluar','kredit','','Ongkos Kerja Kendaraan','2026-06-22 02:20:03'),
(75,NULL,'2026-02-20',6,1172000.00,'Keluar','debit','','DP Sepatu Safety','2026-06-22 02:22:34'),
(76,NULL,'2026-02-20',3,1172000.00,'Keluar','kredit','','DP Sepatu Safety','2026-06-22 02:23:19'),
(77,NULL,'2026-02-20',69,630000.00,'Keluar','debit','','Karangan Bunga','2026-06-22 02:25:15'),
(78,NULL,'2026-02-20',3,630000.00,'Keluar','kredit','','Karangan Bunga','2026-06-22 02:25:47'),
(79,NULL,'2026-02-20',44,616020.00,'Keluar','debit','','Pembelian Tali Helm','2026-06-22 02:27:57'),
(80,NULL,'2026-02-20',3,616020.00,'Keluar','kredit','','Pembelian Tali Helm','2026-06-22 02:28:31'),
(81,NULL,'2026-02-20',44,1555000.00,'Keluar','debit','','Pembelian APD','2026-06-22 02:30:26'),
(82,NULL,'2026-02-20',3,1555000.00,'Keluar','kredit','','Pembelian APD','2026-06-22 02:31:13'),
(83,NULL,'2026-02-20',55,185000.00,'Keluar','debit','','Pembelian Minuman','2026-06-22 02:41:56'),
(84,NULL,'2026-02-20',3,185000.00,'Keluar','kredit','','Pembelian Minuman','2026-06-22 02:42:38'),
(85,NULL,'2026-02-21',45,1002500.00,'Keluar','debit','','Pembelian Kaos Kaki','2026-06-22 02:43:52'),
(86,NULL,'2026-02-21',3,1002500.00,'Keluar','kredit','','Pembelian Kaos Kaki','2026-06-22 02:44:51'),
(87,NULL,'2026-02-21',42,200000.00,'Keluar','debit','','Pembelian BBM','2026-06-22 02:56:59'),
(88,NULL,'2026-02-21',3,200000.00,'Keluar','kredit','','Pembelian BBM','2026-06-22 02:57:26'),
(89,NULL,'2026-02-21',45,16402500.00,'Keluar','debit','','Pelunasan Baju Dami','2026-06-22 03:00:17'),
(90,NULL,'2026-02-21',3,16402500.00,'Keluar','kredit','','Pelunasan Baju Dami','2026-06-22 03:00:55'),
(91,NULL,'2026-02-21',69,425000.00,'Keluar','debit','','Pembelian Peralatan Olahraga','2026-06-22 03:02:07'),
(92,NULL,'2026-02-21',3,425000.00,'Keluar','kredit','','Pembelian Peralatan Olahraga','2026-06-22 03:02:27'),
(93,NULL,'2026-02-23',61,550000.00,'Keluar','debit','','Ongkos Kerja Mobil Rush','2026-06-22 03:04:59'),
(94,NULL,'2026-02-23',3,550000.00,'Keluar','kredit','','Ongkos Kerja Mobil Rush','2026-06-22 03:05:42'),
(95,NULL,'2026-03-02',66,41677168.00,'Keluar','debit','','Pembayaran BPJS Kesehatan','2026-06-22 03:11:50'),
(96,NULL,'2026-03-02',1,41677168.00,'Keluar','kredit','','Pembayaran BPJS Kesehatan','2026-06-22 03:12:53'),
(97,NULL,'2026-03-02',66,185850189.00,'Keluar','debit','','Pembayaran BPJS Ketenagakerjaan','2026-06-22 03:14:54'),
(98,NULL,'2026-03-02',1,185850189.00,'Keluar','kredit','','Pembayaran BPJS Ketenagakerjaan','2026-06-22 03:16:31'),
(99,NULL,'2026-03-03',69,2502500.00,'Keluar','debit','','Pembelian Baju Futsal','2026-06-22 03:18:03'),
(100,NULL,'2026-03-03',3,2502500.00,'Keluar','kredit','','Pembelian Baju Futsal','2026-06-22 03:18:52'),
(101,NULL,'2026-03-04',57,500000.00,'Keluar','debit','','Ongkos Kirim Baju','2026-06-22 03:49:57'),
(102,NULL,'2026-03-04',3,500000.00,'Keluar','kredit','','Ongkos Kirim Baju','2026-06-22 03:50:29'),
(103,NULL,'2026-03-04',67,215000.00,'Keluar','debit','','Sewa Lapangan Futsal','2026-06-22 03:51:49'),
(104,NULL,'2026-03-04',3,215000.00,'Keluar','kredit','','Sewa Lapangan Futsal','2026-06-22 03:52:32'),
(105,NULL,'2026-03-05',42,200000.00,'Keluar','debit','','Pembelian BBM','2026-06-22 03:53:24'),
(106,NULL,'2026-03-05',1,200000.00,'Keluar','kredit','','Pembelian BBM','2026-06-22 03:54:01'),
(107,NULL,'2026-03-06',6,11002500.00,'Keluar','debit','','DP 100 PCS Baju Dami','2026-06-22 03:55:40'),
(108,NULL,'2026-03-06',3,11002500.00,'Keluar','kredit','','DP 100 PCS Baju Dami','2026-06-22 03:56:27'),
(109,NULL,'2026-03-08',42,300000.00,'Keluar','debit','','Pembelian BBM','2026-06-22 03:57:34'),
(110,NULL,'2026-03-08',3,300000.00,'Keluar','kredit','','Pembelian BBM','2026-06-22 03:58:08'),
(111,NULL,'2026-03-11',59,216451329.00,'Keluar','debit','','Pembayaran Pajak','2026-06-22 04:29:37'),
(112,NULL,'2026-03-11',3,216451329.00,'Keluar','kredit','','Pembayaran Pajak','2026-06-22 04:30:13'),
(113,NULL,'2026-03-11',59,60176444.00,'Keluar','debit','','Pembayaran Pajak','2026-06-22 04:31:39'),
(114,NULL,'2026-03-11',3,60176444.00,'Keluar','kredit','','Pembayaran Pajak','2026-06-22 04:32:11'),
(115,NULL,'2026-03-11',59,142515219.00,'Keluar','debit','','Pembayaran Pajak','2026-06-22 05:23:15'),
(116,NULL,'2026-03-11',3,142515219.00,'Keluar','kredit','','Pembayaran Pajak','2026-06-22 05:23:59'),
(117,NULL,'2026-02-15',69,170000.00,'Keluar','debit','','Biaya Pencucian Mobil','2026-06-22 05:27:19'),
(118,NULL,'2026-02-15',3,170000.00,'Keluar','kredit','','Biaya Pencucian Mobil','2026-06-22 05:27:52'),
(119,NULL,'2026-02-21',55,134500.00,'Masuk','debit','','Pembelian Minuman','2026-06-22 05:29:14'),
(120,NULL,'2026-02-21',3,134500.00,'Keluar','kredit','','Pembelian Minuman','2026-06-22 05:29:44'),
(121,NULL,'2026-02-23',57,3149500.00,'Keluar','debit','','Ongkos Kirim Sepatu Safety','2026-06-22 05:33:01'),
(122,NULL,'2026-02-23',3,3149500.00,'Keluar','kredit','','Ongkos Kirim Sepatu Safety','2026-06-22 05:33:47'),
(123,NULL,'2026-02-23',6,11000000.00,'Keluar','kredit','','DP Baju APD 100 Pcs','2026-06-22 05:35:05'),
(124,NULL,'2026-02-23',3,11000000.00,'Keluar','debit','','DP Baju APD 100 PCS','2026-06-22 05:35:43'),
(125,NULL,'2026-03-01',69,249000.00,'Keluar','debit','','Pembelian Buah untuk Karyawan Sakit','2026-06-22 05:38:23'),
(126,NULL,'2026-03-01',3,249000.00,'Keluar','kredit','','Pembelian Buah untuk Karyawan Sakit ','2026-06-22 05:39:04'),
(127,NULL,'2026-03-02',67,200000.00,'Keluar','debit','','Sewa Lapangan Futsal','2026-06-22 05:50:07'),
(128,NULL,'2026-03-02',3,200000.00,'Keluar','kredit','','Sewa Lapangan Futsal','2026-06-22 05:50:49'),
(129,NULL,'2026-03-05',67,300000.00,'Keluar','debit','','Donor Darah','2026-06-22 05:52:11'),
(130,NULL,'2026-03-05',3,300000.00,'Keluar','kredit','','Donor Darah','2026-06-22 05:52:44'),
(131,NULL,'2026-03-06',67,602500.00,'Keluar','debit','','Pendaftaran Futsal','2026-06-22 05:53:40'),
(132,NULL,'2026-03-06',3,602500.00,'Keluar','kredit','','Pendaftaran Futsal','2026-06-22 05:54:26'),
(133,NULL,'2026-03-08',69,469900.00,'Keluar','debit','','Pembelian Cooler Box','2026-06-22 05:57:42'),
(134,NULL,'2026-03-08',3,469900.00,'Keluar','kredit','','Pembelian Cooler Box','2026-06-22 05:58:27'),
(135,NULL,'2026-03-08',67,1463000.00,'Keluar','debit','','Pengeluaran Futsal','2026-06-22 06:00:09'),
(136,NULL,'2026-03-08',3,1463000.00,'Keluar','kredit','','Pengeluaran Futsal','2026-06-22 06:01:02'),
(137,NULL,'2026-03-10',69,2010000.00,'Keluar','debit','','Penarikan Tunai','2026-06-22 07:55:41'),
(138,NULL,'2026-03-10',3,2010000.00,'Keluar','kredit','','Penarikan Tunai','2026-06-22 07:56:23'),
(139,NULL,'2026-03-10',67,200000.00,'Keluar','debit','','Pengeluaran Futsal','2026-06-22 07:57:29'),
(140,NULL,'2026-03-10',3,200000.00,'Keluar','kredit','','Pengeluaran Futsal ','2026-06-22 07:58:00'),
(141,NULL,'2026-03-10',67,502500.00,'Keluar','debit','','Pengeluaran Futsal','2026-06-22 07:58:42'),
(142,NULL,'2026-03-10',3,502500.00,'Keluar','kredit','','Pengeluaran futsal','2026-06-22 07:59:17'),
(143,NULL,'2026-03-12',44,6552500.00,'Keluar','debit','','Pembelian Helm','2026-06-22 08:01:55'),
(144,NULL,'2026-03-12',3,6552500.00,'Keluar','kredit','','Pembelian Helm','2026-06-22 08:02:36'),
(145,NULL,'2026-03-12',44,6100000.00,'Keluar','debit','','Pembelian Helm Kuning 100 Pcs','2026-06-22 08:04:33'),
(146,NULL,'2026-03-12',3,6100000.00,'Keluar','kredit','','Pembelian Helm Kuning 100 Pcs','2026-06-22 08:05:04'),
(147,NULL,'2026-03-12',44,450000.00,'Keluar','debit','','Pembelian Tali Dagu Karet 100 Pcs','2026-06-22 08:06:13'),
(148,NULL,'2026-03-12',3,450000.00,'Keluar','kredit','','Pembelian Tali Dagu Karet 100 Pcs','2026-06-22 08:07:17'),
(149,NULL,'2026-03-16',42,300000.00,'Keluar','debit','','Pembelian BBM','2026-06-22 08:08:55'),
(150,NULL,'2026-03-16',3,300000.00,'Keluar','kredit','','Pembelian BBM','2026-06-22 08:09:28'),
(151,NULL,'2026-03-19',69,100000.00,'Keluar','debit','','Biaya Pencucian Mobil','2026-06-22 08:11:14'),
(152,NULL,'2026-03-19',3,100000.00,'Keluar','kredit','','Biaya Pencucian Mobil','2026-06-22 08:11:49'),
(153,NULL,'2026-03-26',44,70000.00,'Keluar','debit','','Pembelian Stiker Helm','2026-06-22 08:12:33'),
(154,NULL,'2026-03-26',3,70000.00,'Keluar','kredit','','Pembelian Stiker Helm','2026-06-22 08:13:04'),
(155,NULL,'2026-03-26',44,970000.00,'Keluar','debit','','Pembelian Helm Merah 10 Pcs','2026-06-22 08:14:11'),
(156,NULL,'2026-03-26',3,970000.00,'Keluar','kredit','','Pembelian Helm Merah 10 Pcs','2026-06-22 08:14:46'),
(157,NULL,'2026-03-30',57,750000.00,'Keluar','debit','','Ongkos Kirim Helm','2026-06-22 08:15:31'),
(158,NULL,'2026-03-30',3,750000.00,'Keluar','kredit','','Ongkos Kirim Helm','2026-06-22 08:16:18'),
(159,NULL,'2026-04-02',68,10000000.00,'Keluar','debit','','Pelunasan 100 Pcs baju','2026-06-22 08:17:42'),
(160,NULL,'2026-04-02',3,10000000.00,'Keluar','kredit','','Pelunasan 100 Pcs baju','2026-06-22 08:18:11'),
(161,NULL,'2026-04-02',6,11500000.00,'Keluar','debit','','DP Baju 100 Pcs','2026-06-22 08:19:03'),
(162,NULL,'2026-04-02',3,11500000.00,'Keluar','kredit','','DP Baju 100 Pcs','2026-06-22 08:19:39'),
(163,NULL,'2026-04-02',9,9761000.00,'Keluar','debit','','Pinjaman Karyawan','2026-06-22 08:21:36'),
(164,NULL,'2026-04-02',3,9761000.00,'Keluar','kredit','','Pinjaman Karyawan','2026-06-22 08:22:10'),
(165,NULL,'2026-04-02',9,302500.00,'Keluar','debit','','Pinjaman Karyawan','2026-06-22 08:35:52'),
(166,NULL,'2026-04-02',3,302500.00,'Keluar','kredit','','Pinjaman Karyawan','2026-06-22 08:36:27'),
(167,NULL,'2026-04-02',9,2000000.00,'Keluar','debit','','Pinjaman Karyawan','2026-06-22 08:37:15'),
(168,NULL,'2026-04-02',3,2000000.00,'Keluar','kredit','','Pinjaman Karyawan','2026-06-22 08:37:46'),
(169,NULL,'2026-04-02',69,4300000.00,'Keluar','debit','','Iuran Ormas','2026-06-22 08:38:37'),
(170,NULL,'2026-04-02',3,4300000.00,'Keluar','kredit','','Iuran Ormas','2026-06-22 08:39:15'),
(171,NULL,'2026-04-02',9,5690000.00,'Keluar','debit','','Pinjaman Karyawan','2026-06-22 08:40:05'),
(172,NULL,'2026-04-02',3,5690000.00,'Keluar','kredit','','Pinjaman Karyawan','2026-06-22 08:40:58'),
(173,NULL,'2026-04-03',69,5002500.00,'Keluar','debit','','Infaq Fisabilillah Palestina PT TJS','2026-06-23 00:46:08'),
(174,NULL,'2026-04-03',3,5002500.00,'Keluar','kredit','','Infaq Fisabilillah Palestina PT TJS','2026-06-23 00:46:45'),
(175,NULL,'2026-04-06',69,10000000.00,'Keluar','debit','','Tambahan DP','2026-06-23 00:54:04'),
(176,NULL,'2026-04-06',3,10000000.00,'Keluar','kredit','','Tambahan DP','2026-06-23 00:54:47'),
(177,NULL,'2026-04-06',64,79145000.00,'Keluar','debit','','Pembayaran SIO SKM','2026-06-23 00:56:40'),
(178,NULL,'2026-04-06',3,79145000.00,'Keluar','kredit','','Pembayaran SIO SKM','2026-06-23 00:57:23'),
(179,NULL,'2026-04-09',69,120000.00,'Keluar','debit','','Biaya Pencucian Mobil','2026-06-23 00:58:22'),
(180,NULL,'2026-04-09',3,120000.00,'Keluar','kredit','','Biaya Pencucian Mobil','2026-06-23 00:58:52'),
(181,NULL,'2026-04-10',50,1234000.00,'Keluar','debit','','Pembelian ATK','2026-06-23 00:59:35'),
(182,NULL,'2026-04-10',3,1234000.00,'Keluar','kredit','','Pembelian ATK','2026-06-23 01:00:00'),
(183,NULL,'2026-04-11',55,50000.00,'Keluar','debit','','Pembelian Minuman','2026-06-23 01:21:12'),
(184,NULL,'2026-04-11',3,50000.00,'Keluar','kredit','','Pembelian Minuman','2026-06-23 01:21:39'),
(185,NULL,'2026-04-12',57,2372000.00,'Keluar','debit','','Ongkos Kirim Sepatu 100 Pcs','2026-06-23 01:22:40'),
(186,NULL,'2026-04-12',3,2372000.00,'Keluar','kredit','','Ongkos Kirim Sepatu 100 Pcs','2026-06-23 01:23:27'),
(187,NULL,'2026-04-13',64,40006500.00,'Keluar','debit','','Pembayaran Tahap 2 Operasi Pak Aswin','2026-06-23 01:24:46'),
(188,NULL,'2026-04-13',3,40006500.00,'Keluar','kredit','','Pembayaran tahap 2 operasi Pak Aswin','2026-06-23 01:25:40'),
(189,NULL,'2026-04-11',42,350000.00,'Keluar','debit','','Pembelian BBM','2026-06-23 01:26:24'),
(190,NULL,'2026-04-11',3,350000.00,'Keluar','kredit','','Pembelian BBM','2026-06-23 01:26:49'),
(191,NULL,'2026-04-13',66,41864540.00,'Keluar','debit','','Pembayaran BPJS Kesehatan','2026-06-23 01:46:57'),
(192,NULL,'2026-04-13',3,41864540.00,'Keluar','kredit','','Pembayaran BPJS Kesehatan','2026-06-23 01:47:33'),
(193,NULL,'2026-04-13',66,278359576.00,'Keluar','debit','','Pembayaran BPJS Ketenagakerjaan','2026-06-23 01:50:38'),
(194,NULL,'2026-04-13',3,278359576.00,'Keluar','kredit','','Pembayaran BPJS Ketenagakerjaan','2026-06-23 01:51:17'),
(195,NULL,'2026-04-14',55,132000.00,'Keluar','debit','','Pembelian Minuman','2026-06-23 02:02:35'),
(196,NULL,'2026-04-14',3,132000.00,'Keluar','kredit','','Pembelian Minuman ','2026-06-23 02:03:05'),
(197,NULL,'2026-04-15',61,2102500.00,'Keluar','debit','','Pembelian Ban Hilux','2026-06-23 02:04:09'),
(198,NULL,'2026-04-15',3,2102500.00,'Keluar','kredit','','Pembelian Ban Hilux','2026-06-23 02:04:35'),
(199,NULL,'2026-04-15',61,522500.00,'Keluar','debit','','Pembelian Oli Hilux','2026-06-23 02:05:28'),
(200,NULL,'2026-04-15',3,522500.00,'Keluar','kredit','','Pembelian Oli Hilux','2026-06-23 02:06:18'),
(201,NULL,'2026-04-24',42,417000.00,'Keluar','debit','','Pembelian BBM','2026-06-23 02:07:16'),
(202,NULL,'2026-04-24',3,417000.00,'Keluar','kredit','','Pembelian BBM','2026-06-23 02:07:59'),
(203,NULL,'2026-04-25',42,1000000.00,'Keluar','debit','','Pembelian BBM','2026-06-23 02:12:04'),
(204,NULL,'2026-04-25',3,1000000.00,'Keluar','kredit','','Pembelian BBM','2026-06-23 02:12:36'),
(205,NULL,'2026-04-27',50,270000.00,'Keluar','debit','','Pembelian Tinta Print','2026-06-23 02:14:19'),
(206,NULL,'2026-04-27',3,270000.00,'Keluar','kredit','','Pembelian Tinta Print ','2026-06-23 02:14:52'),
(207,NULL,'2026-04-27',50,270000.00,'Keluar','debit','','Pembelian Tinta Print','2026-06-23 02:15:59'),
(208,NULL,'2026-04-27',3,270000.00,'Keluar','kredit','','Pembelian Tinta Print','2026-06-23 02:16:33'),
(209,NULL,'2026-04-26',42,1000000.00,'Keluar','debit','','Pembelian BBM','2026-06-23 02:23:03'),
(210,NULL,'2026-04-26',3,1000000.00,'Keluar','kredit','','Pembelian BBM','2026-06-23 02:23:34'),
(211,NULL,'2026-04-28',50,200000.00,'Keluar','debit','','Pembelian Kertas HVS','2026-06-23 02:25:00'),
(212,NULL,'2026-04-28',3,200000.00,'Keluar','kredit','','Pembelian Kerta HVS','2026-06-23 02:25:33'),
(213,NULL,'2026-04-29',64,16506500.00,'Keluar','debit','','Pelunasan SIO Fadira 9 Orang','2026-06-23 02:26:49'),
(214,NULL,'2026-04-29',3,16506500.00,'Keluar','kredit','','Pelunasan SIO Fadira 9 orang','2026-06-23 02:27:33'),
(215,NULL,'2026-04-29',50,1774500.00,'Keluar','debit','','Pembelian ATK','2026-06-23 02:45:12'),
(216,NULL,'2026-04-29',3,1774500.00,'Keluar','kredit','','Pembelian ATK','2026-06-23 02:46:02'),
(217,NULL,'2026-04-29',42,300000.00,'Keluar','debit','','Pembelian BBM','2026-06-23 02:47:02'),
(218,NULL,'2026-04-29',3,300000.00,'Keluar','kredit','','Pembelian BBM','2026-06-23 02:47:25'),
(219,NULL,'2026-04-29',55,100000.00,'Keluar','debit','','Pembelian Minuman','2026-06-23 02:48:27'),
(220,NULL,'2026-04-29',3,100000.00,'Keluar','kredit','','Pembelian Minuman','2026-06-23 02:49:03'),
(221,NULL,'2026-05-01',47,325602188.00,'Keluar','debit','','Pembayaran Gaji Maret-April','2026-06-23 02:50:26'),
(222,NULL,'2026-05-01',3,325602188.00,'Keluar','kredit','','Pembayaran Gaji Maret-April','2026-06-23 02:52:19'),
(225,NULL,'2026-05-01',69,320000.00,'Keluar','debit','','Pembayaran WPS 1 Tahun','2026-06-23 02:58:42'),
(226,NULL,'2026-05-01',1,320000.00,'Keluar','kredit','','Pembayaran WPS 1 Tahun','2026-06-23 02:59:19'),
(227,NULL,'2026-05-01',67,300000.00,'Keluar','debit','','Pengeluaran Futsal','2026-06-23 03:00:07'),
(228,NULL,'2026-05-01',3,300000.00,'Keluar','kredit','','Pengeluaran Futsal','2026-06-23 03:00:43'),
(229,NULL,'2026-05-02',45,12006500.00,'Keluar','debit','','Pelunasan Baju Dami','2026-06-23 03:03:07'),
(230,NULL,'2026-05-02',3,12006500.00,'Keluar','kredit','','Pelunasan Baju Dami','2026-06-23 03:04:30'),
(231,NULL,'2026-05-03',69,120000.00,'Keluar','debit','','Biaya Pencucian Mobil','2026-06-23 03:09:38'),
(232,NULL,'2026-05-03',3,120000.00,'Keluar','kredit','','Biaya Pencucian Mobil','2026-06-23 03:10:09'),
(233,NULL,'2026-05-03',69,120000.00,'Keluar','debit','','Biaya Pencucian Mobil','2026-06-23 03:13:10'),
(234,NULL,'2026-05-03',3,120000.00,'Keluar','kredit','','Biaya Pencucian Mobil','2026-06-23 03:13:54'),
(235,NULL,'2026-04-29',42,900000.00,'Keluar','debit','','Pembelian BBM','2026-06-23 03:14:50'),
(236,NULL,'2026-04-29',1,900000.00,'Keluar','kredit','','Pembelian BBM','2026-06-23 03:15:28'),
(237,NULL,'2026-05-04',42,400000.00,'Keluar','debit','','Pembelian BBM','2026-06-23 03:16:50'),
(238,NULL,'2026-05-04',1,400000.00,'Keluar','kredit','','Pembelian BBM','2026-06-23 03:17:26'),
(239,NULL,'2026-05-04',69,500000.00,'Keluar','debit','','Pembelian Materai','2026-06-23 03:18:51'),
(240,NULL,'2026-05-04',1,500000.00,'Keluar','kredit','','Pembelian Materai','2026-06-23 03:19:43'),
(241,NULL,'2026-05-05',50,190000.00,'Keluar','debit','','Pembelian ATK','2026-06-23 03:20:43'),
(242,NULL,'2026-05-05',3,190000.00,'Keluar','kredit','','Pembelian ATK','2026-06-23 03:21:43'),
(243,NULL,'2026-05-09',42,350000.00,'Keluar','debit','','Pembelian BBM','2026-06-23 03:22:40'),
(244,NULL,'2026-05-09',3,350000.00,'Masuk','kredit','','Pembelian BBM','2026-06-23 03:23:18'),
(245,NULL,'2026-05-10',69,120000.00,'Keluar','debit','','Biaya Pencucian Mobil','2026-06-23 05:07:34'),
(246,NULL,'2026-05-10',3,120000.00,'Keluar','kredit','','Biaya Pencucian Mobil','2026-06-23 05:08:05'),
(247,NULL,'2026-05-11',68,2630000.00,'Keluar','debit','','Pembelian Printer','2026-06-23 05:22:42'),
(248,NULL,'2026-05-11',3,2630000.00,'Keluar','kredit','','Pembelian Printer','2026-06-23 05:23:52'),
(249,NULL,'2026-05-11',64,55006500.00,'Keluar','debit','','Pembayaran tahap 3 Pelunasan Operasi Aswin\r\n','2026-06-23 05:25:00'),
(250,NULL,'2026-05-11',3,55006500.00,'Keluar','kredit','','Pembayaran tahap 3 Pelunasan Operasi Aswin','2026-06-23 05:25:42'),
(251,NULL,'2026-05-12',56,503000.00,'Keluar','debit','','Pembayaran Listrik','2026-06-23 05:27:56'),
(252,NULL,'2026-05-12',3,503000.00,'Keluar','kredit','','Pembayaran Listrik','2026-06-23 05:28:33'),
(253,NULL,'2026-05-12',66,40944377.00,'Keluar','debit','','Pembayaran BPJS Kesehatan','2026-06-23 05:29:44'),
(254,NULL,'2026-05-12',3,40944377.00,'Keluar','kredit','','Pembayaran BPJS Kesehatan','2026-06-23 05:30:24'),
(255,NULL,'2026-05-12',69,35804000.00,'Keluar','debit','','Pembayaran Tagihan Maret-April','2026-06-23 05:31:33'),
(256,NULL,'2026-05-12',3,35804000.00,'Keluar','kredit','','Pembayaran Tagihan Maret-April','2026-06-23 05:32:15'),
(257,NULL,'2026-05-12',66,8922014.00,'Keluar','debit','','Pembayaran BPJS Ketenagakerjaan','2026-06-23 05:34:12'),
(258,NULL,'2026-05-12',3,8922014.00,'Keluar','kredit','','Pembayaran BPJS Ketenagakerjaan','2026-06-23 05:34:46'),
(259,NULL,'2026-05-12',66,268557122.00,'Keluar','debit','','Pembayaran BPJS Ketenagakerjaan','2026-06-23 05:36:22'),
(260,NULL,'2026-05-12',3,268557122.00,'Keluar','kredit','','Pembayaran BPJS Ketenagakerjaan','2026-06-23 05:37:23'),
(261,NULL,'2026-05-13',50,175000.00,'Keluar','debit','','Pembelian ATK','2026-06-23 05:38:33'),
(262,NULL,'2026-05-13',3,175000.00,'Keluar','kredit','','Pembelian ATK','2026-06-23 05:39:13'),
(263,NULL,'2026-05-13',69,50000.00,'Keluar','debit','','Pembelian Sapu','2026-06-23 05:40:02'),
(264,NULL,'2026-05-13',3,50000.00,'Keluar','kredit','','Pembelian Sapu','2026-06-23 05:40:29'),
(265,NULL,'2026-05-19',56,14603170.00,'Keluar','debit','','Pembayaran Listrik','2026-06-23 05:41:41'),
(266,NULL,'2026-05-19',3,14603170.00,'Keluar','kredit','','Pembayaran Listrik','2026-06-23 05:42:26'),
(267,NULL,'2026-05-19',76,6686500.00,'Keluar','debit','','Pembayaran Angsuran Rush','2026-06-23 05:51:58'),
(269,NULL,'2026-05-19',3,6686500.00,'Keluar','kredit','','Pembayaran Angsuran Rush','2026-06-23 05:53:08'),
(270,NULL,'2026-05-19',76,7786500.00,'Keluar','debit','','Pembayaran Angsuran Mobil Box','2026-06-23 05:55:54'),
(271,NULL,'2026-05-19',3,7786500.00,'Keluar','kredit','','Pembayaran Angsuran Mobil Box','2026-06-23 05:56:39'),
(272,NULL,'2026-05-20',44,15006500.00,'Keluar','debit','','Pelunasan Sepatu Safety 300 Pasang','2026-06-23 06:03:43'),
(273,NULL,'2026-05-20',3,15006500.00,'Keluar','kredit','','Pelunasan Sepatu Safety 300 Pasang\r\n','2026-06-23 06:04:19'),
(274,NULL,'2026-05-20',69,6800000.00,'Keluar','debit','','Renov Hotel','2026-06-23 06:05:18'),
(275,NULL,'2026-05-20',3,6800000.00,'Keluar','kredit','','Renov Hotel','2026-06-23 06:05:55'),
(276,NULL,'2026-05-24',57,4353500.00,'Keluar','debit','','Pembayaran Ongkos Kirim Sepatu','2026-06-23 06:07:11'),
(277,NULL,'2026-05-24',3,4353500.00,'Keluar','kredit','','Pembayaran Ongkos Kirim Sepatu','2026-06-23 06:07:59'),
(278,NULL,'2026-05-29',76,21300000.00,'Keluar','debit','','Cicilan','2026-06-23 06:19:45'),
(279,NULL,'2026-05-29',3,21300000.00,'Keluar','kredit','','Cicilan','2026-06-23 06:20:11'),
(280,NULL,'2026-06-09',55,90000.00,'Keluar','debit','','Uang Makan Karyawan yang Masuk RS ','2026-06-23 06:21:36'),
(281,NULL,'2026-06-09',3,90000.00,'Keluar','kredit','','Uang Makan Karyawan yang Masuk RS','2026-06-23 06:22:16'),
(282,NULL,'2026-06-10',66,35785725.00,'Keluar','debit','','Pembayaran BPJS Kesehatan','2026-06-23 06:23:18'),
(283,NULL,'2026-06-10',3,35785725.00,'Keluar','kredit','','Pembayaran BPJS Kesehatan','2026-06-23 06:24:07'),
(284,NULL,'2026-06-10',66,6624073.00,'Keluar','debit','','Pembayaran BPJS Ketenagakerjaan','2026-06-23 06:25:04'),
(285,NULL,'2026-06-10',3,6624073.00,'Keluar','kredit','','Pembayaran BPJS Ketenagakerjaan','2026-06-23 06:25:50'),
(288,NULL,'2026-06-12',64,21845500.00,'Keluar','debit','','Biaya Operasi & Rawat Inap Rionaldo Samosir','2026-06-23 06:38:28'),
(289,NULL,'2026-06-12',3,21845500.00,'Keluar','kredit','','Biaya Operasi & Rawat Inap Rionaldo Samosir\r\n','2026-06-23 06:39:07'),
(290,NULL,'2026-06-12',69,52065500.00,'Keluar','debit','','Tagihan Periode Mei 2026 SKM\r\n','2026-06-23 06:40:33'),
(291,NULL,'2026-06-12',3,52065500.00,'Keluar','kredit','','Tagihan Periode Mei 2026 SKM\r\n','2026-06-23 06:41:06'),
(292,NULL,'2026-06-14',49,12000000.00,'Keluar','debit','','Kurang Bayar Sewa Truck Emplasemen','2026-06-23 06:46:02'),
(293,NULL,'2026-06-14',3,12000000.00,'Keluar','kredit','','Kurang Bayar Sewa Truck Emplasemen\r\n','2026-06-23 06:46:30'),
(294,NULL,'2026-06-19',42,300000.00,'Keluar','debit','','Pembelian BBM','2026-06-23 06:47:08'),
(295,NULL,'2026-06-19',3,300000.00,'Keluar','kredit','','Pembelian BBM','2026-06-23 06:47:37'),
(296,NULL,'2026-06-19',76,6686500.00,'Keluar','debit','','Pembayaran Angsuran Rush\r\n','2026-06-23 06:48:17'),
(297,NULL,'2026-06-19',3,6686500.00,'Keluar','kredit','','Pembayaran Angsuran Rush\r\n','2026-06-23 06:48:45'),
(298,NULL,'2026-06-19',76,7786500.00,'Keluar','debit','','Pembayaran Angsuran Mobil Box\r\n','2026-06-23 06:49:40'),
(299,NULL,'2026-06-19',3,7786500.00,'Keluar','kredit','','Pembayaran Angsuran Mobil Box\r\n','2026-06-23 06:50:11'),
(300,NULL,'2026-06-19',56,7758615.00,'Keluar','debit','','Pembayaran Listrik','2026-06-23 06:51:00'),
(301,NULL,'2026-06-19',3,7758615.00,'Keluar','kredit','','Pembayaran Listrik','2026-06-23 06:51:51'),
(302,NULL,'2026-06-19',76,7786500.00,'Keluar','debit','','Pembayaran Angsuran Mobil Box\r\n','2026-06-23 06:52:34'),
(303,NULL,'2026-06-19',3,7786500.00,'Keluar','kredit','','Pembayaran Angsuran Mobil Box\r\n','2026-06-23 06:52:56'),
(304,NULL,'2026-06-20',6,11776500.00,'Keluar','debit','','DP Baju 107 Pcs','2026-06-23 06:53:42'),
(305,NULL,'2026-06-20',3,11776500.00,'Keluar','kredit','','DP Baju 107 pcs','2026-06-23 06:54:17'),
(306,NULL,'2026-06-21',56,1003000.00,'Keluar','debit','','Pembayaran Listrik','2026-06-23 06:58:03'),
(307,NULL,'2026-06-21',3,1003000.00,'Keluar','kredit','','Pembayaran Listrik','2026-06-23 06:58:29'),
(308,NULL,'2026-06-22',76,23379500.00,'Keluar','debit','','Angsuran Fortuner','2026-06-23 07:01:50'),
(309,NULL,'2026-06-22',5,23379500.00,'Keluar','kredit','','Angsuran Rush','2026-06-23 07:02:26'),
(310,NULL,'2026-06-22',64,380000.00,'Keluar','debit','','Kontrol Rionaldo Samosir','2026-06-23 07:03:17'),
(311,NULL,'2026-06-22',3,380000.00,'Keluar','kredit','','Kontrol Rionaldo Samosir','2026-06-23 07:03:53'),
(312,NULL,'2026-06-26',76,18500000.00,'Keluar','debit','','Angsuran Mixer','2026-06-26 07:45:27'),
(313,NULL,'2026-06-26',76,18500000.00,'Keluar','kredit','','Angsuran Mixer','2026-06-26 07:45:58'),
(314,NULL,'2026-06-26',64,380000.00,'Keluar','debit','','Buka Jahitan Rionaldo Samosir','2026-06-27 01:19:23'),
(315,NULL,'2026-02-26',3,380000.00,'Keluar','kredit','','Buka Jahitan Rionaldo Samosir','2026-06-27 01:21:08'),
(316,NULL,'2026-06-26',64,130000.00,'Keluar','debit','','Biaya Kecelakaan Kerja ','2026-06-27 01:23:52'),
(317,NULL,'2026-06-26',1,130000.00,'Keluar','kredit','','Biaya Kecelakaan Kerja','2026-06-27 01:24:34'),
(318,NULL,'2026-06-26',69,6000000.00,'Keluar','debit','','Fee Akuntan','2026-06-27 01:27:11'),
(319,NULL,'2026-06-26',3,6000000.00,'Keluar','kredit','','Fee Akuntan','2026-06-27 01:27:57'),
(320,NULL,'2026-06-26',59,265700279.00,'Keluar','debit','','Pembayaran PPN','2026-06-27 01:29:58'),
(321,NULL,'2026-06-26',3,265700279.00,'Keluar','kredit','','Pembayaran PPN','2026-06-27 01:30:57'),
(324,NULL,'2026-06-26',76,18500000.00,'Keluar','debit','','Pembayaran Angsuran Mixer','2026-06-27 02:41:45'),
(325,NULL,'2026-06-26',3,18500000.00,'Keluar','kredit','','Pembayaran Angsuran Mixer','2026-06-27 02:42:23'),
(326,NULL,'2026-06-29',59,15405765.00,'Keluar','debit','','Pembayaran SP2DK','2026-06-29 01:10:18'),
(327,NULL,'2026-06-29',3,15405765.00,'Keluar','kredit','','Pembayaran SP2DK','2026-06-29 01:10:56'),
(328,NULL,'2026-01-24',1,2503642400.00,'Masuk','debit','','Jasa Manpower IPIP','2026-06-29 04:29:08'),
(329,NULL,'2026-01-24',38,2503642400.00,'Masuk','kredit','','Jasa Manpower','2026-06-29 04:30:10'),
(330,NULL,'2026-01-24',1,180784890.00,'Masuk','debit','','Jasa Manpower IPK','2026-06-29 04:33:27'),
(331,NULL,'2026-01-24',39,180784890.00,'Masuk','kredit','','Jasa Manpower IPK','2026-06-29 04:33:57'),
(332,NULL,'2026-01-26',1,184579118.00,'Masuk','debit','','Jasa Manpower IPK','2026-06-29 04:35:35'),
(333,NULL,'2026-01-26',39,184579118.00,'Masuk','kredit','','Jasa Manpower IPK','2026-06-29 04:36:14'),
(334,NULL,'2026-01-26',1,2514898257.00,'Masuk','debit','','Jasa Manpower IPIP','2026-06-29 04:37:45'),
(335,NULL,'2026-01-26',38,2514898257.00,'Masuk','kredit','','Jasa Manpower IPIP','2026-06-29 04:38:44'),
(336,NULL,'2026-01-26',1,130928864.00,'Masuk','debit','','Jasa Manpower IPK','2026-06-29 04:39:56'),
(337,NULL,'2026-01-26',39,130928864.00,'Masuk','kredit','','Jasa Manpower IPK','2026-06-29 04:40:23'),
(338,NULL,'2026-01-26',1,2405324306.00,'Masuk','debit','','Jasa Manpower IPIP','2026-06-29 04:41:31'),
(339,NULL,'2026-01-26',38,2405324306.00,'Masuk','kredit','','Jasa Manpower IPIP','2026-06-29 04:41:57'),
(340,NULL,'2026-01-27',1,170151072.00,'Masuk','debit','','Jasa Manpower IPK','2026-06-29 04:42:51'),
(341,NULL,'2026-01-27',39,170151072.00,'Masuk','kredit','','Jasa Manpower IPK','2026-06-29 04:43:34'),
(342,NULL,'2026-01-27',1,2545567889.00,'Masuk','debit','','Jasa Manpower IPIP','2026-06-29 04:45:39'),
(343,NULL,'2026-01-27',38,2545567889.00,'Masuk','kredit','','Jasa Manpower IPIP','2026-06-29 04:46:08'),
(344,NULL,'2026-01-26',1,326502345.00,'Masuk','debit','','Jasa Manpower IPK','2026-06-29 04:46:52'),
(345,NULL,'2026-01-26',39,326502345.00,'Masuk','kredit','','Jasa Manpower IPK','2026-06-29 04:47:34'),
(346,NULL,'2026-01-26',1,4038642797.00,'Masuk','debit','','Jasa Manpower IPIP','2026-06-29 04:48:30'),
(347,NULL,'2026-01-26',38,4038642797.00,'Masuk','kredit','','Jasa Manpower IPIP','2026-06-29 04:48:57'),
(348,NULL,'2026-03-10',1,552389007.00,'Masuk','debit','','Jasa Manpower THR','2026-06-29 04:54:15'),
(349,NULL,'2026-03-10',38,552389007.00,'Masuk','kredit','','Jasa Manpower IPIP','2026-06-29 04:55:22'),
(350,NULL,'2026-03-11',1,38518784.00,'Masuk','debit','','Jasa Manpower IPK THR','2026-06-29 04:57:03'),
(351,NULL,'2026-03-11',39,38518784.00,'Masuk','kredit','','Jasa Manpower IPK','2026-06-29 04:57:29'),
(352,NULL,'2026-02-26',1,2536982398.00,'Masuk','debit','','Jasa Manpower HBB','2026-06-29 04:58:31'),
(353,NULL,'2026-02-26',37,2536982398.00,'Masuk','kredit','','Jasa Manpower HBB','2026-06-29 04:59:02'),
(354,NULL,'2026-03-16',1,562040833.00,'Masuk','debit','','THR Jasa Manpower HBB ','2026-06-29 05:01:17'),
(355,NULL,'2026-03-16',37,562040833.00,'Masuk','kredit','','THR Jasa Manpower HBB','2026-06-29 05:02:11'),
(356,NULL,'2026-03-27',1,3549889018.00,'Masuk','debit','','Jasa Manpower HBB','2026-06-29 05:47:24'),
(357,NULL,'2026-03-27',37,3549889018.00,'Masuk','kredit','','Jasa Manpower HBB','2026-06-29 05:47:52'),
(358,NULL,'2026-04-27',1,4216946072.00,'Masuk','debit','','Jasa Manpower HBB','2026-06-29 05:49:10'),
(359,NULL,'2026-04-27',37,4216946072.00,'Masuk','kredit','','Jasa Manpower HBB','2026-06-29 05:49:32'),
(360,NULL,'2026-05-26',1,6156505618.00,'Masuk','debit','','Jasa Manpower HBB','2026-06-29 05:50:58'),
(361,NULL,'2026-05-26',37,6156505618.00,'Masuk','kredit','','Jasa Manpower HBB','2026-06-29 05:51:22'),
(362,NULL,'2026-01-27',1,2780027723.00,'Masuk','debit','','Jasa Manpower HBB','2026-06-29 05:53:45'),
(363,NULL,'2026-01-27',37,2780027723.00,'Masuk','kredit','','Jasa Manpower HBB','2026-06-29 05:54:15'),
(364,NULL,'2026-06-27',55,52500.00,'Keluar','debit','','Uang Makan','2026-06-29 06:15:27'),
(365,NULL,'2026-06-27',3,52500.00,'Masuk','kredit','','Uang Makan','2026-06-29 06:15:49'),
(366,NULL,'2026-06-27',55,209000.00,'Keluar','debit','','Konsumsi Rapat','2026-06-29 06:16:49'),
(367,NULL,'2026-06-27',3,209000.00,'Keluar','kredit','','Konsumsi Rapat','2026-06-29 06:17:23'),
(368,NULL,'2026-06-27',69,37000.00,'Keluar','debit','','','2026-06-29 06:18:07'),
(369,NULL,'2026-06-27',3,37000.00,'Keluar','kredit','','','2026-06-29 06:18:26'),
(370,NULL,'2026-06-29',50,200000.00,'Keluar','debit','','Pembelian Materai','2026-06-29 06:20:40'),
(371,NULL,'2026-06-29',3,200000.00,'Keluar','kredit','','Pembelian Materai','2026-06-29 06:21:01'),
(372,NULL,'2026-06-29',67,1000000.00,'Keluar','debit','','Sumbangan Aksi Demo Formakom','2026-06-29 06:21:50'),
(373,NULL,'2026-06-29',3,1000000.00,'Keluar','kredit','','Sumbangan Aksi Demo Formakom','2026-06-29 06:22:22'),
(374,NULL,'2026-06-29',28,27000000.00,'Keluar','debit','','Angsuran Hutang ke BRI','2026-06-29 06:24:52'),
(375,NULL,'2026-06-29',3,27000000.00,'Keluar','kredit','','Angsuran Hutang ke BRI','2026-06-29 06:25:24'),
(376,NULL,'2026-06-29',6,5000000.00,'Keluar','debit','','Tanda Jadi Mobil Rush','2026-06-29 06:26:27'),
(377,NULL,'2026-06-29',3,5000000.00,'Keluar','kredit','','Tanda Jadi Mobil Rush','2026-06-29 06:26:57'),
(378,NULL,'2026-06-28',55,55000.00,'Keluar','debit','','Uang Makan','2026-06-29 06:28:53'),
(379,NULL,'2026-06-28',1,55000.00,'Keluar','kredit','','Uang Makan','2026-06-29 06:29:47'),
(380,NULL,'2026-06-29',55,57000.00,'Keluar','debit','','Konsumsi','2026-06-29 06:31:27'),
(381,NULL,'2026-06-29',1,57000.00,'Keluar','kredit','','Konsumsi','2026-06-29 06:32:00'),
(382,NULL,'2026-06-28',55,120000.00,'Keluar','debit','','Pembelian Minuman','2026-06-29 06:32:51'),
(383,NULL,'2026-06-28',1,120000.00,'Keluar','kredit','','Pembelian Minuman','2026-06-29 06:33:23'),
(384,NULL,'2026-06-28',55,103000.00,'Keluar','debit','','Uang Makan','2026-06-29 06:34:05'),
(385,NULL,'2026-06-28',1,103000.00,'Keluar','kredit','','Uang Makan','2026-06-29 06:34:26'),
(386,NULL,'2026-06-29',61,4006500.00,'Keluar','debit','','Tanduk Rush Baru Depan Belakang','2026-06-29 06:36:21'),
(387,NULL,'2026-06-29',3,4006500.00,'Keluar','kredit','','Tanduk Rush Baru Depan Belakang','2026-06-29 06:36:52'),
(388,NULL,'2026-06-29',69,80000.00,'Keluar','debit','','Biaya Pencucian Mobil','2026-06-29 06:37:53'),
(389,NULL,'2026-06-29',1,80000.00,'Keluar','kredit','','Biaya Pencucian Mobil','2026-06-29 06:38:25'),
(390,NULL,'2026-06-29',1,258308130.00,'Masuk','debit','','Jasa Manpower IPK','2026-06-30 04:55:31'),
(391,NULL,'2026-06-29',39,258308130.00,'Masuk','kredit','','Jasa Manpower IPK','2026-06-30 04:56:47'),
(392,NULL,'2026-06-29',1,2957611335.00,'Masuk','debit','','Jasa Manpower IPIP','2026-06-30 04:57:43'),
(393,NULL,'2026-06-29',38,2957611335.00,'Masuk','kredit','','Jasa Manpower IPIP','2026-06-30 04:58:22'),
(394,NULL,'2026-06-30',6,49450000.00,'Keluar','debit','','Pelunasan DP Rush','2026-07-01 00:43:58'),
(395,NULL,'2026-06-30',3,49450000.00,'Keluar','kredit','','Pelunasan DP Rush','2026-07-01 00:44:26'),
(396,NULL,'2026-06-02',42,680000.00,'Keluar','debit','','Pembelian BBM untuk Pembabatan, Motor & Mobil','2026-07-01 01:27:00'),
(397,NULL,'2026-06-02',1,680000.00,'Keluar','kredit','','Pembelian BBM untuk Pembabatan, Motor & Mobil','2026-07-01 01:27:34'),
(398,NULL,'2026-06-03',42,180000.00,'Keluar','debit','','Pembelian BBM untuk Pembabatan','2026-07-01 01:28:33'),
(399,NULL,'2026-06-03',1,180000.00,'Keluar','kredit','','Pembelian BBM untuk Pembabatan','2026-07-01 01:29:00'),
(400,NULL,'2026-06-04',42,280000.00,'Keluar','debit','','Pembelian BBM untuk Pembabatan, Motor','2026-07-01 01:29:45'),
(401,NULL,'2026-06-04',1,280000.00,'Keluar','kredit','','Pembelian BBM untuk Pembabatan, Motor ','2026-07-01 01:30:08'),
(402,NULL,'2026-06-05',42,180000.00,'Keluar','debit','','Pembelian BBM untuk Pembabatan','2026-07-01 01:30:44'),
(403,NULL,'2026-06-05',1,180000.00,'Keluar','kredit','','Pembelian BBM untuk Pembabatan','2026-07-01 01:31:05'),
(404,NULL,'2026-06-08',42,750000.00,'Keluar','debit','','Pembelian BBM untuk Pembabatan, Motor & Mobil','2026-07-01 01:32:31'),
(405,NULL,'2026-06-08',1,750000.00,'Keluar','kredit','','Pembelian BBM untuk Pembabatan, Motor & Mobil','2026-07-01 01:32:51'),
(406,NULL,'2026-06-09',42,250000.00,'Keluar','debit','','Pembelian BBM untuk Pembabatan','2026-07-01 01:33:41'),
(407,NULL,'2026-06-09',1,250000.00,'Keluar','kredit','','Pembelian BBM untuk Pembabatan','2026-07-01 01:34:05'),
(408,NULL,'2026-06-10',42,250000.00,'Keluar','debit','','Pembelian BBM untuk Pembabatan','2026-07-01 01:39:40'),
(409,NULL,'2026-06-10',1,250000.00,'Keluar','kredit','','Pembelian BBM untuk Pembabatan','2026-07-01 01:40:01'),
(410,NULL,'2026-06-11',42,350000.00,'Keluar','debit','','Pembelian BBM untuk Pembabatan, Motor','2026-07-01 01:40:49'),
(411,NULL,'2026-06-11',1,350000.00,'Keluar','kredit','','Pembelian BBM untuk Pembabatan, Motor','2026-07-01 01:41:20'),
(412,NULL,'2026-06-12',42,650000.00,'Keluar','debit','','Pembelian BBM untuk Pembabatan & Mobil','2026-07-01 01:43:01'),
(413,NULL,'2026-06-12',1,650000.00,'Keluar','kredit','','Pembelian BBM untuk Pembabatan & Mobil','2026-07-01 01:43:29'),
(414,NULL,'2026-06-15',42,350000.00,'Keluar','debit','','Pembelian BBM untuk Pembabatan, Motor ','2026-07-01 01:44:31'),
(415,NULL,'2026-06-15',1,350000.00,'Keluar','kredit','','Pembelian BBM untuk Pembabatan, Motor','2026-07-01 01:44:56'),
(416,NULL,'2026-06-17',42,250000.00,'Keluar','debit','','Pembelian BBM untuk Pembabatan','2026-07-01 02:43:13'),
(417,NULL,'2026-06-17',1,250000.00,'Keluar','kredit','','Pembelian BBM untuk Pembabatan','2026-07-01 02:43:36'),
(418,NULL,'2026-06-18',42,747440.00,'Keluar','debit','','Pembelian BBM untuk Pembabatan, Motor & Mobil','2026-07-01 02:48:42'),
(419,NULL,'2026-06-18',1,747440.00,'Keluar','kredit','','Pembelian BBM untuk Pembabatan, Motor & Mobil','2026-07-01 02:50:30'),
(420,NULL,'2026-06-19',42,250000.00,'Keluar','debit','','Pembelian BBM untuk Pembabatan','2026-07-01 02:53:36'),
(421,NULL,'2026-06-19',1,250000.00,'Keluar','kredit','','Pembelian BBM untuk Pembabatan','2026-07-01 02:53:59'),
(422,NULL,'2026-06-22',42,350000.00,'Keluar','debit','','Pembelian BBM untuk Pembabatan, Motor','2026-07-01 04:33:09'),
(423,NULL,'2026-06-22',1,350000.00,'Keluar','kredit','','Pembelian BBM untuk Pembabatan, Motor ','2026-07-01 04:33:31'),
(424,NULL,'2026-06-23',42,250000.00,'Keluar','debit','','Pembelian BBM untuk Pembabatan','2026-07-01 04:45:32'),
(425,NULL,'2026-06-23',1,250000.00,'Keluar','kredit','','Pembelian BBM untuk Pembabatan','2026-07-01 04:45:54'),
(426,NULL,'2026-06-24',42,250000.00,'Keluar','debit','','Pembelian BBM untuk Pembabatan','2026-07-01 04:56:59'),
(427,NULL,'2026-06-24',1,250000.00,'Keluar','kredit','','Pembelian BBM untuk Pembabatan','2026-07-01 04:57:53'),
(428,NULL,'2026-06-25',42,750000.00,'Keluar','debit','','Pembelian BBM untuk Pembabatan, Motor & Mobil','2026-07-01 05:01:37'),
(429,NULL,'2026-06-25',1,750000.00,'Keluar','kredit','','Pembelian BBM untuk Pembabatan, Motor & Mobil','2026-07-01 05:02:00'),
(430,NULL,'2026-06-27',42,150000.00,'Keluar','debit','','Pembelian BBM untuk Pembabatan','2026-07-01 05:56:15'),
(431,NULL,'2026-06-27',1,150000.00,'Keluar','kredit','','Pembelian BBM untuk Pembabatan','2026-07-01 05:56:40'),
(432,NULL,'2026-06-29',42,400000.00,'Keluar','debit','','Pembelian BBM untuk Pembabatan, Motor','2026-07-01 05:57:24'),
(433,NULL,'2026-06-29',1,400000.00,'Keluar','kredit','','Pembelian BBM untuk Pembabatan, Motor','2026-07-01 05:57:49'),
(435,NULL,'2026-06-30',42,300000.00,'Keluar','debit','','Pembelian BBM untuk Pembabatan','2026-07-01 05:59:09'),
(448,NULL,'2026-06-30',1,300000.00,'Keluar','kredit','','Pembelian BBM untuk Pembabatan','2026-07-01 06:14:02'),
(449,NULL,'2026-06-26',1,5063456439.00,'Masuk','debit','','Jasa Manpower HBB','2026-07-01 06:16:39'),
(450,NULL,'2026-06-26',37,5063456439.00,'Masuk','kredit','','Jasa Manpower HBB','2026-07-01 06:17:09'),
(451,NULL,'2026-06-02',44,400000.00,'Keluar','debit','','Tasi Kos Tangan dan Lain-lain','2026-07-02 02:25:13'),
(452,NULL,'2026-06-02',1,400000.00,'Keluar','kredit','','Tasi Kos Tangan dan Lain-lain','2026-07-02 02:25:34'),
(453,NULL,'2026-06-02',61,600000.00,'Keluar','debit','','Sparepart Mesin','2026-07-02 02:27:19'),
(454,NULL,'2026-06-02',1,600000.00,'Keluar','kredit','','Sparepart Mesin','2026-07-02 02:27:49'),
(455,NULL,'2026-06-04',50,100000.00,'Keluar','debit','','Materai','2026-07-02 02:28:47'),
(456,NULL,'2026-06-04',1,100000.00,'Keluar','kredit','','Materai','2026-07-02 02:29:14'),
(457,NULL,'2026-06-08',44,100000.00,'Keluar','debit','','Tasi Kos Tangan dan Lain-lain','2026-07-02 02:30:12'),
(458,NULL,'2026-06-08',1,100000.00,'Keluar','kredit','','Tasi Kos Tangan dan Lain-lain','2026-07-02 02:31:52'),
(459,NULL,'2026-06-08',61,1500000.00,'Keluar','debit','','Sparepart Mesin','2026-07-02 02:32:44'),
(460,NULL,'2026-06-08',1,1500000.00,'Keluar','kredit','','Sparepart Mesin','2026-07-02 02:33:11'),
(461,NULL,'2026-06-15',44,400000.00,'Masuk','debit','','Tasi Kos Tangan dan Lain-lain','2026-07-02 02:33:42'),
(462,NULL,'2026-06-15',1,400000.00,'Keluar','kredit','','Tasi Kos Tangan dan Lain-lain','2026-07-02 02:34:03'),
(463,NULL,'2026-06-15',56,203000.00,'Keluar','debit','','Pembayaran Listrik Kantor','2026-07-02 02:35:37'),
(464,NULL,'2026-06-15',1,203000.00,'Keluar','kredit','','Pembayaran Listrik Kantor','2026-07-02 02:36:04'),
(465,NULL,'2026-06-18',61,550000.00,'Keluar','debit','','Sparepart Mesin','2026-07-02 02:36:51'),
(466,NULL,'2026-06-18',1,550000.00,'Keluar','kredit','','Sparepart Mesin','2026-07-02 02:37:17'),
(467,NULL,'2026-06-22',44,100000.00,'Keluar','debit','','Tasi Kos Tangan dan Lain-lain','2026-07-02 02:37:59'),
(468,NULL,'2026-06-22',1,100000.00,'Keluar','kredit','','Tasi Kos Tangan dan Lain-lain','2026-07-02 02:38:19'),
(469,NULL,'2026-06-29',44,450000.00,'Keluar','debit','','Tasi Kos Tangan dan Lain-lain','2026-07-02 02:39:07'),
(470,NULL,'2026-06-29',1,450000.00,'Keluar','kredit','','Tasi Kos Tangan dan Lain-lain','2026-07-02 02:39:31'),
(483,NULL,'2026-06-10',1,31098401.00,'Keluar','kredit','','Pinjaman Karyawan','2026-07-02 03:27:46'),
(484,NULL,'2026-06-01',1,6988641605.00,'Keluar','kredit','','','2026-07-02 05:22:11'),
(485,NULL,'2026-06-01',1,113578450.00,'Keluar','kredit','','Pinjaman Karyawan','2026-07-02 05:29:43'),
(486,NULL,'2026-06-01',1,94796209.00,'Masuk','debit','','Uang Titipan','2026-07-02 05:31:10'),
(487,NULL,'2026-06-18',1,56000000.00,'Masuk','debit','','Pembayaran Hutang Karyawan','2026-07-02 05:35:46'),
(488,NULL,'2026-06-22',1,20750000.00,'Keluar','debit','','Pembayaran Hutang Karyawan','2026-07-02 05:41:25'),
(489,NULL,'2026-06-23',1,7530000.00,'Masuk','debit','','Pembayaran Hutang Karyawan','2026-07-02 05:42:43'),
(490,NULL,'2026-06-24',1,9250000.00,'Masuk','debit','','Pembayaran Hutang Karyawan','2026-07-02 05:44:04'),
(491,NULL,'2026-06-26',1,380000.00,'Keluar','kredit','','Buka Jahitan Rionaldo Samosir','2026-07-02 05:46:52'),
(492,NULL,'2026-06-25',1,5300000.00,'Masuk','debit','','Pembayaran hutang Karyawan','2026-07-02 05:52:04'),
(493,NULL,'2026-05-01',1,7815038127.00,'Keluar','kredit','','Ket: Auto-Posting Payroll: 21/03/26 - 20/04/26 - Beban Gaji','2026-07-02 05:54:38'),
(495,NULL,'2026-07-01',9,34850000.00,'Keluar','debit',NULL,'Auto-Posting Pencairan Pinjaman: Tanggal 01/07/2026','2026-07-02 07:47:36'),
(496,NULL,'2026-07-01',1,34850000.00,'Keluar','kredit',NULL,'Auto-Posting Pencairan Pinjaman: Tanggal 01/07/2026','2026-07-02 07:47:36'),
(497,NULL,'2026-07-02',9,7500000.00,'Keluar','debit',NULL,'Auto-Posting Pencairan Pinjaman: Tanggal 02/07/2026','2026-07-02 07:47:36'),
(498,NULL,'2026-07-02',1,7500000.00,'Keluar','kredit',NULL,'Auto-Posting Pencairan Pinjaman: Tanggal 02/07/2026','2026-07-02 07:47:36'),
(499,NULL,'2026-06-10',9,47098401.00,'Keluar','debit',NULL,'Auto-Posting Pencairan Pinjaman: Tanggal 10/06/2026','2026-07-02 07:54:38'),
(500,NULL,'2026-06-10',1,47098401.00,'Keluar','kredit',NULL,'Auto-Posting Pencairan Pinjaman: Tanggal 10/06/2026','2026-07-02 07:54:38'),
(501,NULL,'2026-06-18',9,55000000.00,'Keluar','debit',NULL,'Auto-Posting Pencairan Pinjaman: Tanggal 18/06/2026','2026-07-02 07:54:38'),
(502,NULL,'2026-06-18',1,55000000.00,'Keluar','kredit',NULL,'Auto-Posting Pencairan Pinjaman: Tanggal 18/06/2026','2026-07-02 07:54:38'),
(503,NULL,'2026-06-22',9,20750000.00,'Keluar','debit',NULL,'Auto-Posting Pencairan Pinjaman: Tanggal 22/06/2026','2026-07-02 07:54:38'),
(504,NULL,'2026-06-22',1,20750000.00,'Keluar','kredit',NULL,'Auto-Posting Pencairan Pinjaman: Tanggal 22/06/2026','2026-07-02 07:54:38'),
(505,NULL,'2026-06-23',9,7500000.00,'Keluar','debit',NULL,'Auto-Posting Pencairan Pinjaman: Tanggal 23/06/2026','2026-07-02 07:54:38'),
(506,NULL,'2026-06-23',1,7500000.00,'Keluar','kredit',NULL,'Auto-Posting Pencairan Pinjaman: Tanggal 23/06/2026','2026-07-02 07:54:38'),
(507,NULL,'2026-06-24',9,9250000.00,'Keluar','debit',NULL,'Auto-Posting Pencairan Pinjaman: Tanggal 24/06/2026','2026-07-02 07:54:38'),
(508,NULL,'2026-06-24',1,9250000.00,'Keluar','kredit',NULL,'Auto-Posting Pencairan Pinjaman: Tanggal 24/06/2026','2026-07-02 07:54:38'),
(509,NULL,'2026-06-25',9,5300000.00,'Keluar','debit',NULL,'Auto-Posting Pencairan Pinjaman: Tanggal 25/06/2026','2026-07-02 07:54:38'),
(510,NULL,'2026-06-25',1,5300000.00,'Keluar','kredit',NULL,'Auto-Posting Pencairan Pinjaman: Tanggal 25/06/2026','2026-07-02 07:54:38'),
(511,NULL,'2026-06-29',9,2500000.00,'Keluar','debit',NULL,'Auto-Posting Pencairan Pinjaman: Tanggal 29/06/2026','2026-07-02 07:54:38'),
(512,NULL,'2026-06-29',1,2500000.00,'Keluar','kredit',NULL,'Auto-Posting Pencairan Pinjaman: Tanggal 29/06/2026','2026-07-02 07:54:38'),
(513,NULL,'2026-06-30',9,78300000.00,'Keluar','debit',NULL,'Auto-Posting Pencairan Pinjaman: Tanggal 30/06/2026','2026-07-02 07:54:38'),
(514,NULL,'2026-06-30',1,78300000.00,'Keluar','kredit',NULL,'Auto-Posting Pencairan Pinjaman: Tanggal 30/06/2026','2026-07-02 07:54:38'),
(515,NULL,'2026-05-10',9,26000000.00,'Keluar','debit',NULL,'Auto-Posting Pencairan Pinjaman: Tanggal 10/05/2026','2026-07-02 07:56:40'),
(516,NULL,'2026-05-10',1,26000000.00,'Keluar','kredit',NULL,'Auto-Posting Pencairan Pinjaman: Tanggal 10/05/2026','2026-07-02 07:56:40'),
(517,NULL,'2026-05-18',9,90578450.00,'Keluar','debit',NULL,'Auto-Posting Pencairan Pinjaman: Tanggal 18/05/2026','2026-07-02 07:56:40'),
(518,NULL,'2026-05-18',1,90578450.00,'Keluar','kredit',NULL,'Auto-Posting Pencairan Pinjaman: Tanggal 18/05/2026','2026-07-02 07:56:40'),
(519,NULL,'2026-05-01',1,34049000.00,'Keluar','kredit','','Pelunasan Pinjaman Karyawan','2026-07-02 08:09:14'),
(520,NULL,'2026-05-01',1,311635131.00,'Masuk','debit','','Pendapatan Denda/Sanksi','2026-07-02 08:11:05'),
(521,NULL,'2026-05-01',1,98704347.00,'Masuk','debit','','Sumbangan Titipan','2026-07-02 08:11:45'),
(523,NULL,'2026-04-10',1,14300000.00,'Masuk','kredit','','Pinjaman Karyawan','2026-07-02 08:24:46'),
(524,NULL,'2026-04-11',1,18749000.00,'Keluar','kredit','','Pinjaman Karyawan','2026-07-02 08:30:06'),
(525,NULL,'2026-06-10',9,31098401.00,'Keluar','debit','','Pinjaman Karyawan','2026-07-02 08:42:14'),
(526,NULL,'2026-06-18',9,56000000.00,'Masuk','kredit','','Pembayaran Hutang karyawan','2026-07-02 08:46:41'),
(527,NULL,'2026-06-22',9,20750000.00,'Masuk','kredit','','Pembayaran Hutang Karyawan','2026-07-02 08:48:21'),
(528,NULL,'2026-06-23',9,7530000.00,'Masuk','kredit','','Pembayaran Hutang Karyawan','2026-07-03 01:31:42'),
(529,NULL,'2026-06-24',9,9250000.00,'Masuk','kredit','','Pembayaran Hutang Karyawan','2026-07-03 01:33:40'),
(530,NULL,'2026-06-25',9,5300000.00,'Masuk','kredit','','Pembayaran Hutang Karyawan','2026-07-03 01:36:00'),
(531,NULL,'2026-02-26',64,380000.00,'Keluar','debit','','Buka Jahitan Rionaldo Samosir','2026-07-03 01:42:18'),
(537,NULL,'2026-02-01',47,5161496582.00,'Keluar','debit','','Beban Gaji Periode Des-Jan ','2026-07-03 05:42:44'),
(538,NULL,'2026-02-01',1,5161496582.00,'Keluar','kredit','','Beban Gaji Periode Des-Jan','2026-07-03 05:43:25'),
(539,NULL,'2026-03-01',47,4829045515.00,'Keluar','debit','','Beban Gaji Periode Jan-Feb','2026-07-03 05:53:49'),
(540,NULL,'2026-03-01',1,4829045515.00,'Keluar','kredit','','Beban Gaji Periode Jan-Feb','2026-07-03 05:54:14'),
(541,NULL,'2026-04-01',47,5519666084.00,'Keluar','debit','','Beban Gaji Periode Feb-Mar','2026-07-03 05:56:40'),
(542,NULL,'2026-04-01',1,5519666084.00,'Keluar','kredit','','Beban Gaji Periode Feb-Mar','2026-07-03 05:57:11'),
(581,NULL,'2026-07-14',59,334986240.00,'Keluar','debit','','Pembayaran Beban Pajak','2026-07-15 01:48:48'),
(582,NULL,'2026-07-14',3,334986240.00,'Keluar','kredit','','[Auto] Pembayaran Beban Pajak','2026-07-15 01:48:48'),
(583,NULL,'2026-07-14',66,269745438.00,'Keluar','debit','','Pembayaran Beban Pajak','2026-07-15 01:52:06'),
(584,NULL,'2026-07-14',3,269745438.00,'Keluar','kredit','','[Auto] Pembayaran Beban Pajak','2026-07-15 01:52:06'),
(585,NULL,'2026-07-14',66,7038990.00,'Keluar','debit','','Pembayaran Beban BPJS','2026-07-15 01:52:59'),
(586,NULL,'2026-07-14',3,7038990.00,'Keluar','kredit','','[Auto] Pembayaran Beban BPJS','2026-07-15 01:52:59'),
(587,NULL,'2026-07-14',66,35787504.00,'Keluar','debit','','Pembayaran Beban BPJS','2026-07-15 01:53:46'),
(588,NULL,'2026-07-14',3,35787504.00,'Keluar','kredit','','[Auto] Pembayaran Beban BPJS','2026-07-15 01:53:46'),
(589,NULL,'2026-07-15',76,6686500.00,'Keluar','debit','','Pembayaran Angsuran Mobil Rush','2026-07-16 01:19:26'),
(590,NULL,'2026-07-15',1,6686500.00,'Keluar','kredit','','[Auto] Pembayaran Angsuran Mobil Rush','2026-07-16 01:19:26'),
(591,NULL,'2026-07-15',76,7786500.00,'Keluar','debit','','Pembayaran Angsuran Mobil Box','2026-07-16 01:20:44'),
(592,NULL,'2026-07-15',3,7786500.00,'Keluar','kredit','','[Auto] Pembayaran Angsuran Mobil Box','2026-07-16 01:20:44'),
(593,NULL,'2026-07-15',76,7786500.00,'Keluar','debit','','Pembayaran Angsuran Mobil Box','2026-07-16 01:22:20'),
(594,NULL,'2026-07-15',3,7786500.00,'Keluar','kredit','','[Auto] Pembayaran Angsuran Mobil Box','2026-07-16 01:22:20'),
(595,NULL,'2026-07-15',56,10388691.00,'Keluar','debit','','Pembayaran Listrik Hotel','2026-07-16 01:25:26'),
(596,NULL,'2026-07-15',3,10388691.00,'Keluar','kredit','','[Auto] Pembayaran Listrik Hotel','2026-07-16 01:25:26'),
(597,NULL,'2026-07-15',59,411143635.00,'Keluar','debit','','Pembayaran Beban Pajak','2026-07-16 01:26:38'),
(598,NULL,'2026-07-15',3,411143635.00,'Keluar','kredit','','[Auto] Pembayaran Beban Pajak','2026-07-16 01:26:38'),
(601,NULL,'2026-07-15',45,12216500.00,'Keluar','debit','','Pelunasan Baju Dami 109 pcs','2026-07-16 01:31:19'),
(602,NULL,'2026-07-15',3,12216500.00,'Keluar','kredit','','[Auto] Pelunasan Baju Dami 109 pcs','2026-07-16 01:31:19'),
(603,NULL,'2026-07-01',55,580000.00,'Keluar','debit','','Pembelian Konsumsi Untuk Rapat','2026-07-17 02:00:20'),
(604,NULL,'2026-07-01',3,580000.00,'Keluar','kredit','','[Auto] Pembelian Konsumsi Untuk Rapat','2026-07-17 02:00:20'),
(605,NULL,'2026-07-01',42,1400000.00,'Keluar','debit','','Pembelian BBM','2026-07-17 02:01:05'),
(606,NULL,'2026-07-01',3,1400000.00,'Keluar','kredit','','[Auto] Pembelian BBM','2026-07-17 02:01:05'),
(607,NULL,'2026-07-03',47,2365066614.00,'Keluar','debit','','Pembayaran Gaji Karyawan','2026-07-17 02:02:06'),
(608,NULL,'2026-07-03',3,2365066614.00,'Keluar','kredit','','[Auto] Pembayaran Gaji Karyawan','2026-07-17 02:02:06'),
(609,NULL,'2026-07-03',47,209964579.00,'Keluar','debit','','Pembayaran gaji karyawan','2026-07-17 02:03:05'),
(610,NULL,'2026-07-03',3,209964579.00,'Keluar','kredit','','[Auto] Pembayaran gaji karyawan','2026-07-17 02:03:05'),
(611,NULL,'2026-07-03',47,3328216654.00,'Keluar','debit','','Pembayaran gaji karyawan','2026-07-17 02:04:23'),
(612,NULL,'2026-07-03',3,3328216654.00,'Keluar','kredit','','[Auto] Pembayaran gaji karyawan','2026-07-17 02:04:23'),
(613,NULL,'2026-07-03',47,492931274.00,'Keluar','debit','','Pembayaran gaji karyawan','2026-07-17 02:05:08'),
(614,NULL,'2026-07-03',3,492931274.00,'Keluar','kredit','','[Auto] Pembayaran gaji karyawan','2026-07-17 02:05:08'),
(615,NULL,'2026-07-03',47,12043079.00,'Keluar','debit','','Pembayaran gaji karyawan','2026-07-17 02:09:25'),
(616,NULL,'2026-07-03',3,12043079.00,'Keluar','kredit','','[Auto] Pembayaran gaji karyawan','2026-07-17 02:09:25'),
(617,NULL,'2026-07-04',42,300000.00,'Keluar','debit','','Pembelian BBM','2026-07-17 02:10:01'),
(618,NULL,'2026-07-04',1,300000.00,'Keluar','kredit','','[Auto] Pembelian BBM','2026-07-17 02:10:01'),
(619,NULL,'2026-07-04',69,80000.00,'Keluar','debit','','Biaya pencucian mobil','2026-07-17 02:10:47'),
(620,NULL,'2026-07-04',1,80000.00,'Keluar','kredit','','[Auto] Biaya pencucian mobil','2026-07-17 02:10:47'),
(621,NULL,'2026-07-04',47,38158750.00,'Keluar','debit','','','2026-07-17 02:13:05'),
(622,NULL,'2026-07-04',3,38158750.00,'Keluar','kredit','','[Auto] ','2026-07-17 02:13:05'),
(623,NULL,'2026-07-04',47,1806500.00,'Keluar','debit','','Uang Iwin Setiawan','2026-07-17 02:14:04'),
(624,NULL,'2026-07-04',3,1806500.00,'Keluar','kredit','','[Auto] Uang Iwin Setiawan','2026-07-17 02:14:04'),
(625,NULL,'2026-07-07',47,2000000.00,'Keluar','debit','','Sisa gaji Alfin','2026-07-17 02:15:08'),
(626,NULL,'2026-07-07',3,2000000.00,'Keluar','kredit','','[Auto] Sisa gaji Alfin','2026-07-17 02:15:08'),
(627,NULL,'2026-07-08',64,455000.00,'Keluar','debit','','Biaya RS','2026-07-17 02:16:00'),
(628,NULL,'2026-07-08',3,455000.00,'Keluar','kredit','','[Auto] Biaya RS','2026-07-17 02:16:00'),
(629,NULL,'2026-07-08',42,189000.00,'Keluar','debit','','Pembelian BBM','2026-07-17 02:16:33'),
(630,NULL,'2026-07-08',1,189000.00,'Keluar','kredit','','[Auto] Pembelian BBM','2026-07-17 02:16:33'),
(631,NULL,'2026-06-27',59,315580551.00,'Keluar','debit','','Beban Pajak','2026-07-17 02:22:49'),
(632,NULL,'2026-06-27',3,315580551.00,'Keluar','kredit','','[Auto] Beban Pajak','2026-07-17 02:22:49'),
(633,NULL,'2026-01-28',6,5000000.00,'Keluar','debit','','Pembayaran uang muka LV','2026-07-18 01:30:47'),
(634,NULL,'2026-01-28',3,5000000.00,'Keluar','kredit','','[Auto] Pembayaran uang muka LV','2026-07-18 01:30:47'),
(637,NULL,'2026-06-10',66,275290910.00,'Keluar','debit','','Pembayaran BPJS Ketenagakerjaan','2026-07-18 01:44:21'),
(638,NULL,'2026-06-10',3,275290910.00,'Keluar','kredit','','[Auto] Pembayaran BPJS Ketenagakerjaan','2026-07-18 01:44:21'),
(641,NULL,'2026-07-15',68,1200000.00,'Keluar','debit','','Pembelian meja kantor','2026-07-18 02:12:16'),
(642,NULL,'2026-07-15',3,1200000.00,'Keluar','kredit','','[Auto] Pembelian meja kantor','2026-07-18 02:12:16'),
(643,NULL,'2026-07-15',45,12216500.00,'Keluar','debit','','Pelunasan baju dami 109 pcs','2026-07-18 02:13:10'),
(644,NULL,'2026-07-15',3,12216500.00,'Keluar','kredit','','[Auto] Pelunasan baju dami 109 pcs','2026-07-18 02:13:10'),
(645,NULL,'2026-07-16',55,106000.00,'Keluar','debit','','Pembelian kopi','2026-07-18 02:13:45'),
(646,NULL,'2026-07-16',3,106000.00,'Keluar','kredit','','[Auto] Pembelian kopi','2026-07-18 02:13:45'),
(647,NULL,'2026-07-16',42,100000.00,'Keluar','debit','','Pembelian BBM','2026-07-18 02:15:06'),
(648,NULL,'2026-07-16',3,100000.00,'Keluar','kredit','','[Auto] Pembelian BBM','2026-07-18 02:15:06'),
(649,NULL,'2026-07-16',42,100000.00,'Keluar','debit','','Pembelian BBM eceran','2026-07-18 02:15:35'),
(650,NULL,'2026-07-16',3,100000.00,'Keluar','kredit','','[Auto] Pembelian BBM eceran','2026-07-18 02:15:35'),
(651,NULL,'2026-07-16',42,350000.00,'Keluar','debit','','Pembelian BBM','2026-07-18 02:16:15'),
(652,NULL,'2026-07-16',1,350000.00,'Keluar','kredit','','[Auto] Pembelian BBM','2026-07-18 02:16:15'),
(653,NULL,'2026-07-13',42,300000.00,'Keluar','debit','','Pembelian BBM','2026-07-18 02:16:46'),
(654,NULL,'2026-07-13',1,300000.00,'Keluar','kredit','','[Auto] Pembelian BBM','2026-07-18 02:16:46'),
(655,NULL,'2026-07-14',42,300000.00,'Keluar','debit','','Pembelian BBM','2026-07-18 02:17:40'),
(656,NULL,'2026-07-14',1,300000.00,'Keluar','kredit','','[Auto] Pembelian BBM','2026-07-18 02:17:40'),
(657,NULL,'2026-07-09',42,300000.00,'Keluar','debit','','Pembelian BBM','2026-07-18 02:18:34'),
(658,NULL,'2026-07-09',1,300000.00,'Keluar','kredit','','[Auto] Pembelian BBM','2026-07-18 02:18:34'),
(659,NULL,'2026-07-16',55,402500.00,'Keluar','debit','','Pengeluaran konsumsi untuk jubir','2026-07-18 02:19:33'),
(660,NULL,'2026-07-16',3,402500.00,'Keluar','kredit','','[Auto] Pengeluaran konsumsi untuk jubir','2026-07-18 02:19:33'),
(661,NULL,'2026-07-17',56,503000.00,'Keluar','debit','','Pembayaran listrik kantor','2026-07-18 02:21:43'),
(662,NULL,'2026-07-17',3,503000.00,'Keluar','kredit','','[Auto] Pembayaran listrik kantor','2026-07-18 02:21:43'),
(665,'REF-1784515859-127','2026-07-18',42,300000.00,'Keluar','debit','','Pembelian BBM','2026-07-20 02:50:59'),
(666,'REF-1784515859-127','2026-07-18',3,300000.00,'Keluar','kredit','','[Auto] Pembelian BBM','2026-07-20 02:50:59'),
(667,'REF-1784515987-503','2026-07-19',55,287500.00,'Keluar','debit','','Pembelian kopi saat rapat','2026-07-20 02:53:07'),
(668,'REF-1784515987-503','2026-07-19',3,287500.00,'Keluar','kredit','','[Auto] Pembelian kopi saat rapat','2026-07-20 02:53:07'),
(669,'REF-1784516017-168','2026-07-19',42,300000.00,'Keluar','debit','','Pembelian BBM','2026-07-20 02:53:37'),
(670,'REF-1784516017-168','2026-07-19',1,300000.00,'Keluar','kredit','','[Auto] Pembelian BBM','2026-07-20 02:53:37'),
(671,'REF-1784617598-780','2026-07-20',55,200000.00,'Keluar','debit','','Konsumsi rapat','2026-07-21 07:06:38'),
(672,'REF-1784617598-780','2026-07-20',3,200000.00,'Keluar','kredit','','[Auto] Konsumsi rapat','2026-07-21 07:06:38'),
(673,'REF-1784617633-200','2026-07-21',57,150000.00,'Keluar','debit','','Biaya ongkos kirim','2026-07-21 07:07:13'),
(674,'REF-1784617633-200','2026-07-21',3,150000.00,'Keluar','kredit','','[Auto] Biaya ongkos kirim','2026-07-21 07:07:13'),
(675,'REF-1784617824-946','2026-07-21',56,485650.00,'Keluar','debit','','Pembayaran WI-FI kantor','2026-07-21 07:10:24'),
(676,'REF-1784617824-946','2026-07-21',3,485650.00,'Keluar','kredit','','[Auto] Pembayaran WI-FI kantor','2026-07-21 07:10:24'),
(677,'REF-1784617867-131','2026-07-21',56,319150.00,'Keluar','debit','','Pembayaran WI-FI hotel','2026-07-21 07:11:07'),
(678,'REF-1784617867-131','2026-07-21',3,319150.00,'Keluar','kredit','','[Auto] Pembayaran WI-FI hotel','2026-07-21 07:11:07'),
(679,'REF-1784704111-658','2026-07-21',42,101000.00,'Keluar','debit','','Pembelian Bensin','2026-07-22 07:08:31'),
(680,'REF-1784704111-658','2026-07-21',3,101000.00,'Keluar','kredit','','[Auto] Pembelian Bensin','2026-07-22 07:08:31'),
(681,'REF-1784857919-568','2026-07-22',69,140000.00,'Keluar','debit','','Biaya Pencucian Mobil','2026-07-24 01:51:59'),
(682,'REF-1784857919-568','2026-07-22',3,140000.00,'Keluar','kredit','','[Auto] Biaya Pencucian Mobil','2026-07-24 01:51:59'),
(683,'REF-1784858075-356','2026-07-23',44,86500.00,'Keluar','debit','','Pembelian Helm Merah 2 Pcs','2026-07-24 01:54:35'),
(684,'REF-1784858075-356','2026-07-23',3,86500.00,'Keluar','kredit','','[Auto] Pembelian Helm Merah 2 Pcs','2026-07-24 01:54:35'),
(685,'REF-1785115977-727','2026-07-20',55,99500.00,'Keluar','debit','','Biaya konsumsi','2026-07-27 01:32:57'),
(686,'REF-1785115977-727','2026-07-20',3,99500.00,'Keluar','kredit','','[Auto] Biaya konsumsi','2026-07-27 01:32:57'),
(687,'REF-1785116044-768','2026-07-20',55,50000.00,'Keluar','debit','','Biaya konsumsi','2026-07-27 01:34:04'),
(688,'REF-1785116044-768','2026-07-20',3,50000.00,'Keluar','kredit','','[Auto] Biaya konsumsi','2026-07-27 01:34:04'),
(689,'REF-1785116091-606','2026-07-20',55,35000.00,'Keluar','debit','','Biaya konsumsi','2026-07-27 01:34:51'),
(690,'REF-1785116091-606','2026-07-20',3,35000.00,'Keluar','kredit','','[Auto] Biaya konsumsi','2026-07-27 01:34:51'),
(691,'REF-1785116131-464','2026-07-21',55,30000.00,'Keluar','debit','','Biaya konsumsi','2026-07-27 01:35:31'),
(692,'REF-1785116131-464','2026-07-21',3,30000.00,'Keluar','kredit','','[Auto] Biaya konsumsi','2026-07-27 01:35:31'),
(693,'REF-1785116166-126','2026-07-21',55,35000.00,'Keluar','debit','','Biaya konsumsi','2026-07-27 01:36:06'),
(694,'REF-1785116166-126','2026-07-21',3,35000.00,'Keluar','kredit','','[Auto] Biaya konsumsi','2026-07-27 01:36:06'),
(695,'REF-1785116202-410','2026-07-21',55,20000.00,'Keluar','debit','','Biaya konsumsi','2026-07-27 01:36:42'),
(696,'REF-1785116202-410','2026-07-21',3,20000.00,'Keluar','kredit','','[Auto] Biaya konsumsi','2026-07-27 01:36:42'),
(697,'REF-1785116257-797','2026-07-22',55,68000.00,'Keluar','debit','','Biaya konsumsi','2026-07-27 01:37:37'),
(698,'REF-1785116257-797','2026-07-22',3,68000.00,'Keluar','kredit','','[Auto] Biaya konsumsi','2026-07-27 01:37:37'),
(699,'REF-1785116300-872','2026-07-22',69,100000.00,'Keluar','debit','','Biaya pencucian mobil','2026-07-27 01:38:20'),
(700,'REF-1785116300-872','2026-07-22',3,100000.00,'Keluar','kredit','','[Auto] Biaya pencucian mobil','2026-07-27 01:38:20'),
(701,'REF-1785116383-421','2026-07-22',55,100000.00,'Keluar','debit','','Biaya konsumsi','2026-07-27 01:39:43'),
(702,'REF-1785116383-421','2026-07-22',3,100000.00,'Keluar','kredit','','[Auto] Biaya konsumsi','2026-07-27 01:39:43'),
(703,'REF-1785116426-876','2026-07-23',55,60000.00,'Keluar','debit','','Biaya konsumsi','2026-07-27 01:40:26'),
(704,'REF-1785116426-876','2026-07-23',3,60000.00,'Keluar','kredit','','[Auto] Biaya konsumsi','2026-07-27 01:40:26'),
(705,'REF-1785116461-848','2026-07-23',55,80000.00,'Keluar','debit','','Biaya konsumsi','2026-07-27 01:41:01'),
(706,'REF-1785116461-848','2026-07-23',3,80000.00,'Keluar','kredit','','[Auto] Biaya konsumsi','2026-07-27 01:41:01'),
(707,'REF-1785116506-838','2026-07-23',55,28000.00,'Keluar','debit','','Biaya konsumsi','2026-07-27 01:41:46'),
(708,'REF-1785116506-838','2026-07-23',3,28000.00,'Keluar','kredit','','[Auto] Biaya konsumsi','2026-07-27 01:41:46'),
(709,'REF-1785116550-926','2026-07-23',55,60000.00,'Keluar','debit','','Biaya konsumsi','2026-07-27 01:42:30'),
(710,'REF-1785116550-926','2026-07-23',3,60000.00,'Keluar','kredit','','[Auto] Biaya konsumsi','2026-07-27 01:42:30'),
(711,'REF-1785116613-270','2026-07-23',55,30000.00,'Keluar','debit','','Biaya konsumsi','2026-07-27 01:43:33'),
(712,'REF-1785116613-270','2026-07-23',3,30000.00,'Keluar','kredit','','[Auto] Biaya konsumsi','2026-07-27 01:43:33'),
(713,'REF-1785116655-201','2026-07-24',42,300000.00,'Keluar','debit','','Pembelian BBM','2026-07-27 01:44:15'),
(714,'REF-1785116655-201','2026-07-24',1,300000.00,'Keluar','kredit','','[Auto] Pembelian BBM','2026-07-27 01:44:15'),
(715,'REF-1785288341-241','2026-07-27',50,150000.00,'Keluar','debit','','Pembelian materai','2026-07-29 01:25:41'),
(716,'REF-1785288341-241','2026-07-27',3,150000.00,'Keluar','kredit','','[Auto] Pembelian materai','2026-07-29 01:25:41'),
(717,'REF-1785288386-208','2026-07-27',42,155000.00,'Keluar','debit','','Pembelian BBM eceran','2026-07-29 01:26:26'),
(718,'REF-1785288386-208','2026-07-27',3,155000.00,'Keluar','kredit','','[Auto] Pembelian BBM eceran','2026-07-29 01:26:26'),
(721,'REF-1785302678-660','2026-07-27',38,2706762113.00,'Masuk','kredit','','Jasa Manpower IPIP','2026-07-29 05:24:38'),
(722,'REF-1785302678-660','2026-07-27',1,2706762113.00,'Masuk','debit','','[Auto] Jasa Manpower IPIP','2026-07-29 05:24:38'),
(723,'REF-1785302812-537','2026-07-27',37,230190360.00,'Masuk','kredit','','Jasa Manpower IPK','2026-07-29 05:26:52'),
(724,'REF-1785302812-537','2026-07-27',1,230190360.00,'Masuk','debit','','[Auto] Jasa Manpower IPK','2026-07-29 05:26:52'),
(725,'REF-1785302868-770','2026-07-28',37,4908924999.00,'Masuk','kredit','','Jasa Manpower HBB','2026-07-29 05:27:48'),
(726,'REF-1785302868-770','2026-07-28',1,4908924999.00,'Masuk','debit','','[Auto] Jasa Manpower HBB','2026-07-29 05:27:48'),
(727,'REF-1785546916-706','2026-07-28',76,18500000.00,'Keluar','debit','','Angsuran mobil mixer','2026-08-01 01:15:16'),
(728,'REF-1785546916-706','2026-07-28',3,18500000.00,'Keluar','kredit','','[Auto] Angsuran mobil mixer','2026-08-01 01:15:16'),
(729,'REF-1785547001-129','2026-07-30',28,24500000.00,'Keluar','debit','','Angsuran BRI','2026-08-01 01:16:41'),
(730,'REF-1785547001-129','2026-07-30',3,24500000.00,'Keluar','kredit','','[Auto] Angsuran BRI','2026-08-01 01:16:41'),
(731,'REF-1785547060-105','2026-07-29',42,300000.00,'Keluar','debit','','Pembelian BBM','2026-08-01 01:17:40'),
(732,'REF-1785547060-105','2026-07-29',3,300000.00,'Keluar','kredit','','[Auto] Pembelian BBM','2026-08-01 01:17:40'),
(733,'REF-1785548315-493','2026-08-01',42,1320027.00,'Keluar','debit','','Pembelian BBM','2026-08-01 01:38:35'),
(734,'REF-1785548315-493','2026-08-01',1,1320027.00,'Keluar','kredit','','[Auto] Pembelian BBM','2026-08-01 01:38:35'),
(742,'REF-1785725047-553','2026-08-01',55,86000.00,'Keluar','debit','','Pembelian Kopi','2026-08-03 02:44:07'),
(743,'REF-1785725047-553','2026-08-01',3,86000.00,'Keluar','kredit','','[Auto] Pembelian Kopi','2026-08-03 02:44:07'),
(744,'REF-1785725110-764','2026-08-02',69,70000.00,'Keluar','debit','','Biaya pencucian mobil operasional','2026-08-03 02:45:10'),
(745,'REF-1785725110-764','2026-08-02',3,70000.00,'Keluar','kredit','','[Auto] Biaya pencucian mobil operasional','2026-08-03 02:45:10'),
(746,'REF-1785733973-598','2026-07-01',42,1180000.00,'Keluar','debit','','Pembelian BBM TJS-Antam','2026-08-03 05:12:53'),
(747,'REF-1785733973-598','2026-07-01',1,1180000.00,'Keluar','kredit','','[Auto] Pembelian BBM TJS-Antam','2026-08-03 05:12:53'),
(748,'REF-1785734007-428','2026-07-02',42,704000.00,'Keluar','debit','','Pembelian BBM TJS-Antam','2026-08-03 05:13:27'),
(749,'REF-1785734007-428','2026-07-02',1,704000.00,'Keluar','kredit','','[Auto] Pembelian BBM TJS-Antam','2026-08-03 05:13:27'),
(750,'REF-1785734056-312','2026-07-03',42,204000.00,'Keluar','debit','','Pembelian BBM TJS-Antam','2026-08-03 05:14:16'),
(751,'REF-1785734056-312','2026-07-03',1,204000.00,'Keluar','kredit','','[Auto] Pembelian BBM TJS-Antam','2026-08-03 05:14:16'),
(752,'REF-1785734099-261','2026-07-06',42,1250000.00,'Keluar','debit','','Pembelian BBM TJS-Antam','2026-08-03 05:14:59'),
(753,'REF-1785734099-261','2026-07-06',1,1250000.00,'Keluar','kredit','','[Auto] Pembelian BBM TJS-Antam','2026-08-03 05:14:59'),
(754,'REF-1785734136-315','2026-07-07',42,704000.00,'Keluar','debit','','Pembelian BBM TJS-Antam','2026-08-03 05:15:36'),
(755,'REF-1785734136-315','2026-07-07',1,704000.00,'Keluar','kredit','','[Auto] Pembelian BBM TJS-Antam','2026-08-03 05:15:36'),
(756,'REF-1785734175-600','2026-07-08',42,304000.00,'Keluar','debit','','Pembelian BBM TJS-Antam','2026-08-03 05:16:15'),
(757,'REF-1785734175-600','2026-07-08',1,304000.00,'Keluar','kredit','','[Auto] Pembelian BBM TJS-Antam','2026-08-03 05:16:15'),
(758,'REF-1785734213-490','2026-07-09',42,204000.00,'Keluar','debit','','Pembelian BBM TJS-Antam','2026-08-03 05:16:53'),
(759,'REF-1785734213-490','2026-07-09',1,204000.00,'Keluar','kredit','','[Auto] Pembelian BBM TJS-Antam','2026-08-03 05:16:53'),
(760,'REF-1785734248-716','2026-07-10',42,804000.00,'Keluar','debit','','Pembelian BBM TJS-Antam','2026-08-03 05:17:28'),
(761,'REF-1785734248-716','2026-07-10',1,804000.00,'Keluar','kredit','','[Auto] Pembelian BBM TJS-Antam','2026-08-03 05:17:28'),
(762,'REF-1785734300-290','2026-07-11',42,500000.00,'Keluar','debit','','Pembelian BBM TJS-Antam','2026-08-03 05:18:20'),
(763,'REF-1785734300-290','2026-07-11',1,500000.00,'Keluar','kredit','','[Auto] Pembelian BBM TJS-Antam','2026-08-03 05:18:20'),
(764,'REF-1785734392-601','2026-07-13',42,804000.00,'Keluar','debit','','Pembelian BBM TJS-Antam','2026-08-03 05:19:52'),
(765,'REF-1785734392-601','2026-07-13',1,804000.00,'Keluar','kredit','','[Auto] Pembelian BBM TJS-Antam','2026-08-03 05:19:52'),
(766,'REF-1785734433-753','2026-07-14',42,1554000.00,'Keluar','debit','','Pembelian BBM TJS-Antam','2026-08-03 05:20:33'),
(767,'REF-1785734433-753','2026-07-14',1,1554000.00,'Keluar','kredit','','[Auto] Pembelian BBM TJS-Antam','2026-08-03 05:20:33'),
(768,'REF-1785734471-104','2026-07-15',42,204000.00,'Keluar','debit','','Pembelian BBM TJS-Antam','2026-08-03 05:21:11'),
(769,'REF-1785734471-104','2026-07-15',1,204000.00,'Keluar','kredit','','[Auto] Pembelian BBM TJS-Antam','2026-08-03 05:21:11'),
(770,'REF-1785734503-687','2026-07-16',42,804000.00,'Keluar','debit','','Pembelian BBM TJS-Antam','2026-08-03 05:21:43'),
(771,'REF-1785734503-687','2026-07-16',1,804000.00,'Keluar','kredit','','[Auto] Pembelian BBM TJS-Antam','2026-08-03 05:21:43'),
(772,'REF-1785734538-116','2026-07-17',42,204000.00,'Keluar','debit','','Pembelian BBM TJS-Antam','2026-08-03 05:22:18'),
(773,'REF-1785734538-116','2026-07-17',1,204000.00,'Keluar','kredit','','[Auto] Pembelian BBM TJS-Antam','2026-08-03 05:22:18'),
(774,'REF-1785734570-134','2026-07-18',42,500000.00,'Keluar','debit','','Pembelian BBM TJS-Antam','2026-08-03 05:22:50'),
(775,'REF-1785734570-134','2026-07-18',1,500000.00,'Keluar','kredit','','[Auto] Pembelian BBM TJS-Antam','2026-08-03 05:22:50'),
(776,'REF-1785734613-787','2026-07-19',42,300000.00,'Keluar','debit','','Pembelian BBM TJS-Antam','2026-08-03 05:23:33'),
(777,'REF-1785734613-787','2026-07-19',1,300000.00,'Keluar','kredit','','[Auto] Pembelian BBM TJS-Antam','2026-08-03 05:23:33'),
(778,'REF-1785734643-414','2026-07-20',42,704000.00,'Keluar','debit','','Pembelian BBM TJS-Antam','2026-08-03 05:24:03'),
(779,'REF-1785734643-414','2026-07-20',1,704000.00,'Keluar','kredit','','[Auto] Pembelian BBM TJS-Antam','2026-08-03 05:24:03'),
(780,'REF-1785734685-556','2026-07-21',42,654000.00,'Keluar','debit','','Pembelian BBM TJS-Antam','2026-08-03 05:24:45'),
(781,'REF-1785734685-556','2026-07-21',1,654000.00,'Keluar','kredit','','[Auto] Pembelian BBM TJS-Antam','2026-08-03 05:24:45'),
(782,'REF-1785734721-411','2026-07-22',42,204000.00,'Keluar','debit','','Pembelian BBM TJS-Antam','2026-08-03 05:25:21'),
(783,'REF-1785734721-411','2026-07-22',1,204000.00,'Keluar','kredit','','[Auto] Pembelian BBM TJS-Antam','2026-08-03 05:25:21'),
(784,'REF-1785734762-141','2026-07-23',42,304000.00,'Keluar','debit','','Pembelian BBM TJS-Antam','2026-08-03 05:26:02'),
(785,'REF-1785734762-141','2026-07-23',1,304000.00,'Keluar','kredit','','[Auto] Pembelian BBM TJS-Antam','2026-08-03 05:26:02'),
(786,'REF-1785734789-436','2026-07-24',42,704000.00,'Keluar','debit','','Pembelian BBM TJS-Antam','2026-08-03 05:26:29'),
(787,'REF-1785734789-436','2026-07-24',1,704000.00,'Keluar','kredit','','[Auto] Pembelian BBM TJS-Antam','2026-08-03 05:26:29'),
(788,'REF-1785734821-757','2026-07-25',42,500000.00,'Keluar','debit','','Pembelian BBM TJS-Antam','2026-08-03 05:27:01'),
(789,'REF-1785734821-757','2026-07-25',1,500000.00,'Keluar','kredit','','[Auto] Pembelian BBM TJS-Antam','2026-08-03 05:27:01'),
(790,'REF-1785734866-175','2026-07-27',42,704000.00,'Keluar','debit','','Pembelian BBM TJS-Antam','2026-08-03 05:27:46'),
(791,'REF-1785734866-175','2026-07-27',1,704000.00,'Keluar','kredit','','[Auto] Pembelian BBM TJS-Antam','2026-08-03 05:27:46'),
(792,'REF-1785734900-597','2026-07-28',42,604000.00,'Keluar','debit','','Pembelian BBM TJS-Antam','2026-08-03 05:28:20'),
(793,'REF-1785734900-597','2026-07-28',1,604000.00,'Keluar','kredit','','[Auto] Pembelian BBM TJS-Antam','2026-08-03 05:28:20'),
(794,'REF-1785734974-804','2026-07-29',42,204000.00,'Keluar','debit','','Pembelian BBM TJS-Antam','2026-08-03 05:29:34'),
(795,'REF-1785734974-804','2026-07-29',1,204000.00,'Keluar','kredit','','[Auto] Pembelian BBM TJS-Antam','2026-08-03 05:29:34'),
(796,'REF-1785735012-964','2026-07-30',42,804000.00,'Keluar','debit','','Pembelian BBM TJS-Antam','2026-08-03 05:30:12'),
(797,'REF-1785735012-964','2026-07-30',1,804000.00,'Keluar','kredit','','[Auto] Pembelian BBM TJS-Antam','2026-08-03 05:30:12'),
(798,'REF-1785735036-103','2026-07-31',42,304000.00,'Keluar','debit','','Pembelian BBM TJS-Antam','2026-08-03 05:30:36'),
(799,'REF-1785735036-103','2026-07-31',1,304000.00,'Keluar','kredit','','[Auto] Pembelian BBM TJS-Antam','2026-08-03 05:30:36'),
(800,'REF-1785806429-210','2026-07-01',69,12000.00,'Keluar','debit','','Pembelian sapu lidi','2026-08-04 01:20:29'),
(801,'REF-1785806429-210','2026-07-01',1,12000.00,'Keluar','kredit','','[Auto] Pembelian sapu lidi','2026-08-04 01:20:29'),
(802,'REF-1785806525-542','2026-07-02',68,50000.00,'Keluar','debit','','Pembelian lem dan pipa','2026-08-04 01:22:05'),
(803,'REF-1785806525-542','2026-07-02',1,50000.00,'Keluar','kredit','','[Auto] Pembelian lem dan pipa','2026-08-04 01:22:05'),
(804,'REF-1785806707-685','2026-07-04',63,820000.00,'Keluar','debit','','Alat Dan Biaya Montly Meeting','2026-08-04 01:25:07'),
(805,'REF-1785806707-685','2026-07-04',1,820000.00,'Keluar','kredit','','[Auto] Alat Dan Biaya Montly Meeting','2026-08-04 01:25:07'),
(806,'REF-1785806768-645','2026-07-05',44,25000.00,'Keluar','debit','','Pembelian masker','2026-08-04 01:26:08'),
(807,'REF-1785806768-645','2026-07-05',1,25000.00,'Keluar','kredit','','[Auto] Pembelian masker','2026-08-04 01:26:08'),
(808,'REF-1785806821-843','2026-07-07',55,620000.00,'Keluar','debit','',' Konsumsi 3x Acra Rapat & Karyawan Antam di Pabrik ','2026-08-04 01:27:01'),
(809,'REF-1785806821-843','2026-07-07',1,620000.00,'Keluar','kredit','','[Auto]  Konsumsi 3x Acra Rapat & Karyawan Antam di Pabrik ','2026-08-04 01:27:01'),
(810,'REF-1785806885-507','2026-07-07',42,120000.00,'Keluar','debit','','Pembelian BBM dan oli motor 3 roda','2026-08-04 01:28:05'),
(811,'REF-1785806885-507','2026-07-07',1,120000.00,'Keluar','kredit','','[Auto] Pembelian BBM dan oli motor 3 roda','2026-08-04 01:28:05'),
(812,'REF-1785806944-369','2026-07-08',61,225000.00,'Keluar','debit','','Pembelian sparepart mesin rumput','2026-08-04 01:29:04'),
(813,'REF-1785806944-369','2026-07-08',1,225000.00,'Keluar','kredit','','[Auto] Pembelian sparepart mesin rumput','2026-08-04 01:29:04'),
(814,'REF-1785806993-924','2026-07-10',61,281000.00,'Keluar','debit','','Pembelian sparepart mesin rumput','2026-08-04 01:29:53'),
(815,'REF-1785806993-924','2026-07-10',1,281000.00,'Keluar','kredit','','[Auto] Pembelian sparepart mesin rumput','2026-08-04 01:29:53'),
(816,'REF-1785807050-311','2026-07-13',61,580000.00,'Keluar','debit','','Biaya las motor 3 roda','2026-08-04 01:30:50'),
(817,'REF-1785807050-311','2026-07-13',1,580000.00,'Keluar','kredit','','[Auto] Biaya las motor 3 roda','2026-08-04 01:30:50'),
(818,'REF-1785807097-241','2026-07-15',42,200000.00,'Keluar','debit','','Pembelian solar truck','2026-08-04 01:31:37'),
(819,'REF-1785807097-241','2026-07-15',1,200000.00,'Keluar','kredit','','[Auto] Pembelian solar truck','2026-08-04 01:31:37'),
(820,'REF-1785807141-889','2026-07-16',69,100000.00,'Keluar','debit','','Kebutuhan alat Iccang','2026-08-04 01:32:21'),
(821,'REF-1785807141-889','2026-07-16',1,100000.00,'Keluar','kredit','','[Auto] Kebutuhan alat Iccang','2026-08-04 01:32:21'),
(822,'REF-1785807180-760','2026-07-17',55,100000.00,'Keluar','debit','','Biaya konsumsi rapat','2026-08-04 01:33:00'),
(823,'REF-1785807180-760','2026-07-17',1,100000.00,'Keluar','kredit','','[Auto] Biaya konsumsi rapat','2026-08-04 01:33:00'),
(824,'REF-1785807231-853','2026-07-19',61,320000.00,'Keluar','debit','','Pembelian sparepart mesin rumput','2026-08-04 01:33:51'),
(825,'REF-1785807231-853','2026-07-19',1,320000.00,'Keluar','kredit','','[Auto] Pembelian sparepart mesin rumput','2026-08-04 01:33:51'),
(826,'REF-1785807274-154','2026-07-20',55,50000.00,'Keluar','debit','','Konsumsi PLN','2026-08-04 01:34:34'),
(827,'REF-1785807274-154','2026-07-20',1,50000.00,'Keluar','kredit','','[Auto] Konsumsi PLN','2026-08-04 01:34:34'),
(828,'REF-1785807331-915','2026-07-21',42,500000.00,'Keluar','debit','','Pembelian solar mobil mixer','2026-08-04 01:35:31'),
(829,'REF-1785807331-915','2026-07-21',1,500000.00,'Keluar','kredit','','[Auto] Pembelian solar mobil mixer','2026-08-04 01:35:31'),
(830,'REF-1785807398-323','2026-07-26',61,550000.00,'Keluar','debit','','Pembelian sparepart mesin rumput','2026-08-04 01:36:38'),
(831,'REF-1785807398-323','2026-07-26',1,550000.00,'Keluar','kredit','','[Auto] Pembelian sparepart mesin rumput','2026-08-04 01:36:38'),
(832,'REF-1785807523-209','2026-07-27',42,900000.00,'Keluar','debit','','Pembelian solar truck','2026-08-04 01:38:43'),
(833,'REF-1785807523-209','2026-07-27',1,900000.00,'Keluar','kredit','','[Auto] Pembelian solar truck','2026-08-04 01:38:43'),
(834,'REF-1785807593-425','2026-07-28',61,680000.00,'Keluar','debit','','Pembelian Oli mesin rumput 1 dos','2026-08-04 01:39:53'),
(835,'REF-1785807593-425','2026-07-28',1,680000.00,'Keluar','kredit','','[Auto] Pembelian Oli mesin rumput 1 dos','2026-08-04 01:39:53'),
(836,'REF-1785807645-548','2026-07-30',61,100000.00,'Keluar','debit','','Pembelian oli motor 3 roda','2026-08-04 01:40:45'),
(837,'REF-1785807645-548','2026-07-30',1,100000.00,'Keluar','kredit','','[Auto] Pembelian oli motor 3 roda','2026-08-04 01:40:45'),
(838,'REF-1785812660-660','2026-07-22',44,50000.00,'Keluar','debit','','Pembelian helm merah safety','2026-08-04 03:04:20'),
(839,'REF-1785812660-660','2026-07-22',1,50000.00,'Keluar','kredit','','[Auto] Pembelian helm merah safety','2026-08-04 03:04:20'),
(840,'REF-1785812842-635','2026-07-24',42,500000.00,'Keluar','debit','','Pembelian solar mobil mixer ','2026-08-04 03:07:22'),
(841,'REF-1785812842-635','2026-07-24',1,500000.00,'Keluar','kredit','','[Auto] Pembelian solar mobil mixer ','2026-08-04 03:07:22'),
(842,'REF-1785822334-458','2026-08-04',55,239000.00,'Keluar','debit','','Biaya rapat konsumsi','2026-08-04 05:45:34'),
(843,'REF-1785822334-458','2026-08-04',3,239000.00,'Keluar','kredit','','[Auto] Biaya rapat konsumsi','2026-08-04 05:45:34'),
(844,'REF-1785822657-424','2026-08-04',50,506000.00,'Keluar','debit','','Pembelian ATK','2026-08-04 05:50:57'),
(845,'REF-1785822657-424','2026-08-04',3,506000.00,'Keluar','kredit','','[Auto] Pembelian ATK','2026-08-04 05:50:57'),
(846,'REF-1785824653-166','2026-08-04',42,300000.00,'Keluar','debit','','Pembelian BBM','2026-08-04 06:24:13'),
(847,'REF-1785824653-166','2026-08-04',3,300000.00,'Keluar','kredit','','[Auto] Pembelian BBM','2026-08-04 06:24:13'),
(848,'REF-1785824689-557','2026-08-04',55,128000.00,'Keluar','debit','','Biaya konsumsi','2026-08-04 06:24:49'),
(849,'REF-1785824689-557','2026-08-04',3,128000.00,'Keluar','kredit','','[Auto] Biaya konsumsi','2026-08-04 06:24:49'),
(850,'REF-1785889989-837','2026-08-01',55,202500.00,'Keluar','debit','','Biaya Konsumsi rapat','2026-08-05 00:33:09'),
(851,'REF-1785889989-837','2026-08-01',3,202500.00,'Keluar','kredit','','[Auto] Biaya Konsumsi rapat','2026-08-05 00:33:09'),
(852,'REF-1785890070-902','2026-08-01',57,301000.00,'Keluar','debit','','Biaya ongkos kirim','2026-08-05 00:34:30'),
(853,'REF-1785890070-902','2026-08-01',3,301000.00,'Keluar','kredit','','[Auto] Biaya ongkos kirim','2026-08-05 00:34:30'),
(854,'REF-1785890151-187','2026-08-04',61,302500.00,'Keluar','debit','','Biaya service laptop','2026-08-05 00:35:51'),
(855,'REF-1785890151-187','2026-08-04',3,302500.00,'Keluar','kredit','','[Auto] Biaya service laptop','2026-08-05 00:35:51'),
(856,'REF-1785908125-872','2026-08-05',59,439819297.00,'Keluar','debit','','Pajak Oktober 2025','2026-08-05 05:35:25'),
(857,'REF-1785908125-872','2026-08-05',1,439819297.00,'Keluar','kredit','','[Auto] Pembayaran masa pajak tgl 10-10-2025','2026-08-05 05:35:25'),
(858,'REF-1785908260-932','2026-08-05',6,11006500.00,'Keluar','debit','','DP 100 pcs baju dami konveksi','2026-08-05 05:37:40'),
(859,'REF-1785908260-932','2026-08-05',3,11006500.00,'Keluar','kredit','','[Auto] DP 100 pcs baju dami konveksi','2026-08-05 05:37:40'),
(860,'REF-1785908566-556','2026-08-05',21,23310000.00,'Keluar','debit','','Invoice Juni 2026 SKM','2026-08-05 05:42:46'),
(861,'REF-1785908566-556','2026-08-05',3,23310000.00,'Keluar','kredit','','[Auto] Invoice Juni 2026 SKM','2026-08-05 05:42:46'),
(862,'REF-1785908703-623','2026-08-05',59,439819297.00,'Keluar','debit','','Pajak November 2025','2026-08-05 05:45:03'),
(863,'REF-1785908703-623','2026-08-05',3,439819297.00,'Keluar','kredit','','[Auto] Pajak November 2025','2026-08-05 05:45:03'),
(864,'REF-1785908779-963','2026-08-05',3,152500.00,'Keluar','debit','','Rahmat Andri','2026-08-05 05:46:19'),
(865,'REF-1785908779-963','2026-08-05',3,152500.00,'Keluar','kredit','','[Auto] Rahmat Andri','2026-08-05 05:46:19'),
(866,'REF-1785908885-690','2026-08-04',61,250000.00,'Keluar','debit','','Biaya perbaikan laptop','2026-08-05 05:48:05'),
(867,'REF-1785908885-690','2026-08-04',3,250000.00,'Keluar','kredit','','[Auto] Biaya perbaikan laptop','2026-08-05 05:48:05'),
(868,'REF-1785908940-885','2026-08-04',55,52000.00,'Keluar','debit','','Biaya konsumsi\r\n','2026-08-05 05:49:00'),
(869,'REF-1785908940-885','2026-08-04',3,52000.00,'Keluar','kredit','','[Auto] Biaya konsumsi\r\n','2026-08-05 05:49:00'),
(870,'REF-1785908970-587','2026-08-04',55,60000.00,'Keluar','debit','','Biaya konsumsi','2026-08-05 05:49:30'),
(871,'REF-1785908970-587','2026-08-04',3,60000.00,'Keluar','kredit','','[Auto] Biaya konsumsi','2026-08-05 05:49:30'),
(872,'REF-1785909002-507','2026-08-04',55,60000.00,'Keluar','debit','','Biaya konsumsi','2026-08-05 05:50:02'),
(873,'REF-1785909002-507','2026-08-04',3,60000.00,'Keluar','kredit','','[Auto] Biaya konsumsi','2026-08-05 05:50:02'),
(874,'REF-1785909033-270','2026-08-05',42,400000.00,'Keluar','debit','','Pembelian BBM','2026-08-05 05:50:33'),
(875,'REF-1785909033-270','2026-08-05',3,400000.00,'Keluar','kredit','','[Auto] Pembelian BBM','2026-08-05 05:50:33'),
(876,'REF-1785909084-427','2026-08-05',42,150000.00,'Keluar','debit','','Pembelian BBM','2026-08-05 05:51:24'),
(877,'REF-1785909084-427','2026-08-05',3,150000.00,'Keluar','kredit','','[Auto] Pembelian BBM','2026-08-05 05:51:24'),
(878,'REF-1785909109-552','2026-08-05',42,150000.00,'Keluar','debit','','Pembelian BBM','2026-08-05 05:51:49'),
(879,'REF-1785909109-552','2026-08-05',3,150000.00,'Keluar','kredit','','[Auto] Pembelian BBM','2026-08-05 05:51:49'),
(880,'REF-1785978504-751','2026-08-05',55,152500.00,'Keluar','debit','','Konsumsi perjalanan dinas Andri','2026-08-06 01:08:24'),
(881,'REF-1785978504-751','2026-08-05',3,152500.00,'Keluar','kredit','','[Auto] Konsumsi perjalanan dinas Andri','2026-08-06 01:08:24'),
(882,'REF-1786001624-955','2026-08-06',66,279044579.00,'Keluar','debit','','Pembayaran BPJS tenaga kerja','2026-08-06 07:33:44'),
(883,'REF-1786001624-955','2026-08-06',3,279044579.00,'Keluar','kredit','','[Auto] Pembayaran BPJS ','2026-08-06 07:33:44'),
(884,'REF-1786001953-525','2026-08-06',66,7038990.00,'Keluar','debit','','Pembayaran BPJS tenaga kerja','2026-08-06 07:39:13'),
(885,'REF-1786001953-525','2026-08-06',3,7038990.00,'Keluar','kredit','','[Auto] Pembayaran BPJS tenaga kerja','2026-08-06 07:39:13'),
(886,'REF-1786002040-239','2026-08-06',66,35974876.00,'Keluar','debit','','Pembayaran BPJS kesehatan','2026-08-06 07:40:40'),
(887,'REF-1786002040-239','2026-08-06',3,35974876.00,'Keluar','kredit','','[Auto] Pembayaran BPJS kesehatan','2026-08-06 07:40:40'),
(888,'REF-1786322656-424','2026-08-07',69,582000.00,'Keluar','debit','','Biaya stiker mobil','2026-08-10 00:44:16'),
(889,'REF-1786322656-424','2026-08-07',3,582000.00,'Keluar','kredit','','[Auto] Biaya stiker mobil','2026-08-10 00:44:16'),
(890,'REF-1786322722-802','2026-08-07',42,400000.00,'Keluar','debit','','Pembelian BBM Pertamax','2026-08-10 00:45:22'),
(891,'REF-1786322722-802','2026-08-07',1,400000.00,'Keluar','kredit','','[Auto] Pembelian BBM Pertamax','2026-08-10 00:45:22'),
(892,'REF-1786322775-327','2026-08-07',55,67000.00,'Keluar','debit','','Pembelian kopi direktur','2026-08-10 00:46:15'),
(893,'REF-1786322775-327','2026-08-07',3,67000.00,'Keluar','kredit','','[Auto] Pembelian kopi direktur','2026-08-10 00:46:15'),
(894,'REF-1786322948-405','2026-08-08',69,36000.00,'Keluar','debit','','Pembelian Infinity kolaka','2026-08-10 00:49:08'),
(895,'REF-1786322948-405','2026-08-08',3,36000.00,'Keluar','kredit','','[Auto] Pembelian Infinity kolaka','2026-08-10 00:49:08'),
(896,'REF-1786323001-580','2026-08-09',42,400000.00,'Keluar','debit','','Pembelian BBM Pertamax','2026-08-10 00:50:01'),
(897,'REF-1786323001-580','2026-08-09',1,400000.00,'Keluar','kredit','','[Auto] Pembelian BBM Pertamax','2026-08-10 00:50:01'),
(898,'REF-1786410539-957','2026-08-10',55,100000.00,'Keluar','debit','','Konsumsi rapat','2026-08-11 01:08:59'),
(899,'REF-1786410539-957','2026-08-10',3,100000.00,'Keluar','kredit','','[Auto] Konsumsi rapat','2026-08-11 01:08:59'),
(900,'REF-1786598194-902','2026-08-11',42,350000.00,'Keluar','debit','','Pembelian BBM Pertamax','2026-08-13 05:16:34'),
(901,'REF-1786598194-902','2026-08-11',3,350000.00,'Keluar','kredit','','[Auto] ','2026-08-13 05:16:34'),
(902,'REF-1786598309-362','2026-08-12',69,70000.00,'Keluar','debit','','Biaya pencucian mobil','2026-08-13 05:18:29'),
(903,'REF-1786598309-362','2026-08-12',3,70000.00,'Keluar','kredit','','[Auto] Biaya pencucian mobil','2026-08-13 05:18:29'),
(904,'REF-1786672437-137','2026-08-13',42,700000.00,'Keluar','debit','','Pembelian BBM Dexlite','2026-08-14 01:53:57'),
(905,'REF-1786672437-137','2026-08-13',3,700000.00,'Keluar','kredit','','[Auto] Pembelian BBM Dexlite','2026-08-14 01:53:57'),
(906,'REF-1786672501-201','2026-08-13',55,120000.00,'Keluar','debit','','Biaya konsumsi perjalanan','2026-08-14 01:55:01'),
(907,'REF-1786672501-201','2026-08-13',3,120000.00,'Keluar','kredit','','[Auto] Biaya konsumsi perjalanan','2026-08-14 01:55:01'),
(908,'REF-1786672539-100','2026-08-13',55,80000.00,'Keluar','debit','','Pembelian kopi saat perjalanan rapat','2026-08-14 01:55:39'),
(909,'REF-1786672539-100','2026-08-13',3,80000.00,'Keluar','kredit','','[Auto] Pembelian kopi saat perjalanan rapat','2026-08-14 01:55:39'),
(910,'REF-1786687084-994','2026-08-14',42,300000.00,'Keluar','debit','','Pembelian BBM','2026-08-14 05:58:04'),
(911,'REF-1786687084-994','2026-08-14',1,300000.00,'Keluar','kredit','','[Auto] Pembelian BBM','2026-08-14 05:58:04'),
(912,'REF-1787098719-806','2026-08-18',69,650000.00,'Keluar','debit','','Biaya karangan bunga untuk karyawan yang meninggal','2026-08-19 00:18:39'),
(913,'REF-1787098719-806','2026-08-18',3,650000.00,'Keluar','kredit','','[Auto] Biaya karangan bunga untuk karyawan yang meninggal','2026-08-19 00:18:39'),
(914,'REF-1787098776-870','2026-08-18',42,1000000.00,'Keluar','debit','','Pembelian BBM dexlite','2026-08-19 00:19:36'),
(915,'REF-1787098776-870','2026-08-18',1,1000000.00,'Keluar','kredit','','[Auto] Pembelian BBM dexlite','2026-08-19 00:19:36'),
(916,'REF-1787099057-791','2026-08-18',55,245000.00,'Keluar','debit','','Konsumsi perjalanan','2026-08-19 00:24:17'),
(917,'REF-1787099057-791','2026-08-18',3,245000.00,'Keluar','kredit','','[Auto] Konsumsi perjalanan','2026-08-19 00:24:17'),
(918,'REF-1787190857-810','2026-08-19',76,7786500.00,'Keluar','debit','','Angsuran mobil box','2026-08-20 01:54:17'),
(919,'REF-1787190857-810','2026-08-19',3,7786500.00,'Keluar','kredit','','[Auto] Angsuran mobil box','2026-08-20 01:54:17'),
(920,'REF-1787190941-209','2026-08-19',56,12433953.00,'Keluar','debit','','Pembayaran listrik hotel','2026-08-20 01:55:41'),
(921,'REF-1787190941-209','2026-08-19',3,12433953.00,'Keluar','kredit','','[Auto] Pembayaran listrik hotel','2026-08-20 01:55:41'),
(922,'REF-1787190976-925','2026-08-19',76,7786500.00,'Keluar','debit','','Angsuran mobil box','2026-08-20 01:56:16'),
(923,'REF-1787190976-925','2026-08-19',3,7786500.00,'Keluar','kredit','','[Auto] Angsuran mobil box','2026-08-20 01:56:16'),
(924,'REF-1787191039-251','2026-08-19',76,6686500.00,'Keluar','debit','','Angsuran mobil operasional Rush 1','2026-08-20 01:57:19'),
(925,'REF-1787191039-251','2026-08-19',3,6686500.00,'Keluar','kredit','','[Auto] Angsuran mobil operasional Rush 1','2026-08-20 01:57:19'),
(926,'REF-1787191144-141','2026-08-19',6,34306500.00,'Keluar','debit','','DP baju APD 320pcs','2026-08-20 01:59:04'),
(927,'REF-1787191144-141','2026-08-19',3,34306500.00,'Keluar','kredit','','[Auto] DP baju APD 320pcs','2026-08-20 01:59:04'),
(928,'REF-1787198087-847','2026-08-20',50,100000.00,'Keluar','debit','','Pembelian tinta epson','2026-08-20 03:54:47'),
(929,'REF-1787198087-847','2026-08-20',1,100000.00,'Keluar','kredit','','[Auto] Pembelian tinta epson','2026-08-20 03:54:47'),
(930,'REF-1787206497-108','2026-08-20',42,1000000.00,'Keluar','debit','','','2026-08-20 06:14:57'),
(931,'REF-1787206497-108','2026-08-20',3,1000000.00,'Keluar','kredit','','[Auto] ','2026-08-20 06:14:57'),
(936,'REF-1787279498-277','2026-08-20',60,100000.00,'Keluar','debit','','Biaya pelatihan pajak','2026-08-21 02:31:38'),
(937,'REF-1787279498-277','2026-08-20',3,100000.00,'Keluar','kredit','','[Auto] Biaya pelatihan pajak','2026-08-21 02:31:38'),
(938,'REF-1787279522-560','2026-08-21',60,100000.00,'Keluar','debit','','Biaya pelatihan pajak','2026-08-21 02:32:02'),
(939,'REF-1787279522-560','2026-08-21',3,100000.00,'Keluar','kredit','','[Auto] Biaya pelatihan pajak','2026-08-21 02:32:02'),
(940,'REF-1787281350-249','2026-08-21',42,150000.00,'Keluar','debit','','Pembelian BBM','2026-08-21 03:02:30'),
(941,'REF-1787281350-249','2026-08-21',3,150000.00,'Keluar','kredit','','[Auto] ','2026-08-21 03:02:30'),
(942,'REF-1787281495-569','2026-08-21',45,11006500.00,'Keluar','debit','','Pelunasan 100pcs APD','2026-08-21 03:04:55'),
(943,'REF-1787281495-569','2026-08-21',3,11006500.00,'Keluar','kredit','','[Auto] Pelunasan 100pcs APD','2026-08-21 03:04:55'),
(944,'REF-1787281543-547','2026-08-21',42,400000.00,'Keluar','debit','','Pembelian BBM Pertamax','2026-08-21 03:05:44'),
(945,'REF-1787281543-547','2026-08-21',3,400000.00,'Keluar','kredit','','[Auto] Pembelian BBM Pertamax','2026-08-21 03:05:44'),
(948,'REF-1787711921-254','2026-08-21',42,350000.00,'Keluar','debit','','Pembelian BBM','2026-08-26 02:38:41'),
(949,'REF-1787711921-254','2026-08-21',1,350000.00,'Keluar','kredit','','[Auto] Pembelian BBM','2026-08-26 02:38:41'),
(950,'REF-1787711999-116','2026-08-21',44,11006500.00,'Keluar','debit','','Pelunasan APD 100 pcs','2026-08-26 02:39:59'),
(951,'REF-1787711999-116','2026-08-21',3,11006500.00,'Keluar','kredit','','[Auto] Pelunasan APD 100 pcs','2026-08-26 02:39:59'),
(952,'REF-1787712068-820','2026-08-21',42,400000.00,'Keluar','debit','','Pembelian BBM Pertamax','2026-08-26 02:41:08'),
(953,'REF-1787712068-820','2026-08-21',3,400000.00,'Keluar','kredit','','[Auto] Pembelian BBM Pertamax','2026-08-26 02:41:08'),
(954,'REF-1787712125-530','2026-08-22',56,319150.00,'Keluar','debit','','Biaya wifi kantor','2026-08-26 02:42:05'),
(955,'REF-1787712125-530','2026-08-22',3,319150.00,'Keluar','kredit','','[Auto] Biaya wifi kantor','2026-08-26 02:42:05'),
(956,'REF-1787712242-724','2026-08-22',56,485650.00,'Keluar','debit','','Biaya wifi hotel','2026-08-26 02:44:02'),
(957,'REF-1787712242-724','2026-08-22',3,485650.00,'Keluar','kredit','','[Auto] Biaya wifi hotel','2026-08-26 02:44:02'),
(958,'REF-1787712301-119','2026-08-23',69,70000.00,'Keluar','debit','','Biaya pencucian mobil','2026-08-26 02:45:01'),
(959,'REF-1787712301-119','2026-08-23',3,70000.00,'Keluar','kredit','','[Auto] Biaya pencucian mobil','2026-08-26 02:45:01'),
(960,'REF-1787712357-808','2026-08-24',42,150000.00,'Keluar','debit','','Pembelian BBM','2026-08-26 02:45:57'),
(961,'REF-1787712357-808','2026-08-24',3,150000.00,'Keluar','kredit','','[Auto] Pembelian BBM','2026-08-26 02:45:57'),
(962,'REF-1787712411-400','2026-08-24',42,1300078.00,'Keluar','debit','','Pembelian BBM Dexlite','2026-08-26 02:46:51'),
(963,'REF-1787712411-400','2026-08-24',3,1300078.00,'Keluar','kredit','','[Auto] Pembelian BBM Dexlite','2026-08-26 02:46:51'),
(964,'REF-1787712471-931','2026-08-24',55,86000.00,'Keluar','debit','','Pembelian kopi','2026-08-26 02:47:51'),
(965,'REF-1787712471-931','2026-08-24',3,86000.00,'Keluar','kredit','','[Auto] Pembelian kopi','2026-08-26 02:47:51'),
(966,'REF-1787712530-947','2026-08-24',67,244000.00,'Keluar','debit','','Biaya air untuk pemain bola','2026-08-26 02:48:50'),
(967,'REF-1787712530-947','2026-08-24',3,244000.00,'Keluar','kredit','','[Auto] Biaya air untuk pemain bola','2026-08-26 02:48:50'),
(968,'REF-1787712589-650','2026-08-25',55,40000.00,'Keluar','debit','','Air minum rapat','2026-08-26 02:49:49'),
(969,'REF-1787712589-650','2026-08-25',3,40000.00,'Keluar','kredit','','[Auto] Air minum rapat','2026-08-26 02:49:49'),
(970,'REF-1787807495-889','2026-08-26',76,18500000.00,'Keluar','debit','','Angsuran mixer','2026-08-27 05:11:35'),
(971,'REF-1787807495-889','2026-08-26',3,18500000.00,'Keluar','kredit','','[Auto] Angsuran mixer','2026-08-27 05:11:35'),
(972,'REF-1787807570-122','0026-08-24',76,23376000.00,'Keluar','debit','','Angsuran fortuner','2026-08-27 05:12:50'),
(973,'REF-1787807570-122','0026-08-24',1,23376000.00,'Keluar','kredit','','[Auto] Angsuran fortuner','2026-08-27 05:12:50'),
(974,'REF-1787807818-977','2026-08-26',55,112000.00,'Keluar','debit','','pembelian kopi dengan orang ipip','2026-08-27 05:16:58'),
(975,'REF-1787807818-977','2026-08-26',3,112000.00,'Keluar','kredit','','[Auto] pembelian kopi dengan orang ipip','2026-08-27 05:16:58'),
(976,'REF-1787807883-745','2026-08-26',55,150000.00,'Keluar','debit','','Konsumsi ','2026-08-27 05:18:03'),
(977,'REF-1787807883-745','2026-08-26',3,150000.00,'Keluar','kredit','','[Auto] Konsumsi ','2026-08-27 05:18:03'),
(978,'REF-1787808002-296','2026-08-26',55,141000.00,'Keluar','debit','','Pembelian kopi','2026-08-27 05:20:02'),
(979,'REF-1787808002-296','2026-08-26',1,141000.00,'Keluar','kredit','','[Auto] Pembelian kopi','2026-08-27 05:20:02'),
(980,'REF-1788135585-836','2026-08-29',56,7102500.00,'Keluar','debit','','Penambahan daya listrik kantor ','2026-08-31 00:19:45'),
(981,'REF-1788135585-836','2026-08-29',3,7102500.00,'Keluar','kredit','','[Auto] Penambahan daya listrik kantor ','2026-08-31 00:19:45'),
(982,'REF-1788135671-703','2026-06-29',67,1000000.00,'Keluar','debit','','Support kegiatan ormas TNM','2026-08-31 00:21:11'),
(983,'REF-1788135671-703','2026-06-29',3,1000000.00,'Keluar','kredit','','[Auto] Support kegiatan ormas TNM','2026-08-31 00:21:11'),
(984,'REF-1788135973-777','2026-07-31',67,2500000.00,'Keluar','debit','','Support kegiatan ormas TNM','2026-08-31 00:26:13'),
(985,'REF-1788135973-777','2026-07-31',3,2500000.00,'Keluar','kredit','','[Auto] Support kegiatan ormas TNM','2026-08-31 00:26:13'),
(986,'REF-1788136027-507','2026-08-11',67,1000000.00,'Keluar','debit','','Support kegiatan ormas TNM','2026-08-31 00:27:07'),
(987,'REF-1788136027-507','2026-08-11',3,1000000.00,'Keluar','kredit','','[Auto] Support kegiatan ormas TNM','2026-08-31 00:27:07'),
(988,'REF-1788136162-465','2026-08-27',55,104500.00,'Keluar','debit','','Biaya konsumsi perjalanan','2026-08-31 00:29:22'),
(989,'REF-1788136162-465','2026-08-27',3,104500.00,'Keluar','kredit','','[Auto] Biaya konsumsi perjalanan','2026-08-31 00:29:22'),
(990,'REF-1788136226-156','2026-08-27',61,700000.00,'Keluar','debit','','Service mainboard lenovo yoga L13','2026-08-31 00:30:26'),
(991,'REF-1788136226-156','2026-08-27',3,700000.00,'Keluar','kredit','','[Auto] Service mainboard lenovo yoga L13','2026-08-31 00:30:26'),
(992,'REF-1788136294-284','2026-08-09',67,2500000.00,'Keluar','debit','','Support kegiatan Desa Towua','2026-08-31 00:31:34'),
(993,'REF-1788136294-284','2026-08-09',3,2500000.00,'Keluar','kredit','','[Auto] Support kegiatan Desa Towua','2026-08-31 00:31:34'),
(994,'REF-1788136558-456','2026-08-29',6,10027500.00,'Keluar','debit','','DP sepatu safety','2026-08-31 00:35:58'),
(995,'REF-1788136558-456','2026-08-29',3,10027500.00,'Keluar','kredit','','[Auto] DP sepatu safety','2026-08-31 00:35:58'),
(996,'REF-1788136610-304','2026-08-30',42,1300000.00,'Keluar','debit','','Pembelian BBM dexlite','2026-08-31 00:36:50'),
(997,'REF-1788136610-304','2026-08-30',1,1300000.00,'Keluar','kredit','','[Auto] Pembelian BBM dexlite','2026-08-31 00:36:50'),
(998,'REF-1788136737-307','2026-08-30',44,17006500.00,'Keluar','debit','','Pelunasan baju 320 pcs termin 1','2026-08-31 00:38:57'),
(999,'REF-1788136737-307','2026-08-30',3,17006500.00,'Keluar','kredit','','[Auto] Pelunasan baju 320 pcs termin 1','2026-08-31 00:38:57'),
(1000,'REF-1788145025-950','2026-08-29',42,400000.00,'Keluar','debit','','Pembelian BBM Pertalite','2026-08-31 02:57:05'),
(1001,'REF-1788145025-950','2026-08-29',1,400000.00,'Keluar','kredit','','[Auto] Pembelian BBM Pertalite','2026-08-31 02:57:05'),
(1002,'REF-1788145084-750','2026-08-30',69,70000.00,'Keluar','debit','','Biaya pencucian mobil','2026-08-31 02:58:04'),
(1003,'REF-1788145084-750','2026-08-30',3,70000.00,'Keluar','kredit','','[Auto] Biaya pencucian mobil','2026-08-31 02:58:04'),
(1004,'REF-1788148246-980','2026-08-16',67,1002500.00,'Keluar','debit','','Sponsor proposal','2026-08-31 03:50:46'),
(1005,'REF-1788148246-980','2026-08-16',3,1002500.00,'Keluar','kredit','','[Auto] Sponsor proposal','2026-08-31 03:50:46'),
(1006,'REF-1788158069-199','2026-08-31',42,400000.00,'Keluar','debit','','Pembelian Bensin','2026-08-31 06:34:29'),
(1007,'REF-1788158069-199','2026-08-31',1,400000.00,'Keluar','kredit','','[Auto] Pembelian Bensin','2026-08-31 06:34:29'),
(1008,'REF-1788233117-353','2026-08-03',42,795000.00,'Keluar','debit','','Pembelian BBM TJS-Antam','2026-09-01 03:25:17'),
(1009,'REF-1788233117-353','2026-08-03',1,795000.00,'Keluar','kredit','','[Auto] Pembelian BBM TJS-Antam','2026-09-01 03:25:17'),
(1010,'REF-1788233151-572','2026-08-04',42,195000.00,'Keluar','debit','','Pembelian BBM TJS-Antam','2026-09-01 03:25:51'),
(1011,'REF-1788233151-572','2026-08-04',1,195000.00,'Keluar','kredit','','[Auto] Pembelian BBM TJS-Antam','2026-09-01 03:25:51'),
(1012,'REF-1788233184-253','2026-08-05',42,495000.00,'Keluar','debit','','Pembelian BBM TJS-Antam','2026-09-01 03:26:24'),
(1013,'REF-1788233184-253','2026-08-05',1,495000.00,'Keluar','kredit','','[Auto] Pembelian BBM TJS-Antam','2026-09-01 03:26:24'),
(1014,'REF-1788233232-891','2026-08-06',42,295000.00,'Keluar','debit','','Pembelian BBM TJS-Antam','2026-09-01 03:27:12'),
(1015,'REF-1788233232-891','2026-08-06',1,295000.00,'Keluar','kredit','','[Auto] Pembelian BBM TJS-Antam','2026-09-01 03:27:12'),
(1016,'REF-1788233267-948','2026-08-07',42,195000.00,'Keluar','debit','','Pembelian BBM TJS-Antam','2026-09-01 03:27:47'),
(1017,'REF-1788233267-948','2026-08-07',1,195000.00,'Keluar','kredit','','[Auto] Pembelian BBM TJS-Antam','2026-09-01 03:27:47'),
(1018,'REF-1788233305-579','2026-08-09',42,500000.00,'Keluar','debit','','Pembelian BBM TJS-Antam','2026-09-01 03:28:25'),
(1019,'REF-1788233305-579','2026-08-09',1,500000.00,'Keluar','kredit','','[Auto] Pembelian BBM TJS-Antam','2026-09-01 03:28:25'),
(1020,'REF-1788233338-923','2026-08-10',42,295000.00,'Keluar','debit','','Pembelian BBM TJS-Antam','2026-09-01 03:28:58'),
(1021,'REF-1788233338-923','2026-08-10',1,295000.00,'Keluar','kredit','','[Auto] Pembelian BBM TJS-Antam','2026-09-01 03:28:58'),
(1022,'REF-1788233370-305','2026-08-11',42,495000.00,'Keluar','debit','','Pembelian BBM TJS-Antam','2026-09-01 03:29:30'),
(1023,'REF-1788233370-305','2026-08-11',1,495000.00,'Keluar','kredit','','[Auto] Pembelian BBM TJS-Antam','2026-09-01 03:29:30'),
(1024,'REF-1788233406-486','2026-08-12',42,395000.00,'Keluar','debit','','Pembelian BBM TJS-Antam','2026-09-01 03:30:06'),
(1025,'REF-1788233406-486','2026-08-12',1,395000.00,'Keluar','kredit','','[Auto] Pembelian BBM TJS-Antam','2026-09-01 03:30:06'),
(1026,'REF-1788233447-558','2026-08-13',42,1595000.00,'Keluar','debit','','Pembelian BBM TJS-Antam','2026-09-01 03:30:47'),
(1027,'REF-1788233447-558','2026-08-13',1,1595000.00,'Keluar','kredit','','[Auto] Pembelian BBM TJS-Antam','2026-09-01 03:30:47'),
(1028,'REF-1788233478-964','2026-08-14',42,195000.00,'Keluar','debit','','Pembelian BBM TJS-Antam','2026-09-01 03:31:18'),
(1029,'REF-1788233478-964','2026-08-14',1,195000.00,'Keluar','kredit','','[Auto] Pembelian BBM TJS-Antam','2026-09-01 03:31:18'),
(1030,'REF-1788233521-663','2026-08-17',42,1000000.00,'Keluar','debit','','Pembelian BBM TJS-Antam','2026-09-01 03:32:01'),
(1031,'REF-1788233521-663','2026-08-17',1,1000000.00,'Keluar','kredit','','[Auto] Pembelian BBM TJS-Antam','2026-09-01 03:32:01'),
(1032,'REF-1788233594-685','2026-08-18',42,595000.00,'Keluar','debit','','Pembelian BBM TJS-Antam','2026-09-01 03:33:14'),
(1033,'REF-1788233594-685','2026-08-18',1,595000.00,'Keluar','kredit','','[Auto] Pembelian BBM TJS-Antam','2026-09-01 03:33:14'),
(1034,'REF-1788233631-594','2026-08-19',42,195000.00,'Keluar','debit','','Pembelian BBM TJS-Antam','2026-09-01 03:33:51'),
(1035,'REF-1788233631-594','2026-08-19',1,195000.00,'Keluar','kredit','','[Auto] Pembelian BBM TJS-Antam','2026-09-01 03:33:51'),
(1036,'REF-1788233658-108','2026-08-20',42,595000.00,'Keluar','debit','','Pembelian BBM TJS-Antam','2026-09-01 03:34:18'),
(1037,'REF-1788233658-108','2026-08-20',1,595000.00,'Keluar','kredit','','[Auto] Pembelian BBM TJS-Antam','2026-09-01 03:34:18'),
(1038,'REF-1788233688-220','2026-08-21',42,1295000.00,'Keluar','debit','','Pembelian BBM TJS-Antam','2026-09-01 03:34:48'),
(1039,'REF-1788233688-220','2026-08-21',1,1295000.00,'Keluar','kredit','','[Auto] Pembelian BBM TJS-Antam','2026-09-01 03:34:48'),
(1040,'REF-1788233716-822','2026-08-24',42,295000.00,'Keluar','debit','','Pembelian BBM TJS-Antam','2026-09-01 03:35:16'),
(1041,'REF-1788233716-822','2026-08-24',1,295000.00,'Keluar','kredit','','[Auto] Pembelian BBM TJS-Antam','2026-09-01 03:35:16'),
(1042,'REF-1788233750-188','2026-08-25',42,1000000.00,'Keluar','debit','','Pembelian BBM TJS-Antam','2026-09-01 03:35:50'),
(1043,'REF-1788233750-188','2026-08-25',1,1000000.00,'Keluar','kredit','','[Auto] Pembelian BBM TJS-Antam','2026-09-01 03:35:50'),
(1044,'REF-1788233785-133','2026-08-26',42,295000.00,'Keluar','debit','','Pembelian BBM TJS-Antam','2026-09-01 03:36:25'),
(1045,'REF-1788233785-133','2026-08-26',1,295000.00,'Keluar','kredit','','[Auto] Pembelian BBM TJS-Antam','2026-09-01 03:36:25'),
(1046,'REF-1788233834-428','2026-08-27',42,495000.00,'Keluar','debit','','Pembelian BBM TJS-Antam','2026-09-01 03:37:14'),
(1047,'REF-1788233834-428','2026-08-27',1,495000.00,'Keluar','kredit','','[Auto] Pembelian BBM TJS-Antam','2026-09-01 03:37:14'),
(1048,'REF-1788233874-994','2026-08-28',42,295000.00,'Keluar','debit','','Pembelian BBM TJS-Antam','2026-09-01 03:37:54'),
(1049,'REF-1788233874-994','2026-08-28',1,295000.00,'Keluar','kredit','','[Auto] Pembelian BBM TJS-Antam','2026-09-01 03:37:54'),
(1050,'REF-1788233918-379','2026-08-31',42,495000.00,'Keluar','debit','','Pembelian BBM TJS-Antam','2026-09-01 03:38:38'),
(1051,'REF-1788233918-379','2026-08-31',1,495000.00,'Keluar','kredit','','[Auto] Pembelian BBM TJS-Antam','2026-09-01 03:38:38'),
(1058,'REF-1788313748-147','2026-08-26',37,4399299611.00,'Masuk','kredit','','Jasa Manpower HBB','2026-09-02 01:49:08'),
(1059,'REF-1788313748-147','2026-08-26',1,4399299611.00,'Masuk','debit','','[Auto] Jasa Manpower HBB','2026-09-02 01:49:08'),
(1060,'REF-1788313805-468','2026-08-26',39,237606388.00,'Masuk','kredit','','Jasa Manpower IPK Port','2026-09-02 01:50:05'),
(1061,'REF-1788313805-468','2026-08-26',1,237606388.00,'Masuk','debit','','[Auto] Jasa Manpower IPK Port','2026-09-02 01:50:05'),
(1062,'REF-1788313871-279','2026-08-26',38,2756999576.00,'Masuk','kredit','','Jasa Manpower IPIP','2026-09-02 01:51:11'),
(1063,'REF-1788313871-279','2026-08-26',1,2756999576.00,'Masuk','debit','','[Auto] Jasa Manpower IPIP','2026-09-02 01:51:11'),
(1069,'REF-1788485134-386','2026-09-02',68,755000.00,'Keluar','debit','','Pembelian lem fox 10kg','2026-09-04 01:25:34'),
(1070,'REF-1788485134-386','2026-09-02',3,755000.00,'Keluar','kredit','','[Auto] Pembelian lem fox 10kg','2026-09-04 01:25:34'),
(1071,'REF-1788485216-901','2026-09-03',47,5565549.00,'Keluar','debit','','Beban gaji Sulvika Juli-Agustus','2026-09-04 01:26:56'),
(1072,'REF-1788485216-901','2026-09-03',3,5565549.00,'Keluar','kredit','','[Auto] Beban gaji Sulvika Juli-Agustus','2026-09-04 01:26:56'),
(1073,'REF-1788485269-382','2026-09-03',47,2573876.00,'Keluar','debit','','Beban gaji Juli-Agustus Marsel Rupang','2026-09-04 01:27:49'),
(1074,'REF-1788485269-382','2026-09-03',3,2573876.00,'Keluar','kredit','','[Auto] Beban gaji Juli-Agustus Marsel Rupang','2026-09-04 01:27:49'),
(1075,'REF-1788485322-708','2026-09-03',47,24164911.00,'Keluar','debit','','Taufan','2026-09-04 01:28:42'),
(1076,'REF-1788485322-708','2026-09-03',3,24164911.00,'Keluar','kredit','','[Auto] Taufan','2026-09-04 01:28:42'),
(1077,'REF-1788485407-210','2026-09-03',47,4880000.00,'Keluar','debit','','Nur Ijah','2026-09-04 01:30:07'),
(1078,'REF-1788485407-210','2026-09-03',3,4880000.00,'Keluar','kredit','','[Auto] Nur Ijah','2026-09-04 01:30:07'),
(1079,'REF-1788485458-358','2026-09-03',56,503000.00,'Keluar','debit','','Pembbelian token listrik kantor','2026-09-04 01:30:58'),
(1080,'REF-1788485458-358','2026-09-03',3,503000.00,'Keluar','kredit','','[Auto] Pembbelian token listrik kantor','2026-09-04 01:30:58'),
(1081,'REF-1788485502-853','2026-09-03',44,7002500.00,'Keluar','debit','','Tambahan DP sepatu Ozero','2026-09-04 01:31:42'),
(1082,'REF-1788485502-853','2026-09-03',3,7002500.00,'Keluar','kredit','','[Auto] Tambahan DP sepatu Ozero','2026-09-04 01:31:42'),
(1083,'REF-1788485612-282','2026-09-05',59,279890856.00,'Keluar','debit','','Pembayaran  pajak periode November 2025','2026-09-04 01:33:32'),
(1084,'REF-1788485612-282','2026-09-03',3,279890856.00,'Keluar','kredit','','[Auto] Pembayaran  pajak periode November 2025','2026-09-04 01:33:32'),
(1087,'REF-1788503156-518','2026-09-05',66,281201542.00,'Keluar','debit','','Pembayaran BPJS Ketenagakerjaan TJS-IPIP','2026-09-04 06:25:56'),
(1088,'REF-1788503156-518','2026-09-04',3,281201542.00,'Keluar','kredit','','[Auto] Pembayaran BPJS Ketenagakerjaan TJS-Antam','2026-09-04 06:25:56'),
(1089,'REF-1788503234-136','2026-09-05',66,7082439.00,'Keluar','debit','','Pembayaran BPJS Ketenagakerjaan TJS-Antam','2026-09-04 06:27:14'),
(1090,'REF-1788503234-136','2026-09-04',3,7082439.00,'Keluar','kredit','','[Auto] Pembayaran BPJS Ketenagakerjaan TJS-Antam','2026-09-04 06:27:14'),
(1104,NULL,'2026-08-01',47,6713906549.00,'Keluar','debit',NULL,'Auto-Posting Payroll: 21/06/26 - 20/07/26 - Beban Gaji','2026-09-05 01:36:33'),
(1105,NULL,'2026-08-01',9,189650000.00,'Masuk','kredit',NULL,'Auto-Posting Payroll: 21/06/26 - 20/07/26 - Pelunasan Pinjaman','2026-09-05 01:36:33'),
(1106,NULL,'2026-08-01',40,327596008.00,'Masuk','kredit',NULL,'Auto-Posting Payroll: 21/06/26 - 20/07/26 - Denda/Sanksi','2026-09-05 01:36:33'),
(1107,NULL,'2026-08-01',22,115216079.00,'Masuk','kredit',NULL,'Auto-Posting Payroll: 21/06/26 - 20/07/26 - Sumbangan/Titipan','2026-09-05 01:36:33'),
(1108,NULL,'2026-08-01',1,6081444492.00,'Keluar','kredit',NULL,'Auto-Posting Payroll: 21/06/26 - 20/07/26 - Pembayaran Gaji Bersih ke Karyawan','2026-09-05 01:36:33'),
(1114,NULL,'2026-09-01',47,6633677622.00,'Keluar','debit',NULL,'Auto-Posting Payroll: 21/07/26 - 20/08/26 - Beban Gaji','2026-09-05 01:46:56'),
(1115,NULL,'2026-09-01',9,180011870.00,'Masuk','kredit',NULL,'Auto-Posting Payroll: 21/07/26 - 20/08/26 - Pelunasan Pinjaman','2026-09-05 01:46:56'),
(1116,NULL,'2026-09-01',40,416213433.00,'Masuk','kredit',NULL,'Auto-Posting Payroll: 21/07/26 - 20/08/26 - Denda/Sanksi','2026-09-05 01:46:56'),
(1117,NULL,'2026-09-01',22,118814338.00,'Masuk','kredit',NULL,'Auto-Posting Payroll: 21/07/26 - 20/08/26 - Sumbangan/Titipan','2026-09-05 01:46:56'),
(1118,NULL,'2026-09-01',1,5918637951.00,'Keluar','kredit',NULL,'Auto-Posting Payroll: 21/07/26 - 20/08/26 - Pembayaran Gaji Bersih ke Karyawan','2026-09-05 01:46:56'),
(1123,'REF-1788574522-495','2026-09-05',66,36531062.00,'Keluar','debit','','Pembayaran BPJS Kesehatan','2026-09-05 02:15:22'),
(1124,'REF-1788574522-495','2026-09-05',3,36531062.00,'Keluar','kredit','','[Auto] Pembayaran BPJS Kesehatan','2026-09-05 02:15:22'),
(1125,'REF-1788744648-653','2026-09-03',42,300000.00,'Keluar','debit','','Pembelian BBM Pertamax','2026-09-07 01:30:48'),
(1126,'REF-1788744648-653','2026-09-03',1,300000.00,'Keluar','kredit','','[Auto] Pembelian BBM Pertamax','2026-09-07 01:30:48'),
(1127,'REF-1788744748-890','2026-09-03',50,85000.00,'Keluar','debit','','Pembelian kertas HVS A4 & gunting','2026-09-07 01:32:28'),
(1128,'REF-1788744748-890','2026-09-03',3,85000.00,'Keluar','kredit','','[Auto] Pembelian kertas HVS A4 & gunting','2026-09-07 01:32:28'),
(1129,'REF-1788744828-530','2026-09-02',61,700000.00,'Keluar','debit','','Biaya perbaikan laptop Hadi','2026-09-07 01:33:48'),
(1130,'REF-1788744828-530','2026-09-02',3,700000.00,'Keluar','kredit','','[Auto] Biaya perbaikan laptop Hadi','2026-09-07 01:33:48'),
(1131,'REF-1788744875-900','2026-09-05',50,65000.00,'Keluar','debit','','Pembelian kertas HVS A4','2026-09-07 01:34:35'),
(1132,'REF-1788744875-900','2026-09-05',3,65000.00,'Keluar','kredit','','[Auto] Pembelian kertas HVS A4','2026-09-07 01:34:35'),
(1133,'REF-1788830690-569','2026-09-07',42,375000.00,'Keluar','debit','','Pembelian BBM pertalite','2026-09-08 01:24:50'),
(1134,'REF-1788830690-569','2026-09-07',3,375000.00,'Keluar','kredit','','[Auto] Pembelian BBM pertalite','2026-09-08 01:24:50'),
(1135,'REF-1788915732-227','2026-09-05',59,808597759.00,'Keluar','debit','','Pembayaran pajak Desember 2025','2026-09-09 01:02:12'),
(1136,'REF-1788915732-227','2026-09-05',3,808597759.00,'Keluar','kredit','','[Auto] Pembayaran pajak Desember 2025','2026-09-09 01:02:12'),
(1137,'REF-1788915822-844','2026-09-08',50,1124000.00,'Keluar','debit','','Pembelian kertas HVS A4 dan tinta','2026-09-09 01:03:42'),
(1138,'REF-1788915822-844','2026-09-08',3,1124000.00,'Keluar','kredit','','[Auto] Pembelian kertas HVS A4 dan tinta','2026-09-09 01:03:42'),
(1139,'REF-1788916205-432','2026-09-08',33,100006500.00,'Keluar','debit','','Pengambilan dana (modal) oleh Pak Nafrudin','2026-09-09 01:10:05'),
(1140,'REF-1788916205-432','2026-09-08',3,100006500.00,'Keluar','kredit','','[Auto] Pengambilan dana (modal) oleh Pak Nafrudin','2026-09-09 01:10:05'),
(1141,'REF-1788916276-526','2026-09-08',33,5006500.00,'Keluar','debit','','Pengambilan dana (modal) oleh pak Nafrudin','2026-09-09 01:11:16'),
(1142,'REF-1788916276-526','2026-09-08',3,5006500.00,'Keluar','kredit','','[Auto] Pengambilan dana (modal) oleh pak Nafrudin','2026-09-09 01:11:16'),
(1143,'REF-1788918098-830','2026-09-08',50,100000.00,'Keluar','debit','','Pembelian ATK','2026-09-09 01:41:38'),
(1144,'REF-1788918098-830','2026-09-08',3,100000.00,'Keluar','kredit','','[Auto] Pembelian ATK','2026-09-09 01:41:38'),
(1147,'REF-1789347750-329','2026-09-11',44,17306500.00,'Keluar','debit','','Pelunasan baju 300 pcs','2026-09-14 01:02:30'),
(1148,'REF-1789347750-329','2026-09-11',3,17306500.00,'Keluar','kredit','','[Auto] Pelunasan baju 300 pcs','2026-09-14 01:02:30'),
(1149,'REF-1789347813-574','2026-09-11',44,5469500.00,'Keluar','debit','','Pelunasan sepatu','2026-09-14 01:03:33'),
(1150,'REF-1789347813-574','2026-09-11',3,5469500.00,'Keluar','kredit','','[Auto] Pelunasan sepatu','2026-09-14 01:03:33'),
(1151,'REF-1789348222-318','2026-09-12',49,36000000.00,'Keluar','debit','','Pembayaran LV selama 2 bulan','2026-09-14 01:10:22'),
(1152,'REF-1789348222-318','2026-09-12',3,36000000.00,'Keluar','kredit','','[Auto] Pembayaran LV selama 2 bulan','2026-09-14 01:10:22'),
(1153,'REF-1789365671-653','2026-09-09',42,200000.00,'Keluar','debit','','Pembelian BBM','2026-09-14 06:01:11'),
(1154,'REF-1789365671-653','2026-09-09',1,200000.00,'Keluar','kredit','','[Auto] Pembelian BBM','2026-09-14 06:01:11'),
(1155,'REF-1789365733-165','2026-09-10',69,70000.00,'Keluar','debit','','Biaya pencucian mobil','2026-09-14 06:02:13'),
(1156,'REF-1789365733-165','2026-09-10',3,70000.00,'Keluar','kredit','','[Auto] Biaya pencucian mobil','2026-09-14 06:02:13'),
(1157,'REF-1789365778-355','2026-09-10',42,400000.00,'Keluar','debit','','Pembelian BBM','2026-09-14 06:02:58'),
(1158,'REF-1789365778-355','2026-09-10',3,400000.00,'Keluar','kredit','','[Auto] Pembelian BBM','2026-09-14 06:02:58'),
(1159,'REF-1789365821-202','2026-09-12',42,400000.00,'Keluar','debit','','Pembelian BBM','2026-09-14 06:03:41'),
(1160,'REF-1789365821-202','2026-09-12',3,400000.00,'Keluar','kredit','','[Auto] Pembelian BBM','2026-09-14 06:03:41'),
(1161,'REF-1789440062-548','2026-09-15',42,450000.00,'Keluar','debit','','Pembelian BBM','2026-09-15 02:41:02'),
(1162,'REF-1789440062-548','2026-09-15',1,450000.00,'Keluar','kredit','','[Auto] Pembelian BBM','2026-09-15 02:41:02'),
(1163,'REF-1789440582-663','2026-09-14',42,400000.00,'Keluar','debit','','Pembelian BBM','2026-09-15 02:49:42'),
(1164,'REF-1789440582-663','2026-09-14',3,400000.00,'Keluar','kredit','','[Auto] Pembelian BBM','2026-09-15 02:49:42'),
(1165,'REF-1789440615-641','2026-09-15',57,400000.00,'Keluar','debit','','Biaya ongkos kirim baju','2026-09-15 02:50:15'),
(1166,'REF-1789440615-641','2026-09-15',3,400000.00,'Keluar','kredit','','[Auto] Biaya ongkos kirim baju','2026-09-15 02:50:15'),
(1167,'REF-1789619931-809','2026-09-15',44,756000.00,'Keluar','debit','','Peralatan APD ','2026-09-17 04:38:51'),
(1168,'REF-1789619931-809','2026-09-15',3,756000.00,'Keluar','kredit','','[Auto] Peralatan APD ','2026-09-17 04:38:51'),
(1169,'REF-1789620005-161','2026-09-16',46,100000000.00,'Keluar','debit','','Pembayaran MCU','2026-09-17 04:40:05'),
(1170,'REF-1789620005-161','2026-09-16',3,100000000.00,'Keluar','kredit','','[Auto] Pembayaran MCU','2026-09-17 04:40:05'),
(1171,'REF-1789693486-675','2026-09-17',42,500000.00,'Keluar','debit','','Pembelian BBM Pertamax','2026-09-18 01:04:46'),
(1172,'REF-1789693486-675','2026-09-17',1,500000.00,'Keluar','kredit','','[Auto] Pembelian BBM Pertamax','2026-09-18 01:04:46'),
(1173,'REF-1789969422-237','2026-09-21',56,12650268.00,'Keluar','debit','','Pembayaran listrik hotel','2026-09-21 05:43:42'),
(1174,'REF-1789969422-237','2026-09-21',3,12650268.00,'Keluar','kredit','','[Auto] Pembayaran listrik hotel','2026-09-21 05:43:42'),
(1175,'REF-1789969469-106','2026-09-21',76,7786500.00,'Keluar','debit','','Angsuran mobil box','2026-09-21 05:44:29'),
(1176,'REF-1789969469-106','2026-09-21',3,7786500.00,'Keluar','kredit','','[Auto] Angsuran mobil box','2026-09-21 05:44:29'),
(1177,'REF-1789969518-979','2026-09-21',76,7786500.00,'Keluar','debit','','Angsuran mobil Rush','2026-09-21 05:45:18'),
(1178,'REF-1789969518-979','2026-09-21',3,7786500.00,'Keluar','kredit','','[Auto] Angsuran mobil Rush','2026-09-21 05:45:18'),
(1179,'REF-1789969596-925','2026-09-21',76,6686500.00,'Keluar','debit','','Angsuran mobil operasional Rush','2026-09-21 05:46:36'),
(1180,'REF-1789969596-925','2026-09-21',3,6686500.00,'Keluar','kredit','','[Auto] Angsuran mobil operasional Rush','2026-09-21 05:46:36'),
(1181,'REF-1790128205-546','2026-09-21',42,500000.00,'Keluar','debit','','Pembelian BBM pertamax','2026-09-23 01:50:05'),
(1182,'REF-1790128205-546','2026-09-21',1,500000.00,'Keluar','kredit','','[Auto] Pembelian BBM pertamax','2026-09-23 01:50:05'),
(1183,'REF-1790128263-713','2026-09-21',56,319150.00,'Keluar','debit','','Pembayaran listrik kantor','2026-09-23 01:51:03'),
(1184,'REF-1790128263-713','2026-09-21',3,319150.00,'Keluar','kredit','','[Auto] Pembayaran listrik kantor','2026-09-23 01:51:03'),
(1185,'REF-1790216483-983','2026-09-23',76,23379500.00,'Keluar','debit','','Pembayaran angsuran fortuner','2026-09-24 02:21:23'),
(1186,'REF-1790216483-983','2026-09-23',5,23379500.00,'Keluar','kredit','','[Auto] Pembayaran angsuran fortuner','2026-09-24 02:21:23'),
(1187,'REF-1790216523-500','2026-09-23',57,50000.00,'Keluar','debit','','Biaya ongkos kirim baju','2026-09-24 02:22:03'),
(1188,'REF-1790216523-500','2026-09-23',3,50000.00,'Keluar','kredit','','[Auto] Biaya ongkos kirim baju','2026-09-24 02:22:03'),
(1189,'REF-1790298987-420','2026-09-24',76,18500000.00,'Keluar','debit','','Pembayaran angsuran mixer','2026-09-25 01:16:27'),
(1190,'REF-1790298987-420','2026-09-24',3,18500000.00,'Keluar','kredit','','[Auto] Pembayaran angsuran mixer','2026-09-25 01:16:27'),
(1191,'REF-1790299101-177','2026-09-25',28,25100000.00,'Keluar','debit','','Pembayaran angsuran ke Bank BRI','2026-09-25 01:18:21'),
(1192,'REF-1790299101-177','2026-09-25',3,25100000.00,'Keluar','kredit','','[Auto] Pembayaran angsuran ke Bank BRI','2026-09-25 01:18:21'),
(1193,'REF-1790299185-913','2026-09-25',76,7753500.00,'Keluar','debit','','Pembayaran angsuran Rush 2','2026-09-25 01:19:45'),
(1194,'REF-1790299185-913','2026-09-25',3,7753500.00,'Keluar','kredit','','[Auto] Pembayaran angsuran Rush 2','2026-09-25 01:19:45'),
(1195,'REF-1790559191-462','2026-09-27',42,500000.00,'Keluar','debit','','Pembelian BBM Pertamax','2026-09-28 01:33:11'),
(1196,'REF-1790559191-462','2026-09-27',1,500000.00,'Keluar','kredit','','[Auto] Pembelian BBM Pertamax','2026-09-28 01:33:11'),
(1197,'REF-1790559232-412','2026-09-27',44,795000.00,'Keluar','debit','','Pembelian helm APD','2026-09-28 01:33:52'),
(1198,'REF-1790559232-412','2026-09-27',3,795000.00,'Keluar','kredit','','[Auto] Pembelian helm APD','2026-09-28 01:33:52'),
(1199,'REF-1790559282-451','2026-09-25',50,200000.00,'Keluar','debit','','Pembelian materai','2026-09-28 01:34:42'),
(1200,'REF-1790559282-451','2026-09-25',1,200000.00,'Keluar','kredit','','[Auto] Pembelian materai','2026-09-28 01:34:42'),
(1201,'REF-1790559319-529','2026-09-25',55,271000.00,'Keluar','debit','','Biaya konsumsi dengan pak Viktor','2026-09-28 01:35:19'),
(1202,'REF-1790559319-529','2026-09-25',3,271000.00,'Keluar','kredit','','[Auto] Biaya konsumsi dengan pak Viktor','2026-09-28 01:35:21'),
(1203,'REF-1790559354-845','2026-09-25',55,88000.00,'Keluar','debit','','Biaya konsumsi dengan pak viktor','2026-09-28 01:35:55'),
(1204,'REF-1790559354-845','2026-09-25',3,88000.00,'Keluar','kredit','','[Auto] Biaya konsumsi dengan pak viktor','2026-09-28 01:36:06'),
(1205,'REF-1790559431-459','2026-09-25',42,220000.00,'Keluar','debit','','Pembelian BBM eceran','2026-09-28 01:37:11'),
(1206,'REF-1790559431-459','2026-09-25',3,220000.00,'Keluar','kredit','','[Auto] Pembelian BBM eceran','2026-09-28 01:37:54'),
(1207,'REF-1790735478-972','2026-09-29',6,17506500.00,'Keluar','debit','','DP baju dami','2026-09-30 02:31:18'),
(1208,'REF-1790735478-972','2026-09-29',3,17506500.00,'Keluar','kredit','','[Auto] DP baju dami','2026-09-30 02:31:18');
/*!40000 ALTER TABLE `tb_transaksi_keuangan` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

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
  `jenis_tunjangan` enum('tetap','tidak_tetap') NOT NULL DEFAULT 'tetap' COMMENT 'Tipe tunjangan: tetap / tidak_tetap',
  `jumlah_tunjangan` varchar(25) NOT NULL,
  `tampil_template` tinyint(1) unsigned NOT NULL DEFAULT 1 COMMENT '1=tampilkan di dokumen kontrak, 0=sembunyikan',
  PRIMARY KEY (`id_tunjangan`),
  KEY `relasi_tunjangan_karyawan` (`id_karier`),
  CONSTRAINT `relasi_tunjangan_karyawan` FOREIGN KEY (`id_karier`) REFERENCES `tb_karier` (`id_karier`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tb_tunjangan`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `tb_tunjangan` WRITE;
/*!40000 ALTER TABLE `tb_tunjangan` DISABLE KEYS */;
/*!40000 ALTER TABLE `tb_tunjangan` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `tb_tunjangan_kontrak`
--

DROP TABLE IF EXISTS `tb_tunjangan_kontrak`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tb_tunjangan_kontrak` (
  `id_tunjangan_kontrak` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_detail_kontrak` int(11) unsigned NOT NULL COMMENT 'Relasi ke tb_detail_kontrak.id_detail',
  `nama_tunjangan` varchar(25) NOT NULL,
  `jenis_tunjangan` enum('tetap','tidak_tetap') NOT NULL DEFAULT 'tetap' COMMENT 'Tipe tunjangan: tetap / tidak_tetap',
  `jumlah_tunjangan` varchar(25) NOT NULL,
  `tampil_template` tinyint(1) unsigned NOT NULL DEFAULT 1 COMMENT '1=tampilkan di dokumen kontrak, 0=sembunyikan',
  PRIMARY KEY (`id_tunjangan_kontrak`),
  KEY `idx_tk_detail` (`id_detail_kontrak`),
  CONSTRAINT `fk_tk_detail` FOREIGN KEY (`id_detail_kontrak`) REFERENCES `tb_detail_kontrak` (`id_detail`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Tunjangan milik detail kontrak (bisa banyak per detail)';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tb_tunjangan_kontrak`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `tb_tunjangan_kontrak` WRITE;
/*!40000 ALTER TABLE `tb_tunjangan_kontrak` DISABLE KEYS */;
INSERT INTO `tb_tunjangan_kontrak` VALUES
(1,1,'Tunjangan Posisi','tetap','0',1),
(2,1,'Bonus Kehadiran','tetap','100000',1),
(3,1,'Tunjangan Daerah','tetap','100000',1),
(4,1,'Tunjangan Hidup','tetap','400000',1),
(5,1,'Tunjangan Skill','tetap','0',1),
(6,1,'Tunjangan Makan','tetap','15000',1),
(7,5,'Tunjangan Daerah','tetap','100000',1),
(8,5,'Tunjangan Hidup','tetap','400000',1),
(9,5,'Tunjangan Makan','tidak_tetap','20000',1),
(10,5,'Tunjangan Skill','tidak_tetap','100000',1),
(11,5,'Bonus Kehadiran','tidak_tetap','150000',1),
(12,5,'Tunjangan Posisi','tetap','0',1);
/*!40000 ALTER TABLE `tb_tunjangan_kontrak` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tb_user`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `tb_user` WRITE;
/*!40000 ALTER TABLE `tb_user` DISABLE KEYS */;
INSERT INTO `tb_user` VALUES
(2,'triputra','$2y$10$1AxuzLjb/yqMcpV6RgpsBexZaLdCOoLu9VssJbvwW/9vyaCtSkSO.','Master Fendi','Master','1782196454_715f63c63d73.jpeg',0,0),
(6,'safety','$2y$10$Xzb6LAg5X2ygst6p6f5Jv.vlLJxLJ3c9GzlNV/Ygj8P87R4zMO/Sa','Abdul','Safety','',0,4),
(7,'direktur','$2y$10$B4ECIPkrumefjkxTeihk/eVQcaY4T9FdHmaWHWGHr.tCKc8ojnaC.','Muh. Ilham Nafruddin','Direktur','1775643038_5f4105a073a9.jpeg',0,1),
(8,'fadly','$2y$12$lBGu9AJ0DCNxQn/J13i8buk1mHj/aYgorXUFY.vFuEshKN7NTo3xO','Muhammad Fadly','HRD','',0,2),
(9,'supervisor','$2y$10$qRgbZKn4j5TEqbG.T5njKuioJZV4jT22JEY2hhdNMXiWac4Wmm0hi','Alimin','Supervisor','',0,3),
(10,'admin','$2y$10$r0/PTuPWCeH8J5byp9Ptte6betFh61OagvXJu6DpmY96fnloRfYNy','Admin TJS','Admin','',0,5),
(11,'masterakmal','$2y$10$goETfrpOuSECnQf3s.chS.zcwOXq98fecnTi/0gVio9ZsDPUrTkDS','Akmal Vitamin C','IT','1787573577_f8c040ebde3b.png',0,0),
(12,'finance','$2y$10$quXEVfzXqv2TcTX/oidTzemNeh7KWQI9fqsLwF1U3gitatBeMvwdu','Arya','Bendahara','',0,6),
(16,'arya','$2y$12$Q1ZaIyFB0JObi2cN2.kFqOB3N3HWm.PpyJWrllnNJqL0sP9ylwAGy','Arya','Bendahara','',24,6);
/*!40000 ALTER TABLE `tb_user` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=125 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tb_user_token`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `tb_user_token` WRITE;
/*!40000 ALTER TABLE `tb_user_token` DISABLE KEYS */;
/*!40000 ALTER TABLE `tb_user_token` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*M!100616 SET NOTE_VERBOSITY=@OLD_NOTE_VERBOSITY */;

-- Dump completed on 2026-10-24 20:24:16
