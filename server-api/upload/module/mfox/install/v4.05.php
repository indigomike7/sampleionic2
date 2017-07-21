<?php

$db = Phpfox::getLib('database');

$db->query("INSERT INTO `" . Phpfox::getT('mfox_leftnavi') . "` (`id`, `name`, `is_enabled`, `sort_order`, `label`, `module`, `module_alt`, `icon`, `url`) VALUES (NULL, 'group', '1', '1', 'Groups', 'groups', '', 'ion-person-stalker', '/app/groups');");
