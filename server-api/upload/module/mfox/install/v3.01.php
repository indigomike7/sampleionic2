<?php
/**
 * @package mfox
 * @version 3.01
 */
defined('PHPFOX') or exit('NO DICE!');

/**
 * @author ductc@younetco.com
 * @package mfox
 * @subpackage mfox.service
 * @version 3.01
 * @since May 21, 2013
 * @link Mfox Api v3.0
 */

$db =  Phpfox::getLib('database');

$sql = "CREATE TABLE IF NOT EXISTS `".Phpfox::getT('mfox_token')."` (
	`token_id` VARCHAR(64) NOT NULL,
	`user_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
	`created_at` INT(11) UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (`token_id`)
);";

$db->query($sql);

$sSql = "CREATE TABLE IF NOT EXISTS `" . Phpfox::getT('mfox_device') . "` (
  `id` varchar(45) NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `token` varchar(64) DEFAULT NULL,
  `timestamp` int(10) NOT NULL,
  `platform` varchar(50) DEFAULT NULL,
  `device_id` tinytext NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
$db->query($sSql);

$sSql = "CREATE TABLE IF NOT EXISTS `" . Phpfox::getT('mfox_leftnavi') . "` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL,
  `is_enabled` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `sort_order` int(10) unsigned NOT NULL DEFAULT '1',
  `label` varchar(50) NOT NULL,
  `layout` varchar(50) NOT NULL,
  `icon` varchar(50) NOT NULL,
  `url` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=13 ;";
$db->query($sSql);

$sSql = "CREATE TABLE IF NOT EXISTS `" . Phpfox::getT('mfox_style') . "` (
  `style_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `is_publish` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `name` varchar(50) NOT NULL,
  `data` text NOT NULL,
  `time_stamp` int(10) unsigned NOT NULL,
  PRIMARY KEY (`style_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;";
$db->query($sSql);
