<?php

defined('PHPFOX') or exit('NO DICE!');


function ynmf_install307()
{
    $oDatabase = Phpfox::getLib('database');

	// table support In App Purchase payment protocol 
	$oDatabase -> query("CREATE TABLE IF NOT EXISTS `".Phpfox::getT('mfox_transaction')."` (
		`transaction_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
		`transaction_method_id` TINYINT(1) UNSIGNED,
		`transaction_status_id` TINYINT(1) UNSIGNED,
		`extra` TEXT,
		`transaction_description` MEDIUMTEXT,
		`transaction_amount` DECIMAL(10,2)  DEFAULT '0.00',
		`transaction_currency` VARCHAR(4) DEFAULT 'USD',
		`gateway_transaction_id` VARCHAR(255),
		`transaction_start_date` INT(10) UNSIGNED,
		`transaction_pay_date` INT(10) UNSIGNED,	
		`transaction_item_id` INT(10) UNSIGNED COMMENT 'product or invoice id' ,
		`transaction_item_type` VARCHAR(255) COMMENT 'each module can have more payment types' ,
    `transaction_module_id` VARCHAR(75),
		`transaction_store_kit_purchase_id` VARCHAR(255) COMMENT 'store kit to purchase on IAP' ,
    `transaction_store_kit_transaction_id` VARCHAR(255) COMMENT 'store kit of transaction on IAP' ,
		`transaction_user_id` INT(10) UNSIGNED,
		PRIMARY KEY (`transaction_id`),
		KEY `transaction_user_method_status_id` (`transaction_user_id`, `transaction_method_id`, `transaction_status_id`),
		KEY `transaction_user_status_id` (`transaction_user_id`, `transaction_status_id`),
		KEY `transaction_method_id` (`transaction_method_id`),
		KEY `transaction_status_id` (`transaction_status_id`)		
	);");

  // table has supported storing kit
  $oDatabase -> query("CREATE TABLE IF NOT EXISTS `".Phpfox::getT('mfox_storekitpurchase')."` (
    `storekitpurchase_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `storekitpurchase_key` VARCHAR(75) COMMENT 'which has been created manually on Apple Dev' ,
    `storekitpurchase_module_id` VARCHAR(75),
    `storekitpurchase_type` VARCHAR(255) DEFAULT 'purchase_product' COMMENT 'purchase product/sponsor/feature/...',
    `storekitpurchase_item_id` INT(10) UNSIGNED DEFAULT NULL COMMENT 'some modules need item id' ,
    PRIMARY KEY (`storekitpurchase_id`) 
  );");

  if (!$oDatabase->isField(Phpfox::getT('mfox_storekitpurchase'), 'storekitpurchase_device'))
  {
      $oDatabase->query("ALTER TABLE `".Phpfox::getT('mfox_storekitpurchase')."` ADD `storekitpurchase_device` tinyint(1) DEFAULT '1'");
  }  	
}

ynmf_install307();

?>