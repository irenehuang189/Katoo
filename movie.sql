-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               10.1.16-MariaDB - mariadb.org binary distribution
-- Server OS:                    Win32
-- HeidiSQL Version:             9.4.0.5125
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;


-- Dumping database structure for movie
CREATE DATABASE IF NOT EXISTS `movie` /*!40100 DEFAULT CHARACTER SET latin1 */;
USE `movie`;

-- Dumping structure for table movie.nowplaying
CREATE TABLE IF NOT EXISTS `nowplaying` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=latin1;

-- Dumping data for table movie.nowplaying: ~10 rows (approximately)
/*!40000 ALTER TABLE `nowplaying` DISABLE KEYS */;
INSERT INTO `nowplaying` (`id`, `name`) VALUES
	(1, 'danur'),
	(2, 'perfect dream'),
	(3, 'dear nathan'),
	(4, 'sword art online the movie : ordinal scale'),
	(5, 'the moment'),
	(6, 'ghost in the shell'),
	(7, 'life'),
	(8, 'beauty and the beast'),
	(9, 'smurfs the lost village'),
	(10, 'kong skull island');
/*!40000 ALTER TABLE `nowplaying` ENABLE KEYS */;

-- Dumping structure for table movie.nowplayingcinema
CREATE TABLE IF NOT EXISTS `nowplayingcinema` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `movie` varchar(100) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `showtime` varchar(10) DEFAULT NULL,
  `price` varchar(10) DEFAULT NULL,
  `auditype` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=158 DEFAULT CHARSET=latin1;

