<?php

$db = Phpfox::getLib('database');

$sSql = "UPDATE `" . Phpfox::getT('product') . "` SET `is_active` = 0 WHERE `product_id` = 'younet_mfox';";
$db->query($sSql);

$sSql = "DELETE FROM `" . Phpfox::getT('module') . "` WHERE `product_id` = 'younet_mfox';";
$db->query($sSql);

$sSql = "DELETE FROM `" . Phpfox::getT('mfox_leftnavi') . "` WHERE `name` IN ('video', 'subscription', 'setting');";
$db->query($sSql);
