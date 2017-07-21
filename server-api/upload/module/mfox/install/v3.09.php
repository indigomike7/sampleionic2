<?php
$db = Phpfox::getLib('database');

$sSql = "DROP TABLE IF EXISTS " . Phpfox::getT('mfox_leftnavi');
$db->query($sSql);

$sSql = "CREATE TABLE IF NOT EXISTS `" . Phpfox::getT('mfox_leftnavi') . "` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL,
  `is_enabled` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `sort_order` int(10) unsigned NOT NULL DEFAULT '1',
  `label` varchar(50) NOT NULL,
  `module` varchar(50) NOT NULL DEFAULT '',
  `module_alt` varchar(32) NOT NULL DEFAULT '',
  `icon` varchar(50) NOT NULL,
  `url` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
);";
$db->query($sSql);

$sSql = "INSERT INTO `" . Phpfox::getT('mfox_leftnavi') . "` (`id`, `name`, `is_enabled`, `sort_order`, `label`, `module`, `module_alt`, `icon`, `url`) VALUES
(1, 'activity', 1, 1, 'News Feed', 'core', '', 'ion-card', '/app/newsfeed'),
(2, 'friend', 1, 1, 'Friends', 'user', '', 'ion-ios-people-outline', '/app/friends'),
(3, 'message', 1, 1, 'Messages', 'mail', '', 'ion-ios-email-outline', '/app/messages'),
(4, 'member', 1, 1, 'Members', 'user', '', 'ion-ios-personadd-outline', '/app/members'),
(5, 'photo', 1, 1, 'Photos', 'photo', 'advancedphoto', 'ion-ios-photos-outline', '/app/albums'),
(6, 'video', 1, 1, 'Videos', 'video', 'videochannel', 'ion-ios-film-outline', '/app/videos'),
(7, 'event', 1, 1, 'Events', 'event', 'fevent', 'ion-ios-calendar-outline', '/app/events'),
(8, 'music', 1, 1, 'Music', 'music', 'musicsharing', 'ion-ios-musical-notes', '/app/music_songs'),
(9, 'quiz', 1, 1, 'Quizzes', 'quiz', '', 'ion-ios-help-outline', '/app/quizzes'),
(10, 'poll', 1, 1, 'Polls', 'poll', '', 'ion-ios-analytics-outline', '/app/polls'),
(11, 'blog', 1, 1, 'Blogs', 'blog', '', 'ion-ios-paper-outline', '/app/blogs'),
(12, 'marketplace', 1, 1, 'Marketplace', 'marketplace', 'advancedmarketplace', 'ion-ios-briefcase-outline', '/app/listings'),
(13, 'page', 1, 1, 'Pages', 'page', '', 'ion-ios-copy-outline', '/app/pages'),
(14, 'forum', 1, 1, 'Forums', 'forum', '', 'ion-ios-bookmarks-outline', '/app/forums'),
(15, 'subscription', 1, 1, 'Memberships', 'core', '', 'ion-ribbon-b', '/app/subscriptions'),
(16, 'setting', 1, 1, 'Settings', 'core', '', 'ion-ios-gear-outline', '/app/settings');";
$db->query($sSql);

$sSql = "DROP TABLE IF EXISTS " . Phpfox::getT('mfox_style');
$db->query($sSql);

$sSql = "CREATE TABLE IF NOT EXISTS `" . Phpfox::getT('mfox_style') . "` (
  `style_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `is_publish` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `build_number` int(10) unsigned NOT NULL DEFAULT '0',
  `name` varchar(50) NOT NULL,
  `data` text NOT NULL,
  `time_stamp` int(10) unsigned NOT NULL,
  PRIMARY KEY (`style_id`)
);";
$db->query($sSql);

$sSql = "INSERT INTO `" . Phpfox::getT('mfox_style') . "` (`style_id`, `is_publish`, `build_number`, `name`, `data`, `time_stamp`) VALUES
(1, 1, 0, 'Default', 'a:1:{s:8:\"positive\";s:7:\"#01a0db\";}', 0);";
$db->query($sSql);
