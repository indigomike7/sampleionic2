<?php

/**
 * @package mfox
 * @version 3.01
 */
defined('PHPFOX') or exit('NO DICE!');

class Mfox_Service_Helper_Notification extends Phpfox_Service
{
	public function getNumberOfUnseenMessage() {
		return Phpfox::getService('mail')->getUnseenTotal();
	}

	public function getNumberOfUnseenNotification() {
		return Phpfox::getService('notification')->getUnseenTotal();
	}

	public function getNumberOfUnseenFriendRequest() {
		return Phpfox::getService('friend.request')->getUnseenTotal();
	}

}