-- Dumping data for table movie.nowplayingcinema: ~157 rows (approximately)
/*!40000 ALTER TABLE `nowplayingcinema` DISABLE KEYS */;
INSERT INTO `nowplayingcinema` (`id`, `movie`, `name`, `showtime`, `price`, `auditype`) VALUES
	(1, 'LIFE', 'Kawanua Mall', '19:15', '35000', 'Regular 2D'),
	(2, 'LIFE', 'Jwalk Mall', '15:40', '30000', 'Regular 2D'),
	(3, 'LIFE', 'Paris Van Java', '21:05', '40000', 'Regular 2D'),
	(4, 'LIFE', 'Central Park', '22:00', '110000', 'Velvet Cla'),
	(5, 'LIFE', 'Grand Indonesia', '15:25', '50000', 'Starium'),
	(6, 'LIFE', 'Bekasi Cyber Park', '16:30', '30000', 'Regular 2D'),
	(7, 'LIFE', 'Mall Of Indonesia', '22:30', '40000', 'Regular 2D'),
	(8, 'LIFE', 'Grand Galaxi Park', '22:00', '25000', 'Regular 2D'),
	(9, 'LIFE', 'BEC Mall', '19:30', '35000', 'Regular 2D'),
	(10, 'LIFE', 'Hartono Mall', '21:00', '35000', 'Regular 2D'),
	(11, 'LIFE', 'Marvell City', '18:00', '35000', 'Regular 2D'),
	(12, 'LIFE', 'Pacific Place', '21:05', '50000', 'Regular 2D'),
	(13, 'BEAUTY AND THE BEAST', 'BEC Mall', '22:10', '35000', 'Regular 2D'),
	(14, 'BEAUTY AND THE BEAST', 'Grand Dadap City', '21:20', '30000', 'Regular 2D'),
	(15, 'BEAUTY AND THE BEAST', 'Central Park', '16:45', '110000', 'Velvet Cla'),
	(16, 'BEAUTY AND THE BEAST', 'Plaza Balikpapan', '16:30', '65000', 'Satin Clas'),
	(17, 'BEAUTY AND THE BEAST', 'Miko Mall', '21:55', '30000', 'Regular 2D'),
	(18, 'BEAUTY AND THE BEAST', 'Pacific Place', '20:30', '110000', 'Velvet Cla'),
	(19, 'BEAUTY AND THE BEAST', 'Jwalk Mall', '21:00', '30000', 'Regular 2D'),
	(20, 'BEAUTY AND THE BEAST', 'Festive Walk', '19:00', '35000', 'Regular 2D'),
	(21, 'BEAUTY AND THE BEAST', 'Sunrise Mall', '22:10', '35000', 'Regular 2D'),
	(22, 'BEAUTY AND THE BEAST', 'Social Market Palembang', '21:55', '35000', 'Regular 2D'),
	(23, 'BEAUTY AND THE BEAST', 'Bekasi Cyber Park', '21:55', '30000', 'Regular 2D'),
	(24, 'BEAUTY AND THE BEAST', 'Ecoplaza Cikupa', '21:35', '35000', 'Regular 2D'),
	(25, 'BEAUTY AND THE BEAST', 'Focal Point Medan', '21:00', '35000', 'Regular 2D'),
	(26, 'BEAUTY AND THE BEAST', 'Harbour Bay', '21:25', '25000', 'Regular 2D'),
	(27, 'BEAUTY AND THE BEAST', 'Rita Supermall', '21:20', '35000', 'Regular 3D'),
	(28, 'BEAUTY AND THE BEAST', 'Green Pramuka Mall', '22:35', '35000', 'Regular 2D'),
	(29, 'BEAUTY AND THE BEAST', 'Kepri Mall', '15:10', '30000', 'Regular 2D'),
	(30, 'BEAUTY AND THE BEAST', 'Mall Of Indonesia', '22:15', '40000', 'Regular 2D'),
	(31, 'BEAUTY AND THE BEAST', 'Grand Galaxi Park', '21:40', '25000', 'Regular 2D'),
	(32, 'BEAUTY AND THE BEAST', 'Grage City Mall', '21:55', '30000', 'Regular 2D'),
	(33, 'BEAUTY AND THE BEAST', 'Marvell City', '22:20', '35000', 'Regular 2D'),
	(34, 'BEAUTY AND THE BEAST', 'Kawanua Mall', '18:15', '60000', 'Velvet Cla'),
	(35, 'BEAUTY AND THE BEAST', 'Paris Van Java', '21:55', '40000', 'Regular 2D'),
	(36, 'BEAUTY AND THE BEAST', 'Grand Indonesia', '17:45', '50000', 'Starium'),
	(37, 'BEAUTY AND THE BEAST', 'Teraskota', '22:20', '35000', 'Regular 2D'),
	(38, 'BEAUTY AND THE BEAST', 'Slipi Jaya', '21:45', '35000', 'Regular 2D'),
	(39, 'BEAUTY AND THE BEAST', 'Hartono Mall', '16:45', '65000', 'Velvet Cla'),
	(40, 'AYU ANAK TITIPAN SURGA', 'Green Pramuka Mall', '10:00', '35000', 'Regular 2D'),
	(41, 'KONG SKULL ISLAND', 'Mall Of Indonesia', '21:20', '40000', 'Regular 2D'),
	(42, 'KONG SKULL ISLAND', 'Ecoplaza Cikupa', '21:10', '35000', 'Regular 2D'),
	(43, 'KONG SKULL ISLAND', 'Focal Point Medan', '21:55', '35000', 'Regular 2D'),
	(44, 'PERFECT DREAM', 'Jwalk Mall', '11:00', '30000', 'Regular 2D'),
	(45, 'PERFECT DREAM', 'Sunrise Mall', '17:30', '35000', 'Regular 2D'),
	(46, 'PERFECT DREAM', 'Bekasi Cyber Park', '21:40', '30000', 'Regular 2D'),
	(47, 'PERFECT DREAM', 'Ecoplaza Cikupa', '18:55', '35000', 'Regular 2D'),
	(48, 'PERFECT DREAM', 'Slipi Jaya', '18:45', '35000', 'Regular 2D'),
	(49, 'PERFECT DREAM', 'Miko Mall', '21:00', '30000', 'Regular 2D'),
	(50, 'PERFECT DREAM', 'Marvell City', '20:30', '35000', 'Regular 2D'),
	(51, 'PERFECT DREAM', 'Grage City Mall', '14:00', '30000', 'Regular 2D'),
	(52, 'SMURFS THE LOST VILLAGE', 'BEC Mall', '17:30', '35000', 'Regular 2D'),
	(53, 'SMURFS THE LOST VILLAGE', 'Central Park', '15:40', '45000', 'Regular 3D'),
	(54, 'SMURFS THE LOST VILLAGE', 'Grand Dadap City', '19:20', '30000', 'Regular 2D'),
	(55, 'SMURFS THE LOST VILLAGE', 'Miko Mall', '18:35', '30000', 'Regular 2D'),
	(56, 'SMURFS THE LOST VILLAGE', 'Pacific Place', '21:15', '50000', 'Regular 2D'),
	(57, 'SMURFS THE LOST VILLAGE', 'Jwalk Mall', '17:20', '30000', 'Regular 2D'),
	(58, 'SMURFS THE LOST VILLAGE', 'Sunrise Mall', '21:45', '35000', 'Regular 2D'),
	(59, 'SMURFS THE LOST VILLAGE', 'Social Market Palembang', '21:35', '35000', 'Regular 2D'),
	(60, 'SMURFS THE LOST VILLAGE', 'Bekasi Cyber Park', '19:00', '30000', 'Regular 2D'),
	(61, 'SMURFS THE LOST VILLAGE', 'Focal Point Medan', '21:00', '35000', 'Regular 2D'),
	(62, 'SMURFS THE LOST VILLAGE', 'Harbour Bay', '18:30', '25000', 'Regular 2D'),
	(63, 'SMURFS THE LOST VILLAGE', 'Rita Supermall', '16:35', '35000', 'Regular 3D'),
	(64, 'SMURFS THE LOST VILLAGE', 'Kepri Mall', '20:55', '30000', 'Regular 2D'),
	(65, 'SMURFS THE LOST VILLAGE', 'Mall Of Indonesia', '21:00', '80000', 'Velvet Cla'),
	(66, 'SMURFS THE LOST VILLAGE', 'Grand Galaxi Park', '21:15', '25000', 'Regular 2D'),
	(67, 'SMURFS THE LOST VILLAGE', 'Grage City Mall', '18:30', '30000', 'Regular 2D'),
	(68, 'SMURFS THE LOST VILLAGE', 'Marvell City', '17:15', '35000', 'Regular 2D'),
	(69, 'SMURFS THE LOST VILLAGE', 'Kawanua Mall', '19:30', '35000', 'Regular 2D'),
	(70, 'SMURFS THE LOST VILLAGE', 'Paris Van Java', '18:00', '45000', 'Regular 3D'),
	(71, 'SMURFS THE LOST VILLAGE', 'Grand Indonesia', '20:35', '50000', 'Regular 2D'),
	(72, 'SMURFS THE LOST VILLAGE', 'Teraskota', '15:30', '40000', 'Regular 3D'),
	(73, 'SMURFS THE LOST VILLAGE', 'Slipi Jaya', '19:00', '35000', 'Regular 2D'),
	(74, 'SMURFS THE LOST VILLAGE', 'Hartono Mall', '21:30', '35000', 'Starium'),
	(75, 'SWORD ART ONLINE THE MOVIE : ORDINAL SCALE', 'Kawanua Mall', '21:30', '35000', 'Regular 2D'),
	(76, 'SWORD ART ONLINE THE MOVIE : ORDINAL SCALE', 'Jwalk Mall', '14:30', '30000', 'Regular 2D'),
	(77, 'SWORD ART ONLINE THE MOVIE : ORDINAL SCALE', 'Paris Van Java', '21:35', '40000', 'Regular 2D'),
	(78, 'SWORD ART ONLINE THE MOVIE : ORDINAL SCALE', 'Kepri Mall', '17:40', '30000', 'Regular 2D'),
	(79, 'SWORD ART ONLINE THE MOVIE : ORDINAL SCALE', 'Central Park', '14:50', '40000', 'Regular 2D'),
	(80, 'SWORD ART ONLINE THE MOVIE : ORDINAL SCALE', 'Grand Indonesia', '19:35', '50000', 'Regular 2D'),
	(81, 'SWORD ART ONLINE THE MOVIE : ORDINAL SCALE', 'Bekasi Cyber Park', '21:00', '30000', 'Regular 2D'),
	(82, 'SWORD ART ONLINE THE MOVIE : ORDINAL SCALE', 'Mall Of Indonesia', '22:15', '40000', 'Regular 2D'),
	(83, 'SWORD ART ONLINE THE MOVIE : ORDINAL SCALE', 'Focal Point Medan', '15:50', '35000', 'Regular 2D'),
	(84, 'SWORD ART ONLINE THE MOVIE : ORDINAL SCALE', 'Teraskota', '21:15', '35000', 'Regular 2D'),
	(85, 'SWORD ART ONLINE THE MOVIE : ORDINAL SCALE', 'BEC Mall', '21:45', '35000', 'Regular 2D'),
	(86, 'SWORD ART ONLINE THE MOVIE : ORDINAL SCALE', 'Social Market Palembang', '17:15', '35000', 'Regular 2D'),
	(87, 'SWORD ART ONLINE THE MOVIE : ORDINAL SCALE', 'Hartono Mall', '16:45', '35000', 'Regular 2D'),
	(88, 'SWORD ART ONLINE THE MOVIE : ORDINAL SCALE', 'Marvell City', '16:50', '40000', 'Sphere X'),
	(89, 'DEAR NATHAN', 'Jwalk Mall', '20:40', '30000', 'Regular 2D'),
	(90, 'DEAR NATHAN', 'Sunrise Mall', '21:40', '35000', 'Regular 2D'),
	(91, 'DEAR NATHAN', 'Green Pramuka Mall', '21:10', '35000', 'Regular 2D'),
	(92, 'DEAR NATHAN', 'Ecoplaza Cikupa', '21:25', '35000', 'Regular 2D'),
	(93, 'DEAR NATHAN', 'Teraskota', '19:50', '35000', 'Regular 2D'),
	(94, 'DEAR NATHAN', 'Grage City Mall', '21:50', '30000', 'Regular 2D'),
	(95, 'DEAR NATHAN', 'Miko Mall', '21:45', '30000', 'Regular 2D'),
	(96, 'DEAR NATHAN', 'Slipi Jaya', '21:00', '35000', 'Regular 2D'),
	(97, 'THE MOMENT', 'Kawanua Mall', '21:00', '35000', 'Regular 2D'),
	(98, 'THE MOMENT', 'Jwalk Mall', '13:20', '30000', 'Regular 2D'),
	(99, 'THE MOMENT', 'Central Park', '21:20', '40000', 'Regular 2D'),
	(100, 'THE MOMENT', 'Grand Indonesia', '22:10', '50000', 'Regular 2D'),
	(101, 'THE MOMENT', 'Bekasi Cyber Park', '20:40', '30000', 'Regular 2D'),
	(102, 'THE MOMENT', 'Mall Of Indonesia', '19:15', '40000', 'Regular 2D'),
	(103, 'THE MOMENT', 'Focal Point Medan', '19:55', '35000', 'Regular 2D'),
	(104, 'THE MOMENT', 'Teraskota', '20:15', '35000', 'Regular 2D'),
	(105, 'THE MOMENT', 'Harbour Bay', '17:00', '25000', 'Regular 2D'),
	(106, 'THE MOMENT', 'Grage City Mall', '22:30', '30000', 'Regular 2D'),
	(107, 'DANUR', 'BEC Mall', '23:15', '35000', 'Regular 2D'),
	(108, 'DANUR', 'Grand Dadap City', '16:25', '30000', 'Regular 2D'),
	(109, 'DANUR', 'Central Park', '19:55', '40000', 'Regular 2D'),
	(110, 'DANUR', 'Plaza Balikpapan', '21:00', '25000', 'Regular 2D'),
	(111, 'DANUR', 'Miko Mall', '22:30', '30000', 'Regular 2D'),
	(112, 'DANUR', 'Jwalk Mall', '19:35', '30000', 'Regular 2D'),
	(113, 'DANUR', 'Festive Walk', '22:15', '35000', 'Regular 2D'),
	(114, 'DANUR', 'Sunrise Mall', '22:00', '35000', 'Regular 2D'),
	(115, 'DANUR', 'Social Market Palembang', '22:15', '35000', 'Regular 2D'),
	(116, 'DANUR', 'Bekasi Cyber Park', '22:00', '30000', 'Regular 2D'),
	(117, 'DANUR', 'Ecoplaza Cikupa', '21:55', '35000', 'Starium'),
	(118, 'DANUR', 'Focal Point Medan', '22:30', '35000', 'Regular 2D'),
	(119, 'DANUR', 'Harbour Bay', '20:20', '25000', 'Regular 2D'),
	(120, 'DANUR', 'Rita Supermall', '21:15', '30000', 'Regular 2D'),
	(121, 'DANUR', 'Green Pramuka Mall', '19:20', '35000', 'Regular 2D'),
	(122, 'DANUR', 'Kepri Mall', '21:40', '30000', 'Regular 2D'),
	(123, 'DANUR', 'Mall Of Indonesia', '17:55', '40000', 'Regular 2D'),
	(124, 'DANUR', 'Grand Galaxi Park', '20:40', '25000', 'Regular 2D'),
	(125, 'DANUR', 'Grage City Mall', '22:30', '30000', 'Regular 2D'),
	(126, 'DANUR', 'Marvell City', '21:55', '35000', 'Regular 2D'),
	(127, 'DANUR', 'Kawanua Mall', '19:00', '25000', 'Regular 2D'),
	(128, 'DANUR', 'Paris Van Java', '19:45', '40000', 'Regular 2D'),
	(129, 'DANUR', 'Grand Indonesia', '21:10', '50000', 'Regular 2D'),
	(130, 'DANUR', 'Teraskota', '22:00', '35000', 'Regular 2D'),
	(131, 'DANUR', 'Slipi Jaya', '22:45', '35000', 'Regular 2D'),
	(132, 'DANUR', 'Hartono Mall', '21:00', '35000', 'Regular 2D'),
	(133, 'GHOST IN THE SHELL', 'BEC Mall', '17:30', '40000', 'Regular 3D'),
	(134, 'GHOST IN THE SHELL', 'Grand Dadap City', '21:40', '30000', 'Regular 2D'),
	(135, 'GHOST IN THE SHELL', 'Central Park', '21:55', '110000', 'Velvet Cla'),
	(136, 'GHOST IN THE SHELL', 'Plaza Balikpapan', '21:30', '65000', 'Satin Clas'),
	(137, 'GHOST IN THE SHELL', 'Miko Mall', '21:10', '30000', 'Regular 2D'),
	(138, 'GHOST IN THE SHELL', 'Pacific Place', '20:20', '110000', 'Velvet Cla'),
	(139, 'GHOST IN THE SHELL', 'Sunrise Mall', '19:30', '35000', 'Regular 2D'),
	(140, 'GHOST IN THE SHELL', 'Social Market Palembang', '21:40', '50000', 'Velvet Cla'),
	(141, 'GHOST IN THE SHELL', 'Bekasi Cyber Park', '21:05', '30000', 'Regular 2D'),
	(142, 'GHOST IN THE SHELL', 'Ecoplaza Cikupa', '23:00', '35000', 'Regular 2D'),
	(143, 'GHOST IN THE SHELL', 'Focal Point Medan', '22:00', '35000', 'Regular 2D'),
	(144, 'GHOST IN THE SHELL', 'Harbour Bay', '23:00', '25000', 'Regular 2D'),
	(145, 'GHOST IN THE SHELL', 'Rita Supermall', '21:30', '30000', 'Regular 2D'),
	(146, 'GHOST IN THE SHELL', 'Green Pramuka Mall', '20:55', '35000', 'Regular 2D'),
	(147, 'GHOST IN THE SHELL', 'Kepri Mall', '20:40', '30000', 'Regular 2D'),
	(148, 'GHOST IN THE SHELL', 'Mall Of Indonesia', '20:40', '70000', 'Satin Clas'),
	(149, 'GHOST IN THE SHELL', 'Grand Galaxi Park', '19:45', '25000', 'Regular 2D'),
	(150, 'GHOST IN THE SHELL', 'Grage City Mall', '21:45', '30000', 'Regular 2D'),
	(151, 'GHOST IN THE SHELL', 'Marvell City', '19:15', '90000', '4DX 3D Cin'),
	(152, 'GHOST IN THE SHELL', 'Kawanua Mall', '23:15', '60000', 'Velvet Cla'),
	(153, 'GHOST IN THE SHELL', 'Paris Van Java', '23:05', '75000', 'Velvet Cla'),
	(154, 'GHOST IN THE SHELL', 'Grand Indonesia', '21:30', '50000', 'Sweet Box'),
	(155, 'GHOST IN THE SHELL', 'Teraskota', '18:45', '35000', 'Regular 2D'),
	(156, 'GHOST IN THE SHELL', 'Slipi Jaya', '23:05', '35000', 'Regular 2D'),
	(157, 'GHOST IN THE SHELL', 'Hartono Mall', '21:50', '65000', 'Velvet Cla');
