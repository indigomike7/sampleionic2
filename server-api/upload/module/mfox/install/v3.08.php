<?php

defined('PHPFOX') or exit('NO DICE!');


function ynmf_install308()
{
    $db = Phpfox::getLib('database');
	
	$db =  Phpfox::getLib('database');
	
	$sql = "ALTER TABLE `".Phpfox::getT('mfox_token')."` ADD  `latitude` DOUBLE NOT NULL DEFAULT  '0' AFTER  `email` , ADD  `longitude` DOUBLE NOT NULL DEFAULT  '0' AFTER  `latitude`;";
	
	$db->query($sql);
}

// ynmf_install308();

?>