-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 20, 2026 at 11:04 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `simpaud`
--

-- --------------------------------------------------------

--
-- Table structure for table `absensi_guru`
--

CREATE TABLE `absensi_guru` (
  `id` int(11) NOT NULL,
  `id_guru` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `status` enum('Hadir','Izin','Sakit','Alpha') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `absensi_guru`
--

INSERT INTO `absensi_guru` (`id`, `id_guru`, `tanggal`, `status`, `created_at`) VALUES
(1, 3, '2026-07-18', 'Hadir', '2026-07-18 16:56:32'),
(2, 2, '2026-07-18', 'Hadir', '2026-07-18 16:56:35'),
(3, 1, '2026-07-18', 'Hadir', '2026-07-18 16:56:38'),
(4, 3, '2026-07-20', 'Hadir', '2026-07-20 08:58:57');

-- --------------------------------------------------------

--
-- Table structure for table `guru`
--

CREATE TABLE `guru` (
  `id` int(11) NOT NULL,
  `nama_guru` varchar(100) NOT NULL,
  `nuptk` varchar(50) DEFAULT NULL,
  `status_pegawai` enum('PNS','Non PNS') DEFAULT NULL,
  `jk` enum('L','P') DEFAULT NULL,
  `tempat_lahir` varchar(100) DEFAULT NULL,
  `tgl_lahir` date DEFAULT NULL,
  `jtm` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `guru`
--

INSERT INTO `guru` (`id`, `nama_guru`, `nuptk`, `status_pegawai`, `jk`, `tempat_lahir`, `tgl_lahir`, `jtm`) VALUES
(1, 'Yanah, S.Pd.I, S.Pd', '0745755657210072', 'Non PNS', 'P', 'Jakarta', '1977-04-13', 24),
(2, 'Umi Kalsum, S.Pd', '20178427179002', 'Non PNS', 'P', 'Jakarta', '1979-08-08', 30),
(3, 'Marwah, S.Pd', '20178427173001', 'Non PNS', 'P', 'Jakarta', '1973-10-26', 30);

-- --------------------------------------------------------

--
-- Table structure for table `kelas`
--

CREATE TABLE `kelas` (
  `id` int(11) NOT NULL,
  `nama_kelas` varchar(50) NOT NULL,
  `kelompok` enum('A','B','Playgroup') NOT NULL,
  `wali_kelas_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `materi_poin`
--

CREATE TABLE `materi_poin` (
  `id` int(11) NOT NULL,
  `kategori` varchar(100) NOT NULL,
  `deskripsi` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `materi_poin`
--

INSERT INTO `materi_poin` (`id`, `kategori`, `deskripsi`, `created_at`) VALUES
(1, 'Nilai Agama', 'Anak dapat mengenal dan percaya kepada Allah SWT melalui asmaul husna', '2026-07-09 01:41:01'),
(2, 'Nilai Agama', 'Anak mengenal Al-Qur\'an dan al-Hadis sebagai pedoman hidupnya.', '2026-07-20 08:47:54'),
(3, 'Nilai Agama', 'Anak mempraktekan ibadah sehari-hari dengan tuntunan orang dewasa', '2026-07-20 08:48:38');

-- --------------------------------------------------------

--
-- Table structure for table `materi_sub_poin`
--

CREATE TABLE `materi_sub_poin` (
  `id` int(11) NOT NULL,
  `poin_id` int(11) NOT NULL,
  `kode` varchar(20) NOT NULL,
  `deskripsi` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `materi_sub_poin`
--

INSERT INTO `materi_sub_poin` (`id`, `poin_id`, `kode`, `deskripsi`) VALUES
(1, 1, 'A.1.1', 'Anak mampu menirukan dan menyebutkan minimal sepuluh (10) Asmaul Husna dengan artinya'),
(2, 2, 'A.1.1', 'Anak mengenal Al Qur’an sebagai pedoman hidupnya'),
(3, 2, 'A.1.2', 'Anak mengenal Al Hadist sebagai pedoman hidupnya'),
(4, 3, 'A.1.1', 'Anak mengenal ibadah sehari-hari dengan tuntunan orang dewasa.'),
(5, 3, 'A.1.2', 'Anak mengenal ibadah sehari-hari dengan tuntunan orang dewasa.'),
(6, 3, 'A.1.3', 'Anak mempraktikkan  ibadah sehari-hari dengan tuntunan orang dewasa.');

-- --------------------------------------------------------

--
-- Table structure for table `penilaian`
--

CREATE TABLE `penilaian` (
  `id` int(11) NOT NULL,
  `rpph_id` int(11) NOT NULL,
  `siswa_id` int(11) NOT NULL,
  `indikator` text NOT NULL,
  `nilai` enum('BB','MB','BSH','BSB') DEFAULT NULL,
  `tanggal` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `penilaian`
--

INSERT INTO `penilaian` (`id`, `rpph_id`, `siswa_id`, `indikator`, `nilai`, `tanggal`, `created_at`) VALUES
(1, 1, 1, 'Anak dapat mengenal guru dan teman baru di sekolah.', 'BSH', '2026-07-20', '2026-07-20 08:54:48'),
(2, 1, 1, 'Anak mampu beradaptasi dengan lingkungan ruang kelas dan fasilitas sekolah.', 'BSH', '2026-07-20', '2026-07-20 08:54:48'),
(3, 1, 1, 'Anak mulai memahami tata tertib sederhana di sekolah.', 'BSH', '2026-07-20', '2026-07-20 08:54:48'),
(4, 1, 2, 'Anak dapat mengenal guru dan teman baru di sekolah.', 'BSH', '2026-07-20', '2026-07-20 08:54:48'),
(5, 1, 2, 'Anak mampu beradaptasi dengan lingkungan ruang kelas dan fasilitas sekolah.', 'BSH', '2026-07-20', '2026-07-20 08:54:48'),
(6, 1, 2, 'Anak mulai memahami tata tertib sederhana di sekolah.', 'BSH', '2026-07-20', '2026-07-20 08:54:48'),
(7, 1, 3, 'Anak dapat mengenal guru dan teman baru di sekolah.', 'BSB', '2026-07-20', '2026-07-20 08:54:48'),
(8, 1, 3, 'Anak mampu beradaptasi dengan lingkungan ruang kelas dan fasilitas sekolah.', 'BSH', '2026-07-20', '2026-07-20 08:54:48'),
(9, 1, 3, 'Anak mulai memahami tata tertib sederhana di sekolah.', 'BSH', '2026-07-20', '2026-07-20 08:54:48'),
(10, 1, 4, 'Anak dapat mengenal guru dan teman baru di sekolah.', 'MB', '2026-07-20', '2026-07-20 08:54:48'),
(11, 1, 4, 'Anak mampu beradaptasi dengan lingkungan ruang kelas dan fasilitas sekolah.', 'BSH', '2026-07-20', '2026-07-20 08:54:48'),
(12, 1, 4, 'Anak mulai memahami tata tertib sederhana di sekolah.', 'BSH', '2026-07-20', '2026-07-20 08:54:48'),
(13, 1, 5, 'Anak dapat mengenal guru dan teman baru di sekolah.', 'BSH', '2026-07-20', '2026-07-20 08:54:48'),
(14, 1, 5, 'Anak mampu beradaptasi dengan lingkungan ruang kelas dan fasilitas sekolah.', 'BSH', '2026-07-20', '2026-07-20 08:54:48'),
(15, 1, 5, 'Anak mulai memahami tata tertib sederhana di sekolah.', 'BSH', '2026-07-20', '2026-07-20 08:54:48');

-- --------------------------------------------------------

--
-- Table structure for table `program_semester`
--

CREATE TABLE `program_semester` (
  `id` int(11) NOT NULL,
  `bulan` varchar(50) NOT NULL,
  `alokasi_waktu` varchar(50) NOT NULL,
  `tema` varchar(255) NOT NULL,
  `sub_topik` text NOT NULL,
  `keterangan` text NOT NULL,
  `capaian` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `program_semester`
--

INSERT INTO `program_semester` (`id`, `bulan`, `alokasi_waktu`, `tema`, `sub_topik`, `keterangan`, `capaian`, `created_at`) VALUES
(1, 'Juli', '1 Minggu', 'MPLS (Masa Pengenalan Lingkungan Sekolah)', '-	Mengenal guru\r\n-	Mengenal teman\r\n-	Mengenal ruang kelas dan fasilitas sekolah\r\n-	Tata tertib sederhana\r\n-	Pembiasaan positif\r\n-	Bermain bersama', 'Masa Pengenalan Sekolah atau Adaptasi', 'Jati diri ', '2026-07-20 08:51:24'),
(2, 'Juli', '4 Minggu', 'Diriku', '-	Aku istimewa\r\n-	Identitasku\r\n-	Tubuhku\r\n-	Kesukaanku', 'Aku mengenal diri sendiri, tubuhku, perasaanku, hal – hal yang kusukai', 'Jati diri', '2026-07-20 08:52:30');

-- --------------------------------------------------------

--
-- Table structure for table `rpph`
--

CREATE TABLE `rpph` (
  `id` int(11) NOT NULL,
  `semester_bulan` varchar(100) DEFAULT NULL,
  `kelompok` varchar(100) DEFAULT NULL,
  `minggu_hari` varchar(100) DEFAULT NULL,
  `tema` varchar(255) DEFAULT NULL,
  `subtema` varchar(255) DEFAULT NULL,
  `kd` text DEFAULT NULL,
  `tujuan` text DEFAULT NULL,
  `kegiatan_pembuka` text DEFAULT NULL,
  `tanggal_dibuat` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rpph`
--

INSERT INTO `rpph` (`id`, `semester_bulan`, `kelompok`, `minggu_hari`, `tema`, `subtema`, `kd`, `tujuan`, `kegiatan_pembuka`, `tanggal_dibuat`) VALUES
(1, '1 / Juli', 'Kelas A (4-5 tahun)', '1 / Senin, 13 Juli 2026', 'MPLS (Masa Pengenalan Lingkungan Sekolah)', 'Aku Cinta Indonesia', '', 'Anak dapat mengenal guru dan teman baru di sekolah.\r\nAnak mampu beradaptasi dengan lingkungan ruang kelas dan fasilitas sekolah.\r\nAnak mulai memahami tata tertib sederhana di sekolah.', 'A. Pembukaan:\r\nPenyambutan anak di gerbang sekolah.\r\nBerbaris, salam, doa pembuka, dan presensi/absensi pagi.\r\nBernyanyi lagu ceria bersama untuk mencairkan suasana.\r\n\r\nB. Inti:\r\nMengenal Guru & Teman: Guru memperkenalkan diri secara menarik, dilanjutkan dengan permainan estafet bola/nama untuk saling mengenal teman baru \r\nMengenal Ruang Kelas & Fasilitas: School tour bersama (berkeliling melihat toilet, tempat cuci tangan, area bermain, dan kantor).\r\nMembahas aturan main dan tata tertib sederhana di dalam kelas (misal: menaruh tas di loker).\r\n\r\nC. Penutup:\r\nRefleksi perasaan anak di hari pertama sekolah (menanyakan perasaan mereka).\r\nMengulas kembali nama-nama guru atau teman yang diingat.\r\nInformasi singkat untuk kegiatan esok hari.\r\nDoa pulang dan salam', '2026-07-19 01:53:45');

-- --------------------------------------------------------

--
-- Table structure for table `rpph_harian`
--

CREATE TABLE `rpph_harian` (
  `id` int(11) NOT NULL,
  `kelas_id` int(11) NOT NULL,
  `hari_tanggal` date NOT NULL,
  `tema` varchar(100) DEFAULT NULL,
  `sub_tema` varchar(100) DEFAULT NULL,
  `materi` text DEFAULT NULL,
  `status` enum('draft','selesai') DEFAULT 'draft',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rppm`
--

CREATE TABLE `rppm` (
  `id` int(11) NOT NULL,
  `penulis` varchar(255) NOT NULL,
  `asal_sekolah` varchar(255) NOT NULL,
  `fase` varchar(50) DEFAULT NULL,
  `model_pembelajaran` varchar(100) DEFAULT NULL,
  `semester_bulan` varchar(100) DEFAULT NULL,
  `minggu_ke` varchar(50) DEFAULT NULL,
  `topik` varchar(255) DEFAULT NULL,
  `cp` text DEFAULT NULL,
  `tp` text DEFAULT NULL,
  `kegiatan_inti` text DEFAULT NULL,
  `tanggal_dibuat` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rppm`
--

INSERT INTO `rppm` (`id`, `penulis`, `asal_sekolah`, `fase`, `model_pembelajaran`, `semester_bulan`, `minggu_ke`, `topik`, `cp`, `tp`, `kegiatan_inti`, `tanggal_dibuat`) VALUES
(1, 'Yanah, S.Pd.I, S.Pd', 'RA Darul Falah', 'Pondasi / PAUD', 'Tatap Muka', '1 / Juli', '1', 'MPLS (Masa Pengenalan Lingkungan Sekolah) / Aku Cinta Indonesia', 'Anak mengenali dan mengekspresikan emosi yang wajar, beradaptasi dengan lingkungan sekolah baru, serta membangun hubungan sosial yang sehat dengan pendidik dan teman sebaya.', '1. Anak mampu beradaptasi dan merasa nyaman dengan lingkungan sekolah baru.\r\n2. Anak dapat mengenal serta berinteraksi dengan guru dan teman sebaya.\r\n3. Anak dapat memahami dan mengikuti aturan/tata tertib sederhana di kelas.', '- Senin: Hari Berkenalan: Permainan interaktif menggunakan bola/nama untuk saling mengenal guru dan teman baru.\n- Selasa: Jelajah Sekolah (School Tour): Berkeliling melihat area kelas, toilet, tempat cuci tangan, dan area bermain untuk mengenal fasilitas sekolah.\n- Rabu: Kesepakatan Kelas: Membuat aturan dan tata tertib sederhana bersama anak (misal: merapikan mainan) dibalut dengan game interaktif. (Alat & Bahan: Papan tulis, gambar/simbol aturan kelas, spidol warna.)\n- Kamis: Pembiasaan Positif: Praktik langsung cara mencuci tangan yang benar, membuang sampah, serta cara meminta izin atau mengucap tolong/terima kasih. (Alat & Bahan: Sabun cuci tangan, air mengalir, tempat sampah.)\n- Jumat: Bermain Bersama: Kegiatan fun games berkelompok di halaman sekolah untuk mempererat keakraban antar anak dan guru sebelum penutupan MPLS.\n', '2026-07-19 02:09:18');

-- --------------------------------------------------------

--
-- Table structure for table `siswa`
--

CREATE TABLE `siswa` (
  `id` int(11) NOT NULL,
  `nama_lengkap` varchar(150) NOT NULL,
  `nisn` varchar(20) DEFAULT NULL,
  `nis` varchar(20) DEFAULT NULL,
  `tempat_lahir` varchar(100) DEFAULT NULL,
  `tgl_lahir` date NOT NULL,
  `jk` enum('L','P') NOT NULL,
  `kelas` enum('A','B') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `jenis_kelamin` enum('L','P') DEFAULT NULL,
  `nama_wali` varchar(150) DEFAULT NULL,
  `alamat` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `siswa`
--

INSERT INTO `siswa` (`id`, `nama_lengkap`, `nisn`, `nis`, `tempat_lahir`, `tgl_lahir`, `jk`, `kelas`, `created_at`, `jenis_kelamin`, `nama_wali`, `alamat`) VALUES
(1, 'AURORA SHAKIRA AL BANI', '3207380583', '101231740092250192', 'Jakarta', '2020-06-05', 'P', 'B', '2026-07-18 10:40:44', NULL, 'Abdul Fatah', 'Pedurenan'),
(2, 'CARISSA ANINDIRA', '3216749978', '101231740092250193', 'Jakarta', '2021-04-23', 'P', 'B', '2026-07-18 10:45:57', NULL, 'Arie', 'Pedurenan'),
(3, 'FIERO JAYA SAPUTRA', '3200737569', '101231740092250201', 'Depok', '2020-11-17', 'L', 'B', '2026-07-18 10:46:51', NULL, 'Zain Rafif', 'Jl. Benda Gg.H.Musa'),
(4, 'GENTALA QAUTSAR ISKANDAR', '3201258903', '101231740092250194', 'Jakarta', '2020-09-06', 'L', 'B', '2026-07-18 10:47:38', NULL, 'Iskandar Pandes', 'Jl. Benda Gg.H.Musa'),
(5, 'NAURA SHAFIRA ZAHRAH', '3202971444', '101231740092250198', 'Jakarta', '2020-10-21', 'P', 'B', '2026-07-18 10:48:34', NULL, 'Ilham Rusydi', 'Kp. Pekayon GG sawo I');

-- --------------------------------------------------------

--
-- Table structure for table `spp_tagihan`
--

CREATE TABLE `spp_tagihan` (
  `id` int(11) NOT NULL,
  `id_siswa` int(11) NOT NULL,
  `bulan` varchar(50) NOT NULL,
  `nominal` int(11) NOT NULL,
  `status` enum('Lunas','Belum Bayar') NOT NULL DEFAULT 'Belum Bayar',
  `order_id` varchar(50) DEFAULT NULL,
  `tgl_bayar` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `spp_tagihan`
--

INSERT INTO `spp_tagihan` (`id`, `id_siswa`, `bulan`, `nominal`, `status`, `order_id`, `tgl_bayar`, `created_at`) VALUES
(1, 1, 'Juli 2026', 250000, 'Lunas', 'INV-202607-00001-1784428557', '2026-07-19 09:36:18', '2026-07-19 00:01:25'),
(2, 2, 'Juli 2026', 250000, 'Lunas', 'INV-202607-00002', '2026-07-19 07:18:36', '2026-07-19 00:02:43'),
(3, 3, 'Juli 2026', 250000, 'Lunas', 'INV-202607-00003-1784430317', '2026-07-19 10:05:43', '2026-07-19 03:05:15'),
(4, 4, 'Juli 2026', 250000, 'Lunas', 'INV-202607-00004-1784537962', '2026-07-20 15:59:34', '2026-07-20 08:59:14');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','guru','kepala_sekolah','orangtua') DEFAULT 'orangtua',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `absensi_guru`
--
ALTER TABLE `absensi_guru`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_guru` (`id_guru`);

--
-- Indexes for table `guru`
--
ALTER TABLE `guru`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kelas`
--
ALTER TABLE `kelas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `wali_kelas_id` (`wali_kelas_id`);

--
-- Indexes for table `materi_poin`
--
ALTER TABLE `materi_poin`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `materi_sub_poin`
--
ALTER TABLE `materi_sub_poin`
  ADD PRIMARY KEY (`id`),
  ADD KEY `poin_id` (`poin_id`);

--
-- Indexes for table `penilaian`
--
ALTER TABLE `penilaian`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `rpph_id` (`rpph_id`,`siswa_id`,`indikator`(100),`tanggal`);

--
-- Indexes for table `program_semester`
--
ALTER TABLE `program_semester`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `rpph`
--
ALTER TABLE `rpph`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `rpph_harian`
--
ALTER TABLE `rpph_harian`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kelas_id` (`kelas_id`);

--
-- Indexes for table `rppm`
--
ALTER TABLE `rppm`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `siswa`
--
ALTER TABLE `siswa`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `spp_tagihan`
--
ALTER TABLE `spp_tagihan`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `absensi_guru`
--
ALTER TABLE `absensi_guru`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `guru`
--
ALTER TABLE `guru`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `kelas`
--
ALTER TABLE `kelas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `materi_poin`
--
ALTER TABLE `materi_poin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `materi_sub_poin`
--
ALTER TABLE `materi_sub_poin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `penilaian`
--
ALTER TABLE `penilaian`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `program_semester`
--
ALTER TABLE `program_semester`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `rpph`
--
ALTER TABLE `rpph`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `rpph_harian`
--
ALTER TABLE `rpph_harian`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rppm`
--
ALTER TABLE `rppm`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `siswa`
--
ALTER TABLE `siswa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `spp_tagihan`
--
ALTER TABLE `spp_tagihan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `absensi_guru`
--
ALTER TABLE `absensi_guru`
  ADD CONSTRAINT `absensi_guru_ibfk_1` FOREIGN KEY (`id_guru`) REFERENCES `guru` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `kelas`
--
ALTER TABLE `kelas`
  ADD CONSTRAINT `kelas_ibfk_1` FOREIGN KEY (`wali_kelas_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `materi_sub_poin`
--
ALTER TABLE `materi_sub_poin`
  ADD CONSTRAINT `materi_sub_poin_ibfk_1` FOREIGN KEY (`poin_id`) REFERENCES `materi_poin` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `rpph_harian`
--
ALTER TABLE `rpph_harian`
  ADD CONSTRAINT `rpph_harian_ibfk_1` FOREIGN KEY (`kelas_id`) REFERENCES `kelas` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