/*!40000 ALTER TABLE `nowplayingcinema` ENABLE KEYS */;

-- Dumping structure for table movie.nowplayinginfo
CREATE TABLE IF NOT EXISTS `nowplayinginfo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `poster` varchar(100) DEFAULT NULL,
  `genre` varchar(20) DEFAULT NULL,
  `duration` varchar(20) DEFAULT NULL,
  `trailer` varchar(200) DEFAULT NULL,
  `plot` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=latin1;

-- Dumping data for table movie.nowplayinginfo: ~10 rows (approximately)
/*!40000 ALTER TABLE `nowplayinginfo` DISABLE KEYS */;
INSERT INTO `nowplayinginfo` (`id`, `name`, `poster`, `genre`, `duration`, `trailer`, `plot`) VALUES
	(1, 'LIFE', 'https://www.cgv.id//uploads/movie/compressed/MOV3108.jpg', 'SCIENCE FICTION', '103 Minutes', 'https://www.youtube.com/embed/EQxmNDRjdTM', 'awak kapal Stasiun Luar Angkasa Internasional  sukses menangkap roket jarak jauh yang kembali dari Mars dengan sampel di dalamnya. para awak kapal  bertugas mempelajari sampel, yang mungkin merupakan bukti pertama kehidupan di luar bumi. Namun, penelitian ini akhirnya menjadi bumerang . Terjebak kapal ISS dengan organisme yang tumbuh dengan cepat, awak kapal  harus mencari tahu bagaimana untuk membunuhnya sebelum berhasil melarikan diri dan memusnahkan bumi.'),
	(2, 'BEAUTY AND THE BEAST', 'https://www.cgv.id//uploads/movie/compressed/MOV3095.jpg', 'FANTASY', '129 Minutes', 'https://www.youtube.com/embed/RDDM_Cky9M8', 'Diangkat dari film animasi klasik Disney, dengan mitologi  Seorang pangeran muda, terkurung dalam bentuk hewan, yang hanya bisa di hancurkan dengan cinta sejati. Apa yang mungkin ini menjadi satu-satunya kesempatan yang datang ketika ia bertemu Belle, satu-satunya gadis manusia yang telah mengunjungi istana sejak kutukan itu ada'),
	(3, 'AYU ANAK TITIPAN SURGA', 'https://www.cgv.id//uploads/movie/compressed/MOV3064.jpg', 'DRAMA', '85 Minutes', 'https://www.youtube.com/embed/KXthDmMEV0U', 'Ayu, seorang anak perempuan yang usia Sembilan tahun sudah ditinggal Ayahnya meninggal dunia karena sakit komplikasi, nampaknya benar-benar menunjukan sifat yang jarang dimiliki anak-anak seusianya dan menjadi contoh bagi anak-anak yang lain. Kecerdasan dan kejujurannya, bisa menjadi pelipur lara dan kepercayaan bagi keluarga yang sedang dalam tekanan hidup yang ekonominya pas-pasan kesetia kawanannya, bisa menjadi sahabat bagi semua, termasuk pak karta sosok miskin yang hanya menjadi tukang kebun di sekolahnya. Keberaniannya bisa menjadi penolong sesama.'),
	(4, 'KONG SKULL ISLAND', 'https://www.cgv.id//uploads/movie/compressed/MOV3091.jpg', 'ACTION', '120 Minutes', 'https://www.youtube.com/embed/78DfhU3c3Q0', 'Pada tahun 1970an, sekelompok tim ilmuan tentara dan penjelajah  dikirim untuk bersatu untuk menelusuri sebuah pulau misterius di samudra Pasifik bernama Skull Island yang terletak di samudera Hindia. Mereka segera menyadari ancaman yang terdapat di pulau tersebut sebab Skull Island adalah rumah dari kera raksasa bernama King Kong yang memiliki kekuatan dahsyat dan kecerdasan yang menyerupai manusia.'),
	(5, 'PERFECT DREAM', 'https://www.cgv.id//uploads/movie/compressed/MOV3109.jpg', '', '111 Minutes', 'https://www.youtube.com/embed/Ocd9naaBprI', 'Bagi DIBYO keberhasilan diukur dari seberapa besar ia mampu  memenuhi ambisi hidupnya. Dari kehidupan jalanan menjalankan bisnis gelap, Dibyo berhasil menikahi Lisa, putri Marcel Himawan, seorang pengusaha  besar  di kalangan elite Surabaya. Dibyo bahkan berhasil mengembalikan  kejayaan bisnis Marcel. Harta berlimpah tak membuat Dibyo puas. Ambisi Dibyo adalah menguasai wilayah lawan bisnisnya, Hartono si mafia nomor satu. Pertikaian antar-geng pun tak terelakkan. Ambisi Dibyo makin meluap setelah mengenal Rina, pemilik galeri foto yang mampu memberi kehangatan cinta seorang ibu yang tak pernah Dibyo dapatkan selama ini.  Lisa harus memilih, mengikuti ambisi suaminya atau berjuang mempertahankan keutuhan keluarga yang ia cintai!'),
	(6, 'SMURFS THE LOST VILLAGE', 'https://www.cgv.id//uploads/movie/compressed/MOV3094.jpg', 'ANIMATION', '89 Minutes', 'https://www.youtube.com/embed/8LdpyRBE0aA', 'Ketika Smurfette (Demi Lovato) menemukan sebuah peta misterius, ia bersama para sahabatnya Brainy, Clumsy dan Hefty pergi dalam sebuah petualangan menuju sebuah hutan terlarang yang dihuni oleh hewan ajaib untuk mecari sebuah desa misterius sebelum penyihir jahat Gargamel menemukannya. Dengan melalui perjalan yang dipenuhi rintangan dan bahaya, para Smurf akan menemukan sebuah rahasia terbesar dalam sejarah kaum Smurf.'),
	(7, 'DEAR NATHAN', 'https://www.cgv.id//uploads/movie/compressed/MOV3105.jpg', 'DRAMA', '98 Minutes', 'https://www.youtube.com/embed/8GIQsLKMBkk', 'Tidak ada hal yang sangat diinginkan SALMA di sekolah barunya selain focus pada belajar dan menunjukkan prestasinya. Sebagai murid pindahan di SMA Garuda, Salma berusaha selektif memilih teman. Sayangnya pagi itu Salma terlambat datang dan seorang siswa yang tidak kenal menolongnya menyelinap kesekolah dan menyelamatkannya dari hukuman terlambat upacara bendera. Belakangan Salma tahu bahwa siswa penolong itu bernama NATHAN, murid paling berandal seantero sekolah yang hobi tawuran.'),
	(8, 'THE MOMENT', 'https://www.cgv.id//uploads/movie/compressed/MOV3128.jpg', 'ROMANCE', '93 Minutes', 'https://www.youtube.com/embed/Ux4zKJMo6VE', 'The Moment menceritakan kisah cinta yang berada di tiga negara berbeda, antara lain New York USA, London (Inggris) , dan Seoul (Korea Selatan).'),
	(9, 'DANUR', 'https://www.cgv.id//uploads/movie/compressed/MOV3084.jpg', 'HORROR', '78 Minutes', 'https://www.youtube.com/embed/YLU6Qfi0cDY', 'bercerita tentang Risa Dihari ulang tahunnya ke-8, Risa dengan polosnya meminta seorang teman agar ia tidak kesepian lagi. Namun ternyata ibunya, Elly, mulai curiga mendapati anaknya sering tertawa sendiri dan bermain seolah-olah dengan banyak teman, padahal Elly hanya melihatnya bermain sendiri! Elly mencari jalan untuk memisahkan Risa dari  sahabat nya yang ternyata hantu.  Dengan terpaksa teman Risa pergi dari rumah nenek nya dan berpisah dengan teman teman nya. 9 Tahun kemudian Risa harus kembali ke rumah tersebut menjaga nenek bersama adik nya Riri, kejadian kejadian aneh dan gangguan roh halus mulai terjadi lagi. Puncak nya ketika Riri tiba tiba menghilang, Risa harus menyelamatkan adiknya Riri dari hantu jahat yang berencana membawa Riri ke dunia lain. Saksikan film Danur yang diangkat dari Gerbang dialog Danur karya Risa Saraswati.'),
	(10, 'GHOST IN THE SHELL', 'https://www.cgv.id//uploads/movie/compressed/MOV3122.jpg', 'ACTION', '106 Minutes', 'https://www.youtube.com/embed/ON-m-IVhcjM', 'Cyborg yang melawan cyberterrorist komandan lapangan Mayor (Scarlett Johansson) dan satuan tugasnya  Bagian 9 menghentikan penjahat cyber dan hacker. Sekarang, mereka harus menghadapi musuh baru yang tidak akan berhenti untuk menyabotase kecerdasan teknologi  buatan Hanka Robotika \'');
/*!40000 ALTER TABLE `nowplayinginfo` ENABLE KEYS */;

-- Dumping structure for table movie.upcoming
CREATE TABLE IF NOT EXISTS `upcoming` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=latin1;

-- Dumping data for table movie.upcoming: ~15 rows (approximately)
/*!40000 ALTER TABLE `upcoming` DISABLE KEYS */;
INSERT INTO `upcoming` (`id`, `name`) VALUES
	(1, 'the last word'),
	(2, 'attraction'),
	(3, 'labuan hati'),
	(4, 'get out'),
	(5, 'night bus'),
	(6, 'the guys'),
	(7, 'kartini'),
	(8, 'sweet 20'),
	(9, 'mengejar halal'),
	(10, 'the curse'),
	(11, 'best friend forever'),
	(12, 'the boss baby'),
	(13, 'fast & furious 8'),
	(14, 'stip dan pensil'),
	(15, 'surau dan silek');
/*!40000 ALTER TABLE `upcoming` ENABLE KEYS */;

-- Dumping structure for table movie.upcominginfo
CREATE TABLE IF NOT EXISTS `upcominginfo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `trailer` varchar(200) DEFAULT NULL,
  `plot` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=latin1;

