/*
SQLyog Professional v13.1.1 (64 bit)
MySQL - 10.4.28-MariaDB : Database - ciptapro_jjm
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
USE `ciptapro_jjm`;

/*Table structure for table `preference_company` */

DROP TABLE IF EXISTS `preference_company`;

CREATE TABLE `preference_company` (
  `company_id` int(11) NOT NULL AUTO_INCREMENT,
  `company_name` varchar(100) DEFAULT '',
  `member_mandatory_savings` decimal(20,2) DEFAULT 0.00,
  `central_branch_id` int(11) DEFAULT 0,
  `account_cash_id` int(11) DEFAULT 0,
  `principal_savings_id` int(11) DEFAULT 0,
  `special_savings_id` int(11) DEFAULT 0,
  `mandatory_savings_id` int(11) DEFAULT 0,
  `cash_deposit_id` int(11) DEFAULT 0,
  `cash_withdrawal_id` int(11) DEFAULT 0,
  `account_interest_id` int(11) DEFAULT 0,
  `account_savings_transfer_from_id` int(11) DEFAULT 0,
  `account_savings_transfer_to_id` int(11) DEFAULT 0,
  `savings_profit_sharing_id` int(11) DEFAULT 0,
  `deposito_profit_sharing_id` int(11) DEFAULT 0,
  `account_savings_profit_sharing_id` int(11) DEFAULT 0,
  `account_deposito_profit_sharing_id` int(11) DEFAULT 0,
  `account_deposito_accrual_id` int(11) DEFAULT 0,
  `account_deposito_id` int(11) DEFAULT 0,
  `credits_payment_fine_percentage` decimal(10,2) NOT NULL DEFAULT 0.00,
  `account_credits_payment_fine` int(11) DEFAULT 0,
  `deposito_basil_id` int(11) DEFAULT 0,
  `account_deposito_basil_id` int(11) DEFAULT 0,
  `account_acquittance_loss_id` int(11) DEFAULT 0,
  `account_penalty_id` int(11) DEFAULT 0,
  `account_notary_cost_id` int(11) DEFAULT 0,
  `account_insurance_cost_id` int(11) DEFAULT 0,
  `account_central_capital_id` int(11) DEFAULT 0,
  `account_principal_savings_id` int(11) NOT NULL,
  `account_mandatory_savings_id` int(11) NOT NULL,
  `account_special_savings_id` int(11) NOT NULL,
  `account_mutation_adm_id` int(11) NOT NULL,
  `account_savings_tax_id` int(11) DEFAULT 0,
  `account_interest_income_id` int(11) DEFAULT NULL,
  `account_others_income_id` int(11) DEFAULT NULL,
  `account_materai_id` int(11) DEFAULT NULL,
  `account_risk_reserve_id` int(11) DEFAULT NULL,
  `account_stash_id` int(11) DEFAULT NULL,
  `account_principal_id` int(11) DEFAULT NULL,
  `account_commission_id` int(11) DEFAULT NULL,
  `account_income_tax_id` int(11) DEFAULT NULL,
  `account_shu_last_year` int(11) DEFAULT NULL,
  `tax_minimum_amount` int(11) NOT NULL DEFAULT 0,
  `tax_percentage` double(10,2) NOT NULL DEFAULT 0.00,
  `logo_koperasi` text DEFAULT NULL,
  `logo_koperasi_icon` text DEFAULT NULL,
  `logo_koperasi_icon_gray` text DEFAULT NULL,
  `user_time_limit` varchar(20) DEFAULT '',
  `savings_id` int(11) DEFAULT 0,
  `maintenance_status` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `data_state` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`company_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

/*Data for the table `preference_company` */

insert  into `preference_company`(`company_id`,`company_name`,`member_mandatory_savings`,`central_branch_id`,`account_cash_id`,`principal_savings_id`,`special_savings_id`,`mandatory_savings_id`,`cash_deposit_id`,`cash_withdrawal_id`,`account_interest_id`,`account_savings_transfer_from_id`,`account_savings_transfer_to_id`,`savings_profit_sharing_id`,`deposito_profit_sharing_id`,`account_savings_profit_sharing_id`,`account_deposito_profit_sharing_id`,`account_deposito_accrual_id`,`account_deposito_id`,`credits_payment_fine_percentage`,`account_credits_payment_fine`,`deposito_basil_id`,`account_deposito_basil_id`,`account_acquittance_loss_id`,`account_penalty_id`,`account_notary_cost_id`,`account_insurance_cost_id`,`account_central_capital_id`,`account_principal_savings_id`,`account_mandatory_savings_id`,`account_special_savings_id`,`account_mutation_adm_id`,`account_savings_tax_id`,`account_interest_income_id`,`account_others_income_id`,`account_materai_id`,`account_risk_reserve_id`,`account_stash_id`,`account_principal_id`,`account_commission_id`,`account_income_tax_id`,`account_shu_last_year`,`tax_minimum_amount`,`tax_percentage`,`logo_koperasi`,`logo_koperasi_icon`,`logo_koperasi_icon_gray`,`user_time_limit`,`savings_id`,`maintenance_status`,`created_at`,`updated_at`,`deleted_at`,`data_state`) values 
(0,'KOPERASI JACOB JAYA MANDIRI',0.00,1,433,4,6,5,1,2,472,11,10,6,6,0,0,0,0,0.05,475,9,488,312,476,520,521,248,492,494,497,473,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,0.00,'logo\\logo-v1-250x250.png','logo\\logo-v1-250x250.png','logo\\logo-v1-250x250.png','23:00:00',11,0,NULL,NULL,NULL,0);

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
