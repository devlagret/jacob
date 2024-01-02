/*
SQLyog Professional v13.1.1 (64 bit)
MySQL - 10.4.28-MariaDB : Database - dev_ciptapro_jjm
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
USE `ciptapro_jjm`;

/*Table structure for table `acct_profit_loss_report` */

DROP TABLE IF EXISTS `acct_profit_loss_report`;

CREATE TABLE `acct_profit_loss_report` (
  `profit_loss_report_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `format_id` int(11) DEFAULT 0,
  `report_no` int(11) DEFAULT 0,
  `account_type_id` int(11) DEFAULT 0,
  `account_id` int(11) DEFAULT 0,
  `account_code` varchar(20) DEFAULT '',
  `account_name` varchar(100) DEFAULT '',
  `report_formula` varchar(250) DEFAULT NULL,
  `report_operator` varchar(250) DEFAULT NULL,
  `report_type` int(11) DEFAULT 0,
  `report_tab` int(11) DEFAULT 0,
  `report_bold` int(11) DEFAULT 0,
  `created_id` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `data_state` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`profit_loss_report_id`),
  KEY `account_type_id` (`account_type_id`),
  KEY `account_id` (`account_id`),
  KEY `report_no` (`report_no`)
) ENGINE=InnoDB AUTO_INCREMENT=98 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

/*Data for the table `acct_profit_loss_report` */

insert  into `acct_profit_loss_report`(`profit_loss_report_id`,`format_id`,`report_no`,`account_type_id`,`account_id`,`account_code`,`account_name`,`report_formula`,`report_operator`,`report_type`,`report_tab`,`report_bold`,`created_id`,`created_at`,`updated_at`,`deleted_at`,`data_state`) values 
(51,0,1,2,0,'','PENDAPATAN ','','',1,1,1,37,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,0),
(52,0,2,2,472,'400.01','Pendapatan Bunga Pinjaman','','',3,3,0,37,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,0),
(53,0,3,2,473,'400.02','Pendapatan Administrasi','','',3,3,0,37,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,0),
(54,0,4,2,474,'400.03','Pendapatan Provisi','','',3,3,0,37,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,0),
(55,0,5,2,475,'400.04','Pendapatan Denda','','',3,3,0,37,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,0),
(56,0,6,2,476,'400.05','Pendapatan Pinalty Pinjaman','','',3,3,0,37,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,0),
(57,0,7,2,477,'400.06','Pendapatan Adm Penutupan Tabungan','','',3,3,0,37,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,0),
(58,0,8,2,478,'400.07','Pend. Penggantian Buku Tabungan','','',3,3,0,37,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,0),
(59,0,9,2,545,'400.08','Pend.Lain - Lain','','',3,3,0,37,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,0),
(60,0,10,2,0,'',' JUMLAH PENDAPATAN','2#3#4#5#6#7#8#9','+#+#+#+#+#+#+#+#+#+',5,2,1,37,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,0),
(61,0,11,0,0,'','','','',0,3,0,37,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,0),
(62,0,12,3,0,'','BEBAN OPERASIONAL','','',1,1,1,37,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,0),
(63,0,13,3,481,'401.01','Beban Bunga Simpanan','','',3,3,0,37,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,0),
(64,0,14,3,482,'401.02','Beban Bunga Simpanan Berjangka','','',3,3,0,37,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,0),
(65,0,15,3,483,'401.03','Beban Gaji Pengelola','','',3,3,0,37,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,0),
(66,0,16,3,484,'401.04','Beban Sewa','','',3,3,0,37,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,0),
(67,0,17,3,485,'401.05','Beban ATK','','',3,3,0,37,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,0),
(68,0,18,3,486,'401.06','Beban Telepon dan Listrik','','',3,3,0,37,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,0),
(69,0,19,3,487,'401.07','Beban Barang Cetakan','','',3,3,0,37,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,0),
(70,0,20,3,488,'401.08','Beban Insentif','','',3,3,0,37,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,0),
(71,0,21,3,489,'401.09','Beban Komisi','','',3,3,0,37,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,0),
(72,0,22,3,490,'401.10','Beban Cadangan Resiko','','',3,3,0,37,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,0),
(73,0,23,3,491,'401.11','Beban PPAP','','',3,3,0,37,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,0),
(74,0,24,3,492,'401.12','Beban Penyusutan Inventaris','','',3,3,0,37,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,0),
(75,0,25,3,493,'401.13','Beban Peny Biaya Dibayar Dimuka','','',3,3,0,37,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,0),
(76,0,26,3,494,'401.14','Biaya Promosi/Marketing','','',3,3,0,37,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,0),
(77,0,27,0,0,'','','','',0,3,0,37,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,0),
(78,0,28,3,0,'','JUMLAH BEBAN OPERASIONAL','13#14#15#16#17#18#19#20#21#22#23#24#25#26','+#+#+#+#+#+#+#+#+#+#+#+#+#+',5,2,1,37,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,0),
(79,0,29,0,0,'','','','',0,3,0,37,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,0),
(80,0,30,0,0,'','','','',0,3,0,37,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,0),
(81,0,31,3,0,'','BEBAN NON OPERASIONAL','','',1,2,1,37,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,0),
(82,0,32,3,496,'402.01','Biaya Pelatihan','','',3,3,0,37,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,0),
(83,0,33,3,497,'402.02','Biaya Materai','','',3,3,0,37,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,0),
(84,0,34,3,498,'402.03','Biaya Entertaint','','',3,3,0,37,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,0),
(85,0,35,3,499,'402.04','Biaya Perbaikan/Maintenance','','',3,3,0,37,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,0),
(86,0,36,3,500,'402.05','Beban Sosial','','',3,3,0,37,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,0),
(87,0,37,3,503,'402.06','Biaya RAT','','',3,3,0,37,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,0),
(88,0,38,3,504,'402.07','Biaya Rapat','','',3,3,0,37,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,0),
(89,0,39,3,505,'402.08','Beban Pihak III','','',3,3,0,37,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,0),
(90,0,40,3,506,'402.09','Beban Pajak','','',3,3,0,37,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,0),
(91,0,41,3,507,'402.10','Beban Lain-lain','','',3,3,0,37,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,0),
(92,0,42,0,0,'','','','',0,3,0,37,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,0),
(93,0,43,3,0,'','JUMLAH NON BEBAN OPERASIONAL','32#33#34#35#36#37#38#39#40#41','+#+#+#+#+#+#+#+#+#+#+',5,2,1,37,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,0),
(94,0,44,0,0,'','','','',0,3,0,37,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,0),
(95,0,45,3,0,'','JUMLAH BEBAN','13#14#15#16#17#18#19#20#21#22#23#24#25#26#32#33#34#35#36#37#38#39#40#41','+#+#+#+#+#+#+#+#+#+#+#+#+#+#+#+#+#+#+#+#+#+#+#+',6,2,1,37,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,0),
(96,0,46,0,0,'','','','',0,3,0,37,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,0),
(97,0,47,0,0,'410.00','Sisa Hasil Usaha','10#45','-#-',1,2,1,37,'0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,0);

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
