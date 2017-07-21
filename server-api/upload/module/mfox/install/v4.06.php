<?php

$db = Phpfox::getLib('database');

$db->query("INSERT INTO `" . Phpfox::getT('mfox_leftnavi') . "` (`id`, `name`, `is_enabled`, `sort_order`, `label`, `module`, `module_alt`, `icon`, `url`) VALUES (NULL, 'ultimatevideo', '1', '1', 'Ultimate Videos', 'ultimatevideo', '', 'ion-social-youtube-outline', '/app/ultimatevideo');");

$db->query("UPDATE `" . Phpfox::getT('language_phrase') ."` SET text = '<title>Default Chat Module</title><info>Support YNChat, CometChat, Instant Messaging module on mobile</info>' WHERE var_name = 'setting_chat_module' AND language_id = 'en';");