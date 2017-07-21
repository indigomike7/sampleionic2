<?php

$db = Phpfox::getLib('database');

// fix mobile phrases
$db->query("UPDATE `" . Phpfox::getT('language_phrase') . "` SET `text` = `text_default` WHERE `language_id` = 'en' AND `module_id` = 'mfox' AND `var_name` IN ('about_me', 'about_me_', 'album', 'album_', 'can_not_get_data_from_server', 'can_not_get_data_from_server_', 'can_not_load_data_from_server', 'can_not_load_data_from_server_', 'edit_post', 'edit_post_', 'friends', 'friends_', 'members', 'members_', 'more', 'more_', 'on', 'on_', 'page', 'page_', 'photo', 'photo_', 'photos', 'photos_', 'poll', 'poll_', 'post', 'post_', 'posts', 'posts_', 'send_message', 'send_message_', 'threads', 'threads_', 'user_results', 'user_results_', 'video_title', 'video_title_');");

// add menu
$db->query("INSERT INTO `" . Phpfox::getT('mfox_leftnavi') . "` (`id`, `name`, `is_enabled`, `sort_order`, `label`, `module`, `module_alt`, `icon`, `url`) VALUES 
	(NULL, 'videochannel', '1', '1', 'Video Channel', 'videochannel', '', 'ion-ios-videocam', '/app/videochannel'),
	(NULL, 'directory', '1', '1', 'Business Directory', 'directory', '', 'ion-cash', '/app/directory');");
