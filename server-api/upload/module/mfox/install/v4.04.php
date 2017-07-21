<?php

$db = Phpfox::getLib('database');

// Update Menus
$tbNav = Phpfox::getT('mfox_leftnavi');

$db->query("ALTER TABLE `" . $tbNav . "` 
    CHANGE `label` `label` VARCHAR(50) NULL DEFAULT NULL, 
    CHANGE `module` `module` VARCHAR(50) NULL DEFAULT NULL, 
    CHANGE `module_alt` `module_alt` VARCHAR(32) NULL DEFAULT NULL, 
    CHANGE `icon` `icon` VARCHAR(50) NULL DEFAULT NULL, 
    CHANGE `url` `url` VARCHAR(50) NULL DEFAULT NULL;");

if (!$db->isField($tbNav, 'is_group')) {
    $db->query("ALTER TABLE `" . $tbNav . "` ADD `is_group` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0';");  
}

if (!$db->isField($tbNav, 'parent_id')) {
    $db->query("ALTER TABLE `" . $tbNav . "` ADD `parent_id` INT(10) UNSIGNED NOT NULL DEFAULT '0';");  
}

// Update Settings
$tbSetting = Phpfox::getT('setting');
$tbPhrase = Phpfox::getT('language_phrase');

$db->query("UPDATE `" . $tbSetting . "` SET `is_hidden` = '0' WHERE `product_id` = 'younet_mfox4' AND `var_name` = 'replace_music';");
$db->query("UPDATE `" . $tbPhrase . "` SET `text_default` = '<title>Use Music Sharing on mobile app</title><info>If select \"True\" and the Music Sharing is enable on full site, system will use it in mobile app. Otherwise, the Default Music will be used.</info>', `text` = '<title>Use Music Sharing on mobile app</title><info>If select \"True\" and the Music Sharing is enable on full site, system will use it in mobile app. Otherwise, the Default Music will be used.</info>' WHERE `language_id` = 'en' AND `product_id` = 'younet_mfox4' AND `var_name` = 'setting_replace_music';");

$db->query("UPDATE `" . $tbSetting . "` SET `is_hidden` = '0' WHERE `product_id` = 'younet_mfox4' AND `var_name` = 'replace_event';");
$db->query("UPDATE `" . $tbPhrase . "` SET `text_default` = '<title>Use Advanced Event on mobile app</title><info>If select \"True\" and the Advanced Event is enable on full site, system will use it in mobile app. Otherwise, the Default Event will be used.</info>', `text` = '<title>Use Advanced Event on mobile app</title><info>If select \"True\" and the Advanced Event is enable on full site, system will use it in mobile app. Otherwise, the Default Event will be used.</info>' WHERE `language_id` = 'en' AND `product_id` = 'younet_mfox4' AND `var_name` = 'setting_replace_event';");

$db->query("UPDATE `" . $tbSetting . "` SET `is_hidden` = '0' WHERE `product_id` = 'younet_mfox4' AND `var_name` = 'replace_photo';");
$db->query("UPDATE `" . $tbPhrase . "` SET `text_default` = '<title>Use Advanced Photo on mobile app</title><info>If select \"True\" and the Advanced Photo is enable on full site, system will use it in mobile app. Otherwise, the Default Photo will be used.</info>', `text` = '<title>Use Advanced Photo on mobile app</title><info>If select \"True\" and the Advanced Photo is enable on full site, system will use it in mobile app. Otherwise, the Default Photo will be used.</info>' WHERE `language_id` = 'en' AND `product_id` = 'younet_mfox4' AND `var_name` = 'setting_replace_photo';");

$db->query("UPDATE `" . $tbSetting . "` SET `is_hidden` = '0' WHERE `product_id` = 'younet_mfox4' AND `var_name` = 'replace_marketplace';");
$db->query("UPDATE `" . $tbPhrase . "` SET `text_default` = '<title>Use Advanced Marketplace on mobile app</title><info>If select \"True\" and the Advanced Marketplace is enable on full site, system will use it in mobile app. Otherwise, the Default Marketplace will be used.</info>', `text` = '<title>Use Advanced Marketplace on mobile app</title><info>If select \"True\" and the Advanced Marketplace is enable on full site, system will use it in mobile app. Otherwise, the Default Marketplace will be used.</info>' WHERE `language_id` = 'en' AND `product_id` = 'younet_mfox4' AND `var_name` = 'setting_replace_marketplace';");