-- Dumping data for table movie.upcominginfo: ~12 rows (approximately)
/*!40000 ALTER TABLE `upcominginfo` DISABLE KEYS */;
INSERT INTO `upcominginfo` (`id`, `name`, `trailer`, `plot`) VALUES
	(1, 'BEST FRIEND FOREVER', 'https://www.youtube.com/embed/8uSdiV6Om7M', 'Berkisah tentang ke empat remeja perempuan, Bianca(19th), Laura(18th), Sascha(19th) dan Tammy(18th) berencana menghabiskan jumat malam bersama. Kali ini Bianca merencanakan dengan sangat tertata, tidak seperti malam-malam sebelumnya. Namun, kenyataan tak seperti apa yang mereka berempat bayangkan. Rencana yang Bianca buat berubah 180 derajat, dan menjadi malapetaka.'),
	(2, 'MENGEJAR HALAL', 'https://www.youtube.com/embed/skSeKOOvMgg', 'Kisah Haura berawal dengan batalnya pernikahan Haura dan Shidiq. Kegagalan pernikahan membuat Haura menjadi wanita yang terobsesi pada pernikahan sempurna, baginya butuh pria sempurna untuk mendapatkan pernikahan sempurna. Ditengah kegalauan menanti sosok pria sempurna, Haura dipertemukan dengan sosok Halal, pria yang memiliki semua yang diharapkan haura ada pada pasangan hidupnya. Haura melakukan segala cara demi menjadikan Halal pasangan hidupnya, Hubungan Haura dengan saudara dan sahabat-sahabatnya mulai renggang karena sikap egois Haura yang semakin tak terkendali kala mengejar Halal.'),
	(3, 'NIGHT BUS', 'https://www.youtube.com/embed/i5UzVLd88Ac', 'Sebuah Bis Malam terjebak di daerah konflik bernama Sampar, dan seluruh penumpang menghadapi teror dari pihak-pihak yang bertikai. Ancaman, todongan senjata, desingan peluru tidak bisa dihindari, mereka mencoba untuk selamat, berjuang untuk bisa tetap bertahan hidup, menjaga nyawa agar tidak menjadi korban. Siapa yang selamat? Siapa akan mati menjadi korban?'),
	(4, 'KARTINI', 'https://www.youtube.com/embed/ePQV41Rk9uw', 'Kisah perjuangan RA. Kartini (Dian Sastrowardoyo), pahlawan wanita yang paling populer di Indonesia. Tahun 1900- an yang boleh mendapat pendidikan hanya para Ningrat. Wanita tidak diperbolehkan berpendidikan tinggi, walaupun Ningrat & keturunan raja. Tujuan wanita Jawa hanya menjadi istri seorang pria bahkan untuk keturunan Ningrat.'),
	(5, 'STIP DAN PENSIL', 'https://www.youtube.com/embed/spIsyTJ0DS8', 'Toni (Ernest Prakasa), Aghi (Ardit Erwandha), Bubu (Tatjana Saphira) dan Saras (Indah Permatasari) adalah anak anak orang kaya yang dimusuhi anak anak di SMU sekolahnya. Karena dibanding yang lain mereka selalu merasa sok jago dan songong. . Suatu hari mereka mendapat tugas essay untuk menulis masalah sosial dari Pak Adam (Pandji Pragiwaksono). Alih-alih  menulis essay mereka malah sok bikin tindakan yang lebih kongkrit dengan membangun sekolah untuk anak anak orang miskin di kolong jembatan. Awalnya mereka menganggap hal itu enteng, tapi ternyata hal itu tidak semudah yang mereka bayangkan. Karena banyak sekali rintangan di sekelilingnya yang menghadang. Mulai dari kepala suku pemulung disana, Pak Toro (Arie Kriting), Si anak kecil yang bengal, Ucok (Iqbal Sinchan) dan Mak Rambe (Gita Bhebhita) emaknya Ucok yang gak setuju anaknya ikut sekolah gratis yang diadakan Toni cs. Belum lagi ledekan teman teman di sekolahnya yang diketuai oleh Edwin (Rangga Azof) yang selalu meremehkan mereka. Berhasilkah mereka mewujudkan keinginannya untuk mendirikan sekolah buat anak anak miskin itu. Temukan jawabannya di dalam film Stip dan pensil.'),
	(6, 'ATTRACTION', 'https://www.youtube.com/embed/tgtMEn62y4g', 'Setelah asing kapal menabrak kota di Rusia, banyak yang melihat dan penduduk mulai mempertanyakan eksistensi mereka sendiri sementara ada orang-orang yang bersikeras agar alien meninggalkan Bumi.'),
	(7, 'THE CURSE', 'https://www.youtube.com/embed/rvmN3mZIqdI', 'Shelina, gadis Indonesia yang bekerja di salah satu kantor pengacara di Melbourne. Suatu malam ia didatangi sosok roh di rumahnya dan semakin hari semakin sosok itu semakin mencekam. Seorang paranormal datang untuk melakukan pengusiran. Namun ada pesan dari kehadiran roh halus itu, dan Shelina harus menghadapi sesuatu yang sangat menakutkan.'),
	(8, 'FAST & FURIOUS 8', 'https://www.youtube.com/embed/F1aKP6oOH58', 'Dom dan Letty di bulan madu mereka, Brian dan Mia setelah pensiun dari permainan, dan kru lainnya telah dibebaskan dan menemukan kehidupan normal. Tapi ketika seorang wanita misterius menggoda Dom kembali ke dunia kejahatan dia tidak bisa melarikan diri, menyebabkan dia mengkhianati orang-orang terdekat dia, mereka akan menghadapi rintangan yang akan menguji mereka yang belum pernah dihadapi sebelumnya.'),
	(9, 'THE LAST WORD', 'https://www.youtube.com/embed/Njzmfp0bd6U', 'Harriet adalah seorang pensiunan pengusaha  yang mencoba untuk mengontrol segala sesuatu di sekelilingnya. Ketika dia memutuskan untuk menulis Obituary sendiri, seorang wartawan muda mengambil tugas untuk mencari tahu kebenaran yang dihasilkan dalam persahabatan yang mengubah hidup.'),
	(10, 'SURAU DAN SILEK', 'https://www.youtube.com/embed/beAMEP5x4dE', 'Adil (11th) adalah seorang anak yatim yang sangat menginginkan Ayah nya masuk surga dengan cara menjadi anak yang shaleh. Namun di saat yang bersamaan Adil juga sangat berambisi memenangkan pertandingan Silat di kampungnya. Ambisi Adil ini di dasari oleh kekalahan yang di alaminya pada pertandingan periode sebelumnya. Adil di kalahkan oleh Hardi (11Th). Hardi melakukan kecurangan dengan menyiramkan serbuk jerami ke mata Adil. Namun hal ini tidak di akui oleh Hardi. Karena menurut Hardi, Adil hanya mencari cari alasan atas kekalahan yang di alami nya. Dan di saat bersamaan Adil juga tidak bisa membuktikan kecurangan tersebut kepada dewan juri saat pertandingan final periode sebelumnya tersebut.'),
	(11, 'LABUAN HATI', 'https://www.youtube.com/embed/D3S_F07qmkY', 'Berawal dari Labuan Bajo 3 perempuan; Bia,Indi, dan Maria ada di dalam satu kapal & berpetualang ke pulau Komodo bersama Mahesa sang instruktur diving. Dalam waktu singkat, hubungan tiga perempuan ini berubah dari pertemanan, persahabatan, hingga perang dingin untuk merebut hati Mahesa. '),
	(12, 'THE GUYS', 'https://www.youtube.com/embed/JxTmtJ0Fc2U', 'Alfi bercita-cita menjadi bos dan ingin mendapatkan cinta tambatan hatinya yang juga teman sekantornya, Amira. Masalah muncul ketika Via, gebetannya menunjukkan rasa cinta. Dibantu dengan teman-temannya yang aneh & karyawan ekspat dari Thailand, Alfi mencoba mewujudkan mimpinya menjadi seorang bos sekaligus menggaet cinta sejatinya.');
/*!40000 ALTER TABLE `upcominginfo` ENABLE KEYS */;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
